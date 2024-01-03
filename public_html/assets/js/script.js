let main = {
    swipeStartX: 0,
    swipeStartY: 0,

    swipe(e) {
        let swipeX = e.targetTouches[0].clientX;
        let swipeY = e.targetTouches[0].clientY;
        let distance = 50;

        let modalIsOpen = document.querySelector('.custom-modal') !== null;

        if (modalIsOpen) {
            if (swipeX < this.swipeStartX - distance && Math.abs(swipeY - this.swipeStartY) < distance / 2)
                return publication.nextModal();
            else if (swipeX > this.swipeStartX + distance && Math.abs(swipeY - this.swipeStartY) < distance / 2)
                return publication.prevModal();
            else if (swipeY < this.swipeStartY - distance)
                return publication.closeModal();
            else if (swipeY > this.swipeStartY + distance)
                return publication.closeModal(true);
        } else {
            let sideBar = document.querySelector('aside');
            if (swipeX < this.swipeStartX - distance && Math.abs(swipeY - this.swipeStartY) < distance / 2)
                return sideBar.classList.add('visible');
            else if (swipeX > this.swipeStartX + distance && Math.abs(swipeY - this.swipeStartY) < distance / 2)
                return sideBar.classList.remove('visible');
        }

    },

    swipeInit(e) {
        this.swipeStartX = e.targetTouches[0].clientX;
        this.swipeStartY = e.targetTouches[0].clientY;
    },
};



$(document).ready(() => {


    if (screen.width < 768) {
        window.addEventListener('touchstart', main.swipeInit, {passive: false});
        window.addEventListener('touchmove', main.swipe, {passive: false});
    }



    $(document).on('click', function (e) {
        if(!$(e).closest('button, input[type="submit"]').length){
            $('.invalid-feedback').hide().html('');
            $('input, textarea').removeClass('is-invalid');
        }
    });

    $("#button-search").on('click', function () {
        let input = this.parentElement.querySelector('input[name="search"]');
        let invalidFeedback = this.parentElement.querySelector('.invalid-feedback');
        let searchValue = input.value;
        if (!searchValue){
            input.classList.add('is-invalid');
            invalidFeedback.innerHTML = "Вы не заполнили поле поиска...";
            return false;
        }

        return location.href = `/publication/search/?search=${searchValue}`;
    });

    $('.password-generator').on('click', function () {
        let form = $(this).closest('form');
        let data = {
            method: 'password_generator'
        };
        let callback = response => {
            console.log(response);
            form.find('input[name="password"], input[name="confirm_password"]').val(response.password);
        };
        ffetch('/profile', callback, data);
    });

    $('.search-form__input').on('keyup', function (e) {

        let helper = $(this).siblings('.search-helper').find('ul');

        if(!this.value)
            return helper.html('');

        if (this.value.length < 3)
            return false;
        else if ([38, 40].includes(e.keyCode) && helper.html()) {

            let activeItem = helper.find('li.selected');
            let newActiveItem;
            if (e.keyCode === 38) {
                if (!activeItem.length || !activeItem.prev().length)
                    newActiveItem = helper.find('li').last();
                else
                    newActiveItem = activeItem.prev();

            } else if (e.keyCode === 40) {
                if (!activeItem.length || !activeItem.next().length)
                    newActiveItem = helper.find('li').first();
                else
                    newActiveItem = activeItem.next();
            }
            newActiveItem
                .addClass('selected')
                .siblings('li').removeClass('selected');
            this.value = newActiveItem.text();
            return true;
        } else if (e.keyCode === 13)
            return location.href = helper.find('li.selected').length
                ? helper.find('li.selected a').attr('href')
                : `/publication/search/?search=${this.value}`;

        let data = {
            search: this.value,
            method: 'keyup_search'
        };
        let callback = response => {
            console.log(response);
            helper.html(response.join(''));

            $(document.body).on('click', e => {
                if(!$(e.target).closest('.search-form-group').length)
                    return helper.html('');
            });
        };
        ffetch('/publication', callback, data);
    });


    $('.show-password').on('click', function () {
        let form = $(this).closest('form');
        let inputs = form.find('input[name="password"], input[name="confirm_password"]');
        if (this.classList.contains('fa-eye-slash')) {
            this.title = 'Отображать пароль';
            this.className = 'fa fa-eye';
            inputs.attr('type', 'password');
        } else {
            this.title = 'Скрыть пароль';
            this.className = 'fa fa-eye-slash';
            inputs.attr('type', 'text');
        }
    });


    $('.profile-area img').on('click', function () {
        let popup = this.nextElementSibling;
        if (popup.classList.contains('visible'))
            popup.classList.remove('visible');
        else
            popup.classList.add('visible');

        $(document.body).on('click', e => {
            if (!$(e.target).closest('.pop-up, .profile-area img').length && popup.classList.remove('visible'))
                popup.classList.remove('visible');
        });
    });


    window.onload = () => {
        document
            .querySelectorAll('img.publication-img-preview')
            .forEach(img => img.src = img.naturalHeight ? img.src : '/assets/uploads/img/not-available.jpg')
    };
    window.onscroll = () => {
        let scrollTop = this.pageYOffset;
        let lift = document.getElementById('lift');
        let liftOnclick = e => e.target.classList.contains('visible') ? this.scrollTo({top: 0}) : false;
        if (scrollTop > 1000) {
            lift.classList.add('visible');
            lift.addEventListener("click", liftOnclick);
        } else {
            lift.classList.remove('visible');
            lift.removeEventListener('click', liftOnclick);
        }

       /* let navBar = $('#navbarSupportedContent');
        let navBarToggler = $('.navbar-toggler[data-target="#navbarSupportedContent"]');

        if(navBar.hasClass('show') && !navBarToggler.hasClass('collapsed'))
            navBarToggler.click();*/




    };

    document.oncopy = addLink;


    $('form#date-range-form').on('submit', function (e) {
        e.preventDefault();
        let from = this.elements.from.value;
        let to = this.elements.to.value;
        return location.href = `/publication/date/${from}/${to}`;
    });

    //САЙДБАР
    if(screen.width < 992){
        let sideBar = document.querySelector('aside');
        let headerHeight = $('.container-body > header').outerHeight();
        $(sideBar).css({
            top: headerHeight + 'px',
            height: (screen.height - headerHeight) + 'px',
        });

        $('#sidebar-toggler').on('click', () => {
            if(sideBar.classList.contains('visible'))
                return sideBar.classList.remove('visible');
            else
                return sideBar.classList.add('visible');
        });
    }




    setInterval(() => {
        let date = new Date();
        let hours = date.getHours();
        if (+hours < 10)
            hours = '0' + hours;
        let minutes = date.getMinutes();
        if (+minutes < 10)
            minutes = '0' + minutes;
        // let seconds = date.getSeconds();
        // if (+seconds < 10)
        //     seconds = '0' + seconds;

        let timer = document.getElementById('time')
        let time = timer.innerHTML;
        let currentTime = `${hours}:${minutes}`;

        if(time !== currentTime)
            timer.innerHTML = currentTime;
    }, 1000);


});