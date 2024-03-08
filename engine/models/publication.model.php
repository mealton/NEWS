<?php


class PublicationModel extends MainModel
{

    private $history = [];

    public function set_notifications($data)
    {
        $sql = "INSERT INTO `notifications` (`subscriber_id`, `note`) VALUES ";
        foreach ($data['subscriber_ids'] as $subscriber_id)
            $sql .= "($subscriber_id, \"" . htmlspecialchars($data['note']) . "\"),";

        $sql = trim($sql, ",");
        return db::getInstance()->QueryInsert($sql);
    }

    public function subscribe($data)
    {
        $subsriber_id = (int)$data['subsriber_id'];
        $user_id = (int)$data['user_id'];
        if (!$subsriber_id || !$user_id)
            return false;
        $sql = <<<SQL
INSERT INTO `subscribers` (`subsriber_id`, `user_id`)
VALUES ($subsriber_id, $user_id)
SQL;
        return db::getInstance()->QueryInsert($sql);
    }

    public function unsubscribe($data)
    {
        $subsriber_id = (int)$data['subsriber_id'];
        $user_id = (int)$data['user_id'];
        if (!$subsriber_id || !$user_id)
            return false;
        $sql = <<<SQL
DELETE FROM `subscribers`
WHERE `subsriber_id` = $subsriber_id AND `user_id` = $user_id
SQL;
        return db::getInstance()->Query($sql);
    }


    public function init()
    {

    }

    public function trash_cleaner()
    {
        $sql = <<<SQL
SELECT `p`.`id`, `c`.`content` as `file`
FROM `publications` as `p`
LEFT JOIN `content` as `c` ON `c`.`publication_id` = `p`.`id` AND `c`.`tag` IN ("image", "video")
WHERE `p`.`is_deleted` = 1
SQL;
        $query = db::getInstance()->Select($sql);
        $publication_ids = array_map(function ($item) {
            $file = str_replace($_SERVER['HTTP_ORIGIN'], $_SERVER['DOCUMENT_ROOT'], $item['file']);
            if (file_exists($file))
                unlink($file);
            return $item['id'];
        }, (array)$query);

        $publication_ids = implode(", ", array_unique($publication_ids));

        $result1 = db::getInstance()->Query("DELETE FROM `publications` WHERE `is_deleted` = 1");
        if ($result1)
            $result2 = db::getInstance()->Query("DELETE FROM `content` WHERE `publication_id` IN($publication_ids)");

        return ($result1 && $result2);

    }

    private function get_filter_string($filter)
    {
        switch ($filter['filter']) {
            case ('tag'):
                $tagFilter = "RIGHT JOIN `hashtags` as `t` ON `p`.`id` = `t`.`publication_id` AND `t`.`name` = \"" . htmlspecialchars($filter['value']) . "\"";
                break;
            case ('category'):
                $categoryFilter = "AND `cat`.`id` = $filter[value]";
                break;
            case ('search'):
                $searchFilter = "AND (`p`.`title` LIKE \"%$filter[value]%\" OR `cnt`.`content` LIKE \"%" . htmlspecialchars($filter['value']) . "%\" OR `cnt`.`description` LIKE \"%" . htmlspecialchars($filter['value']) . "%\")";
                break;
            case ('recent'):
                $recentFilter = " AND DATE(`p`.`published_date`) >= DATE_SUB(CURRENT_DATE, INTERVAL 14 DAY)";
                break;
            case ('author-profile'):
                $authorFilter = "AND `p`.`user_id` = $filter[value]";
                break;
            case ('top'):
                $topFilter = "AND `p`.`likes` >= 2 AND `p`.`views` >= 10 ";
                break;
            case ('liked'):
                $likedFilter = "AND `p`.`id` IN(" . implode(', ', $filter['value']) . ") ";
                break;
            case ('date'):
                $filter['value'] = explode("::", $filter['value']);
                $date_from = current($filter['value']);
                $date_to = end($filter['value']);
                $dateFilter = "AND DATE(`p`.`published_date`) BETWEEN '$date_from' AND '$date_to'";
                break;
        }
        return [
            'tagFilter' => $tagFilter,
            'categoryFilter' => $categoryFilter,
            'searchFilter' => $searchFilter,
            'recentFilter' => $recentFilter,
            'authorFilter' => $authorFilter,
            'topFilter' => $topFilter,
            'dateFilter' => $dateFilter,
            'likedFilter' => $likedFilter,
        ];
    }

