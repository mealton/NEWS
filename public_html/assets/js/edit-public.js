const publication = {

    action: '/publication',

    setLikeWidth() {
        let likesBlock = $('#likes-count');
        let nextLikesSpanCount = likesBlock.find('.next-likes-count > span').length;
        let singeLikeSpanWith = $('.current-likes-count > span').first().width();
        //console.log(nextLikesSpanCount, singeLikeSpanWith);
        likesBlock.css({width: `${nextLikesSpanCount * singeLikeSpanWith}px`})
    },

    like(icon) {

        if (icon.dataset.disabled)
            return false;

        let data = {
            method: 'like',
            id: icon.dataset.id,
            user_id: icon.dataset.user,
        };

        let callback = response => {
            console.log(response);
            let likesCount = icon.nextElementSibling;
            let breakFlag = false;

            if (response.dislike && icon.classList.contains('fa-heart')) {
                let likes = (+response.likes + 1).toString();
                let likesArray = likes.split('');
                //считаем сколько нулей на конце
                let zeroCount = 0;
                likesArray.reverse().forEach(number => +number === 0 && !breakFlag ? zeroCount++ : breakFlag = true);
                let likeSpans = $(likesCount).find(`span.count > span:nth-last-child(-n+${zeroCount + 1})`);
                icon.className = 'fa fa-heart-o pointer';
                likeSpans.removeClass('slided-up').addClass('slided-down');
            } else if (!response.dislike && icon.classList.contains('fa-heart-o')) {

                let likes = (+response.likes - 1).toString();
                let likesArray = likes.split('');
                //считаем сколько девяток на конце
                let nineCount = 0;
                likesArray.reverse().forEach(number => +number === 9 && !breakFlag ? nineCount++ : breakFlag = true);
                let likeSpans = $(likesCount).find(`span.count > span:nth-last-child(-n+${nineCount + 1})`);

                icon.className = 'fa fa-heart pointer';
                likeSpans.removeClass('slided-down').addClass('slided-up');
            }

            //юлокироуем кнопку на время анимации
            icon.dataset.disabled = 1;

            setTimeout(() => {
                //снимаем блокировку
                icon.removeAttribute('data-disabled');
                let previousLikesCount = +response.likes - 1;
                let currentLikesCount = +response.likes;
                let nextLikesCount = +response.likes + 1;
                previousLikesCount = previousLikesCount < 0 ? 0 : previousLikesCount;
                //Перестраиваем блок лайков новыми значениями
                previousLikesCount = `<span>${previousLikesCount.toString().split('').join('</span><span>')}</span>`;
                currentLikesCount = `<span>${currentLikesCount.toString().split('').join('</span><span>')}</span>`;
                nextLikesCount = `<span>${nextLikesCount.toString().split('').join('</span><span>')}</span>`;
                likesCount.innerHTML =
                    `<span class="previous-likes-count count">${previousLikesCount}</span>
                            <span class="current-likes-count count">${currentLikesCount}</span>
                            <span class="next-likes-count count">${nextLikesCount}</span>`;
            }, 350);


        };
        ffetch(this.action, callback, data);
    },

    publication: {
        meta: {},
        content: {},
    },

    trash_cleaner(icon) {
        if (!confirm('Вы действительно хотите очистить корзину?'))
            return false;

        let data = {
            method: 'trash_cleaner'
        };

        let callback = response => {
            console.log(response);

            if (response.result) {
                $('#trash_cleaner').html('');
                //location.reload();
            }
        };
        ffetch(this.action, callback, data);
    },

    publish(checkbox) {
        let publication = $(checkbox).closest('.public-item-preview');
        let publication_id = publication[0].dataset.id;
        if (!parseInt(publication_id))
            return console.log('Не задан идентификатор публикации');

        let data = {
            method: 'publish',
            is_published: checkbox.checked,
            id: publication_id
        };
        let callback = response => {
            console.log(response);

            if (!response)
                return alert('Ошибка обновления статуса публикации...');

            if (parseInt(response.is_published))
                publication.removeClass('opacity-75');
            else
                publication.addClass('opacity-75');

            if (parseInt(response.is_deleted))
                publication.addClass('is-deleted');
            else
                publication.removeClass('is-deleted');
        };
        ffetch(this.action, callback, data);
    },

    delete(icon) {
        let publication = $(icon).closest('.public-item-preview');
        let publication_id = publication[0].dataset.id;
        if (!parseInt(publication_id))
            return console.log('Не задан идентификатор публикации');

        let data = {
            method: 'delete',
            is_deleted: icon.dataset.action === "delete" ? 1 : 0,
            id: publication_id
        };

        let callback = response => {
            console.log(response);

            if (!response)
                return alert('Ошибка обновления статуса публикации...');

            if (parseInt(response.publication.is_deleted)) {
                publication.addClass('is-deleted');
                icon.className = 'fa fa-trash pointer';
                icon.dataset.action = 'recovery';
            } else {
                publication.removeClass('is-deleted');
                icon.className = 'fa fa-trash-o pointer';
                icon.dataset.action = 'delete';
            }

            if (parseInt(response.publication.is_published))
                publication.removeClass('opacity-75');
            else
                publication.addClass('opacity-75');

            let trash_cleaner = $('#trash_cleaner');
            if (!response.trash_cleaner)
                trash_cleaner.html('');
            else
                trash_cleaner.html("<i class='fa fa-trash-o pointer' onclick='publication.trash_cleaner(this)' aria-hidden='true'></i>")

        };
        ffetch(this.action, callback, data);
    },

    imgDefault(checkbox) {
        let image_default = document.querySelector('input[name="image_default"]');
        if (!checkbox.checked)
            return image_default.value = "";

        let image = $(checkbox).closest('.publication__item').find('img.publication-image-item').attr('src');

        if (!image)
            alert('Сначала добавьте изображение');

        console.log(image);

        $(checkbox).closest('.publication__item').siblings().find('input[name="img-default"]').prop('checked', false);
        return image_default.value = image;
    },

    checkNewCategory(input) {
        let data = {
            category: input.value,
            method: 'check_new_category'
        };
        let callback = response => {
            console.log(response);
            validateInput(input, 'Извините, данная категория уже существует', !response.result, true);
        };
        ffetch(this.action, callback, data);
    },

    import(btn) {
        let url_input = $('input[name="url_import"]');
        let url = url_input.val();
        let content_container = $('input[name="import_public_container"]').val();
        let tags_container = $('input[name="import_public_hashtags_container"]').val();
        let only_img = $('input[name="only_img"]').prop('checked');
        let data = {
            url: url,
            content_container: content_container,
            tags_container: tags_container,
            only_img: only_img,
            method: 'import'
        };
        btn.classList.add('preloader');
        $(btn).closest('fieldset').find('input, button').prop('disabled', true);
        //console.log(data);
        let callback = response => {
            console.log(response);
            btn.classList.remove('preloader');
            $(btn).closest('fieldset').find('input, button').prop('disabled', false);
            let newPublicForm = document.getElementById('new-public-form');
            let publication = newPublicForm.elements;
            if (!response.publication)
                return false;

            publication.title.value = response.publication.title;
            publication.introtext.value = response.publication.introtext;
            publication.hashtags.value = response.publication.hashtags;
            publication.comment.value = response.publication.comment;
            let publication_body = $('#publication_body');
            publication_body.html(response.publication.content);
            uploader.init();
            $('.editor').hide();
            $('.editor-icon').addClass('non-active');
            publication_body.find('.publication__item-content video').prop('controls', 'controls');
            publication_body.find('.publication__item-content iframe').attr({
                width: "640",
                height: "360"
            });
        };

        let on_error = response => {
            console.log(response);
            btn.classList.remove('preloader');
            $(btn).closest('fieldset').find('input, button').prop('disabled', false);
            validateInput(url_input[0], 'Ошибка импорта...', true, true);
        };

        ffetch(this.action, callback, data, on_error);
    },

    setContent() {
        $('.publication__item').each((i, item) => {
            let tag = item.dataset.tag;
        });
    },

    cancel() {
        let data = {
            images: Object.values($('#publication_body').find('img').not('img[src="undefined"]')).map(img => img.src),
            method: 'cancel'
        };
        //console.log(data);
        let callback = response => {
            console.log(response);
            document.querySelector('#publication_body').innerHTML = "";
            document.getElementById('new-public-form').reset();
        };
        ffetch(this.action, callback, data);
    },

    getImportContainers(url) {
        let data = {
            url: url,
            method: 'get_import_containers'
        };
        let callback = response => {
            console.log(response);
            if (!response)
                return false;

            $('input[name="import_public_container"]').val(response.publication_container);
            $('input[name="import_public_hashtags_container"]').val(response.hashtags_container);

        };
        ffetch(this.action, callback, data);
    },

    getVideo(btn) {
        let item = $(btn).closest('.publication__item');
        let input = btn.previousElementSibling.querySelector('input[name="url"]');
        let url = input.value;

        if (!url) {
            validateInput(input, 'Вы не ввели URL', !url, 1);
            return false;
        } else {
            input.classList.remove('is-invalid');
            input.nextElementSibling.style.display = "none";
        }

        let videoId;
        let query = parseQuery(url);
        if (query.v)
            videoId = query.v;
        else
            videoId = url.trim().split('/').pop().split('?')[0];

        fetch(`https://www.youtube.com/oembed?url=https://www.youtube.com/watch?v=${videoId}&format=json`)
            .then(response => response.json())
            .then(result => $(item).find('input[name="description"]').val(result.title));

        let preview = `<div class="preview-item preview-item-video position-relative d-inline-block">
                            <img src="//img.youtube.com/vi/${videoId}/mqdefault.jpg" width="320" height="180" alt="">
                        </div>`;
        item.find('.uploader-previews').html(preview)
    },

    textStyle(btn) {
        let item = $(btn).closest('.publication__item');
        let content = item.find('.publication__item-content')[0];

        if (btn.dataset.prop === 'text-align') {
            item.find('.fa[data-prop="text-align"]').removeClass('active');
            btn.classList.add('active');
            content.style[btn.dataset.prop] = btn.dataset.style;
        } else if (['font-weight', 'font-style', 'text-decoration'].includes(btn.dataset.prop)) {
            if (btn.classList.contains('active')) {
                btn.classList.remove('active');
                content.style[btn.dataset.prop] = '';
            } else {
                btn.classList.add('active');
                content.style[btn.dataset.prop] = btn.dataset.style;
            }
        } else if (btn.dataset.prop === 'font-size') {
            let inputFontSize = item.find('input[name="fontsize"]');
            let fontSize = parseInt(inputFontSize.val());
            fontSize = btn.dataset.style === 'less' ? fontSize - 1 : fontSize + 1;
            if (fontSize < 14 || fontSize > 36)
                return false;
            content.style.fontSize = fontSize + 'px';
            //content.querySelector('h3, p').style.fontSize = fontSize + 'px';
            inputFontSize.val(fontSize);
        }

    },

    changeTag(select) {
        let item = $(select).closest('.publication__item');
        let contentContainer = item.find('.publication__item-content');
        let content = contentContainer.text();
        let style = contentContainer.attr('style');

        let data = {
            method: 'add_publication_item',
            tag: select.value,
            content: content,
            style: style
        };
        let callback = response => {
            console.log(response);
            if (response.result) {
                item.replaceWith(response.result);
            }
        };

        ffetch(this.action, callback, data);
    },

    setStyleToAll(button) {
        let item = $(button).closest('.publication__item');
        let content = item.find('.publication__item-content')[0];
        let style = content.getAttribute('style');

        console.log(style)

        let tag = item.find('select.change-tag').val();
        $('.publication__item').each((i, item) => {
            let select = item.querySelector('select.change-tag');

            if (['text', 'subtitle'].includes(select.value) && select.value !== tag)
                select.value = tag;
            //this.changeTag(select);
        });

        $('.publication__item-content').attr('style', style);
    },

    setDescription(checkbox) {
        let item = $(checkbox).closest('.publication__item');
        let itemPrevious = item.prev();
        let descriptionInput = item.find('input[name="description"]');
        let imgDescription = item.find('p.img-description em');

        if (!checkbox.checked) {
            descriptionInput.val('');
            imgDescription.html('');
            return false;
        }

        if (!itemPrevious.length || !['text', 'subtitle'].includes(itemPrevious[0].dataset.tag)) {
            console.log('Не найден предыдущий текст...');
            let label = checkbox.parentElement.querySelector('small');
            let labelTextDefault = label.innerHTML;
            label.innerHTML = `<span style="color: red;">Описание не найдено...</span>`;
            setTimeout(() => label.innerHTML = labelTextDefault, 2500);
            checkbox.checked = false;
            return false;
        }


        let description = itemPrevious.find('.publication__item-content').text().trim();
        descriptionInput.val(description);
        imgDescription.html(description);
        itemPrevious.remove();
    },

    acceptEdit(btn) {
        let item = $(btn).closest('.publication__item');
        let content = '';
        let description = '';
        let images = [];
        switch (item[0].dataset.tag) {
            case ('text'):
                let text = item.find('textarea[name="text-content"]').val();
                text = text
                    .replace(/\n/g, '</p><p>')
                    .replace(
                        /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/gi,
                        '<a href="$&" target="_blank">$&</a>');
                content = `<p>${text}</p>`;
                break;
            case ('quote'):
                let quote = item.find('textarea[name="quote-content"]').val();
                quote = quote
                    .replace(/\n/g, '</p><p>')
                    .replace(
                        /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/gi,
                        '<a href="$&" target="_blank">$&</a>');
                content = `<blockquote class="blockquotered">${quote}</blockquote>`;
                break;
            case ('image'):

                let description = `<br><p class="img-description"><em>${item.find('input[name="description"]').val()}</em></p>`;

                if (item.find('input[name="description"]').val().trim() && !item.find('.preview-item img').length) {
                    item.find('.editor').hide();
                    item.find('.editor-icon').addClass('non-active');
                    if (item.find('p.img-description').length)
                        item.find('p.img-description em').html(item.find('input[name="description"]').val());
                    else
                        item.find('.publication__item-content').prepend(description);
                    return true;
                } else if (!item.find('input[name="description"]').val().trim() && !item.find('.preview-item img').length) {
                    item.find('.editor').hide();
                    item.find('.editor-icon').addClass('non-active');
                    return item.find('.publication__item-content p.img-description').remove();
                }


                images = Object.values(item.find('.preview-item img')).slice(0, -2)
                    .map(img => `<img src="${img.src}" data-source="${item.find('input[name="url"]').val()}" alt="" class="publication-image-item img-fluid d-block" />`);

                content = description + images.shift();
                break;
            case ('subtitle'):
                content = `<h2>${item.find('input[name="subtitle"]').val()}</h2>`;
                break;
            case ('video'):
                if (item.find('.uploader-previews video').length)
                    content = `<video src="${item.find('.uploader-previews video').attr('src')}" controls="controls"></video>`;
                else {

                    let description = item.find('input[name="description"]').val().trim();

                    if (!item.find('.uploader-previews').find('video, img').length && description) {
                        description = `<p class="img-description"><em>${description}</em></p><br>`;
                        item.find('.editor').hide();
                        item.find('.editor-icon').addClass('non-active');
                        if (item.find('.publication__item-content p.img-description').length)
                            return item.find('.publication__item-content p.img-description em').html(description);
                        else
                            return item.find('.publication__item-content').prepend(description);
                    }

                    let url = item.find('input[name="url"]').val();
                    let videoId;
                    let query = parseQuery(url);
                    if (query.v)
                        videoId = query.v;
                    else
                        videoId = url.trim().split('/').pop().split('?')[0];
                    let title = item.find('input[name="description"]').val();
                    content = `<iframe width="640" height="360" src="https://www.youtube.com/embed/${videoId}" title="${title}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>`;
                }
                break;
        }
        item.find('.publication__item-content').html(content);
        //item.find('input, textarea').not('input[name="fontsize"]').val('');
        item.find('.uploader-previews').html('');
        item.find('.editor').hide();
        item.find('.editor-icon').addClass('non-active');

        if (images.length) {
            images.forEach((image) => {
                let data = {
                    method: 'add_publication_item',
                    tag: 'image',
                    content: image
                };
                let callback = response => {
                    if (response.result) {
                        $('#publication_body').append(response.result);
                        uploader.init();
                        let item_index = item.index();
                        $('.publication__item').each((i, _item) => {
                            if (i > item_index) {
                                $(_item).find('.editor').hide();
                                $(_item).find('.editor-icon').addClass('non-active');
                            }
                        });
                    }
                };

                ffetch(this.action, callback, data);
            });
        }

    },

    editor(icon) {
        let item = $(icon).closest('.publication__item');
        let editorElements = item.find('.editor');
        if (icon.classList.contains('non-active')) {
            editorElements.show();
            icon.classList.remove('non-active');
        } else {
            editorElements.hide();
            icon.classList.add('non-active');
        }
    },

    multiSelectPublicItems(checkbox) {

        let items = $('.publication__item');

        if (checkbox.checked)
            $(checkbox).closest('.publication__item').addClass('selected');
        else
            $(checkbox).closest('.publication__item').removeClass('selected');

        if (items.find('input[name="multi-select"]:checked').length < 2)
            return false;

        let startSelect = $('input[name="multi-select"]:checked').first().closest('.publication__item.selected').index();
        let finishSelect = $(checkbox).closest('.publication__item.selected').index();

        items.each((i, item) => {
            if (i > startSelect && i < finishSelect)
                item.classList.add('selected');
        });

        setTimeout(() => {
            if (confirm('Удалить выбранные элементы?')) {
                let selected = $('.publication__item.selected');
                window.scrollTo({
                    top: selected.first().offset().top,
                    behavior: 'smooth'
                });
                selected.each((i, item) => item.querySelector('.remove-public-item').click());
            }
        }, 200);


    },

    removeItem(icon) {
        let item = $(icon).closest('.publication__item');
        if (['image', 'video'].includes(item[0].dataset.tag)) {
            let data = {
                method: 'remove',
                src: item.find('img, video').attr('src'),
                folder: item.find('input[name="upload_folder"]').val()
            };
            let callback = response => {
                console.log(response);
                //if (response.result)
                item.remove();
            };
            ffetch('/uploader', callback, data);
        } else
            item.remove();

    },

    addItem(icon) {
        let after = $(icon).closest('.publication__item').length;
        let data = {
            method: 'add_publication_item',
            tag: icon.dataset.tag
        };
        let callback = response => {
            console.log(response);
            if (response.result) {

                $('.start-text').remove();
                if (after)
                    $(icon).closest('.publication__item').after(response.result);
                else
                    $('#publication_body').append(response.result);

                if (data.tag === 'image')
                    uploader.init();
            }
        };

        ffetch(this.action, callback, data);
    },

    moveItem(icon) {
        let item = $(icon).closest('.publication__item')[0];
        let direction = icon.dataset.direction;
        if (direction === 'up' && item.previousElementSibling)
            item.parentNode.insertBefore(item, item.previousElementSibling);
        else if (direction === 'down' && item.nextElementSibling)
            item.parentNode.insertBefore(item.nextElementSibling, item);

        window.scrollTo({top: item.offsetTop - 20, behavior: 'instant'});
    },

    getPublicForm(form) {

    },

    submit(form) {
        let publication = {
            head: {},
            body: []
        };

        if (form.dataset.method === 'update') {
            publication.head.id = parseInt(form.elements.id.value);
            publication.head['update-date'] = form.elements['update-date'].checked;
        }

        publication.head.user_id = form.elements.user_id.value.trim();
        publication.head.is_published = form.elements.publish.checked;
        publication.head.image_default = form.elements.image_default.value.trim();
        publication.head.category_id = form.elements.category_id.value.trim();
        publication.head.new_category = {
            name: form.elements.new_category.value.trim(),
            parent_id: form.elements.new_category_parent_id.value.trim(),
            is_hidden: form.elements.is_hidden.checked,
            description: form.elements.new_category_description.value.trim(),
        };
        publication.head.source_url = form.elements.url_import.value.trim();

        if (publication.head.source_url) {
            publication.head.import_containers = {
                publication_container: form.elements.import_public_container.value.trim(),
                hashtags_container: form.elements.import_public_hashtags_container.value.trim()
            }
        }

        publication.head.title = form.elements.title.value.trim();
        publication.head.introtext = form.elements.introtext.value.trim();
        publication.head.hashtags = form.elements.hashtags.value.trim();
        publication.head.comment = form.elements.comment.value.trim();

        form.querySelectorAll('.publication__item').forEach(item => {
            let tag = item.dataset.tag;
            let content, description, source, is_hidden, style, poster;

            if (['text', 'subtitle', 'quote'].includes(tag)) {
                content = item.querySelector('.publication__item-content').innerText;
                style = item.querySelector('.publication__item-content').getAttribute('style');
            } else {
                if (tag === 'image') {
                    content = Object.values($(item).find('img.publication-image-item')).slice(0, -2).map(img => img.src);
                    if (content.length === 1)
                        content = content[0];
                    description = $(item).find('.img-description').text();
                    is_hidden = item.querySelector('input[name="is_hidden"]').checked;
                    source = $(item).find('img.publication-image-item[data-source]').first().attr('data-source');
                    if (source !== undefined && source.length === 1)
                        source = source[0];
                } else if (tag === 'video') {
                    let video = item.querySelector('video, iframe');
                    description = $(item).find('.img-description').text();
                    poster = video.poster;
                    if (video.src)
                        content = video.src;
                    else
                        content = video.querySelector('source').src;
                    source = content;
                }
            }

            publication.body.push({
                tag: tag,
                content: content,
                description: description,
                source: source,
                is_hidden: is_hidden,
                style: style,
                poster: poster,
            });
        });

        if (!publication.head.title.trim()) {
            validateInput(form.elements.title, 'Пожалуйста, придумайте название для новой публикации', true, true);
            window.scrollTo({top: form.elements.title.offsetTop - 20, behavior: 'smooth'});
            return false;
        }

        if (!publication.head.category_id && !publication.head.new_category.name) {
            validateInput(form.elements.category_id, 'Вы не выбрали категорию новой публикации', true, true);
            window.scrollTo({top: form.elements.category_id.offsetTop - 20, behavior: 'smooth'});
            return false;
        }

        if (form.elements.new_category.classList.contains('is-invalid')) {
            validateInput(form.elements.new_category, 'Извините, данная категория уже существует', true, true);
            window.scrollTo({top: form.elements.new_category.offsetTop - 20, behavior: 'smooth'});
            return false;
        }

        let data = {
            publication: publication,
            method: form.dataset.method,
        };

        let callback = response => {
            console.log(response);

            if (response.result) {
                /*if(response.action === 'update'){
                    location.href = `/publication/show/${response.result}::${response.publication.alias}.html`;
                    return  true;
                }*/

                if (response.action !== 'update') {
                    form.reset();
                    $('#publication_body').html(`<p class="lead start-text">Здесь будет отображена ваша публикация...</p>`);
                }

                return alert("Публикация добавлена в очередь на модерацию. Будет доступна после одобрения");
            }
        };

        //console.log(data);

        ffetch(this.action, callback, data);

    },

    comment(form) {
        let data = formExecute(form);
        let preview = form.querySelector('.preview-item img');
        data.image = preview !== null ? preview.getAttribute('src') : '';
        data.method = 'comment';

        if (!data.comment.trim() && !data.image) {
            validateInput(form.elements.comment, 'Пустой комменарий', true, true);
            return false;
        }

        let callback = response => {
            console.log(response);

            if (data.is_reply) {
                $(form).closest('.card-body').find('.replies').prepend(response.comment);
                form.remove();
                return true;
            }

            if (response.comment) {
                form.reset();
                $(form).find('.uploader-previews').html('');
                let commentContainer = $('#comments');
                commentContainer.find('p.lead').remove();
                commentContainer.prepend(response.comment);
            }
        };
        ffetch(this.action, callback, data);
        return false;
    },

    like_comment(icon) {

        let data = {
            method: 'like_comment',
            id: icon.dataset.id,
            user_id: icon.dataset.user,
        };

        let callback = response => {
            console.log(response);
            let likesCount = icon.nextElementSibling;

            if (response.dislike && icon.classList.contains('fa-heart'))
                icon.className = 'fa fa-heart-o pointer';
            else if (!response.dislike && icon.classList.contains('fa-heart-o'))
                icon.className = 'fa fa-heart pointer';

            likesCount.innerHTML = response.likes;

        };
        ffetch(this.action, callback, data);
    },

    reply(icon) {
        let data = {
            method: 'reply',
            publication_id: icon.dataset.publication_id,
            parent_id: icon.dataset.id,
            is_reply: 1,
            user_id: icon.dataset.user,
        };

        let callback = response => {
            console.log(response);
            let replyForm = $(icon).closest('.card-body').find('.reply-form');
            replyForm.html(response.form);
            setTimeout(function () {
                window.scrollTo({top: replyForm.offset().top - 100});
                uploader.init();
            }, 50);
        };
        ffetch(this.action, callback, data);
    },

    removeComment(icon, manager = 0) {
        let data = {
            method: 'remove_comment',
            id: icon.dataset.id,
            manager: manager
        };

        let callback = response => {
            console.log(response);
            if (response.result) {
                let comment = $(icon).closest('.comment-item-container');
                let commentsContainer = $('#comments');
                comment.next('hr').remove();
                comment.remove();
                if (!commentsContainer.find('.comment-item-container').length)
                    commentsContainer.html("<br><p class=\"lead\">Комментарии пока отсутствуют...</p>");
            } else
                alert('Комментарий не может быть удалён');
        };
        ffetch(this.action, callback, data);
    },

    prevModal() {
        let customModal = $('.custom-modal');
        let imgSrc = customModal.find('.custom-modal-img')[0].getAttribute('src');
        let currentImg = $(`#publication-body img.publication-image-item[src="${imgSrc}"]`);
        let prevImage = $(currentImg).closest('figure.image-item-container').prevAll('figure.image-item-container').first().find('img');

        customModal.remove();
        if (!prevImage.length)
            return this.closeModal();

        prevImage[0].scrollIntoView({block: "center", behavior: "smooth"});

        return this.showModal(prevImage[0]);

    },

    nextModal() {
        let customModal = $('.custom-modal');
        let imgSrc = customModal.find('.custom-modal-img')[0].getAttribute('src');
        let currentImg = $(`#publication-content img.publication-image-item[src="${imgSrc}"]`);
        let nextImage = $(currentImg).closest('figure.image-item-container').nextAll('figure.image-item-container').first().find('img.publication-image-item');

        customModal.remove();
        if (!nextImage.length)
            return this.closeModal(currentImg);

        nextImage[0].scrollIntoView({block: "center", behavior: "smooth"});

        return this.showModal(nextImage[0]);

    },

    closeModal(toDown = false) {

        let customModal = $('.custom-modal');

        customModal.addClass(`disappearing${toDown ? "-down" : ""}`);

        setTimeout(() => {
            customModal.remove();
            window.removeEventListener('wheel', this.scrollModalImages);
            document.body.style.overflow = 'inherit';
        }, 500);

        // window.removeEventListener('touchstart', this.swipeInit);
        // window.removeEventListener('touchmove', this.swipe);
    },

    nodraggable(icon) {
        let img = icon.nextElementSibling;
        img.classList.remove('draggable');
        img.classList.add('static');
        icon.className = 'fa fa-search-plus clickable';
        icon.onclick = () => this.draggable(icon);

        // $('.custom-modal').css({flexWrap: 'wrap'});
    },

    draggable(icon) {
        let img = icon.nextElementSibling;
        img.classList.add('draggable');
        img.classList.remove('static');
        img.style.left = 0;
        img.style.top = 0;
        $(img).draggable({
            drag: function (event, ui) {
                if (!this.classList.contains('draggable'))
                    return false;
            }
        });
        icon.className = 'fa fa-search-minus clickable';
        icon.onclick = () => this.nodraggable(icon);

        //$('.custom-modal').css({flexWrap: 'nowrap'});
    },

    modalImgPlus(modalImg) {

        $('.custom-modal-img-preloader').hide();

        if (+modalImg.clientWidth < +modalImg.naturalWidth - 100) {
            $('.custom-modal-wrapper').prepend(`<i class='fa fa-search-plus clickable' title="Кликните по значку, либо двочйной щелчок мыши по изображению для изменения масштаба картинки" onclick="publication.draggable(this)" aria-hidden='true'></i>`);

            modalImg.ondblclick = () => {
                let icon = modalImg.previousElementSibling;

                if (icon === null)
                    return false;

                if (modalImg.classList.contains('draggable'))
                    return this.nodraggable(icon);
                else
                    return this.draggable(icon);
            };
        }

        if (screen.width < 1000)
            return false;

        document.addEventListener("keydown", (keyboardEvent) => {
            if (keyboardEvent.key === "Control" && screen.width > 999)
                modalImg.dispatchEvent(new MouseEvent("dblclick"))
        });
    },

    showModal(img) {

        let src = img.src;
        let previous = $(img).closest('figure').prev('p, h3');
        let descriptionPrevious;
        if (!previous.length)
            descriptionPrevious = "";
        else
            descriptionPrevious = previous.text();

        let description = img.getAttribute('alt').trim() ? img.alt : descriptionPrevious;

        let prevImage = $(img).closest('figure.image-item-container').prevAll('figure.image-item-container').first().find('img');
        let nextImage = $(img).closest('figure.image-item-container').nextAll('figure.image-item-container').first().find('img');

        let faPrev = prevImage.length
            ? `<i class='fa fa-chevron-left prev-next modal-control clickable' onclick="publication.prevModal()" aria-hidden='true'></i>`
            : "";
        let faNext = nextImage.length
            ? `<i class='fa fa-chevron-right prev-next modal-control clickable' onclick="publication.nextModal()" aria-hidden='true'></i>`
            : "";


        if (img.classList.contains('comment-img')) {
            faPrev = faNext = "";
            description = img.previousElementSibling.innerHTML;
        }

        let modal =
            `<div class="custom-modal">
                <i class='fa fa-times close-modal modal-control clickable' onclick="publication.closeModal()" aria-hidden='true'></i>
                <div class="custom-modal-wrapper">
                    <img src="${src}" alt="${description}" class="clickable img-fluid custom-modal-img" onload="publication.modalImgPlus(this)"  />
                    <div class="custom-modal-img-preloader preloader"></div>
                    ${description ? `<p class="lead clickable">${description}</p>` : ""}                    
                </div>
                ${faPrev} ${faNext}                
            </div>`;

        $(document.body)
            .css({overflow: 'hidden'})
            .on('click', e => {
                if (!$(e.target).closest('.clickable, .publication-image-item').length)
                    this.closeModal()
            })
            .on('keyup', e => e.keyCode === 27 ? this.closeModal() : false);

        $(document.body).append(modal);

        //let modalImg = $('.custom-modal-img')[0];

        window.addEventListener('wheel', this.scrollModalImages, {passive: false});

    },

    getWidth() {
        if (screen.width > 1000)
            return .7 * screen.width;
        if (screen.width <= 1000 && screen.width > 601)
            return .9 * screen.width;
        else
            return screen.width;
    },

    titleDefault: '',

    iframe(id, description) {
        let width = this.getWidth();
        let height = .58 * width;
        //let description = $(item).closest('figure').next('.media').find('.item-description').text();
        this.titleDefault = document.title;

        ffetch(this.action, response => {
            document.title = response.title;
            $.fancybox
                .open(`<div class="video-iframe-container">
                        <iframe class="video-iframe" style="width: ${width}px; height: ${height}px" src="https://www.youtube.com/embed/${id}?autoplay=1" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                        <p class="iframe-description" style="width: ${width}px">${description ? description : response.title}</p></div>`);

        }, {method: 'get_video_info', id: id});

    },


    //Блокиратор скролла колесом мыши
    scrollBlock: false,

    scrollModalImages(e) {
        e.preventDefault();
        if (this.scrollBlock)
            return false;
        e.deltaY < 0 ? publication.prevModal() : publication.nextModal();
        //Блокируем скролл на пол секунды
        this.scrollBlock = true;
        setTimeout(() => this.scrollBlock = false, 500);
    },

    complain(icon) {
        let data = {
            method: 'complain',
            id: icon.dataset.id
        };

        let callback = response => {
            console.log(response);
            if (response.result)
                $(icon).closest('.comment-item-container').find('span.complain').removeClass('d-none');
            else
                return alert('Ошибка...');
        };
        ffetch(this.action, callback, data);
    },

    playNext(video) {

        video.webkitExitFullScreen();

        let nextVideo = $(video).closest('.public-video-item').next('.public-video-item').find('video');
        if (!nextVideo.length)
            return false;

        nextVideo[0].play();
        nextVideo[0].scrollIntoView({block: "center", behavior: "smooth"});

    }


};

