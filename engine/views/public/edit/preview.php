<?php
$classname = '';
if ($is_deleted)
    $classname = 'is-deleted';
else {
    if (!$is_published)
        $classname = 'opacity-75';
}
?>

<div class="<?= $moderated ? '' : 'none-moderated' ?>">
    <div class="row gx-5 public-item-preview <?= $classname ?>" data-id=<?= $id ?>>
        <div class="col-md-6 mb-4 image-preview">
            <div class="bg-image hover-overlay ripple shadow-2-strong rounded-5 position-relative"
                 data-mdb-ripple-color="light">
                <div class="form-check position-absolute publication-status-checkbox">
                    <div class="row justify-content-between top-labels">
                        <div class="col-md-6">
                            <label class="form-check-label">
                                <input class="form-check-input" type="checkbox" value="1"
                                       onchange="publication.publish(this)"
                                    <?= $is_published ? 'checked' : '' ?>>
                                Опубликовано
                            </label>
                        </div>
                        <div class="col-md-6" align="right">
                            <?php if ($is_deleted): ?>
                                <i class='fa fa-trash-o pointer' data-action="recovery"
                                   onclick="publication.delete(this)" aria-hidden='true'></i>
                            <?php else: ?>
                                <i class='fa fa-trash pointer' data-action="delete" onclick="publication.delete(this)"
                                   aria-hidden='true'></i>
                            <?php endif ?>
                        </div>
                    </div>
                </div>
                <a href="/publication/show/<?= $id ?>::<?= $alias ?>.html">
                    <img src="<?= $public_img ? $public_img : '/assets/uploads/img/not-available.jpg' ?>"
                         class="publication-img-preview img-fluid"/>
                </a>
                <a href="/publication/authors/<?= $user_id . '::' . $author ?>/" class="public-author-preview">
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
                                        <small><?= $likes ?></small>
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
            <a href="/publication/show/<?= $id ?>::<?= $alias ?>.html">
                <h4><strong><?= htmlspecialchars_decode($title) ?></strong></h4></a>
            <p class="text-muted">
                <?= htmlspecialchars_decode($introtext) ?>
            </p>
            <a href="?tab=publications&page=<?= $_GET['page'] ?>&publication_id=<?= $id ?>">
                <button class="btn btn-primary">Редактировать</button>
            </a>
            <br>
            <hr>
            <br>
        </div>
    </div>
</div>