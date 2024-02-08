<?php
/**
 * Информация о пользователе на странице администрирования
 * @var $username string
 * @var $registration_date string
 * @var $profile_image string
 * @var $uploader string
 * @var $fullname string
 * @var $id integer
 * @var $registration_token string
 * @var $email string
 * @var $about string
 * @var $gender string
 */
?>
<p><b><?= $username ?></b> Зарегистрирован: <?= date_rus_format($registration_date) ?> года</p>
<form id="update-profile-form" action="/profile/update">
    <div class="row">
        <div class="col-md-4 border-right">
            <div class="d-flex flex-column align-items-center text-center p-3 py-5">
                <img alt="" class="img-fluid" id="profile_image" src="<?= $profile_image ?>">
                <br>
                <?= $uploader ?>
            </div>
        </div>
        <div class="col-md-4 border-right">
            <div class="p-3 py-5">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="text-right">Данные пользователя</h4>
                </div>
                <div class="row mt-2">
                    <div class="col-md-12"><label class="labels">Фаимлия Имя Отчество</label>
                        <input name="fullname" type="text" class="form-control" placeholder="ФИО"
                               value="<?= $fullname ?>">
                    </div>
                </div>
                <div class="row mt-3">

                    <input type="hidden" name="id" value="<?= $id ?>">
                    <input type="hidden" name="registration_token" value="<?= $registration_token ?>">

                    <div class="col-md-12"><label class="labels">Email</label>
                        <input name="email" type="email" class="form-control" placeholder="email"
                               value="<?= $email ?>">
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12"><label class="labels">О себе</label>
                        <textarea style="width: 100%; !important; height: 124px !important;" name="about"
                                  class="form-control" placeholder="О себе"><?= $about ?></textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 py-5">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="text-right">Смена пароля</h4>
                </div>
                <div class="row mt-2">
                    <div class="col-md-12"><label class="labels">Пароль</label>
                        <div class="field-item">
                            <?php include_once dirname(__DIR__) . '/password-extras.php' ?>
                            <input type="password" data-type="password" name="password" disabled
                                   class="form-control" placeholder="пароль"/>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12"><label class="labels">Повторите пароль</label>
                        <input type="password" data-type="password" name="confirm_password" disabled
                               class="form-control" placeholder="пароль"/>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" name="changePasswordCheckbox" type="checkbox"
                                   id="changePasswordCheckbox" value="">
                            <label class="form-check-label" for="changePasswordCheckbox">Изменить пароль</label>
                        </div>
                    </div>
                </div>
                <div class="row align-items-center">
                    <div class="col mt-4">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="gender" value="M"
                                   id="male" <?= $gender == 'M' ? 'checked' : '' ?> >
                            <label class="form-check-label" for="male">
                                Мужчина
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="gender" value="F"
                                   id="female" <?= $gender == 'F' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="female">
                                Женщина
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <hr>
        <div align="right">
            <button class="btn btn-primary profile-button" type="submit" disabled>Изменить профиль</button>
            <button class="btn btn-danger" id="delete-profile" data-id="<?= $id ?>"
                    data-token="<?= $registration_token ?>" type="button">Удалить профиль
            </button>
        </div>
    </div>
</form>
