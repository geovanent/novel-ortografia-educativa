	<!-- Aqui é a área do conteúdo -->
    <div id="conteudo" class="novel-level-screen">
        <section class="novel-level-hero">
            <p class="novel-eyebrow">Missões de ortografia</p>
            <h3 class="titulo-menu">Escolha um desafio de palavras</h3>
            <p class="novel-level-copy">Complete as lacunas, ganhe pontos e avance pela Academia de Novéis.</p>
        </section>
        <div id="imagens-menu" class="novel-challenge-grid" >
	
			<?php
				$tamGrafemasBD = count($grafemasCadastrados);
				$tamGrafemasJogados = count($grafemasJogados);				

				for($i = 0; $i<$tamGrafemasBD; $i++) {
                    $tipoGrafema = $grafemasCadastrados[$i]->tipoGrafema;
                    $concluido = FALSE;
								for ($j=0; $j < $tamGrafemasJogados; $j++) { 
                        if (($grafemasJogados[$j]->tipoGrafema == $tipoGrafema) && $grafemasJogados[$j]->pontuacao > 20){
                            $concluido = TRUE;
									}								
								}
                    $url = 'palavra/jogarPalavra/'.rawurlencode($tipoGrafema);
                    $imagem = 'assets/img/grafemas/palavra/'.$tipoGrafema.'.png';
                    $rotulo = strtoupper(str_replace('_', ' / ', $tipoGrafema));

                    echo '<a class="novel-challenge-card" href="'.base_url($url).'">';
                        echo '<span class="novel-stage-number">'.($i + 1).'</span>';
                        if ($concluido) {
                            echo '<span class="novel-complete-badge"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span> Concluída</span>';
				}
                        echo '<img class="img-responsive aa" src="'.novel_image_url($imagem).'" alt="Missão '.$rotulo.'">';
                        echo '<strong>'.$rotulo.'</strong>';
                        echo '<small>Treino de palavra</small>';
                    echo '</a>';
                }
			?>
        </div>
		
		<input type="hidden" id="abrirModalGabarito" value="<?php echo ($abrirModalGabarito); ?>">
		<input type="hidden" id="abrirModalHistoria" value="<?php echo (isset($abrirModalHistoria[0]) ? $abrirModalHistoria[0] : ''); ?>">
		<input type="hidden" id="inseriu" value="<?php echo ($inseriu); ?>">
		<input type="hidden" id="erro" value="<?php echo ($erro); ?>">
		

  <!-- Modal -->
  	<div class="modal fade" id="modalGabarito" role="dialog">
	    <div class="modal-dialog modal-lg">
	      <div class="modal-content">
	        <div class="modal-header" style="background-color: #3498db;">
	          	<button type="submit" class="close" data-dismiss="modal">&times;</button>
	          <h4 class="modal-title title" style="font-size: 40px;">Veja seu desempenho!</h4>
	        </div>
	        <input type="hidden" id="inputs" name="inputs" value=""/>
	        <div class="modal-body">          
					<?php
						if ( ! empty($abrirModalGabarito)) {
						echo '<div class="table-responsive">';									
							echo '<table class="centered tabela-gabarito table table-striped">';
								echo '<tr>';
									echo '<th class="titulo"> Sua resposta </th>';
									echo '<th class="titulo"> Gabarito </th>';
									echo '<th class="titulo"> Resposta correta</th>';									
									echo '<th class="titulo"> Explicação </th>';
                                echo '</tr>';
								$i = 0;
								foreach ($inputJogador as $input => $value) {
									echo '<tr>';
										echo '<td>'.$value.'</td>';	
										echo '<td>'.$gabarito[$i].'</td>';
										echo '<td>'.$palavraCompleta[$i].'</td>';											
										echo '<td>'.$justificativa[$i].'</td>';	
									echo '</tr>';
								$i++;
								}
							echo '</table>';
						echo '</div>';
						echo '<h4 class="centered afastado-1pc" > Sua pontuação foi '.$pontuacao. ' pontos!</h4>';						
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

<?php if (!empty($erro)) { echo novel_notify('Não foi possível abrir este treino. Escolha outro desafio ou tente novamente.', 'warning'); } ?>
