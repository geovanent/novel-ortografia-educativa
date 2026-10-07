<section class="novel-form-card"><header class="novel-section-header"><p class="novel-eyebrow">Nova conta</p><h1>Cadastre-se para jogar</h1><p>Crie seu perfil e escolha um personagem literário.</p></header>
<?php if(!empty($formError)): ?><p class="novel-form-error" role="alert"><?php echo html_escape($formError); ?></p><?php endif; ?>
<?php echo form_open('cadastro/salvar',array('id'=>'cadastro-form')); ?>
<div class="novel-form-grid">
<?php foreach(array('nome'=>'Nome','email'=>'E-mail','login'=>'Login') as $field=>$label): ?><div class="form-group"><label for="<?php echo $field; ?>"><?php echo $label; ?></label><input class="form-control" type="<?php echo $field==='email'?'email':'text'; ?>" id="<?php echo $field; ?>" name="<?php echo $field; ?>" required maxlength="<?php echo array('nome'=>50,'email'=>254,'login'=>20)[$field]; ?>" autocomplete="<?php echo array('nome'=>'name','email'=>'email','login'=>'username')[$field]; ?>" value="<?php echo html_escape(isset($formValues[$field])&&is_string($formValues[$field])?$formValues[$field]:''); ?>"></div><?php endforeach; ?>
<?php foreach(array('senha1'=>'Senha','senha2'=>'Confirme a senha') as $field=>$label): ?><div class="form-group"><label for="<?php echo $field; ?>"><?php echo $label; ?></label><input class="form-control" type="password" id="<?php echo $field; ?>" name="<?php echo $field; ?>" required minlength="8" maxlength="72" autocomplete="new-password"></div><?php endforeach; ?>
</div><p>Use um login com 3 a 20 letras, números, pontos, traços ou sublinhados. A senha deve ter pelo menos 8 caracteres.</p>
<fieldset class="novel-avatar-field"><legend>Escolha seu personagem</legend><div class="novel-avatar-grid">
<?php foreach(array('cecilia'=>'Cecília','graciliano'=>'Graciliano','clarice'=>'Clarice','verissimo'=>'Veríssimo') as $key=>$label): ?><label class="novel-avatar-option"><input type="radio" name="avatar" value="<?php echo $key; ?>" required<?php echo isset($formValues['avatar'])&&$formValues['avatar']===$key?' checked':''; ?>><img src="<?php echo novel_image_url($key.'.png'); ?>" alt=""><span><?php echo $label; ?></span></label><?php endforeach; ?>
</div></fieldset><button type="submit" class="btn btn-primary">Criar conta</button><a class="btn btn-link" href="<?php echo base_url('login'); ?>">Já tenho conta</a></form>
</section>
<script>
document.getElementById('cadastro-form').addEventListener('submit', function (event) {
 if (document.getElementById('senha1').value !== document.getElementById('senha2').value) {
  event.preventDefault(); Novel.notify('As senhas não coincidem. Confira os dois campos.'); document.getElementById('senha2').focus();
 }
});
</script>