$(document).ready(() => {

    $('.video-item .img-fluid').each((i, img) => {
        if (img.offsetWidth < 300)
            img.src = img.src.replace('maxresdefault', 'sddefault');
    });


    $(document).on('afterClose.fb', () => {
        if (publication.titleDefault)
            document.title = publication.titleDefault;
    });

    window.onresize = e => {
        let iframe = document.querySelector('.video-iframe');
        if (iframe !== null) {
            let width = publication.getWidth();
            iframe.style.width = `${width}px`;
            iframe.style.height = `${width * .58}px`;

            //document.querySelector('.iframe-description').style.width = `${width}px`;
        }

    };


    document.querySelectorAll('img').forEach(img => {

        let callback = isGif => {
            if (isGif) {
                let hiddenImg = img.cloneNode(1);
                hiddenImg.style.display = "none";
                img.parentElement.append(hiddenImg);
                img.setAttribute('data-gifffer', img.getAttribute('src'));
                img.removeAttribute('src');
                Gifffer();
            }
        };

        isAnimatedGif(img.src, callback);

    });


    publication.setLikeWidth();

    $('.publication-form').on('submit', function (e) {
        e.preventDefault();
        publication.submit(this);
    });

    let url_import_input = document.querySelector('input[name="url_import"]');

    if (url_import_input !== null)
        url_import_input.addEventListener('paste', e => publication.getImportContainers(e.clipboardData.getData('Text')));

});