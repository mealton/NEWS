<?php
/**
 * Комментарий
 * @var $user_id integer
 * @var $has_replies boolean
 * @var $is_complained boolean
 * @var $reason_complaint string
 * @var $id integer
 * @var $username string
 * @var $profile_image string
 * @var $gender string
 * @var $is_author boolean
 * @var $date string
 * @var $is_active boolean
 * @var $commet_is_liked boolean
 * @var $likes integer
 * @var $publication_id integer
 * @var $comment string
 * @var $image string
 * @var $replies string
 * @var $commented_content string
 */
?>
<div class="card-body p-4 position-relative comment-item-container" data-contentid="<?= $content_id ?>">
    <?php session_start();
    if ($user_id == $_SESSION['user']['id'] && !$is_complained && !$has_replies): ?>
        <i class='fa fa-times remove-comment pointer' title="Удалить комментарий" data-id="<?= $id ?>"
           onclick="publication.removeComment(this)" aria-hidden='true'></i>
    <?php endif; ?>
    <div class="d-flex flex-start">
        <a href="/publication/authors/<?= $user_id ?>::<?= $username ?>/">
            <?php if ($profile_image): ?>
                <img class="rounded-circle shadow-1-strong me-3"
                     src="<?= $profile_image ?>" alt="avatar" width="60"
                     height="60"/>
            <?php else: ?>
                <img class="rounded-circle shadow-1-strong me-3"
                     src="/assets/uploads/icons/profile/profile-<?= $gender ?>-default.jpg"
                     alt="avatar" width="60"
                     height="60"/>
            <?php endif; ?>
        </a>
        <div>
            <h6 class="fw-bold mb-1">
                <a href="/publication/authors/<?= $user_id ?>::<?= $username ?>/">
                    <?= $username ?>
                    <?= $is_author ? '<span class="author-comment">Комментарий автора</span>' : '' ?>
                </a>

                <?php if ($commented_content): $commented_content = preg_replace("/https?:\/\/(mtuci|news)\.mealton\.ru/", '', $commented_content) ?>
                    <span class="mb-0 comment-note">
                        <small>к изображению </small>
                        <img src="<?= $commented_content ?>" class="comment-to-img" onclick="publication.scrollToImage(this)" alt="">
                    </span>
                <?php endif; ?>
            </h6>
            <div class="d-flex align-items-center mb-3">
                <p class="mb-0">
                    <?= $date ?>

                    <?php if ($is_active): ?>

                        <span class="complain <?= $is_complained ? '' : 'd-none' ?>">
                        &nbsp;<span class="badge bg-danger">Жалоба!</span>
                    </span>

                        &nbsp;<?php if ($_SESSION['user']['id'] && $_SESSION['user']['id'] != $user_id): ?>
                            <i class='fa fa-heart<?= $commet_is_liked ? '' : '-o' ?> pointer'
                               data-id="<?= $id ?>"
                               data-user="<?= $_SESSION['user']['id'] ?>"
                               onclick="publication.like_comment(this)" aria-hidden='true'></i>
                            <small class="comment-likes-count"><?= $likes ?></small>                    &nbsp;
                            <i class='fa fa-reply pointer'
                               data-publication_id="<?= $publication_id ?>"
                               data-id="<?= $id ?>"
                               data-user="<?= $_SESSION['user']['id'] ?>"
                               onclick="publication.reply(this)"
                               aria-hidden='true'></i>&nbsp;&nbsp;&nbsp;
                            <i class="fa fa-exclamation-triangle pointer"
                               title="Пожаловаться"
                               data-id="<?= $id ?>"
                               onclick="publication.complain(this)"
                               aria-hidden="true"></i>
                        <?php else: ?>
                            <i class='fa fa-heart-o' aria-hidden='true'></i>
                            <small class="comment-likes-count"><?= $likes ?></small>
                        <?php endif; endif; ?>
                </p>
            </div>

            <?php if (!$is_active && $is_complained): ?>
                <p class="lead"><small>Комментарий удалён
                        модератором. <?= $reason_complaint ? 'Причина: ' . $reason_complaint : "" ?></small></p>
            <?php else: ?>
                <p class="mb-0 comment-text" style="margin-bottom: 20px;"><?= $comment ?></p>
                <?= $image ?>
            <?php endif; ?>


        </div>
    </div>
    <div class="reply-form"></div>
    <div class="replies">
        <?= $replies ?>
    </div>
</div>
<hr class="my-0"/>





