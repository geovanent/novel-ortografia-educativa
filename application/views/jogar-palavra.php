<section id="conteudo" class="novel-activity" data-elapsed="<?php echo max(0,time()-(int)$startedAt); ?>">
    <header class="novel-activity-heading"><p class="novel-eyebrow">Treino de palavra</p><h1>Complete a palavra</h1><p>Leia a dica e escolha o grafema que falta.</p></header>
    <form id="form" method="post" action="<?php echo base_url('palavra/inserirRodadaPalavra'); ?>">
<input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">		
        <input type="hidden" name="roundToken" value="<?php echo html_escape($roundToken); ?>">
        <input type="hidden" id="duracao" name="duracao" value="0">
        <div class="novel-activity-toolbar"><span class="novel-question-progress" aria-live="polite"></span><label class="novel-clock">Tempo <input id="tempo" value="00:00:00" disabled aria-label="Tempo de atividade"></label></div>
        <div id="carrosselPalavras" class="carousel novel-exercise-carousel" data-interval="false" data-wrap="false">
            <div class="carousel-inner">
                <?php foreach ($palavras as $i => $p): $partes = explode('_', $p->palavraIncompleta, 2); ?>
                <div class="item<?php echo $i === 0 ? ' active' : ''; ?>">
                    <p class="novel-exercise-rule">Complete com <strong><?php echo html_escape(str_replace('_', ' / ', $grafema)); ?></strong></p>
                    <p class="novel-exercise-prompt"><?php echo html_escape($p->enunciado); ?></p>
                    <?php if (!empty($p->imagem) && novel_image_url($p->imagem) !== novel_asset_url('assets/img/illustration-unavailable.svg')): ?>
                    <img class="novel-word-image" src="<?php echo novel_image_url($p->imagem); ?>" alt="Ilustração da palavra">
                    <?php endif; ?>
                    <div class="novel-word-answer"><span><?php echo html_escape($partes[0]); ?></span><input type="text" id="inputLetra<?php echo $i; ?>" name="inputLetra<?php echo $i; ?>" value="<?php echo html_escape(isset($draft['inputLetra'.$i])?$draft['inputLetra'.$i]:''); ?>" class="input-palavra" required maxlength="2" autocomplete="off" autocapitalize="none" spellcheck="false" aria-label="Grafema que falta na palavra <?php echo html_escape(str_replace('_', ' lacuna ', $p->palavraIncompleta)); ?>"><span><?php echo html_escape(isset($partes[1]) ? $partes[1] : ''); ?></span></div>
				</div>
                <?php endforeach; ?>
                <div class="item novel-submit-step"><h2>Pronto para conferir?</h2><p>Você pode voltar às questões antes de enviar.</p><button type="submit" class="btn btn-success">Enviar respostas</button></div>
							</div>							
            <div class="novel-exercise-navigation"><button type="button" class="btn btn-default" data-target="#carrosselPalavras" data-slide="prev">← Anterior</button><button type="button" class="btn btn-primary" data-target="#carrosselPalavras" data-slide="next">Próxima →</button></div>
									</div>
    </form>
    <div class="novel-activity-actions"><button type="submit" form="form" name="action" value="saveExit" class="btn btn-default" formnovalidate>Salvar e sair</button><button type="button" class="btn btn-default" data-toggle="modal" data-target="#modalRegra">Consultar a regra</button><button type="button" class="btn btn-link" onclick="Novel.confirmExit(<?php echo html_escape(json_encode(base_url('palavra'))); ?>)">Sair da atividade</button></div>
</section>
<div class="modal fade" id="modalRegra" tabindex="-1" role="dialog" aria-labelledby="regraTitulo">
    <div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Fechar regra">×</button><h2 class="modal-title" id="regraTitulo">Ensinamentos do mestre</h2></div><div class="modal-body">
        <div id="carrosselRegra" class="carousel slide novel-rule-carousel" data-interval="false" data-wrap="false"><div class="carousel-inner">
            <?php for ($l = 1; $l <= (int) $regra[0]->quadros; $l++): ?>
            <div class="item<?php echo $l === 1 ? ' active' : ''; ?>"><img class="img-responsive" src="<?php echo novel_image_url('assets/img/regras/'.$grafema.'-'.$l.'.png'); ?>" alt="Ilustração da regra, quadro <?php echo $l; ?>. Texto abaixo."><div class="novel-transcript"><?php echo html_escape(novel_transcript('regras/'.$grafema.'-'.$l.'.png')); ?></div></div>
            <?php endfor; ?>
        </div><div class="novel-exercise-navigation"><button type="button" class="btn btn-default" data-target="#carrosselRegra" data-slide="prev">← Anterior</button><button type="button" class="btn btn-primary" data-target="#carrosselRegra" data-slide="next">Próximo →</button></div></div>
    </div><div class="modal-footer"><button type="button" class="btn btn-primary" data-dismiss="modal">Voltar ao treino</button></div></div></div>
								</div>
