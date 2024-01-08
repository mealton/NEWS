<?php

/**
 * Class Publication
 * Класс для работы с публикациями
 */

class Publication extends Main
{

    private $publication_author_id;

    public function init()
    {

    }

    //action для поиска публикаций
    protected function search($query)
    {

        if(count($query) > 2)
            exit404($query);

        $search = trim(urldecode($_GET['search']));
        if (!$search) {
            page("<p>Не задан критерий поиска...</p>", $this->components);
            exit();
        }

        $this->components['breadcrumb'] = $this->breadcrumb('', $search);
        $this->components['title'] = $search;

        $filter = ['filter' => 'search', 'value' => $search];
        $this->get_publications($filter);
    }

    //Вывод подсказок во время печатания строки поиска
    protected function keyup_search($data)
    {
        require_once dirname(__DIR__) . '/models/publication.model.php';
        $publication = new PublicationModel();
        $search = $publication->get_publications(0, ['filter' => 'search', 'value' => trim($data['search'])]);
        $GLOBALS['search-value'] = trim($data['search']);
        $search = array_map(function ($item) {
            $title = preg_replace('/(' . $GLOBALS['search-value'] . ')/iu', '<b>$1</b>', strip_tags(htmlspecialchars_decode($item['title'])));
            $href = "/publication/show/$item[id]::$item[alias].html";
            $image = "<img src='$item[public_img]' class='search-img' alt='' />";
            return "<li class='list-group-item'><a href=\"$href\">$image <span>$title</span></a></li>";
        }, $search);
        json($search);
    }

    public function __construct($query = [], $async = false)
    {
        parent::__construct($query, $async);//авторизация, вывод основных компонентов страницы
        //$this->components['extra-scripts'] = ['edit-public'];
        $this->get_publications();
    }

    //action для вывода публикаций по хештегам
    protected function tags($query = [], $async = false)
    {
        $tag = trim(urldecode($query[2]));
        if (!$tag)
            exit404($query);

        $this->components['title'] = '#' . $tag;
        //$this->components['extra-scripts'] = ['edit-public'];
        $this->components['breadcrumb'] = $this->breadcrumb('', $tag);
        $filter = ['filter' => 'tag', 'value' => $tag];
        $this->get_publications($filter);
    }

    //Минимум лайков для вывода публикации в топ
    private $top = 1;

    //action для вывода топа публикаций
    protected function top($query = [], $async = false)
    {
        if(count($query) > 2)
            exit404($query);
        $this->components['title'] = 'Топ';
        $this->components['breadcrumb'] = $this->breadcrumb('', 'Топ');
        $filter = ['filter' => 'top', 'value' => $this->top];
        $this->get_publications($filter);
    }

    //action для вывода публикаций по категориям
    protected function category($query = [], $async = false)
    {

        if(count($query) > 4)
            exit404($query);

        $category_id = intval($query[2]);
        if (!$category_id)
            exit404($query);
        $category = strval(trim(urldecode($query[3])));
        if (!$category)
            exit404($query);

        $this->components['breadcrumb'] = $this->breadcrumb($category_id);

        require_once dirname(__DIR__) . '/models/publication.model.php';
        $model = new PublicationModel();
        $category_data = $model->getter('categories', ['id' => $category_id]);

        $this->components['title'] = $category_data[0]['name'];

        if(translit($category_data[0]['name']) != $category)
            exit404($query);

        $this->components['keywords'] = $category_data[0]['keywords'];
        //$this->components['extra-scripts'] = ['edit-public'];

        //вывод подкатегорий, если такие имеются
        $subcategories = $this->get_subcategories($category_id);

        if ($subcategories[0]['public_count'])
            $this->components['subcategories'] = "<div class='row'>" . render('public/show', 'category-card', $subcategories) . '</div><hr>';
        $filter = ['filter' => 'category', 'value' => $category_id];
        $this->get_publications($filter);

    }

