<section class="novel-form-card"><h1>Escolha uma nova senha</h1>
<?php if($formError): ?><p role="alert"><?php echo html_escape($formError); ?></p><?php endif; ?>
<?php echo form_open('redefinir-senha/'.rawurlencode($token)); ?>
<?php foreach(array('senha1'=>'Nova senha','senha2'=>'Confirme a nova senha') as $field=>$label): ?><div class="form-group"><label for="<?php echo $field; ?>"><?php echo $label; ?></label><input class="form-control" id="<?php echo $field; ?>" name="<?php echo $field; ?>" type="password" required minlength="8" maxlength="72" autocomplete="new-password"></div><?php endforeach; ?>
<button class="btn btn-primary" type="submit">Redefinir senha</button></form><a href="<?php echo base_url('login'); ?>">Voltar ao login</a>
</section>
