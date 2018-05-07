<?php
    // MySQL model implementation
    //
    //
    class Post
    {
        // These attributes are public so that we can access them using $post->author etc. directly
        public  $id;
        public  $name;
        public  $age;
        public  $photo_filename;
        public  $photo_source;
        public  $date;
        public  $year;
        public  $month;
        public  $day;
        public  $tgeu_ref;
        public  $location;
        public  $country;
        public  $cause;
        public  $description;
        public  $description_html;


        private static function get_item_from_row($row)
        {
            $item = new Post();

            $item->id               =  $row['id'];
            $item->name             =  $row['name'];
            $item->age              =  $row['age'];
            $item->photo_filename   =  $row['photo_filename'];
            $item->photo_source     =  $row['photo_source'];
            $item->date             =  $row['date'];
            $item->tgeu_ref         =  $row['tgeu_ref'];
            $item->location         =  $row['location'];
            $item->country          =  $row['country'];
            $item->cause            =  $row['cause'];
            $item->description      =  $row['description'];
            $item->description_html =  $row['description_html'];

            return $item;
        }


        public static function has_posts()
        {
            $db         = Db::getInstance();
            $result     = $db->query('SELECT COUNT(id) FROM incidents');

            if ($result)
            {
                return ($result->fetch()[0] > 0);
            }
            return false;
        }


        public static function get_minmax_dates()
        {
            $retval     = array();

            $db         = Db::getInstance();
            $result     = $db->query('SELECT MIN(date), MAX(date) FROM incidents');

            if ($result)
            {
                 $retval = $result->fetch();
            }
            return $retval;
        }


        private static function get_filter_condition_sql($filter)
        {
            $condition = '';

            if (!empty($filter) )
            {
                $condition = "CONCAT(name, ' ', age, ' ', location, ' ', country, ' ', cause) LIKE '%$filter%'";
            }
            return $condition;
        }


        public static function all($filter = '')
        {
            $list       = array();

            if (!empty($filter) )
            {
                $condition_sql = 'WHERE '.self::get_filter_condition_sql($filter);
            }

            $sql        = "SELECT * FROM incidents $condition_sql ORDER BY date";

            $db         = Db::getInstance();
            $result     = $db->query($sql);

            foreach ($result->fetchAll() as $row)
            {
                $item   = Post::get_item_from_row($row);

                $list[] = $item;
            }
            return $list;
        }


        public static function all_in_range($date_from_str, $date_to_str, $filter = '')
        {
            $list           = array();

            $date_sql       = "(date >= '".date_str_to_utc($date_from_str)."' AND date <= '".date_str_to_utc($date_to_str)."')";

            $condition_sql  = $date_sql;

            if (!empty($filter) )
            {
                $condition_sql = '('.$date_sql.' AND '.self::get_filter_condition_sql($filter).')';
            }

            $sql        = "SELECT * FROM incidents WHERE $condition_sql ORDER BY date";

            $db         = Db::getInstance();
            $result     = $db->query($sql);

            foreach ($result->fetchAll() as $row)
            {
                $item   = Post::get_item_from_row($row);

                $list[] = $item;
            }
            return $list;
        }


        public static function find($id)
        {
            // Make sure that $id is an integer value
            $id     = intval($id);

            $sql    = "SELECT * FROM incidents WHERE id = $id";

            $db     = Db::getInstance();
            $result = $db->query($sql);

            if ($result)
            {
                $row = $result->fetch();

                $item = Post::get_item_from_row($row);

                return $item;
            }
            else
            {
                echo "<br>".$db->error;
            }
        }
    }

?>