<section class="novel-form-card novel-profile-card">
<h1>Minha conta</h1>
<?php echo form_open('minha-conta/salvar'); ?>
<?php foreach(array('nome'=>'Nome','email'=>'E-mail','login'=>'Login') as $field=>$label): ?><div class="form-group"><label for="<?php echo $field; ?>"><?php echo $label; ?></label><input class="form-control" id="<?php echo $field; ?>" name="<?php echo $field; ?>" type="<?php echo $field==='email'?'email':'text'; ?>" required maxlength="<?php echo array('nome'=>50,'email'=>254,'login'=>20)[$field]; ?>" value="<?php echo html_escape($jogador[0]->$field); ?>"></div><?php endforeach; ?>
<button class="btn btn-primary" type="submit">Salvar dados</button></form>
<hr><h2>Alterar senha</h2>
<?php echo form_open('minha-conta/senha'); ?>
<?php foreach(array('senhaAtual'=>'Senha atual','senha1'=>'Nova senha','senha2'=>'Confirme a nova senha') as $field=>$label): ?><div class="form-group"><label for="<?php echo $field; ?>"><?php echo $label; ?></label><input class="form-control" type="password" id="<?php echo $field; ?>" name="<?php echo $field; ?>" autocomplete="<?php echo $field==='senhaAtual'?'current-password':'new-password'; ?>" required maxlength="72"<?php echo $field==='senhaAtual'?'':' minlength="8"'; ?>></div><?php endforeach; ?>
<button class="btn btn-primary" type="submit">Atualizar senha</button></form>
</section>
