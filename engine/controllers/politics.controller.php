<?php

/**
 * Class Politics
 * Политика конфиденциальности
 */


class Politics extends Main
{
    public function __construct()
    {
        parent::__construct();
        $content = render('extra', 'politics');
        page($content, $this->components);
    }
}