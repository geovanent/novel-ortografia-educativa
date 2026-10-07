<!-- Aqui e a area do conteudo -->
			<div class="row afastado-1pc">
				<div id="conteudo" class="col-md-8 col-xs-12 centered" >
					<div class="row">
						<div id="painel-login" class="col-md-12 col-xs-12">
							<div class="row">
								<h1 class="titulo-menu centered">Edição de texto</h1>
							</div>			
							<form class="form-horizontal" role="form">				
							<div class="row">
								<!--LADO ESQUERDO-->
								<div class="col-md-7 col-xs-12">
									<div class="row">
								  		<div class="form-group centered">
								  			<div class="col-md-4 col-xs-4">
								  				<label for="texto">Texto:</label>
								  			</div>
									    	<div class="col-md-8 col-xs-8">
									    		<textarea  style="width:100%" required="" maxlength= class="form-control" id="texto" name="texto"><?php echo('$texto');?></textarea>
									    	</div>								    
								  		</div>
							  		</div>
							  		<div class="row">
								  	<div class="form-group centered">
								  		<div class="col-md-4 col-xs-4">
								    		<label for="imagem">Imagem atual:</label>
								    	</div>
								   		<div class="col-md-8 col-xs-8">
                                            <img src="<?php echo novel_image_url(isset($imagem) ? $imagem : ''); ?>"> <!-- TEM QUE VER COMO VAI FICAR ISSO -->
								   		</div>
								  	</div>
							  	</div>
							  		<div class="row">
									  	<div class="form-group centered">
									  		<div class="col-md-4 col-xs-4">
									    		<label for="imagem">Alterar imagem:</label>
									    	</div>
									   		<div class="col-md-8 col-xs-8">
									   			<input type="file" class="form-control" required="" accept="image/*" id="imagem" name="imagem">
									   		</div>
									  	</div>
								  	</div>

								  	<!-- INICIO DO PREENCHIMENTO DINAMICO DOS GRAFEMAS-->
								  	<div class="row">
									  	<div class="form-group centered">
									  		<div class="col-md-4 col-xs-4">
									    		<label for="grafema">Selecione os grafemas:</label>
									    	</div>
									   		<div class="col-md-8 col-xs-8">
									   			<input type="checkbox" name="grafema" value="<?php echo('$grafema');?>"> <?php echo('$grafema');?> 											
									   		</div>
									  	</div>
								  	</div>
								  	<div class="row">
									  	<div class="form-group centered">
									  		<div class="col-md-4 col-xs-4">

									    	</div>
									   		<div class="col-md-8 col-xs-8">
									   			<input type="checkbox" name="grafema" value="<?php echo('$grafema');?>"> <?php echo('$grafema');?> 											
									   		</div>
									  	</div>
								  	</div>
								  	<div class="row">
									  	<div class="form-group centered">
									  		<div class="col-md-4 col-xs-4">
									    		
									    	</div>
									   		<div class="col-md-8 col-xs-8">
									   			<input type="checkbox" name="grafema" value="<?php echo('$grafema');?>"> <?php echo('$grafema');?> 											
									   		</div>
									  	</div>
								  	</div>
								  	<div class="row">
									  	<div class="form-group centered">
									  		<div class="col-md-4 col-xs-4">
									    		
									    	</div>
									   		<div class="col-md-8 col-xs-8">
									   			<input type="checkbox" name="grafema" value="<?php echo('$grafema');?>"> <?php echo('$grafema');?> 											
									   		</div>
									  	</div>
								  	</div>
								  	<div class="row">
									  	<div class="form-group centered">
									  		<div class="col-md-4 col-xs-4">
									    		
									    	</div>
									   		<div class="col-md-8 col-xs-8">
									   			<input type="checkbox" name="grafema" value="<?php echo('$grafema');?>"> <?php echo('$grafema');?> 											
									   		</div>
									  	</div>
								  	</div>
								  	<div class="row">
									  	<div class="form-group centered">
									  		<div class="col-md-4 col-xs-4">
									    		
									    	</div>
									   		<div class="col-md-8 col-xs-8">
									   			<input type="checkbox" name="grafema" value="<?php echo('$grafema');?>"> <?php echo('$grafema');?> 											
									   		</div>
									  	</div>
								  	</div>
								  	<div class="row">
									  	<div class="form-group centered">
									  		<div class="col-md-4 col-xs-4">
									    		
									    	</div>
									   		<div class="col-md-8 col-xs-8">
									   			<input type="checkbox" name="grafema" value="<?php echo('$grafema');?>"> <?php echo('$grafema');?> 											
									   		</div>
									  	</div>
								  	</div>
								  	<div class="row">
									  	<div class="form-group centered">
									  		<div class="col-md-4 col-xs-4">
									    		
									    	</div>
									   		<div class="col-md-8 col-xs-8">
									   			<input type="checkbox" name="grafema" value="<?php echo('$grafema');?>"> <?php echo('$grafema');?> 											
									   		</div>
									  	</div>
								  	</div>
								  	<div class="row">
									  	<div class="form-group centered">
									  		<div class="col-md-4 col-xs-4">
									    		
									    	</div>
									   		<div class="col-md-8 col-xs-8">
									   			<input type="checkbox" name="grafema" value="<?php echo('$grafema');?>"> <?php echo('$grafema');?> 											
									   		</div>
									  	</div>
								  	</div>
								  	<div class="row">
									  	<div class="form-group centered">
									  		<div class="col-md-4 col-xs-4">
									    		
									    	</div>
									   		<div class="col-md-8 col-xs-8">
									   			<input type="checkbox" name="grafema" value="<?php echo('$grafema');?>"> <?php echo('$grafema');?> 											
									   		</div>
									  	</div>
								  	</div>

								  	<!-- FIM DO PREENCHIMENTO DINAMICO DOS GRAFEMAS-->

								</div>

								<!--LADO DIREITO-->
								<div class="col-md-5 col-xs-12">

								<!-- INICIO DO PREENCHIMENTO DINAMICO DO GABARITO-->
									<div class="row">
									  	<div class="form-group centered">
									  		<div class="col-md-4 col-xs-4">
									    		<label for="gabarito">Letra gabarito 1:</label>
									    	</div>
									   		<div class="col-md-8 col-xs-8">
									   			<input type="text" class="form-control" required=""  size="40" id="gabarito" name="gabarito" value="<?php echo('$letraGabarito');?>">
									   		</div>
									  	</div>
								  	</div>

								  	<div class="row">
									  	<div class="form-group centered">
									  		<div class="col-md-4 col-xs-4">
									    		<label for="gabarito">Letra gabarito 2:</label>
									    	</div>
									   		<div class="col-md-8 col-xs-8">
									   			<input type="text" class="form-control" required=""  size="40" id="gabarito" name="gabarito" value="<?php echo('$letraGabarito');?>">
									   		</div>
									  	</div>
								  	</div>

								  	<div class="row">
									  	<div class="form-group centered">
									  		<div class="col-md-4 col-xs-4">
									    		<label for="gabarito">Letra gabarito 3:</label>
									    	</div>
									   		<div class="col-md-8 col-xs-8">
									   			<input type="text" class="form-control" required=""  size="40" id="gabarito" name="gabarito" value="<?php echo('$letraGabarito');?>">
									   		</div>
									  	</div>
								  	</div>

								  	<div class="row">
									  	<div class="form-group centered">
									  		<div class="col-md-4 col-xs-4">
									    		<label for="gabarito">Letra gabarito 4:</label>
									    	</div>
									   		<div class="col-md-8 col-xs-8">
									   			<input type="text" class="form-control" required=""  size="40" id="gabarito" name="gabarito" value="<?php echo('$letraGabarito');?>">
									   		</div>
									  	</div>
								  	</div>

								  	<div class="row">
									  	<div class="form-group centered">
									  		<div class="col-md-4 col-xs-4">
									    		<label for="gabarito">Letra gabarito 5:</label>
									    	</div>
									   		<div class="col-md-8 col-xs-8">
									   			<input type="text" class="form-control" required=""  size="40" id="gabarito" name="gabarito" value="<?php echo('$letraGabarito');?>">
									   		</div>
									  	</div>
								  	</div>

								  	<div class="row">
									  	<div class="form-group centered">
									  		<div class="col-md-4 col-xs-4">
									    		<label for="gabarito">Letra gabarito 6:</label>
									    	</div>
									   		<div class="col-md-8 col-xs-8">
									   			<input type="text" class="form-control" required=""  size="40" id="gabarito" name="gabarito" value="<?php echo('$letraGabarito');?>">
									   		</div>
									  	</div>
								  	</div>

								  	<div class="row">
									  	<div class="form-group centered">
									  		<div class="col-md-4 col-xs-4">
									    		<label for="gabarito">Letra gabarito 7:</label>
									    	</div>
									   		<div class="col-md-8 col-xs-8">
									   			<input type="text" class="form-control" required=""  size="40" id="gabarito" name="gabarito" value="<?php echo('$letraGabarito');?>">
									   		</div>
									  	</div>
								  	</div>

								  	<div class="row">
									  	<div class="form-group centered">
									  		<div class="col-md-4 col-xs-4">
									    		<label for="gabarito">Letra gabarito 8:</label>
									    	</div>
									   		<div class="col-md-8 col-xs-8">
									   			<input type="text" class="form-control" required=""  size="40" id="gabarito" name="gabarito" value="<?php echo('$letraGabarito');?>">
									   		</div>
									  	</div>
								  	</div>

								  	<div class="row">
									  	<div class="form-group centered">
									  		<div class="col-md-4 col-xs-4">
									    		<label for="gabarito">Letra gabarito 9:</label>
									    	</div>
									   		<div class="col-md-8 col-xs-8">
									   			<input type="text" class="form-control" required=""  size="40" id="gabarito" name="gabarito" value="<?php echo('$letraGabarito');?>">
									   		</div>
									  	</div>
								  	</div>

								  	<div class="row">
									  	<div class="form-group centered">
									  		<div class="col-md-4 col-xs-4">
									    		<label for="gabarito">Letra gabarito 10:</label>
									    	</div>
									   		<div class="col-md-8 col-xs-8">
									   			<input type="text" class="form-control" required=""  size="40" id="gabarito" name="gabarito" value="<?php echo('$letraGabarito');?>">
									   		</div>
									  	</div>
								  	</div>
								  	<!-- FIM DO PREENCHIMENTO DINAMICO DO GABARITO-->							  	
								</div>
							</div>	  								  								  	
							<div class="row centered">
								<button type="button" class="btn btn-primary">Voltar</button>
								<button type="submit" class="btn btn-success">Editar</button>
								<button type="button" class="btn btn-danger">Excluir</button>
							</div>
							</form>																				
						</div>
					</div>
				</div>


<!--

PREENCHIMENTO DINAMICO DO GABARITO

<?php
/*
for ($i=1; $i <= 10; $i++) { 
	foreach ($gabarito as $gab):
        echo '<div class="row">';
        	echo '<div class="form-group centered">';
        		echo '<div class="col-md-4 col-xs-4">';
			        echo '<label for="gabarito">'
			        echo ('Letra gabarito ' + $i);
		        echo '<div class="col-md-8 col-xs-8">';
		        	echo '<input type="text" class="form-control" required=""  size="40" id="gabarito" name="gabarito" value=" '$gab'">';
		        echo '</div>';
		    echo '</div>';
		echo '</div>';
	endforeach;
}
*/            
?>


PREENCHIMENTO DINAMICO DOS GRAFEMAS

foreach ($grafema as $gr):
	echo '<div class="row">';
	  	echo '<div class="form-group centered">';
	  		echo '<div class="col-md-4 col-xs-4">';
			echo '</div>';
	   		echo '<div class="col-md-8 col-xs-8">';
	   			echo '<input type="checkbox" name="grafema" value="'$gr'"> 
	   			echo ($gr);
	   		echo '</div>';
	  	echo '</div>';
	echo '</div>';
endforeach;

-->
