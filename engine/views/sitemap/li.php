<?php
/**
 * Вывод категории и относящихся к ней публикаций и подкатегорий
 * @var $id integer
 * @var $name string
 * @var $publications string
 * @var $subs string
 */
?>
<li>
    <a href="/publication/category/<?= $id ?>/<?= translit($name) ?>" class="main-link"><?= $name ?></a>
    <ul class="publications-list"><?= $publications ?></ul>
    <ul><?= $subs ?></ul>
</li>
