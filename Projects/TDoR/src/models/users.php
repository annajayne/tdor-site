<?php
    /**
     * MySQL model implementation classes for the users table.
     *
     */



    /**
     * MySQL model implementation class for the users table.
     *
     */
    class Users
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
         * @param array $table_name         The name of the table. The default is 'users'.
         */
        public function __construct($db, $table_name = 'users')
        {
            $this->db         = $db;
            $this->table_name = $table_name;
        }


        /**
         * Create the users table.
         *
         * @return boolean                  true if OK; false otherwise.
         */
        function create_table()
        {
            $conn = get_connection($this->db);

            $sql = "CREATE TABLE $this->table_name (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                                                username VARCHAR(50) NOT NULL UNIQUE,
                                                password VARCHAR(255) NOT NULL,
                                                roles VARCHAR(16) NOT NULL,
                                                activated INT NOT NULL,
                                                created_at DATETIME)";

            if ($conn->query($sql) !== FALSE)
            {
                return true;
            }
            
            $this->error = $conn->error;
            
            return false;
        }


        /**
         * Get data on all users.
         *
         * @return array                    An array of database entries corresponding to all users.
         */
        public function get_all()
        {
            $users          = array();

            $this->error    = null;
            $conn           = get_connection($this->db);

            $sql            = "SELECT * FROM $this->table_name";

            $result         = $conn->query($sql);

            if ($result !== FALSE)
            {
                foreach ($result->fetchAll() as $row)
                {
                    $user    = new User;
                    
                    $user->set_from_row($row);

                    $users[] = $user;
                }
            }
            else
            {
                $this->error = $conn->error;
            }
            return $users;
        }
    
  
        /**
         * Get the given user.
         *
         * @param User      $user           The user to get.
         * @return User                     The database entry corresponding to the specified username, or null if not found.
         */
        public function get_user($username)
        {
            $user = null;
            
            $this->error = null;
            
            $conn = get_connection($this->db);

            $sql = "SELECT * FROM $this->table_name WHERE username = :username";

            if ($stmt = $conn->prepare($sql) )
            {
                // Bind variables as parameters to the prepared statement
                // and attempt to execute the prepared statement
                $stmt->bindParam(':username', $username, PDO::PARAM_STR);

                if ($stmt->execute() )
                {
                    if ($stmt->rowCount() == 1)
                    {
                        if ($row = $stmt->fetch() )
                        {
                            $user = new User;

                            $user->set_from_row($row);
                        }
                    }
                }
            }
            else
            {
                $this->error = $conn->error;
            }
            return $user;
        }
  
  
        /**
         * Update the given user.
         *
         * @param User      $user           The user to update.
         * @return boolean                  true if the user was updated successfully; false otherwise.
         */
        public function update_user($user)
        {
            $conn = get_connection($this->db);

            $sql = "UPDATE $this->table_name SET roles = :roles, activated = :activated WHERE username = :username";

            if ($stmt = $conn->prepare($sql) )
            {
                // Bind variables to the prepared statement as parameters
                $stmt->bindParam(':username',   $user->username,    PDO::PARAM_STR);
                $stmt->bindParam(':roles',      $user->roles,       PDO::PARAM_STR);
                $stmt->bindParam(':activated',  $user->activated,   PDO::PARAM_STR);

                // Attempt to execute the prepared statement
                if ($stmt->execute() )
                {
                    return true;
                }
            }
            return false;
        }
    }
    

    
     /**
     * MySQL model implementation class for a specific user in the users table.
     *
     */
    class User
    {
        /** @var string                     The name of the user */
        public  $username;
        
        /** @var string                     The roles the user has */
        public  $roles;
        
        /** @var int                        Whether the user is active */
        public  $activated;
        
        /** @var string                     When the user was created */
        public  $created_at;



        /**
         * Set the contents of the object from the given database row.
         *
         * @param array $row                An array containing the contents of the given database row.
         */
        function set_from_row($row)
        {
             if (isset( $row['username']) )
            {
                $this->username     = $row['username'];
                $this->roles        = $row['roles'];
                $this->activated    = $row['activated'];
                $this->created_at   = $row['created_at'];
            }
        }

    }

        
?>