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

            // Update DB table schema if necessary
            if (table_exists($db, $this->table_name) )
            {
                $conn = get_connection($db);

                // If the "roles" column doesn't exist, create it.
                if (!column_exists($db, $this->table_name, 'roles') )
                {
                    $sql = "ALTER TABLE `users` ADD `roles` VARCHAR(16) AFTER password";

                    if ($conn->query($sql) !== FALSE)
                    {
                        log_text("Roles column added to users table");
                    }
                }

                if (!column_exists($db, $this->table_name, 'email') )
                {
                    $sql = "ALTER TABLE users ADD COLUMN email varchar(128) AFTER username";

                    if ($conn->query($sql) !== FALSE)
                    {
                        log_text("Inserted email column to users table");
                    }
                }

                if (!column_exists($db, $this->table_name, 'api_key') )
                {
                    $sql = "ALTER TABLE users ADD COLUMN api_key varchar(64) AFTER roles";

                    if ($conn->query($sql) !== FALSE)
                    {
                        log_text("Inserted api_key column to users table");
                    }
                }

                $conn = null;

                $conn = null;
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

            $sql = "CREATE TABLE $this->table_name (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                                                username VARCHAR(50) NOT NULL UNIQUE,
                                                email VARCHAR(128) NOT NULL UNIQUE,
                                                password VARCHAR(255) NOT NULL,
                                                roles VARCHAR(16) NOT NULL,
                                                api_key VARCHAR(64) NOT NULL,
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
         * Get the user corresponding to a given email address.
         *
         * @param string      $email        The email address of the user to get.
         * @return User                     The database entry corresponding to the specified email address, or null if not found.
         */
        public function get_user_from_email_address($email)
        {
            $user = null;
            
            $this->error = null;
            
            $conn = get_connection($this->db);

            $sql = "SELECT * FROM $this->table_name WHERE email = :email";

            if ($stmt = $conn->prepare($sql) )
            {
                // Bind variables as parameters to the prepared statement
                // and attempt to execute the prepared statement
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);

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
         * Get the user corresponding to a given API key.
         *
         * @param string $api_key           The API key to validate.
         * @return User                     The user corresponding to the specified API key, or null if not found.
         */
        public function get_user_from_api_key($api_key)
        {
            $user = null;
            
            $this->error = null;
            
            $conn = get_connection($this->db);

            $sql = "SELECT * FROM $this->table_name WHERE api_key = :api_key";

            if ($stmt = $conn->prepare($sql) )
            {
                // Bind variables as parameters to the prepared statement
                // and attempt to execute the prepared statement
                $stmt->bindParam(':api_key', $api_key, PDO::PARAM_STR);

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
         * Add the given user.
         *
         * @param User      $user           The user to add.
         * @return boolean                  true if the user was added successfully; false otherwise.
         */
        public function add_user($user)
        {
            $conn = get_connection($this->db);

            $sql = "INSERT INTO $this->table_name (username, email, password, roles, activated, created_at) VALUES (:username, :email, :password, :roles, :activated, :created_at)";

            if ($stmt = $conn->prepare($sql) )
            {
                // Bind variables to the prepared statement as parameters
                $stmt->bindParam(':username',   $user->username,            PDO::PARAM_STR);
                $stmt->bindParam(':email',      $user->email,               PDO::PARAM_STR);
                $stmt->bindParam(':password',   $user->hashed_password,     PDO::PARAM_STR);
                $stmt->bindParam(':roles',      $user->roles,               PDO::PARAM_STR);
                $stmt->bindParam(':api_key',    $user->api_key,             PDO::PARAM_STR);
                $stmt->bindParam(':activated',  $user->activated,           PDO::PARAM_STR);
                $stmt->bindParam(':created_at', $user->created_at,          PDO::PARAM_STR);

                // Attempt to execute the prepared statement
                if ($stmt->execute() )
                {
                    return true;
                }
            }
            return false;
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

            $sql = "UPDATE $this->table_name SET password = :password, roles = :roles, api_key = :api_key, activated = :activated WHERE username = :username";

            if ($stmt = $conn->prepare($sql) )
            {
                // Bind variables to the prepared statement as parameters
                $stmt->bindParam(':username',   $user->username,            PDO::PARAM_STR);
                $stmt->bindParam(':password',   $user->hashed_password,     PDO::PARAM_STR);
                $stmt->bindParam(':roles',      $user->roles,               PDO::PARAM_STR);
                $stmt->bindParam(':api_key',    $user->api_key,             PDO::PARAM_STR);
                $stmt->bindParam(':activated',  $user->activated,           PDO::PARAM_STR);

                // Attempt to execute the prepared statement
                if ($stmt->execute() )
                {
                    return true;
                }
            }
            return false;
        }


        /**
         * Generate an API key.
         *
         * @param User $user                The user for which an API key should be generated. Reserved for expansion
         * @return string                   An API key.
         */
        function generate_api_key($user)
        {
            $api_key = '';

            $encoder = new Tuupola\Base62;

            do
            {
                $api_key = $encoder->encode(random_bytes(24) );

                if (!$this->get_user_from_api_key($api_key) )
                {
                    break;
                }

            } while (true);

            return $api_key;
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
        
        /** @var string                     The email address of the user */
        public  $email;
        
        /** @var string                     The hashed password of the user */
        public  $hashed_password;
        
        /** @var string                     The roles the user has */
        public  $roles;
        
        /** @var string                     The API key of the user (N.B. may be blank if the API user role is not applied) */
        public  $api_key;
        
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
                $this->username         = $row['username'];
                $this->email            = $row['email'];
                $this->hashed_password  = $row['password'];
                $this->roles            = $row['roles'];
                $this->api_key          = $row['api_key'];
                $this->activated        = $row['activated'];
                $this->created_at       = $row['created_at'];
            }
        }


    }

?>