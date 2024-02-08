<?php
/**
 * Публикация в режиме редактирования
 * @var $image_default string
 * @var $categories array
 * @var $category_id integer
 * @var $category_id integer
 * @var $url_import string
 * @var $import_containers array
 * @var $title string
 * @var $introtext string
 * @var $hashtags string
 * @var $comment string
 * @var $start_text string
 * @var $publication_content string
 */
?>
<!--Выбор категории-->
<fieldset>
    <input type="hidden" name="user_id" value="<?php session_start();
    echo $_SESSION['user']['id'] ?>"/>
    <input type="hidden" name="image_default" value="<?= $image_default ?>"/>
    <legend>Категория</legend>
    <div class="row">
        <div class="col-md-8"></div>
        <div class="col-md-4">
            <select name="category_id" class="form-control">
                <option value="" selected disabled>Выберете категорию публикации</option>
                <?php foreach ($categories as $category): ?>
                    <option <?= $category['id'] == $category_id ? 'selected' : '' ?>
                            value="<?= $category['id'] ?>"><?= $category['name'] ?></option>
                <?php endforeach; ?>
            </select>
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <br>
    <!--Добавление новой категории-->
    <div class="row">
        <div class="col-md-12">
            <div class="accordion" id="accordionExample">
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingOne">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                            Добавить новую категорию
                        </button>
                    </h2>
                    <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne"
                         data-bs-parent="#accordionExample">
                        <div class="accordion-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="text" name="new_category" class="form-control"
                                           onchange="publication.checkNewCategory(this)"
                                           placeholder="Название новой категории">
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="col-md-6">
                                    <select name="new_category_parent_id" class="form-control">
                                        <option value="0" selected>
                                            Выберете родительскую категорию (по умолчанию без родителя)
                                        </option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?= $category['id'] ?>"><?= $category['name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-check" style="margin: 10px 0;">
                                <label>
                                    <input class="form-check-input" type="checkbox" name="is_hidden" />
                                    категория 18+
                                </label>
                            </div>
                            <textarea name="new_category_description" class="form-control" rows="2"
                                      style="max-height: 250px"
                                      placeholder="Описание новой категории"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</fieldset>
<br>
<!--Импорт-->
<fieldset>
    <legend>Импорт</legend>
    <div class="row">
        <div class="col-md-10">
            <input type="text" name="url_import" class="form-control"
                   value="<?= $url_import ?>"
                   placeholder="Url страницы источника публикации">
            <div class="invalid-feedback"></div>
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-primary" style="width: 100%" onclick="publication.import(this)">
                Импорт
            </button>
        </div>
    </div>
    <br>
    <div class="row align-items-center">
        <div class="col-md-4">
            <input type="text" name="import_public_container" class="form-control"
                   value="<?= $import_containers['publication_container'] ?>"
                   placeholder="Селектор контейнера импортируемой страницы">
        </div>
        <div class="col-md-4">
            <input type="text" name="import_public_hashtags_container" class="form-control"
                   value="<?= $import_containers['hashtags_container'] ?>"
                   placeholder="Селектор контейнера хештегов импортируемой страницы">
        </div>
        <div class="col-md-4">
            <div class="form-check">
                <label class="form-check-label">
                    <input class="form-check-input" name="only_img" type="checkbox" value="1">
                    Импортировать только фото
                </label>
            </div>
        </div>
    </div>
</fieldset>
<br>
<!--Метаданные-->
<fieldset>
    <legend>Основные данные</legend>
    <div class="row">
        <div class="col-md-12">
            <input type="text" name="title" class="form-control" placeholder="Название публикации" required
                   value="<?= $title ?>">
            <div class="invalid-feedback"></div>
            <br>
            <input type="text" name="introtext" class="form-control"
                   placeholder="Аннтотация (краткое описание публикации)"
                   value="<?= $introtext ?>">
            <br>
            <input type="text" name="hashtags" class="form-control" placeholder="Метки (через запятую)"
                   value="<?= $hashtags ?>">
            <br>
            <input type="text" name="comment" class="form-control"
                   placeholder="Комментарий к статье (виден только вам)" value="<?= $comment ?>">
        </div>
    </div>
</fieldset>
<br>
<!--Меню добавления новых тегов-->
<div id="main_tag_menu">
    <?php include dirname(dirname(__DIR__)) . '/public/edit/item/tags-menu.php' ?>
</div>

<div class="row">
    <div class="col-md-12">
        <div id="publication_body">
            <?php if ($start_text): ?>
                <p class="lead start-text">Здесь будет отображена ваша публикация...</p>
            <?php endif; ?>
            <?= $publication_content ?>
        </div>
    </div>
</div>
