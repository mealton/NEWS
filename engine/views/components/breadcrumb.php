<?php
/**
 * Хлебные крошки
 * @var $breadcrumb string
 */
?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb" itemscope itemtype="https://schema.org/BreadcrumbList">
        <li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
            <a href="/" itemprop="item">
                <span itemprop="name">Главная</span>
                <meta itemprop="position" content="0">
            </a>
        </li>
        <?= $breadcrumb ?>
    </ol>
</nav>
