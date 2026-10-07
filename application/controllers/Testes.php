<?php
/** Testes de integração somente via CLI e banco explicitamente isolado. */
defined('BASEPATH') OR exit('No direct script access allowed');
class Testes extends CI_Controller {
    private function check($ok,$label) {if(!$ok) throw new RuntimeException($label);echo 'OK '.$label."\n";}
    public function index() {
        if(!$this->input->is_cli_request()||getenv('DB_NAME')!=='novel_audit_test') {show_404();return;}
        $this->load->model('ModelRodada','rounds');$this->load->model('ModelConta','accounts');
        $tag=bin2hex(random_bytes(4));
        $this->db->insert('Jogador',array('nome'=>'Teste isolado','login'=>'audit_'.$tag,'email'=>'audit_'.$tag.'@example.invalid','senha'=>password_hash('test-pass-123',PASSWORD_BCRYPT),'avatar'=>'graciliano'));$player=$this->db->insert_id();
        $this->db->insert('Jogador',array('nome'=>'Outro','login'=>'other_'.$tag,'email'=>'other_'.$tag.'@example.invalid','senha'=>password_hash('test-pass-123',PASSWORD_BCRYPT),'avatar'=>'clarice'));$other=$this->db->insert_id();
        try {
            $view=$this->rounds->prepare($player,'palavra','g_j');$token=$view['roundToken'];
            $this->check($this->rounds->getAttempt($token,$other)===NULL,'rodada pertence somente ao jogador');
            try {$this->rounds->complete($other,'palavra',$token,array());throw new RuntimeException('aceitou outro jogador');}catch(InvalidArgumentException $e){echo "OK envio de outro jogador rejeitado\n";}
            try {$this->rounds->complete($player,'palavra',$token,array('inputLetra0'=>'g'));throw new RuntimeException('aceitou incompleta');}catch(InvalidArgumentException $e){echo "OK servidor exige todas as respostas\n";}
            $before=$this->db->where('codJogador',$player)->get('Jogador')->row();
            $this->check($this->db->where('codJogador',$player)->get('Rodada')->num_rows()===0,'envios inválidos não escrevem histórico');
            $post=array('duracao'=>-500,'codGrafema'=>999);
            for($i=0;$i<5;$i++) {$post['inputLetra'.$i]='x';$post['gabarito'.$i]='x';}
            $r=$this->rounds->complete($player,'palavra',$token,$post);
            $this->check($r['points']===0&&$r['duration']>=1,'gabarito e duração adulterados são ignorados; zero gravado');
            $this->check($this->db->where('codJogador',$player)->get('Rodada')->num_rows()===1,'rodada de zero consta no histórico');
            $this->rounds->complete($player,'palavra',$token,$post);
            $this->check($this->db->where('codJogador',$player)->get('Rodada')->num_rows()===1,'reenvio idempotente');
            $v=$this->rounds->prepare($player,'palavra','g_j');$a=$this->rounds->getAttempt($v['roundToken'],$player);$s=json_decode($a->snapshot,TRUE);$post=array();
            foreach($s['questions'] as $i=>$q) $post['inputLetra'.$i]=$q['answer'];
            $r=$this->rounds->complete($player,'palavra',$v['roundToken'],$post);
            $this->check($r['points']===50&&$r['levelUp'],'100% concede domínio e um nível');
            $v=$this->rounds->prepare($player,'palavra','g_j');$s=json_decode($this->rounds->getAttempt($v['roundToken'],$player)->snapshot,TRUE);$post=array();foreach($s['questions'] as $i=>$q) $post['inputLetra'.$i]=$q['answer'];
            $r=$this->rounds->complete($player,'palavra',$v['roundToken'],$post);$p=$this->db->where('codJogador',$player)->get('Jogador')->row();
            $this->check(!$r['levelUp']&&(int)$p->nivel===2&&(int)$p->experiencia===100,'mesma habilidade não duplica avanço');
            $this->check(count($this->rounds->missing($player,'g_j&ch_x'))===1,'pré-requisito específico faltante');
            $this->check($this->rounds->resume($token,$other,'palavra')===NULL,'impossível');
        } catch(InvalidArgumentException $e) {
            // A última chamada deve rejeitar retomada por outro jogador.
            $this->check(strpos($e->getMessage(),'indisponível')!==FALSE,'retomada respeita proprietário');
        }
        // Falha forçada via trigger em banco de teste: nenhum efeito parcial pode permanecer.
        $v=$this->rounds->prepare($player,'palavra','ch_x');$s=json_decode($this->rounds->getAttempt($v['roundToken'],$player)->snapshot,TRUE);$post=array();foreach($s['questions'] as $i=>$q) $post['inputLetra'.$i]=$q['answer'];
        $before=$this->db->where('codJogador',$player)->get('Jogador')->row();$count=$this->db->where('codJogador',$player)->get('Rodada')->num_rows();
        $this->db->query("CREATE TRIGGER audit_fail BEFORE INSERT ON Resposta FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='audit simulated failure'");
        try {$this->rounds->complete($player,'palavra',$v['roundToken'],$post);throw new LogicException('aceitou falha de banco');}catch(RuntimeException $e){echo "OK falha de banco detectada\n";}finally{$this->db->query('DROP TRIGGER audit_fail');}
        $after=$this->db->where('codJogador',$player)->get('Jogador')->row();
        $this->check($before->experiencia===$after->experiencia&&$before->nivel===$after->nivel&&$count===$this->db->where('codJogador',$player)->get('Rodada')->num_rows(),'rollback preserva histórico e progresso');
        $this->check($this->rounds->getAttempt($v['roundToken'],$player)->resultado===NULL,'rodada pode ser reenviada após falha');
        $r=$this->rounds->complete($player,'palavra',$v['roundToken'],$post);$this->check($r['points']===50,'repetição após rollback funciona');
        $this->check(novel_duration(7261)==='02:01:01','formatador usa módulo 60');
        // Cadastro, unicidade, perfil, reset: sem alterar contas de uso.
        $data=array('nome'=>'Conta de teste','email'=>'long-address-'.str_repeat('a',40).$tag.'@example.invalid','login'=>'long_'.$tag,'senha1'=>'password-123','senha2'=>'password-123','avatar'=>'cecilia');
        $id=$this->accounts->register($data);$this->check((bool)$id,'e-mail acima de 30 caracteres aceito');
        try {$this->accounts->register($data);throw new LogicException('cadastro duplicado aceito');}catch(InvalidArgumentException $e){echo "OK login/e-mail duplicados rejeitados\n";}
        foreach(array(array('avatar'=>'forjado'),array('senha2'=>'diferente'),array('login'=>array('x'))) as $change) {
            $bad=array_merge($data,array('login'=>'new_'.$tag,'email'=>'new_'.$tag.'@example.invalid'),$change);
            try {$this->accounts->register($bad);throw new LogicException('cadastro inválido aceito');}catch(InvalidArgumentException $e){echo "OK cadastro inválido rejeitado\n";}
        }
        $this->db->where('chave',hash('sha256','reset-ip|'.$this->input->ip_address()))->delete('LimiteAcesso');
        $old=$this->db->where('codJogador',$id)->get('Jogador')->row()->senha;
        $this->accounts->resetRequest('absent-'.$tag.'@example.invalid');
        $this->accounts->resetRequest($data['email']);
        if(getenv('SMTP_HOST')) $this->check($this->db->where(array('codJogador'=>$id,'usado'=>0))->where('expira >',time())->get('Recuperacao')->num_rows()===1,'transporte local entrega instruções sem trocar senha');
        $this->check($old===$this->db->where('codJogador',$id)->get('Jogador')->row()->senha,'solicitação de recuperação não altera a senha');
        $reset=bin2hex(random_bytes(32));$this->db->insert('Recuperacao',array('tokenHash'=>hash('sha256',$reset),'codJogador'=>$id,'expira'=>time()+1800));
        $this->accounts->reset($reset,'new-password-123','new-password-123');
        $this->check(password_verify('new-password-123',$this->db->where('codJogador',$id)->get('Jogador')->row()->senha),'token válido redefine senha');
        try {$this->accounts->reset($reset,'again-password-123','again-password-123');throw new LogicException('token reutilizado');}catch(InvalidArgumentException $e){echo "OK token de uso único\n";}
        $reset=bin2hex(random_bytes(32));$this->db->insert('Recuperacao',array('tokenHash'=>hash('sha256',$reset),'codJogador'=>$id,'expira'=>time()-1));
        try {$this->accounts->reset($reset,'again-password-123','again-password-123');throw new LogicException('token expirado');}catch(InvalidArgumentException $e){echo "OK token expirado rejeitado\n";}
        $this->check(password_verify('new-password-123',$this->db->where('codJogador',$id)->get('Jogador')->row()->senha),'reset inválido preserva senha atual');
        // Conteúdo existente: validar todas as combinações e permitir todas no fixture apenas.
        foreach($this->db->get('Grafema')->result() as $g) {
            $this->rounds->prepare($player,'palavra',$g->tipoGrafema);
            $this->db->query('INSERT IGNORE INTO Dominio(codJogador,modo,habilidade) VALUES(?,?,?)',array($player,'palavra',$g->tipoGrafema));
        }
        $groups=array();foreach($this->db->get('GrupoTeste')->result() as $g) $groups[]=$g->grafemas;
        foreach($groups as $group) $this->rounds->prepare($player,'teste',$group);
        $this->check(TRUE,'todas as missões de Teste têm questões e alternativas válidas');
        $links=$this->db->select('gt.codTexto,g.tipoGrafema')->from('GrafemaTexto gt')->join('Grafema g','g.codGrafema=gt.codGrafema')->order_by('gt.codTexto')->order_by('g.codGrafema')->get()->result();$map=array();
        foreach($links as $l) $map[$l->codTexto][]=$l->tipoGrafema;
        foreach($map as $types) $this->rounds->prepare($player,'texto',implode('&',$types));
        $this->check(TRUE,'todas as combinações de Texto têm lacunas válidas');
        $v=$this->rounds->prepare($player,'palavra','m_n');
        $this->rounds->saveDraft($player,'palavra',$v['roundToken'],array('inputLetra0'=>'m'));
        $resumed=$this->rounds->resume($v['roundToken'],$player,'palavra');$this->check($resumed['draft']['inputLetra0']==='m','rascunho preserva respostas e questões');
        $snap=json_decode($this->rounds->getAttempt($v['roundToken'],$player)->snapshot,TRUE);$post=array();foreach($snap['questions'] as $i=>$q) $post['inputLetra'.$i]=$i===0?'x':$q['answer'];
        $this->rounds->complete($player,'palavra',$v['roundToken'],$post);$review=$this->rounds->prepare($player,'palavra','m_n',$v['roundToken']);$reviewSnap=json_decode($this->rounds->getAttempt($review['roundToken'],$player)->snapshot,TRUE);
        $this->check(count($reviewSnap['questions'])===1&&!$reviewSnap['eligibleForMastery']&&$reviewSnap['questions'][0]['id']===$snap['questions'][0]['id'],'revisão de Palavra repete somente o erro e não concede domínio por amostra');
        $v=$this->rounds->prepare($player,'texto','m_n&r_rr');$snap=json_decode($this->rounds->getAttempt($v['roundToken'],$player)->snapshot,TRUE);$post=array();foreach($snap['questions'] as $i=>$q) $post['inputLetra'.$i]='x';
        $this->rounds->complete($player,'texto',$v['roundToken'],$post);$review=$this->rounds->prepare($player,'texto','m_n&r_rr',$v['roundToken']);
        $this->check($review['texto'][0]->codTexto===$v['texto'][0]->codTexto,'revisão de Texto mantém a leitura completa original');
        // A conclusão de todo o currículo libera a cena final, sem repetir o grupo total.
        $total=implode('&',array_map(function($g){return $g->tipoGrafema;},$this->rounds->groups('total')));
        foreach(array('palavra','texto','teste') as $mode) foreach($this->rounds->catalogue($mode) as $key) if(!($mode==='teste'&&$key===$total)) $this->db->query('INSERT IGNORE INTO Dominio(codJogador,modo,habilidade) VALUES(?,?,?)',array($player,$mode,$key));
        $this->db->where('codJogador',$player)->update('Jogador',array('nivel'=>22));
        $v=$this->rounds->prepare($player,'teste','total');$snap=json_decode($this->rounds->getAttempt($v['roundToken'],$player)->snapshot,TRUE);$post=array();foreach($snap['questions'] as $i=>$q) $post['inputJogador'.$i]=$q['answer'];
        $r=$this->rounds->complete($player,'teste',$v['roundToken'],$post);
        $this->check($r['mastered']&&(int)$r['level']===24,'currículo completo libera o nível final da história');
        $this->check(count($r['achievements'])>1,'todas as conquistas elegíveis são concedidas no mesmo avanço');
        $this->db->where('codJogador',$player)->update('Jogador',array('nivel'=>24));
        $this->load->model('ModelBonus','bonus');$this->db->where('codJogador',$player)->update('Jogador',array('experiencia'=>4000));
        $v=$this->bonus->prepare($player);$positions=array();foreach($v['bonusWords'] as $w) {$positions[]=(string)$w['inicio'];$positions[]=(string)$w['fim'];}
        try {$this->bonus->complete($other,$v['roundToken'],$positions);throw new LogicException('bônus de outro jogador aceito');}catch(InvalidArgumentException $e){echo "OK bônus respeita proprietário\n";}
        try {$this->bonus->complete($player,$v['roundToken'],array_map('strval',range(0,224)));throw new LogicException('seleção completa aceita');}catch(InvalidArgumentException $e){echo "OK bônus rejeita seleção de toda a grade\n";}
        $r=$this->bonus->complete($player,$v['roundToken'],$positions);$this->check($r['points']===250,'coordenadas corretas das cinco palavras concedem bônus');
        $before=(int)$this->db->where('codJogador',$player)->get('Jogador')->row()->experiencia;$this->bonus->complete($player,$v['roundToken'],$positions);
        $this->check($before===(int)$this->db->where('codJogador',$player)->get('Jogador')->row()->experiencia,'bônus repetido não duplica experiência');
        echo 'Fixture visual: audit_'.$tag." / test-pass-123\n";
        // Dois processos concorrentes concluem a mesma rodada.
        $v=$this->rounds->prepare($player,'palavra','g_j');$token=$v['roundToken'];$before=(int)$this->db->where('codJogador',$player)->get('Jogador')->row()->experiencia;
        $workers=array();
        for($i=0;$i<2;$i++) {
            $pipes=array();$process=proc_open(array(PHP_BINARY,FCPATH.'index.php','testes','worker',(string)$player,$token),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes,FCPATH);
            fclose($pipes[0]);$workers[]=array($process,$pipes);
        }
        foreach($workers as $worker) { $output=stream_get_contents($worker[1][1]);$error=stream_get_contents($worker[1][2]);fclose($worker[1][1]);fclose($worker[1][2]);$code=proc_close($worker[0]);$this->check($code===0&&strpos($output,'worker OK')!==FALSE,'processo concorrente finaliza sem erro'); }
        $after=(int)$this->db->where('codJogador',$player)->get('Jogador')->row()->experiencia;
        $this->check($after-$before===50,'concorrência concede recompensa uma vez');
        echo "Integração concluída. Fixtures preservadas apenas no banco isolado.\n";
    }
    public function worker($player='',$token='') {
        if(!$this->input->is_cli_request()||getenv('DB_NAME')!=='novel_audit_test') {show_404();return;}
        $this->load->model('ModelRodada','rounds');$a=$this->rounds->getAttempt($token,(int)$player);if(!$a) exit(1);
        $s=json_decode($a->snapshot,TRUE);$post=array();foreach($s['questions'] as $i=>$q) $post['inputLetra'.$i]=$q['answer'];
        $this->rounds->complete((int)$player,'palavra',$token,$post);echo "worker OK\n";
    }
    public function mailFailure() {
        if(!$this->input->is_cli_request()||getenv('DB_NAME')!=='novel_audit_test'||getenv('SMTP_HOST')!=='127.0.0.1'||getenv('SMTP_PORT')!=='1') {show_404();return;}
        $this->load->model('ModelConta','accounts');$tag=bin2hex(random_bytes(4));$email='fail_'.$tag.'@example.invalid';$hash=password_hash('password-test-123',PASSWORD_BCRYPT);
        $this->db->insert('Jogador',array('nome'=>'Falha de envio','login'=>'fail_'.$tag,'email'=>$email,'senha'=>$hash,'avatar'=>'cecilia'));$id=$this->db->insert_id();
        $this->db->where('chave',hash('sha256','reset-ip|'.$this->input->ip_address()))->delete('LimiteAcesso');
        $this->accounts->resetRequest($email);
        $this->check($hash===$this->db->where('codJogador',$id)->get('Jogador')->row()->senha,'falha SMTP preserva a senha atual');
        $this->check($this->db->where('codJogador',$id)->get('Recuperacao')->num_rows()===0,'falha SMTP invalida o token não entregue');
    }
}
