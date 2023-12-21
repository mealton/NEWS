<?php

class MainModel
{

    public function init()
    {
    }

    public function __construct($data = [])
    {
        $this->Is_user_area = $data['is-user-area'];

        $method = strval($data['method']) ? strval($data['method']) : 'init';
        if (!method_exists($this, $method)) {
            json(array('result' => false, 'message' => 'Запрашиваемый метод отсутствует', 'data' => $data));
            return false;
        } else {
            require_once __DIR__ . '/db.php';
            db::getInstance()->Connect(
                $GLOBALS['config']['mysql']['username'],
                $GLOBALS['config']['mysql']['password'],
                $GLOBALS['config']['mysql']['database'],
                $GLOBALS['config']['mysql']['host']);
            $this->$method($data);
        }
        return true;
    }

    public function get_categories($limit = 0, $no_children = 0)
    {

        //Фильтр на показ эротики
        $erotic_user_filter = $_SESSION['user']['show_erotic'] ? "" : "HAVING `c`.`is_hidden` != 1";

        $no_children = $no_children ? " AND `c`.`parent_id` = 0" : "";
        $sql = <<<SQL
SELECT `c`.*
FROM `categories` as `c`
LEFT JOIN `categories` as `c_`
	ON `c`.`id` = `c_`.`parent_id`
    AND `c`.`is_active` = 1 AND `c_`.`is_active` = 1 
INNER JOIN `publications` as `p`
    ON (`c`.`id` = `p`.`category_id` OR `c_`.`id` = `p`.`category_id`)
    AND `c`.`is_active` = 1 AND `p`.`moderated` = 1 AND `p`.`is_published` = 1 AND `p`.`is_deleted` = 0 $no_children
GROUP BY `c`.`id`
$erotic_user_filter
ORDER BY `c`.`name`
SQL;

        if ((int)$limit > 0)
            $sql .= " LIMIT " . $limit;

        $categories = db::getInstance()->Select($sql);

        foreach ($categories as $i => $item) {
            if (in_array(0, $this->category_checker($item['id'])))
                unset($categories[$i]);
        }

        return $categories;

    }

    public function get_all_hashtags()
    {
        $sql = <<<SQL
SELECT *
FROM `hashtags` 
WHERE `publication_id` IN (
    SELECT DISTINCT `id`
        FROM `publications` 
        WHERE `moderated` = 1 AND `is_published` = 1  AND `is_deleted` = 0 )
ORDER BY `name`
SQL;
        return db::getInstance()->Select($sql);

    }

    public function get_users()
    {
        $sql = <<<SQL
SELECT *
FROM `users`
ORDER BY `username`
SQL;
        return db::getInstance()->Select($sql);
    }

    public function insert_test($table, $data)
    {
        $fields = $this->get_table_fields($table);
        foreach ($data as $field => $value) {
            if (!in_array($field, $fields))
                unset($data[$field]);
        }
        $fields = implode("`, `", array_keys($data));
        $values = implode('", "', array_keys($data));
        $sql = <<<SQL
INSERT INTO `$table`
(`$fields`) VALUES ("$values")
SQL;
        return db::getInstance()->QueryInsert($sql);

    }

    public function check_existence($tablename, $data)
    {
        if (!is_array($data) || empty($data))
            return false;

        $columns = $this->get_table_fields($tablename);

        $sql = 'SELECT * FROM `' . $tablename . '` WHERE ';

        $where = '';

        foreach ($data as $field => $value) {
            if (in_array($field, $columns))
                $where .= ' `' . $field . '` = "' . str_replace('"', '\"', $value) . '" AND';
        }
        $sql .= trim($where, 'AND') . ' LIMIT 1';
        $fetch = db::getInstance()->Select($sql);
        return !empty($fetch);
    }

    public function get_table_fields($table_name)
    {
        $columns = db::getInstance()->Select('SHOW COLUMNS FROM `' . $table_name . '`');
        return fetch_to_array($columns, 'Field');
    }

    public function insert($table_name, $data, $multiple_insert = false)
    {
        if (!$table_name || !is_array($data))
            return false;

        $columns = $this->get_table_fields($table_name);
        $fields = $values = '';
        if (!$multiple_insert) {
            $values .= '(';
            $data = is_array($data[0]) ? $data[0] : $data;
            foreach ($data as $field => $value) {
                if (in_array($field, $columns)) {
                    $fields .= '`' . $field . '`,';
                    $values .= '"' . str_replace('"', '\"', $value) . '",';
                }
            }
            $values = trim($values, ',') . '),';
        } else {
            foreach ($data as $i => $row) {
                if (!is_array($row))
                    continue;
                $values .= '(';
                foreach ($row as $field => $value) {
                    if (in_array($field, $columns)) {
                        if ($i == 0)
                            $fields .= '`' . $field . '`,';
                        $values .= '"' . $value . '",';
                    }
                }
                $values = trim($values, ',') . '),';
            }
        }
        $sql = 'INSERT INTO `' . $table_name . '` (' . trim($fields, ',') . ') VALUES ' . trim($values, ',');
        return db::getInstance()->QueryInsert($sql);
    }

