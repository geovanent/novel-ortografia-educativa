<!-- Aqui é a área do conteúdo -->
<div id="conteudo" class="novel-game-home">
    <section class="novel-mission-hero">
        <div>
            <p class="novel-eyebrow">Academia de Novéis</p>
            <h1 class="titulo-menu">Treine português em missões de ortografia</h1>
            <p class="novel-lead">Escolha uma trilha, leia a história e avance pelas fases para dominar grafemas, textos e testes.</p>
        </div>
        <img src="<?php echo novel_image_url('assets/img/graciliano.png'); ?>" alt="Mestre do Novel">
    </section>

    <section class="novel-recommendation"><h2>Seu próximo passo</h2>
<?php if($pending): ?><p>Há um treino em andamento.</p><a class="btn btn-primary" href="<?php echo base_url($pending->modo.'/continuar/'.$pending->token); ?>">Continuar treino</a>
<?php elseif($nextSkill): ?><p>Pratique <?php echo html_escape(str_replace('_',' / ',$nextSkill)); ?> para desbloquear novas missões.</p><a class="btn btn-primary" href="<?php echo base_url('palavra/jogarPalavra/'.rawurlencode($nextSkill)); ?>">Começar treino recomendado</a>
<?php else: ?><p>Você já dominou as habilidades de Palavra. Aplique o que aprendeu em textos e testes.</p><a class="btn btn-primary" href="<?php echo base_url('texto'); ?>">Praticar leitura</a><?php endif; ?>
<?php if($availableBonus): ?><a class="btn btn-default" href="<?php echo base_url('bonus/0'); ?>">Jogar bônus desbloqueado</a><?php endif; ?>
<a class="btn btn-link" href="<?php echo base_url('historia'); ?>">Ler ou rever minha história</a></section>
    <section class="novel-map">
        <a class="novel-map-stage novel-map-stage-primary" href="<?php echo base_url('palavra'); ?>">
            <span class="novel-stage-number">1</span>
            <img src="<?php echo novel_image_url('assets/img/palavra.png'); ?>" alt="Nível Palavra">
            <strong>Palavra</strong>
            <small>Complete lacunas e descubra o grafema certo.</small>
		</a>

        <div class="novel-map-path" aria-hidden="true"></div>

        <a class="novel-map-stage novel-map-stage-secondary" href="<?php echo base_url('texto'); ?>">
            <span class="novel-stage-number">2</span>
            <img src="<?php echo novel_image_url('assets/img/texto.png'); ?>" alt="Nível Texto">
            <strong>Texto</strong>
            <small>Use as regras dentro de frases e narrativas.</small>
		</a>

        <div class="novel-map-path" aria-hidden="true"></div>

        <a class="novel-map-stage novel-map-stage-final" href="<?php echo base_url('teste'); ?>">
            <span class="novel-stage-number">3</span>
            <img src="<?php echo novel_image_url('assets/img/teste.png'); ?>" alt="Nível Teste">
            <strong>Teste</strong>
            <small>Mostre sua evolução e some pontos.</small>
        </a>
    </section>
			</div>
