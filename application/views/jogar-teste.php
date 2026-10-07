<section id="conteudo" class="novel-activity" data-elapsed="<?php echo max(0,time()-(int)$startedAt); ?>">
    <header class="novel-activity-heading"><p class="novel-eyebrow">Teste de ortografia</p><h1>Escolha a resposta</h1><p>Leia cada questão e marque uma alternativa.</p></header>
    <form id="form" method="post" action="<?php echo base_url('teste/inserirRodadaTeste'); ?>">
<input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">		
								<input type="hidden" name="roundToken" value="<?php echo html_escape($roundToken); ?>">
        <input type="hidden" id="duracao" name="duracao" value="0">
        <div class="novel-activity-toolbar"><span class="novel-question-progress" aria-live="polite"></span><label class="novel-clock">Tempo <input id="tempo" value="00:00:00" disabled aria-label="Tempo de atividade"></label></div>
        <div id="carrosselTestes" class="carousel novel-exercise-carousel" data-interval="false" data-wrap="false"><div class="carousel-inner">
            <?php foreach ($testes as $i => $t): $alt = $alternativas[$i]; shuffle($alt); ?>
            <div class="item<?php echo $i === 0 ? ' active' : ''; ?>">
                <fieldset class="novel-alternatives"><legend class="novel-exercise-prompt"><?php echo nl2br(html_escape(str_replace('|', "\n", $t->enunciado))); ?></legend>
                <?php foreach ($alt as $k => $a): ?><label class="novel-alternative"><input type="radio" name="inputJogador<?php echo $i; ?>" value="<?php echo html_escape($a->alternativa); ?>"<?php echo isset($draft['inputJogador'.$i])&&$draft['inputJogador'.$i]===$a->alternativa?' checked':''; ?> required><span><?php echo html_escape($a->alternativa); ?></span></label><?php endforeach; ?>
                </fieldset>
							</div>							
            <?php endforeach; ?>
            <div class="item novel-submit-step"><h2>Vamos conferir seu desempenho?</h2><p>Revise as questões ou envie suas respostas para concluir.</p><button type="submit" class="btn btn-success">Concluir teste</button></div>
        </div><div class="novel-exercise-navigation"><button type="button" class="btn btn-default" data-target="#carrosselTestes" data-slide="prev">← Anterior</button><button type="button" class="btn btn-primary" data-target="#carrosselTestes" data-slide="next">Próxima →</button></div></div>
	</form>
    <div class="novel-activity-actions"><button type="submit" form="form" name="action" value="saveExit" class="btn btn-default" formnovalidate>Salvar e sair</button><button type="button" class="btn btn-link" onclick="Novel.confirmExit(<?php echo html_escape(json_encode(base_url('teste'))); ?>)">Sair da atividade</button></div>
</section>
