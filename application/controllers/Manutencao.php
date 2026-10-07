<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Manutencao extends CI_Controller {
    public function __construct() {
        parent::__construct();
        if (!$this->input->is_cli_request()) { show_404(); exit; }
    }
    public function migrar() {
        foreach(glob(APPPATH.'migrations/[0-9][0-9][0-9]_*.sql') as $file) {
            $version=(int)substr(basename($file),0,3);
            if ($this->db->table_exists('NovelMigration') && $this->db->where('version',$version)->get('NovelMigration')->num_rows()) continue;
            $sql = preg_replace('/^--.*$/m','',file_get_contents($file));
            foreach (explode(';',$sql) as $statement) {
                if(preg_match('/^\s*ALTER TABLE (\w+) ADD COLUMN (\w+)/i',$statement,$match)&&$this->db->field_exists($match[2],$match[1])) continue;
                if (trim($statement) && !$this->db->query($statement)) { fwrite(STDERR,"Migração incompleta; execute novamente após corrigir o banco.\n"); exit(1); }
            }
        }
        // Nunca apagar dados duplicados para fazer a migração passar.
        $indexes = array('Jogador'=>array('login','email'),'Administrador'=>array('login','email'));
        foreach ($indexes as $table=>$fields) foreach ($fields as $field) {
            $duplicates = $this->db->query("SELECT COUNT(*) AS n FROM `$table` GROUP BY `$field` HAVING COUNT(*)>1")->num_rows();
            if ($duplicates) { fwrite(STDERR,"Duplicados em $table.$field: $duplicates grupos. Resolver as contas antes de criar unicidade.\n"); exit(1); }
            $name = 'novel_unique_'.$field;
            if (!$this->db->query("SHOW INDEX FROM `$table` WHERE Key_name=?",array($name))->num_rows()) {
                if (!$this->db->query("ALTER TABLE `$table` ADD UNIQUE `$name` (`$field`)")) { exit(1); }
            }
        }
        foreach (array('ConquistaJogador'=>array('codJogador','codConquista'),'JogadorBonus'=>array('codJogador','codBonus'),'RodadaGrafema'=>array('codRodada','codGrafema'),'Gabarito'=>array('codTexto','posicao')) as $table=>$fields) {
            $columns = implode(',',$fields);
            if ($this->db->query("SELECT COUNT(*) n FROM `$table` GROUP BY $columns HAVING COUNT(*)>1")->num_rows()) {
                fwrite(STDERR,"Relações duplicadas em $table; preservadas. Resolva antes de continuar.\n"); exit(1);
            }
            if (!$this->db->query("SHOW INDEX FROM `$table` WHERE Key_name='novel_unique_relation'")->num_rows()) {
                if (!$this->db->query("ALTER TABLE `$table` ADD UNIQUE novel_unique_relation ($columns)")) exit(1);
            }
        }
        echo "Migração e índices verificados.\n";
    }
    public function auditar() {
        $errors=array(); $alternatives=array();
        foreach($this->db->get('Bonus')->result() as $b) {
            if(strlen($b->textoBonus)!==225) {$errors[]='Bônus '.$b->codBonus.': grade diferente de 15 x 15.';continue;}
            foreach($this->db->where('codBonus',$b->codBonus)->get('PalavraBonus')->result() as $w) {
                $start=(int)$w->inicio;$end=(int)$w->fim;$length=strlen($w->palavra);
                $dr=($end>=0&&$end<225)?(intdiv($end,15)-intdiv($start,15)):0;
                $dc=$end%15-$start%15;$valid=$start>=0&&$start<225&&$end>=0&&$end<225&&$length>1;
                if($valid) $valid=($dr===0||abs($dr)===$length-1)&&($dc===0||abs($dc)===$length-1)&&($dr!==0||$dc!==0);
                $letters='';if($valid) for($i=0;$i<$length;$i++) $letters.=$b->textoBonus[$start+($dr<=>0)*15*$i+($dc<=>0)*$i];
                if(!$valid||$letters!==$w->palavra) $errors[]='Bônus '.$b->codBonus.': coordenadas incorretas de '.$w->palavra;
            }
        }
        foreach($this->db->get('Alternativas')->result() as $a) $alternatives[$a->codTeste][]=trim($a->alternativa);
        foreach($this->db->get('Teste')->result() as $t) {
            $values=isset($alternatives[$t->codTeste])?$alternatives[$t->codTeste]:array();$matches=FALSE;
            foreach($values as $v) if(strcasecmp($v,trim($t->gabarito))===0) $matches=TRUE;
            if(count($values)<2||!$matches) $errors[]='Teste '.$t->codTeste.': alternativas/gabarito inconsistentes.';
        }
        $answers=array();foreach($this->db->get('Gabarito')->result() as $a) $answers[$a->codTexto][]=$a;
        foreach($this->db->get('Texto')->result() as $t) if(!isset($answers[$t->codTexto])||count($answers[$t->codTexto])!==substr_count($t->textoIncompleto,'_')) $errors[]='Texto '.$t->codTexto.': lacunas inconsistentes.';
        $counts=array();foreach($this->db->get('Palavra')->result() as $p) {
            $counts[$p->codGrafema]=isset($counts[$p->codGrafema])?$counts[$p->codGrafema]+1:1;
            if(substr_count($p->palavraIncompleta,'_')!==1||strcasecmp(str_replace('_',$p->letraGabarito,$p->palavraIncompleta),$p->palavraCompleta)!==0) $errors[]='Palavra '.$p->codPalavra.': lacuna/gabarito inconsistentes.';
        }
        foreach($this->db->get('Grafema')->result() as $g) {
            if(empty($counts[$g->codGrafema])||$counts[$g->codGrafema]<5) $errors[]='Habilidade '.$g->tipoGrafema.': menos de cinco palavras.';
            if(!is_file(FCPATH.'assets/img/grafemas/palavra/'.$g->tipoGrafema.'.png')) $errors[]='Imagem de missão ausente: '.$g->tipoGrafema;
            for($i=1;$i<=$g->quadros;$i++) {
                $path='regras/'.$g->tipoGrafema.'-'.$i.'.png';
                if(!is_file(FCPATH.'assets/img/'.$path)||!novel_transcript($path)) $errors[]='Regra sem imagem/texto: '.$path;
            }
        }
        foreach($this->db->get('Cenas')->result() as $c) foreach(array('cecilia','clarice','graciliano','verissimo') as $avatar) for($i=1;$i<=$c->quadros;$i++) {
            $path='historia/'.$avatar.'/'.$c->nomeCena.'-'.$i.'.png';
            if(!is_file(FCPATH.'assets/img/'.$path)||!novel_transcript($path)) $errors[]='História sem imagem/texto: '.$path;
        }
        if($errors) {echo implode("\n",$errors)."\n";exit(1);}
        echo "Conteúdo auditado: questões, alternativas, lacunas, imagens de Palavra e textos acessíveis consistentes.\n";
    }
    public function palavrasTextos() {
        foreach($this->db->order_by('codTexto')->get('Texto')->result() as $t) {
            echo 'Texto '.$t->codTexto."\n";$chunks=explode('_',$t->textoIncompleto);$answers=$this->db->where('codTexto',$t->codTexto)->order_by('posicao')->get('Gabarito')->result();
            foreach($answers as $i=>$a) {
                preg_match('/([^\s]+)$/u',$chunks[$i],$left);preg_match('/^([^\s.,;!?]*)/u',$chunks[$i+1],$right);
                echo $a->codGabarito.' (posição '.$a->posicao.'): '.(isset($left[1])?$left[1]:'').'['.$a->letraGabarito.']'.(isset($right[1])?$right[1]:'')."\n";
            }
        }
    }
}
