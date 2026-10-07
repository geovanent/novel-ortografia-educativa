<?php
require __DIR__.'/../application/libraries/RoundScorer.php';
function check($ok,$label) { if (!$ok) throw new RuntimeException($label); echo "OK $label\n"; }
$q=array_fill(0,5,array('answer'=>'ss','question'=>'pa_o'));
$r=RoundScorer::evaluate($q,array_fill(0,5,'x'),10); check($r['points']===0 && !$r['mastered'],'zero preservado');
$r=RoundScorer::evaluate($q,array('ss','SS','ss','x','x'),10); check($r['points']===30 && $r['mastered'],'corte de 60% e caixa');
check(RoundScorer::evaluate($q,array_fill(0,5,'ss'),10)['points']===50,'100%');
foreach (array(array('ss'),array('ss','ss','ss','ss',array('ss')),array('ss','ss','ss','ss','')) as $answers) {
 try { RoundScorer::evaluate($q,$answers,10); throw new RuntimeException('aceitou resposta inválida'); } catch (InvalidArgumentException $e) { echo "OK rejeitou resposta incompleta ou inválida\n"; }
}
try { RoundScorer::evaluate(array(array('answer'=>'a','question'=>'q','alternatives'=>array('a','b'))),array('forjada'),30); throw new RuntimeException('alternativa forjada'); } catch (InvalidArgumentException $e) { echo "OK alternativa forjada rejeitada\n"; }
check(RoundScorer::elapsed(86395,86405)===10,'meia-noite');

check(RoundScorer::evaluate(array(array('answer'=>'ç','question'=>'q')),array('Ç'),10)['correct']===1,'grafema acentuado em maiúscula');
