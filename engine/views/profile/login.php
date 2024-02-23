<?php
/**
 * Страница авторизации
 */

//pre($_SESSION['auth'] );

?>
<section>
    <form action="/profile/auth" method="post">
        <div class="container">

            <div class="row justify-content-center">

                <div class="col-12 col-md-8 col-lg-8 col-xl-6">

                    <?php if ($_SESSION['auth']['error']): ?>
                        <div class="invalid-feedback" style="display: block"><?= $_SESSION['auth']['message'] ?></div>
                        <br>
                    <?php endif; ?>


                    <div class="row align-items-center">
                        <div class="col mt-4 field-item">
                            <label class="form-label" for="form2Example1">Имя пользователя</label>
                            <input type="text" name="username" id="form2Example1" class="form-control"
                                   value="<?= $_SESSION['auth']['input-login'] ?>"/>
                        </div>
                    </div>
                    <div class="row align-items-center">
                        <div class="col mt-4">
                            <label class="form-label" for="form2Example2">Пароль</label>
                            <div class="field-item login" >
                                <?php include_once __DIR__ . '/password-extras.php'?>
                                <input type="password" name="password" id="form2Example2" class="form-control"
                                       value="<?= $_SESSION['auth']['input-password'] ?>"/>
                            </div>

                        </div>
                    </div>
                    <br>
                    <div class="row mb-4 ">
                        <div class="col d-flex justify-content-center">
                            <!-- Checkbox -->
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember-user"
                                       id="form2Example31" checked />
                                <label class="form-check-label" for="form2Example31"> Запомнить </label>
                            </div>
                        </div>

                        <div class="col">
                            <!-- Simple link -->
                            <a href="/profile/forgot.html">Забыли пароль</a>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block mb-4">Авторизоваться</button>
                    <a href="/profile/registration.html">
                        <button type="button" class="btn btn-outline-warning btn-block mb-4">Зарегистрироваться</button>
                    </a>

                </div>

            </div>
        </div>
    </form>
</section>






