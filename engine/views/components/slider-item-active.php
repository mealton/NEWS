<?/**
 * Элемент слайдера
 * @var $public_img string
 * @var $title string
 * @var $introtext string
 */
?>
<div class="carousel-item active">
    <img src="<?= $public_img ?>" class="d-block w-100" alt="...">
    <div class="carousel-caption d-none d-md-block">
        <h5><?= $title ?></h5>
        <p><?= $introtext ?></p>
    </div>
</div>
