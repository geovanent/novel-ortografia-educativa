<section class="novel-activity" data-novel-bonus><h1>Encontre as palavras</h1><p>Marque a primeira e a última letra de cada palavra. Você tem 60 segundos a partir da abertura da atividade. Cada palavra encontrada vale 50 pontos.</p><p role="status" id="bonus-tempo">60 segundos</p>
<ul><?php foreach($bonusWords as $w): ?><li><?php echo html_escape($w['palavra']); ?></li><?php endforeach; ?></ul>
<?php echo form_open('principal/inserirBonus',array('id'=>'bonus-form')); ?><input type="hidden" name="roundToken" value="<?php echo html_escape($roundToken); ?>">
<div class="novel-bonus-scroll"><div class="novel-bonus-grid">
<?php for($i=0;$i<strlen($bonusData->textoBonus);$i++): ?><label><input type="checkbox" name="posicoes[]" value="<?php echo $i; ?>" aria-label="Linha <?php echo intdiv($i,15)+1; ?>, coluna <?php echo $i%15+1; ?>, letra <?php echo html_escape($bonusData->textoBonus[$i]); ?>"><span><?php echo html_escape($bonusData->textoBonus[$i]); ?></span></label><?php endfor; ?>
</div></div><button type="submit" class="btn btn-primary">Concluir bônus</button></form></section>
<script>
document.addEventListener('DOMContentLoaded', function () {
 var deadline = Date.now() + 60000;
 var timer = setInterval(function () {
  var remaining = Math.max(0, Math.ceil((deadline - Date.now()) / 1000));
  document.getElementById('bonus-tempo').textContent = remaining + ' segundos';
  if (!remaining) { clearInterval(timer); document.getElementById('bonus-form').requestSubmit(); }
 }, 1000);
});
</script>
