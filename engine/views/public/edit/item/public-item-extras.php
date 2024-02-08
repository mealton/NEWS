<?php
/**
 * Элменты управления элеменнтом контента публикации
 * @var $editor_hide boolean
 */
?>
<div class="public-item-extras">
    <i class='fa fa-times remove-public-item' aria-hidden='true' title="Удалить блок" onclick="publication.removeItem(this)"></i>
    <i title="Редактор" class='fa fa-pencil-square-o editor-icon <?= $editor_hide ? 'non-active' : '' ?>'  onclick="publication.editor(this)" aria-hidden='true'></i>
    <div class="move">
        <i class='fa fa-sort-asc move-pubic-item-up' onclick="publication.moveItem(this)" data-direction="up"
           aria-hidden='true' title="Переместить выше"></i>
        <i class='fa fa-sort-desc move-pubic-item-down' onclick="publication.moveItem(this)" data-direction="down"
           aria-hidden='true' title="Переместить ниже"></i>
    </div>
</div>
