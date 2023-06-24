<div class="author" data-id="<?= $user_id ?>">
    <div class="row">
        <div class="col-md-4">
            <img src="<?= $profile_image ?>" alt="" class="img-fluid">
        </div>
        <div class="col-md-4">
            <p><b>Зарегистрирован:</b> <?= date_rus_format($registration_date) ?></p>
            <p><b>На сайте:</b> <?= getPeriod($registration_date) ?></p>
            <p><b>Публикаций:</b> <?= $publication_count ?></p>
        </div>
        <div class="col-md-4">
            <p><b>O себе:</b> <?= $about ?></p>
        </div>
    </div>
    <?php session_start();
    if ($_SESSION['user']['is_admin']): ?>
        <br>
        <div class="row">
            <div class="accordion" id="accordionExample">
                <?= $manager_controls ?>
            </div>
        </div>
    <?php endif ?>
</div>
