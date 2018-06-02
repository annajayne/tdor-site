<?php
    // MySQL model implementation
    //
    //

    class Reports
    {
        public static function has_reports()
        {
            $db         = Db::getInstance();
            $result     = $db->query('SELECT COUNT(id) FROM reports');

            if ($result)
            {
                $records = $result->fetch();

                return ($records[0] > 0);
            }
            return false;
        }


        public static function get_count($date_from_str, $date_to_str)
        {
            $db             = Db::getInstance();

            $date_sql       = "(date >= '".date_str_to_iso($date_from_str)."' AND date <= '".date_str_to_iso($date_to_str)."')";

            $condition_sql  = $date_sql;

            if (!empty($filter) )
            {
                $condition_sql = '('.$date_sql.' AND '.self::get_filter_condition_sql($filter).')';
            }

            $sql        = "SELECT COUNT(id) FROM reports WHERE $condition_sql";

            $db         = Db::getInstance();
            $result     = $db->query($sql);

            if ($result)
            {
                $records = $result->fetch();

                return ($records[0] > 0);
            }
            return false;
        }


        public static function get_date_range()
        {
            $retval     = array();

            $db         = Db::getInstance();
            $result     = $db->query('SELECT MIN(date), MAX(date) FROM reports');

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


        public static function get_all($filter = '')
        {
            $list       = array();

            $condition_sql = '';

            if (!empty($filter) )
            {
                $condition_sql = 'WHERE '.self::get_filter_condition_sql($filter);
            }

            $sql        = "SELECT * FROM reports $condition_sql ORDER BY date";

            $db         = Db::getInstance();
            $result     = $db->query($sql);

            foreach ($result->fetchAll() as $row)
            {
                $item   = Report::get_item_from_row($row);

                $list[] = $item;
            }
            return $list;
        }


        public static function get_all_in_range($date_from_str, $date_to_str, $filter = '')
        {
            $list           = array();

            $date_sql       = "(date >= '".date_str_to_iso($date_from_str)."' AND date <= '".date_str_to_iso($date_to_str)."')";

            $condition_sql  = $date_sql;

            if (!empty($filter) )
            {
                $condition_sql = '('.$date_sql.' AND '.self::get_filter_condition_sql($filter).')';
            }

            $sql        = "SELECT * FROM reports WHERE $condition_sql ORDER BY date";

            $db         = Db::getInstance();
            $result     = $db->query($sql);

            foreach ($result->fetchAll() as $row)
            {
                $item   = Report::get_item_from_row($row);

                $list[] = $item;
            }
            return $list;
        }


        public static function get_most_recent($count, $filter = '')
        {
            $list = array();

            try
            {
                $condition_sql = '';

                if (!empty($filter) )
                {
                    $condition_sql = 'WHERE '.self::get_filter_condition_sql($filter);
                }

                $sql        = "SELECT * FROM reports $condition_sql ORDER BY date DESC LIMIT $count";

                $db         = Db::getInstance();
                $result     = $db->query($sql);

                foreach ($result->fetchAll() as $row)
                {
                    $item   = Report::get_item_from_row($row);

                    $list[] = $item;
                }
            }
            catch (Exception $e)
            {
                echo 'Caught exception: ',  $e->getMessage(), "\n";
            }
            return $list;
        }


        public static function find($id)
        {
            // Make sure that $id is an integer value
            $id     = intval($id);

            $sql    = "SELECT * FROM reports WHERE id = $id";

            $db     = Db::getInstance();
            $result = $db->query($sql);

            if ($result)
            {
                $row = $result->fetch();

                $item = Report::get_item_from_row($row);

                return $item;
            }
            else
            {
                echo "<br>".$db->error;
            }
        }


        public static function find_id_from_uid($uid)
        {
            $sql            = "SELECT id FROM reports WHERE (uid = '$uid')";

            $db             = Db::getInstance();
            $result         = $db->query($sql);

            if ($result)
            {
                $row = $result->fetch();

                $item = Report::get_item_from_row($row);

                return $item->id;
            }
            else
            {
                echo "<br>".$db->error;
            }
        }

    }


    class Report
    {
        // These attributes are public so that we can access them using $item->author etc. directly
        public  $id;
        public  $uid;
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
        public  $permalink;


        public static function get_item_from_row($row)
        {
            $item = new Report();

            $item->id                   = $row['id'];

            if (isset( $row['uid']) )
            {
                $item->uid              = $row['uid'];
                $item->name             = $row['name'];
                $item->age              = $row['age'];
                $item->photo_filename   = $row['photo_filename'];
                $item->photo_source     = $row['photo_source'];
                $item->date             = $row['date'];
                $item->tgeu_ref         = $row['tgeu_ref'];
                $item->location         = $row['location'];
                $item->country          = $row['country'];
                $item->cause            = $row['cause'];
                $item->description      = $row['description'];
                $item->permalink        = $row['permalink'];
            }
            return $item;
        }

    }

?>