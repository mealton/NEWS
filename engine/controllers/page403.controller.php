<?php
/**
 * Class Page403
 * Для отображения страницы с запрещенным доступом
 */


class Page403 extends Main
{
    public function __construct()
    {
        parent::__construct();
        $this->components['title'] = "Доступ запрещен";
        $this->components['noindex'] = true;
        $content = render('extra', 'access-denied');
        page($content, $this->components);
    }
}