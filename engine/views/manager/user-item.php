<?php
/**
 * Элемент управления правами пользователя на странице администрирования
 * @var $id integer
 * @var $username string
 * @var $profile_image string
 * @var $gender string
 * @var $is_active boolean
 * @var $email string
 * @var $about string
 * @var $is_admin boolean
 */
$disabled = $_SESSION['user']['id'] == $id ? 'disabled' : ''; ?>

<div class="accordion-item user-item" data-id="<?= $id ?>" data-username="<?= $username ?>">
    <div class="accordion-header">
        <table class="table" style="margin: 0;">
            <tr>
                <td>
                    <?php if ($profile_image): ?>
                        <img class="user-circle-img" src="<?= $profile_image ?>" alt="" width="50">
                    <?php else: ?>
                        <img src="/assets/uploads/icons/profile/profile-<?= $gender ?>-default.jpg" alt="" width="50">
                    <?php endif; ?>
                </td>
                <td><?= $username ?></td>
                <td>
                    <div class="form-check">
                        <label>
                        <input class="form-check-input" type="checkbox" name="is_active"
                               onchange="manager.updateUser(this)"
                               <?= $disabled  ?>
                               <?= $is_active ? 'checked' : '' ?> />
                            Активен
                        </label>
                    </div>
                </td>
                <td>
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            style="background-color: #fff; border: none; box-shadow: none; outline: none"
                            data-bs-target="#collapse_user_<?= $id ?>" aria-expanded="true" aria-controls="collapse_user_<?= $id ?>">
                    </button>
                </td>
            </tr>
        </table>
    </div>
    <div id="collapse_user_<?= $id ?>" class="accordion-collapse collapse" aria-labelledby="headingOne"
         data-bs-parent="#accordionExample">
        <div class="accordion-body">
            <table class="table">
                <tbody>
                <tr>
                    <td><?= $email ?></td>
                    <td><?= $gender ?></td>
                    <td><?= $about ?></td>
                    <td>
                        <div class="form-check">
                            <label>
                            <input class="form-check-input" type="checkbox" name="is_admin"
                                   onchange="manager.updateUser(this)"
                                   <?= $disabled ?>
                                   <?= $is_admin ? 'checked' : '' ?> />
                                Права администратора
                            </label>
                        </div>
                    </td>
                    <td>
                        <select name="banned_period" class="form-control" onchange="manager.updateUser(this)" <?= $disabled ?>>
                            <option value="" selected disabled>Заблокировать</option>
                            <option value="86400">На сутки</option>
                            <option value="604800">На неделю</option>
                            <option value="2592000">На месяц</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td colspan="5" align="center">
                        <button class="btn btn-danger" <?= $disabled ?> onclick="manager.removeUser(this)">Удалить пользователя</button>
                    </td>
                </tr>
                </tbody>
            </table>

        </div>
    </div>
</div>