    //Выборка публикаций с различными вариациями отбора
    public function get_publications($offset, $filter = [], $slider = false)
    {

        //pre($filter);

        $filter_value = $filter['value'];
        $filter_name = $filter['filter'];

        if ($filter['filter'] == "search") {
            $searchJoinContent = 'LEFT JOIN `content` as `cnt` ON `p`.`id` = `cnt`.`publication_id`';
            $cnt = ' IF(`cnt`.`tag` IN("video", "image"), `cnt`.`description`, `cnt`.`content`) as `search`,';
        } else {
            $searchJoinContent = $cnt = "";
        }

        if (!$filter['manager-zone']) {

            //pre($filter_name);

            //Фильтр на показ эротики
            $erotic_user_filter = ($_SESSION['user']['show_erotic'] && in_array($filter_name, ['', 'author-profile', 'category', 'tag'])) || $filter['user-zone'] ? "" : "HAVING `cat`.`is_hidden` != 1";

            if ($filter['filter'] == "liked") {
                $filter = $this->get_filter_string($filter);
                $limit_sql = " ORDER BY `p`.`published_date` DESC";
            } else {
                //pre($filter_name);
                // $erotic_filter = $_SESSION['user']['show_erotic'] && $filter_name != "top" ? '': ' AND `cat`.`is_hidden` != 1';
                $unpublihed = !$filter['user-zone'] ? 'AND `p`.`is_published` = 1 AND `p`.`is_deleted` = 0 AND `p`.`moderated` = 1' . $erotic_filter : '';
                $filter = $this->get_filter_string($filter);
                $limit = $GLOBALS['config']['publications']['pagination-limit'];
                $limit_sql = $slider ? " ORDER BY RAND() LIMIT 20" : " ORDER BY `p`.`published_date` DESC LIMIT $limit OFFSET $offset";
            }

        } else {
            //$filter['managerZone'] = "AND `p`.`moderated` = 0 ";
            $limit_sql = " ORDER BY `p`.`published_date` DESC";
        }

        $sql = <<<SQL
SELECT 
`p`.*, $cnt
(SELECT COUNT(`id`) FROM `content` WHERE `p`.`id` = `content`.`publication_id` AND `content`.`tag` = 'image' AND `content`.`is_active` = 1 AND `content`.`is_hidden` = 0) as `img_counter`, 
(SELECT COUNT(`id`) FROM `content` WHERE `p`.`id` = `content`.`publication_id` AND `content`.`tag` = 'video' AND `content`.`is_active` = 1) as `video_counter`,
IF(`p`.`image_default` != "", `p`.`image_default`, (SELECT `content` FROM `content` WHERE `publication_id` = `p`.`id` AND `tag` = "image" AND `content` != "" AND `is_active` = 1 ORDER BY RAND() LIMIT 1)) as `public_img`,
`cat`.`name` as `category`,
`cat`.`is_hidden` as `special_content_category`,
`u`.`username` as `author`,
`u`.`profile_image` as `author_image`,
(SELECT COUNT(`id`) FROM `comments` WHERE `publication_id` = `p`.`id` AND `is_active` = 1) as `comment_count`
FROM `publications` as `p`
RIGHT JOIN `categories` as `cat` ON `p`.`category_id` = `cat`.`id` AND `cat`.`is_active` = 1 $filter[categoryFilter]
$searchJoinContent
$filter[tagFilter]
LEFT JOIN `users` as `u` ON `p`.`user_id` = `u`.`id`
WHERE 1 $unpublihed $filter[searchFilter] $filter[recentFilter] $filter[authorFilter] $filter[topFilter] $filter[dateFilter] $filter[managerZone] $filter[likedFilter]
GROUP BY `p`.`id`
$erotic_user_filter
$limit_sql

SQL;

        //pre($sql);

        $publications = db::getInstance()->Select($sql);

        foreach ($publications as $i => $item) {

            if (in_array(0, $this->category_checker($item['category_id'])))
                unset($publications[$i]);

            if ($item['search']) {
                $strposition = mb_strpos(mb_strtolower($item['search']), mb_strtolower($filter_value), 0, 'utf-8');

                if ($strposition === false) {
                    $publications[$i]['search'] = "";
                    continue;
                }

                if ($strposition > 50)
                    $publications[$i]['search'] = "..." . mb_substr($item['search'], $strposition - 15, null, 'utf-8');

                if (mb_strlen($item['search']) > mb_strlen($filter_value) + 50)
                    $publications[$i]['search'] = mb_substr($publications[$i]['search'], 0, 15 + mb_strlen($filter_value) + 15, 'utf-8') . "...";
            }


        }

        //pre($publications);

        return $publications;
    }

