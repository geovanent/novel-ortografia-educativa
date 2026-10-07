<section class="novel-level-screen">
<header class="novel-level-hero"><p class="novel-eyebrow">Missões de ortografia</p><h1>Nível <?php echo ucfirst(html_escape($mode)); ?></h1><p>Domine cada missão com pelo menos 60% de acertos. Cada habilidade concede avanço de nível uma vez por modalidade.</p></header>
<div class="novel-challenge-grid">
<?php foreach($cards as $i=>$card): $group=$card['group']; $display=str_replace(array('&','_'),array(' · ',' / '),$group); $action=array('palavra'=>'jogarPalavra','texto'=>'jogarTexto','teste'=>'jogarTeste')[$mode]; $image=count(explode('&',$group))===8?'total':$group; ?>
<article class="novel-challenge-card<?php echo $card['missing']||!$card['available']?' novel-locked':''; ?>">
<span class="novel-stage-number"><?php echo $i+1; ?></span>
<img class="img-responsive aa" src="<?php echo novel_image_url('assets/img/grafemas/'.$mode.'/'.$image.'.png'); ?>" alt="" loading="lazy">
<h2><?php echo html_escape($display); ?></h2><p><?php echo $card['questions']?$card['questions'].' questões':'Leitura com lacunas'; ?></p>
<p><?php echo !$card['available']?'Indisponível':($card['missing']?'Bloqueada':($card['finished']?'Dominada':($card['pending']?'Em andamento':($card['completed']?'Concluída · a revisar':'Disponível')))); ?></p>
<?php if(!$card['available']): ?><p>Conteúdo insuficiente. A administração precisa completar esta missão.</p><?php elseif($card['missing']): ?><p>Treine Palavra primeiro:</p><?php foreach($card['missing'] as $missing): ?><a href="<?php echo base_url('palavra/jogarPalavra/'.rawurlencode($missing)); ?>"><?php echo html_escape(str_replace('_',' / ',$missing)); ?></a><?php endforeach; ?>
<?php else: ?><?php if($card['pending']): ?><a class="btn btn-default" href="<?php echo base_url($mode.'/continuar/'.$card['pending']->token); ?>">Continuar</a><?php endif; ?><a class="btn btn-primary" href="<?php echo base_url($mode.'/'.$action.'/'.rawurlencode($group)); ?>"><?php echo $card['finished']?'Praticar novamente':'Iniciar missão'; ?></a><?php endif; ?>
</article>
<?php endforeach; ?>
</div>
<?php if(!$cards): ?><p>Nenhuma missão disponível. Fale com a administração.</p><?php endif; ?>
</section>
