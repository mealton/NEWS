<?php
/**
 * Карточка с названием, описанием публикации
 * @var $special_content_category boolean
 * @var $id integer
 * @var $alias string
 * @var $title string
 * @var $public_img string
 * @var $author_image string
 * @var $user_id integer
 * @var $author string
 * @var $published_date string
 * @var $views integer
 * @var $likes integer
 * @var $comment_count integer
 * @var $category_id integer
 * @var $category string
 * @var $introtext string
 */
?>
<article>
    <div class="row gx-5 public-item-preview">
        <div class="col-md-6 mb-4">
            <div class="bg-image hover-overlay ripple shadow-2-strong rounded-5 position-relative <?= $special_content_category ? 'is-erotic-container' : '' ?>"
                 data-mdb-ripple-color="light">
                <a href="/publication/show/<?= $id ?>::<?= $alias ?>.html">
                    <h2 class="preview-title mobile-preview-title">
                        <strong>
                            <? if ($_GET['search']): ?>
                                <?= preg_replace('/(' . urldecode($_GET['search']) . ')/iu', '<mark>$1</mark>', htmlspecialchars_decode($title)) ?>
                            <?php else: ?>
                                <?= htmlspecialchars_decode($title) ?>
                            <?php endif; ?>
                        </strong>
                        <br>
                        <br>
                    </h2>
                    <img src="<?= $public_img ? $public_img : '/assets/uploads/img/not-available.jpg' ?>" alt="#"
                         class="publication-img-preview img-fluid <?= $special_content_category ? 'is-erotic' : '' ?>">
                </a>
                <div class="public-author-preview">
                    <div class="mask" style="background-color: rgba(0, 0, 0, .35);">
                        <div class="row align-items-center" style="color: #FFF;">
                            <div class="col-md-12">
                                <table style="width: 100%">
                                    <tr>
                                        <td>
                                            <?php if ($author_image): ?>
                                                <a href="/publication/authors/<?= $user_id . '::' . $author ?>/"
                                                   class="user-circle-img profile-public-img"
                                                   style="background-image:url('<?= $author_image ?>');">
                                                </a>
                                            <?php endif ?>
                                        </td>
                                        <td style="padding-left: 10px;">

                                            <a href="/publication/authors/<?= $user_id . '::' . $author ?>/"><small><b><?= $author ?></b></small></a>
                                            <div>
                                                <small><?= date_rus_format($published_date) ?></small>
                                            </div>
                                        </td>
                                        <td style="text-align: right">
                                            <i class='fa fa-eye' aria-hidden='true'></i>
                                            <small><?= $views ?></small>
                                            &nbsp;
                                            <i class='fa fa-heart<?= in_array($id, array_keys(Publication::$liked_publics)) ? '' : '-o' ?>'
                                               aria-hidden='true'></i>
                                            <small><?= $likes ?></small>
                                            &nbsp;
                                            <a href="/publication/show/<?= $id ?>::<?= $alias ?>.html#comments">
                                                <i class='fa fa-comment' aria-hidden='true'></i>
                                                <small><?= $comment_count ?></small>
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
        <span class="badge bg-danger px-2 py-1 shadow-1-strong mb-3">
            <a href="/publication/category/<?= $category_id ?>/<?= translit($category) ?>/"><?= $category ?></a>
        </span>
            <a href="/publication/show/<?= $id ?>::<?= $alias ?>.html" class="preview-title desktop-preview-title">
                <h4><strong>
                        <? if ($_GET['search']): ?>
                            <?= preg_replace('/(' . urldecode($_GET['search']) . ')/iu', '<mark>$1</mark>', htmlspecialchars_decode($title)) ?>
                        <?php else: ?>
                            <?= htmlspecialchars_decode($title) ?>
                        <?php endif; ?>
                    </strong></h4>
            </a>
            <p class="text-muted">
                <?= htmlspecialchars_decode($introtext) ?>
            </p>
            <br>
            <br>
        </div>
        <br>
    </div>
</article>
