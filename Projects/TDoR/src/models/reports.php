<?php
    /**
     * MySQL model implementation classes for reports.
     *
     */


    /**
     * MySQL model implementation for reports.
     *
     */
    class Reports
    {
        /** @var db_credentials             The credentials of the database. */
        public  $db;

        /** @var string                     The name of the table. */
        public  $table_name;

        /** @var string                     Error message. */
        public  $error;



        /**
         * Constructor
         *
         * @param db_credentials $db        The credentials of the database.
         * @param string $table_name         The name of the table. The default is 'reports'.
         */
        public function __construct($db, $table_name = 'reports')
        {
            $this->db         = $db;
            $this->table_name = $table_name;
        }


        /**
         * Create the reports table.
         *
         * @return boolean                  true if OK; false otherwise.
         */
        function create_table()
        {
            $conn = get_connection($this->db);

            $sql = "CREATE TABLE $this->table_name (id INT(6) UNSIGNED AUTO_INCREMENT,
                                                    uid VARCHAR(8),
                                                    deleted BOOL NOT NULL,
                                                    name VARCHAR(255) NOT NULL,
                                                    age VARCHAR(30),
                                                    photo_filename VARCHAR(255),
                                                    photo_source VARCHAR(255),
                                                    date DATE NOT NULL,
                                                    source_ref VARCHAR(255),
                                                    location VARCHAR(255) NOT NULL,
                                                    country VARCHAR(255) NOT NULL,
                                                    country_code VARCHAR(2) NOT NULL,
                                                    latitude DECIMAL(10, 8),
                                                    longitude DECIMAL(11, 8),
                                                    category VARCHAR(64),
                                                    cause VARCHAR(255),
                                                    description TEXT,
                                                    permalink VARCHAR(255),
                                                    tweet VARCHAR(280),
                                                    date_created DATE,
                                                    date_updated DATE,
                                                    PRIMARY KEY (`id`),
                                                    UNIQUE KEY (`uid`) )";

            if ($conn->query($sql) !== FALSE)
            {
                return true;
            }

            $this->error = $conn->error;

            return false;
        }


        /**
         * Determine if there are any (non-deleted) reports in the database.
         *
         * @return boolean                Returns true if there are any reports; false otherwise.
         */
        public function has_reports()
        {
            $conn       = get_connection($this->db);

            $result     = $conn->query("SELECT COUNT(id) FROM $this->table_name WHERE (deleted=0)");

            if ($result)
            {
                $records = $result->fetch();

                return ($records[0] > 0);
            }
            return false;
        }


        /**
         * Get the number of reports in the database between the given dates matching the given filter condition.
         *
         * @param string $date_from_str   The start date as an ISO date.
         * @param string $date_to_str     The end date as an ISO date.
         * @param string $filter          The filter to apply.
         * @return int                    The number of reports.
         */
        public function get_count($date_from_str = '', $date_to_str = '', $filter = '')
        {
            $conn               = get_connection($this->db);

            $not_deleted_sql    = '(deleted=0)';
            $condition_sql      = $not_deleted_sql;

            if (!empty($date_from_str) || !empty($date_to_str) )
            {
                $date_sql       = "(date >= '".date_str_to_iso($date_from_str)."' AND date <= '".date_str_to_iso($date_to_str)."')";
                $condition_sql  = $not_deleted_sql.' AND '.$date_sql;
            }

            if (!empty($filter) )
            {
                $condition_sql  = '('.$condition_sql.' AND '.self::get_filter_condition_sql($filter).')';
            }

            $sql                = "SELECT COUNT(id) FROM $this->table_name WHERE $condition_sql";
            $result             = $conn->query($sql);

            if ($result)
            {
                $records = $result->fetch();

                return $records[0];
            }
            return false;
        }


        /**
         * Get the date range of available reports.
         *
         * @return array                    The start and end date.
         */
        public function get_date_range()
        {
            $retval     = array();

            $conn       = get_connection($this->db);

            $result     = $conn->query("SELECT MIN(date), MAX(date) FROM $this->table_name");

            if ($result)
            {
                $retval = $result->fetch();
            }
            return $retval;
        }


        /**
         * Get the locations of available reports. Used to populate the fields on the Add/Edit Report pages.
         *
         * @return array                    The locations, ordered alphabetically.
         */
        public function get_locations()
        {
            $locations  = array();

            $conn       = get_connection($this->db);

            $result     = $conn->query("SELECT DISTINCT location FROM $this->table_name WHERE (deleted=0) ORDER BY location ASC");

            foreach ($result->fetchAll() as $row)
            {
                $locations[] = stripslashes($row['location']);
            }
            return $locations;
        }


        /**
         * Get the countries of available reports. Used to populate the fields on the Add/Edit Report pages.
         *
         * @return array                  The countries, ordered alphabetically.
         * @param string $date_from_str   The start date as an ISO date.
         * @param string $date_to_str     The end date as an ISO date.
         * @param string $filter          The filter to apply.
         */
        public function get_countries_with_counts($date_from_str = '', $date_to_str = '', $filter = '')
        {
            $countries                  = array();

            $conn                       = get_connection($this->db);

            $condition_sql              = '(deleted=0)';

            if (!empty($date_from_str) && !empty($date_to_str) )
            {
                $date_sql               = "(date >= '".date_str_to_iso($date_from_str)."' AND date <= '".date_str_to_iso($date_to_str)."')";
                $condition_sql         .= " AND $date_sql";
            }

            if (!empty($filter) )
            {
                $condition_sql         .= ' AND '.self::get_filter_condition_sql($filter);
            }

            $sql                        = "SELECT country, count(country) as reports_for_country from $this->table_name WHERE ($condition_sql) GROUP BY country ORDER BY country ASC";

            $result                     = $conn->query($sql);

            foreach ($result->fetchAll() as $row)
            {
                $country                = stripslashes($row['country']);
                $countries[$country]    = $row['reports_for_country'];
            }
            return $countries;
        }


        /**
         * Get the countries of available reports. Used to populate the fields on the Add/Edit Report pages.
         *
         * @param string $date_from_str   The start date as an ISO date.
         * @param string $date_to_str     The end date as an ISO date.
         * @param string $filter          The filter to apply.
         * @return array                  The countries, ordered alphabetically.
         */
        public function get_countries($date_from_str = '', $date_to_str = '', $filter = '')
        {
            $countries          = array();

            $conn               = get_connection($this->db);

            $condition_sql      = '(deleted=0)';

            if (!empty($date_from_str) && !empty($date_to_str) )
            {
                $condition_sql .= " AND (date >= '".date_str_to_iso($date_from_str)."' AND date <= '".date_str_to_iso($date_to_str)."')";
            }

            if (!empty($filter) )
            {
                $condition_sql .= ' AND '.self::get_filter_condition_sql($filter);
            }

            $sql                = "SELECT DISTINCT country FROM $this->table_name WHERE ($condition_sql) ORDER BY country ASC";

            $result             = $conn->query($sql);

            foreach ($result->fetchAll() as $row)
            {
                $countries[]    = stripslashes($row['country']);
            }
            return $countries;
        }


        /**
         * Get the categories of available reports. Used to populate the fields on the Add/Edit Report pages.
         *
         * @return array                    The categories, ordered alphabetically.
         */
        public function get_categories()
        {
            $categories = array();

            $conn       = get_connection($this->db);

            $result     = $conn->query("SELECT DISTINCT category FROM $this->table_name WHERE (deleted=0) ORDER BY category ASC");

            foreach ($result->fetchAll() as $row)
            {
                $categories[] = stripslashes($row['category']);
            }
            return $categories;
        }


        /**
         * Get the causes of death of available reports. Used to populate the fields on the Add/Edit Report pages.
         *
         * @return array                    The causes, ordered alphabetically.
         */
        public function get_causes()
        {
            $causes     = array();

            $conn       = get_connection($this->db);

            $result     = $conn->query("SELECT DISTINCT cause FROM $this->table_name WHERE (deleted=0) ORDER BY cause ASC");

            foreach ($result->fetchAll() as $row)
            {
                $causes[] = stripslashes($row['cause']);
            }
            return $causes;
        }


        /**
         * Get the SQL corresponding to the given filter condition.
         *
         * @param string $filter            The filter condition.
         * @return string                   The SQL  corresponding to the given filter condition.
         */
        private static function get_filter_condition_sql($filter)
        {
            $condition = '';

            $filter = htmlspecialchars($filter, ENT_QUOTES);

            if (!empty($filter) )
            {
                $condition = "CONCAT(name, ' ', age, ' ', location, ' ', country, ' ', country_code, ' ', category, ' ', cause) LIKE '%$filter%'";
            }
            return $condition;
        }


        /**
         * Get all reports corresponding to the given filter condition, with the given sort order.
         *
         * @param string $country           The country.
         * @param string $filter            The filter condition.
         * @param string $sort_column       The sort column.
         * @param boolean $sort_ascending   true to sort reports in ascending order; false otherwise.
         * @return array                    An array containing the corresponding reports.
         */
        public function get_all($country = '', $filter = '', $sort_column ='date', $sort_ascending = true)
        {
            $list               = array();

            $conn               = get_connection($this->db);

            $condition_sql      = 'WHERE (deleted=0)';

            if ( (!empty($country) && $country != 'all') )
            {
                $condition_sql .= " AND (country='$country')";
            }

            if (!empty($filter) )
            {
                $condition_sql .= ' AND '.self::get_filter_condition_sql($filter);
            }

            $sort_column        = self::validate_column_name($sort_column);
            $sort_order         = $sort_ascending ? 'ASC' : 'DESC';

            $sql                = "SELECT * FROM $this->table_name $condition_sql ORDER BY $sort_column $sort_order";
            $result             = $conn->query($sql);

            foreach ($result->fetchAll() as $row)
            {
                $report         = new Report();

                $report->set_from_row($row);

                $list[]         = $report;
            }
            return $list;
        }


        /**
         * Get all reports corresponding to the given filter condition in the specified date range.
         *
         * @param string $date_from_str     The start date.
         * @param string $date_to_str       The finish date.
         * @param string $country           The country.
         * @param string $filter            The filter condition.
         * @param string $sort_column       The sort column.
         * @param boolean $sort_ascending   true to sort reports in ascending order; false otherwise.
         * @return array                    An array containing the corresponding reports.
         */
        public function get_all_in_range($date_from_str, $date_to_str, $country = '', $filter = '', $sort_column ='date', $sort_ascending = true)
        {
            $list               = array();

            $conn               = get_connection($this->db);

            $date_sql           = "(date >= '".date_str_to_iso($date_from_str)."' AND date <= '".date_str_to_iso($date_to_str)."')";
            $condition_sql      = '(deleted=0) AND '.$date_sql;

            $sort_column        = self::validate_column_name($sort_column);
            $sort_order         = $sort_ascending ? 'ASC' : 'DESC';

            if ( (!empty($country) && $country != 'all') )
            {
                $condition_sql .= " AND (country='$country')";
            }

            if (!empty($filter) )
            {
                $condition_sql .= ' AND '.self::get_filter_condition_sql($filter);
            }

            $sql                = "SELECT * FROM $this->table_name WHERE ($condition_sql) ORDER BY $sort_column $sort_order";
            $result             = $conn->query($sql);

            foreach ($result->fetchAll() as $row)
            {
                $report         = new Report();

                $report->set_from_row($row);

                $list[]         = $report;
            }
            return $list;
        }


        /**
         * Get the most recent reports.
         *
         * @param string $count             The number of reports to return.
         * @param string $filter            The filter condition.
         * @return array                    An array containing the corresponding reports.
         */
        public function get_most_recent($count, $filter = '')
        {
            $list   = array();

            try
            {
                $conn               = get_connection($this->db);

                $condition_sql = 'WHERE deleted=0';

                if (!empty($filter) )
                {
                    $condition_sql .= ' AND '.self::get_filter_condition_sql($filter);
                }

                $sql                = "SELECT * FROM $this->table_name $condition_sql ORDER BY date DESC LIMIT $count";
                $result             = $conn->query($sql);

                foreach ($result->fetchAll() as $row)
                {
                    $report         = new Report();
                    $report->set_from_row($row);

                    $list[]         = $report;
                }
            }
            catch (Exception $e)
            {
                echo 'Caught exception: ',  $e->getMessage(), "\n";
            }
            return $list;
        }


        /**
         * Find the report with the given id.
         *
         * @param int $id                   The id of the report.
         * @return Report                   The corresponding report.
         */
        public function find($id)
        {
            $conn               = get_connection($this->db);

            $id                 = intval($id);          // Check that $id is an integer value

            $sql                = "SELECT * FROM $this->table_name WHERE id = $id";

            $result             = $conn->query($sql);

            if ($result)
            {
                $row            = $result->fetch();
                $report         = new Report();

                $report->set_from_row($row);

                return $report;
            }
            else
            {
                echo "<br>".$db->error;
            }
        }


        /**
         * Find the id of the report with the given uid.
         *
         * @param string $uid               The uid of the report.
         * @return int                      The corresponding id.
         */
        public function find_id_from_uid($uid)
        {
            $conn           = get_connection($this->db);

            $sql            = "SELECT id FROM $this->table_name WHERE (uid = '$uid')";

            $result         = $conn->query($sql);

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
            return 0;
        }


        /**
         * Add the given report.
         *
         * @param string $report            The report to add.
         * @return boolean                  true if the report was added successfully; false otherwise.
         */
        public function add($report)
        {
            $date_created       = !empty($report->date_created) ? $report->date_created : date("Y-m-d");
            $date_updated       = !empty($report->date_updated) ? $report->date_updated : $date_created;

            $category           = $report->category;

            if (empty($category) )
            {
                $category       = Report::get_category($report);
            }

            $comma              = ', ';

            $lat_lon_sql = 'NULL, NULL';

            if (!empty($report->latitude) )
            {
                $lat_lon_sql    = $report->latitude.$comma.$report->longitude;
            }

            $conn               = get_connection($this->db);

            $sql                = "INSERT INTO $this->table_name   (uid, deleted, name, age, photo_filename, photo_source, date, source_ref, location, country, country_code, latitude, longitude, category, cause, description, tweet, permalink, date_created, date_updated) VALUES (".
                                                                    $conn->quote($report->uid).$comma.
                                                                    '0,'.
                                                                    $conn->quote($report->name).$comma.
                                                                    $conn->quote($report->age).$comma.
                                                                    $conn->quote($report->photo_filename).$comma.
                                                                    $conn->quote($report->photo_source).$comma.
                                                                    $conn->quote(date_str_to_iso($report->date) ).$comma.
                                                                    $conn->quote($report->source_ref).$comma.
                                                                    $conn->quote($report->location).$comma.
                                                                    $conn->quote($report->country).$comma.
                                                                    $conn->quote($report->country_code).$comma.
                                                                    $lat_lon_sql.$comma.
                                                                    $conn->quote($category).$comma.
                                                                    $conn->quote($report->cause).$comma.
                                                                    $conn->quote($report->description).$comma.
                                                                    $conn->quote($report->tweet).$comma.
                                                                    $conn->quote($report->permalink).$comma.
                                                                    $conn->quote($date_created).$comma.
                                                                    $conn->quote($date_updated).')';
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


        /**
         * Update the given report.
         *
         * @param string $report            The report to update.
         * @return boolean                  true if the report was updated successfully; false otherwise.
         */
        public function update($report)
        {
            $date_created       = !empty($report->date_created) ? $report->date_created : '';
            $date_updated       = !empty($report->date_updated) ? $report->date_updated : date("Y-m-d");

            $comma  = ', ';

            $lat_lon_sql = '';

            $conn               = get_connection($this->db);

            if (!empty($report->latitude) )
            {
               $lat_lon_sql     = 'latitude='.$conn->quote($report->latitude).$comma.
                                  'longitude='.$conn->quote($report->longitude).$comma;
            }

            $sql                = "UPDATE $this->table_name SET ".
                                        'uid='.$conn->quote($report->uid).$comma.
                                        'name='.$conn->quote($report->name).$comma.
                                        'age='.$conn->quote($report->age).$comma.
                                        'photo_filename='.$conn->quote($report->photo_filename).$comma.
                                        'photo_source='.$conn->quote($report->photo_source).$comma.
                                        'date='.$conn->quote($report->date).$comma.
                                        'source_ref='.$conn->quote($report->source_ref).$comma.
                                        'location='.$conn->quote($report->location).$comma.
                                        'country='.$conn->quote($report->country).$comma.
                                        'country_code='.$conn->quote($report->country_code).$comma.
                                        $lat_lon_sql.
                                        'category='.$conn->quote($report->category).$comma.
                                        'cause='.$conn->quote($report->cause).$comma.
                                        'description='.$conn->quote($report->description).$comma.
                                        'tweet='.$conn->quote($report->tweet).$comma.
                                        'permalink='.$conn->quote($report->permalink).$comma.
                                        'date_created='.$conn->quote($report->date_created).$comma.
                                        'date_updated='.$conn->quote($report->date_updated).
                                        ' WHERE id='.$report->id;

            $result = $conn->query($sql);

            if ($result)
            {
                return true;
            }

            echo "<br>".$db->error;

            return false;
        }


        /**
         * Delete the given report.
         *
         * @param string $report            The report to update.
         * @return boolean                  true if the report was updated successfully; false otherwise.
         */
        public function delete($report)
        {
            $conn               = get_connection($this->db);

            $sql                = "UPDATE $this->table_name SET deleted=1 WHERE id=$report->id";

            $result             = $conn->query($sql);

            if ($result)
            {
                return true;
            }

            echo "<br>".$conn->error;

            return false;
        }


        /**
         * Validate the given column name for use in sort operations.
         *
         * @param string $column_name       The name of the column to validate.
         * @return boolean                  true if $column_name is valid; false otherwise.
         */
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
                case 'source_ref':
                case 'location':
                case 'country':
                case 'country_code':
                case 'category':
                case 'cause':
                case 'description':
                case 'permalink':
                case 'tweet':
                    return $column_name;

                default:
            }
            return 'date';
        }

    }


    /**
     * Class representing an individual report.
     *
     */
    class Report
    {
        // These attributes are public so that we can access them using $report->author etc. directly

        /** @var int                     The id of the the report. */
        public  $id;

        /** @var string                  The uid (a hexadecimal number in string form) of the report. */
        public  $uid;

        /** @var boolean                 true if the report has been deleted; false otherwise. */
        public  $deleted;

        /** @var string                  The name of the victim. */
        public  $name;

        /** @var string                  The age of the victim. */
        public  $age;

        /** @var string                  The filename of the victim's photo. */
        public  $photo_filename;

        /** @var string                  The source of the victim's photo. */
        public  $photo_source;

        /** @var string                  The date of death for the victim if known; otherwise the best guess based on available data. */
        public  $date;

        /** @var string                  A reference to the corresponding entry within the list the report appears in (e.g. TGEU or tdor.info) if any. */
        public  $source_ref;

        /** @var string                  The location (city, state etc.). */
        public  $location;

        /** @var string                  The country. */
        public  $country;

        /** @var string                  The country code. */
        public  $country_code;

        /** @var double                  The latitude. */
        public  $latitude;

        /** @var double                  The longitude. */
        public  $longitude;

        /** @var string                  The category. */
        public  $category;

        /** @var string                  The cause of death if known. */
        public  $cause;

        /** @var string                  A textual description of what happened. */
        public  $description;

        /** @var string                  A permalink to the report. */
        public  $permalink;

        /** @var string                  The text of a tweet describing the report. If not specified, default text will be generated. */
        public  $tweet;

        /** @var string                  The date the report was created. */
        public  $date_created;

        /** @var string                  The date the report was last updated. */
        public  $date_updated;


        /**
         * Set the contents of the report from the given database row.
         *
         * @param array $row             An array containing the database row.
         */
        function set_from_row($row)
        {
            $this->id                 = isset($row['id']) ? $row['id'] : 0;

            if (isset( $row['uid']) )
            {
                $this->uid            = $row['uid'];
                $this->deleted        = $row['deleted'];
                $this->name           = stripslashes($row['name']);
                $this->age            = stripslashes($row['age']);
                $this->photo_filename = $row['photo_filename'];
                $this->photo_source   = $row['photo_source'];
                $this->date           = $row['date'];
                $this->source_ref     = stripslashes($row['source_ref']);
                $this->location       = stripslashes($row['location']);
                $this->country        = stripslashes($row['country']);

                if (isset($row['country_code']) )
                {
                    $this->country_code   = stripslashes($row['country_code']);
                }

                if (isset($row['latitude']) )
                {
                    $this->latitude   = $row['latitude'];
                    $this->longitude  = $row['longitude'];
                }

                $this->cause          = stripslashes($row['cause']);
                $this->description    = stripslashes($row['description']);
                $this->permalink      = $row['permalink'];

                if (isset($row['tweet']) )
                {
                    $this->tweet      = stripslashes($row['tweet']);
                }

                $this->date_created   = $row['date_created'];
                $this->date_updated   = $row['date_updated'];

                if (isset($row['category']) )
                {
                    $this->category   = stripslashes($row['category']);
                }
                else
                {
                    $this->category   = self::get_category($this);
                }
            }
        }


        /**
         * Set the contents of the report from the given report.
         *
         * @param Report $report            The report whose data should be copied.
         */
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
            $this->source_ref     = $report->source_ref;
            $this->location       = $report->location;
            $this->country        = $report->country;
            $this->country_code   = $report->country_code;
            $this->latitude       = $report->latitude;
            $this->longitude      = $report->longitude;
            $this->category       = $report->category;
            $this->cause          = $report->cause;
            $this->description    = $report->description;
            $this->tweet          = $report->tweet;
            $this->permalink      = $report->permalink;

            $this->date_created   = $report->date_created;
            $this->date_updated   = $report->date_updated;
        }


        /**
         * Determine whether the report has a location.
         *
         * @return boolean                  true if the report has a location; false otherwise.
         */
        function has_location()
        {
            return !empty($this->location) && ($this->location != '-') && ($this->location != $this->country);
        }


        /**
         * Get the category corresponding to the given report. Note this is of necessity imperfect - ideally this should be a DB field.
         *
         * @param Report $report                      The source report.
         * @return string                             The corresponding category ('violence'/<cause>, 'medical', 'suicide', etc.).
         */
        static function get_category($report)
        {
            $category = '';

            if (stripos($report->cause, 'custody') !== false)
            {
                $category = 'custodial';
            }
            else if (stripos($report->cause, 'suicide') !== false)
            {
                $category = 'suicide';
            }
            else if (stripos($report->cause, 'not reported') !== false)
            {
                $category = 'uncategorised';
            }
            else if ( (stripos($report->cause, 'clinical') !== false) ||
                      (stripos($report->cause, 'cosmetic') !== false) ||
                      (stripos($report->cause, 'silicone') !== false) )
            {
                $category = 'medical';
            }
            else
            {
                $category = 'violence';
            }
            return $category;
        }

    }

?>