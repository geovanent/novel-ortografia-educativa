<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH.'libraries/RoundScorer.php';
class ModelRodada extends CI_Model {
    private $grafemas;
    public function groups($group) {
        $all=$this->grafemas===NULL?($this->grafemas=$this->db->order_by('codGrafema')->get('Grafema')->result()):$this->grafemas;
        if (in_array($group,array('total','_total','-total'),TRUE)) return $all;
        if (preg_match('/^[0-9]+(?:_[0-9]+)+$/D',$group)) {
            $wantedIds=explode('_',$group); $names=array();
            foreach($all as $g) if(in_array((string)$g->codGrafema,$wantedIds,TRUE)) $names[]=$g->tipoGrafema;
            if(count($names)!==count(array_unique($wantedIds))) throw new InvalidArgumentException('Habilidade desconhecida.');
            $group=implode('&',$names);
        }
        $wanted=explode('&',$group); $result=array();
        foreach ($all as $g) if (in_array($g->tipoGrafema,$wanted,TRUE)) $result[]=$g;
        if (count($result)!==count(array_unique($wanted)) || !$result) throw new InvalidArgumentException('Habilidade desconhecida.');
        return $result;
    }
    public function catalogue($mode) {
        $keys=array();
        if($mode==='palavra') foreach($this->groups('total') as $g) $keys[]=$g->tipoGrafema;
        elseif($mode==='teste') foreach($this->db->get('GrupoTeste')->result() as $g) $keys[]=implode('&',array_map(function($r){return $r->tipoGrafema;},$this->groups($g->grafemas)));
        else {
            $links=$this->db->select('gt.codTexto,g.tipoGrafema')->from('GrafemaTexto gt')->join('Grafema g','g.codGrafema=gt.codGrafema')->order_by('gt.codTexto')->order_by('g.codGrafema')->get()->result();$map=array();
            foreach($links as $l) $map[$l->codTexto][]=$l->tipoGrafema;
            foreach($map as $types) $keys[]=implode('&',array_unique($types));
        }
        return array_values(array_unique($keys));
    }
    private function curriculumComplete($player) {
        $rows=$this->db->where('codJogador',$player)->get('Dominio')->result();$mastery=array();
        foreach($rows as $r) $mastery[$r->modo][$r->habilidade]=TRUE;
        foreach($this->masteredWords($player) as $key) $mastery['palavra'][$key]=TRUE;
        foreach(array('palavra','texto','teste') as $mode) foreach($this->catalogue($mode) as $key) if(empty($mastery[$mode][$key])) return FALSE;
        return TRUE;
    }
    public function masteredWords($player) {
        $new=$this->db->where(array('codJogador'=>$player,'modo'=>'palavra'))->get('Dominio')->result();
        $types=array_map(function($r){ return $r->habilidade; },$new);
        // Histórico antigo mantém os desbloqueios conquistados, sem inventar taxa de acerto.
        $old=$this->db->select('g.tipoGrafema')->distinct()->from('Rodada r')->join('RodadaGrafema rg','rg.codRodada=r.codRodada')->join('Grafema g','g.codGrafema=rg.codGrafema')->where(array('r.codJogador'=>$player,'r.tipoRodada'=>'palavra'))->where('r.pontuacao >=',30)->get()->result();
        foreach ($old as $g) $types[]=$g->tipoGrafema;
        return array_unique($types);
    }
    public function missing($player,$group) {
        $known=$this->masteredWords($player); $missing=array();
        foreach ($this->groups($group) as $g) if (!in_array($g->tipoGrafema,$known,TRUE)) $missing[]=$g->tipoGrafema;
        return $missing;
    }
    public function prepare($player,$mode,$group,$review=NULL) {
        $groups=$this->groups($group);
        $ids=array_map(function($g){return (int)$g->codGrafema;},$groups);
        $key=implode('&',array_map(function($g){return $g->tipoGrafema;},$groups));
        if ($mode!=='palavra' && $this->missing($player,$key)) throw new InvalidArgumentException('Conclua com pelo menos 60% o treino de Palavra: '.str_replace('_',' / ',implode(', ',$this->missing($player,$key))).'.');
        $questions=array(); $reviewIds=array();$reviewText=NULL; $view=array('grafema'=>$key,'grafemas'=>$key,'codGrafema'=>$ids,'regra'=>$groups);
        // Revisão conserva somente questões erradas da tentativa pertencente ao jogador.
        if ($review) {
            $old=$this->getAttempt($review,$player);
            if (!$old || !$old->resultado || $old->modo!==$mode || $old->habilidade!==$key) throw new InvalidArgumentException('Revisão indisponível.');
            $snapshot=json_decode($old->snapshot,TRUE); $result=json_decode($old->resultado,TRUE);
            $indices=array(); foreach ($result['rows'] as $i=>$row) if (!$row['correct']) $indices[]=$i;
            if (!$indices) throw new InvalidArgumentException('Você acertou todas as questões desta rodada.');
            if($mode==='texto') $reviewText=(int)$snapshot['view']['texto'][0]['codTexto'];
            else foreach($indices as $i) $reviewIds[]=(int)$snapshot['questions'][$i]['id'];
        }
        if (!$questions && $mode==='palavra') {
            if (count($groups)!==1) throw new InvalidArgumentException('Selecione uma habilidade para treinar palavras.');
            $this->db->where('codGrafema',$ids[0]);if($reviewIds) $this->db->where_in('codPalavra',$reviewIds);
            $rows=$this->db->get('Palavra')->result();
            if($reviewIds&&count($rows)!==count($reviewIds)) throw new InvalidArgumentException('Questão alterada ou removida. Inicie uma missão completa.');
            if (!$reviewIds && count($rows)<5) throw new InvalidArgumentException('Esta missão precisa de pelo menos cinco palavras cadastradas.');
            shuffle($rows); if(!$reviewIds) $rows=array_slice($rows,0,5); $view['palavras']=$rows;
            foreach ($rows as $p) $questions[]=array('id'=>$p->codPalavra,'grafema'=>$ids[0],'question'=>$p->palavraIncompleta.' — '.$p->enunciado,'answer'=>$p->letraGabarito,'explanation'=>$p->palavraCompleta.': '.$p->justificativa,'content'=>(array)$p);
        } elseif (!$questions && $mode==='teste') {
            $this->db->where_in('codGrafema',$ids);if($reviewIds) $this->db->where_in('codTeste',$reviewIds);$rows=$this->db->get('Teste')->result();
            $selected=array();
            if($reviewIds) {if(count($rows)!==count($reviewIds)) throw new InvalidArgumentException('Questão alterada ou removida. Inicie uma missão completa.');$selected=$rows;}
            else foreach ($ids as $id) { $items=array_values(array_filter($rows,function($t) use($id){return (int)$t->codGrafema===$id;})); if (count($items)<2) throw new InvalidArgumentException('Esta missão precisa de duas questões por habilidade.'); shuffle($items); $selected=array_merge($selected,array_slice($items,0,2)); }
            $alternatives=$this->db->where_in('codTeste',array_map(function($t){return $t->codTeste;},$selected))->get('Alternativas')->result();
            $view['testes']=$selected; $view['alternativas']=array();
            foreach ($selected as $t) {
                $alt=array_values(array_filter($alternatives,function($a) use($t){return $a->codTeste==$t->codTeste;})); shuffle($alt);
                $values=array_map(function($a){$a->alternativa=trim($a->alternativa);return $a->alternativa;},$alt);
                $expected=NULL;foreach($values as $value) if(strcasecmp(trim($t->gabarito),$value)===0) {$expected=$value;break;}
                if (count($values)<2 || $expected===NULL) throw new InvalidArgumentException('Questão sem alternativas válidas; informe a administração.');
                $view['alternativas'][]=$alt;
                $questions[]=array('id'=>$t->codTeste,'grafema'=>(int)$t->codGrafema,'question'=>$t->enunciado,'answer'=>$expected,'alternatives'=>$values,'content'=>(array)$t);
            }
        } elseif (!$questions && $mode==='texto') {
            $relations=$this->db->order_by('codGrafema')->get('GrafemaTexto')->result(); $map=array();
            foreach ($relations as $r) $map[$r->codTexto][]=(int)$r->codGrafema;
            $eligible=array(); foreach($map as $id=>$list) if(array_values(array_unique($list))===$ids) $eligible[]=$id;
            if (!$eligible) throw new InvalidArgumentException('Nenhum texto disponível para esta combinação.');
            if($reviewText&&!in_array($reviewText,array_map('intval',$eligible),TRUE)) throw new InvalidArgumentException('Texto alterado ou removido. Inicie uma nova leitura.');
            shuffle($eligible); $t=$this->db->where('codTexto',$reviewText?:$eligible[0])->get('Texto')->row();
            $answers=$this->db->where('codTexto',$t->codTexto)->order_by('posicao')->order_by('codGabarito')->get('Gabarito')->result();
            if (!$answers || count($answers)!==substr_count($t->textoIncompleto,'_')) throw new InvalidArgumentException('Texto com lacunas inconsistentes; informe a administração.');
            $view['texto']=array($t);
            // O banco legado não relaciona lacunas individuais a grafemas: usar 0 (misto), sem atribuição falsa.
            $chunks=explode('_',$t->textoIncompleto);
            foreach($answers as $i=>$a) {
                preg_match('/([^\s]+)$/u',$chunks[$i],$left);preg_match('/^([^\s.,;!?]*)/u',$chunks[$i+1],$right);
                $word=(isset($left[1])?$left[1]:'').'['.$a->letraGabarito.']'.(isset($right[1])?$right[1]:'');
                $questions[]=array('id'=>$a->codGabarito,'grafema'=>count($ids)===1?$ids[0]:0,'question'=>'Lacuna '.($i+1).' — '.$word,'answer'=>$a->letraGabarito,'explanation'=>'Na leitura, a palavra se escreve '.str_replace(array('[',']'),'', $word).'.');
            }
        }
        if (!$questions) throw new InvalidArgumentException('Missão indisponível.');
        $token=bin2hex(random_bytes(32));
        $snapshot=array('questions'=>$questions,'grafemas'=>$ids,'points'=>array('palavra'=>10,'texto'=>20,'teste'=>30)[$mode],'view'=>$view,'eligibleForMastery'=>!$review || $mode==='texto');
        if (!$this->db->insert('Tentativa',array('token'=>$token,'codJogador'=>$player,'modo'=>$mode,'habilidade'=>$key,'snapshot'=>json_encode($snapshot,JSON_UNESCAPED_UNICODE),'inicio'=>time()))) throw new RuntimeException('Não foi possível preparar a rodada.');
        $view['roundToken']=$token; $view['startedAt']=time(); $view['regra']=$groups; return $view;
    }
    public function saveDraft($player,$mode,$token,$post) {
        $a=$this->getAttempt($token,$player);
        if(!$a||$a->modo!==$mode||$a->resultado!==NULL||time()-(int)$a->inicio>86400) throw new InvalidArgumentException('Treino indisponível ou expirado.');
        $s=json_decode($a->snapshot,TRUE);$prefix=$mode==='teste'?'inputJogador':'inputLetra';$draft=array();
        foreach($s['questions'] as $i=>$q) {
            $name=$prefix.$i;$value=isset($post[$name])?$post[$name]:'';
            if(!is_string($value)||strlen($value)>1000) throw new InvalidArgumentException('Resposta inválida.');
            $value=trim($value);
            if($value!==''&&isset($q['alternatives'])&&!in_array($value,$q['alternatives'],TRUE)) throw new InvalidArgumentException('Alternativa inválida.');
            $draft[$name]=$value;
        }
        if(!$this->db->where(array('token'=>$token,'codJogador'=>$player,'resultado'=>NULL))->update('Tentativa',array('rascunho'=>json_encode($draft,JSON_UNESCAPED_UNICODE)))) throw new RuntimeException('Não foi possível salvar o treino.');
    }
    public function resume($token,$player,$mode) {
        $a=$this->getAttempt($token,$player);
        if(!$a||$a->modo!==$mode||$a->resultado!==NULL||time()-(int)$a->inicio>86400) throw new InvalidArgumentException('Treino indisponível ou expirado.');
        $snapshot=json_decode($a->snapshot); $view=(array)$snapshot->view;
        if(isset($view['palavras'])) $view['palavras']=(array)$view['palavras'];
        if(isset($view['testes'])) $view['testes']=(array)$view['testes'];
        if(isset($view['texto'])) $view['texto']=(array)$view['texto'];
        if(isset($view['regra'])) $view['regra']=(array)$view['regra'];
        $view['roundToken']=$token; $view['startedAt']=(int)$a->inicio; $view['draft']=$a->rascunho?json_decode($a->rascunho,TRUE):array();
        return $view;
    }
    public function getAttempt($token,$player) {
        if (!is_string($token) || !preg_match('/^[a-f0-9]{64}$/D',$token)) return NULL;
        return $this->db->where(array('token'=>$token,'codJogador'=>$player))->get('Tentativa')->row();
    }
    public function grantAchievements($player,$experience) {
        $names=array();$earned=$this->db->where('experienciaDesbloqueio <=',$experience)->get('Conquista')->result();
        foreach($earned as $c) if(!$this->db->where(array('codJogador'=>$player,'codConquista'=>$c->codConquista))->get('ConquistaJogador')->num_rows()) {
            $this->must($this->db->insert('ConquistaJogador',array('codJogador'=>$player,'codConquista'=>$c->codConquista)));$names[]=$c->nomeConquista;
        }
        return $names;
    }
    private function must($ok) { if (!$ok) throw new RuntimeException('Não foi possível salvar a rodada; tente novamente.'); }
    public function complete($player,$mode,$token,$post) {
        if (!is_string($token)||!preg_match('/^[a-f0-9]{64}$/D',$token)) throw new InvalidArgumentException('Rodada inválida.');
        $this->db->trans_begin();
        try {
            // Bloqueio do jogador serializa recompensas, inclusive rodadas diferentes simultâneas.
            $p=$this->db->query('SELECT * FROM Jogador WHERE codJogador=? FOR UPDATE',array($player))->row();
            $a=$this->db->query('SELECT * FROM Tentativa WHERE token=? AND codJogador=? FOR UPDATE',array($token,$player))->row();
            if (!$a || $a->modo!==$mode || !$p) throw new InvalidArgumentException('Rodada inválida para este jogador.');
            if ($a->resultado!==NULL) { $this->db->trans_commit(); return json_decode($a->resultado,TRUE); }
            if(time()-(int)$a->inicio>86400) throw new InvalidArgumentException('A rodada expirou. Inicie um novo treino.');
            $s=json_decode($a->snapshot,TRUE); $answers=array(); $prefix=$mode==='teste'?'inputJogador':'inputLetra';
            foreach ($s['questions'] as $i=>$q) $answers[]=isset($post[$prefix.$i])?$post[$prefix.$i]:NULL;
            $result=RoundScorer::evaluate($s['questions'],$answers,$s['points']);
            $result['review']=empty($s['eligibleForMastery']);
            $result['mastered']=$result['mastered'] && !$result['review'];
            $legacyBefore=$mode==='palavra' && in_array($a->habilidade,$this->masteredWords($player),TRUE);
            $result['duration']=RoundScorer::elapsed($a->inicio,time()); $result['mode']=$mode; $result['group']=$a->habilidade; $result['token']=$token;
            $this->must($this->db->insert('Rodada',array('codJogador'=>$player,'tipoRodada'=>$mode,'pontuacao'=>$result['points'],'duracao'=>$result['duration']))); $round=$this->db->insert_id();
            foreach($s['grafemas'] as $id) $this->must($this->db->insert('RodadaGrafema',array('codGrafema'=>$id,'codRodada'=>$round)));
            foreach($result['rows'] as $i=>$r) $this->must($this->db->insert('Resposta',array('codRodada'=>$round,'posicao'=>$i,'codGrafema'=>$s['questions'][$i]['grafema'],'questao'=>$r['question'],'resposta'=>$r['answer'],'correta'=>$r['expected'],'acertou'=>(int)$r['correct'])));
            $domain=$this->db->where(array('codJogador'=>$player,'modo'=>$mode,'habilidade'=>$a->habilidade))->get('Dominio')->num_rows();
            // Não conceder novamente níveis já conquistados antes desta migração.
            $legacy=FALSE;
            if ($mode==='palavra') $legacy=$legacyBefore;
            else {
                $old=$this->db->select('r.codRodada,r.pontuacao')->from('Rodada r')->where(array('r.codJogador'=>$player,'r.tipoRodada'=>$mode))->where('r.codRodada !=',$round)->get()->result();
                foreach($old as $r) {
                    $links=$this->db->select('codGrafema')->where('codRodada',$r->codRodada)->order_by('codGrafema')->get('RodadaGrafema')->result();
                    $oldIds=array_map(function($l){return (int)$l->codGrafema;},$links);
                    if($oldIds===$s['grafemas'] && $r->pontuacao>=($mode==='texto'?140:count($s['grafemas'])*36)) {$legacy=TRUE;break;}
                }
            }
            $result['levelUp']=$result['mastered'] && !$domain && !$legacy && (int)$p->nivel<24;
            if($result['mastered'] && !$domain) $this->must($this->db->insert('Dominio',array('codJogador'=>$player,'modo'=>$mode,'habilidade'=>$a->habilidade)));
            $nextLevel=(int)$p->nivel+($result['levelUp']?1:0);
            // O catálogo legado repetia “total”. A conclusão de todas as habilidades únicas
            // libera o nível final da narrativa, sem repetir recompensas pelo mesmo grafema.
            if($result['mastered'] && $this->curriculumComplete($player)) $nextLevel=max($nextLevel,24);
            $result['levelUp']=$nextLevel>(int)$p->nivel; $result['level']=$nextLevel;
            $this->must($this->db->where('codJogador',$player)->update('Jogador',array('experiencia'=>(int)$p->experiencia+$result['points'],'tempoTotal'=>(int)$p->tempoTotal+$result['duration'],'nivel'=>$nextLevel)));
            $result['achievements']=$this->grantAchievements($player,(int)$p->experiencia+$result['points']);
            $this->must($this->db->where('token',$token)->update('Tentativa',array('fim'=>time(),'codRodada'=>$round,'resultado'=>json_encode($result,JSON_UNESCAPED_UNICODE))));
            $this->must($this->db->trans_status()); $this->must($this->db->trans_commit()); return $result;
        } catch (Throwable $e) { $this->db->trans_rollback(); throw $e; }
    }
}
