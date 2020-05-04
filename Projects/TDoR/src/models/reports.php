<?php
    /**
     * MySQL model implementation classes for reports.
     *
     */


    /**
     * Class to encapsulate report query parameters.
     *
     */
    class ReportsQueryParams
    {
        // These attributes are public so that we can access them using $report->author etc. directly

        /** @var string                  The start date. */
        public  $date_from;

        /** @var string                  The finish date. */
        public  $date_to;

        /** @var string                  Return results from the specified country only. */
        public  $country;

        /** @var string                  Return results matching the specified filter only. */
        public  $filter;

        /** @var string                  The maximum number of results to return. If 0, the number is unlimited. */
        public  $max_results;

        /** @var string                  Sort query results by this field. */
        public  $sort_field;

        /** @var string                  true to sort reports in ascending order; false otherwise. */
        public  $sort_ascending;



        /**
         * Constructor
         *
         */
        public function __construct()
        {
            $this->date_from        = '';
            $this->date_to          = '';
            $this->country          = '';
            $this->filter           = '';
            $this->max_count        = 0;
            $this->sort_field       = 'date';
            $this->sort_ascending   = true;
        }

    }



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
         * @param ReportsQueryParams $query_params  Query parameters.
         * @return int                              The number of reports.
         */
        public function get_count($query_params = null)
        {
            if ($query_params == null)
            {
                $query_params = new ReportsQueryParams();
            }

            $conn               = get_connection($this->db);

            $not_deleted_sql    = '(deleted=0)';
            $condition_sql      = $not_deleted_sql;

            if (!empty($query_params->date_from) || !empty($query_params->date_to) )
            {
                $date_sql       = "(date >= '".date_str_to_iso($query_params->date_from)."' AND date <= '".date_str_to_iso($query_params->date_to)."')";
                $condition_sql  = $not_deleted_sql.' AND '.$date_sql;
            }

            if (!empty($query_params->filter) )
            {
                $condition_sql  = '('.$condition_sql.' AND '.self::get_filter_condition_sql($query_params->filter).')';
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
         * Get the countries of available reports, and the number of reports for each. Used to populate the fields on the Reports page.
         *
         * @param ReportsQueryParams $query_params  Query parameters.
         * @return array                            The report countries, ordered alphabetically.
         */
        public function get_countries_with_counts($query_params = null)
        {
            if ($query_params == null)
            {
                $query_params = new ReportsQueryParams();
            }

            $countries                  = array();

            $conn                       = get_connection($this->db);

            $condition_sql              = '(deleted=0)';

            if (!empty($query_params->date_from) && !empty($query_params->date_to) )
            {
                $date_sql               = "(date >= '".date_str_to_iso($query_params->date_from)."' AND date <= '".date_str_to_iso($query_params->date_to)."')";
                $condition_sql         .= " AND $date_sql";
            }

            if (!empty($query_params->filter) )
            {
                $condition_sql         .= ' AND '.self::get_filter_condition_sql($query_params->filter);
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
         * @param ReportsQueryParams $query_params  Query parameters.
         * @return array                            The report countries, ordered alphabetically.
         */
        public function get_countries($query_params = null)
        {
            if ($query_params == null)
            {
                $query_params = new ReportsQueryParams();
            }

            $countries          = array();

            $conn               = get_connection($this->db);

            $condition_sql      = '(deleted=0)';

            if (!empty($query_params->date_from) && !empty($query_params->date_to) )
            {
                $condition_sql .= " AND (date >= '".date_str_to_iso($query_params->date_from)."' AND date <= '".date_str_to_iso($query_params->date_to)."')";
            }

            if (!empty($query_params->filter) )
            {
                $condition_sql .= ' AND '.self::get_filter_condition_sql($query_params->filter);
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
         * Get all reports corresponding to the given filter condition in the specified date range.
         *
         * @param ReportsQueryParams $query_params  Query parameters.
         * @return array                            An array containing a copy of reports matching the query.
         */
        public function get_all($query_params = null)
        {
            if ($query_params == null)
            {
                $query_params = new ReportsQueryParams();
            }

            $list               = array();

            $conn               = get_connection($this->db);

            $date_sql           = '';
            $condition_sql      = '';

            if (!empty($query_params->date_from) && !empty($query_params->date_to) )
            {
                $date_sql       = " AND (date >= '".date_str_to_iso($query_params->date_from)."' AND date <= '".date_str_to_iso($query_params->date_to)."')";
            }

            $condition_sql      = '(deleted=0) '.$date_sql;

            if ( (!empty($query_params->country) && $query_params->country != 'all') )
            {
                $condition_sql .= " AND (country='$query_params->country')";
            }

            if (!empty($query_params->filter) )
            {
                $condition_sql .= ' AND '.self::get_filter_condition_sql($query_params->filter);
            }

            $sort_column        = self::validate_column_name($query_params->sort_field);
            $sort_order         = $query_params->sort_ascending ? 'ASC' : 'DESC';

            $query_limit_sql    = ($query_params->max_results > 0) ? "LIMIT $query_params->max_results" : '';

            $sql                = "SELECT * FROM $this->table_name WHERE ($condition_sql) ORDER BY $sort_column $sort_order $query_limit_sql";

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
         * Find the report with the given id.
         *
         * @param int $id                   The id of the report.
         * @return Report                   The corresponding report.
         */
        public function find($id)
        {
            $conn	= get_connection($this->db);

            $sql	= "SELECT * FROM $this->table_name WHERE (id = :id)";

            if ($stmt = $conn->prepare($sql) )
            {
                // Bind variables as parameters to the prepared statement
                // and attempt to execute the prepared statement
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);

                if ($stmt->execute() && ($stmt->rowCount() == 1) )
                {
                    if ($row = $stmt->fetch() )
                    {
                        $report = new Report();

                        $report->set_from_row($row);

                        return $report;
                    }
                }
            }
            else
            {
                $this->error = $conn->error;
            }
            return null;
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

            $sql            = "SELECT id FROM $this->table_name WHERE (uid = :uid)";

            if ($stmt = $conn->prepare($sql) )
            {
                // Bind variables as parameters to the prepared statement
                // and attempt to execute the prepared statement
                $stmt->bindParam(':uid', $uid, PDO::PARAM_STR);

                if ($stmt->execute() && ($stmt->rowCount() == 1) )
                {
                    if ($row = $stmt->fetch() )
                    {
                        $report = new Report();

                        $report->set_from_row($row);

                        return $report->id;
                    }
                }
            }
            else
            {
                $this->error	= $conn->error;
            }
        }


        /**
         * Add the given report.
         *
         * @param string $report            The report to add.
         * @return boolean                  true if the report was added successfully; false otherwise.
         */
        /**
         * Add the given report.
         *
         * @param string $report            The report to add.
         * @return boolean                  true if the report was added successfully; false otherwise.
         */
        public function add($report)
        {
            $result             = false;

            $conn               = get_connection($this->db);

            $sql                = "INSERT INTO $this->table_name (uid, deleted, name, age, photo_filename, photo_source, date, source_ref, location, country, country_code, latitude, longitude, category, cause, description, tweet, permalink, date_created, date_updated) VALUES (:uid, :deleted, :name, :age, :photo_filename, :photo_source, :date, :source_ref, :location, :country, :country_code, :latitude, :longitude, :category, :cause, :description, :tweet, :permalink, :date_created, :date_updated)";

            if ($stmt = $conn->prepare($sql) )
            {
                $date_created   = !empty($report->date_created) ? $report->date_created : date("Y-m-d");
                $date_updated   = !empty($report->date_updated) ? $report->date_updated : $date_created;

                $category       = $report->category;

                if (empty($category) )
                {
                    $category   = Report::get_category($report);
                }

                // Bind variables to the prepared statement as parameters
                $stmt->bindParam(':uid',                   		$report->uid,                   PDO::PARAM_STR);
                $stmt->bindParam(':deleted',                   	$report->deleted,               PDO::PARAM_BOOL);
                $stmt->bindParam(':name',                   	$report->name,                  PDO::PARAM_STR);
                $stmt->bindParam(':age',                   		$report->age,                   PDO::PARAM_STR);
                $stmt->bindParam(':photo_filename',             $report->photo_filename,        PDO::PARAM_STR);
                $stmt->bindParam(':photo_source',               $report->photo_source,          PDO::PARAM_STR);
                $stmt->bindParam(':date',                   	date_str_to_iso($report->date), PDO::PARAM_STR);
                $stmt->bindParam(':source_ref',                 $report->source_ref,            PDO::PARAM_STR);
                $stmt->bindParam(':location',                   $report->location,              PDO::PARAM_STR);
                $stmt->bindParam(':country',                   	$report->country,               PDO::PARAM_STR);
                $stmt->bindParam(':country_code',               $report->country_code,          PDO::PARAM_STR);

                if (!empty($report->latitude) && !empty($report->longitude) )
                {
                    $stmt->bindParam(':latitude',               strval($report->latitude),      PDO::PARAM_STR);
                    $stmt->bindParam(':longitude',              strval($report->longitude),     PDO::PARAM_STR);
                }
                else
                {
                    $stmt->bindValue(':latitude',               null,                           PDO::PARAM_NULL);
                    $stmt->bindValue(':longitude',              null,                           PDO::PARAM_NULL);
                }
                $stmt->bindParam(':category',                   $category,                      PDO::PARAM_STR);
                $stmt->bindParam(':cause',                   	$report->cause,                 PDO::PARAM_STR);
                $stmt->bindParam(':description',                $report->description,           PDO::PARAM_STR);
                $stmt->bindParam(':tweet',                   	$report->tweet,                 PDO::PARAM_STR);
                $stmt->bindParam(':permalink',                  $report->permalink,             PDO::PARAM_STR);
                $stmt->bindParam(':date_created',               $date_created,                  PDO::PARAM_STR);
                $stmt->bindParam(':date_updated',               $date_updated,                  PDO::PARAM_STR);

                try
                {
                    // Attempt to execute the prepared statement
                    $result = $stmt->execute();
                }
                catch (Exception $e)
                {
                    $this->error = dump_exception('Reports::add()', $e);
                }
            }

            if ($result !== FALSE)
            {
                log_text("Record for $report->name ($report->date) added successfully");
            }
            return $result;
        }


        /**
         * Update the given report.
         *
         * @param string $report            The report to update.
         * @return boolean                  true if the report was updated successfully; false otherwise.
         */
        public function update($report)
        {
            $result				= false;

            $conn               = get_connection($this->db);

            $sql                = "UPDATE $this->table_name SET uid = :uid, deleted = :deleted, name = :name, age = :age, photo_filename = :photo_filename, photo_source = :photo_source, date = :date, source_ref = :source_ref, location = :location, country = :country, country_code = :country_code, latitude = :latitude, longitude = :longitude, category = :category, cause = :cause, description = :description, tweet = :tweet, permalink = :permalink, date_created = :date_created, date_updated = :date_updated WHERE id= :id";

            if ($stmt = $conn->prepare($sql) )
            {
                $date_created   = !empty($report->date_created) ? $report->date_created : '';
                $date_updated   = !empty($report->date_updated) ? $report->date_updated : date("Y-m-d");

                // Bind variables to the prepared statement as parameters
                $stmt->bindParam(':id',                   		$report->id,                 	PDO::PARAM_INT);
                $stmt->bindParam(':uid',                   		$report->uid,                   PDO::PARAM_STR);
                $stmt->bindParam(':deleted',                   	$report->deleted,               PDO::PARAM_BOOL);
                $stmt->bindParam(':name',                   	$report->name,                  PDO::PARAM_STR);
                $stmt->bindParam(':age',                   		$report->age,                   PDO::PARAM_STR);
                $stmt->bindParam(':photo_filename',             $report->photo_filename,        PDO::PARAM_STR);
                $stmt->bindParam(':photo_source',               $report->photo_source,          PDO::PARAM_STR);
                $stmt->bindValue(':date',                   	date_str_to_iso($report->date), PDO::PARAM_STR);
                $stmt->bindParam(':source_ref',                 $report->source_ref,            PDO::PARAM_STR);
                $stmt->bindParam(':location',                   $report->location,              PDO::PARAM_STR);
                $stmt->bindParam(':country',                   	$report->country,               PDO::PARAM_STR);
                $stmt->bindParam(':country_code',               $report->country_code,          PDO::PARAM_STR);

                if (!empty($report->latitude) && !empty($report->longitude) )
                {
                    $stmt->bindValue(':latitude',               strval($report->latitude),      PDO::PARAM_STR);
                    $stmt->bindValue(':longitude',              strval($report->longitude),     PDO::PARAM_STR);
                }
                else
                {
                    $stmt->bindValue(':latitude',               null,                           PDO::PARAM_NULL);
                    $stmt->bindValue(':longitude',              null,                           PDO::PARAM_NULL);
                }
                $stmt->bindParam(':category',                   $category,                      PDO::PARAM_STR);
                $stmt->bindParam(':cause',                   	$report->cause,                 PDO::PARAM_STR);
                $stmt->bindParam(':description',                $report->description,           PDO::PARAM_STR);
                $stmt->bindParam(':tweet',                   	$report->tweet,                 PDO::PARAM_STR);
                $stmt->bindParam(':permalink',                  $report->permalink,             PDO::PARAM_STR);
                $stmt->bindParam(':date_created',               $date_created,                  PDO::PARAM_STR);
                $stmt->bindParam(':date_updated',               $date_updated,                  PDO::PARAM_STR);

                try
                {
                    // Attempt to execute the prepared statement
                    $result = $stmt->execute();
                }
                catch (Exception $e)
                {
                    $this->error = dump_exception('Reports::update()', $e);
                }
            }

            if ($result !== FALSE)
            {
                log_text("Record for $report->name ($report->date) updated successfully");
            }
            return $result;
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

            $sql				= "UPDATE $this->table_name SET deleted=1 WHERE (id = :id)";

            if ($stmt = $conn->prepare($sql) )
            {
                // Bind variables as parameters to the prepared statement
                // and attempt to execute the prepared statement
                $stmt->bindParam(':id', $report->id, PDO::PARAM_INT);

                $result	        = $stmt->execute();
            }
            else
            {
                $this->error    = $conn->error;
            }

            if ($result)
            {
                return true;
            }
            return false;
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


        /**
         * Dump details of the given PDO exception to the error log (i.e. console), and return its contents as a string.
         *
         * @param string $func_Name         The name of the function.
         * @param PDOException $e           The caught exception.
         * @return string                  	Details of the exception.
         */
        private static function dump_exception($func_Name, $e)
        {
            ob_flush();

            log_error("ERROR: exception caught in func_Name [".$e->getFile().' line '.$e->getLine().']');
            log_error('<br>'.$e->getMessage() );

            log_error('&nbsp;<pre>');

            $stmt->debugDumpParams();

            log_error('</pre>');

            log_error('&nbsp;&nbsp;Call stack: ');

            $trace = $e->getTrace();

            echo '<br><pre>';

            foreach ($trace as $item)
            {
                log_error("    $item[file] ($item[line])");
            }

            log_error("</pre><br>");

            return ob_get_contents();
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
         * Constructor
         *
         */
        public function __construct()
        {
            $this->id             = 0;
            $this->deleted        = false;
        }


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