<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class modelJogador extends CI_Model {
    public function fazerLogin($login,$senha) {
        if(!is_string($login)||!is_string($senha)||strlen($senha)>72) return FALSE;
        $p=$this->db->where('login',$login)->get('Jogador')->row();if(!$p) return FALSE;
        $ok=password_verify($senha,$p->senha);
        // Migração de contas anteriores: só comparar texto puro se não for um hash moderno.
        $legacy=password_get_info($p->senha)['algoName']==='unknown';
        if(!$ok&&$legacy) $ok=preg_match('/^[a-f0-9]{32}$/iD',$p->senha)?hash_equals(strtolower($p->senha),md5($senha)):hash_equals($p->senha,$senha);
        if(!$ok) return FALSE;
        if($legacy||password_needs_rehash($p->senha,PASSWORD_BCRYPT)) {
            if(!$this->db->where('codJogador',$p->codJogador)->update('Jogador',array('senha'=>password_hash($senha,PASSWORD_BCRYPT)))) return FALSE;
        }
        return array('nome'=>$p->nome,'avatar'=>novel_avatar($p->avatar),'codJogador'=>$p->codJogador,'nivel'=>$p->nivel,'experiencia'=>$p->experiencia);
    }
    public function buscarDadosJogador($id) {return $this->db->select('nome,email,login,avatar,codJogador')->where('codJogador',$id)->get('Jogador')->result();}
}
