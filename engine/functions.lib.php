<?php


function watermark($image, $text = "mealton.ru")
{
    header('Content-type: image/*');

    $fontName = dirname(__DIR__) . "/public_html/assets/fonts/arial.ttf"; // Ссылка на шрифт
    $fontSise = 14; // Размер шрифта

    $image_info = getimagesize($image);
    $type = $image_info['mime'];

    switch ($type){
        case "image/gif":
            $img = imagecreatefromgif($image);
            break;
        case "image/bmp":
            $image = imagecreatefrombmp($image);
            break;
        case "image/png":
            $img = imagecreatefrompng($image);
            break;
        default:
            $img = imagecreatefromjpeg($image); // Функция создания изображения
    }

    $img_w = imagesx($img);
    $img_h = imagesy($img);

    $y = $img_h - 10; // Смещение сверху (координата y)
    $x = $img_w - 100; // Смещение слева (координата x)


//    imagealphablending($img, true);
//    imagesavealpha($img, true);
//    imagealphablending($img_cover, true);
//    imagesavealpha($img_cover, true);
//    imagecopy($img, $img_cover, 0, 0, 0, 0, imagesx($img_cover), imagesy($img_cover));


    $textColor = imagecolorallocate($img, 255, 255, 255); // Функция выделения цвета для текста
    $aroundColor = imagecolorallocate($img, 0, 0, 0); // Функция выделения цвета для обводки текста

    imagettftext($img, $fontSise, 0, $x + 2, $y, $aroundColor, $fontName, $text);
    // смещение влево
    imagettftext($img, $fontSise, 0, $x - 2, $y, $aroundColor, $fontName, $text);
    // смещение вниз
    imagettftext($img, $fontSise, 0, $x, $y + 2, $aroundColor, $fontName, $text);
    // смещение вверх
    imagettftext($img, $fontSise, 0, $x, $y - 2, $aroundColor, $fontName, $text);
    // смещение вправо и вниз
    imagettftext($img, $fontSise, 0, $x + 1, $y + 1, $aroundColor, $fontName, $text);
    // смещение вправо и вверх
    imagettftext($img, $fontSise, 0, $x + 1, $y - 1, $aroundColor, $fontName, $text);
    // смещение влево и вверх
    imagettftext($img, $fontSise, 0, $x - 1, $y - 1, $aroundColor, $fontName, $text);
    // смещение влево и вниз
    imagettftext($img, $fontSise, 0, $x - 1, $y + 1, $aroundColor, $fontName, $text);
    // вывод самого текста
    imagettftext($img, $fontSise, 0, $x, $y, $textColor, $fontName, $text);

    switch ($type){
        case "image/gif":
            imagegif($img, $image);
            break;
        case "image/bmp":
            imagebmp($img, $image);
            break;
        case "image/png":
            imagepng($img, $image);
            break;
        default:
            imagejpeg($img, $image); // Сохранение рисунка
    }

    imagedestroy($img); // Освобождение памяти и закрытие рисунка
}

function get_some_array_fields($array, $fields)
{
    $fields = is_array($fields) ? $fields : array($fields);
    $result = array();
    foreach ($array as $key => $value) {
        if (in_array($key, $fields))
            $result[$key] = $value;
    }
    return $result;
}

function init($controller, $query)
{
    $controller = ucfirst($controller);

    if (!class_exists($controller))
        exit404($query);

    if (!empty($_FILES) && method_exists($controller, 'upload_files'))
        return $controller::upload_files($_FILES);

    $data = file_get_contents('php://input');

    if (is_array(json_decode($data, 1)))
        return new $controller(json_decode($data, 1), true);

    return new $controller($query);

}

function json($data)
{
    echo json_encode($data);
}

function pre($data)
{
    echo '<pre>';
    print_r($data);
    echo '</pre>';
}


function fetch_to_array($fetch, $field = false)
{
    $result = array();
    if (!in_array($field, array_keys(current($fetch))))
        $field = false;
    foreach ($fetch as $row)
        $result[] = $field ? $row[$field] : current($row);
    return $result;
}

