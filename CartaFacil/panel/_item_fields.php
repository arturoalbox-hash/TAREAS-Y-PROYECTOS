<?php
// Campos comunes del formulario de plato. Usa $it (array) si existe.
$it = $it ?? null;
$fmt = fn($v) => $v === null || $v === '' ? '' : number_format((float)$v, 2, ',', '');
$sel = alergenos_list($it['allergens'] ?? '');
?>
<label>Nombre<input name="name" maxlength="120" required value="<?= e($it['name'] ?? '') ?>" placeholder="Boquerones fritos"></label>
<label>Descripción <span class="muted">(opcional)</span><textarea name="description" maxlength="400" rows="2" placeholder="Con limón y alioli casero"><?= e($it['description'] ?? '') ?></textarea></label>
<div class="row2">
  <label>Precio (€)<input name="price" inputmode="decimal" value="<?= e($fmt($it['price'] ?? null)) ?>" placeholder="9,50"></label>
  <label>Media ración (€) <span class="muted">(opcional)</span><input name="half_price" inputmode="decimal" value="<?= e($fmt($it['half_price'] ?? null)) ?>" placeholder="5,50"></label>
</div>
<fieldset class="alg-pick">
  <legend>Alérgenos <span class="muted">(obligatorio informar, Reglamento UE 1169/2011)</span></legend>
  <?php foreach (ALERGENOS as $k => [$label, $icon]): ?>
    <label class="chip"><input type="checkbox" name="allergens[]" value="<?= e($k) ?>" <?= in_array($k, $sel, true) ? 'checked' : '' ?>><span><?= $icon ?> <?= e($label) ?></span></label>
  <?php endforeach; ?>
</fieldset>
