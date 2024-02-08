<?php
/**
 * Элеменнт названия (ссылки) на публикации определенной категории
 * @var $is_current boolean
 * @var $href string
 * @var $name string
 */
?>
<li class="<?= $is_current ? 'current-category' : '' ?>"><a href="<?= $href ?>"><?= $name ?></a></li>
