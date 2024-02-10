<?php

/**
 * Class Uploader
 * Класс для загрузки файлов
 */


class Uploader
{

    private $upload_dir = '/assets/uploads/';

    public function init()
    {

    }

    //Инициализация
    public function __construct($data = [])
    {
        $method = strval($data['method']) ? strval($data['method']) : 'init';
        if (!method_exists($this, $method)) {
            json(['result' => false, 'message' => 'Запрашиваемый метод отсутствует', 'data' => $data]);
            return false;
        }
        $this->$method($data);
        return true;
    }

    //Удаление загруженных файлов
    protected function remove($data)
    {
        $upload_dir = $_SERVER['DOCUMENT_ROOT'] . $this->upload_dir . trim($data['folder'], "/") . '/';
        $file = $upload_dir . end(explode("/", $data['src']));
        $result = file_exists($file) ? unlink($file) : false;
        json(['result' => $result, 'data' => $data, 'file' => $file]);
        return $result;
    }


    private function watermark($image, $text = "mealton.ru")
    {
        header('Content-type: image/*');

        $fontName = dirname(dirname(__DIR__)) . "/public_html/assets/fonts/arial.ttf"; // Ссылка на шрифт
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


    //Загрузка
    protected function upload($data)
    {
        //sleep(.5);
        header("Content-type: image/*");
        $url = trim(strval($data['url']));
        if (!$url) {
            json(['result' => 0, 'warning' => 'Нет url']);
            return false;
        }

        if (preg_match('/data\:/', $url)) {
            $url = end(explode(',', $url));
            $image = base64_decode($url);
        } else
            $image = curl($url);

        $fileName = time() . rand(0, 100000) . time() . '.jpg';
        $folder = trim($data['folder'], "/") . '/' . $fileName;
        $upload_dir = $_SERVER['DOCUMENT_ROOT'] . $this->upload_dir . $folder;

        file_put_contents($upload_dir, $image);

        if (getimagesize($upload_dir)) {
            $ext = $this->watermark($upload_dir);
            $preview = render('components', 'uploader-preview-item', ['src' => $this->upload_dir . $folder]);
            json(['result' => true, 'preview' => $preview, 'src' => $this->upload_dir . $folder, '$ext' => $ext]);
        } else {
            json(['result' => false, 'warning' => 'Файл не загружен', 'data' => $data]);
        }
        return true;
    }

    protected function upload_video_file($data)
    {
        header("Content-type: video/mp4");
        $base64 = trim($data['base64']);
        $video = end(explode(',', $base64));
        $video = base64_decode($video);


        $fileName = time() . rand(0, 100000) . time() . '.mp4';
        $folder = trim($data['folder'], "/") . '/' . $fileName;
        $upload_dir = $_SERVER['DOCUMENT_ROOT'] . $this->upload_dir . $folder;
        $result = file_put_contents($upload_dir, $video);
        $preview = render('components', 'uploader-preview-video-item', ['src' => $this->upload_dir . $folder]);

        if ($result)
            json(['result' => true, 'src' => $this->upload_dir . $folder, 'preview' => $preview]);
        else
            json(['result' => false, 'warning' => 'Файл не загружен', 'data' => $data]);
    }

}
