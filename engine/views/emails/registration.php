<?php
/**
 * Письмо для завешения процесса регистрации на сайте
 * @var $user_id integer
 * @var $token string
 */
?>
<div>
    <h2>Спасибо за регистрацию на сайте!</h2>
    <p>
        Для окончания процесса регистрации пройдите по ссылке
        <a href="https://mtuci.mealton.ru/profile/registration_confirm/<?= $user_id ?>/<?= $token ?>/index.html">
            Завершить регистрацию
        </a>
    </p>
</div>
