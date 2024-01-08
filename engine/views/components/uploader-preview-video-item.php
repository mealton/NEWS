<?php
/**
 * Элмент превью загрузчика
 * @var $src string
 */
?>
<div class="preview-item preview-item-video position-relative d-inline-block">
    <video src="<?= $src ?>" controls="controls"></video>
    <i class='fa fa-times remove-img' onclick="uploader.remove(this)" aria-hidden='true' title="Удалить видео"></i>
</div>