    //Вывод истории публикаций
    public function get_history($history)
    {
        $this->history = $history;
        $ids = implode(", ", array_keys($this->history));

        $erotic_user_filter =
            $_SESSION['user']['show_erotic']
                ? ""
                : "AND `cat`.`is_hidden` != 1";

        $sql = <<<SQL
SELECT 
`p`.*,
`cat`.`is_hidden` as `special_content_category`,
IF(`p`.`image_default` != "", 
    `p`.`image_default`, 
    (SELECT `content` FROM `content` WHERE `publication_id` = `p`.`id` AND `tag` = "image" AND `content` != "" AND `is_active` = 1 ORDER BY RAND() LIMIT 1)) as `public_img`
FROM `publications` as `p`
RIGHT JOIN `categories` as `cat` ON `p`.`category_id` = `cat`.`id` AND `cat`.`is_active` = 1 $erotic_user_filter
WHERE `p`.`id` IN ($ids)
ORDER BY FIELD(`p`.`id`, $ids)
SQL;

        $publications = db::getInstance()->Select($sql);
        return array_map(function ($item) {
            $visited = $this->history[$item['id']];
            $item['visited_date'] = date('Y-m-d', $visited);
            $item['time'] = date('H:i', $visited);
            //$item['breadcrumbs'] = $this->get_breadcrumb($item['category_id']);
            return $item;
        }, $publications);
    }

    //Вывод одной публикации
    public function get_publication($id, $alias, $admin_mode = false)
    {
        session_start();
        //Прибавляем просмотры
        if (!$_SESSION['viewed'][$id] && !$admin_mode) {
            if (db::getInstance()->Query("UPDATE `publications` SET `views` = `views` + 1 WHERE `id` = $id"))
                $_SESSION['viewed'][$id] = true;
        }

        $alias_where = !$admin_mode ? 'AND CONCAT(`p`.`alias`, ".html") = "' . $alias . '"' : '';
        $unpublihed = !$admin_mode ? 'AND `p`.`is_published` = 1 AND `p`.`is_deleted` = 0  AND `p`.`moderated` = 1' : '';

        $erotic_user_filter =
            $_SESSION['user']['show_erotic']
                ? ""
                : "AND `cat`.`is_hidden` != 1";


        $sql = <<<SQL
SELECT 
COUNT(`com`.`id`) as `comment_counter`, `p`.*, `c`.*, `p`.`id` as `publication_id`,
(SELECT COUNT(`id`) FROM `content` WHERE `p`.`id` = `content`.`publication_id` AND `content`.`tag` = 'image' AND `content`.`is_active` = 1 AND `content`.`is_hidden` = 0) as `img_counter`, 
(SELECT COUNT(`id`) FROM `content` WHERE `p`.`id` = `content`.`publication_id` AND `content`.`tag` = 'video' AND `content`.`is_active` = 1) as `video_counter`, 
`cat`.`name` as `category`,
`u`.`username` as `author`,
`u`.`profile_image` as `author_image`,
(SELECT GROUP_CONCAT(`name` SEPARATOR ",") as `hashtags` FROM `hashtags` WHERE `publication_id` = $id) as `hashtags`,
(SELECT COUNT(`id`) FROM `comments` WHERE `publication_id` = `p`.`id` AND `is_active` = 1) as `comment_count`
FROM `publications` as `p`
LEFT JOIN `content` as `c` ON `p`.`id` = `c`.`publication_id` AND  `c`.`is_active` = 1
RIGHT JOIN `categories` as `cat` ON `p`.`category_id` = `cat`.`id` AND `cat`.`is_active` = 1 $erotic_user_filter
LEFT JOIN `comments` as `com` ON `c`.`id` = `com`.`content_id`        
LEFT JOIN `users` as `u` ON `p`.`user_id` = `u`.`id`
WHERE 1 $unpublihed AND `p`.`id` = $id $alias_where
GROUP BY `c`.`id`
SQL;

        $publication = db::getInstance()->Select($sql);

        if (empty($publication))
            return false;

        //pre($publication);

        $erotic_user_filter = $_SESSION['user']['show_erotic'] ? "" : "AND `c`.`is_hidden` = 0";

        $sql = <<<SQL
SELECT `t1`.`name`, COUNT(`t2`.`name`) as `count`
FROM `hashtags` as `t1`
LEFT JOIN `hashtags` as `t2` ON `t1`.`name` = `t2`.`name`
RIGHT JOIN `publications` as `p` ON `t2`.`publication_id` = `p`.`id` AND `p`.`moderated` = 1 AND `p`.`is_deleted` = 0 AND `p`.`is_published` = 1
RIGHT JOIN `categories` as `c` ON `p`.`category_id` = `c`.`id` AND `c`.`is_active` = 1 $erotic_user_filter
WHERE `t1`.`publication_id` = $id
GROUP BY `t1`.`name`
SQL;

        $hashtags = db::getInstance()->Select($sql);

        $publication[0]['hashtagsCount'] = $hashtags;

        return $publication;
    }

