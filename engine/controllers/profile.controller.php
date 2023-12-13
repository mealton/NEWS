<?php

/**
 * Class Profile
 * Страница пользователя
 */


class Profile extends Main
{
    private $uploader;

    public function __construct($query = [], $async = false)
    {
        $this->uploader = render('components', 'uploader', ['upload_folder' => 'img/profile/', 'multiple' => 0, 'placeholder' => 'Изображение пользователя']);
        parent::__construct($query, $async);
    }

    //action страницы авторизации
    protected function login()
    {
        session_start();
        if ($_SESSION['user']) {
            header('Location: /');
            return true;
        }
        $_SESSION['pre-auth-page'] = $_SERVER['HTTP_REFERER'];
        $this->components['title'] = 'Авторизация';
        $content = render('profile', 'login');
        page($content, $this->components);
        return true;
    }

    //action страницы восстановления пароля
    protected function forgot()
    {
        $this->components['title'] = 'Восстановление учетной записи';
        $this->components['extra-scripts'] = 'profile';
        $content = render('profile', 'forgot-password');
        page($content, $this->components);
    }

    //action страницы указания email'а для письма с ссылкой для смены пароля
    protected function forgot_send_email($data)
    {
        require_once dirname(__DIR__) . '/models/profile.model.php';
        $model = new ProfileModel();
        $user = $model->getter('users', $data);
        if (empty($user)) {
            json(['result' => false]);
            return false;
        }
        $subject = "Восстановление учетной записи";
        $message = render('emails', 'forgot-password', ['user_id' => $user[0]['id'], 'token' => $user[0]['registration_token']]);
        mailer($subject, $message, $user[0]['email']);
        json(['result' => true]);
        return true;

    }

    //action страницы выхода с сайта
    protected function logout()
    {
        session_start();
        session_destroy();
        setcookie('username', 0, time() - 3600 * 24 * 365, '/');
        setcookie('password', 0, time() - 3600 * 24 * 365, '/');
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }

    //авторизация
    protected function auth()
    {
        session_start();
        $username = $_POST['username'];
        $password = md5($_POST['password']);
        require_once dirname(__DIR__) . '/models/profile.model.php';
        $model = new ProfileModel();
        $profile = $model->auth($username, $password);
        if (!empty($profile)) {

            if ($profile['is_banned']) {
                $unbanned_date = strtotime($profile['banned_date']) + $profile['banned_period'];

                if (time() < $unbanned_date) {
                    $_SESSION['auth'] = [
                        'error' => true,
                        'input-login' => $username,
                        'input-password' => $_POST['password'],
                        'remember-user' => isset($_POST['remember-user']),
                        'message' => 'Извините, вы заблокированы до ' . date_rus_format(date('Y-m-d h:i:s', $unbanned_date), ['time' => 1])
                    ];
                    header('location: /profile/login.html');
                    return false;
                }


            }

            if ($_POST['remember-user']) {
                setcookie('username', $username, time() + 3600 * 24 * 365, '/');
                setcookie('password', $password, time() + 3600 * 24 * 365, '/');
            }

            $_SESSION['user'] = $profile;
            $_POST = [];
            header('location: ' . $_SESSION['pre-auth-page']);
            return true;
        } else {
            $_SESSION['auth'] = [
                'error' => true,
                'input-login' => $username,
                'input-password' => $_POST['password'],
                'remember-user' => isset($_POST['remember-user']),
                'message' => 'Извините, пользоваетль не найден...'
            ];
            header('location: /profile/login.html');
            return false;
        }
    }