function date_rus_format($date, $options = [])
{
    $date = date_parse($date);
    $months = [1 => 'Января', 'Февраля', 'Марта', 'Апреля', 'Мая', 'Июня', 'Июля', 'Августа', 'Сентября', 'Октября', 'Ноября', 'Декабря'];
    $month = $options['upper'] ? $months[$date['month']] : mb_strtolower($months[$date['month']]);
    $time = (bool)$options['time'];
    $year_g = (bool)$options['year'];
    $delimiter = $options['delimiter'] ? $options['delimiter'] : ', ';

    return $date['day'] . ' ' . $month . ' ' . $date['year'] . ($year_g ? ' г.' : '') .
        ($time ? $delimiter . $date['hour'] . ':' . (intval($date['minute']) < 10 ? '0' . $date['minute'] : $date['minute']) : '');
}

function date_converter($date)
{
    if ($date == date('Y-m-d'))
        return 'Сегодня';
    elseif ($date == date('Y-m-d', time() - 86400))
        return 'Вчера';
    elseif ($date == date('Y-m-d', time() - 2 * 86400))
        return 'Позавчера';
    elseif ($date == date('Y-m-d', time() + 86400))
        return 'Завтра';
    elseif ($date == date('Y-m-d', time() + 2 * 86400))
        return 'Послезавтра';
    else
        return date_rus_format($date);
}


function current_ending($count, $endings = array())
{
    switch (1) {
        case ($count % 10 == 1 && $count % 100 != 11):
            $result = $endings[0];
            break;
        case (in_array($count % 10, array(2, 3, 4)) && !in_array($count % 100, array(12, 13, 14))):
            $result = $endings[1];
            break;
        default:
            $result = $endings[2];
    }
    return $result;
}

function translit($string)
{
    $abc = [
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'e', 'ж' => 'j', 'з' => 'z',
        'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r',
        'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch',
        'ъ' => '\'', 'ы' => 'y', 'ь' => '\'', 'э' => 'e', 'ю' => 'ju', 'я' => 'ja'
    ];

    $translit = '';
    $string = str_replace("&nbsp;", "-", $string);
    $string = html_entity_decode($string);
    $string = htmlspecialchars_decode($string);

    $string = preg_split('//u', $string, -1, PREG_SPLIT_NO_EMPTY);

    foreach ($string as $char) {
        if (preg_match('/\p{Cyrillic}/ui', $char))
            $translit .= $abc[mb_strtolower($char)];
        else
            $translit .= mb_strtolower($char);
    }

    $translit = preg_replace('/\s+/', '-', $translit);
    $translit = preg_replace('/(–|-){2,}/', '-', $translit);
    return preg_replace('/[^a-z\d+\-\']/', "", trim($translit, '-'));
}

function transrus($string)
{
    $abc = [
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'e', 'ж' => 'j', 'з' => 'z',
        'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r',
        'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch',
        'ъ' => '\'', 'ы' => 'y', 'ь' => '\'', 'э' => 'e', 'ю' => 'ju', 'я' => 'ja'
    ];

    $string = str_replace("&nbsp;", "-", $string);
    $string = html_entity_decode($string);
    $string = htmlspecialchars_decode($string);

    return str_replace(array_values($abc), array_keys($abc), $string);
}

function textCutter($string, $char_limit = 15)
{
    $string = trim(strip_tags($string));
    return
        mb_strwidth($string, 'utf-8') > $char_limit + 10 ?
            mb_substr($string, 0, $char_limit, "utf-8") . '...'
            : $string;
}


function get_youtube_video_id($url)
{
    if (stristr($url, 'youtu.be/')) {
        preg_match('/(https:|http:|)(\/\/www\.|\/\/|)(.*?)\/(.{11})/i', $url, $final_ID);
        return $final_ID[4];
    } else {
        @preg_match('/(https:|http:|):(\/\/www\.|\/\/|)(.*?)\/(embed\/|watch.*?v=|)([a-z_A-Z0-9\-]{11})/i', $url, $IDD);
        return $IDD[5];
    }

    /*$url_parse = parse_url($url);
    $query = $url_parse['query'];
    $query_parse = explode("&", $query);
    $query_result = [];

    foreach ($query_parse as $item){
        $item = explode("=", $item);
        $query_result[$item[0]] = $item[1];
    }

    if($query_result['v'])
        return $query_result['v'];
    else{
        $path = $url_parse['path'];
        $path_parse = trim($path, "/");
        return $path_parse;
    }*/
}


