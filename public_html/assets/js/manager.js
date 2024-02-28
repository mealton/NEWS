let manager = {

    action: '/manager',

    updateCategory(item){
        let container = $(item).closest('.accordion-item.category-item');
        let data = {
            method: 'update_category',
            id: container[0].dataset.id,
            name: container.find('input[name="name"]').val(),
            is_active: container.find('input[name="is_active"]')[0].checked,
            is_hidden: container.find('input[name="is_hidden"]')[0].checked,
            parent_id: container.find('select[name="parent_id"]').val(),
            description: container.find('textarea[name="description"]').val(),
            keywords: container.find('input[name="keywords"]').val(),
        };
        console.log(data);
        let callback = response => {
            console.log(response);
        };
        ffetch(this.action, callback, data);
    },

    updateUser(item){
        let container = $(item).closest('.accordion-item.user-item');
        let data = {
            method: 'update_user',
            id: container[0].dataset.id,
            is_active: container.find('input[name="is_active"]')[0].checked,
            is_admin: container.find('input[name="is_admin"]')[0].checked,
            no_moderate: container.find('input[name="no_moderate"]')[0].checked
        };

        let banned_period = container.find('select[name="banned_period"]').val();

        if(banned_period){
            data.is_banned = 1;
            data.banned_period = banned_period;
        }

        let callback = response => {
            console.log(response);
        };
        ffetch(this.action, callback, data);
    },

    removeCategory(item){
        let container = $(item).closest('.accordion-item.category-item');
        let data = {
            method: 'remove_category',
            id: container[0].dataset.id
        };
        console.log(data);
        let callback = response => {
            console.log(response);
            if(response.result)
                container.remove();
        };
        ffetch(this.action, callback, data);
    },

    moderatePublication(btn){
        let data = {
            method: 'moderate_publication',
            moderated: btn.classList.contains('btn-success'),
            id: btn.dataset.id
        };
        console.log(data);
        let callback = response => {
            console.log(response);
            if(response.result[0].moderated){
                btn.className = 'btn btn-danger';
                btn.innerText = 'Отозвать публикацию';
            }else{
                btn.className = 'btn btn-success';
                btn.innerText = 'Одобрить публикацию';
            }
        };
        ffetch(this.action, callback, data);
    },

    removeUser(btn){
        let container = $(btn).closest('.accordion-item.user-item');
        let data = {
            method: 'remove_user',
            id: container[0].dataset.id
        };
        console.log(data);
        let callback = response => {
            console.log(response);
            if(response.result)
                container.remove();
        };

        if(confirm(`Вы действительно хотите удалить пользователя ${container[0].dataset.username}?`))
            ffetch(this.action, callback, data);
    }

};