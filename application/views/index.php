<!DOCTYPE html>
<html lang="pt-BR">
<head>
	<title>Novel</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="<?php echo base_url("assets/css/bootstrap.css"); ?>" />
    <link rel="stylesheet" href="<?php echo novel_asset_url("assets/css/estilo.css")."?v=".filemtime(FCPATH."assets/css/estilo.css"); ?>" />
    <link rel="stylesheet" href="<?php echo novel_asset_url("assets/css/interface.css")."?v=".filemtime(FCPATH."assets/css/interface.css"); ?>">
    <script src="<?php echo novel_asset_url("assets/js/novel-ui.js")."?v=".filemtime(FCPATH."assets/js/novel-ui.js"); ?>"></script>

</head>

<body id="pagina-inicial" data-image-fallback="<?php echo novel_asset_url('assets/img/illustration-unavailable.svg'); ?>">
<script type="text/javascript" src="<?php echo base_url("assets/js/jquery-3.7.1.js"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url("assets/js/bootstrap.js"); ?>"></script>

<main class="novel-auth-page">
	<section class="novel-auth-hero">
		<div>
			<p class="novel-eyebrow">Ortografia gamificada</p>
			<h1>Novel</h1>
			<p>Aprenda regras ortográficas com fases curtas, personagens literários e acompanhamento de progresso.</p>
		</div>
        <img src="<?php echo novel_image_url('assets/img/graciliano.png'); ?>" alt="Personagem do Novel">
	</section>

	<section class="novel-auth-card">
		<div id="novel-login-form">
			<h2>Entrar na aventura</h2>
			<form role="form" method="post" action="<?php echo base_url('autenticar');?>">
				<input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
				<div class="form-group">
					<label for="login">Login</label>
					<input type="text" required name="login" autocomplete="username" maxlength="20" value="<?php echo html_escape(isset($loginValue)?$loginValue:''); ?>" class="form-control" id="login" placeholder="Seu login">
				</div>
				<div class="form-group">
					<label for="senha">Senha</label>
					<input type="password" required name="senha" autocomplete="current-password" maxlength="72" class="form-control" id="senha" placeholder="Sua senha">
				</div>
				<button type="submit" name="btn-login" class="btn btn-lg btn-block btn-success">Começar</button>
			</form>

			<div class="novel-auth-actions">
				<a href="<?php echo base_url('cadastro'); ?>">Criar conta</a>
				<button type="button" class="btn btn-link" data-novel-auth="reset">Esqueci a senha</button>
			</div>
		</div>

        <div id="novel-reset-form" hidden>
			<h2>Recuperar senha</h2>
			<form role="form" method="post" action="<?php echo base_url('recuperar-senha');?>">
				<input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
				<div class="form-group">
					<label for="email">E-mail</label>
					<input type="email" class="form-control" id="email" name="email" required maxlength="254" autocomplete="email" placeholder="email@exemplo.com">
				</div>
				<button type="submit" class="btn btn-lg btn-block btn-success">Enviar link de recuperação</button>
				<button type="button" class="btn btn-link btn-block" data-novel-auth="login">Voltar ao login</button>
			</form>
		</div>
	</section>
</main>

<?php					
if (!empty($erro)) { echo novel_notify('Login ou senha inválidos. Confira seus dados e tente novamente.'); }
if (!empty($email) && !empty($enviou)) { echo novel_notify('Se o e-mail estiver cadastrado, você receberá as instruções de acesso.', 'info'); }
if (isset($enviou) && !$enviou) { echo novel_notify('Não foi possível enviar o e-mail. Tente novamente mais tarde.'); }
$notification = $this->session->flashdata('notification');
if ($notification) { echo novel_notify($notification['message'], $notification['type']); }
?>	
</body>
</html>