    //action для вывода публикаций за определенный день
    protected function date($query = [], $async = false)
    {

        if(count($query) > 4)
            exit404($query);

        $date_from = trim($query[2]);
        $date_to = trim($query[3]);

        $pattern  = "/\d{4}-\d{2}-\d{2}/";
        if(!preg_match($pattern, $date_from) || !preg_match($pattern, $date_from))
            exit404($query);

        if ($date_from == $date_to)
            $title = "Публикации за " . date_rus_format($date_from);
        else
            $title = "Публикации от " . date_rus_format($date_from) . " до " . date_rus_format($date_to);

        $this->components['breadcrumb'] = $this->breadcrumb(false, $title);
        $this->components['title'] = $title;
       // $this->components['extra-scripts'] = ['edit-public'];
        $filter = ['filter' => 'date', 'value' => $date_from . "::" . $date_to];
        $this->get_publications($filter);
    }

    //Подкатегории
    private function get_subcategories($category_id)
    {
        require_once dirname(__DIR__) . '/models/publication.model.php';
        $publication = new PublicationModel();
        return $publication->get_subcategories($category_id);
    }

    //Отображение одной публикации
    public function show($query)
    {

        $publication = trim(end($query));

        if (!$publication)
            exit404($query);
        $publication = explode("::", $publication);
        $publication_id = $publication[0];
        $alias = $publication[1];
        if (!(int)$publication_id)
            exit404($query);


        require_once dirname(__DIR__) . '/models/publication.model.php';
        $PublicationModel = new PublicationModel();
        $publication = $PublicationModel->get_publication($publication_id, $alias);

        //История
        $history = $PublicationModel->getter('users', ['id' => $_SESSION['user']['id']], 'history');
        $history = unserialize($history[0]['history']);
        $history = is_array($history) ? $history : [];
        $history[$publication_id] = time();
        $PublicationModel->update('users', ['history' => serialize($history)], $_SESSION['user']['id']);

        $this->publication_author_id = $publication[0]['user_id'];

        if (empty($publication))
            exit404($query);

        $liked_publics = $PublicationModel->getter('users', ['id' => $_SESSION['user']['id']], 'liked_publics');
        if ($liked_publics[0]['liked_publics'])
            $liked_publics = unserialize($liked_publics[0]['liked_publics']);
        else
            $liked_publics = [];

        $publication[0]['is_liked'] = in_array($publication[0]['publication_id'], array_keys($liked_publics));

        $publication[0]['media_title'] = $this->convert_title_2($publication[0]['title'], $publication[0]['img_counter'], $publication[0]['video_counter']);

        //pre($publication);

        $publication = pre_show($publication);
        $this->components['title'] = $publication[0]['title'];
        $this->components['description'] = $publication[0]['introtext'];
        $this->components['extra-vendors'] = ['giffer' => 'javascript-giffer'];
        $this->components['extra-scripts'] = ['edit-public'];

        $publication[0]['hashtags'] = array_map(function ($hashtag) {
            return trim($hashtag);
        }, explode(",", trim($publication[0]['hashtags'])));

        $this->components['breadcrumb'] = $this->breadcrumb($publication[0]['category_id'], $publication[0]['title']);
        $this->components['keywords'] = implode(", ", array_slice($publication[0]['hashtags'], 0, 20));

        $currentLikesCount = $publication[0]['likes'];
        $previousLikesCount = $currentLikesCount - 1;
        $nextiLkesCount = $currentLikesCount + 1;
        $previousLikesCount = $previousLikesCount < 0 ? 0 : $previousLikesCount;

        $previousLikesCount = '<span>' . implode('</span><span>', str_split((string)$previousLikesCount)) . '</span>';
        $currentLikesCount = '<span>' . implode('</span><span>', str_split((string)$currentLikesCount)) . '</span>';
        $nextLikesCount = '<span>' . implode('</span><span>', str_split((string)$nextiLkesCount)) . '</span>';

        $publication[0]['likes'] = <<<LIKES
<span class="previous-likes-count count">$previousLikesCount</span>
<span class="current-likes-count count">$currentLikesCount</span>
<span class="next-likes-count count">$nextLikesCount</span>
LIKES;

        $publication_content = render('public', 'switch_tag', $publication);

        if ($publication[0]['image_default'])
            $this->components['data_image'] = $publication[0]['image_default'];
        else {
            $publication_content_image = array_filter($publication, function ($item) {
                return $item['content'] != "" && $item['tag'] == "image";
            });
            $this->components['data_image'] = $publication_content_image[0]['content'];
        }


        $publication[0]['publication_content'] = $publication_content;
        $publication[0]['data_image'] = $this->components['data_image'];

        $publication_header = render('public/show', 'publication_header', $publication[0]);

        //Ищем похожие публикации
        $similar = $PublicationModel->get_similar($publication_id);
        //pre($similar);
        $this->components['similar'] = !empty($similar)
            ? render('public/show', 'similar-item', $similar)
            : "";


        //Форма добавления комментариев
        $comment_form = render('public/show/comments', 'comment-form', $publication[0]);
        //Список коментариев

        $comments = $PublicationModel->get_comments($publication_id);

        $comments = array_map(function ($item) {
            $item['is_author'] = $item['user_id'] == $this->publication_author_id;
            $item['commet_is_liked'] = in_array($item['id'], Main::$liked_comments);
            return $item;
        }, (array)$comments);

        //pre($comments);

        if (!empty($comments))
            $comments = render('public/show/comments', 'comment-item', $this->comment_builder($comments));
        else
            $comments = "<br><p class='lead'>Комментарии пока отсутствуют...</p>";

        $content = render('public/show', 'publication', [
            'publication_header' => $publication_header,
            'publication_content' => $publication_content,
            'comment_form' => $comment_form,
            'comments' => $comments,
            'user_id' => $publication[0]['user_id'],
            'publication_id' => $publication_id,
            'source' => $publication[0]['source_url']
        ]);

        page($content, $this->components);
    }

