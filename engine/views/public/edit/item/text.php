<div class="publication__item" data-tag="text">
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
            <legend>Блок текст</legend>
            <?= render('public/edit/item', 'text-style-controls', ['fontsize' => 22]) ?>
        </div>
        <div class="row">
            <div class="col-md-12 publication__item-content" style="font-size: <?= $fontsize ?>px; <?= $style ?>">
                <?= str_replace(["\n", "<br><br>"], ["<br>", "<br>"], $content)  ?>
            </div>
        </div>
        <?php include __DIR__ . '/public-item-extras.php' ?>
        <div class="editor" style="display: <?= $editor_hide ? 'none' : 'block' ?>">
            <div class="row">
                <div class="col-md-12">
                    <textarea name="text-content" class="form-control" rows="3"
                              placeholder="Введите текст"><?= strip_tags($content) ?></textarea>
                </div>
            </div>
        </div>
        <?= render('public/edit/item', 'tags-menu', ['after' => 'после данного поля', 'buttons' => 1, 'editor_hide' => $editor_hide]) ?>
    </fieldset>
    <hr>
</div>
