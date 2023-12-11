function ffetch(action, callback, data, callback_error = () => false) {
    fetch(action, {
        method: 'post',
        body: JSON.stringify(data)
    })
        .then(response => response.json())
        .then(result => callback(result))
        .catch(error => callback_error(error));
}




function formExecute(form) {
    const fields = form.elements;
    const data = {};
    for (let i in fields) {
        let field = fields[i];
        if (['SELECT', 'TEXTAREA', 'INPUT'].includes(field.tagName) && field.type !== 'submit') {
            if (['checkbox', 'radio'].includes(field.type)) {
                if (field.type === "radio" && !field.checked)
                    continue;
                else if (field.type === "checkbox")
                    data[field.name] = +field.checked;
                else
                    data[field.name] = field.value ? field.value : 1;
            } else
                data[field.name] = field.value;
        }
    }
    return data;
}

function parseQuery(url = false) {
    const queryString = url ?
        url.split('?')[1] :
        window.location.href.split('?')[1];
    if (queryString === undefined)
        return false;
    const result = {};
    const queryArray = queryString.split('&');
    queryArray.forEach(item => {
        const exp = item.split('=');
        result[exp[0]] = exp[1];
    });
    return result;
}

function arrayShuffle(array) {
    const result = [];
    while (result.length < array.length) {
        let index = Math.round(Math.random() * array.length - .5);
        if (result.indexOf(array[index]) === -1)
            result.push(array[index]);
    }
    return result;
}


function uploadFile(action, file, callback, before = () => {
}) {
    const formData = new FormData();
    if (file.files.length > 0)
        $.each(file.files, (i, file) => formData.append("file[" + i + "]", file));
    else
        return false;

    $.ajax({
        type: "POST",
        url: action,
        cache: false,
        dataType: "JSON",
        contentType: false,
        processData: false,
        data: formData,
        beforeSend: function () {
            before();
        },
        success: function (response) {
            callback(response);
        },
        error: function (xhr, ajaxOptions, thrownError) {
            console.log(thrownError);
        }
    });
}


function youtubePlayer(url, playerId) {
    const video = url.split('/');
    const videoId = video[video.length - 1].replace(/\?.*/, '');
    const tag = document.createElement('script');
    tag.src = "https://www.youtube.com/player_api";
    const firstScriptTag = document.getElementsByTagName('script')[0];
    firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

    const containerWidth = $(`#${playerId}`).closest('div').innerWidth();
    const videoWidth = containerWidth;
    const videoHeigth = containerWidth / 1280 * 720;

    const videoQuery = parseQuery(url);

    setTimeout(function () {
        return new YT.Player(playerId, {
            width: videoWidth,
            height: videoHeigth,
            videoId: videoId,
            playerVars: {
                start: videoQuery.t ? videoQuery.t : 0
            }
        });
    }, 500);
}


function getElemetsAttributes(elements, attributes = []) {
    if (typeof attributes !== 'object')
        attributes = [attributes];

    const data = {};

    if (!(elements instanceof jQuery))
        elements = $(elements);

    elements.each((i, element) => {
        attributes.forEach(attribute => {
            if (data[attribute])
                data[attribute].push(element[attribute]);
            else
                data[attribute] = [element[attribute]]
        });
    });

    return data;
}


function copyToClipboard(text, context = false) {
    let textField = document.createElement('textarea');
    textField.innerHTML = text;

    if (context)
        context.parentNode.insertBefore(textField, context);
    else
        document.body.appendChild(textField);

    textField.select();
    document.execCommand('copy');
    textField.parentNode.removeChild(textField);
}


function validateInput(input, errorText, invalid = true, onlyOnFalse = false) {
    if (invalid) {
        input.className = `form-control is-invalid`;
        input.nextElementSibling.innerHTML = errorText;
        input.nextElementSibling.style.display = 'block';
    } else {
        if (!onlyOnFalse)
            input.className = `form-control is-valid`;
        input.nextElementSibling.innerHTML = '';
        input.nextElementSibling.style.display = 'none';
    }
}


function addLink() {
    var body_element = document.getElementsByTagName('body')[0];
    var selection = window.getSelection();
    var pagelink = `<br>Подробнее на <a href="${document.location.href}">${document.location.href}</a> &copy;`;
    var copytext = selection + pagelink;
    var newdiv = document.createElement('div');
    newdiv.style.position = 'absolute';
    newdiv.style.left = '-99999px';
    body_element.appendChild(newdiv);
    newdiv.innerHTML = copytext;
    selection.selectAllChildren(newdiv);
    window.setTimeout( function() {
        body_element.removeChild(newdiv);
    }, 0);
}



