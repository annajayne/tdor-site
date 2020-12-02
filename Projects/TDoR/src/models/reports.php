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

        /** @var string                  Return results from the specified category only. */
        public  $category;

        /** @var string                  Return results matching the specified filter only. */
        public  $filter;

        /** @var boolean                 Whether draft posts should be included. */
        public  $include_drafts;

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
            $this->include_drafts   = false;
            $this->max_count        = 0;
            $this->sort_field       = 'date';
            $this->sort_ascending   = true;
        }


        /**
         * Bind variables as parameters to the given prepared statement.
         *
         * @param PDO::Statement $stmt       The SQL statement prepared by PDO::prepare().
         */
        public function bind_statement($stmt)
        {
            $sql = $stmt->queryString;

            if (strpos($sql, ':date_from') !== false)
            {
                $stmt->bindValue(':date_from',      date_str_to_iso($this->date_from),  PDO::PARAM_STR);
            }
            if (strpos($sql, ':date_to') !== false)
            {
                $stmt->bindValue(':date_to',        date_str_to_iso($this->date_to),    PDO::PARAM_STR);
            }
            if (strpos($sql, ':country') !== false)
            {
                $stmt->bindParam(':country',        $this->country,                     PDO::PARAM_STR);
            }
            if (strpos($sql, ':category') !== false)
            {
                $stmt->bindParam(':category',       $this->category,                    PDO::PARAM_STR);
            }
            if (strpos($sql, ':filter') !== false)
            {
                $stmt->bindParam(':filter',         $this->filter,                      PDO::PARAM_STR);
            }
            if (strpos($sql, ':max_results') !== false)
            {
                $stmt->bindParam(':max_results',    $this->max_results,                 PDO::PARAM_INT);
            }
        }


        /**
         * Get an SQL condition encapsulating dates given by $date_from and %date_to.
         *
         * @return string                   The SQL  corresponding to the given draft post condition.
         */
        public function get_draft_reports_condition_sql()
        {
            if (!$this->include_drafts)
            {
                return '(draft!=1)';
            }
            return '';
        }


        /**
         * Get an SQL condition encapsulating dates given by $date_from and %date_to.
         *
         * @return string                   The SQL  corresponding to the given date condition.
         */
        public function get_date_range_condition_sql()
        {
            if (!empty($this->date_from) || !empty($this->date_to) )
            {
                return '(date >= :date_from AND date <= :date_to)';
            }
            return '';
        }


        /**
         * Get an SQL condition encapsulating the country specified by $country.
         *
         * @return string                   The SQL  corresponding to the given country condition.
         */
        public function get_country_condition_sql()
        {
            if (!empty($this->country) && ($this->country != 'all') )
            {
                return '(country = :country)';
            }
            return '';
        }


        /**
         * Get an SQL condition encapsulating the category specified by $category.
         *
         * @return string                   The SQL  corresponding to the given category condition.
         */
        public function get_category_condition_sql()
        {
            if (!empty($this->category) && ($this->category != 'all') )
            {
                return '(category = :category)';
            }
            return '';
        }


        /**
         * Get the SQL corresponding to the filter condition specified by $filter.
         *
         * @return string                   The SQL  corresponding to the given filter condition.
         */
        public function get_filter_condition_sql()
        {
            $condition = '';

            if (!empty($this->filter) )
            {
                $condition = "CONCAT(name, ' ', age, ' ', location, ' ', country, ' ', country_code, ' ', category, ' ', cause) LIKE CONCAT(CONCAT('%', :filter), '%')";
            }
            return $condition;
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

            // Update DB table schema if necessary
            if (table_exists($db, $this->table_name) )
            {
                $conn = get_connection($db);

                // If the "draft" column doesn't exist, create it.
                if (!column_exists($db, $this->table_name, 'draft') )
                {
                    $sql = "ALTER TABLE `$this->table_name` ADD `draft` BOOL DEFAULT 0 AFTER uid";

                    if ($conn->query($sql) !== FALSE)
                    {
                        log_text("Draft column added to $this->table_name table");
                    }
                }
            }
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
                                                    draft BOOL NOT NULL,
                                                    deleted BOOL NOT NULL,
                                                    name VARCHAR(255) NOT NULL,
                                                    age VARCHAR(30),
                                                    birthdate DATE,
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
                $query_params           = new ReportsQueryParams();
            }

            $conn                       = get_connection($this->db);

            $not_deleted_sql            = '(deleted=0)';

            $condition_sql              = $not_deleted_sql;

            $include_draft_reports_sql  = $query_params->get_draft_reports_condition_sql();
            $date_range_condition_sql   = $query_params->get_date_range_condition_sql();
            $country_condition_sql      = $query_params->get_country_condition_sql();
            $category_condition_sql     = $query_params->get_category_condition_sql();
            $filter_condition_sql       = $query_params->get_filter_condition_sql();

            if (!empty($include_draft_reports_sql) )
            {
                $condition_sql         .= " AND $include_draft_reports_sql";
            }
            if (!empty($date_range_condition_sql) )
            {
                $condition_sql         .= " AND $date_range_condition_sql";
            }
            if (!empty($country_condition_sql) )
            {
                $condition_sql         .= " AND $country_condition_sql";
            }
            if (!empty($category_condition_sql) )
            {
                $condition_sql         .= " AND $category_condition_sql";
            }
            if (!empty($filter_condition_sql) )
            {
                $condition_sql         .= " AND $filter_condition_sql";
            }

            $sql                        = "SELECT COUNT(id) FROM $this->table_name WHERE $condition_sql";

            if ($stmt = $conn->prepare($sql) )
            {
                // Bind variables as parameters to the prepared statement
                // and attempt to execute the prepared statement
                $query_params->bind_statement($stmt);

                if ($stmt->execute() && ($stmt->rowCount() == 1) )
                {
                    if ($row = $stmt->fetch() )
                    {
                        return $row[0];
                    }
                }
            }
            else
            {
                $this->error = $conn->error;
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
         * Get the years of available reports, and the number of reports for each. Used by the Statistics page.
         *
         * @param ReportsQueryParams $query_params  Query parameters.
         * @return array                            The reports for each year, earliest first.
         */
        public function get_years_with_counts($query_params = null)
        {
            if ($query_params == null)
            {
                $query_params = new ReportsQueryParams();
            }

            $years                      = array();

            $conn                       = get_connection($this->db);

            $condition_sql              = '(deleted=0)';

            $include_draft_reports_sql  = $query_params->get_draft_reports_condition_sql();
            $date_range_condition_sql   = $query_params->get_date_range_condition_sql();
            $category_condition_sql     = $query_params->get_category_condition_sql();
            $filter_condition_sql       = $query_params->get_filter_condition_sql();

            if (!empty($include_draft_reports_sql) )
            {
                $condition_sql         .= " AND $include_draft_reports_sql";
            }
            if (!empty($date_range_condition_sql) )
            {
                $condition_sql         .= " AND $date_range_condition_sql";
            }
            if (!empty($category_condition_sql) )
            {
                $condition_sql         .= " AND $category_condition_sql";
            }
            if (!empty($filter_condition_sql) )
            {
                $condition_sql         .= " AND $filter_condition_sql";
            }

            $sql                        = "SELECT year(date), count(year(date)) as reports_for_year from $this->table_name WHERE ($condition_sql) GROUP BY year(date) ORDER BY year(date) ASC";

            if ($stmt = $conn->prepare($sql) )
            {
                // Bind variables as parameters to the prepared statement
                // and attempt to execute the prepared statement
                $query_params->bind_statement($stmt);

                if ($stmt->execute() )
                {
                    $rows = $stmt->fetchAll();

                    foreach ($rows as $row)
                    {
                        $year               = stripslashes($row[0]);
                        $years[$year]       = intval($row['reports_for_year']);
                    }
                }
            }
            else
            {
                $this->error = $conn->error;
            }
            return $years;
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

            $include_draft_reports_sql  = $query_params->get_draft_reports_condition_sql();
            $date_range_condition_sql   = $query_params->get_date_range_condition_sql();
            $category_condition_sql     = $query_params->get_category_condition_sql();
            $filter_condition_sql       = $query_params->get_filter_condition_sql();

            if (!empty($include_draft_reports_sql) )
            {
                $condition_sql         .= " AND $include_draft_reports_sql";
            }
            if (!empty($date_range_condition_sql) )
            {
                $condition_sql         .= " AND $date_range_condition_sql";
            }
            if (!empty($category_condition_sql) )
            {
                $condition_sql         .= " AND $category_condition_sql";
            }
            if (!empty($filter_condition_sql) )
            {
                $condition_sql         .= " AND $filter_condition_sql";
            }

            $sql                        = "SELECT country, count(country) as reports_for_country from $this->table_name WHERE ($condition_sql) GROUP BY country ORDER BY country ASC";

            if ($stmt = $conn->prepare($sql) )
            {
                // Bind variables as parameters to the prepared statement
                // and attempt to execute the prepared statement
                $query_params->bind_statement($stmt);

                if ($stmt->execute() )
                {
                    $rows = $stmt->fetchAll();

                    foreach ($rows as $row)
                    {
                        $country                = stripslashes($row['country']);
                        $countries[$country]    = intval($row['reports_for_country']);
                    }
                }
            }
            else
            {
                $this->error = $conn->error;
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
                $query_params           = new ReportsQueryParams();
            }

            $countries                  = array();

            $conn                       = get_connection($this->db);

            $condition_sql              = '(deleted=0)';

            $include_draft_reports_sql  = $query_params->get_draft_reports_condition_sql();
            $date_range_condition_sql   = $query_params->get_date_range_condition_sql();
            $category_condition_sql     = $query_params->get_category_condition_sql();
            $filter_condition_sql       = $query_params->get_filter_condition_sql();

            if (!empty($include_draft_reports_sql) )
            {
                $condition_sql         .= " AND $include_draft_reports_sql";
            }
            if (!empty($date_range_condition_sql) )
            {
                $condition_sql         .= " AND $date_range_condition_sql";
            }
            if (!empty($category_condition_sql) )
            {
                $condition_sql         .= " AND $category_condition_sql";
            }
            if (!empty($filter_condition_sql) )
            {
                $condition_sql         .= " AND $filter_condition_sql";
            }

            $sql                        = "SELECT DISTINCT country FROM $this->table_name WHERE ($condition_sql) ORDER BY country ASC";

            if ($stmt = $conn->prepare($sql) )
            {
                // Bind variables as parameters to the prepared statement
                // and attempt to execute the prepared statement
                $query_params->bind_statement($stmt);

                if ($stmt->execute() )
                {
                    $rows = $stmt->fetchAll();

                    foreach ($rows as $row)
                    {
                        $countries[]    = stripslashes($row['country']);
                    }
                }
            }
            else
            {
                $this->error = $conn->error;
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
            $categories         = array();

            $conn               = get_connection($this->db);

            $result             = $conn->query("SELECT DISTINCT category FROM $this->table_name WHERE (deleted=0) ORDER BY category ASC");

            foreach ($result->fetchAll() as $row)
            {
                $categories[]   = stripslashes($row['category']);
            }
            return $categories;
        }


        /**
         * Get the categories of available reports, and the number of reports for each. Used to populate the fields on the Reports page.
         *
         * @param ReportsQueryParams $query_params  Query parameters.
         * @return array                            The report categories, ordered alphabetically.
         */
        public function get_categories_with_counts($query_params = null)
        {
            if ($query_params == null)
            {
                $query_params           = new ReportsQueryParams();
            }

            $categories                 = array();

            $conn                       = get_connection($this->db);

            $condition_sql              = '(deleted=0)';

            $include_draft_reports_sql  = $query_params->get_draft_reports_condition_sql();
            $date_range_condition_sql   = $query_params->get_date_range_condition_sql();
            $country_condition_sql      = $query_params->get_country_condition_sql();
            $filter_condition_sql       = $query_params->get_filter_condition_sql();

            if (!empty($include_draft_reports_sql) )
            {
                $condition_sql         .= " AND $include_draft_reports_sql";
            }
            if (!empty($date_range_condition_sql) )
            {
                $condition_sql         .= " AND $date_range_condition_sql";
            }
            if (!empty($country_condition_sql) )
            {
                $condition_sql         .= " AND $country_condition_sql";
            }
            if (!empty($filter_condition_sql) )
            {
                $condition_sql         .= " AND $filter_condition_sql";
            }

            $sql                        = "SELECT category, count(category) as reports_for_category from $this->table_name WHERE ($condition_sql) GROUP BY category ORDER BY category ASC";

            if ($stmt = $conn->prepare($sql) )
            {
                // Bind variables as parameters to the prepared statement
                // and attempt to execute the prepared statement
                $query_params->bind_statement($stmt);

                if ($stmt->execute() )
                {
                    $rows = $stmt->fetchAll();

                    foreach ($rows as $row)
                    {
                        $category               = stripslashes($row['category']);
                        $categories[$category]  = intval($row['reports_for_category']);
                    }
                }
            }
            else
            {
                $this->error = $conn->error;
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
            $causes         = array();

            $conn           = get_connection($this->db);

            $result         = $conn->query("SELECT DISTINCT cause FROM $this->table_name WHERE (deleted=0) ORDER BY cause ASC");

            foreach ($result->fetchAll() as $row)
            {
                $causes[]   = stripslashes($row['cause']);
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
                $query_params           = new ReportsQueryParams();
            }

            $list                       = array();

            $conn                       = get_connection($this->db);

            $condition_sql              = '(deleted=0)';

            $include_draft_reports_sql  = $query_params->get_draft_reports_condition_sql();
            $date_range_condition_sql   = $query_params->get_date_range_condition_sql();
            $country_condition_sql      = $query_params->get_country_condition_sql();
            $category_condition_sql     = $query_params->get_category_condition_sql();
            $filter_condition_sql       = $query_params->get_filter_condition_sql();

            if (!empty($include_draft_reports_sql) )
            {
                $condition_sql         .= " AND $include_draft_reports_sql";
            }
            if (!empty($date_range_condition_sql) )
            {
                $condition_sql         .= " AND $date_range_condition_sql";
            }
            if (!empty($country_condition_sql) )
            {
                $condition_sql         .= " AND $country_condition_sql";
            }
            if (!empty($category_condition_sql) )
            {
                $condition_sql         .= " AND $category_condition_sql";
            }
            if (!empty($filter_condition_sql) )
            {
                $condition_sql         .= " AND $filter_condition_sql";
            }

            $query_params->sort_field   = self::validate_column_name($query_params->sort_field);
            $sort_order                 = $query_params->sort_ascending ? 'ASC' : 'DESC';

            $query_limit_sql            = ($query_params->max_results > 0) ? 'LIMIT :max_results' : '';

            // Note that we can't use a bound parameter for $sort_field in the statement below as the parameter should not be quoted.
            // However, because the value is validated by validate_column_name() it should be safe against injection attacks.
            $sql                        = "SELECT * FROM $this->table_name WHERE ($condition_sql) ORDER BY $query_params->sort_field $sort_order $query_limit_sql";

            if ($stmt = $conn->prepare($sql) )
            {
                // Bind variables as parameters to the prepared statement
                // and attempt to execute the prepared statement
                $query_params->bind_statement($stmt);

                if ($stmt->execute() )
                {
                    $rows = $stmt->fetchAll();

                    foreach ($rows as $row)
                    {
                        $report         = new Report();

                        $report->set_from_row($row);

                        $list[]         = $report;
                    }
                }
            }
            else
            {
                $this->error = $conn->error;
            }
            return $list;
        }


        /**
         * Get all reports which are present in the current table ($this->table_name), but not in a comparison table (comparison_table_name).
         *
         * @param string $comparison_table_name     The name of the table to compare with.
         * @param ReportsQueryParams $query_params  Query parameters.
         * @return array                            An array containing a copy of reports matching the query.
         */
        public function get_all_missing_from($comparison_table_name, $query_params = null)
        {
            if ($query_params == null)
            {
                $query_params           = new ReportsQueryParams();
            }

            $list                       = array();

            $conn                       = get_connection($this->db);

            $condition_sql              = '(deleted=0)';

            $date_range_condition_sql   = $query_params->get_date_range_condition_sql();
            $country_condition_sql      = $query_params->get_country_condition_sql();
            $category_condition_sql     = $query_params->get_category_condition_sql();
            $filter_condition_sql       = $query_params->get_filter_condition_sql();

            if (!empty($date_range_condition_sql) )
            {
                $condition_sql         .= " AND $date_range_condition_sql";
            }
            if (!empty($country_condition_sql) )
            {
                $condition_sql         .= " AND $country_condition_sql";
            }
            if (!empty($category_condition_sql) )
            {
                $condition_sql         .= " AND $category_condition_sql";
            }
            if (!empty($filter_condition_sql) )
            {
                $condition_sql         .= " AND $filter_condition_sql";
            }

            $query_params->sort_field   = self::validate_column_name($query_params->sort_field);
            $sort_order                 = $query_params->sort_ascending ? 'ASC' : 'DESC';

            $query_limit_sql            = ($query_params->max_results > 0) ? 'LIMIT :max_results' : '';

            // Note that we can't use a bound parameter for $sort_field in the statement below as the parameter should not be quoted.
            // However, because the value is validated by validate_column_name() it should be safe against injection attacks.
            $sql                        = "SELECT * FROM $this->table_name WHERE uid NOT IN (SELECT uid FROM $comparison_table_name) AND ($condition_sql)";

            if ($stmt = $conn->prepare($sql) )
            {
                // Bind variables as parameters to the prepared statement
                // and attempt to execute the prepared statement
                $query_params->bind_statement($stmt);

                if ($stmt->execute() )
                {
                    $rows = $stmt->fetchAll();

                    foreach ($rows as $row)
                    {
                        $report         = new Report();

                        $report->set_from_row($row);

                        $list[]         = $report;
                    }
                }
            }
            else
            {
                $this->error = $conn->error;
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

            $sql                = "INSERT INTO $this->table_name (uid, draft, deleted, name, age, birthdate, photo_filename, photo_source, date, source_ref, location, country, country_code, latitude, longitude, category, cause, description, tweet, permalink, date_created, date_updated) VALUES (:uid, :draft, :deleted, :name, :age, :birthdate, :photo_filename, :photo_source, :date, :source_ref, :location, :country, :country_code, :latitude, :longitude, :category, :cause, :description, :tweet, :permalink, :date_created, :date_updated)";

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
                $stmt->bindParam(':uid',                $report->uid,                           PDO::PARAM_STR);
                $stmt->bindParam(':draft',              $report->draft,                         PDO::PARAM_BOOL);
                $stmt->bindParam(':deleted',            $report->deleted,                       PDO::PARAM_BOOL);
                $stmt->bindParam(':name',               $report->name,                          PDO::PARAM_STR);
                $stmt->bindParam(':age',                $report->age,                           PDO::PARAM_STR);

                if (!empty($report->birthdate) )
                {
                    $stmt->bindValue(':birthdate',      date_str_to_iso($report->birthdate),    PDO::PARAM_STR);
                }
                else
                {
                    $stmt->bindValue(':birthdate',      null,                                   PDO::PARAM_NULL);
                }

                $stmt->bindParam(':photo_filename',     $report->photo_filename,                PDO::PARAM_STR);
                $stmt->bindParam(':photo_source',       $report->photo_source,                  PDO::PARAM_STR);
                $stmt->bindValue(':date',               date_str_to_iso($report->date),         PDO::PARAM_STR);
                $stmt->bindParam(':source_ref',         $report->source_ref,                    PDO::PARAM_STR);
                $stmt->bindParam(':location',           $report->location,                      PDO::PARAM_STR);
                $stmt->bindParam(':country',            $report->country,                       PDO::PARAM_STR);
                $stmt->bindParam(':country_code',       $report->country_code,                  PDO::PARAM_STR);

                if (!empty($report->latitude) && !empty($report->longitude) )
                {
                    $stmt->bindValue(':latitude',       strval($report->latitude),              PDO::PARAM_STR);
                    $stmt->bindValue(':longitude',      strval($report->longitude),             PDO::PARAM_STR);
                }
                else
                {
                    $stmt->bindValue(':latitude',       null,                                   PDO::PARAM_NULL);
                    $stmt->bindValue(':longitude',      null,                                   PDO::PARAM_NULL);
                }
                $stmt->bindParam(':category',           $category,                              PDO::PARAM_STR);
                $stmt->bindParam(':cause',              $report->cause,                         PDO::PARAM_STR);
                $stmt->bindParam(':description',        $report->description,                   PDO::PARAM_STR);
                $stmt->bindParam(':tweet',              $report->tweet,                         PDO::PARAM_STR);
                $stmt->bindParam(':permalink',          $report->permalink,                     PDO::PARAM_STR);
                $stmt->bindParam(':date_created',       $date_created,                          PDO::PARAM_STR);
                $stmt->bindParam(':date_updated',       $date_updated,                          PDO::PARAM_STR);

                try
                {
                    // Attempt to execute the prepared statement
                    $result = $stmt->execute();
                }
                catch (Exception $e)
                {
                    $this->error = $this->dump_exception('Reports::add()', $stmt, $e);
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
            $result             = false;

            $conn               = get_connection($this->db);

            $sql                = "UPDATE $this->table_name SET uid = :uid, draft = :draft, deleted = :deleted, name = :name, age = :age, birthdate = :birthdate, photo_filename = :photo_filename, photo_source = :photo_source, date = :date, source_ref = :source_ref, location = :location, country = :country, country_code = :country_code, latitude = :latitude, longitude = :longitude, category = :category, cause = :cause, description = :description, tweet = :tweet, permalink = :permalink, date_created = :date_created, date_updated = :date_updated WHERE id= :id";

            if ($stmt = $conn->prepare($sql) )
            {
                $date_created   = !empty($report->date_created) ? $report->date_created : '';
                $date_updated   = !empty($report->date_updated) ? $report->date_updated : date("Y-m-d");

                // Bind variables to the prepared statement as parameters
                $stmt->bindParam(':id',                 $report->id,                            PDO::PARAM_INT);
                $stmt->bindParam(':uid',                $report->uid,                           PDO::PARAM_STR);
                $stmt->bindParam(':draft',              $report->draft,                         PDO::PARAM_BOOL);
                $stmt->bindParam(':deleted',            $report->deleted,                       PDO::PARAM_BOOL);
                $stmt->bindParam(':name',               $report->name,                          PDO::PARAM_STR);
                $stmt->bindParam(':age',                $report->age,                           PDO::PARAM_STR);

                if (!empty($report->birthdate) )
                {
                    $stmt->bindValue(':birthdate',      date_str_to_iso($report->birthdate),    PDO::PARAM_STR);
                }
                else
                {
                    $stmt->bindValue(':birthdate',      null,                                   PDO::PARAM_NULL);
                }

                $stmt->bindParam(':photo_filename',     $report->photo_filename,                PDO::PARAM_STR);
                $stmt->bindParam(':photo_source',       $report->photo_source,                  PDO::PARAM_STR);
                $stmt->bindValue(':date',               date_str_to_iso($report->date),         PDO::PARAM_STR);
                $stmt->bindParam(':source_ref',         $report->source_ref,                    PDO::PARAM_STR);
                $stmt->bindParam(':location',           $report->location,                      PDO::PARAM_STR);
                $stmt->bindParam(':country',            $report->country,                       PDO::PARAM_STR);
                $stmt->bindParam(':country_code',       $report->country_code,                  PDO::PARAM_STR);

                if (!empty($report->latitude) && !empty($report->longitude) )
                {
                    $stmt->bindValue(':latitude',       strval($report->latitude),              PDO::PARAM_STR);
                    $stmt->bindValue(':longitude',      strval($report->longitude),             PDO::PARAM_STR);
                }
                else
                {
                    $stmt->bindValue(':latitude',       null,                                   PDO::PARAM_NULL);
                    $stmt->bindValue(':longitude',      null,                                   PDO::PARAM_NULL);
                }

                $stmt->bindParam(':category',           $report->category,                      PDO::PARAM_STR);
                $stmt->bindParam(':cause',              $report->cause,                         PDO::PARAM_STR);
                $stmt->bindParam(':description',        $report->description,                   PDO::PARAM_STR);
                $stmt->bindParam(':tweet',              $report->tweet,                         PDO::PARAM_STR);
                $stmt->bindParam(':permalink',          $report->permalink,                     PDO::PARAM_STR);
                $stmt->bindParam(':date_created',       $date_created,                          PDO::PARAM_STR);
                $stmt->bindParam(':date_updated',       $date_updated,                          PDO::PARAM_STR);

                try
                {
                    // Attempt to execute the prepared statement
                    $result = $stmt->execute();
                }
                catch (Exception $e)
                {
                    $this->error = $this->dump_exception('Reports::update()', $stmt, $e);
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

            $sql                = "UPDATE $this->table_name SET deleted=1 WHERE (id = :id)";

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
                case 'draft':
                case 'deleted':
                case 'name':
                case 'age':
                case 'birthdate':
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
         * @param string $func_name         The name of the function.
         * @param PDOStatement $stmt        The SQL statement.
         * @param PDOException $e           The caught exception.
         * @return string                   Details of the exception.
         */
        private static function dump_exception($func_name, $stmt, $e)
        {
            ob_flush();

            log_error("ERROR: exception caught in $func_name [".$e->getFile().' line '.$e->getLine().']');
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

        /** @var boolean                 true if the report is a draft; false otherwise. */
        public  $draft;

        /** @var boolean                 true if the report has been deleted; false otherwise. */
        public  $deleted;

        /** @var string                  The name of the victim. */
        public  $name;

        /** @var string                  The age of the victim. */
        public  $age;

        /** @var string                  The birthdate of the victim. */
        public  $birthdate;

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

                if (isset($row['draft']) )
                {
                    $this->draft      = $row['draft'];
                }

                $this->deleted        = $row['deleted'];

                $this->name           = stripslashes($row['name']);
                $this->age            = stripslashes($row['age']);

                if (isset($row['birthdate']) )
                {
                    $this->birthdate  = $row['birthdate'];
                }

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
            $this->draft          = $report->draft;
            $this->deleted        = $report->deleted;
            $this->name           = $report->name;
            $this->age            = $report->age;
            $this->birthdate      = $report->birthdate;
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
            $category = 'uncategorised';

            if (stripos($report->cause, 'custody') !== false)
            {
                $category = 'custodial';
            }
            else if (stripos($report->cause, 'suicide') !== false)
            {
                $category = 'suicide';
            }
            else if ( (stripos($report->cause, 'clinical') !== false) ||
                      (stripos($report->cause, 'cosmetic') !== false) ||
                      (stripos($report->cause, 'silicone') !== false) ||
                      (stripos($report->cause, 'covid') !== false) )
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