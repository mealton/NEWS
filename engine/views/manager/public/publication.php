<?php
/**
 * Полный текст публикации
 * @var $publication_header string
 * @var $publication_content string
 * @var $moderated boolean
 * @var $publication_id integer
 * @var $comment_form string
 * @var $comments string
 */
?>
<section>
    <?= $publication_header ?>

    <!-- Post content-->
    <section class="mb-5">
        <?= $publication_content ?>
    </section>
</section>
<hr>
<?php if(!$moderated):?>
<button class="btn btn-success" data-id="<?= $publication_id ?>" onclick="manager.moderatePublication(this)">Одобрить публикацию</button>
<?php else:?>
<button class="btn btn-danger"  data-id="<?= $publication_id ?>" onclick="manager.moderatePublication(this)">Отозвать публикацию</button>
<?php endif?>
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
