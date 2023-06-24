<?php
$active_slide = array_shift($publications_slider);
?>

<div class="top-carousel-slider container">
    <div class="filter__cover"></div>
    <div id="carouselExampleDark" class="carousel carousel-dark slide" data-bs-ride="carousel">
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#carouselExampleDark" data-bs-slide-to="0" class="active"
                    aria-current="true" aria-label="Slide <?= $active_slide['id'] ?>"></button>
            <?php foreach ($publications_slider as $i => $item): ?>
                <button type="button" data-bs-target="#carouselExampleDark" data-bs-slide-to="<?= $i + 1 ?>"
                        aria-label="Slide <?= $item['id'] ?>"></button>
            <?php endforeach; ?>
        </div>
        <div class="carousel-inner">

            <div class="carousel-item active">
                <img src="<?= $active_slide['public_img'] ?>" class="d-block w-100" alt="...">
                <div class="carousel-caption d-none d-md-block">
                    <h5>
                        <a href="/publication/show/<?= $active_slide['id'] ?>::<?= $active_slide['alias'] ?>.html" title="Перейти в публикацию">
                            <?= html_entity_decode($item['title']) ?>
                        </a>
                    </h5>
                    <p><?= html_entity_decode($active_slide['introtext']) ?></p>
                    <hr>
                </div>
            </div>

            <?php foreach ($publications_slider as $i => $item): ?>
                <div class="carousel-item">
                    <img src="<?= $item['public_img'] ?>" class="d-block w-100" alt="...">
                    <div class="carousel-caption d-none d-md-block">
                        <h5>
                            <a href="/publication/show/<?= $item['id'] ?>::<?= $item['alias'] ?>.html" title="Перейти в публикацию">
                                <?= html_entity_decode($item['title']) ?>
                            </a>
                        </h5>
                        <p><?= html_entity_decode($item['introtext']) ?></p>
                        <hr>
                    </div>
                </div>
            <?php endforeach; ?>

        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleDark" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleDark" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
</div>


