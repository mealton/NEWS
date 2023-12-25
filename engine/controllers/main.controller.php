<?php
/**
 * Class Main
 * Главный класс системы
 */

class Main
{

    protected $pages = 1;//количество страниц с публикацияем для построения блока пагинации
    protected $page = 1;//конкретная страница с публикацияеми
    protected $pagination_limit;//максимальное количество публикаций на странице
    protected $offset = 0;//отступ отначала при выборе номера страницы с публикациями

    protected $components = [];//отображени различных компонентов сайта
    static $liked_publics = [];//понравивившиеся публикации
    static $liked_comments = [];//понравивившиеся комментарии

    protected $comments_pages = 1;
    protected $comments_pagination_limit = 5;

    private $calledClassName;

    protected function get_liked_publics($user_id)
    {
        require_once dirname(__DIR__) . '/models/main.model.php';
        $model = new MainModel();
        $liked_publics = $model->getter('users', ['id' => $user_id], 'liked_publics');
        return $liked_publics[0]['liked_publics']
            ? unserialize($liked_publics[0]['liked_publics'])
            : [];
    }

    protected function get_liked_comments($user_id)
    {
        require_once dirname(__DIR__) . '/models/main.model.php';
        $model = new MainModel();
        $liked_publics = $model->getter('users', ['id' => $user_id], 'liked_comments');
        return $liked_publics[0]['liked_comments']
            ? array_keys(unserialize($liked_publics[0]['liked_comments']))
            : [];
    }


    private function weather_informer($city)
    {
        //Определеяем погоду в данном регионе
        $api_key = $GLOBALS['config']['api-keys']['open-weather'];
        $url = "https://api.openweathermap.org/data/2.5/weather?q=$city&appid=$api_key&lang=Ru";

        $response = file_get_contents($url);
        $weather_data = json_decode($response, 1);

        $temp = ceil($weather_data['main']['temp'] - 273) == 0 ? '0' : ceil($weather_data['main']['temp'] - 273);

        //Устанавливаем срок жизни cookie 1 час
        setcookie('weather', trim((string)$temp), time() + 60 * 60, '/');

        return trim((string)$temp);
    }

    private function location_informer()
    {
        //Определеяем местоположение
        $url = 'http://ip-api.com/json/' . $_SERVER['REMOTE_ADDR'] . '?lang=ru';
        $response = curl($url);
        $location_data = json_decode($response, 1);

        /*try {
           // $location_data['weather'] = ($_COOKIE['weather'] ? $_COOKIE['weather'] : $this->weather_informer($location_data['city'])) . "&#8451; ";
        } catch (Exception $e) {
            // exception is raised and it'll be handled here
            pre($e->getMessage());// contains the error message
        }*/

        return $location_data;
    }

    private function get_currency()
    {

        $url = 'https://www.cbr.ru/scripts/XML_daily.asp'; // Ссылка на XML-файл с курсами валют, будут самые актуальные значения курса
        $currencies = curl($url);
        $xml = @simplexml_load_string($currencies);

        $general_currencies = ['R01235' => 'usd', 'R01239' => 'eur', 'R01035' => 'gbp'];
        $result = [];

        foreach ($xml->Valute as $item) {
            if (in_array($item['ID'], array_keys($general_currencies))) {
                $json = json_encode($item);
                $array = json_decode($json, TRUE);
                $result[] = ['name' => $array['Name'], 'value' => $array['Value'], 'icon' => $array['CharCode']];
            }

        }

        return $result;
    }

