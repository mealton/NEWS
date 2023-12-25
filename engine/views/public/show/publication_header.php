<header class="mb-4">
    <article>
        <!-- Post title-->
        <h1 itemprop="headline" class="fw-bolder mb-1"><?= $media_title ?></h1>

        <!--schema.org-->
        <div id="meta-data" style="display: none" itemscope itemtype="https://schema.org/Article">
            <link itemprop="mainEntityOfPage" href="<?= get_current_url() ?>" />
            <link itemprop="image" href="<?= $data_image ?>">
            <meta itemprop="headline name" content="<?= $title ?>">
            <meta itemprop="description" content="<?= $introtext ?>">
            <p itemprop="articleBody"><?= $introtext ?></p>
            <meta itemprop="author" content="<?= $author ?>">
            <meta itemprop="datePublished" datetime="<?= $published_date ?>" content="<?= $published_date ?>">
            <meta itemprop="dateModified" datetime="<?= $published_date ?>" content="<?= $published_date ?>">
            <div itemprop="publisher" itemscope itemtype="https://schema.org/Organization">
                <div itemprop="logo" itemscope itemtype="https://schema.org/ImageObject">
                    <img itemprop="url image" src="https://mtuci.mealton.ru/assets/uploads/img/logo.png" alt="Описание картинки" title="Описание картинки" style="display:none;"/>
                </div>
                <meta itemprop="name" content="<?= $GLOBALS['config']['site']['sitename'] ?>">
                <meta itemprop="telephone" content="">
                <meta itemprop="address" content="Россия">
            </div>
            <div itemprop="articleBody">
                <?= $publication_content ?>
            </div>
        </div>



        <span style="display: none;" itemprop="author"><?= $author ?></span>


        <!-- Post meta content-->
        <div class="row justify-content-between">
            <div class="col-md-8">
                <br>
                <div class="text-muted fst-italic mb-2">
                    Опубликовано <?= date_rus_format($published_date) ?> пользователем
                    <a href="/publication/authors/<?= $user_id ?>::<?= $author ?>/"><b><?= $author ?></b></a>
                </div>
            </div>
            <div class="col-md-4 publication-counters" style="text-align: right">
                <div class="row">
                    <div class="col-md-4">
                        <i class='fa fa-eye' aria-hidden='true'></i>
                        <small><?= $views ?></small>
                    </div>
                    <div class="col-md-4 likes-block">
                        <?php session_start();
                        //Запрет на лайки своих публикакий и без авторизации
                        if ($_SESSION['user']['id'] && $_SESSION['user']['id'] != $user_id): ?>
                            <i class='fa fa-heart<?= $is_liked ? '' : '-o' ?> pointer'
                               data-id="<?= $publication_id ?>"
                               data-user="<?= $_SESSION['user']['id'] ?>"
                               onclick="publication.like(this)" aria-hidden='true'></i>
                        <?php else: ?>
                            <i class='fa fa-heart-o disabled' aria-hidden='true'></i>
                        <?php endif; ?>
                        <small class="position-relative overflow-hidden" id="likes-count">
                            <?= $likes ?>
                        </small>
                    </div>
                    <div class="col-md-4">
                        <a href="#comments">
                            <i class='fa fa-comment' aria-hidden='true'></i>
                            <small><?= $comment_count ?></small>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Post categories-->
        <?php // echo $tags ?>
        <?php foreach ((array)$hashtags as $hashtag): ?>
            <a class="badge bg-secondary text-decoration-none link-light"
               href="/publication/tags/<?= urlencode($hashtag) ?>"><?= $hashtag ?></a>
        <?php endforeach; ?>


</header>