    private function prepare_publication($data)
    {
        session_start();
        require_once dirname(__DIR__) . '/models/publication.model.php';
        $publication = new PublicationModel();
        $public = pre_insert($data['publication']['head']);
        $public['new_category'] = pre_insert($data['publication']['head']['new_category']);

        if ($public['new_category']['name'] && !$publication->check_existence('categories', ['name' => $public['new_category']['name']]))
            $public['category_id'] = $publication->insert('categories', $public['new_category']);

        if ($public['source_url']) {
            $containers = $data['publication']['head']['import_containers'];
            $containers['host'] = get_from_url($public['source_url']);
            if (!$publication->check_existence('import_containers', ['host' => $containers['host']]))
                $publication->insert('import_containers', $containers);
        }

        $public['alias'] = translit($public['title']);
        $public['moderated'] = $_SESSION['user']['is_admin'];
        return $public;
    }

    //добавление новой публикации
    protected function add($data)
    {
        require_once dirname(__DIR__) . '/models/publication.model.php';
        $publication = new PublicationModel();
        $public = $this->prepare_publication($data);

        //Добавляем шапку
        $publication_id = $publication->insert('publications', $public);

        //Добавляем контент
        foreach ($data['publication']['body'] as $item) {
            $item = pre_insert($item);
            $item['publication_id'] = $publication_id;
            $publication->insert('content', $item);
        }

        //Добавляем хештеги
        $hashtags = explode(",", trim($data['publication']['head']['hashtags']));
        if (!empty($hashtags)) {
            $hashtags = array_map(function ($hashtag) {
                return trim($hashtag);
            }, $hashtags);
            foreach ($hashtags as $hashtag)
                $publication->insert('hashtags', ['publication_id' => $publication_id, 'name' => $hashtag]);
        }

        json(['result' => $publication_id, 'publication' => $public]);
    }

    //изменение публикации
    protected function update($data)
    {
        session_start();
        if (!(int)$data['publication']['head']['id']) {
            json(['result' => false, 'message' => 'Не передан id публикации!!!', 'data' => $data]);
            return false;
        }

        require_once dirname(__DIR__) . '/models/publication.model.php';
        $publication = new PublicationModel();

        $public = $this->prepare_publication($data);
        $public['token'] = md5(generateRandomString(100));

        if((int)$public['update-date'])
            $public['published_date'] = date('Y-m-d H:i:s');

        if (!$_SESSION['user']['is_admin'])
            $public['moderated'] = 0;

        //Изменяем шапку
        $head_update_result = $publication->update('publications', $public, $public['id']);

        if (!$head_update_result) {
            json(['result' => false, 'message' => 'Ошибка обновления шапки!!!']);
            return false;
        }

        //Добавляем контент
        foreach ($data['publication']['body'] as $item) {
            $item = pre_insert($item);
            $item['text'] = str_replace(["\n", "<br><br>"], ["<br>", "<br>"], $item['text']);
            $item['publication_id'] = $public['id'];
            $item['token'] = $public['token'];
            $publication->insert('content', $item);
        }

        $publication->remove_old_content($public['id'], $public['token']);

        //Удаляем старые хештеги
        $publication->delete('hashtags', $public['id'], 'publication_id');

        //Добавляем хештеги
        $hashtags = explode(",", trim($data['publication']['head']['hashtags']));
        if (!empty($hashtags)) {
            $hashtags = array_map(function ($hashtag) {
                return trim($hashtag);
            }, $hashtags);
            foreach ($hashtags as $hashtag)
                $publication->insert('hashtags', ['publication_id' => $public['id'], 'name' => $hashtag]);
        }

        json(['result' => $public['id'], 'action' => 'update', 'publication' => $public]);
        return true;
    }

