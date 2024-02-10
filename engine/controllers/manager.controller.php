<?php

/**
 * Class Uploader
 * Класс для обеспечения работы администратора сайта
 */


class Manager extends Main
{

    public function __construct($query = [], $async = false)
    {

        session_start();
        if (!$_SESSION['user']['id']) exit403($query);

        parent::__construct($query, $async);
        $this->components['title'] = 'Зона администрирования';

        $this->components['extra-scripts'] = ['profile', 'edit-public', 'manager'];
        require_once dirname(__DIR__) . '/models/publication.model.php';
        $model = new PublicationModel();
        $manager = $model->getter('users', ['id' => $_SESSION['user']['id']]);

        if (!$manager[0]['is_admin']) exit403($query);

        $categories = $model->getter('categories', [], '*', ['order' => 'name']);

        foreach ($categories as $i => $item) {//Выбираем категорию
            $item['categories_list'] = [];
            foreach ($categories as $j => $categories_item) {
                $categories_item['parent_id_selected'] = $item['parent_id'];//Родительский id выбранной категории
                if ($categories_item['id'] != $item['id'])
                    $item['categories_list'][] = $categories_item;//Добавяем в список потенциальных родителей всех, кроме самой себя
            }
            $categories[$i]['categories_list'] = render('manager/categories',
                'category-item-option', $item['categories_list']);
            $breadcrumb = $this->breadcrumb_string($item['id']);
            $breadcrumb = fetch_to_array($breadcrumb, 'name');
            $categories[$i]['breadcrumb'] = implode(" / ", array_reverse($breadcrumb));
        }

        $publications = $this->get_publications([], true);

        $manager[0]['publications'] = !empty($publications)
            ? render('manager/public', 'preview', $this->convert_title($publications))
            : '<p class="lead">Публикации отсутствуют...</p>';


        $categories = render('manager/categories', 'category-item', $categories);

        $manager[0]['categories'] = render('manager/categories', 'categories', $categories);

        $manager[0]['categories'] = $categories;
        $manager[0]['uploader'] = render('components', 'uploader', ['upload_folder' => 'img/profile/', 'multiple' => 0, 'placeholder' => 'Изображение пользователя']);;

        $users = render('manager', 'user-item', $model->get_users());
        $manager[0]['users'] = render('manager', 'users', ['users' => $users]);

        if (!$_GET['tab'])
            $_GET['tab'] = 'publications';

        $comments = $model->get_user_comments();

        if (!empty($comments))
            $manager[0]['comments'] = render('manager', 'comment-item', $this->comment_builder($comments));
        else
            $manager[0]['comments'] = "<p class='lead'>Комментарии осутствут...</p>";

        $content = render('manager', 'index', $manager[0]);
        page($content, $this->components);

    }

    private function breadcrumb_string($id)
    {
        require_once dirname(__DIR__) . '/models/publication.model.php';
        $model = new PublicationModel();
        $breadcrumb = $model->get_breadcrumb($id);
        return $breadcrumb;
    }

    protected function update_category($data)
    {
        require_once dirname(__DIR__) . '/models/main.model.php';
        $model = new MainModel();
        $id = (int)$data['id'];

        if (!$id) {
            json(['result' => false, 'message' => 'Не передан Id']);
            return false;
        }

        json(['result' => $model->update('categories', pre_insert($data), $id)]);
        return true;
    }

    protected function update_user($data)
    {
        require_once dirname(__DIR__) . '/models/main.model.php';
        $model = new MainModel();
        $id = (int)$data['id'];

        if (!$id) {
            json(['result' => false, 'message' => 'Не передан Id']);
            return false;
        }

        if($data['is_banned'])
            $data['banned_date'] = date('Y-m-d h:i:s');

        json(['result' => $model->update('users', $data, $id)]);
        return true;
    }

    protected function remove_category($data)
    {
        require_once dirname(__DIR__) . '/models/main.model.php';
        $model = new MainModel();
        $id = (int)$data['id'];

        if (!$id) {
            json(['result' => false, 'message' => 'Не передан Id']);
            return false;
        }

        json(['result' => $model->delete('categories', $id)]);
        return true;
    }

    protected function remove_user($data)
    {
        require_once dirname(__DIR__) . '/models/main.model.php';
        $model = new MainModel();
        $id = (int)$data['id'];

        if (!$id) {
            json(['result' => false, 'message' => 'Не передан Id']);
            return false;
        }

        json(['result' => $model->delete('users', $id)]);
        return true;
    }

    //Отображение одной публикации
    public function show($query)
    {

        session_start();
        if (!$_SESSION['user']['id']) exit403($query);

        require_once dirname(__DIR__) . '/models/profile.model.php';
        $model = new ProfileModel();
        $manager = $model->getter('users', ['id' => $_SESSION['user']['id']]);

        if (!$manager[0]['is_admin']) exit403($query);

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
        $publication = $PublicationModel->get_publication($publication_id, $alias, 1);

        if (empty($publication))
            exit404($query);


        $publication = pre_show($publication);
        $this->components['title'] = $publication[0]['title'];

        $publication[0]['hashtags'] = array_map(function ($hashtag) {
            return trim($hashtag);
        }, explode(",", trim($publication[0]['hashtags'])));

        $this->components['breadcrumb'] = $this->breadcrumb($publication[0]['category_id'], $publication[0]['title']);

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

        $publication_header = render('manager', 'publication_header', $publication[0]);


        //Форма добавления комментариев
        $comment_form = render('public/show/comments', 'comment-form', $publication[0]);
        //Список коментариев

        $comments = $PublicationModel->get_comments($publication_id);

        $comments = array_map(function ($item) {
            $item['commet_is_liked'] = in_array($item['id'], Main::$liked_comments);
            return $item;
        }, (array)$comments);

        if (!empty($comments))
            $comments = render('public/show/comments', 'comment-item', $this->comment_builder($comments));
        else
            $comments = "<br><p class='lead'>Комментарии пока отсутствуют...</p>";

        $content = render('manager/public', 'publication', [
            'publication_header' => $publication_header,
            'publication_content' => $publication_content,
            'publication_id' => $publication_id,
            'comment_form' => $comment_form,
            'comments' => $comments,
            'moderated' => $publication[0]['moderated']
        ]);

        $this->components['extra-scripts'] = ['manager', 'edit-public'];

        page($content, $this->components);
    }

    //Модерация публикации
    protected function moderate_publication($data)
    {

        require_once dirname(__DIR__) . '/models/publication.model.php';
        $model = new PublicationModel();
        $id = (int)$data['id'];

        if (!$id) {
            json(['result' => false, 'message' => 'Не передан Id']);
            return false;
        }

        json(['result' => $model->update('publications', $data, $id)]);
        return true;


    }


}