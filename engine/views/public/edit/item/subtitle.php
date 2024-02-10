<?php
/**
 * Элемент Подзаголовок в режиме редактирования
 * @var $editor_hide boolean
 * @var $fontsize string
 * @var $style string
 * @var $content string
 */
?>
<div class="publication__item" data-tag="subtitle">
    <fieldset>
        <p class="lead d-flex align-items-center fw-bold">
            <label class="form-check-label">
                <small>Выделить</small>
                <input class="form-check-input" name="multi-select" type="checkbox" value=""
                       onchange="publication.multiSelectPublicItems(this)"
                       title="Выделятся все блоки, начиная с данного и заканчивая следующим выделенным">
            </label>
        </p>
        <div class="editor" style="display: <?= $editor_hide ? 'none' : 'block' ?>">
            <legend>Блок подзаголовок</legend>
            <?= render('public/edit/item', 'text-style-controls', ['fontsize' => 30, 'selected' => 'subtitle']) ?>
        </div>
        <div class="row">
            <div class="col-md-12 publication__item-content" style="font-size: <?= $fontsize ?>px; <?= $style ?>">
                <h2><?= $content ?></h2>
            </div>
        </div>
        <?php include __DIR__ . '/public-item-extras.php' ?>
        <div class="editor" style="display: <?= $editor_hide ? 'none' : 'block' ?>">
            <div class="row">
                <div class="col-md-12">
                    <input name="subtitle" class="form-control" placeholder="Введите подзаголовок"
                           value="<?= $content ?>"/>
                </div>
            </div>
        </div>
        <?= render('public/edit/item', 'tags-menu', ['after' => 'после данного поля', 'buttons' => 1, 'editor_hide' => $editor_hide]) ?>
    </fieldset>
    <hr>
</div>
