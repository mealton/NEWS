<?php
/**
 * Элемент Изображение в режиме редактирования
 * @var $checkImgDefault boolean
 * @var $is_hidden boolean
 * @var $editor_hide boolean
 * @var $description string
 * @var $content string
 */
?>
<div class="publication__item" data-tag="image">
    <fieldset>
        <p class="lead d-flex align-items-center fw-bold">
            <label class="form-check-label" style="margin-right: 10px;">
                <small>Изображение по умолчанию</small>
                <input class="form-check-input" name="img-default" type="checkbox"
                       value="" <?= $checkImgDefault ? 'checked' : '' ?>
                       onclick="publication.imgDefault(this)">
            </label>
            <label class="form-check-label" style="margin-right: 10px;">
                <small>Не отображать</small>
                <input class="form-check-input" name="is_hidden" type="checkbox"
                       value="" <?= $is_hidden ? 'checked' : '' ?> >
            </label>
            <label class="form-check-label" style="margin-right: 10px;">
                <small>Выделить</small>
                <input class="form-check-input" name="multi-select" type="checkbox" value=""
                       onchange="publication.multiSelectPublicItems(this)"
                       title="Выделятся все блоки, начиная с данного и заканчивая следующим выделенным">
            </label>
            <label class="form-check-label" >
                <small>Взять описание из предыдущего блока</small>
                <input class="form-check-input" name="set-description" type="checkbox" value=""
                       onchange="publication.setDescription(this)"
                       title="Взять описание из предыдущего блока">
            </label>
        </p>
        <div class="editor" style="display: <?= $editor_hide ? 'none' : 'block' ?>">
            <legend>Блок изображение</legend>
            <input type="text" class="form-control" name="description" value="<?= $description ?>"
                   onchange="$(this).closest('.publication__item').find('button.accept-btn').click()"
                   placeholder="Описание изображения (изображений)">
            <br>
        </div>
        <div class="row">
            <div class="col-md-12 publication__item-content">
                <?php //if ($description): ?>
                    <br>
                    <p class="img-description"><em><?= $description ?></em></p>
                <?php// endif; ?>
                <?= $content ?>
            </div>
        </div>
        <?php $tag = 'image';
        include __DIR__ . '/public-item-extras.php' ?>
        <div class="editor" style="display: <?= $editor_hide ? 'none' : 'block' ?>">
            <?= render('components', 'uploader', ['upload_folder' => 'img/public', 'multiple' => 1]) ?>
        </div>
        <?= render('public/edit/item', 'tags-menu', ['after' => 'после данного поля', 'buttons' => 1, 'editor_hide' => $editor_hide]) ?>
    </fieldset>
    <hr>
</div>