function mailSender($to, $subject, $message)
{
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=utf-8\r\n";
    $headers .= "From: admin@mealton.ru \r\n";
    mail($to, $subject, $message, $headers);
}

function getAge($birthday)
{
    $age = floor((time() - strtotime($birthday)) / (3600 * 24 * 365));
    return $age . ' ' . current_ending($age, array('год', 'года', 'лет'));
}

function getPeriod($date)
{
    $period = (time() - strtotime($date)) / (3600 * 24);

    switch (1) {
        case $period < 1:
            return ' менее суток';
            break;
        case ($period / intval(date('t')) < 1):
            return ' менее месяца';
            break;
        case ($period / 365 < 1):
            return ceil($period / 30) . ' ' . current_ending(ceil($period / 30), array('месяц', 'месяца', 'месяцев'));
            break;
        case ($period / 365 > 1):
            $years = floor($period / 365) . ' ' . current_ending(floor($period / 365), array('год', 'года', 'лет'));
            $monthes = floor($period % 365 / 30) > 0 ?
                ' и ' . floor($period % 365 / 30) . ' ' . current_ending(ceil($period % 365 / 30), array('месяц', 'месяца', 'месяцев')) : '';
            return $years . $monthes;
            break;
    }

    return 'неопределенное время';
}

function get_date($timestamp)
{
    return current(explode(' ', $timestamp));
}


function curl($url, $cookie = false)
{
    $url = urldecode($url);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    if ($cookie)
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);

    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_AUTOREFERER, 0);

    $result = curl_exec($ch);
    curl_close($ch);

    return $result;

}


function file_force_download($file_path, $filename = false, $ctype = false)
{
    header('Content-Description: File Transfer');
    header('Content-Type: ' . ($ctype ? $ctype : 'application/octet-stream'));
    header('Content-Disposition: attachment; filename="' . ($filename ? $filename : basename($file_path)) . '"');
    header('Content-Transfer-Encoding: binary');
    header('Content-Length: ' . filesize($file_path));

    readfile($file_path);
}


function array_trimmer($data)
{
    foreach ($data as $i => $item)
        $data[$i] = trim(preg_replace('/\s{2,}/', ' ', strval(strip_tags($item))));

    return $data;
}


function textWrapper($text, $char_limit = 100)
{
    return
        mb_strwidth($text, 'utf-8') > $char_limit + 30 ? //Если длинна текста превышает заданный лимит более чем на 30 знаков
            mb_substr($text, 0, $char_limit, "utf-8")
            . '<span class="hidden">' . mb_substr($text, $char_limit, mb_strwidth($text, 'utf-8') - $char_limit, 'utf-8') . '</span>'
            . '<span class="details"> <span class="inner white-space-nowrap" onclick="textLimit(this)"><span class="inner-text">Развернуть</span> <i class="fa fa-caret-down" aria-hidden="true"></i></span></span>'
            : $text;
}


function csv_generate($data, $headers, $filename)
{
    header("Content-type: text/csv");
    header("Content-Disposition: attachment; filename=$filename.csv");
    header("Pragma: no-cache");
    header("Expires: 0");

    $buffer = fopen('php://output', 'w');
    fputs($buffer, chr(0xEF) . chr(0xBB) . chr(0xBF));
    fputcsv($buffer, $headers, ';');
    foreach ($data as $val) {
        fputcsv($buffer, $val, ';');
    }
    fclose($buffer);
    exit();
}


function mailer($subject, $message, $to = "titov_yw@mail.ru")
{
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=utf-8\r\n";
    $headers .= "From:admin@mealton.ru \r\n";

    return mail($to, $subject, $message, $headers);
}


function upload_img_by_url($url, $path)
{
    header("Content-type: image/jpeg");
    $image = curl_get_content($url);
    file_put_contents($path, $image);
    return file_exists($path) && filesize($path) ? str_replace($_SERVER['DOCUMENT_ROOT'], '', $path) : false;
}


function upload_files($files, $upload_dir, $rename = 1)
{
    $uploaded = array();

    for ($i = 0; $i < count($files['file']['tmp_name']); $i++) {
        $fileName = $rename ? time() . rand(0, 10000) . time() . '.jpg' : $files['file']['name'][$i];
        move_uploaded_file($files['file']['tmp_name'][$i], $upload_dir . '/' . $fileName);
        array_push($uploaded, str_replace($_SERVER['DOCUMENT_ROOT'], '', $upload_dir . '/' . $fileName));
    }

    return $uploaded;
}

