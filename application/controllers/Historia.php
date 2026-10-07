<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Historia extends MY_Controller {
    public function index() {
        $id=$this->player();$p=$this->db->where('codJogador',$id)->get('Jogador')->row();
        $scenes=$this->db->query('SELECT c.*, cl.nomeCena AS lida FROM Cenas c LEFT JOIN CenaLida cl ON cl.nomeCena=c.nomeCena AND cl.codJogador=? WHERE c.nivelDesbloqueio<=? ORDER BY c.nivelDesbloqueio,c.codCena',array($id,$p->nivel))->result();
        $this->render('historia-lista',array('scenes'=>$scenes));
    }
    public function ler($name='') {
        $id=$this->player();$p=$this->db->where('codJogador',$id)->get('Jogador')->row();
        $scene=$this->db->where('nomeCena',$name)->where('nivelDesbloqueio <=',$p->nivel)->get('Cenas')->row();
        if(!$scene) {show_404();return;}
        $this->render('historia-leitura',array('scene'=>$scene,'avatar'=>novel_avatar($p->avatar)));
    }
    public function concluir($name='') {
        $this->postOnly();$id=$this->player();$p=$this->db->where('codJogador',$id)->get('Jogador')->row();
        if(!$this->db->where('nomeCena',$name)->where('nivelDesbloqueio <=',$p->nivel)->get('Cenas')->num_rows()) {show_404();return;}
        $this->db->query('INSERT IGNORE INTO CenaLida(codJogador,nomeCena) VALUES(?,?)',array($id,$name));
        $this->notice('Leitura registrada. Você pode rever esta cena quando quiser.','success');redirect('historia');
    }
}
