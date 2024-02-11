const uploader = {

    action: '/uploader',

    uploadFile(input) {
        Object.values(input['files']).forEach(file => {
            let reader = new FileReader();
            let self = this;
            this.previews = $(input).closest('.uploader-container').next('.uploader-previews');
            this.previews.html('');
            reader.onload = () => self.uploadUrl(input, reader.result);
            reader.readAsDataURL(file);
        });
    },

    uploadVideoFile(input) {
        this.previews = $(input).closest('.uploader-container').next('.uploader-previews');
        let file = input['files'][0];
        if (file.size > UPLOAD_VIDEO_MAX_SIZE)
            return this.previews.html(`<p class="preview-item">Пожалуйста, не загружайте видео более 20 мегабайт.</p>`);
        let reader = new FileReader();
        input.parentElement.classList.add('preloader');
        reader.onload = () => this.uploadVideo(input, reader.result);
        reader.readAsDataURL(file);
    },

    uploadPoster(input) {
        let file = input['files'][0];
        let reader = new FileReader();
        reader.onload = () => {
            let data = {
                method: 'upload',
                url: reader.result,
                folder: "posters"
            };
            let callback = response => {
                let video = $(input).closest('.uploader-container').next('.uploader-previews').find('video');
                console.log(response, video[0]);

                if(video[0] === undefined)
                    video = $(input).closest('.publication__item').find('video');

                video.prop({poster: response.src});
            };
            ffetch(this.action, callback, data);
        };
        reader.readAsDataURL(file);
    },

    uploadVideo(input, base64) {
        let data = {
            method: 'upload_video_file',
            base64: base64,
            folder: $(input).closest('.uploader-container').find('input[name="upload_folder"]').val()
        };
        let callback = response => {
            console.log(response);
            input.parentElement.classList.remove('preloader');
            if (response.result) {
                this.previews.html(response.preview);
                $(input).closest('.uploader-container').find('input[name="url"]').val(response.src);
                // $(input).closest('.uploader-container').find('.poster-uploader')
                //     .removeClass('disabled')
                //     .prop({title: 'Загрузить постер'});
            } else
                this.previews.html(`<p class="preview-item">Ошибка загрузки</p>`);

        };
        ffetch(this.action, callback, data);
    },

    uploadUrl(input, base64 = false) {
        let data = {
            method: 'upload',
            url: base64 ? base64 : input.value,
            folder: $(input).closest('.uploader-container').find('input[name="upload_folder"]').val()
        };
        console.log(input, data);
        let callback = response => {
            console.log(response);
            $('.upload-url, .upload-file').removeClass('preloader');
            if (response.result) {
                this.previews.append(response.preview);
                $('#update-user').prop({disabled: 0});
            } else
                this.previews.html(`<p class="preview-item">Ошибка загрузки</p>`);
        };
        ffetch(this.action, callback, data);
    },

    remove(icon) {
        let element = icon.previousElementSibling;
        let input = $(icon).closest('.uploader-previews').prev('.uploader-container').find('input[name="url"]');
        let data = {
            method: 'remove',
            src: element.getAttribute('src'),
            folder: $(input).closest('.uploader-container').find('input[name="upload_folder"]').val()
        };
        let callback = response => {
            console.log(response);
            //if (response.result) {
            element.parentElement.remove();
            input.val('');
            $('#update-user').prop({disabled: 1});
            // }
        };
        ffetch(this.action, callback, data, () => console.log('Error'));
    },

    preUploadUrl(btn) {
        btn.classList.add('preloader');
        uploader.previews = $(btn).closest('.uploader-container').next('.uploader-previews');
        uploader.previews.html('');
        uploader.uploadUrl(btn.previousElementSibling);
    },

    init() {
        document.querySelectorAll('input[name="url"]').forEach(input => {

            if (input.getAttribute('data-onpaste') === null) {
                input.addEventListener("paste", function (e) {
                    if (e.clipboardData) {

                        let items = e.clipboardData.items;
                        if (items && !this.value) {
                            // находим изображение
                            for (let i = 0; i < items.length; i++) {
                                if (items[i].type.indexOf("image") !== -1) {
                                    // представляем изображение в виде файла
                                    let blob = items[i].getAsFile();
                                    // создаем временный урл объекта
                                    let URLObj = window.URL || window.webkitURL;
                                    //let source = URLObj.createObjectURL(blob);

                                    let reader = new FileReader();
                                    reader.readAsDataURL(blob);

                                    // добавляем картинку в DOM
                                    reader.onloadend = () => {
                                        let btn = this.nextElementSibling;
                                        btn.classList.add('preloader');
                                        uploader.previews = $(this).closest('.uploader-container').next('.uploader-previews');
                                        //uploader.previews.html('');
                                        uploader.uploadUrl(this, reader.result);
                                        e.clipboardData.clearData("Text");
                                    };
                                }
                            }
                        }
                    }
                    input.setAttribute('data-onpaste', '1');
                });
            }
        });
    }

};

$(document).ready((e) => {
    uploader.init();
    if(typeof e.preventDefault === 'function')
        e.preventDefault()
});