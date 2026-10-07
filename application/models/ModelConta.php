<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class ModelConta extends CI_Model {
    public function allow($scope,$identity,$max=10) {
        $key=hash('sha256',$scope.'|'.$identity);$window=intdiv(time(),900);
        $this->db->query('INSERT INTO LimiteAcesso(chave,janela,quantidade) VALUES(?,?,1) ON DUPLICATE KEY UPDATE quantidade=quantidade+1',array($key,$window));
        $row=$this->db->where(array('chave'=>$key,'janela'=>$window))->get('LimiteAcesso')->row();
        return $row && $row->quantidade<=$max;
    }
    public function validateProfile($data,$id=NULL) {
        foreach(array('nome','email','login') as $field) if(!isset($data[$field])||!is_string($data[$field])) throw new InvalidArgumentException('Preencha nome, e-mail e login.');
        $clean=array('nome'=>trim($data['nome']),'email'=>strtolower(trim($data['email'])),'login'=>trim($data['login']));
        if(strlen($clean['nome'])<2 || strlen($clean['nome'])>150 || preg_match('/[\x00-\x1f]/',$clean['nome'])) throw new InvalidArgumentException('Informe um nome válido, com até 50 caracteres.');
        if(preg_match_all('/./us',$clean['nome'])>50) throw new InvalidArgumentException('O nome deve ter até 50 caracteres.');
        if(strlen($clean['email'])>254 || !filter_var($clean['email'],FILTER_VALIDATE_EMAIL)) throw new InvalidArgumentException('Informe um e-mail válido.');
        if(!preg_match('/^[a-zA-Z0-9_.-]{3,20}$/D',$clean['login'])) throw new InvalidArgumentException('O login deve ter 3 a 20 letras, números, pontos, traços ou sublinhados.');
        foreach(array('email','login') as $field) {
            $this->db->where($field,$clean[$field]); if($id) $this->db->where('codJogador !=',$id);
            if($this->db->get('Jogador')->num_rows()) throw new InvalidArgumentException('Este '.$field.' já está cadastrado.');
        }
        return $clean;
    }
    public function password($a,$b) {
        if(!is_string($a)||!is_string($b)||$a!==$b||strlen($a)<8||strlen($a)>72) throw new InvalidArgumentException('As senhas devem coincidir e ter entre 8 e 72 caracteres.');
        return password_hash($a,PASSWORD_BCRYPT);
    }
    public function register($data) {
        $clean=$this->validateProfile($data);
        $clean['senha']=$this->password(isset($data['senha1'])?$data['senha1']:NULL,isset($data['senha2'])?$data['senha2']:NULL);
        if(!isset($data['avatar'])||!is_string($data['avatar'])||!in_array($data['avatar'],array('graciliano','cecilia','verissimo','clarice'),TRUE)) throw new InvalidArgumentException('Escolha um dos personagens disponíveis.');
        $clean['avatar']=$data['avatar'];
        $this->db->trans_begin();
        if(!$this->db->insert('Jogador',$clean)) {$this->db->trans_rollback();throw new InvalidArgumentException('Não foi possível cadastrar. Confira login e e-mail.');}
        $id=$this->db->insert_id();
        if(!$this->db->insert('ConquistaJogador',array('codJogador'=>$id,'codConquista'=>1))) {$this->db->trans_rollback();throw new RuntimeException('Falha ao cadastrar.');}
        $this->db->trans_commit(); return $id;
    }
    public function resetRequest($email) {
        if(!is_string($email)||!filter_var($email,FILTER_VALIDATE_EMAIL)) return;
        if(!$this->allow('reset-ip',$this->input->ip_address(),5)||!$this->allow('reset-email',strtolower($email),3)) return;
        $p=$this->db->where('email',strtolower(trim($email)))->get('Jogador')->row();
        if(!$p) return;
        if(!getenv('SMTP_HOST') || !getenv('SMTP_FROM')) {log_message('error','Recuperação indisponível: configure SMTP_HOST e SMTP_FROM.');return;}
        $token=bin2hex(random_bytes(32));$hash=hash('sha256',$token);
        if(!$this->db->insert('Recuperacao',array('tokenHash'=>$hash,'codJogador'=>$p->codJogador,'expira'=>time()+1800))) return;
        $this->load->library('email');
        $this->email->from(getenv('SMTP_FROM'),'Novel'); $this->email->to($p->email);$this->email->subject('Redefinir senha do Novel');
        $this->email->message("Abra o link abaixo para escolher uma nova senha. Ele expira em 30 minutos e pode ser usado uma vez.\n\n".base_url('redefinir-senha/'.$token)."\n\nSe você não pediu esta alteração, ignore a mensagem. Sua senha atual continua válida.");
        try {$sent=$this->email->send();} catch(Throwable $error) {$sent=FALSE;}
        if(!$sent) { $this->db->where('tokenHash',$hash)->delete('Recuperacao'); log_message('error','Falha no envio da recuperação de senha.'); }
    }
    public function reset($token,$a,$b) {
        if(!is_string($token)||!preg_match('/^[a-f0-9]{64}$/D',$token)) throw new InvalidArgumentException('Link inválido ou expirado.');
        $password=$this->password($a,$b);$this->db->trans_begin();
        try {
            $r=$this->db->query('SELECT * FROM Recuperacao WHERE tokenHash=? FOR UPDATE',array(hash('sha256',$token)))->row();
            if(!$r||$r->usado||(int)$r->expira<time()) throw new InvalidArgumentException('Link inválido ou expirado. Solicite outro.');
            if(!$this->db->where('codJogador',$r->codJogador)->update('Jogador',array('senha'=>$password))||!$this->db->where('tokenHash',$r->tokenHash)->update('Recuperacao',array('usado'=>1))||!$this->db->trans_status()) throw new RuntimeException('Falha ao redefinir a senha.');
            $this->db->trans_commit();
        } catch(Throwable $e) {$this->db->trans_rollback();throw $e;}
    }
}
