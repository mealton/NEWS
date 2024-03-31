<?php
/**
 * Class Page404
 * Страница не найдена
 */


class Page404 extends Main
{
    public function __construct()
    {
        parent::__construct();
        $this->components['title'] = "Страница не найдена";
        $this->components['noindex'] = true;
        $content = render('extra', 'page-404');
        page($content, $this->components);
    }
}