<?php
/**
 * Форма редактирования публикации
 * @var $publication_id integer
 * @var $publication_content string
 * @var $categories array
 * @var $public_header array
 * @var $import_containers array
 * @var $image_default string
 * @var $is_published boolean
 * @var $comment_form string
 * @var $comments string
 */
?>
<div class="container rounded bg-white mt-5 mb-5">
    <form action="/publication/update" id="edit-public-form" method="post" class="publication-form"
          data-method="update">
        <input type="hidden" name="id" value="<?= $publication_id ?>"/>
        <?= render('public/edit', 'publication', [
            'publication_content' => $publication_content,
            'categories' => $categories,
            'url_import' => $public_header['source_url'],
            'category_id' => $public_header['category_id'],
            'title' => $public_header['title'],
            'like_content' => $public_header['like_content'],
            'introtext' => $public_header['introtext'],
            'comment' => $public_header['comment'],
            'hashtags' => $public_header['hashtags'],
            'import_containers' => $import_containers,
            'image_default' => $image_default,
        ]) ?>
        <div class="row">
            <div class="col-md-12">
                <div class="form-check">
                    <label class="form-check-label" style="margin-right: 40px">
                        <input class="form-check-input" name="publish" type="checkbox"
                               value="1" <?= $is_published ? 'checked' : '' ?> >
                        Опубликовать
                    </label>
                    <label class="form-check-label">
                        <input class="form-check-input" name="update-date" type="checkbox">
                        Обновить дату публикации
                    </label>
                </div>
            </div>
        </div>
        <hr>
        <button class="btn btn-primary" type="submit">Обновить</button>
        <a href="?tab=publications&page=<?= $_GET['page'] ?>">
            <button type="button" class="btn btn-secondary">Выйти</button>
        </a>
        <a href="/publication/show/<?= $publication_id . "::" . $public_header['alias'] ?>.html" target="_blank">
            <button type="button" class="btn btn-success">Перейти к публикации</button>
        </a>
    </form>

    <br>
    <hr>
    <!-- Comments section-->
    <section class="mb-5">
        <div class="card bg-light">
            <div class="card-body">
                <!-- Comment form-->
                <?= $comment_form ?>
                <!-- Comment with nested comments-->
                <section>
                    <div class="container my-5 py-5">
                        <div class="row d-flex justify-content-center">
                            <div class="col-md-12 col-lg-12">
                                <div class="card text-dark">
                                    <div class="card-body p-4 comments-list">
                                        <h4 class="mb-0">Комментарии</h4>
                                        <div id="comments">
                                            <?= $comments ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>


            </div>
        </div>
    </section>
</div>
