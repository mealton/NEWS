<?php
/**
 * Письмо при восстановлении доступа на сайте
 * @var $user_id integer
 * @var $token string
 */
?>
<div>
    <h2>Восстановление учетной записи</h2>
    <p>
        Для восстановления пройдите по ссылке
        <a href="https://mtuci.mealton.ru/profile/recovery/<?= $user_id ?>/<?= $token ?>/index.html">
            сменить пароль
        </a>
    </p>
</div>
