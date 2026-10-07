<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class MY_Controller extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->output->set_header('Cache-Control: no-store, private');
    }
    protected function player() {
        $id=$this->session->userdata('codJogador');
        if (!$id || !$this->session->userdata('logged_in')) {redirect('login');exit;}
        return (int)$id;
    }
    protected function postOnly() { if ($this->input->method()!=='post') {show_error('Método não permitido.',405);exit;} }
    protected function render($screen,$data=array()) {
        $defaults=array('tela'=>$screen,'linkNovel'=>'menu','linkLogoff'=>'sair','abrirModalHistoria'=>FALSE,'abrirModalGabarito'=>FALSE,'conquista'=>0,'erro'=>FALSE,'inseriu'=>TRUE);
        $this->load->view('construtor',array_merge($defaults,$data));
    }
    protected function notice($message,$type='error') { $this->session->set_flashdata('notification',array('message'=>$message,'type'=>$type)); }
}
class Jogo_Controller extends MY_Controller {
    protected $mode;
    public function __construct() { parent::__construct(); $this->load->model('ModelRodada','rounds'); }
    public function index() {
        $player=$this->player(); $groups=$this->rounds->catalogue($this->mode);
        $groups=array_unique($groups); $cards=array(); $counts=array();
        if($this->mode!=='texto') foreach($this->db->select('codGrafema,COUNT(*) AS n',FALSE)->group_by('codGrafema')->get($this->mode==='palavra'?'Palavra':'Teste')->result() as $c) $counts[$c->codGrafema]=(int)$c->n;
        foreach($groups as $group) {
            try { $g=$this->rounds->groups($group); $key=implode('&',array_map(function($r){return $r->tipoGrafema;},$g));
                $missing=$this->mode==='palavra'?array():$this->rounds->missing($player,$key);
                $available=TRUE;foreach($g as $item) if($this->mode!=='texto'&&(!isset($counts[$item->codGrafema])||$counts[$item->codGrafema]<($this->mode==='palavra'?5:2))) $available=FALSE;
                $completed=$this->db->where(array('codJogador'=>$player,'modo'=>$this->mode,'habilidade'=>$key))->where('resultado IS NOT NULL',NULL,FALSE)->get('Tentativa')->num_rows();
                $finished=$this->db->where(array('codJogador'=>$player,'modo'=>$this->mode,'habilidade'=>$key))->get('Dominio')->num_rows();
                if($this->mode==='palavra' && in_array($key,$this->rounds->masteredWords($player),TRUE)) $finished=TRUE;
                $pending=$this->db->where(array('codJogador'=>$player,'modo'=>$this->mode,'habilidade'=>$key,'resultado'=>NULL))->where('inicio >',time()-86400)->order_by('inicio','DESC')->get('Tentativa')->row();
                $cards[]=array('group'=>$key,'missing'=>$missing,'finished'=>$finished,'pending'=>$pending,'available'=>$available,'completed'=>$completed,'questions'=>$this->mode==='palavra'?5:($this->mode==='teste'?count($g)*2:NULL));
            } catch(InvalidArgumentException $e) {log_message('error','Grupo de conteúdo inválido.');}
        }
        $this->render('missoes',array('mode'=>$this->mode,'cards'=>$cards));
    }
    protected function start() {
        $player=$this->player();
        try { $data=$this->rounds->prepare($player,$this->mode,rawurldecode($this->uri->segment(3,'')),$this->input->get('revisar')); $this->render('jogar-'.$this->mode,$data); }
        catch (InvalidArgumentException $e) {$this->notice($e->getMessage(),'warning');redirect($this->mode);}
        catch (Throwable $e) {log_message('error','Falha ao preparar rodada: '.$e->getMessage());$this->notice('Não foi possível iniciar a missão. Tente novamente.');redirect($this->mode);}
    }
    public function continuar($token='') {
        try { $data=$this->rounds->resume($token,$this->player(),$this->mode); $this->render('jogar-'.$this->mode,$data); }
        catch(InvalidArgumentException $e) {$this->notice($e->getMessage());redirect($this->mode);}
    }
    protected function finish() {
        $this->postOnly();$player=$this->player();$token=$this->input->post('roundToken');
        try {
            if($this->input->post('action')==='saveExit') {$this->rounds->saveDraft($player,$this->mode,$token,$this->input->post());$this->notice('Treino salvo. Você pode continuar pelas mesmas questões.','success');redirect($this->mode);return;}
            $this->rounds->complete($player,$this->mode,$token,$this->input->post());
            $p=$this->db->where('codJogador',$player)->get('Jogador')->row(); $this->session->set_userdata(array('nivel'=>$p->nivel,'experiencia'=>$p->experiencia));
            redirect($this->mode.'/resultado/'.$token);
        } catch (InvalidArgumentException $e) {$this->notice($e->getMessage());$attempt=$this->rounds->getAttempt($token,$player);if($attempt&&$attempt->modo===$this->mode&&$attempt->resultado===NULL) {try{$this->rounds->saveDraft($player,$this->mode,$token,$this->input->post());}catch(Throwable $ignored){}}redirect($attempt&&$attempt->modo===$this->mode?$this->mode.'/continuar/'.$token:$this->mode);}
        catch (Throwable $e) {log_message('error','Falha ao concluir rodada: '.$e->getMessage());$this->notice('Sua rodada não foi concluída. Tente novamente.');redirect($this->mode);}
    }
    public function resultado($token='') {
        $a=$this->rounds->getAttempt($token,$this->player());
        if (!$a || !$a->resultado || $a->modo!==$this->mode) {show_404();return;}
        $this->render('resultado',array('result'=>json_decode($a->resultado,TRUE)));
    }
}
