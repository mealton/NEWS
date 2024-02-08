<?php
/**
 * Элементы стилизации текстовых элементов
 * @var $fontsize string
 * @var $selected boolean
 */
?>
<div class="text-style-controls">
    <hr>
    <div class="row">
        <div class="col-md-3 fs-5 text border-end text-center">
            <div class="row">
                <div class="col-md-3">
                    <i class="btn fa fa-align-left" onclick="publication.textStyle(this)" data-prop="text-align"
                       data-style="left" aria-hidden="true"></i>
                </div>
                <div class="col-md-3">
                    <i class="btn fa fa-align-center" onclick="publication.textStyle(this)" aria-hidden="true"
                       data-prop="text-align" data-style="center"></i>
                </div>
                <div class="col-md-3">
                    <i class="btn fa fa-align-right" onclick="publication.textStyle(this)" aria-hidden="true"
                       data-prop="text-align" data-style="right"></i>
                </div>
                <div class="col-md-3">
                    <i class="btn fa fa-align-justify" onclick="publication.textStyle(this)" aria-hidden="true"
                       data-prop="text-align"
                       data-style="justify"></i>
                </div>
            </div>
        </div>
        <div class="col-md-2 fs-5 text border-end text-center">
            <div class="row">
                <div class="col-md-4">
                    <i data-prop="font-weight" onclick="publication.textStyle(this)" data-style="bold"
                       class="btn fa fa-bold" aria-hidden="true"></i>
                </div>
                <div class="col-md-4">
                    <i data-prop="font-style" onclick="publication.textStyle(this)" data-style="italic"
                       class="btn fa fa-italic" aria-hidden="true"></i>
                </div>
                <div class="col-md-4">
                    <i data-prop="text-decoration" onclick="publication.textStyle(this)" data-style="underline"
                       class="btn fa fa-underline"
                       aria-hidden="true"></i>
                </div>
            </div>
        </div>
        <div class="col-md-2 fs-6 border-end text-center">
            <div class="row align-center-center">
                <div class="col-md-3 text-center">
                    <i data-prop="font-size" onclick="publication.textStyle(this)" data-style="less"
                       class="btn fa fa-caret-down" aria-hidden="true"></i>
                </div>
                <div class="col-md-6 text-center">
                    <input type="text" class="form-control text-center" style="font-size: small; padding: 5px 0;"
                           name="fontsize" value="<?= $fontsize ?>" readonly>
                </div>
                <div class="col-md-3 text-center">
                    <i data-prop="font-size" onclick="publication.textStyle(this)" data-style="larger"
                       class="btn fa fa-caret-up" aria-hidden="true"></i>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <select name="all-fields" class="form-control field change-tag"
                            onchange="publication.changeTag(this)">
                        <option value="text">Текст</option>
                        <option value="subtitle" <?= $selected ? 'selected' : '' ?>>Подзаголовок</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <button type="button" class="btn btn-light set-style-to-all" onclick="publication.setStyleToAll(this)">
                        Применить  ко всем
                    </button>
                </div>
            </div>
        </div>
    </div>
    <hr>
</div>
