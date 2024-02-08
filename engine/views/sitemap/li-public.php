<?php
/**
 * Вывод ссылки на публикацию в карте сайта
 * @var $id integer
 * @var $alias string
 * @var $title string
 */
?>
<li>
    <b><small><?= ++$_SESSION['p-counter'] ?>.</small></b>&nbsp;
    <a href="/publication/show/<?= $id ?>::<?= $alias ?>.html"><?= html_entity_decode($title) ?></a>
</li>