    //проверка наличия названия категории при попытке создать новую
    protected function check_new_category($data)
    {
        $category = strval(trim($data['category']));
        require_once dirname(__DIR__) . '/models/publication.model.php';
        $publication = new PublicationModel();
        json(['result' => !$publication->check_existence('categories', ['name' => $category])]);
    }

    //Предлагает названия селекторов публикации при импорте
    protected function get_import_containers($data)
    {
        $parse_url = parse_url($data['url']);
        $host = $parse_url['host'];
        if (!$host) {
            json(['result' => false, 'message' => 'Не опренделен хост']);
            return false;
        }
        require_once dirname(__DIR__) . '/models/publication.model.php';
        $publication = new PublicationModel();
        json(current((array)$publication->getter('import_containers', ['host' => $host])));
        return true;
    }

    //Добавление нового блока при создании новой публикации
    protected function add_publication_item($data)
    {
        $tag = (string)trim($data['tag']);
        $content = (string)trim($data['content']);
        switch ($tag) {
            case 'text':
                $content = "<p>$content</p>";
                break;
            case 'subtitle':
                $content = "<h3>$content</h3>";
                break;
        }
        $item = render('public/edit/item/', $tag, ['content' => $content, 'style' => $data['style']]);
        json(['result' => $item, 'data' => $data]);
    }

    //Загрузка изображения
    private function upload($src)
    {
        $image = curl($src);
        $fileName = time() . rand(0, 100000) . time() . '.jpg';
        $folder = '/assets/uploads/img/public/' . $fileName;
        $upload_dir = $_SERVER['DOCUMENT_ROOT'] . $folder;
        file_put_contents($upload_dir, $image);
        return getimagesize($upload_dir) ? $folder : false;
    }

    //Отмена создания публикации, удаление всех загруженных для нее изображений
    protected function cancel($data)
    {
        $images = array_map(function ($src) {
            $file = str_replace($_SERVER['HTTP_ORIGIN'], $_SERVER['DOCUMENT_ROOT'], $src);
            if (file_exists($file))
                return unlink($file);
            return false;
        }, (array)$data['images']);
        json($images);
    }

