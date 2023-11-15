<?php
/**
 * Навигация в шапке сайта
 */
session_start();
?>
<nav class="navbar navbar-expand-lg navbar-light bg-light" style="padding-left: 10px; padding-right: 10px;">

    <div class="container">

        <a class="navbar-brand" href="/">
            <img src="/assets/uploads/img/logo.png" alt="<?= $GLOBALS['config']['site']['sitename'] ?>" width="50">
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">


            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php foreach ($GLOBALS['config']['top-menu'] as $name => $item): ?>

                    <?php if (is_array($item['items'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" id="navbarDropdown-<?= $item['action'] ?>" role="button"
                               data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <?= $name ?>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="navbarDropdown-<?= $item['action'] ?>">
                                <?php foreach ($item['items'] as $item_name => $link): ?>
                                    <?php if ($item_name != 'divider'): ?>
                                        <a class="dropdown-item"
                                           href="/<?= $item['action'] . '/' . $link ?>"><?= $item_name ?></a>
                                    <?php else: ?>
                                        <div class="dropdown-divider"></div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </li>

                    <?php else: ?>
                        <li class="nav-item">
                            <?php if ($item == $action): ?>
                                <a class="nav-link active" aria-current="page"><?= $name ?></a>
                            <?php else: ?>
                                <a class="nav-link"
                                   href="/<?= $item ?>/index.html"><?= $name ?></a>
                            <?php endif; ?>
                        </li>
                    <?php endif; ?>

                <?php endforeach; ?>

                <?= $categories_dropdown ?>

                <li>
                    <div class="custom-date">
                        <div class="pa__top-sec">
                            <form class="pa__middle-range" id="date-range-form">
                                <div class="pa__middle-item">
                                    <input class="has-value" type="date" name="from" placeholder="От"
                                           value="<?= $date_from ? $date_from : $published_date_start ?>"
                                           min="<?= $published_date_start ?>" required>
                                </div>
                                <div class="pa__middle-item">
                                    <input class="has-value" type="date" name="to"
                                           placeholder="До"
                                           value="<?= $date_to ? $date_to : date('Y-m-d') ?>"
                                           max="<?= date('Y-m-d') ?>"
                                           required></div>
                                <div class="pa__middle-btn">
                                    <button class="btn btn-primary" type="submit">Выбрать по дате</button>
                                </div>
                            </form>
                        </div>
                    </div>

                </li>

            </ul>
            <small id="today-informer"><?= $today_info ?></small>
            <?= $profile_area ?>

        </div>
    </div>
</nav>
