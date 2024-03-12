<?php
/**
 * Страница Регистарции
 * @var $registration_fields array
 * @var $uploader string
 */
?>
<section>
    <form action="/profile/create.html" method="post" id="registration-form">
        <div class="container">

            <div class="row justify-content-center">

                <div class="col-12 col-md-8 col-lg-8 col-xl-6">
                    <div class="row">
                        <div class="col text-center">
                            <p class="text-h3">Пожалуйста, заполените поля ниже</p>
                        </div>
                    </div>

                    <?php foreach ($registration_fields as $item): ?>
                        <div class="row align-items-center">
                            <div class="col mt-4 field-item">

                                <?php if ($item['name'] == "password") include_once __DIR__ . '/password-extras.php' ?>

                                <input type="<?= $item['type'] ?>" name="<?= $item['name'] ?>" class="form-control"
                                       placeholder="<?= $item['placeholder'] . ($item['required'] ? '*' : '') ?>" <?= $item['required'] ? 'required' : '' ?> />
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="row align-items-center">
                        <div class="col mt-4">
                            <?= $uploader ?>
                            <input type="hidden" name="profile_image" class="form-control">
                        </div>
                    </div>
                    <div class="row align-items-center">
                        <div class="col mt-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="gender" value="M" id="male" checked>
                                <label class="form-check-label" for="male">
                                    Мужчина
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="gender" value="F" id="female">
                                <label class="form-check-label" for="female">
                                    Женщина
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="row justify-content-start mt-4">
                        <div class="col">
                            <div class="form-check">
                                <label class="form-check-label">
                                    <input type="checkbox" class="form-check-input" required>
                                    Я прочитал(а) и принимаю
                                    <a href="/politics.html" target="_blank"><ins>политику конциденциальности</ins>*</a>
                                </label>
                            </div>

                            <button type="submit" class="btn btn-primary mt-4">Зарегистрироваться</button>

                            <a href="/profile/login.html">
                                <button type="button" class="btn btn-outline-warning btn-block mt-4">Авторизоваться</button>
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>
</section>