    //Импорт публикации с другого сайта
    protected function import($data)
    {
        session_start();
        require_once dirname(__DIR__) . "/simple_html_dom.php";

        $url = $data['url'];
        $content_container = $data['content_container'] ? $data['content_container'] : 'body';
        $tags_container = $data['tags_container'] ? $data['tags_container'] : '.tags';

        $html = file_get_html($url);
        $dom = str_get_html($html);

        $metaTags = get_meta_tags($url);
        $h1 = $dom->find("h1", 0)->plaintext;
        $title = $h1 ? $h1 : $metaTags['title'];

        $title = htmlspecialchars_decode($title);
        $title = html_entity_decode($title);

        $media = [];
        $tags = $dom->find($tags_container, 0);
        $content_elements = $dom->find($content_container, 0);

        if ($tags) {
            $tags = is_array($tags->find("a")) ? $tags->find("a") : [];

            $tagsArray = array_map(function ($tag) {
                return $tag->plaintext;
            }, $tags);
        }

        if (!$content_elements) {
            json(['publication' => '', 'message' => 'Контент не найден...']);
            return false;
        }

        $elements = (int)$data['only_img']
            ? $content_elements->find("img")
            : $content_elements->find("img, video, iframe, p, h2 h3, h4, h5, h6, span, div");

        foreach ($elements as $element) {
            if ($element->tag == 'img') {
                $tag = 'image';
                $src = $this->upload($element->src);
                if (!$src)
                    continue;
                $content = '<img src="' . $src . '" data-source="' . $element->src . '" alt="" class="publication-image-item img-fluid d-block">';
            } elseif (in_array($element->tag, ['h2', 'h3', 'h4', 'h5', 'h6'])) {
                $tag = 'subtitle';
                $subtitle = $element->plaintext;
                if (!$subtitle)
                    continue;
                $content = '<h2>' . $subtitle . '</h2>';
            } elseif (in_array($element->tag, ['video', 'iframe'])) {
                $tag = 'video';
                $content = $element->outertext;
            } else {
                $tag = 'text';
                if (!$element->plaintext)
                    continue;
                $content = '<p>' . $element->plaintext . '</p>';
            }
            $media[] = ['tag' => $tag, 'content' => $content, 'source' => $element->src, 'description' => '', 'item_folder' => 'edit'];
        }

        $publication = [
            'alias' => translit($title),
            'title' => $title,
            'introtext' => $metaTags['description'],
            'user_id' => $_SESSION['user']['id'],
            'hashtags' => implode(", ", $tagsArray),
            'imported' => $url,
            "comment" => 'Импортированно на сайта ' . get_from_url($url),
            'media' => $media,
            'content' => render('public', 'switch_tag', $media)
        ];

        json(['publication' => $publication]);
        return true;
    }

    //action для вывода недавних публикаций
    protected function recent($query = [], $async = false)
    {
        if(count($query) > 2)
            exit404($query);
        $this->components['breadcrumb'] = $this->breadcrumb('', 'Последние добавленные публикации');
        $this->components['title'] = 'Последние добавленные публикации';
        $filter = ['filter' => 'recent', 'value' => 1];
        $this->get_publications($filter);
    }

    //action для вывода публикаций определенного автора
    protected function authors($query = [], $async = false)
    {
        if(count($query) > 3)
            exit404($query);
        $author = trim($query[2]);
        if (!$author) {
            //$author
            require_once dirname(__DIR__) . '/models/publication.model.php';
            $publication = new PublicationModel();
            $authors = $publication->get_authors();
            $this->components['breadcrumb'] = $this->breadcrumb('', "Все авторы");
            $this->components['title'] = 'Все авторы';
            $content = !empty($authors)
                ? render('public/show', 'author_card', $authors)
                : '<p>Авторов пока нет...</p>';

            page("<div class=\"row justify-content-around\">" . $content . "</div>", $this->components);
            return true;
        }

        $author = explode("::", $author);
        $author_id = (int)$author[0];
        $author_name = (string)$author[1];
        if ((!$author_id || !$author_name) || !$this->get_user($author_id))
            exit404($query);

        $this->components['breadcrumb'] = $this->breadcrumb('', $author_name);
        $this->components['title'] = 'Публикации пользователя ' . $author_name;
        //$this->components['extra-scripts'][] = 'manager';
        $filter = ['filter' => 'author', 'value' => $author_id];
        $this->get_publications($filter);
        return true;
    }

    private function get_user($user_id)
    {
        require_once dirname(__DIR__) . '/models/publication.model.php';
        $publication = new PublicationModel();
        return $publication->check_existence('users', ['id' => $user_id]);
    }

    protected function publish($data)
    {
        $id = (int)$data['id'];
        if (!$id) {
            json(['result' => false, 'message' => 'Не задан идентификатор публикации']);
            return false;
        }

        require_once dirname(__DIR__) . '/models/publication.model.php';
        $publication = new PublicationModel();
        $result = $publication->update('publications', ['is_published' => $data['is_published']], $id);
        json($result[0]);
        return true;
    }

    protected function delete($data)
    {
        $id = (int)$data['id'];
        if (!$id) {
            json(['result' => false, 'message' => 'Не задан идентификатор публикации']);
            return false;
        }

        require_once dirname(__DIR__) . '/models/publication.model.php';
        $publication = new PublicationModel();
        $result = $publication->update('publications', ['is_deleted' => $data['is_deleted']], $id);
        json(['publication' => $result[0], 'trash_cleaner' => $publication->check_existence('publications', ['is_deleted' => 1])]);
        return true;
    }

