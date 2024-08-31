<?php
/**
 * Полный текст публикации
 * @var $publication_header string
 * @var $publication_content string
 * @var $subscribe_btn string
 * @var $source string
 * @var $user_id integer
 * @var $publication_id integer
 * @var $comment_form string
 * @var $comments string
 */

//pre($_SESSION['user']);
?>
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
        if ($user_id == $_SESSION['user']['id'] || $_SESSION['user']['is_admin']): ?>
            <div class="mb-5">
                <a href="/profile/user/<?= $_SESSION['user']['id'] ?>/profile-page.html?tab=publications&page=&publication_id=<?= $publication_id ?>">
                    <button class="btn btn-primary">Редактировать</button>
                </a>
            </div>
        <?php endif; ?>
        <?php if ($subscribe_btn): ?>
        <div class="mb-5">
        <?= $subscribe_btn ?>
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

<?php if ($_GET['search']): ?>
    <script>
        //Прокурутка до элеметка, содержащего поисковую подстроку
        setTimeout(() => {
            let publication = document.getElementById('publication-content');
            let elems = publication.querySelectorAll('p, h3');
            let matches = [];
            let word = "<?= urldecode($_GET['search']) ?>";
            elems.forEach(elem => {
                if (elem.innerHTML.toLowerCase().match(word.toLowerCase())) matches.push(elem);
            });
            let element = matches[0];
            let re = new RegExp(word, 'gi');
            element.innerHTML = element.innerHTML.replace(re, "<mark>$&</mark>");
            element.scrollIntoView({block: "center"});
        }, 100);
    </script>
<?php endif ?>