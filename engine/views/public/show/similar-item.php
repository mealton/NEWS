<p>
    <small><?= html_entity_decode($title) ?></small>
</p>
<div class="bg-image hover-overlay ripple shadow-2-strong rounded-5 position-relative"
     data-mdb-ripple-color="light">
    <a href="/publication/show/<?= $id ?>::<?= $alias ?>.html">
        <img src="<?= $public_img ? $public_img : '/assets/uploads/img/not-available.jpg' ?>"
             class="publication-img-preview img-fluid <?= $category_id == 3 ? 'is-erotic' : '' ?>"/>
    </a>
    <a class="public-author-preview">
        <div class="mask" style="background-color: rgba(0, 0, 0, .35);">
            <div class="row align-items-center" style="color: #FFF;">
                <div class="col-md-12">                    
                    <table width="100%">
                        <tr>
                            <td style="text-align: right">
                                <i class='fa fa-eye' aria-hidden='true'></i>
                                <small><?= $views ?></small>
                                &nbsp;
                                <i class='fa fa-heart<?= in_array($id, Publication::$liked_publics) ? '' : '-o' ?>'
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
    </a>
</div>
<hr>