    //Подсчет количества страниц для вывода пагинации
    public function get_total_count($filter = [])
    {

        $searchJoinContent = $filter['filter'] == "search"
            ? 'LEFT JOIN `content` as `cnt` ON `p`.`id` = `cnt`.`publication_id` AND `cnt`.`tag` = "text"'
            : "";
        $erotic_filter = $filter['filter'] == 'category' ? '' : ' AND `cat`.`is_hidden` != 1';
        $unpublihed = !$filter['user-zone'] ? 'AND `p`.`is_published` = 1 AND `p`.`is_deleted` = 0 AND `p`.`moderated` = 1' . $erotic_filter : '';
        $filter = $this->get_filter_string($filter);

        //pre($filter);

        $sql = <<<SQL
SELECT COUNT(DISTINCT `p`.`id`) as `publication_counter`
FROM `publications` as `p`
RIGHT JOIN `categories` as `cat` ON `p`.`category_id` = `cat`.`id` AND `cat`.`is_active` = 1 $filter[categoryFilter]
$searchJoinContent
$filter[tagFilter]
WHERE 1 $unpublihed $filter[searchFilter] $filter[recentFilter] $filter[topFilter] $filter[authorFilter] $filter[dateFilter] $filter[managerZone]
SQL;

        $query = db::getInstance()->Select($sql);
        $publication_counter = $query[0]['publication_counter'];
        return $publication_counter;
    }

    //Хлебные крошки
    public function get_breadcrumb($category_id, $breadcrumb = [])
    {
        $query = $this->getter('categories', ['id' => $category_id, 'is_active' => 1]);

        if (empty((array)$query))
            return $breadcrumb;

        $query = $query[0];
        $breadcrumb[] = ['id' => $query['id'], 'name' => $query['name']];

        return $this->get_breadcrumb($query['parent_id'], $breadcrumb);
    }

    //Вывод подкатегорий
    public function get_subcategories($id)
    {

        //Фильтр на показ эротики
        $erotic_user_filter = $_SESSION['user']['show_erotic'] ? "" : "HAVING `c`.`is_hidden` != 1";

        $sql = <<<SQL
SELECT `c`.*, COUNT(`p`.`id`) as `public_count`
FROM `categories` as `c`
INNER JOIN `publications` as `p`
    ON `c`.`id` = `p`.`category_id`
    AND `c`.`is_active` = 1 AND `p`.`moderated` = 1 AND `p`.`is_published` = 1 AND `p`.`is_deleted` = 0
WHERE `c`.`parent_id` = $id
GROUP BY `c`.`id`
$erotic_user_filter
ORDER BY `c`.`name`
SQL;
        return db::getInstance()->Select($sql);
    }

