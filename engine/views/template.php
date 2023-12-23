<!doctype html>
<html lang="en">
<head>
    <?php include_once __DIR__ . '/components/head.php' ?>
</head>
<body>
<div data-image='<?= $components['data_image'] ?>'></div>

<div class="container-body">

    <header>
        <?= $components['nav'] ?>
    </header>

    <div class="slider">
        <?= $components['slider'] ?>
    </div>

    <div class="container main-container">
        <div class="container mt-5">
            <div class="row">
                <main class="col-lg-9">
                    <div class="row">
                        <div class="col-md-12">
                            <?= $components['breadcrumb'] ?>
                        </div>
                    </div>
                    <?= $components['subcategories'] ?>
                    <?= $content ?>
                </main>
                <!-- Side widgets-->
                <aside class="col-lg-3">
                    <!-- Search widget-->
                    <div class="card mb-4">
                        <div class="card-header">Поиск</div>
                        <div class="card-body">
                            <div class="input-group position-relative search-form-group">
                                <input class="form-control search-form__input" name="search" type="text"
                                       placeholder="Поиск по названию"
                                       autocomplete="off"
                                       required
                                       aria-label="Enter search term..." aria-describedby="button-search">
                                <button class="btn btn-primary" id="button-search" type="button">
                                    <i class='fa fa-search' aria-hidden='true'></i>
                                </button>
                                <div class="invalid-feedback"></div>
                                <div class="search-helper">
                                    <ul class="list-group"></ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Categories widget-->
                    <div class="card mb-4">
                        <div class="card-header">Рубрики</div>
                        <div class="card-body">
                            <div class="row categories">
                                <div class="col-sm-6 categories__item-container">
                                    <ul class="list-unstyled mb-0">
                                        <?= $components['categories_left'] ?>
                                    </ul>
                                </div>
                                <div class="col-sm-6 categories__item-container">
                                    <ul class="list-unstyled mb-0">
                                        <?= $components['categories_right'] ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Side widget-->
                    <!--div class="card mb-4">
                        <div class="card-header">Side Widget</div>
                        <div class="card-body">You can put anything you want inside of these side widgets. They are easy to
                            use,
                            and feature the Bootstrap 5 card component!
                        </div>
                    </div-->
                    <!-- Похожие публикации-->
                    <?php if ($components['similar']): ?>
                        <div class="card mb-4">
                            <div class="card-header">Похожие публикации</div>
                            <div class="card-body similar-publications">
                                <?= $components['similar'] ?>
                            </div>
                        </div>
                    <?php endif ?>

                    <?php if ($components['sidebar-publics']): ?>
                        <div class="card mb-4">
                            <div class="card-header">Это интересно</div>
                            <div class="card-body sidebar-publics">
                                <?= $components['sidebar-publics'] ?>
                            </div>
                        </div>
                    <?php endif ?>
                </aside>
            </div>
        </div>
    </div>
    <?= $components['footer'] ?>
</div>

<div id="lift"></div>

<script>
    let UPLOAD_VIDEO_MAX_SIZE = <?= $GLOBALS['config']['uploads']['upload_video_max_size'] ?>;
</script>


<script src="/vendors/jquery/jquery-3.2.1.min.js"></script>
<script src="/vendors/jquery/jquery-ui.min.js"></script>
<script src="/vendors/bootstrap-5.3.0-alpha3-dist/js/bootstrap.bundle.js"></script>
<script src="/vendors/bootstrap-4.0.0/js/bootstrap.min.js"></script>
<script src="/assets/js/lib.js"></script>
<script src="/assets/js/cookie.js"></script>
<script src="/assets/js/uploader.js"></script>
<script src="/assets/js/script.js"></script>
<?php foreach ((array)$components['extra-scripts'] as $script): ?>
    <script src="/assets/js/<?= $script ?>.js"></script>
<?php endforeach; ?>
</body>
</html>