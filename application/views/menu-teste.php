	<!-- Aqui é a área do conteúdo -->
    <div id="conteudo" class="novel-level-screen">
        <section class="novel-level-hero">
            <p class="novel-eyebrow">Missões de prova</p>
            <h3 class="titulo-menu">Escolha um teste</h3>
            <p class="novel-level-copy">Mostre domínio das regras, some experiência e desbloqueie novas conquistas.</p>
        </section>
        <div id="imagens-menu" class="novel-challenge-grid" >
				
				<?php
				$tamGrafemasBD = count($grafemasCadastrados);
				$tamGrafemasJogados = count($grafemasJogados);
				
				if(in_array("g_j&ch_x&s_z_x&c_ç_s_ss_sc_sç_xc&m_n&r_rr&e_i&o_u_l", $grafemasJogados)){
					$cod = array_search("g_j&ch_x&s_z_x&c_ç_s_ss_sc_sç_xc&m_n&r_rr&e_i&o_u_l", $grafemasJogados);
					$grafemasJogados[$cod] = "total";
				}

				if(in_array("g_j&ch_x&s_z_x&c_ç_s_ss_sc_sç_xc&m_n&r_rr&e_i&o_u_l", $grafemasJogados)){
					$cod = array_search("g_j&ch_x&s_z_x&c_ç_s_ss_sc_sç_xc&m_n&r_rr&e_i&o_u_l", $grafemasJogados);
					$grafemasJogados[$cod] = "-total";
				}

                for($i = 0; $i<$tamGrafemasBD; $i++) {
                    $grafema = $grafemasCadastrados[$i];
                    $concluido = in_array($grafema, $grafemasJogados);
                    $url = 'teste/jogarTeste/'.rawurlencode($grafema);
                    $imagem = 'assets/img/grafemas/teste/'.$grafema.'.png';
                    $rotulo = strtoupper(str_replace(array('&', '_'), array(' + ', ' / '), $grafema));

                    echo '<a class="novel-challenge-card" href="'.base_url($url).'">';
                        echo '<span class="novel-stage-number">'.($i + 1).'</span>';
                        if ($concluido) {
                            echo '<span class="novel-complete-badge"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span> Concluída</span>';
					}					
                        echo '<img class="img img-responsive" src="'.novel_image_url($imagem).'" alt="Teste '.$rotulo.'">';
                        echo '<strong>Teste '.($i + 1).'</strong>';
                        echo '<small>'.$rotulo.'</small>';
								echo '</a>';
								}								
			?>
        </div>

		<input type="hidden" id="abrirModalGabarito" value="<?php echo ($abrirModalGabarito); ?>">		
		<input type="hidden" id="abrirModalHistoria" value="<?php echo (isset($abrirModalHistoria[0]) ? $abrirModalHistoria[0] : ''); ?>">		

<!-- Modal -->
  	<div class="modal fade" id="modalGabarito" role="dialog">
	    <div class="modal-dialog modal-lg">
	      <div class="modal-content">
	        <div class="modal-header">
	          	<button type="submit" class="close" data-dismiss="modal">&times;</button>
	          <h4 class="modal-title">Veja seu desempenho!</h4>
	        </div>
	        <input type="hidden" id="inputs" name="inputs" value=""/>
	        <div class="modal-body">          
					<?php
						if ( ! empty($abrirModalGabarito)) {
						echo '<div class="table-responsive col-md-12 col-xs-12">';								
							echo '<table class="centered tabela-gabarito table-bordered table-striped">';
								echo '<tr>';
									echo '<th class="titulo"> Sua resposta </th>';
									echo '<th class="titulo"> Gabarito </th>';
                                echo '</tr>';
								$i = 0;
								foreach ($inputJogador as $input => $value) {
									echo '<tr>';
										echo '<td>'.$value.'</td>';	
										echo '<td>'.$gabarito[$i].'</td>';
									echo '</tr>';
								$i++;
								}
							echo '</table>';
						echo '</div>';
						echo '<p class="centered" > Sua pontuação foi '.$pontuacao. ' pontos!</p>';						
						}
					?>			
	        </div>
		    <div class="row centered">
		    	<div class="modal-footer">
		    			<button type="button" id="sairGabarito" data-dismiss="modal" class="btn btn-default">Fechar</button>		    		
		        </div>
		    </div>
	      </div>
	    </div>
	  </div>
	</div>

<?php if (!empty($erro)) { echo novel_notify('Para fazer este teste, pratique primeiro os grafemas indicados no cartão.', 'warning'); } ?>