    //Удаление контента
    public function remove_old_content($publication_id, $token)
    {
        $sql = <<<SQL
DELETE FROM `content` WHERE `publication_id` = $publication_id AND (`token` IS NULL OR `token` != "$token")
SQL;
        return db::getInstance()->Query($sql);
    }


    public function like($id)
    {
        return db::getInstance()->Query("UPDATE `publications` SET `likes` = `likes` + 1 WHERE `id` = $id");
    }

    public function content_like($id)
    {

        session_start();

        $query = db::getInstance()->Select("SELECT `users_liked` FROM `content` WHERE `id` = $id LIMIT 1");
        $user_liked = unserialize($query[0]['users_liked']);
        $user_liked = is_array($user_liked) ? $user_liked : [];
        $user_id = $_SESSION['user']['id'];
        $is_liked = in_array($user_id, $user_liked);

        if ($is_liked) {
            $user_liked = array_diff($user_liked, [$user_id]);
            $user_liked = serialize($user_liked);

            $sql = <<<SQL
UPDATE `content`
SET `content_likes` = `content_likes` - 1, `users_liked` = '$user_liked'
WHERE `id` = $id
SQL;
        } else {
            $user_liked[] = $user_id;
            $user_liked = serialize($user_liked);
            $sql = <<<SQL
UPDATE `content`
SET `content_likes` = `content_likes` + 1, `users_liked` = '$user_liked'
WHERE `id` = $id
SQL;
        }

        $result = db::getInstance()->Query($sql);
        if ($result) {
            $query = db::getInstance()->Select("SELECT `content_likes` FROM `content` WHERE `id` = $id LIMIT 1");
            return ['result' => $result, 'is_liked' => $is_liked, 'likes' => $query[0]['content_likes']];
        } else
            return ['result' => $result];

    }

    public function dislike($id)
    {
        return db::getInstance()->Query("UPDATE `publications` SET `likes` = `likes` - 1 WHERE `id` = $id");
    }

    public function like_comment($id)
    {
        return db::getInstance()->Query("UPDATE `comments` SET `likes` = `likes` + 1 WHERE `id` = $id");
    }

    public function dislike_comment($id)
    {
        return db::getInstance()->Query("UPDATE `comments` SET `likes` = `likes` - 1 WHERE `id` = $id");
    }

    public function get_comments($publication_id, $comment_id = false)
    {
        $comment_where_id = $comment_id ? " AND `com`.`id` = $comment_id" : "";
        $sql = <<<SQL
SELECT 
`com`.*,
IF(`com`.`content_id` != "", (SELECT `content` FROM `content` WHERE `id` = `com`.`content_id` LIMIT 1), "") as `commented_content`,      
IF((SELECT COUNT(`id`) FROM `comments` as `comm` WHERE `com`.`id` = `comm`.`parent_id` AND `comm`.`publication_id` = `com`.`publication_id` AND `comm`.`is_reply` = 1) > 0, 1, 0) as `has_replies`,      
IF(`c`.`content` = "", "", CONCAT('<img src="', `c`.`content`, '"  onclick="publication.showModal(this)" class="img-fluid comment-img clickable" alt="">')) as `image`, 
`com_`.`user_id` as `reply_user_id`, 
`com_`.`comment` as `reply_comment`,
`com_`.`date` as `reply_date`,
IF(`c_`.`content` = "", "", CONCAT('<img src="', `c_`.`content`, '"  onclick="publication.showModal(this)" class="img-fluid comment-img clickable" alt="">')) as `reply_image`,
`u`.`username` as `username`,
`u`.`profile_image` as `profile_image`,
`u`.`gender` as `gender`,
`u_`.`username` as `reply_username`,
`u_`.`profile_image` as `reply_profile_image`,
`u_`.`gender` as `reply_gender`
FROM `comments` as `com` 
LEFT JOIN `content` as `c` ON `com`.`id` = `c`.`publication_id` AND `c`.`tag` = "comment" AND `c`.`content` != "" 
LEFT JOIN `comments` as `com_` ON `com`.`id` = `com_`.`parent_id` AND `com`.`publication_id` = `com_`.`publication_id` AND `com_`.`is_reply` = 1 
LEFT JOIN `content` as `c_` ON `com_`.`id` = `c_`.`publication_id` AND `c_`.`tag` = "comment" AND `c_`.`content` != "" 

LEFT JOIN `users` as `u` ON `com`.`user_id` = `u`.`id` 
LEFT JOIN `users` as `u_` ON `com_`.`user_id` = `u_`.`id` 

WHERE `com`.`publication_id` = $publication_id $comment_where_id
ORDER BY `com`.`date` DESC, `com_`.`date` DESC
SQL;

        return db::getInstance()->Select($sql);

    }

