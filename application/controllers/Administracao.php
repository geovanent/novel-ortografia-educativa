<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Administracao extends MY_Controller {
    public function __construct() {parent::__construct();$this->load->model('modelAdministracao');$this->load->model('ModelConta','accounts');}
    private function admin() {if(!$this->session->userdata('codAdministrador')) {redirect('administracao');exit;}}
    public function index() {if($this->session->userdata('codAdministrador')) redirect('menu-administrador');else $this->render('login-administrador');}
    public function fazerLogin() {
        $this->postOnly();$name=$this->input->post('login');$password=$this->input->post('senha');
        $allowed=$this->accounts->allow('admin-login',$this->input->ip_address(),15);
        $account=is_string($name)&&is_string($password)&&strlen($name)<=20&&strlen($password)<=72&&$allowed?$this->modelAdministracao->fazerLogin($name,$password):FALSE;
        if(!$account) {$this->render('login-administrador',array('erro'=>TRUE));return;}
        $this->session->unset_userdata(array('codJogador','logged_in','avatar','nivel','experiencia'));$this->session->sess_regenerate(TRUE);$this->session->set_userdata($account);redirect('menu-administrador');
    }
    public function logoff() {$this->session->sess_destroy();redirect('administracao');}
    public function menuAdministrador() {
        $this->admin();$this->render('admin-lista',array('words'=>$this->db->order_by('codPalavra')->get('Palavra')->result(),'texts'=>$this->db->order_by('codTexto')->get('Texto')->result()));
    }
    private function editor($mode,$id=NULL) {
        $this->admin();if($id!==NULL&&(!is_scalar($id)||!ctype_digit((string)$id)||(int)$id<1)) {show_error('Identificador inválido.',400);return;}$table=$mode==='palavra'?'Palavra':'Texto';$pk=$mode==='palavra'?'codPalavra':'codTexto';
        $item=$id?$this->db->where($pk,(int)$id)->get($table)->row():NULL;
        if($id&&!$item) {show_404();return;}
        $selected=array();$answers='';
        if($mode==='texto'&&$id) {
            foreach($this->db->where('codTexto',$id)->get('GrafemaTexto')->result() as $g) $selected[]=$g->codGrafema;
            $a=$this->db->where('codTexto',$id)->order_by('posicao')->get('Gabarito')->result();$answers=implode("\n",array_map(function($r){return $r->letraGabarito;},$a));
        }
        $this->render('admin-editor',array('mode'=>$mode,'item'=>$item,'id'=>$id,'groups'=>$this->db->order_by('codGrafema')->get('Grafema')->result(),'selected'=>$selected,'answers'=>$answers));
    }
    public function cadastrarPalavra() {$this->editor('palavra');}
    public function editarPalavra($id=NULL) {$id=$id?:$this->input->get('id');if(!$id) {redirect('menu-administrador');return;}$this->editor('palavra',$id);}
    public function cadastrarTexto() {$this->editor('texto');}
    public function editarTexto($id=NULL) {$id=$id?:$this->input->get('id');if(!$id) {redirect('menu-administrador');return;}$this->editor('texto',$id);}
    private function field($name,$max,$required=TRUE) {
        $value=$this->input->post($name);
        if(!is_string($value)||strlen($value)>$max*4||preg_match_all('/./us',$value)>$max||($required&&trim($value)==='')) throw new InvalidArgumentException('Confira o campo '.$name.' (máximo '.$max.' caracteres).');
        return trim($value);
    }
    public function salvar($mode='') {
        $this->postOnly();$this->admin();if(!in_array($mode,array('palavra','texto'),TRUE)) {show_404();return;}
        $id=$this->input->post('id');$table=$mode==='palavra'?'Palavra':'Texto';$pk=$mode==='palavra'?'codPalavra':'codTexto';
        if(!is_string($id)||($id!==''&&!ctype_digit($id))) {show_error('Identificador inválido.',400);return;}
        $existing=$id?$this->db->where($pk,(int)$id)->get($table)->row():NULL;
        if($id&&!$existing) {show_404();return;}
        $upload=NULL;
        try {
            $data=array();$groups=array();$answers=array();
            if($mode==='palavra') {
                foreach(array('enunciado'=>600,'palavraIncompleta'=>25,'palavraCompleta'=>25,'letraGabarito'=>5,'justificativa'=>200) as $field=>$max) $data[$field]=$this->field($field,$max);
                $g=$this->input->post('codGrafema');
                if(!is_string($g)||!ctype_digit($g)) throw new InvalidArgumentException('Escolha uma habilidade válida.');
                $grafema=$this->db->where('codGrafema',(int)$g)->get('Grafema')->row();
                if(!$grafema||!in_array(strtolower($data['letraGabarito']),explode('_',$grafema->tipoGrafema),TRUE)) throw new InvalidArgumentException('O grafema correto deve pertencer à habilidade selecionada.');
                if(substr_count($data['palavraIncompleta'],'_')!==1||str_replace('_',$data['letraGabarito'],$data['palavraIncompleta'])!==$data['palavraCompleta']) throw new InvalidArgumentException('A palavra completa deve corresponder à lacuna preenchida com o gabarito, incluindo maiúsculas.');
                $data['codGrafema']=(int)$g;
            } else {
                $data['textoIncompleto']=$this->field('textoIncompleto',15000);
                $a=$this->field('respostas',5000);$answers=preg_split('/\R/u',$a);$answers=array_map('trim',$answers);
                if(count($answers)!==substr_count($data['textoIncompleto'],'_')) throw new InvalidArgumentException('Informe uma resposta por lacuna, na ordem do texto.');
                foreach($answers as $answer) if(!preg_match('/^[a-zç]{1,2}$/iuD',$answer)) throw new InvalidArgumentException('Cada resposta deve ter uma ou duas letras.');
                $groups=$this->input->post('grafemas');
                if(!is_array($groups)||!$groups||count($groups)>8) throw new InvalidArgumentException('Escolha as habilidades do texto.');
                $valid=array_map(function($g){return (string)$g->codGrafema;},$this->db->get('Grafema')->result());
                foreach($groups as $g) if(!is_string($g)||!in_array($g,$valid,TRUE)) throw new InvalidArgumentException('Habilidade inválida.');
                $groups=array_unique($groups);
            }
            $data['imagem']=$existing?$existing->imagem:'';
            if(isset($_FILES['imagem']) && $_FILES['imagem']['error']!==UPLOAD_ERR_NO_FILE) {
                $dir=FCPATH.'assets/img/uploads/';if(!is_dir($dir)&&!mkdir($dir,0775,TRUE)) throw new RuntimeException('Diretório de imagens indisponível.');
                $this->load->library('upload');$this->upload->initialize(array('upload_path'=>$dir,'allowed_types'=>'jpg|jpeg|png|webp','max_size'=>2048,'max_width'=>4096,'max_height'=>4096,'encrypt_name'=>TRUE,'detect_mime'=>TRUE));
                if(!$this->upload->do_upload('imagem')) throw new InvalidArgumentException('Imagem inválida. Use JPG, PNG ou WebP de até 2 MB e 4096 pixels.');
                $file=$this->upload->data();$upload=$file['full_path'];
                if(!getimagesize($upload)) throw new InvalidArgumentException('Arquivo de imagem inválido.');
                $data['imagem']='uploads/'.$file['file_name'];
                if(strlen($data['imagem'])>50) throw new InvalidArgumentException('Nome de imagem inválido.');
            }
            $this->db->trans_begin();
            $ok=$id?$this->db->where($pk,(int)$id)->update($table,$data):$this->db->insert($table,$data);$id=$id? (int)$id:$this->db->insert_id();
            if(!$ok) throw new RuntimeException('Falha ao salvar conteúdo.');
            if($mode==='texto') {
                if(!$this->db->where('codTexto',$id)->delete('Gabarito')||!$this->db->where('codTexto',$id)->delete('GrafemaTexto')) throw new RuntimeException('Falha ao salvar relações.');
                foreach($answers as $i=>$answer) if(!$this->db->insert('Gabarito',array('codTexto'=>$id,'posicao'=>$i+1,'letraGabarito'=>$answer))) throw new RuntimeException('Falha ao salvar respostas.');
                foreach($groups as $g) if(!$this->db->insert('GrafemaTexto',array('codTexto'=>$id,'codGrafema'=>$g))) throw new RuntimeException('Falha ao salvar habilidades.');
            }
            if(!$this->db->trans_status()) throw new RuntimeException('Falha ao salvar conteúdo.');$this->db->trans_commit();$this->notice('Conteúdo salvo.','success');
        } catch(Throwable $e) { $this->db->trans_rollback();if($upload&&is_file($upload)) unlink($upload);$this->notice($e instanceof InvalidArgumentException?$e->getMessage():'Não foi possível salvar o conteúdo.'); }
        redirect('menu-administrador');
    }
}
