<?php
/**
 * Форма отправки комментариев
 * @var $publication_id integer
 * @var $user_id integer
 * @var $content_id integer
 * @var $is_reply boolean
 * @var $parent_id integer
 * @var $show_cancel boolean
 */

session_start(); if($_SESSION['user']['id']): ?>
<form class="mb-4 comment-form modal-control clickable" onsubmit="publication.comment(this); return false;">
    <div class="row">
        <div class="col-md-1">
            <?php if ($_SESSION['user']['profile_image']): ?>
                <img class="user-circle-img" src="<?= $_SESSION['user']['profile_image'] ?>" alt="" width="50"
                     title="Личный кабинет пользователя">
            <?php else: ?>
                <img src="/assets/uploads/icons/profile/profile-<?= $_SESSION['user']['gender'] ?>-default.jpg" alt=""
                     width="50" title="Личный кабинет пользователя">
            <?php endif; ?>
        </div>
        <div class="col-md-11 form-body">
            <input type="hidden" name="publication_id" value="<?= $publication_id ?>"/>
            <input type="hidden" name="user_id" value="<?= $user_id ?>"/>
            <input type="hidden" name="is_reply" value="<?= $is_reply ?>"/>
            <input type="hidden" name="parent_id" value="<?= $parent_id ?>"/>
            <input type="hidden" name="content_id" value="<?= $content_id ?>"/>
            <textarea class="form-control" name="comment"
                      placeholder="Оставьте свой комментарий к публикации"></textarea>
            <div class="invalid-feedback"></div>
            <?= render('components', 'uploader', ['upload_folder' => 'img/comments']) ?>
            <button type="submit" class="btn btn-primary">Отправить</button>
            <?php if ($show_cancel): ?>
                <button type="button" class="btn btn-secondary" onclick="$(this).closest('form').remove()">Отмена</button>
            <?php endif; ?>
        </div>
    </div>
    <hr>
</form>
<?php else:?>
    <form class="mb-4 comment-form clickable" onsubmit="return false;">
    <div class="row">
        <div class="col-md-12 form-body">
            <textarea class="form-control" name="comment" disabled
                      placeholder="Авторизуйтесь для возможности комментирования..."></textarea>
            <br>
            <button type="button" class="btn btn-primary" disabled>Отправить</button>
        </div>
    </div>
    <hr>
    </form>
<?php endif;?>