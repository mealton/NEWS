<?php

/**
 * Файл инициализации системы
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.lib.php';
require_once __DIR__ . '/models/db.php';
require_once __DIR__ . '/controllers/main.controller.php';
require_once __DIR__ . '/models/main.model.php';

$query = explode("/", trim($_GET['q'], '/'));

if(strpos(trim($_GET['q']), ".html") && end(explode(".html", trim($_GET['q']))))
    exit404($query);

$controller_name = current(explode(".html", $query[0]));

if(!$controller_name)
    $controller_name = 'publication';
elseif ($controller_name == "sitemap.xml")
    $controller_name = "sitemap";

$controller_path = __DIR__ . '/controllers/' . $controller_name . '.controller.php';

if (file_exists($controller_path)) {
    require_once $controller_path;
} else
    exit404($query);

init($controller_name, $query);