<?php

/**
 * Class Uploader
 * Класс для загрузки файлов
 */

ini_set('memory_limit', '2048M');
ini_set('time_limit', '1000');


class Uploader
{

    private $upload_dir = '/assets/uploads/';

    public function init()
    {

    }

    static function upload_files($files)
    {
        $uploads = [];
        for ($i = 0; $i < count($files['file']['tmp_name']); $i++) {
            $ext = end(explode('/', $files['file']['type'][$i]));
            if ($ext == "jpeg")
                $ext = "jpg";
            $fileName = time() . rand(0, 100000) . time() . '.' . $ext;
            $path = '/assets/uploads/img/public/' . $fileName;
            $folder = $_SERVER['DOCUMENT_ROOT'] . $path;
            $result = move_uploaded_file($files['file']['tmp_name'][$i], $folder);
            if ($result) {
                watermark($folder);
                $image = '<img src="' . $path . '" data-source="" alt="" class="publication-image-item img-fluid d-block">';
                $item = render('public/edit/item/', 'image', ['content' => $image, 'editor_hide' => 1]);
                $uploads[] = $item;
            }
            sleep(.3);
        }
        json(['html' => implode("", $uploads), 'files' => $files]);
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
        } else {
            $url = preg_replace('/\.(jpg|png|gif)(.*)/', '.$1', $url);
            $image = curl($url);
        }


        $fileName = time() . rand(0, 100000) . time() . '.jpg';
        $folder = trim($data['folder'], "/") . '/' . $fileName;
        $upload_dir = $_SERVER['DOCUMENT_ROOT'] . $this->upload_dir . $folder;

        file_put_contents($upload_dir, $image);

        if (getimagesize($upload_dir)) {
            watermark($upload_dir);
            $preview = render('components', 'uploader-preview-item', ['src' => $this->upload_dir . $folder]);
            json(['result' => true, 'preview' => $preview, 'src' => $this->upload_dir . $folder]);
        } else {
            json(['result' => false, 'warning' => 'Файл не загружен', 'data' => $data, 'url' => $url]);
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
