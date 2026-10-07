	<!-- Aqui é a área do conteúdo -->
    <div id="conteudo" class="novel-level-screen">
        <section class="novel-level-hero">
            <p class="novel-eyebrow">Missões de leitura</p>
            <h3 class="titulo-menu">Escolha um desafio de texto</h3>
            <p class="novel-level-copy">Leia com atenção, encontre os pontos de ortografia e avance na trilha.</p>
        </section>
        <div id="imagens-menu" class="novel-challenge-grid" >
			<?php					

					if(in_array("g_j&ch_x&s_z_x&c_ç_s_ss_sc_sç_xc&m_n&r_rr&e_i&o_u_l", $grafemasJogados)){
					$cod = array_search("g_j&ch_x&s_z_x&c_ç_s_ss_sc_sç_xc&m_n&r_rr&e_i&o_u_l", $grafemasJogados);
					$grafemasJogados[$cod] = "total";
				}

				if(in_array("g_j&ch_x&s_z_x&c_ç_s_ss_sc_sç_xc&m_n&r_rr&e_i&o_u_l", $grafemasJogados)){
					$cod = array_search("g_j&ch_x&s_z_x&c_ç_s_ss_sc_sç_xc&m_n&r_rr&e_i&o_u_l", $grafemasJogados);
					$grafemasJogados[$cod] = "_total";
				}
					
					$tamGrafemasBD = count($grafemasTextos);
					$grafemas = array();									
					for ($i=1; $i < $tamGrafemasBD; $i++) { 
						if(!in_array($grafemasTextos[$i], $grafemas)){							
							$grafemas[] = $grafemasTextos[$i];
						}																
					}										
						array_pop($grafemas);
						array_push($grafemas, "_total", "total");
						$tamGrafemasUnicos = count($grafemas);
						for($i = 0; $i<$tamGrafemasUnicos; $i++) {
                        $grafema = $grafemas[$i];
                        $concluido = in_array($grafema, $grafemasJogados);
                        $url = 'texto/jogarTexto/'.rawurlencode($grafema);
                        $imagem = 'assets/img/grafemas/texto/'.$grafema.'.png';
                        $rotulo = strtoupper(str_replace(array('&', '_'), array(' + ', ' / '), $grafema));

                        echo '<a class="novel-challenge-card" href="'.base_url($url).'">';
                            echo '<span class="novel-stage-number">'.($i + 1).'</span>';
                            if ($concluido) {
                                echo '<span class="novel-complete-badge"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span> Concluída</span>';
							}
                            echo '<img class="img-texto img-responsive centered aa" src="'.novel_image_url($imagem).'" alt="Missão '.$rotulo.'">';
                            echo '<strong>Texto '.($i + 1).'</strong>';
                            echo '<small>'.$rotulo.'</small>';
										echo '</a>';
									}								
			?>
		</div>

		<input type="hidden" id="abrirModalGabarito" value="<?php echo ($abrirModalGabarito); ?>">		
		<input type="hidden" id="erro" value="<?php echo ($erro); ?>">			
		<input type="hidden" id="abrirModalHistoria" value="<?php echo (isset($abrirModalHistoria[0]) ? $abrirModalHistoria[0] : ''); ?>">

<!-- Modal -->
  	<div class="modal fade" id="modalGabarito" role="dialog">
	    <div class="modal-dialog modal-lg">
	      <div class="modal-content">
	        <div class="modal-header">
	          	<button type="submit" class="close" data-dismiss="modal">&times;</button>
	          <h4 class="modal-title">Veja seu desempenho!</h4>
	        </div>	        
	        <div class="modal-body">          
					<?php		
						if ( ! empty($abrirModalGabarito)) {
						echo '<div class="table-responsive">';															
							echo '<table class="centered tabela-gabarito table-bordered table-striped">';
								echo '<tr>';
									echo '<th class="titulo"> Sua resposta </th>';
									echo '<th class="titulo"> Gabarito </th>';
                                echo '</tr>';
								$i = 0;
								foreach ($inputJogador as $input => $value) {
									echo '<tr>';
										echo '<td>'.$value.'</td>';	
										echo '<td>'.$gabarito[$i]->letraGabarito.'</td>';	
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


<!--Mostrar mensagem de erro se o o jogador ainda não estiver apto a jogar um texto-->
<?php if (!empty($erro)) { echo novel_notify('Para fazer este treino de texto, pratique primeiro os grafemas indicados no cartão.', 'warning'); } ?>
