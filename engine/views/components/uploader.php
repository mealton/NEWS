<?php
/**
 * Загрузчик
 */
?>
<div class="uploader-container">
    <input type="hidden" name="upload_folder" value="<?= $upload_folder ?>">
    <input type="text" name="url" class="form-control"
           placeholder="<?= $placeholder ? $placeholder : 'URL изображения' ?>">
    <button type="button" class="btn btn-primary upload-url" onclick="uploader.preUploadUrl(this)" title="Загрузить URL"></button>
    <label class="btn btn-primary upload-file" title="Загрузить с устройства">
        <input type="file" accept="image/*" class="file_upload" <?= $multiple ? 'multiple' : '' ?> onchange="uploader.uploadFile(this)">
    </label>
</div>
<div class="uploader-previews">
    <?= $previews ?>
</div>