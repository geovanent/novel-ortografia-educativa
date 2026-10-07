<section id="conteudo" class="novel-activity" data-elapsed="<?php echo max(0,time()-(int)$startedAt); ?>">
    <header class="novel-activity-heading"><p class="novel-eyebrow">Treino de texto</p><h1>Complete a leitura</h1><p>Preencha as lacunas com os grafemas que completam cada palavra.</p></header>
    <form id="form" method="post" action="<?php echo base_url('texto/inserirRodadaTexto'); ?>">
<input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">	
				<input type="hidden" name="roundToken" value="<?php echo html_escape($roundToken); ?>">
        <input type="hidden" id="duracao" name="duracao" value="0">
        <div class="novel-activity-toolbar"><span>Leitura e ortografia</span><label class="novel-clock">Tempo <input id="tempo" value="00:00:00" disabled aria-label="Tempo de atividade"></label></div>
				<?php
        if ($grafemas === 'total' || $grafemas === '_total') { $grafemas = 'g_j&ch_x&s_z_x&c_ç_s_ss_sc_sç_xc&m_n&r_rr&e_i&o_u_l'; }
        $partes = explode('_', $texto[0]->textoIncompleto);
				?>	
        <p class="novel-exercise-rule">Grafemas: <strong><?php echo html_escape(str_replace(array('&', '_'), array(' · ', ' / '), $grafemas)); ?></strong></p>
        <div class="novel-reading">
            <?php foreach ($partes as $i => $parte): ?><?php echo nl2br(html_escape(str_replace('- ', "\n- ", $parte))); ?><?php if ($i < count($partes) - 1): ?><input type="text" id="inputLetra<?php echo $i; ?>" name="inputLetra<?php echo $i; ?>" value="<?php echo html_escape(isset($draft['inputLetra'.$i])?$draft['inputLetra'.$i]:''); ?>" class="input-texto" required maxlength="2" autocomplete="off" autocapitalize="none" spellcheck="false" aria-label="Grafema da lacuna <?php echo $i + 1; ?>"><?php endif; ?><?php endforeach; ?>
					</div>
        <button type="submit" class="btn btn-success">Enviar respostas</button>
	</form>
    <div class="novel-activity-actions"><button type="submit" form="form" name="action" value="saveExit" class="btn btn-default" formnovalidate>Salvar e sair</button><button type="button" class="btn btn-link" onclick="Novel.confirmExit(<?php echo html_escape(json_encode(base_url('texto'))); ?>)">Sair da atividade</button></div>
</section>
