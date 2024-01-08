<?php
/**
 * Элмент списка категорий для выбора родительской
 * @var $id integer
 * @var $parent_id_selected integer
 * @var $name string
 */
?>
<option value="<?= $id ?>" <?= $parent_id_selected == $id ? 'selected' : '' ?> ><?= $name ?></option>
