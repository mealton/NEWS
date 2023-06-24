<?php
/**
 * Элемент хлебных крошек
 */
session_start();
?>
<?php if ($is_active): ?>
    <li class="breadcrumb-item active" aria-current="page" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
        <a itemprop="item">
            <span itemprop="name"><?= $name ?></span>
            <meta itemprop="position" content="<?= $_SESSION['breadcrumb-position']++ ?>">
        </a>
    </li>
<?php else: ?>
    <li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
        <a itemprop="item" href="/publication/category/<?= $id ?>/<?= $name ?>/">
            <span itemprop="name"><?= $name ?></span>
            <meta itemprop="position" content="<?= $_SESSION['breadcrumb-position']++ ?>" />
        </a>
    </li>
<?php endif; ?>


