$(document).ready(() => {

    let action = '/profile';

    $('input[name="username"]').on('change', function () {
        let data = {
            username: this.value,
            method: 'check_username_is_unique'
        };
        let validate = true;
        let errorText;

        if (data.username.trim().length < 3) {
            if (!data.username.trim())
                errorText = "Пожалуйста, укажите имя пользователя";
            else
                errorText = "Имя пользователя слишком короткое";
            validate = false;
        } else if (data.username.match(/[а-я]/gi)) {
            errorText = "Имя пользователя не должно содержать кирилицу";
            validate = false;
        }

        if (!validate) {
            this.className = `form-control is-invalid`;
            this.nextElementSibling.innerHTML = errorText;
            this.nextElementSibling.style.display = 'block';
            return false;
        }

        let callback = response => {
            console.log(response);
            validateInput(this, 'Извините, данное имя пользователя уже существует. Придумайте другое.', response.result);

        };
        ffetch(action, callback, data);
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

    $('input[name="email"]').on('change', function () {
        let valid = this.value.match(/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/);
        if(!valid){
            validateInput(this, 'Проверьте правильность написания email', !valid);
            return false;
        }

        let data = {
            email: this.value,
            method: 'check_username_is_unique'
        };
        let callback = response => {
            console.log(response);
            validateInput(this, 'Извините, данный email уже зарегистрирован на другого пользвателя. Укажите другой.', response.result);
        };
        ffetch(action, callback, data);
    });

    $('#registration-form').on('submit', function (e) {
        e.preventDefault();
        if(this.querySelector('input.is-invalid') !== null)
            return alert('Проверьте праильность заполнения всех полей');
        this.elements.profile_image.value = $('.preview-item img').attr('src');
        this.submit();
    });

});