    public function get_user_comments($user_id = false, $get_total = false)
    {
        $where_user_id = $user_id
            ? "WHERE `com`.`user_id` = $user_id OR `com_`.`parent_id` = `com`.`id`"
            : "WHERE (`com`.`is_complained` = 1 AND `com`.`is_active` = 1) OR (`com_`.`is_complained` = 1 AND `com_`.`is_active` = 1)";
        /* $offset = (int)$_GET['page'];
         $limit = 5;

         $return_limit = $get_total ? "" : " LIMIT $limit OFFSET $offset";*/

        $sql = <<<SQL
SELECT 
`com`.*, 
IF((SELECT COUNT(`id`) FROM `comments` as `comm` WHERE `com`.`id` = `comm`.`parent_id` AND `comm`.`publication_id` = `com`.`publication_id` AND `comm`.`is_reply` = 1) > 0, 1, 0) as `has_replies`,      
IF(`c`.`content` = "", "", CONCAT('<img src="', `c`.`content`, '" class="img-fluid" alt="">')) as `image`, 
`com_`.`user_id` as `reply_user_id`, 
`com_`.`comment` as `reply_comment`,
`com_`.`date` as `reply_date`,
IF(`c_`.`content` = "", "", CONCAT('<img src="', `c_`.`content`, '" class="img-fluid" alt="">')) as `reply_image`,
`u`.`username` as `username`,
`u`.`profile_image` as `profile_image`,
`u`.`gender` as `gender`,
`u_`.`username` as `reply_username`,
`u_`.`profile_image` as `reply_profile_image`,
`u_`.`gender` as `reply_gender`,
`p`.`id` as `publication_id`,
`p`.`alias` as `alias`,
`p`.`title` as `title`,
IF(`p`.`image_default` != "", `p`.`image_default`, (SELECT `content` FROM `content` WHERE `publication_id` = `p`.`id` AND `tag` = "image" AND `content` != "" AND `is_active` = 1 ORDER BY RAND() LIMIT 1)) as `public_img`


FROM `comments` as `com` 
LEFT JOIN `content` as `c` ON `com`.`id` = `c`.`publication_id` AND `c`.`tag` = "comment" AND `c`.`content` != "" 
LEFT JOIN `comments` as `com_` ON `com`.`id` = `com_`.`parent_id` AND `com`.`publication_id` = `com_`.`publication_id` AND `com_`.`is_reply` = 1 
LEFT JOIN `content` as `c_` ON `com_`.`id` = `c_`.`publication_id` AND `c_`.`tag` = "comment" AND `c_`.`content` != "" 

LEFT JOIN `users` as `u` ON `com`.`user_id` = `u`.`id` 
LEFT JOIN `users` as `u_` ON `com_`.`user_id` = `u_`.`id` 

RIGHT JOIN `publications` as `p` ON `com`.`publication_id` = `p`.`id` AND `p`.`is_published` = 1 AND `p`.`is_deleted` = 0

 $where_user_id
HAVING `com`.`id` IS NOT NULL
ORDER BY `com`.`date` DESC, `com_`.`date` DESC
$return_limit
SQL;

        return db::getInstance()->Select($sql);

    }

    private function get_hashtags($id)
    {
        $hashtags = db::getInstance()->Select("SELECT `name` FROM `hashtags` WHERE `publication_id` = $id");
        return !empty((array)$hashtags) ? fetch_to_array($hashtags, 'name') : '';
    }

