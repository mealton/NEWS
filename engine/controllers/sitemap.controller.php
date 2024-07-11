<?php

/**
 * Class Uploader
 * Класс для автоматической генерации файла simemap.xml
 */

class Sitemap
{
    public function __construct()
    {
        require_once dirname(__DIR__) . '/models/main.model.php';
        $model = new MainModel();
        $GLOBALS['site-url'] = get_protocol() . "://" . $_SERVER['HTTP_HOST'] . "/";

        $users = $model->getter('users', ['is_active' => 1]);
        $users = array_map(function ($item) {
            return ['url' => $GLOBALS['site-url'] . "/profile/user/$item[id]/profile-page.html"];
        }, (array)$users);

        $publications = $model->getter('publications',
            ['is_published' => 1, 'is_deleted' => 0, 'moderated' => 1]);
        $publications = array_map(function ($item) {
            return $GLOBALS['site-url'] . "publication/show/$item[id]::" . urlencode($item['alias']);
        }, (array)$publications);

        $categories = $model->get_categories();
        $categories = array_map(function ($item) {
            return ['url' => $GLOBALS['site-url'] . "publication/category/$item[id]/" . urlencode($item['name'])];
        }, (array)$categories);


        $hashtags = $model->get_all_hashtags();
        $hashtags = array_map(function ($item) {
            return ['url' => $GLOBALS['site-url'] . "publication/tags/" . urlencode($item['name'])];
        }, (array)$hashtags);


        $static_urls = [
            ['url' => $GLOBALS['site-url']],
            ['url' => $GLOBALS['site-url'] . 'profile/login.html'],
            ['url' => $GLOBALS['site-url'] . 'profile/forgot.html'],
            ['url' => $GLOBALS['site-url'] . 'profile/registration.html'],
            ['url' => $GLOBALS['site-url'] . 'politics.html'],
            ['url' => $GLOBALS['site-url'] . 'publication/categories'],
        ];

        foreach ($GLOBALS['config']['top-menu'] as $name => $item) {
            $action = $item['action'];
            if (is_array($item['items'])) {
                foreach ($item['items'] as $item_name => $link) {
                    if ($item_name == 'divider' || !$link) continue;
                    $static_urls[] = ['url' => $GLOBALS['site-url'] . "$action/" . $link];
                }

            } else
                $static_urls[] = ['url' => $GLOBALS['site-url'] . "$item/index.html"];
        }

        $urls = array_merge($static_urls, $users, $publications, $categories, $hashtags);

        $urls = render('sitemap', 'url', $urls);

        header("Content-type: text/xml");

        $sitemap = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
$urls
</urlset>
XML;

        echo trim($sitemap);

    }


}