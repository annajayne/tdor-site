<?php
    // MySQL model implementation
    //
    //

    class Reports
    {

        public static function has_reports()
        {
            $db         = Db::getInstance();
            $result     = $db->query('SELECT COUNT(id) FROM reports WHERE (deleted=0)');

            if ($result)
            {
                $records = $result->fetch();

                return ($records[0] > 0);
            }
            return false;
        }


        public static function get_count($date_from_str, $date_to_str, $filter = '')
        {
            $conn           = Db::getInstance();

            $date_sql       = "(date >= '".date_str_to_iso($date_from_str)."' AND date <= '".date_str_to_iso($date_to_str)."')";
            $condition_sql = '(deleted=0) AND '.$date_sql;

            if (!empty($filter) )
            {
                $condition_sql = '('.$date_sql.' AND '.self::get_filter_condition_sql($filter).')';
            }

            $sql        = "SELECT COUNT(id) FROM reports WHERE $condition_sql";
            $result     = $conn->query($sql);

            if ($result)
            {
                $records = $result->fetch();

                return $records[0];
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

            $condition_sql = 'WHERE (deleted=0)';

            if (!empty($filter) )
            {
                $condition_sql .= ' AND '.self::get_filter_condition_sql($filter);
            }

            $sort_column    = self::validate_column_name($sort_column);
            $sort_order     = $sort_ascending ? 'ASC' : 'DESC';

            $sql         = "SELECT * FROM reports $condition_sql ORDER BY $sort_column $sort_order";
            $result      = $conn->query($sql);

            foreach ($result->fetchAll() as $row)
            {
                $report = new Report();

                $report->set_from_row($row);

                $list[] = $report;
            }
            return $list;
        }


        public static function get_all_in_range($date_from_str, $date_to_str, $filter = '', $sort_column ='date', $sort_ascending = true)
        {
            $list           = array();
            $conn           = Db::getInstance();

            $date_sql       = "(date >= '".date_str_to_iso($date_from_str)."' AND date <= '".date_str_to_iso($date_to_str)."')";
            $condition_sql = '(deleted=0) AND '.$date_sql;

            $sort_column    = self::validate_column_name($sort_column);
            $sort_order     = $sort_ascending ? 'ASC' : 'DESC';

            if (!empty($filter) )
            {
                $condition_sql .= ' AND '.self::get_filter_condition_sql($filter);
            }

            $sql            = "SELECT * FROM reports WHERE ($condition_sql) ORDER BY $sort_column $sort_order";
            $result         = $conn->query($sql);

            foreach ($result->fetchAll() as $row)
            {
                $report = new Report();

                $report->set_from_row($row);

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
                $condition_sql = 'WHERE deleted=0';

                if (!empty($filter) )
                {
                    $condition_sql .= ' AND '.self::get_filter_condition_sql($filter);
                }

                $sql        = "SELECT * FROM reports $condition_sql ORDER BY date DESC LIMIT $count";
                $result     = $conn->query($sql);

                foreach ($result->fetchAll() as $row)
                {
                    $report = new Report();
                    $report->set_from_row($row);

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
                $report = new Report();

                $report->set_from_row($row);

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
                $report = new Report();

                $report->set_from_row($row);

                return $report->id;
            }
            else
            {
                echo "<br>".$db->error;
            }
        }


        public static function add($report)
        {
            $conn   = Db::getInstance();

            $comma  = ', ';

            $sql    = 'INSERT INTO reports (uid, deleted, name, age, photo_filename, photo_source, date, tgeu_ref, location, country, cause, description, permalink) VALUES ('.
                $conn->quote($report->uid).$comma.
                '0,'.
                $conn->quote($report->name).$comma.
                $conn->quote($report->age).$comma.
                $conn->quote($report->photo_filename).$comma.
                $conn->quote($report->photo_source).$comma.
                $conn->quote(date_str_to_iso($report->date) ).$comma.
                $conn->quote($report->tgeu_ref).$comma.
                $conn->quote($report->location).$comma.
                $conn->quote($report->country).$comma.
                $conn->quote($report->cause).$comma.
                $conn->quote($report->description).$comma.
                $conn->quote($report->permalink).')';

            $ok = FALSE;

            try
            {
                $ok = $conn->query($sql);
            }
            catch (Exception $e)
            {
                echo "Caught exception: $e->getMessage()\n";
            }

            if ($ok !== FALSE)
            {
                log_text("Record for $report->name added successfully");

                return true;
            }

            log_error("<br>Error adding data: $conn->error");
            log_error("<br>SQL: $sql");

            return false;
        }


        public static function update($report)
        {
            $conn   = Db::getInstance();

            $sql = 'UPDATE reports SET '.
                        'uid='.$conn->quote($report->uid).','.
                        'name='.$conn->quote($report->name).','.
                        'age='.$conn->quote($report->age).','.
                        'photo_filename='.$conn->quote($report->photo_filename).','.
                        'photo_source='.$conn->quote($report->photo_source).','.
                        'date='.$conn->quote($report->date).','.
                        'tgeu_ref='.$conn->quote($report->tgeu_ref).','.
                        'location='.$conn->quote($report->location).','.
                        'country='.$conn->quote($report->country).','.
                        'cause='.$conn->quote($report->cause).','.
                        'description='.$conn->quote($report->description).','.
                        'permalink='.$conn->quote($report->permalink).
                        ' WHERE id='.$report->id;

            $result = $conn->query($sql);

            if ($result)
            {
                return true;
            }

            echo "<br>".$db->error;

            return false;
        }


        public static function delete($report)
        {
            $conn   = Db::getInstance();

            $sql = 'UPDATE reports SET deleted=1 WHERE id='.$report->id;

            $result = $conn->query($sql);

            if ($result)
            {
                return true;
            }

            echo "<br>".$db->error;

            return false;
        }


        private static function validate_column_name($column_name)
        {
            $column_name = htmlspecialchars($column_name, ENT_QUOTES);      // Just in case

            switch ($column_name)
            {
                case 'uid':
                case 'deleted':
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
        public  $deleted;
        public  $name;
        public  $age;
        public  $photo_filename;
        public  $photo_source;
        public  $date;
        public  $tgeu_ref;
        public  $location;
        public  $country;
        public  $cause;
        public  $description;
        public  $permalink;


        function set_from_row($row)
        {
            $this->id                 = $row['id'];

            if (isset( $row['uid']) )
            {
                $this->uid            = $row['uid'];
                $this->deleted        = $row['deleted'];
                $this->name           = stripslashes($row['name']);
                $this->age            = stripslashes($row['age']);
                $this->photo_filename = $row['photo_filename'];
                $this->photo_source   = $row['photo_source'];
                $this->date           = $row['date'];
                $this->tgeu_ref       = stripslashes($row['tgeu_ref']);
                $this->location       = stripslashes($row['location']);
                $this->country        = stripslashes($row['country']);
                $this->cause          = stripslashes($row['cause']);
                $this->description    = stripslashes($row['description']);
                $this->permalink      = $row['permalink'];
            }
        }


        function set_from_report($report)
        {
            $this->id             = $report->id;
            $this->uid            = $report->uid;
            $this->deleted        = $report->deleted;
            $this->name           = $report->name;
            $this->age            = $report->age;
            $this->photo_filename = $report->photo_filename;
            $this->photo_source   = $report->photo_source;
            $this->date           = $report->date;
            $this->tgeu_ref       = $report->tgeu_ref;
            $this->location       = $report->location;
            $this->country        = $report->country;
            $this->cause          = $report->cause;
            $this->description    = $report->description;
            $this->permalink      = $report->permalink;
        }


    }

?>