    public function update($table_name, $data, $identifier_value, $identifier = 'id')
    {
        if (!$table_name || !is_array($data) || empty($data) || !$identifier_value)
            return false;

        $columns = $this->get_table_fields($table_name);

        if (!in_array($identifier, $columns))
            return false;

        $sql = 'UPDATE `' . $table_name . '` SET ';

        foreach ($data as $field => $value) {
            if (in_array($field, $columns))
                $sql .= '`' . $field . '` = "' . str_replace('"', '\"', $value) . '",';
        }

        $sql = trim($sql, ',');

        $sql .= ' WHERE `' . $identifier . '` = "' . $identifier_value . '"';

        db::getInstance()->Query($sql);

        return $this->getter($table_name, [$identifier => $identifier_value]);
    }

    public function delete($table_name, $identifier_value, $identifier = 'id')
    {
        if (!$table_name || !$identifier_value)
            return false;

        $columns = $this->get_table_fields($table_name);

        if (!in_array($identifier, $columns))
            return false;

        $sql = 'DELETE FROM `' . $table_name . '` WHERE `' . $identifier . '` = "' . $identifier_value . '"';
        return db::getInstance()->Query($sql);
    }

    public function getter($tablename, $data = array(), $fields = '*', $order_by = ['order' => false, 'dir' => 'ASC'])
    {
        if (!$tablename || !is_array($data))
            return false;

        fields:
        if (is_array($fields))
            $fields = '`' . implode('`, `', $fields) . '`';
        elseif (strval($fields) && $fields != '*') {
            $fields = explode(',', $fields);
            goto fields;
        }

        $columns = $this->get_table_fields($tablename);

        $where = '';

        if (!empty($data)) {
            $where .= ' WHERE ';
            foreach ($data as $field => $value) {
                if (in_array($field, $columns))
                    $where .= ' `' . $field . '` = "' . $value . '" AND';
            }
        }
        $sql = 'SELECT ' . $fields . ' FROM `' . $tablename . '`' . trim($where, 'AND');

        if ($order_by['order'])
            $sql .= " ORDER BY `$order_by[order]` $order_by[dir]";

        return db::getInstance()->Select($sql);
    }

    public function get_public_published_date_start()
    {
        $sql = <<<SQL
SELECT DATE(`published_date`) as `start` FROM `publications`
WHERE `is_deleted` = 0 AND `is_published` = 1 AND `moderated` = 1
ORDER BY `published_date` LIMIT 1
SQL;
        $query = db::getInstance()->Select($sql);
        return $query[0]['start'];
    }

    public function get_sidebar_publics()
    {
        $sql = <<<SQL
SELECT 
`p`.*, 
IF(
    `p`.`image_default` != "", 
    `p`.`image_default`, 
    (SELECT `content` FROM `content` WHERE `publication_id` = `p`.`id` AND `tag` = "image" AND `content` != "" AND `is_active` = 1 ORDER BY RAND() LIMIT 1)
    ) as `public_img`,
(SELECT COUNT(`id`) FROM `comments` WHERE `publication_id` = `p`.`id` AND `is_active` = 1) as `comment_count`
FROM `publications` as `p`
INNER JOIN `categories` as `cat` ON `p`.`category_id` = `cat`.`id` AND `cat`.`is_active` = 1 AND `cat`.`is_hidden` = 0
WHERE `p`.`is_deleted` != 1 AND `p`.`is_published` = 1 AND `p`.`moderated` = 1 AND `cat`.`is_active` = 1
    AND DATE(`p`.`published_date`) >= DATE_SUB(CURRENT_DATE, INTERVAL 3 DAY)
GROUP BY `p`.`published_date`
ORDER BY `p`.`views` DESC, `p`.`likes` DESC, `p`.`published_date` DESC
LIMIT 3
SQL;
        return db::getInstance()->Select($sql);
    }

    public function category_checker($category_id, $breadcrumb = [])
    {
        $query = $this->getter('categories', ['id' => $category_id]);

        if (empty((array)$query))
            return $breadcrumb;

        $query = $query[0];
        $breadcrumb[] = $query['is_active'];

        return $this->category_checker($query['parent_id'], $breadcrumb);
    }


}
