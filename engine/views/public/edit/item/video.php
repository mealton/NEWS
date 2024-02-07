<div class="publication__item" data-tag="video">
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
            <legend>Блок видео</legend>
            <input type="text" class="form-control" name="description" value="<?= $description ?>"
                   onchange="$(this).closest('.publication__item').find('button.accept-btn').click()"
                   placeholder="Описание видео">
            <br>
        </div>
        <div class="row">
            <div class="col-md-12 publication__item-content">
                <p class="img-description"><em><?= $description ?></em></p>
                <?= $content ?>
            </div>
        </div>
        <?php include __DIR__ . '/public-item-extras.php' ?>
        <div class="editor" style="display: <?= $editor_hide ? 'none' : 'block' ?>">
            <div class="uploader-container">
                <input type="hidden" name="upload_folder" value="video">
                <div style="width: 100%;">
                    <input type="text" name="url" class="form-control" placeholder="URL видео на youtube">
                    <div class="invalid-feedback" style="text-indent: 70px"></div>
                </div>
                <button type="button" class="btn btn-primary upload-video-url" onclick="publication.getVideo(this)"
                        title="Загрузить URL"></button>
                <label class="btn btn-primary upload-video-file" title="Загрузить с устройства">
                    <input type="file" class="file_upload" accept="video/mp4" onchange="uploader.uploadVideoFile(this)">
                </label>
                <label class="btn btn-primary upload-file poster-uploader" style="margin-left: 5px;" title="Загрузить постер">
                    <input type="file" class="file_upload" accept="image/*" name="poster" onchange="uploader.uploadPoster(this)" >
                </label>
            </div>
            <div class="uploader-previews">
                <?= $previews ?>
            </div>
        </div>
        <?= render('public/edit/item', 'tags-menu', ['after' => 'после данного поля', 'buttons' => 1, 'editor_hide' => $editor_hide]) ?>
    </fieldset>
    <hr>
</div>