    public function get_similar($id)
    {

        session_start();

        $hashtags = $this->get_hashtags($id);
        if ($hashtags)
            $hashtags = implode('", "', $hashtags);

        //Фильтр на показ эротики
        $erotic_user_filter = $_SESSION['user']['show_erotic'] ? "" : "AND `cat`.`is_hidden` != 1";

        $sql = <<<SQL
SELECT 
`p`.*,
`cat`.`is_hidden`,
IF(`p`.`image_default` != "", `p`.`image_default`, (SELECT `content` FROM `content` WHERE `publication_id` = `p`.`id` AND `tag` = "image" AND `content` != "" AND `is_active` = 1 ORDER BY RAND() LIMIT 1)) as `public_img`,
(SELECT COUNT(*) FROM `hashtags` WHERE `name` IN ("$hashtags") AND `publication_id` = `p`.`id`) as `tags_counter`,
(SELECT COUNT(`id`) FROM `comments` WHERE `publication_id` = `p`.`id` AND `is_active` = 1) as `comment_count`
FROM `publications` as `p`
RIGHT JOIN `hashtags` as `h` ON  `p`.`id` = `h`.`publication_id` AND `h`.`name` IN ("$hashtags") AND `h`.`publication_id` != $id
RIGHT JOIN `categories` as `cat` ON `p`.`category_id` = `cat`.`id` AND `cat`.`is_active` = 1
WHERE `p`.`id` != $id AND `p`.`is_published` = 1 AND `p`.`is_deleted` = 0 AND `p`.`moderated` = 1
GROUP BY `p`.`id`
HAVING `tags_counter` > 2 $erotic_user_filter
ORDER BY `tags_counter` DESC
LIMIT 20
SQL;
        $query = db::getInstance()->Select($sql);

        if (empty($query)) {
            $sql = <<<SQL
SELECT 
`p`.*,
`cat`.`is_hidden`,
IF(`p`.`image_default` != "", `p`.`image_default`, (SELECT `content` FROM `content` WHERE `publication_id` = `p`.`id` AND `tag` = "image" AND `content` != "" AND `is_active` = 1 ORDER BY RAND() LIMIT 1)) as `public_img`
FROM `publications` as `p`
RIGHT JOIN `publications` as `p_` ON `p`.`category_id` = `p_`.`category_id` AND `p_`.`is_published` = 1 AND `p_`.`is_deleted` = 0 AND `p_`.`id` != `p`.`id`
RIGHT JOIN `categories` as `cat` ON `p`.`category_id` = `cat`.`id` AND `cat`.`is_active` = 1
WHERE `p`.`id` != $id AND `p`.`is_published` = 1 AND `p`.`is_deleted` = 0  AND `p`.`category_id` = (SELECT `category_id` FROM `publications` WHERE `id` = $id LIMIT 1) AND `p`.`moderated` = 1
GROUP BY `p`.`id`
ORDER BY `p`.`published_date` DESC
LIMIT 20
SQL;
            return db::getInstance()->Select($sql);
        }
        return $query;
    }


    public function get_authors($ids = false)
    {
        $filter = !$ids ? "" : "AND `u`.`id` IN ($ids)";
        $sql = <<<SQL
SELECT `u`.*, COUNT(`p`.`id`) as `p_count`
FROM `users` as `u`
RIGHT JOIN `publications` as `p` ON `u`.`id` = `p`.`user_id` AND `p`.`is_published` = 1 AND `p`.`is_deleted` = 0 AND `p`.`moderated` = 1
WHERE `u`.`id` != "" $filter
GROUP BY `u`.`id`
SQL;
        return db::getInstance()->Select($sql);
    }


    public function get_all_hashtags()
    {
        $erotic_filter = $_SESSION['user']['show_erotic']
            ? ""
            : " AND `c`.`is_hidden` != 1";
        $sql = <<<SQL
SELECT `h`.`name`, COUNT(`p`.`id`) as `counter`
FROM `hashtags` as `h`
RIGHT JOIN `publications` as `p` ON `h`.`publication_id` = `p`.`id` 
RIGHT JOIN `categories` as `c` ON `c`.`id` = `p`.`category_id`
WHERE 
      `h`.`name` != "" AND 
      `p`.`is_published` = 1 AND 
      `p`.`is_deleted` = 0 AND 
      `p`.`moderated` = 1 AND 
      `c`.`is_active` = 1 
       $erotic_filter 
GROUP BY `h`.`name`
ORDER BY `h`.`name`
SQL;

        return db::getInstance()->Select($sql);

    }

}