    public function __construct($query = [], $async = false)
    {
        //Для ajax запросов
        if ($async) {
            $method = strval($query['method']) ? strval($query['method']) : 'init';
            if (!method_exists($this, $method)) {
                json(['result' => false, 'message' => 'Запрашиваемый метод отсутствует', 'data' => $query]);
                return false;
            }
            $this->$method($query);
            exit();
        }

        //автоматическая авторизация
        session_start();
        if (!$_SESSION['user'] && $_COOKIE['username'] && $_COOKIE['password'])
            $this->auth_cookie();

        //Опредеяем action страницы
        $action = trim($query[1]);
        $action = explode(".", $action);
        $action = $action[0];

        //pre($action);

        //Задаем список лайкнутых публикаций
        if ((int)$_SESSION['user']['id']) {
            self::$liked_publics = $this->get_liked_publics((int)$_SESSION['user']['id']);
            self::$liked_comments = $this->get_liked_comments((int)$_SESSION['user']['id']);
        }

        require_once dirname(__DIR__) . '/models/main.model.php';
        $model = new MainModel();

        //pre(self::$liked_publics);

        //Дата первой публикации
        $published_date_start = $model->get_public_published_date_start();
        //$this->components['published_date_start'] = $published_date_start;
        //pre($published_date_start);

        //Выводим список доступных категорий
        $categories = $model->get_categories(0, 1);

        //Убираем категории 18+
        $categories = array_filter($categories, function ($item) {
            return !$item['is_hidden'];
        });

        if ($action == 'category')
            $GLOBALS['category'] = trim(end($query));

        $categories = array_map(function ($item) {
            return ['name' => $item['name'], 'href' => '/publication/category/' . $item['id'] . '/' . $item['name'] . '/', 'is_current' => $GLOBALS['category'] == $item['name']];
        }, (array)$categories);

        $categories_dropdown = array_map(function ($item) {
            return "<a class='dropdown-item' href='$item[href]'>$item[name]</a>";
        }, $categories);

        $categories_dropdown = implode("", $categories_dropdown) . "<div class='dropdown-divider'></div><a class='dropdown-item' href='/publication/categories/'>Все категории</a>";

        $categories_dropdown = <<<DROPDOWN
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle"  id="navbarDropdown-categories" role="button"
       data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        Категории
    </a>
    <div class="dropdown-menu" aria-labelledby="navbarDropdown-categories">
    $categories_dropdown
    </div>
</li>
DROPDOWN;

        if ($action == "date") {
            $date_from = trim($query[2]);
            $date_to = trim($query[3]);
        } elseif ($action == "show") {
            $publication_data = $query[2];
            $publication_id = (int)current(explode("::", $publication_data));
        }


        //Вывод компонентов навигации и подвала
        $profile_area = render('components', 'nav/profile-area', $_SESSION['user']);


        //Дата
        $workdays = [1 => 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота', 'Воскресенье'];
        $workday = $workdays[(int)date('N')];
        $location = $this->location_informer(); //Информер погоды

        $currency = $this->get_currency();
        $currencyHTML = '';
        foreach ($currency as $item)
            $currencyHTML .= "<i class='fa fa-" . strtolower($item['icon']) . "' aria-hidden='true' title='$item[name]'></i> " .
                round((float)str_replace(',', '.', $item['value']), 2) . "&nbsp;&nbsp;";


        $time = '<span id="time">' . date('H:i') . '</span>';
        $today_info =
            trim($currencyHTML, "&nbsp;") . '<br>' .
            $location['weather'] .
            '<i class="fa fa-map-marker" aria-hidden="true"></i> ' . $location['country'] . ' ' . $location['city'] .
            '<br>' . $workday . ', ' . date_rus_format(date('Y-m-d'), ['upper' => 1]) . ' ' . $time;


        $this->components['nav'] = render('components', 'nav/nav',
            [
                'profile_area' => $profile_area,
                'action' => $query[0],
                'categories_dropdown' => $categories_dropdown,
                'published_date_start' => $published_date_start,
                'date_from' => $date_from,
                'date_to' => $date_to,
                'today_info' => $today_info
            ]);
        $this->components['footer'] = render('components', 'footer');

        //pre($categories);


        $categories = array_chunk($categories, ceil(count($categories) / 2));
        $this->components['categories_left'] = render('public/show', 'li_link_element', $categories[0]);
        $this->components['categories_right'] = render('public/show', 'li_link_element', $categories[1]);

        $sidebar_publics = $model->get_sidebar_publics($publication_id);


        $this->components['sidebar-publics'] = !empty($sidebar_publics) && in_array(get_called_class(), ["Publication"])
            ? render('public/show', 'similar-item', $sidebar_publics)
            : "";


        $this->components['categories_right'] = render('public/show', 'li_link_element', $categories[1]);

        if ($action && $action != 'index') {
            //Запускаем action, если есть соответствующий метод
            if (method_exists($this, $action)) {
                $this->$action($query);
                exit();
            } else
                exit404($query);
        }
        return true;
    }

    //Для авторизации при входе на сайт, если уже авторизовался ранее
    private function auth_cookie()
    {
        session_start();
        $username = $_COOKIE['username'];
        $password = $_COOKIE['password'];
        require_once dirname(__DIR__) . '/models/profile.model.php';
        $model = new ProfileModel();
        $profile = $model->auth($username, $password);
        if (!empty($profile)) {
            $_SESSION['user'] = $profile;
            header("Refresh:0");
        }
    }


    protected function get_publications($filter = [], $return = false)
    {

        require_once dirname(__DIR__) . '/models/publication.model.php';

        $publication = new PublicationModel();

        $called_class = get_called_class();

        if ($called_class == 'Profile')
            $filter['user-zone'] = 1;
        elseif ($called_class == "Manager")
            $filter['manager-zone'] = 1;

        $this->pagination_limit = $GLOBALS['config']['publications']['pagination-limit'];
        $this->page = (int)$_GET['page'] ? (int)$_GET['page'] : 1;

        $this->offset = $this->pagination_limit * ($this->page - 1);//Отступ, если страница не первая
        $this->offset = $this->offset >= 0 ? $this->offset : 0;
        $publications = $publication->get_publications($this->offset, $filter);

        $publications_content = array_filter($publications, function ($item) {
            return $item['public_img'] != "";
        });
        $this->components['data_image'] = $publications_content[0]['public_img'];

        if ($return)
            return $publications;

        //pre($_SESSION['user']);


        //Slider
        if (!$filter && $_SESSION['user']['show_slider']) {
            $publications_slider = $publication->get_publications($this->offset, $filter, 1);
            $this->components['slider'] = count((array)$publications_slider) > 3
                ? render('components', 'slider', ['publications_slider' => $publications_slider])
                : "";
        }


        $content = !empty($publications)
            ? render('public/show', 'preview', $this->convert_title($publications)) . $this->pagination_constructor($filter)
            : '<p class="lead">Публикации отсутствуют...</p>';

        if ($filter['filter'] == 'author') {
            $author_data = $publication->getter('users', ['id' => $filter['value']]);
            $author_data[0]['manager_controls'] = render('manager', 'user-item', $author_data);
            $author_data[0]['publication_count'] = count((array)$publication->getter(
                'publications',
                [
                    'user_id' => $filter['value'],
                    'is_published' => 1,
                    'moderated' => 1,
                    'is_deleted' => 0
                ]));
            $content = render('public/show', 'author', $author_data[0]) . "<hr>" . $content;
        }

        page($content, $this->components);
        return true;
    }

    //Постоение и вывод блока пагинации
    protected function pagination_constructor($filter = [])
    {
        require_once dirname(__DIR__) . '/models/publication.model.php';
        $publication = new PublicationModel();

        if (in_array(get_called_class(), ['Profile', 'Manager']))
            $filter['user-zone'] = 1;

        $publication_total_count = $publication->get_total_count($filter);
        $this->pages = ceil($publication_total_count / $this->pagination_limit);

        $this->calledClassName = get_called_class();

        if ($this->page < 1)
            $this->page = 1;
        elseif ($this->page > $this->pages)
            $this->page = $this->pages;

        if ($this->pages > 1) {
            $pages_array = range(1, $this->pages);
            $PreviousDisabled = $this->page == 1 ? 'disabled' : '';
            $pages = implode("", array_map(function ($page) {
                //Разные ссылки для страницы публикаций и для страницы пользователя
                $href = $this->calledClassName == 'Profile' ? '?tab=publications&page=' . $page : '?page=' . $page;
                return $page == $this->page
                    ? "<li class='page-item active'><span class='page-link'>$page<span class='sr-only'>(current)</span></span></li>"
                    : "<li class='page-item'><a class='page-link' href='$href'>$page</a></li>";
            }, $pages_array));
            $NextDisabled = $this->page == $this->pages ? 'disabled' : '';
            $PreviousPage = $this->page - 1;
            $NextPage = $this->page + 1;
            $href = $this->calledClassName == 'Profile' ? '?tab=publications&' : '?';
            return render('components', 'pagination',
                [
                    'pages' => $pages,
                    'PreviousDisabled' => $PreviousDisabled,
                    'NextDisabled' => $NextDisabled,
                    'PreviousPage' => $PreviousPage,
                    'NextPage' => $NextPage,
                    'href' => $href
                ]);
        }

        return '';

    }

    //Постоение и вывод блока пагинации
    protected function comment_pagination_constructor($user_id = false)
    {
        require_once dirname(__DIR__) . '/models/publication.model.php';
        $publication = new PublicationModel();

        if (in_array(get_called_class(), ['Profile', 'Manager']))
            $filter['user-zone'] = 1;

        $comments_total_count = count($publication->get_user_comments($user_id, 1));
        $this->comments_pages = ceil($comments_total_count / $this->comments_pagination_limit);

        $this->calledClassName = get_called_class();

        switch ($this->calledClassName) {
            case "Manager":
                $GLOBALS['href'] = "?tab=comments&";
                break;
        }


        if ($this->comments_pages > 1) {
            $pages_array = range(1, $this->pages);
            $PreviousDisabled = $this->page == 1 ? 'disabled' : '';
            $pages = implode("", array_map(function ($page) {
                return $page == $this->page
                    ? "<li class='page-item active'><span class='page-link'>$page<span class='sr-only'>(current)</span></span></li>"
                    : "<li class='page-item'><a class='page-link' href='$GLOBALS[href]page=$page'>$page</a></li>";
            }, $pages_array));
            $NextDisabled = $this->page == $this->pages ? 'disabled' : '';
            $PreviousPage = $this->page - 1;
            $NextPage = $this->page + 1;

            return render('components', 'pagination',
                [
                    'pages' => $pages,
                    'PreviousDisabled' => $PreviousDisabled,
                    'NextDisabled' => $NextDisabled,
                    'PreviousPage' => $PreviousPage,
                    'NextPage' => $NextPage,
                    'href' => $GLOBALS['href']
                ]);
        }

        return '';

    }

    //Подсчет фото/видео, содержащегося в конкретной публикации
    protected function convert_title($publications)
    {
        return array_map(function ($item) {
            $title = $item['title'];
            $media_counter = '';

            if ((int)$item['img_counter'])
                $media_counter_img = $item['img_counter'] . '&nbsp;фото';
            if ((int)$item['video_counter'])
                $media_counter_video = $item['video_counter'] . '&nbsp;видео';

            if ($media_counter_img && $media_counter_video)
                $media_counter = " ($media_counter_img и $media_counter_video)";
            elseif (!$media_counter_img && $media_counter_video)
                $media_counter = " ($media_counter_video)";
            elseif ($media_counter_img && !$media_counter_video)
                $media_counter = " ($media_counter_img)";

            $item['title'] = $title . $media_counter;
            return $item;

        }, $publications);
    }

    protected function convert_title_2($title, $img_counter = 0, $video_counter = 0)
    {
        $media_counter = '';

        if ($img_counter)
            $media_counter_img = $img_counter . '&nbsp;фото';
        if ($video_counter)
            $media_counter_video = $video_counter . '&nbsp;видео';

        if ($media_counter_img && $media_counter_video)
            $media_counter = " ($media_counter_img и $media_counter_video)";
        elseif (!$media_counter_img && $media_counter_video)
            $media_counter = " ($media_counter_video)";
        elseif ($media_counter_img && !$media_counter_video)
            $media_counter = " ($media_counter_img)";

        return $title . $media_counter;

    }

    protected function comment_builder($comments)
    {
        $commentsResult = [];
        foreach ($comments as $item) {
            $item['replies'] = [];
            if ($item['parent_id'] && $item['is_reply'])
                continue;
            $commentsResult[$item['id']] = $item;
        }

        foreach ($comments as $item) {
            if ($item['parent_id'] && $item['is_reply'])
                $commentsResult[$item['parent_id']]['replies'][] = $item;
        }

        foreach ($commentsResult as $id => $item) {
            if (!empty($item['replies']))
                $commentsResult[$id]['replies'] = render('public/show/comments', 'comment-item-reply', $item['replies']);
            else
                $commentsResult[$id]['replies'] = '';
        }

        return $commentsResult;
    }

    //хлебные крошки
    protected function breadcrumb($id, $active_name = false)
    {
        require_once dirname(__DIR__) . '/models/publication.model.php';
        $PublicationModel = new PublicationModel();

        session_start();
        $_SESSION['breadcrumb-position'] = 1;

        if (!$id && $active_name)
            $breadcrumb = render('components', 'breadcrumb-item', ['name' => $active_name, 'is_active' => 1]);
        else {
            $breadcrumb = $PublicationModel->get_breadcrumb($id);

            if ($active_name) {
                $breadcrumb = render('components', 'breadcrumb-item', array_reverse($breadcrumb)) .
                    render('components', 'breadcrumb-item', ['name' => $active_name, 'is_active' => 1]);
            } else {
                $breadcrumb[0]['is_active'] = 1;
                $breadcrumb = render('components', 'breadcrumb-item', array_reverse($breadcrumb));
            }
        }

        return render('components', 'breadcrumb', ['breadcrumb' => $breadcrumb]);
    }


}