<!-- Aqui e a area do conteudo -->
	<div class="row vertical-center">
		<div id="conteudo" class="col-md-4 col-xs-12 centered" >
			<div class="row">
				<div id="painel-login" class="centered col-md-12 col-xs-12">
					<h2>Entre e torne-se um novel!</h2>
					<form class="form-inline" role="form" method="post" action="<?php echo base_url('autenticar');?>">
<input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
					  	<div class="form-group">
					    	<label for="login">Login:</label>
					    	<input type="text" required="" name="login" class="form-control" id="login">
					  	</div>
					  	<div class="form-group">
					    	<label for="pwd">Senha:</label>
					   		<input type="password" required="" name="senha" class="form-control" id="senha">
					  	</div>
						<div class="centered afastado-1pc col-md-10 col-xs-12">
							<button type="submit" id="btn-login"  name="btn-login" class="btn btn-lg btn-block btn-success">Começar</button>
							
						</div>
						<div class="row centered afastado-1pc col-md-10 col-xs-12">
							<a href="<?php echo base_url('cadastro'); ?>" class="btn btn-block btn-lg btn-info" role="button">Não tenho cadastro</a>
						</div>
						<div class="row">
							<div id="botao-esqueciSenha" class="col-md-6 col-xs-6 centered">
								<a href="<?php echo base_url('recuperar-senha'); ?>" class="btn btn-link" role="button">Esqueci a senha</a>
							</div>
						</div>
					</form>								
				</div>
			</div>
		</div>

		<!--Mostrar mensagem de erro se o login ou senha forem inválidos-->
					<?php
						if ($erro){
							echo '<script language="javascript">';							

                                    echo 'Novel.notify("Não foi possível realizar seu cadastro. Por favor, tente novamente.");';


							echo '</script>';
						} else {
							echo '<script language="javascript">';							

                                    echo 'Novel.notify("Cadastro efetuado! Faça login para jogar.");';


							echo '</script>';
						}
					?>		
							
