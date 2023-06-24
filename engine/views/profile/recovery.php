<section>
    <form action="/profile/recovery_user.html" method="post" id="recovery-form">
        <div class="container">

            <div class="row justify-content-center">

                <div class="col-12 col-md-8 col-lg-8 col-xl-6">
                    <div class="row">
                        <div class="col text-center">
                            <p class="text-h3">Введите новый пароль</p>
                        </div>
                    </div>

                    <input type="hidden" name="registration_token" value="<?= $registration_token ?>">
                    <input type="hidden" name="id" value="<?= $id ?>">

                    <?php foreach ($registration_fields as $item): ?>
                        <div class="row align-items-center">
                            <div class="col mt-4 field-item">
                                <?php include_once __DIR__ . '/password-extras.php'?>
                                <input type="<?= $item['type'] ?>" name="<?= $item['name'] ?>" class="form-control"
                                       placeholder="<?= $item['placeholder'] . ($item['required'] ? '*' : '')?>" <?= $item['required'] ? 'required' : '' ?> />
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="row justify-content-start mt-4">
                        <div class="col">
                            <button type="submit" class="btn btn-primary mt-4">Сохранить новый пароль</button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>
</section>
