<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Principal extends MY_Controller {

	public function __construct() {
        parent::__construct();
        $this->load->helper('array');
        $this->load->model('modelJogador');
        $this->load->model('ModelConta','accounts');
    }


	public function index()
	{		
		if ($this->session->userdata('logged_in')) {			
            //$this->logoff();
            redirect('menu');
        } else {        	
			$pagina = array('tela' => 'index', 'erro' => FALSE, 'abrirModalHistoria'=> FALSE, 'enviou' =>  TRUE, 'email'=>NULL, 'conquista' => 0);
			$this->load->view('construtor', $pagina);
		}
	}


	public function login()
	{

		if ($this->session->userdata('logged_in')) {
            redirect('menu');
        } else{ 			       			
			$pagina = array('tela' => 'index', 'erro'=> FALSE, 'abrirModalHistoria'=> FALSE, 'enviou' =>  TRUE, 'email'=>NULL, 'conquista' => 0,);
			$this->load->view('construtor', $pagina);
		}
	}

	public function fazerLogin() {
        $this->postOnly();
        $login=$this->input->post('login'); $password=$this->input->post('senha');
        $valid=is_string($login)&&is_string($password)&&strlen($login)<=20&&strlen($password)<=72;
        $allowed=$this->accounts->allow('login-ip',$this->input->ip_address(),30);
        $account=$valid&&$allowed?$this->modelJogador->fazerLogin(trim($login),$password):FALSE;
        if($account) {
            $this->session->unset_userdata(array('codAdministrador')); $this->session->sess_regenerate(TRUE);
            $account['logged_in']=TRUE; $account['avatar']=novel_avatar($account['avatar']); $this->session->set_userdata($account); redirect('menu');
        } else { $this->render('index',array('erro'=>TRUE,'enviou'=>TRUE,'email'=>NULL,'loginValue'=>is_string($login)?$login:'')); }

    }

	public function logoff() {

        $this->session->sess_destroy();
        redirect('login');
    }

	public function recuperarSenha() {
        $this->postOnly();
        $this->accounts->resetRequest($this->input->post('email'));
        $this->render('index',array('email'=>'solicitado','enviou'=>TRUE));

    }

    public function menu() {
        $id=$this->player();$this->load->model('ModelRodada','rounds');$this->load->model('ModelBonus','bonuses');
        $known=$this->rounds->masteredWords($id);$next=NULL;
        foreach($this->db->order_by('codGrafema')->get('Grafema')->result() as $g) if(!in_array($g->tipoGrafema,$known,TRUE)) {$next=$g->tipoGrafema;break;}
        $pending=$this->db->where('codJogador',$id)->where_in('modo',array('palavra','texto','teste'))->where('resultado',NULL)->where('inicio >',time()-86400)->order_by('inicio','DESC')->get('Tentativa')->row();
        $this->render('menu',array('nextSkill'=>$next,'pending'=>$pending,'availableBonus'=>$this->bonuses->available($id)));
    }

	public function sobre()
	{

		$pagina = array('tela' => 'sobre',				
				'erro' => FALSE,
				'abrirModalHistoria'=> FALSE,
				'linkNovel'=> 'menu', 
				'linkLogoff'=>'sair',
				'existe' => FALSE,
				'conquista' => 0,);
		$this->load->view('construtor', $pagina);
	}


	public function cadastrarJogador()
	{					
		if ($this->session->userdata('logged_in')) {
        	redirect('menu');    
        } else {
			$pagina = array('tela' => 'cadastrar-jogador', 'erro' => FALSE, 'existe' => FALSE, 'conquista' => 0,  'abrirModalHistoria'=> FALSE,);
			$this->load->view('construtor', $pagina);
		}
	}

	public function realizarCadastro() {
        $this->postOnly();
        try {
            if(!$this->accounts->allow('register-ip',$this->input->ip_address(),10)) throw new InvalidArgumentException('Muitas tentativas. Aguarde alguns minutos.');
            $data=$this->input->post(); $this->accounts->register($data);
            $account=$this->modelJogador->fazerLogin($data['login'],$data['senha1']);
            $this->session->sess_regenerate(TRUE); $account['logged_in']=TRUE; $this->session->set_userdata($account); redirect('menu');
        } catch(InvalidArgumentException $e) { $this->render('cadastrar-jogador',array('existe'=>FALSE,'formError'=>$e->getMessage(),'formValues'=>$this->input->post(array('nome','email','login','avatar')))); }
        catch(Throwable $e) {log_message('error','Falha ao cadastrar conta.');$this->render('cadastrar-jogador',array('existe'=>FALSE,'formError'=>'Não foi possível cadastrar. Tente novamente.'));}

    }

    public function meusPontos() {
        $id=$this->player();
        $skills=$this->db->query('SELECT g.tipoGrafema, COUNT(*) total, SUM(res.acertou) correct FROM Resposta res JOIN Rodada r ON r.codRodada=res.codRodada LEFT JOIN Grafema g ON g.codGrafema=res.codGrafema WHERE r.codJogador=? GROUP BY res.codGrafema,g.tipoGrafema',array($id))->result();
        $attempts=$this->db->where('codJogador',$id)->where_in('modo',array('palavra','texto','teste'))->where('resultado IS NOT NULL',NULL,FALSE)->order_by('fim','DESC')->limit(20)->get('Tentativa')->result();
        $earned=$this->db->select('c.*')->from('Conquista c')->join('ConquistaJogador cj','cj.codConquista=c.codConquista')->where('cj.codJogador',$id)->get()->result();
        $this->render('meus-pontos',array('skills'=>$skills,'attempts'=>$attempts,'earnedAchievements'=>$earned,'playerData'=>$this->db->where('codJogador',$id)->get('Jogador')->row()));
    }

	public function minhaConta(){
		if ($this->session->userdata('logged_in')){
			$codJogador = $this->session->userdata('codJogador');
			$jogador = $this->modelJogador->buscarDadosJogador($codJogador);

			$pagina = array('tela' => 'minha-conta', 
				'erro' => FALSE,
				'abrirModalHistoria'=> FALSE,
				'jogador' => $jogador,
				'linkNovel'=> 'menu', 
				'linkLogoff'=>'sair',
				'existe' => FALSE,
				'conquista' => 0,
				);
			
			$this->load->view('construtor', $pagina);
		} else {
			redirect('login');
		}
	}

	public function editarCadastro() {
        $this->postOnly(); $player=$this->player();
        try { $clean=$this->accounts->validateProfile($this->input->post(),$player);
            if(!$this->db->where('codJogador',$player)->update('Jogador',$clean)) throw new InvalidArgumentException('Não foi possível salvar. Confira login e e-mail.');
            $this->session->set_userdata('nome',$clean['nome']);$this->notice('Seus dados foram atualizados.','success');
        } catch(InvalidArgumentException $e) {$this->notice($e->getMessage());}
        redirect('minha-conta');

    }

    public function alterarSenha() {
        $this->postOnly(); $player=$this->player();
        try {
            if(!$this->accounts->allow('password-change',(string)$player,5)) throw new InvalidArgumentException('Muitas tentativas. Aguarde alguns minutos.');
            $current=$this->input->post('senhaAtual'); $p=$this->db->where('codJogador',$player)->get('Jogador')->row();
            if(!is_string($current)||strlen($current)>72||!$this->modelJogador->fazerLogin($p->login,$current)) throw new InvalidArgumentException('A senha atual não confere.');
            $hash=$this->accounts->password($this->input->post('senha1'),$this->input->post('senha2'));
            if(!$this->db->where('codJogador',$player)->update('Jogador',array('senha'=>$hash))) throw new RuntimeException('Falha ao salvar senha.');
            $this->session->sess_regenerate(TRUE);$this->notice('Senha atualizada.','success');
        } catch(InvalidArgumentException $e) {$this->notice($e->getMessage());}
        redirect('minha-conta');
    }
    public function redefinirSenha($token='') {
        $error='';
        if($this->input->method()==='post') {
            try { $this->accounts->reset($token,$this->input->post('senha1'),$this->input->post('senha2'));$this->notice('Senha redefinida. Entre com sua nova senha.','success');redirect('login'); }
            catch(InvalidArgumentException $e) {$error=$e->getMessage();}
        }
        $this->render('redefinir-senha',array('token'=>$token,'formError'=>$error));
    }
    public function bonus() {
        $id=$this->player();$this->load->model('ModelBonus','bonuses');
        $data=$this->bonuses->prepare($id);
        if(!$data) {$this->notice('Você já concluiu os bônus disponíveis.','info');redirect('menu');return;}
        $this->render('bonus',$data);
    }
    public function inserirBonus() {
        $this->postOnly();$id=$this->player();$this->load->model('ModelBonus','bonuses');
        try {
            $positions=$this->input->post('posicoes');$r=$this->bonuses->complete($id,$this->input->post('roundToken'),$positions===NULL?array():$positions);
            $p=$this->db->where('codJogador',$id)->get('Jogador')->row();$this->session->set_userdata('experiencia',$p->experiencia);$this->notice('Bônus concluído: '.$r['points'].' pontos.','success');
        } catch(InvalidArgumentException $e) {$this->notice($e->getMessage());}
        catch(Throwable $e) {log_message('error','Falha ao concluir bônus.');$this->notice('Não foi possível salvar o bônus.');}
        redirect('menu');
    }
}
