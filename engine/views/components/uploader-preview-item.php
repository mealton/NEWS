<?php
/**
 * Элмент превью загрузчика
 * @var $src string
 */
?>
<div class="preview-item">
    <img src="<?= $src ?>" alt="">
    <i class='fa fa-times remove-img' onclick="uploader.remove(this)" aria-hidden='true' title="Удалить изображение"></i>
</div>
