<?php


class PublicationModel extends MainModel
{

    private $history = [];


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
                $tagFilter = "RIGHT JOIN `hashtags` as `t` ON `p`.`id` = `t`.`publication_id` AND `t`.`name` = \"$filter[value]\"";
                break;
            case ('category'):
                $categoryFilter = "AND `cat`.`id` = \"$filter[value]\"";
                break;
            case ('search'):
                $searchFilter = "AND `p`.`title` LIKE \"%$filter[value]%\"";
                break;
            case ('recent'):
                $recentFilter = "AND DATE(`p`.`published_date`) >= DATE_SUB(CURRENT_DATE, INTERVAL 1 DAY)";
                break;
            case ('author'):
                $authorFilter = "AND `p`.`user_id` = $filter[value]";
                break;
            case ('top'):
                $topFilter = "AND `p`.`likes` >= $filter[value] AND DATE(`p`.`published_date`) >= DATE_SUB(CURRENT_DATE, INTERVAL 1 WEEK)";
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
        ];
    }

    //Выборка публикаций с различными вариациями отбора
    public function get_publications($offset, $filter = [], $slider = false)
    {

        if (!$filter['manager-zone']) {
            $erotic_filter = in_array($filter['filter'], ['date', 'search', 'category', 'tag', 'author']) ? '' : ' AND `cat`.`is_hidden` != 1';
            $unpublihed = !$filter['user-zone'] ? 'AND `p`.`is_published` = 1 AND `p`.`is_deleted` = 0 AND `p`.`moderated` = 1' . $erotic_filter : '';
            $filter = $this->get_filter_string($filter);
            $limit = $GLOBALS['config']['publications']['pagination-limit'];
            $limit_sql = $slider ? " ORDER BY RAND() LIMIT 20" : " ORDER BY `p`.`published_date` DESC LIMIT $limit OFFSET $offset";
        } else {
            $filter['managerZone'] = "AND `p`.`moderated` = 0 ";
            $limit_sql = " ORDER BY `p`.`published_date` DESC";
        }

        //Фильтр на показ эротики
        $erotic_user_filter = $_SESSION['user']['show_erotic'] ? "" : "HAVING `cat`.`is_hidden` != 1";

        $sql = <<<SQL
SELECT 
`p`.*, COUNT(`c1`.`id`) as `img_counter`, 
COUNT(`c2`.`id`) as `video_counter`,
IF(`p`.`image_default` != "", `p`.`image_default`, (SELECT `content` FROM `content` WHERE `publication_id` = `p`.`id` AND `tag` = "image" AND `content` != "" AND `is_active` = 1 ORDER BY RAND() LIMIT 1)) as `public_img`,
`cat`.`name` as `category`,
`cat`.`is_hidden` as `special_content_category`,
`u`.`username` as `author`,
`u`.`profile_image` as `author_image`,
(SELECT COUNT(`id`) FROM `comments` WHERE `publication_id` = `p`.`id` AND `is_active` = 1) as `comment_count`
FROM `publications` as `p`
LEFT JOIN `content` as `c1` ON `p`.`id` = `c1`.`publication_id` AND `c1`.`tag` = 'image' AND `c1`.`is_active` = 1 AND `c1`.`is_hidden` = 0
LEFT JOIN `content` as `c2` ON `p`.`id` = `c2`.`publication_id` AND `c2`.`tag` = 'video' AND `c2`.`is_active` = 1
RIGHT JOIN `categories` as `cat` ON `p`.`category_id` = `cat`.`id` AND `cat`.`is_active` = 1 $filter[categoryFilter]
$filter[tagFilter]
LEFT JOIN `users` as `u` ON `p`.`user_id` = `u`.`id`
WHERE 1 $unpublihed $filter[searchFilter] $filter[recentFilter] $filter[authorFilter] $filter[topFilter] $filter[dateFilter] $filter[managerZone]
GROUP BY `p`.`id`
$erotic_user_filter
$limit_sql

SQL;

        $publications = db::getInstance()->Select($sql);

        foreach ($publications as $i => $item) {
            if (in_array(0, $this->category_checker($item['category_id'])))
                unset($publications[$i]);
        }

        return $publications;
    }

    //Вывод истории публикаций
    public function get_history($history)
    {
        $this->history = $history;
        $ids = implode(", ", array_keys($this->history));
        $sql = <<<SQL
SELECT 
`p`.*,
`cat`.`is_hidden` as `special_content_category`,
IF(`p`.`image_default` != "", 
    `p`.`image_default`, 
    (SELECT `content` FROM `content` WHERE `publication_id` = `p`.`id` AND `tag` = "image" AND `content` != "" AND `is_active` = 1 ORDER BY RAND() LIMIT 1)) as `public_img`
FROM `publications` as `p`
RIGHT JOIN `categories` as `cat` ON `p`.`category_id` = `cat`.`id` AND `cat`.`is_active` = 1
WHERE `p`.`id` IN ($ids)
ORDER BY FIELD(`p`.`id`, $ids)
SQL;

        $publications = db::getInstance()->Select($sql);
        return array_map(function ($item){
            $visited = $this->history[$item['id']];
            $item['visited_date'] = date('Y-m-d', $visited);
            $item['time'] = date('H:i', $visited) ;
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

        $sql = <<<SQL
SELECT 
`p`.*, `c`.*, `p`.`id` as `publication_id`,
`cat`.`name` as `category`,
`u`.`username` as `author`,
`u`.`profile_image` as `author_image`,
(SELECT GROUP_CONCAT(`name` SEPARATOR ",") as `hashtags` FROM `hashtags` WHERE `publication_id` = $id) as `hashtags`,
(SELECT COUNT(`id`) FROM `comments` WHERE `publication_id` = `p`.`id` AND `is_active` = 1) as `comment_count`
FROM `publications` as `p`
LEFT JOIN `content` as `c` ON `p`.`id` = `c`.`publication_id` AND  `c`.`is_active` = 1
RIGHT JOIN `categories` as `cat` ON `p`.`category_id` = `cat`.`id` AND `cat`.`is_active` = 1
LEFT JOIN `users` as `u` ON `p`.`user_id` = `u`.`id`
WHERE 1 $unpublihed AND `p`.`id` = $id $alias_where
SQL;
        return db::getInstance()->Select($sql);
    }

    //Подсчет количества страниц для вывода пагинации
    public function get_total_count($filter = [])
    {
        $erotic_filter = $filter['filter'] == 'category' ? '' : ' AND `cat`.`is_hidden` != 1';
        $unpublihed = !$filter['user-zone'] ? 'AND `p`.`is_published` = 1 AND `p`.`is_deleted` = 0 AND `p`.`moderated` = 1' . $erotic_filter : '';
        $filter = $this->get_filter_string($filter);

        $sql = <<<SQL
SELECT COUNT(`p`.`id`) as `publication_counter`
FROM `publications` as `p`
RIGHT JOIN `categories` as `cat` ON `p`.`category_id` = `cat`.`id` AND `cat`.`is_active` = 1 $filter[categoryFilter]
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
        $where_user_id = $user_id ? "WHERE `com`.`user_id` = $user_id" : "WHERE (`com`.`is_complained` = 1 AND `com`.`is_active` = 1) OR (`com_`.`is_complained` = 1 AND `com_`.`is_active` = 1)";
        /* $offset = (int)$_GET['page'];
         $limit = 5;

         $return_limit = $get_total ? "" : " LIMIT $limit OFFSET $offset";*/

        $sql = <<<SQL
SELECT 
`com`.*, 
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

        $hashtags = $this->get_hashtags($id);
        if ($hashtags)
            $hashtags = implode('", "', $hashtags);

        $sql = <<<SQL
SELECT 
`p`.*,
IF(`p`.`image_default` != "", `p`.`image_default`, (SELECT `content` FROM `content` WHERE `publication_id` = `p`.`id` AND `tag` = "image" AND `content` != "" AND `is_active` = 1 ORDER BY RAND() LIMIT 1)) as `public_img`,
(SELECT COUNT(*) FROM `hashtags` WHERE `name` IN ("$hashtags") AND `publication_id` = `p`.`id`) as `tags_counter`,
(SELECT COUNT(`id`) FROM `comments` WHERE `publication_id` = `p`.`id` AND `is_active` = 1) as `comment_count`
FROM `publications` as `p`
RIGHT JOIN `hashtags` as `h` ON  `p`.`id` = `h`.`publication_id` AND `h`.`name` IN ("$hashtags") AND `h`.`publication_id` != $id
RIGHT JOIN `categories` as `cat` ON `p`.`category_id` = `cat`.`id` AND `cat`.`is_active` = 1
WHERE `p`.`id` != $id AND `p`.`is_published` = 1 AND `p`.`is_deleted` = 0 AND `p`.`moderated` = 1
GROUP BY `p`.`id`
HAVING `tags_counter` > 2
ORDER BY `tags_counter` DESC
LIMIT 20
SQL;
        $query = db::getInstance()->Select($sql);

        if (empty($query)) {
            $sql = <<<SQL
SELECT 
`p`.*,
IF(`p`.`image_default` != "", `p`.`image_default`, (SELECT `content` FROM `content` WHERE `publication_id` = `p`.`id` AND `tag` = "image" AND `content` != "" AND `is_active` = 1 ORDER BY RAND() LIMIT 1)) as `public_img`
FROM `publications` as `p`
RIGHT JOIN `publications` as `p_` ON `p`.`category_id` = `p_`.`category_id` AND `p_`.`is_published` = 1 AND `p_`.`is_deleted` = 0 AND `p_`.`id` != `p`.`id`
WHERE `p`.`id` != $id AND `p`.`is_published` = 1 AND `p`.`is_deleted` = 0  AND `p`.`category_id` = (SELECT `category_id` FROM `publications` WHERE `id` = $id LIMIT 1) AND `p`.`moderated` = 1
GROUP BY `p`.`id`
ORDER BY `p`.`published_date` DESC
LIMIT 20
SQL;
            return db::getInstance()->Select($sql);
        }
        return $query;
    }


    public function get_authors()
    {
        $sql = <<<SQL
SELECT `u`.*, COUNT(`p`.`id`) as `p_count`
FROM `users` as `u`
RIGHT JOIN `publications` as `p` ON `u`.`id` = `p`.`user_id` AND `p`.`is_published` = 1 AND `p`.`is_deleted` = 0 AND `p`.`moderated` = 1
WHERE `u`.`id` != ""
GROUP BY `u`.`id`
SQL;
        return db::getInstance()->Select($sql);
    }

}