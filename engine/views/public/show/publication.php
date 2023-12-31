<!-- Post header-->
<div itemscope itemtype="http://schema.org/Article" id="publication-body">
    <?= $publication_header ?>
    <!-- Preview image figure-->

    <!-- Post content-->
    <div class="mb-5" id="publication-content">
        <?= $publication_content ?>
    </div>

    <?php if ($source): ?>
        <div class="mb-5">
            <hr>
            <p><b>Источник:</b> <a href="<?= $source ?>" target="_blank"><?= get_from_url($source) ?></a></p>
        </div>
    <?php endif; ?>

    <?php session_start();
    if ($user_id == $_SESSION['user']['id']): ?>
        <div class="mb-5">
            <a href="/profile/user/<?= $_SESSION['user']['id'] ?>/profile-page.html?tab=publications&page=&publication_id=<?= $publication_id ?>">
                <button class="btn btn-primary">Редактировать</button>
            </a>
        </div>
    <?php endif; ?>

</div>
<!-- Comments section-->
<section class="mb-5">
    <div class="card bg-light">
        <div class="card-body">
            <!-- Comment form-->
            <?= $comment_form ?>
            <!-- Comment with nested comments-->
            <div>
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
            </div>


        </div>
    </div>
</section>

