<?php
/**
 * Элементы добавления новых элментов контента
 * @var $buttons boolean
 * @var $editor_hide boolean
 * @var $after string
 */
?>
<div class="row tags-menu justify-content-between">
    <div class="col-md-8 buttons">
        <?php if ($buttons): ?>
            <button type="button" style="display: <?= $editor_hide ? 'none' : 'block' ?>"
                    class="btn btn-primary accept-btn editor" onclick="publication.acceptEdit(this)">ОК
            </button>
        <?php endif; ?>
    </div>
    <div class="col-md-4 tag-controls">
        <div class="row tags-controls-container fs-3 text justify-content-end">
            <div class="col-md-2">
                <i class="tags-controls__item fa fa-picture-o" aria-hidden="true"
                   title="Добавить <?= $after ?> картинку"
                   onclick="publication.addItem(this)"
                   data-tag="image"></i>
            </div>
            <div class="col-md-2">
                <i class="tags-controls__item fa fa-youtube" aria-hidden="true" title="Добавить <?= $after ?> видео"
                   onclick="publication.addItem(this)"
                   data-tag="video"></i>
            </div>
            <div class="col-md-2">
                <i class="tags-controls__item fa fa-font" aria-hidden="true" title="Добавить <?= $after ?> подзаголовок"
                   onclick="publication.addItem(this)"
                   data-tag="subtitle"></i>
            </div>
            <div class="col-md-2">
                <i class='tags-controls__item fa fa-align-justify tags-controls__item add-text' aria-hidden='true'
                   onclick="publication.addItem(this)"
                   title="Добавить <?= $after ?> текст" data-tag="text"></i>
            </div>
            <div class="col-md-2">
                <i class='tags-controls__item fa fa-quote-left' aria-hidden='true'
                   onclick="publication.addItem(this)"
                   title="Добавить <?= $after ?> цитату" data-tag="quote"></i>
            </div>
        </div>
    </div>
</div>