    protected function trash_cleaner()
    {
        require_once dirname(__DIR__) . '/models/publication.model.php';
        $publication = new PublicationModel();
        $result = $publication->trash_cleaner();
        json(['result' => $result, 'pagination' => $this->pagination_constructor()]);
        return true;
    }

    protected function like($data)
    {
        $id = (int)$data['id'];
        $user_id = (int)$data['user_id'];
        $dislike = 0;
        if (!$id || !$user_id) {
            json('Не переданы обязательные параметры');
            return false;
        }
        require_once dirname(__DIR__) . '/models/publication.model.php';
        $publication = new PublicationModel();
        $liked_publics = $this->get_liked_publics($user_id);

        if (in_array($id, array_keys($liked_publics)) && $publication->dislike($id)) {
            unset($liked_publics[$id]);
            $dislike = 1;
        } elseif (!in_array($id, array_keys($liked_publics)) && $publication->like($id))
            $liked_publics[$id] = 1;

        Publication::$liked_publics = $liked_publics;

        $liked_publics = serialize($liked_publics);
        $result = $publication->update('users', ['liked_publics' => $liked_publics], $user_id);
        $likes = $publication->getter('publications', ['id' => $id], 'likes');
        json(['result' => $result, 'likes' => $likes[0]['likes'], 'dislike' => $dislike]);
        return true;
    }

    protected function like_comment($data)
    {
        $id = (int)$data['id'];
        $user_id = (int)$data['user_id'];
        $dislike = 0;
        if (!$id || !$user_id) {
            json('Не переданы обязательные параметры');
            return false;
        }
        require_once dirname(__DIR__) . '/models/publication.model.php';
        $publication = new PublicationModel();
        $liked_comments = $this->get_liked_comments($user_id);

        if (in_array($id, $liked_comments)) {
            if ($publication->dislike_comment($id)) {
                unset($liked_comments[$id]);
                $dislike = 1;
            }
        } else {
            if ($publication->like_comment($id))
                $liked_comments[$id] = 1;
        }

        Publication::$liked_comments = $liked_comments;

        $liked_comments = serialize($liked_comments);
        $result = $publication->update('users', ['liked_comments' => $liked_comments], $user_id);
        $likes = $publication->getter('comments', ['id' => $id], 'likes');
        json(['result' => $result, 'likes' => $likes[0]['likes'], 'dislike' => $dislike]);
        return true;
    }

    //комменты
    protected function comment($data)
    {
        session_start();
        $publication_id = (int)$data['publication_id'];
        $user_id = (int)$_SESSION['user']['id'];
        $comment = trim((string)$data['comment']);
        $image = trim((string)$data['image']);

        if (!$publication_id || !$user_id || (!$comment && !$image)) {
            json(['result' => false, 'message' => 'Не переданы обязательные поля', 'data' => $data]);
            return false;
        }

        $data = pre_insert($data);
        require_once dirname(__DIR__) . '/models/publication.model.php';
        $publication = new PublicationModel();
        $comment_id = $publication->insert('comments', [
            'publication_id' => $publication_id,
            'user_id' => $user_id,
            'comment' => $comment,
            'is_reply' => (int)$data['is_reply'],
            'parent_id' => (int)$data['parent_id'],
        ]);

        if (!$comment_id) {
            json(['result' => false, 'message' => 'Ошибка добавления комментарий...']);
            return false;
        }

        //Добавляем изображения
        if (trim($data['image']))
            $publication->insert('content',
                ['publication_id' => $comment_id, 'content' => $data['image'], 'tag' => 'comment']);

        $comment = $publication->get_comments($publication_id, $comment_id);
        $view_comment = (int)$data['is_reply'] ? 'comment-item-reply' : 'comment-item';
        $comment = render('public/show/comments', $view_comment, $comment);
        json(['result' => $comment_id, 'comment' => $comment]);
        exit();
    }

    protected function reply($data)
    {
        $parent_id = (int)$data['parent_id'];
        $user_id = (int)$data['user_id'];
        $publication_id = (int)$data['publication_id'];
        if (!$parent_id || !$user_id || !$publication_id) {
            json('Не переданы обязательные параметры');
            return false;
        }
        $data['show_cancel'] = 1;
        $form = render('public/show/comments', 'comment-form', $data);
        json(['result' => true, 'form' => $form]);
        return true;
    }

