<?php
/**
 * Элемент комментария на странице пользователя
 * @var $publication_id integer
 * @var $alias string
 * @var $title string
 * @var $public_img string
 * @var $id integer
 * @var $user_id integer
 * @var $username string
 * @var $date string
 * @var $commet_is_liked boolean
 * @var $likes integer
 * @var $comment string
 * @var $image string
 * @var $replies string
 * @var $has_replies boolean
 */
$public_img = preg_replace("/https?:\/\/(mtuci|news)\.mealton\.ru/", '', $public_img);

?>
<div class="card-body p-4 comment-item-container">
    <div class="row">
        <div class="col-md-6">
            <div class="publication">
                <a href="/publication/show/<?= $publication_id ?>::<?= $alias ?>.html#comments"
                   title="Перейти к комментариям публикации">
                    <h3><?= html_entity_decode($title) ?></h3>
                    <img src="<?= $public_img ?>" class="img-fluid" alt="" style="max-width: 300px;">
                </a>
            </div>
        </div>
        <div class="col-md-6">
            <div class="d-flex flex-start position-relative">
                <div>
                    <?php if ($_SESSION['user']['id'] == $user_id && !$has_replies): ?>
                        <i class='fa fa-times remove-comment pointer' title="Удалить комментарий" data-id="<?= $id ?>"
                           onclick="publication.removeComment(this)" aria-hidden='true'></i>
                    <?php endif; ?>
                    <h6 class="fw-bold mb-1">
                        <a href="/publication/authors/<?= $user_id ?>::<?= $username ?>/">
                            <?= $username ?>
                        </a>
                    </h6>
                    <div class="d-flex align-items-center mb-3">
                        <p class="mb-0">
                            <?= $date ?>
                            <!--<span class="badge bg-success">Approved</span>-->
                            &nbsp;
                            <i class='fa fa-heart<?= $commet_is_liked ? '' : '-o' ?>'
                               data-id="<?= $id ?>"
                               data-user="<?= $_SESSION['user']['id'] ?>"
                               aria-hidden='true'></i>
                            <small class="comment-likes-count"><?= $likes ?></small>
                        </p>
                    </div>
                    <p class="mb-0"><?= $comment ?></p>
                    <br>
                    <?= $image ?>
                </div>
            </div>
            <div class="reply-form"></div>
            <div class="replies">
                <?= $replies ?>
            </div>
        </div>
    </div>
</div>
<hr class="my-0"/>


