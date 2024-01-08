<?php
/**
 * Элемент хлебных крошек
 * @var $is_active boolean
 * @var $name string
 * @var $id integer
 */
session_start();
?>
<?php if ($is_active): ?>
    <li class="breadcrumb-item active" aria-current="page" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
        <span itemprop="item">
            <span itemprop="name"><?= htmlspecialchars_decode($name) ?></span>
            <meta itemprop="position" content="<?= $_SESSION['breadcrumb-position']++ ?>">
        </span>
    </li>
<?php else: ?>
    <li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
        <a itemprop="item" href="/publication/category/<?= $id ?>/<?= translit($name) ?>/">
            <span itemprop="name"><?= htmlspecialchars_decode($name) ?></span>
            <meta itemprop="position" content="<?= $_SESSION['breadcrumb-position']++ ?>" >
        </a>
    </li>
<?php endif; ?>


