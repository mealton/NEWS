<?php
/**
 * Содержимое тега head
 * @var $components array
 */
?>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $components['title'] ? $components['title'] : $GLOBALS['config']['site']['sitename'] ?></title>
<meta name="description" content="<?=  $components['description'] ? $components['description'] : $GLOBALS['config']['site']['description'] ?>" >
<meta name="keywords" content="<?=  $components['keywords'] ? $components['keywords'] : $GLOBALS['config']['site']['keywords'] ?>" >
<link href="/vendors/bootstrap-5.3.0-alpha3-dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Fancybox CSS -->
<link rel="stylesheet" href="/vendors/fancybox/jquery.fancybox.min.css">

<link rel="stylesheet" href="/assets/css/style.css">
<!--<link rel="shortcut icon" href="/favicon.ico"/>-->
<link rel="icon" href="/assets/uploads/icons/favicon.svg" type="image/svg+xml">

<meta property="og:title" content="<?= $components['title'] ? $components['title'] : $GLOBALS['config']['site']['sitename'] ?>" >
<meta property="og:description" content="<?=  $components['description'] ? $components['description'] : $GLOBALS['config']['site']['description'] ?>" >
<meta property="og:image" content="<?= $components['data_image']?>" >

<link rel="canonical" href="<?= get_current_url() ?>">

<!--Yandex Webmaster-->
<meta name="yandex-verification" content="f125773a1f3ed168" >



