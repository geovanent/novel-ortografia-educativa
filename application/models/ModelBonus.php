<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class ModelBonus extends CI_Model {
    public function available($player) {
        return $this->db->query('SELECT b.* FROM Bonus b JOIN Jogador j ON j.codJogador=? WHERE b.experienciaNecessaria<=j.experiencia AND NOT EXISTS(SELECT 1 FROM JogadorBonus jb WHERE jb.codJogador=j.codJogador AND jb.codBonus=b.codBonus) ORDER BY b.codBonus LIMIT 1',array($player))->row();
    }
    public function prepare($player) {
        $b=$this->available($player);if(!$b) return NULL;
        $words=$this->db->where('codBonus',$b->codBonus)->get('PalavraBonus')->result_array();
        $token=bin2hex(random_bytes(32));$s=array('bonus'=>(array)$b,'words'=>$words);
        if(!$this->db->insert('Tentativa',array('token'=>$token,'codJogador'=>$player,'modo'=>'bonus','habilidade'=>(string)$b->codBonus,'snapshot'=>json_encode($s,JSON_UNESCAPED_UNICODE),'inicio'=>time()))) throw new RuntimeException('Falha ao preparar bônus.');
        return array('bonusData'=>$b,'bonusWords'=>$words,'roundToken'=>$token);
    }
    public function complete($player,$token,$positions) {
        if(!is_string($token)||!preg_match('/^[a-f0-9]{64}$/D',$token)||!is_array($positions)||count($positions)>225) throw new InvalidArgumentException('Bônus inválido.');
        foreach($positions as $p) if(!is_string($p)||!ctype_digit($p)||(int)$p>224) throw new InvalidArgumentException('Seleção inválida.');
        $this->db->trans_begin();
        try {
            $j=$this->db->query('SELECT * FROM Jogador WHERE codJogador=? FOR UPDATE',array($player))->row();
            $a=$this->db->query('SELECT * FROM Tentativa WHERE token=? AND codJogador=? FOR UPDATE',array($token,$player))->row();
            if(!$a||$a->modo!=='bonus') throw new InvalidArgumentException('Bônus inválido para este jogador.');
            if($a->resultado!==NULL) {$this->db->trans_commit();return json_decode($a->resultado,TRUE);}
            $s=json_decode($a->snapshot,TRUE);$id=$s['bonus']['codBonus'];$points=0;
            if(count(array_unique($positions))>count($s['words'])*2) throw new InvalidArgumentException('Marque somente a primeira e a última letra de cada palavra.');
            $claimed=$this->db->where(array('codJogador'=>$player,'codBonus'=>$id))->get('JogadorBonus')->num_rows();
            if(!$claimed&&time()-(int)$a->inicio<=60) {
                foreach($s['words'] as $w) if(in_array((string)$w['inicio'],$positions,TRUE)&&in_array((string)$w['fim'],$positions,TRUE)) $points+=50;
            }
            if(!$claimed) {
                if(!$this->db->insert('JogadorBonus',array('codJogador'=>$player,'codBonus'=>$id))||!$this->db->where('codJogador',$player)->update('Jogador',array('experiencia'=>(int)$j->experiencia+$points))) throw new RuntimeException('Falha ao salvar bônus.');
            }
            $this->load->model('ModelRodada','rounds');$achievements=$this->rounds->grantAchievements($player,(int)$j->experiencia+$points);
            $r=array('points'=>$points,'achievements'=>$achievements);
            if(!$this->db->where('token',$token)->update('Tentativa',array('fim'=>time(),'resultado'=>json_encode($r)))||!$this->db->trans_status()) throw new RuntimeException('Falha ao salvar bônus.');
            $this->db->trans_commit();return $r;
        } catch(Throwable $e) {$this->db->trans_rollback();throw $e;}
    }
}