    //action страницы пользователя
    protected function user($query)
    {
        $id = (int)$query[2];
        if (!$_GET['tab'])
            $_GET['tab'] = 'profile';
        session_start();
        if ($_SESSION['user']['id'] == $id) {
            $this->components['title'] = $_SESSION['user']['fullname'] ? $_SESSION['user']['fullname'] : $_SESSION['user']['username'];
            $this->components['extra-scripts'] = ['profile', 'edit-public'];
            require_once dirname(__DIR__) . '/models/profile.model.php';
            $model = new ProfileModel();
            $user = $model->getter('users', ['id' => $id]);

            $user[0]['categories'] = $model->getter('categories', ['is_active' => 1]);
            $user[0]['uploader'] = $this->uploader;

            require_once dirname(__DIR__) . '/models/publication.model.php';
            $PublicationModel = new PublicationModel();

            $history = $PublicationModel->getter('users', ['id' => $_SESSION['user']['id']], 'history');
            $history = (array)unserialize($history[0]['history']);

            if (current($history)) {
                arsort($history);
                $publications_history = $PublicationModel->get_history($history);
                $history_group = [];

                foreach ($publications_history as $item){
                    $date = $item['visited_date'];
                    if($history_group[$date])
                        $history_group[$date][] = $item;
                    else
                        $history_group[$date] = [$item];
                }

                //pre($history_group);
                $user[0]['publications_history'] = "";

                foreach ($history_group as $date => $item){
                    $user[0]['publications_history'] .= "<h5 style='text-align: center; margin: 30px 0; font-weight: 700'>" . date_rus_format($date) . "</h5>";
                    $user[0]['publications_history'] .= render('profile/user', 'publications_history_item', $item);;
                }

            } else {
                $user[0]['publications_history'] = "";
            }


            if ($_GET['publication_id']) {
                $publication_id = (int)$_GET['publication_id'];
                $publication = $PublicationModel->get_publication($publication_id, '', true);

                if ($publication[0]['source_url']) {
                    $source_host = get_from_url($publication[0]['source_url']);
                    $import_containers = $PublicationModel->getter('import_containers', ['host' => $source_host]);
                }

                $GLOBALS['image_default'] = $publication[0]['image_default'];

                $publication = array_map(function ($item) {
                    $item['item_folder'] = 'edit';
                    $item['editor_hide'] = 1;
                    $item['checkImgDefault'] = end(explode("/", $item['content'])) == end(explode("/", $GLOBALS['image_default']));
                    $item['content'] = $this->convert_content_by_tagname($item);
                    return $item;
                }, $publication);
                //pre($publication);
                //
                $publication_content = render('public', 'switch_tag', $publication);

                $comments = $PublicationModel->get_comments($publication_id);
                $comments = array_map(function ($item) {
                    $item['commet_is_liked'] = in_array($item['id'], Main::$liked_comments);
                    return $item;
                }, (array)$comments);

                if (!empty($comments))
                    $comments = render('public/show/comments', 'comment-item', $this->comment_builder($comments));
                else
                    $comments = "<br><p class='lead'>Комментарии пока отсутствуют...</p>";

                $user[0]['publications'] = render('profile/user', 'edit-public',
                    [
                        'is_published' => $publication[0]['is_published'],
                        'publication_id' => $publication_id,
                        'publication_content' => $publication_content,
                        'categories' => $user[0]['categories'],
                        'public_header' => $publication[0],
                        'import_containers' => $import_containers[0],
                        'image_default' => $publication[0]['image_default'],
                        'comments' => $comments,
                    ]);
            } else {

                //<i class='fa fa-trash-o' aria-hidden='true'></i>
                //Если есть удаленные публикации, добавляем иконку очистки корзины
                $publications = $this->get_publications(['filter' => 'author', 'value' => $id], true);

                if (!empty(array_filter((array)$publications, function ($item) {
                    return $item['is_deleted'] == 1;
                })))
                    $user[0]['trash_cleaner'] = "<i class='fa fa-trash-o pointer' onclick='publication.trash_cleaner(this)'  aria-hidden='true'></i>";

                $user_comments = $PublicationModel->get_user_comments($id);
                if (!empty($user_comments))
                    $user[0]['comments'] = render('profile/user', 'comment-item', $this->comment_builder($user_comments));
                else
                    $user[0]['comments'] = "<p class='lead'>Комментарии осутствут...</p>";

                $user[0]['publications'] = !empty($publications)
                    ? render('public/edit', 'preview', $this->convert_title($publications)) . $this->pagination_constructor(['filter' => 'author', 'value' => $id])
                    : '<p class="lead">Публикации отсутствуют...</p>';
            }

            $content = render('profile/user', 'user-page', $user[0]);
        } else
            exit403($query);
        page($content, $this->components);
    }

    //Конвертирует контент
    private function convert_content_by_tagname($item)
    {
        switch ($item['tag']) {
            case ('image'):
                return "<img src='$item[content]' data-source='$item[source]' alt='' class='publication-image-item img-fluid d-block' />";
                break;
            case ('subtitle'):
                return "<h2>$item[content]</h2>";
                break;
            case ('video'):
                return !strpos($item['content'], "youtube")
                    ? "<video src='$item[content]' controls='controls' class='img-fluid'></video>"
                    : "<iframe style='max-width: 100%' width='640' height='360' src='$item[content]' title='' frameborder='0'
                    allow='accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share'
                    allowfullscreen></iframe>";
                break;
            default:
                return "<p>$item[content]</p>";
        }
    }

    //action страницы регистрации
    protected function registration()
    {
        session_start();
        if ($_SESSION['user']) {
            header('Location: /');
            return true;
        }

        $registration_fields = [
            ['type' => 'text', 'name' => 'username', 'placeholder' => 'Имя пользователя', 'required' => 1],
            ['type' => 'password', 'name' => 'password', 'placeholder' => 'Пароль', 'required' => 1],
            ['type' => 'password', 'name' => 'confirm_password', 'placeholder' => 'Повторите пароль', 'required' => 1],
            ['type' => 'text', 'name' => 'fullname', 'placeholder' => 'Фамилия Имя Отчество', 'required' => 0],
            ['type' => 'email', 'name' => 'email', 'placeholder' => 'Email', 'required' => 1],
        ];

        $this->components['title'] = 'Регистрация нового пользователя';
        $this->components['extra-scripts'] = 'registration';
        $content = render('profile', 'registration', ['uploader' => $this->uploader, 'registration_fields' => $registration_fields]);
        page($content, $this->components);
        return true;
    }

