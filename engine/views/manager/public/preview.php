<?php
/**
 * Предпросмотр публикации
 * @var $classname string
 * @var $id integer
 * @var $moderated boolean
 * @var $public_img string
 * @var $author_image string
 * @var $author string
 * @var $published_date string
 * @var $views integer
 * @var $likes integer
 * @var $comment_count integer
 */
?>
<div class="row gx-5 public-item-preview <?= $classname ?>" data-id=<?= $id ?>>
    <div class="col-md-6 mb-4">
        <div class="bg-image hover-overlay ripple shadow-2-strong rounded-5 position-relative"
             data-mdb-ripple-color="light">

                <?php if(!$moderated):?>
                <p><b style="color: red;">На модерации!</b></p>
                <?php endif?>

                <img alt="" src="<?= $public_img ? $public_img : '/assets/uploads/img/not-available.jpg' ?>"
                     class="publication-img-preview img-fluid"/>
            <a class="public-author-preview">
                <div class="mask" style="background-color: rgba(0, 0, 0, .35);">
                    <div class="row align-items-center" style="color: #FFF;">
                        <div class="col-md-7">
                            <table>
                                <tr>
                                    <td>
                                        <?php if ($author_image): ?>
                                            <img src="<?= $author_image ?>" class="user-circle-img"
                                                 alt="Пользователь <?= $author ?>"
                                                 title="Пользователь <?= $author ?>">
                                        <?php endif ?>
                                    </td>
                                    <td style="padding-left: 10px;">
                                        <small><b><?= $author ?></b></small>
                                        <div>
                                            <small><?= date_rus_format($published_date) ?>г.</small>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-5">
                            <div class="row">
                                <div class="col-md-4">
                                    <i class='fa fa-eye' aria-hidden='true'></i>
                                    <small><?= $views ?></small>
                                </div>
                                <div class="col-md-4">
                                    <i class='fa fa-heart' aria-hidden='true'></i>
                                    <small><?= $likes ?></small>
                                </div>
                                <div class="col-md-4">
                                    <i class='fa fa-comment' aria-hidden='true'></i>
                                    <small><?= $comment_count ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <span class="badge bg-danger px-2 py-1 shadow-1-strong mb-3">
            <a href="/publication/category/<?= $category_id ?>/<?= $category ?>/"><?= $category ?></a>
        </span>
        <a>
            <h4><strong><?= htmlspecialchars_decode($title) ?></strong></h4></a>
        <p class="text-muted">
            <?= htmlspecialchars_decode($introtext) ?>
        </p>
        <a href="/manager/show/<?= $id ?>::<?= $alias ?>.html">
            <button class="btn btn-primary">Открыть</button>
        </a>
        <button class="btn btn-success" data-id="<?= $id ?>" onclick="manager.moderatePublication(this)">Одобрить</button>
        <button class="btn btn-danger" data-id="<?= $id ?>" onclick="manager.removePublication(this)">Удалить</button>
        <br>
        <hr>
        <br>
    </div>
</div>