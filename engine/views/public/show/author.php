<?php
/**
 * Информация об авторе
 * @var $user_id integer
 * @var $profile_image string
 * @var $registration_date string
 * @var $publication_count integer
 * @var $about string
 * @var $manager_controls string
 * @var $is_subscribed boolean
 */
?>
<div class="author" data-id="<?= $user_id ?>">
    <div class="row">
        <div class="col-md-4">
            <img src="<?= $profile_image ?>" alt="" class="img-fluid">
        </div>
        <div class="col-md-4">
            <p><b>Зарегистрирован:</b> <?= date_rus_format($registration_date) ?></p>
            <p><b>На сайте:</b> <?= getPeriod($registration_date) ?></p>
            <p><b>Публикаций:</b> <?= $publication_count ?></p>
            <?php if ($_SESSION['user']['id'] && $user_id != $_SESSION['user']['id']): ?>
                <p>
                    <?php if (!$is_subscribed): ?>
                        <button class="btn btn-primary" data-id="<?= $user_id ?>" onclick="publication.subscribe(this)">
                            Подписаться
                        </button>
                    <?php else: ?>
                        <button class="btn btn-secondary" data-id="<?= $user_id ?>"
                                onclick="publication.subscribe(this, 1)">Отписаться
                        </button>
                    <?php endif; ?>
                </p>
            <?php endif; ?>
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
