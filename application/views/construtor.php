<?php
if ($tela == 'index') {
		$this->load->view($tela);
	} else {
		$this->load->view('header');
		if ($tela != 'login' && $tela != 'recuperar-senha' && $tela != 'login-administrador' && $tela != 'cadastrar-jogador' && $tela != 'redefinir-senha'){
		 	$this->load->view('navbar');
	     	$this->load->view($tela);
		} else {			
	    	$this->load->view($tela);
		}	
	$this->load->view('footer');
}
?>