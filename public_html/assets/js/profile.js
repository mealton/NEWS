$(document).ready(() => {

    let profileForm = document.querySelector('form#update-profile-form');

    if(profileForm !== null){
        let profileDefaultValues = formExecute(profileForm);
        $(profileForm).find('input, textarea').on('keyup', function () {
            let newValues = formExecute(profileForm);
            let submitter = profileForm.querySelector('[type = "submit"]');
            for (let i in newValues) {
                if (newValues[i] !== profileDefaultValues[i]) {
                    submitter.disabled = false;
                    break;
                } else
                    submitter.disabled = true;
            }
        });
    }


    $('#changePasswordCheckbox').on('change', function () {
       let passwordInputs = $('input[data-type="password"]');
        passwordInputs.prop('disabled', !this.checked);
    });

    let action = '/profile';

    $('#forgot-form').on('submit', function (e) {
        e.preventDefault();
        let input = this.elements.email;
        let feedback = this.querySelector('.feedback');
        feedback.style.display = 'block';
        let data = {
            email: input.value.trim(),
            is_active: 1,
            method: 'forgot_send_email'
        };
        let callback = response => {
            console.log(response);

            if (!response.result) {
                input.className = `form-control is-invalid`;
                input.nextElementSibling.innerHTML = 'Извините, пользователь не найден...';
                feedback.className = 'feedback invalid-feedback';
            } else {
                input.className = `form-control is-valid`;
                input.nextElementSibling.innerHTML = 'Для смены пароля пройдите по ссылке, указанной в письме. Если письмо долго не приходит, пожалуйста, проверьте папку "Спам".';
                feedback.className = 'feedback valid-feedback';
            }
        };
        ffetch(action, callback, data);

    });

    $('#recovery-form').on('submit', function (e) {
        e.preventDefault();
        if (this.querySelector('input.is-invalid') !== null)
            return alert('Проверьте праильность заполнения всех полей');
        this.submit();
    });


    $('#myTab .nav-link').on('click', function () {
        let tab = this.id.replace('-tab', '');
        let publication_id = parseInt(this.dataset.publication_id);
        let publication_page = parseInt(this.dataset.publication_page);
        history.pushState('', '', '?tab=' + tab +
            (publication_id ? `&publication_id=${publication_id}` : '') +
            (publication_page ? `&page=${publication_page}` : ''));
    });

    $('#clear-history').on('click', function () {
        if (!confirm('Очистить историю посещений?'))
            return false;

        let data = {method: 'clear_history'};
        let callback = response => {
            console.log(response);
            if(!response.result)
                return false;

            this.className = 'fa fa-trash-o disabled';
            document.querySelector('#history-container').innerHTML = `<p>История посещений отсутствует...</p>`;
        };
        ffetch(action, callback, data);
    });

    //Обновить данные профиля
    $('#update-profile-form').on('submit', function (e) {
        e.preventDefault();
        let data = formExecute(this);
        let preview = $('.uploader-previews img').attr('src');

        if(preview)
            data.profile_image = preview;

        let submitter = this.querySelector('[type="submit"]');
        let submitterClassname = submitter.className;
        let submitterText = submitter.innerHTML;

        let passwordInput = this.elements.password;
        let passwordConfirmInput = this.elements.confirm_password;


        if(data.password && data.password.length < 5){
            validateInput(passwordInput, 'Пожалуйста, придумайте пароль, состоящий не менее, чем из 5 символов', true);
            delete data.password;
            return false;
        }
        if(data.password && data.password !== data.confirm_password){
            validateInput(passwordConfirmInput, 'Извините, пароль и подтверждение пароля не совпадают', true);
            delete data.password;
            return false;
        }

        if (!data.password || !this.elements.changePasswordCheckbox.checked)
            delete data.password;

        if (data.profile_image === undefined)
            delete data.profile_image;

        data.method = 'update';

        let callback = response => {
            console.log(response);
            if (response.result) {
                $('img#profile_image').attr('src', response.user.profile_image);
                $(this).find('input[name="password"], input[name="confirm_password"]').val('');
                submitter.className = 'btn btn-success profile-button';
                submitter.innerHTML = 'Изменено';
                $('.uploader-previews').html('');

                setTimeout(() => {
                    submitter.className = submitterClassname;
                    submitter.innerHTML = submitterText;
                    submitter.disabled = true;
                }, 2000);
            }

        };

        ffetch(action, callback, data);
    });

    $('#delete-profile').on('click', function () {
        if (confirm('Вы, действително, хотите удалить свой профиль?')) {
            let id = this.dataset.id;
            let token = this.dataset.token;
            location.href = `/profile/delete/${id}/${token}`;
        }
    });

    $('input[name="password"]').on('change', function () {
        let password = this.value.trim();
        validateInput(this, 'Пожалуйста, придумайте пароль, состоящий не менее, чем из 5 символов', password.length < 5);
    });

    $('input[name="confirm_password"]').on('change', function () {
        let password = $('input[name="password"]').val().trim();
        let confirm_password = this.value.trim();
        validateInput(this, 'Извините, пароль и подтверждение пароля не совпадают', password !== confirm_password)
    });

    $('#new-public-form').on('submit', function (e) {
        e.preventDefault();

    })

});