<?php
    /**
     * MySQL model implementation classes for the Contributors table.
     *
     */

    require_once('models/reports.php');


    /**
     * MySQL model implementation class for the Contributors table.
     *
     */
    class Contributors
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
         * @param array $table_name         The name of the table. The default is 'contributors'.
         */
        public function __construct($db, $table_name = 'contributors')
        {
            $this->db         = $db;
            $this->table_name = $table_name;

            // Create the table if necessary
            if (!table_exists($db, $this->table_name))
            {
                $this->create_table();
            }
       }


        /**
         * Create the users table.
         *
         * @return boolean                  true if OK; false otherwise.
         */
        function create_table()
        {
            $conn = get_connection($this->db);

            // TODO(Contributors): Add a "deleted" field. Consider also a timestamp
            $sql = "CREATE TABLE $this->table_name (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                                                    user_id int NOT NULL,
                                                    report_uid VARCHAR(8) NOT NULL)";

            if ($conn->query($sql) !== FALSE)
            {
                return true;
            }

            $this->error = $conn->error;

            return false;
        }


        /**
         * Retrieve data on all contributions by the specified user.
         *
         * @param User      $user           The user for which the contributed reports should be retrieved.
         * @return array                    An array of reports to which the specified user is a contributor.
         */
        public function get_contributed_reports($user)
        {
            $reports = [];

            $this->error = null;

            $conn = get_connection($this->db);

            $sql = "SELECT * FROM $this->table_name INNER JOIN reports ON (report_uid = reports.uid) WHERE (user_id = :user_id)";

            if ($stmt = $conn->prepare($sql) )
            {
                // Bind variables as parameters to the prepared statement
                // and attempt to execute the prepared statement
                $stmt->bindParam(':user_id', $user->id, PDO::PARAM_INT);

                if ($stmt->execute() )
                {
                    if ($stmt->rowCount() > 0)
                    {
                        $rows = $stmt->fetchAll();

                        foreach ($rows as $row)
                        {
                            $report = new Report;

                            $report->set_from_row($row);

                            $reports[] = $report;
                        }
                    }
                }
            }
            else
            {
                $this->error = $conn->error;
            }
            return $reports;
        }


    /**
     * Determine whether the specified user is a contributor to the given report.
     *
     * @param User      $user           The user for which the contributed reports should be retrieved.
     * @param Report    $report         The report against which the given user's contributions should be checked.
     * @return bool                     true if the user contributed this report; false otherwise.
     */
    public function is_contributor_to_report($user, $report)
    {
        $this->error = null;

        $conn = get_connection($this->db);

        $sql = "SELECT * FROM $this->table_name WHERE ((user_id = :user_id) AND (report_uid = :report_uid))";

        if ($stmt = $conn->prepare($sql))
        {
            $stmt->bindParam(':user_id', $user->id, PDO::PARAM_INT);
            $stmt->bindParam(':report_uid', $report->uid, PDO::PARAM_STR);

            if ($stmt->execute())
            {
                if ($stmt->rowCount() > 0)
                {
                    return true;
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
     * Add the given report as a contribution for the specified user.
     *
     * @param User      $user           The user to add.
     * @param Report    $report         The report to which $user has been added as a contributor.
     * @return boolean                  true if the relationshipwas added successfully; false otherwise.
     */
        public function add_contribution($user, $report)
        {
            $conn = get_connection($this->db);

            $sql = "INSERT INTO $this->table_name (user_id, report_uid) VALUES (:user_id, :report_uid)";

            if ($stmt = $conn->prepare($sql) )
            {
                // Bind variables to the prepared statement as parameters
                $stmt->bindParam(':user_id',                    $user->id,                          PDO::PARAM_STR);
                $stmt->bindParam(':report_uid',                 $report->uid,                       PDO::PARAM_STR);

                // Attempt to execute the prepared statement
                if ($stmt->execute() )
                {
                    return true;
                }
            }
            return false;
        }

    }
?>