    //проверка нового имени пользвателя и email'а на уникальность
    protected function check_username_is_unique($data)
    {
        require_once dirname(__DIR__) . '/models/profile.model.php';
        $model = new ProfileModel();
        $result = $model->check_existence('users', $data);
        json(['result' => $result]);
    }

    //создание нового пользователя
    protected function create()
    {
        require_once dirname(__DIR__) . '/models/profile.model.php';
        $model = new ProfileModel();
        $data = $_POST;
        $data['registration_token'] = generateRandomString(32);
        $data['password'] = md5($data['password']);
        $user_id = $model->insert('users', $data);
        if ($user_id) {
            $subject = "Регистрация на новостном сайте";
            $message = render('emails', 'registration', ['user_id' => $user_id, 'token' => $data['registration_token']]);
            mailer($subject, $message, $data['email']);
            $this->components['title'] = 'Благодарим за регистрацию';
            $content = render('profile', 'post-registration-page');
            page($content, $this->components);
        }
    }

    //action страницы подтверждения регистрации
    protected function registration_confirm($query)
    {
        $user_id = (int)$query[2];
        $token = (string)$query[3];
        if (!$user_id || !$token)
            exit403($query);

        require_once dirname(__DIR__) . '/models/profile.model.php';
        $model = new ProfileModel();
        $user = $model->getter('users', ['id' => $user_id]);
        if ($token == $user[0]['registration_token']) {
            $model->update('users', ['is_active' => 1], $user_id);
            $_SESSION['user'] = $user[0];
            header('location: /profile/user/' . $user_id . '/profile-page.html');
        } else
            exit403($query);
        return true;
    }

    //action страницы смены пароля при забытии старого
    protected function recovery($query)
    {
        $user_id = (int)$query[2];
        $token = (string)$query[3];
        require_once dirname(__DIR__) . '/models/profile.model.php';
        $model = new ProfileModel();
        $check = $model->check_existence('users', ['id' => $user_id, 'registration_token' => $token]);
        if (!$check)
            exit403($query);
        $registration_fields = [
            ['type' => 'password', 'name' => 'password', 'placeholder' => 'Пароль', 'required' => 1],
            ['type' => 'password', 'name' => 'confirm_password', 'placeholder' => 'Повторите пароль', 'required' => 1],
        ];
        $this->components['title'] = 'Смена пароля';
        $this->components['extra-scripts'] = ['registration', 'profile'];
        $content = render('profile', 'recovery', ['id' => $user_id, 'registration_token' => $token, 'registration_fields' => $registration_fields]);
        page($content, $this->components);

        return true;
    }

    //смена пароля на новый
    protected function recovery_user()
    {
        $id = (int)$_POST['id'];
        $registration_token = (string)$_POST['registration_token'];
        $password = (string)$_POST['password'];

        require_once dirname(__DIR__) . '/models/profile.model.php';
        $model = new ProfileModel();
        $user = $model->getter('users', ['id' => $id, 'registration_token' => $registration_token]);
        if (empty($user))
            exit403();

        $model->update('users', ['password' => md5($password)], $id);
        $_SESSION['user'] = $user[0];
        header('location: /profile/user/' . $id . '/profile-page.html');

        return true;
    }

    //предлагает пользователю готовый надежный сгенерированный пароль
    protected function password_generator()
    {
        json(['password' => generateRandomString()]);
    }

    //Обновление данных пользователя
    protected function update($data)
    {
        session_start();
        $id = (int)$data['id'];
        $registration_token = (string)$data['registration_token'];

        if ($_SESSION['user']['id'] != $id || $_SESSION['user']['registration_token'] != $registration_token) {
            json(['result' => false, 'session' => $_SESSION, 'data' => $data]);
            return false;
        }
        if ($data['password'])
            $data['password'] = md5($data['password']);

        require_once dirname(__DIR__) . '/models/profile.model.php';
        $model = new ProfileModel();
        $result = $model->update('users', $data, $id);
        if ((bool)$result) {
            $user = $model->getter('users', ['id' => $id, 'registration_token' => $registration_token]);
            $_SESSION['user'] = $user[0];
            json(['result' => (bool)$result, 'user' => $user[0], 'data' => $data]);
        }
        return true;
    }

    //Удаление пользоваетля с сайта
    protected function delete($query)
    {
        $id = (int)$query[2];
        $token = (string)$query[3];

        session_start();
        if ($_SESSION['user']['id'] == $id && $_SESSION['user']['registration_token'] == $token) {
            require_once dirname(__DIR__) . '/models/profile.model.php';
            $model = new ProfileModel();
            $result = $model->update('users', ['is_active' => 0], $id);
            if ($result)
                $this->logout();
            else
                exit('Возникла непредвиденная ошибка....');
        } else
            header('Location: /');
    }


    //очистка истории
    protected function clear_history()
    {
        session_start();
        $id = (int)$_SESSION['user']['id'];
        if (!$id) {
            json(['result' => false, 'message' => 'Не получен id пользователя']);
            return false;
        }

        require_once dirname(__DIR__) . '/models/profile.model.php';
        $model = new ProfileModel();
        $result = $model->update('users', ['history' => ''], $id);
        json(['result' => (bool)$result]);
        return true;
    }

}