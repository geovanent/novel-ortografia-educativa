<?php $admin=(bool)$this->session->userdata('codAdministrador'); $home=$admin?'menu-administrador':'menu'; ?>
<nav class="navbar navbar-default novel-navbar" aria-label="Navegação principal">
<div class="navbar-header novel-navbar-branding"><a class="navbar-brand" href="<?php echo base_url($home); ?>">Novel<?php echo $admin?' · Administração':''; ?></a>
<?php if(!$admin): ?><a class="novel-avatar-link" href="<?php echo base_url($home); ?>" aria-label="Início"><img id="avatarNav" src="<?php echo novel_image_url(novel_avatar($this->session->userdata('avatar')).'.png'); ?>" alt=""></a><?php endif; ?>
<button class="navbar-toggle" type="button" data-novel-toggle="myNavbar" aria-controls="myNavbar" aria-expanded="false" aria-label="Abrir menu"><span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span></button></div>
<div class="navbar-collapse novel-navbar-menu" id="myNavbar"><ul class="nav navbar-nav navbar-right novel-main-nav">
<li><a href="<?php echo base_url($home); ?>">Início</a></li>
<?php if(!$admin): ?><li><a href="<?php echo base_url('meus-pontos'); ?>">Meu progresso</a></li><li><a href="<?php echo base_url('historia'); ?>">História</a></li><li><a href="<?php echo base_url('sobre-o-novel'); ?>">Sobre</a></li><?php endif; ?>
<li class="novel-user-menu"><button class="novel-user-trigger" type="button" data-novel-toggle="novel-profile-menu" aria-controls="novel-profile-menu" aria-expanded="false"><span class="novel-user-name"><?php echo html_escape($this->session->userdata('nome')); ?></span> ▾</button>
<div class="novel-user-dropdown" id="novel-profile-menu" hidden><?php if(!$admin): ?><a href="<?php echo base_url('minha-conta'); ?>">Minha conta</a><?php endif; ?><a href="<?php echo base_url($admin?'logoff':'sair'); ?>">Sair</a></div></li>
</ul></div></nav>