function isSecure()
{
    $isSecure = false;
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
        $isSecure = true;
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
        $isSecure = true;
    }
    return $isSecure;
}


function get_current_url()
{
    return 'http' . (isSecure() ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}


function get_ob_content($file, $data = array())
{

    if (!is_array($data) || !file_exists($file))
        return false;

    foreach ($data as $k => $v)
        $$k = $v;

    ob_start();
    include_once $file;
    $html = ob_get_contents();
    ob_clean();

    return $html;
}


function get_hash_string($lenght)
{
    $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    return substr(str_shuffle($permitted_chars), 0, $lenght);
}


function curl_rest($url, $query = array(), $headers = array("content-type: application/json"))
{
    $ch = curl_init();
    curl_setopt_array($ch,
        array(
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $query
        ));
    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err)
        return array('error' => "cURL Error #:" . $err);

    return is_array(json_decode($response, 1)) ? json_decode($response, 1) : $response;
}

function sort_by_property($data, $property)
{
    $result = [];
    foreach ($data as $item) {
        $category = $item[$property];
        if ($result[$category])
            $result[$category][] = $item;
        else
            $result[$category] = [$item];
    }
    return $result;
}


function is_multi_array($array)
{
    if (!is_array($array))
        return false;

    foreach ($array as $item) {
        if (!is_array($item))
            return false;
    }

    return true;
}

function isNumeric($arr = [])
{
    if (empty($arr))
        return false;
    foreach (array_keys($arr) as $key) {
        if (!is_integer($key))
            return false;
    }
    return true;
}

function render($folder, $view = 'index', $data = [])
{

    //pre($data);

    ob_start();
    $data = !isNumeric($data) ? [$data] : $data;
    $view = __DIR__ . '/views/' . $folder . '/' . $view . '.php';

    if (file_exists($view)) {
        if (empty($data))
            return '';
        foreach ($data as $k => $row) {
            $row = is_array($row) ? $row : [$k => $row];
            foreach ($row as $key => $value)
                $$key = $value;
            include $view;
        }
    }
    $content = ob_get_contents();

    ob_get_clean();
    return $content;
}


function page($content, $components = [])
{
    ob_start();
    include __DIR__ . '/views/template.php';
    $page = ob_get_contents();
    ob_end_clean();
    echo $page;
}

function generateRandomString($length = 10)
{
    return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
}


function exit404($query)
{
    header("HTTP/1.1 404 Not Found");
    $controller_name = current(explode(".html", $query[0]));
    unset($controller_name);
    require_once __DIR__ . '/controllers/page404.controller.php';
    new Page404();
    exit();
}

function exit403($query)
{
    header('HTTP/1.0 403 Forbidden');
    $controller_name = current(explode(".html", $query[0]));
    unset($controller_name);
    require_once __DIR__ . '/controllers/page403.controller.php';
    new Page403();
    exit();
}


function get_from_url($url, $part = 'host')
{
    $parse_url = parse_url($url);
    return $parse_url[$part];
}


//Конвертация текстовых полей пбликации в спецсимвольных для записи в базу данных
function pre_insert($data)
{
    return array_map(function ($item) {
        return !in_array($item['tag'], ['video', 'iframe']) ? htmlspecialchars(trim($item)) : $item;
    }, $data);
}

function pre_show($data)
{
    return array_map(function ($item) {
        return array_map(function ($_item) {
            return htmlspecialchars_decode(trim($_item));
        }, $item);
    }, $data);
}


function get_protocol()
{
    if ($_SERVER['SERVER_PORT'] == 443)
        $protocol = 'https';
    elseif (isset($_SERVER['HTTPS']) && (($_SERVER['HTTPS'] == 'on') || ($_SERVER['HTTPS'] == '1')))
        $protocol = 'https';
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on')
        $protocol = 'https';
    elseif (strtolower(substr($_SERVER['SERVER_PROTOCOL'], 0, 5)) == 'https')
        $protocol = 'https';
    else
        $protocol = 'http';

    return $protocol;
}