    protected function remove_comment($data)
    {
        $id = (int)$data['id'];
        if (!$id) {
            json('Не передан id');
            return false;
        }

        session_start();

        require_once dirname(__DIR__) . '/models/publication.model.php';
        $publication = new PublicationModel();

        $comment = $publication->getter('comments', ['id' => $id]);
        $comment = $comment[0];

        if ($_SESSION['user']['is_admin'] && $comment['is_complained'] && $data['manager']) {
            $result = $publication->update('comments', ['is_active' => 0], $id);
            json(['result' => $result, 'class' => get_called_class()]);
            return true;
        } elseif (!$_SESSION['user']['is_admin'] && $comment['is_complained']) {
            json(['result' => false, 'message' => 'Комментарий не может быть удалён']);
            return false;
        }

        if ($publication->check_existence('comments', ['parent_id' => $id, 'is_reply' => 1])) {
            json(['result' => false, 'message' => 'Комментарий не может быть удалён']);
            return false;
        }

        $publication->delete('comments', $id);
        $result = !$publication->check_existence('comments', ['id' => $id]);

        json(['result' => $result]);
        return true;
    }

    private function get_subs($item)
    {
        require_once dirname(__DIR__) . '/models/publication.model.php';
        $model = new PublicationModel();

        $id = $item['id'];
        $subcategories = $model->get_subcategories($id);

        if (empty($subcategories))
            return "";

        foreach ($subcategories as $i => $item_) {
            $subcategories[$i]['publications'] = render('sitemap', 'li-public', $model->getter('publications', ['is_published' => 1, 'moderated' => 1, 'is_deleted' => 0, 'category_id' => $item_['id']]));
            $subcategories[$i]['subs'] = $this->get_subs($item_);
        }


        return render('sitemap', 'li', $subcategories);
    }

    private function get_categories_tree($categories)
    {
        require_once dirname(__DIR__) . '/models/publication.model.php';
        $model = new PublicationModel();

        $categories = array_filter($categories, function ($item) {
            return !$item['parent_id'];
        });
        foreach ($categories as $i => $item) {
            $categories[$i]['publications'] = render('sitemap', 'li-public', $model->getter('publications', ['is_published' => 1, 'moderated' => 1, 'is_deleted' => 0, 'category_id' => $item['id']]));
            $categories[$i]['subs'] = $this->get_subs($item);
        }

        return render('sitemap', 'li', $categories);

    }

    protected function categories($query)
    {

        if(count($query) > 2)
            exit404($query);

        $_SESSION['p-counter'] = 0;
        $this->components['title'] = 'Все рубрики';
        require_once dirname(__DIR__) . '/models/publication.model.php';
        $publication = new PublicationModel();
        $categories = $publication->get_categories();

        $hashtags_data = (array)$publication->get_all_hashtags();
        $hashtags = render('public/show', 'hashtag', $hashtags_data);

        $sitemap = $this->get_categories_tree($categories);
        $categories_cards = render('public/show', 'category-card', $categories);
        $content = render('sitemap', 'content',
            [
                'categories_cards' => $categories_cards,
                'hashtags_counter' => count($hashtags_data),
                'hashtags' => $hashtags,
                'sitemap' => $sitemap
            ]);
        page($content, $this->components);

    }

    protected function complain($data)
    {
        $id = (int)$data['id'];
        if (!$id) {
            json('Не передан id');
            return false;
        }

        require_once dirname(__DIR__) . '/models/publication.model.php';
        $publication = new PublicationModel();

        $result = $publication->update('comments', ['is_complained' => 1], $id);
        json(['result' => $result]);
        return true;
    }

    protected function get_video_info($data)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_USERAGENT, filter_input(INPUT_SERVER, 'HTTP_USER_AGENT', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW));
        curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, 'https://www.youtube.com/oembed?url=https://www.youtube.com/watch?v=' . $data['id'] . '&format=json');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);

        $response = curl_exec($ch);
        curl_close($ch);
        $details = json_decode($response, 1); //parse the JSON into an array
        json(['title' => $details['title'], 'data' => $details]);
    }

}