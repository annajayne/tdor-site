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
            $conn           = Db::getInstance();

            $date_sql       = "(date >= '".date_str_to_iso($date_from_str)."' AND date <= '".date_str_to_iso($date_to_str)."')";
            $condition_sql  = $date_sql;

            if (!empty($filter) )
            {
                $condition_sql = '('.$date_sql.' AND '.self::get_filter_condition_sql($filter).')';
            }

            $sql        = "SELECT COUNT(id) FROM reports WHERE $condition_sql";
            $result     = $conn->query($sql);

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

            $filter = htmlspecialchars($filter, ENT_QUOTES);

            if (!empty($filter) )
            {
                $condition = "CONCAT(name, ' ', age, ' ', location, ' ', country, ' ', cause) LIKE '%$filter%'";
            }
            return $condition;
        }


        public static function get_all($filter = '', $sort_column ='date', $sort_ascending = true)
        {
            $list       = array();
            $conn       = Db::getInstance();

            $condition_sql = '';

            if (!empty($filter) )
            {
                $condition_sql = 'WHERE '.self::get_filter_condition_sql($filter);
            }

            $sort_column    = self::validate_column_name($sort_column);
            $sort_order     = $sort_ascending ? 'ASC' : 'DESC';

            $sql         = "SELECT * FROM reports $condition_sql ORDER BY $sort_column $sort_order";
            $result      = $conn->query($sql);

            foreach ($result->fetchAll() as $row)
            {
                $report = new Report($row);

                $list[] = $report;
            }
            return $list;
        }


        public static function get_all_in_range($date_from_str, $date_to_str, $filter = '', $sort_column ='date', $sort_ascending = true)
        {
            $list           = array();
            $conn           = Db::getInstance();

            $date_sql       = "(date >= '".date_str_to_iso($date_from_str)."' AND date <= '".date_str_to_iso($date_to_str)."')";
            $condition_sql  = $date_sql;

            $sort_column    = self::validate_column_name($sort_column);
            $sort_order     = $sort_ascending ? 'ASC' : 'DESC';

            if (!empty($filter) )
            {
                $condition_sql = '('.$date_sql.' AND '.self::get_filter_condition_sql($filter).')';
            }

            $sql            = "SELECT * FROM reports WHERE $condition_sql ORDER BY $sort_column $sort_order";
            $result         = $conn->query($sql);

            foreach ($result->fetchAll() as $row)
            {
                $report = new Report($row);

                $list[] = $report;
            }
            return $list;
        }


        public static function get_most_recent($count, $filter = '')
        {
            $list   = array();
            $conn   = Db::getInstance();

            try
            {
                $condition_sql = '';

                if (!empty($filter) )
                {
                    $condition_sql = 'WHERE '.self::get_filter_condition_sql($filter);
                }

                $sql        = "SELECT * FROM reports $condition_sql ORDER BY date DESC LIMIT $count";
                $result     = $conn->query($sql);

                foreach ($result->fetchAll() as $row)
                {
                    $report = new Report($row);

                    $list[] = $report;
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
                $row    = $result->fetch();
                $report = new Report($row);

                return $report;
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
                $row    = $result->fetch();
                $report = new Report($row);

                return $report->id;
            }
            else
            {
                echo "<br>".$db->error;
            }
        }


        private static function validate_column_name($column_name)
        {
            $column_name = htmlspecialchars($column_name, ENT_QUOTES);      // Just in case

            switch ($column_name)
            {
                case 'uid':
                case 'name':
                case 'age':
                case 'photo_filename':
                case 'photo_source':
                case 'date':
                case 'tgeu_ref':
                case 'location':
                case 'country':
                case 'cause':
                case 'description':
                case 'permalink':
                    return $column_name;

                default:
            }
            return 'date';
        }



    }


    class Report
    {
        // These attributes are public so that we can access them using $report->author etc. directly
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


        function __construct($row)
        {
            $this->id                 = $row['id'];

            if (isset( $row['uid']) )
            {
                $this->uid            = $row['uid'];
                $this->name           = $row['name'];
                $this->age            = $row['age'];
                $this->photo_filename = $row['photo_filename'];
                $this->photo_source   = $row['photo_source'];
                $this->date           = $row['date'];
                $this->tgeu_ref       = $row['tgeu_ref'];
                $this->location       = $row['location'];
                $this->country        = $row['country'];
                $this->cause          = $row['cause'];
                $this->description    = $row['description'];
                $this->permalink      = $row['permalink'];
            }
        }

    }

?>