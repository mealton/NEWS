<?php
/**
 * Страница вывода всех категорий и карты сайта
 * @var $categories_cards string
 * @var $hashtags_counter integer
 * @var $hashtags string
 * @var $sitemap string
 */
?>
<div class="row justify-content-around">
    <?= $categories_cards ?>
</div>
<br><br>
<hr>
<br>
<div class="row justify-content-between">
    <h3>Метки публикаций <sup><?= $hashtags_counter ?></sup>:</h3>
    <br>
    <br>
    <br>
    <?= $hashtags ?>
</div>
<br id="sitemap"><br>
<hr>
<div class='cat-tree'>
    <h3>Карта сайта:</h3>
    <br>
    <ul><?= $sitemap ?></ul>
</div>