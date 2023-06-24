<div class="card-body p-4 position-relative comment-item-container comment-reply">
    <?php session_start();
    if (($user_id == $_SESSION['user']['id'] && !$is_complained) || $_SESSION['user']['is_admin']): ?>
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
                </a>
            </h6>
            <div class="d-flex align-items-center mb-3">
                <p class="mb-0">
                    <?= $date ?>
                    <?php if ($is_active): ?>
                        <span class="complain <?= $is_complained ? '' : 'd-none' ?>">
                            &nbsp;<span class="badge bg-danger">Жалоба!</span>
                        </span>
                        &nbsp;<?php if ($user_id == $_SESSION['user']['id']): ?>
                            <i class='fa fa-heart<?= $commet_is_liked ? '' : '-o' ?> pointer'
                               data-id="<?= $id ?>"
                               data-user="<?= $_SESSION['user']['id'] ?>"
                               onclick="publication.like_comment(this)" aria-hidden='true'></i>
                            <small class="comment-likes-count"><?= $likes ?></small>&nbsp;&nbsp;
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
                <p class="lead">Комментарий удалён модератором.....</p>
            <?php else: ?>
                <p class="mb-0"><?= $comment ?></p>
                <br>
                <?= $image ?>
            <?php endif; ?>


        </div>
    </div>
</div>