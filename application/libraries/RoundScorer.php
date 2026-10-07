<?php
/** Regras puras, usadas por todos os modos e testadas sem banco. */
class RoundScorer {
    const MASTERY_PERCENT = 60;
    public static function evaluate(array $questions, array $answers, $points) {
        if (!$questions || count($questions) !== count($answers)) throw new InvalidArgumentException('Responda todas as questões.');
        $correct = 0; $rows = array();
        foreach ($questions as $i=>$q) {
            $answer = $answers[$i];
            if (!is_string($answer) || trim($answer) === '' || strlen($answer)>1000) throw new InvalidArgumentException('Resposta inválida na questão '.($i+1).'.');
            $answer = trim($answer);
            if (isset($q['alternatives']) && !in_array($answer,$q['alternatives'],TRUE)) throw new InvalidArgumentException('Escolha uma alternativa da questão '.($i+1).'.');
            $fold=function($value){return strtolower(strtr($value,array('Á'=>'á','À'=>'à','Â'=>'â','Ã'=>'ã','É'=>'é','Ê'=>'ê','Í'=>'í','Ó'=>'ó','Ô'=>'ô','Õ'=>'õ','Ú'=>'ú','Ç'=>'ç')));};
            $match = $fold($answer)===$fold(trim($q['answer']));
            $correct += $match ? 1 : 0;
            $rows[] = array('question'=>$q['question'],'answer'=>$answer,'expected'=>$q['answer'],'correct'=>$match,'explanation'=>isset($q['explanation'])?$q['explanation']:'');
        }
        return array('correct'=>$correct,'total'=>count($questions),'points'=>$correct*$points,'mastered'=> $correct*100 >= count($questions)*self::MASTERY_PERCENT,'rows'=>$rows);
    }
    public static function elapsed($start,$end) { return max(1,min(86400,(int)$end-(int)$start)); }
}
