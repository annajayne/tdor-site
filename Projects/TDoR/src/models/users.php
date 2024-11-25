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
                    $sql = "ALTER TABLE `$this->table_name` ADD `roles` VARCHAR(16) AFTER password";

                    if ($conn->query($sql) !== FALSE)
                    {
                        log_text("Roles column added to $this->table_name table");
                    }
                }

                if (!column_exists($db, $this->table_name, 'email') )
                {
                    $sql = "ALTER TABLE users ADD COLUMN email VARCHAR(128) AFTER username";

                    if ($conn->query($sql) !== FALSE)
                    {
                        log_text("Inserted email column to $this->table_name table");
                    }
                }

                if (!column_exists($db, $this->table_name, 'api_key') )
                {
                    $sql = "ALTER TABLE users ADD COLUMN api_key VARCHAR(64) AFTER roles";

                    if ($conn->query($sql) !== FALSE)
                    {
                        log_text("Inserted api_key column to $this->table_name table");
                    }
                }

                if (!column_exists($db, $this->table_name, 'confirmation_id') )
                {
                    $sql = "ALTER TABLE users ADD COLUMN confirmation_id VARCHAR(64) AFTER api_key";

                    if ($conn->query($sql) !== FALSE)
                    {
                        log_text("Inserted confirmation_id column to $this->table_name table");
                    }
                }

                if (!column_exists($db, $this->table_name, 'password_reset_id') )
                {
                    $sql = "ALTER TABLE users ADD COLUMN password_reset_id VARCHAR(64) AFTER confirmation_id";

                    if ($conn->query($sql) !== FALSE)
                    {
                        log_text("Inserted password_reset_id column to $this->table_name table");
                    }
                }

                if (!column_exists($db, $this->table_name, 'password_reset_timestamp') )
                {
                    $sql = "ALTER TABLE users ADD COLUMN password_reset_timestamp DATETIME AFTER password_reset_id";

                    if ($conn->query($sql) !== FALSE)
                    {
                        log_text("Inserted password_reset_timestamp column to $this->table_name table");
                    }
                }

                if (!column_exists($db, $this->table_name, 'last_login') )
                {
                    $sql = "ALTER TABLE users ADD COLUMN last_login DATETIME AFTER created_at";

                    if ($conn->query($sql) !== FALSE)
                    {
                        log_text("Inserted last_login column to users table");
                    }
                }

                if (!column_exists($db, $this->table_name, 'api_key_last_used') )
                {
                    $sql = "ALTER TABLE users ADD COLUMN api_key_last_used DATETIME AFTER last_login";

                    if ($conn->query($sql) !== FALSE)
                    {
                        log_text("Inserted api_key_last_used column to users table");
                    }
                }
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
                                                confirmation_id VARCHAR(64) NOT NULL,
                                                password_reset_id VARCHAR(64),
                                                password_reset_timestamp DATETIME,
                                                activated INT NOT NULL,
                                                created_at DATETIME,
                                                last_login DATETIME,
                                                api_key_last_used DATETIME)";

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

            $sql = "SELECT * FROM $this->table_name WHERE (username = :username)";

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

            $sql = "SELECT * FROM $this->table_name WHERE (email = :email)";

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
         * Get the user corresponding to a given confirmation id.
         *
         * @param string      $email        The confirmation id of the user to get.
         * @return User                     The database entry corresponding to the specified email address, or null if not found.
         */
        public function get_user_from_confirmation_id($confirmation_id)
        {
            $user = null;

            $this->error = null;

            $conn = get_connection($this->db);

            $sql = "SELECT * FROM $this->table_name WHERE (confirmation_id = :confirmation_id)";

            if ($stmt = $conn->prepare($sql) )
            {
                // Bind variables as parameters to the prepared statement
                // and attempt to execute the prepared statement
                $stmt->bindParam(':confirmation_id', $confirmation_id, PDO::PARAM_STR);

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
         * Get the user corresponding to a given confirmation id.
         *
         * @param string      $email        The confirmation id of the user to get.
         * @return User                     The database entry corresponding to the specified email address, or null if not found.
         */
        public function get_user_from_password_reset_id($password_reset_id)
        {
            $user = null;

            $this->error = null;

            $conn = get_connection($this->db);

            $sql = "SELECT * FROM $this->table_name WHERE (password_reset_id = :password_reset_id)";

            if ($stmt = $conn->prepare($sql) )
            {
                // Bind variables as parameters to the prepared statement
                // and attempt to execute the prepared statement
                $stmt->bindParam(':password_reset_id', $password_reset_id, PDO::PARAM_STR);

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

            $sql = "SELECT * FROM $this->table_name WHERE (api_key = :api_key)";

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

            $sql = "INSERT INTO $this->table_name (username, email, password, roles, api_key, confirmation_id, password_reset_id, password_reset_timestamp, activated, created_at, last_login, api_key_last_used) VALUES (:username, :email, :password, :roles, :api_key, :confirmation_id, :password_reset_id, :password_reset_timestamp, :activated, :created_at, :last_login, :api_key_last_used)";

            if ($stmt = $conn->prepare($sql) )
            {
                // Bind variables to the prepared statement as parameters
                $stmt->bindParam(':username',                   $user->username,                    PDO::PARAM_STR);
                $stmt->bindParam(':email',                      $user->email,                       PDO::PARAM_STR);
                $stmt->bindParam(':password',                   $user->hashed_password,             PDO::PARAM_STR);
                $stmt->bindParam(':roles',                      $user->roles,                       PDO::PARAM_STR);
                $stmt->bindParam(':api_key',                    $user->api_key,                     PDO::PARAM_STR);
                $stmt->bindParam(':confirmation_id',            $user->confirmation_id,             PDO::PARAM_STR);
                $stmt->bindParam(':password_reset_id',          $user->password_reset_id,           PDO::PARAM_STR);
                $stmt->bindParam(':password_reset_timestamp',   $user->password_reset_timestamp,    PDO::PARAM_STR);
                $stmt->bindParam(':activated',                  $user->activated,                   PDO::PARAM_STR);
                $stmt->bindParam(':created_at',                 $user->created_at,                  PDO::PARAM_STR);
                $stmt->bindParam(':last_login',                 $user->last_login,                  PDO::PARAM_STR);
                $stmt->bindParam(':api_key_last_used',          $user->api_key_last_used,           PDO::PARAM_STR);

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

            $sql = "UPDATE $this->table_name SET password = :password, roles = :roles, api_key = :api_key, confirmation_id = :confirmation_id, password_reset_id = :password_reset_id, password_reset_timestamp = :password_reset_timestamp, activated = :activated, last_login = :last_login, api_key_last_used = :api_key_last_used WHERE (username = :username)";

            if ($stmt = $conn->prepare($sql) )
            {
                // Bind variables to the prepared statement as parameters
                $stmt->bindParam(':username',                   $user->username,                    PDO::PARAM_STR);
                $stmt->bindParam(':password',                   $user->hashed_password,             PDO::PARAM_STR);
                $stmt->bindParam(':roles',                      $user->roles,                       PDO::PARAM_STR);
                $stmt->bindParam(':api_key',                    $user->api_key,                     PDO::PARAM_STR);
                $stmt->bindParam(':confirmation_id',            $user->confirmation_id,             PDO::PARAM_STR);
                $stmt->bindParam(':password_reset_id',          $user->password_reset_id,           PDO::PARAM_STR);
                $stmt->bindParam(':password_reset_timestamp',   $user->password_reset_timestamp,    PDO::PARAM_STR);
                $stmt->bindParam(':activated',                  $user->activated,                   PDO::PARAM_STR);
                $stmt->bindParam(':last_login',                 $user->last_login,                  PDO::PARAM_STR);
                $stmt->bindParam(':api_key_last_used',          $user->api_key_last_used,           PDO::PARAM_STR);

                // Attempt to execute the prepared statement
                if ($stmt->execute() )
                {
                    return true;
                }
            }
            return false;
        }


        /**
         * Delete the given user.
         *
         * @param User      $user           The user to delete.
         * @return boolean                  true if the user was deleted successfully; false otherwise.
         */
        public function delete_user($user)
        {
            $conn = get_connection($this->db);

            $sql = "DELETE FROM $this->table_name WHERE (username = :username)";

            if ($stmt = $conn->prepare($sql) )
            {
                // Bind variables to the prepared statement as parameters
                $stmt->bindParam(':username',           $user->username,            PDO::PARAM_STR);

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

        /** @var string                     The confirmation ID sent to the user when they first register an account.
         *                                  If blank, their registration has been confirmed */
        public  $confirmation_id;

        /** @var string                     The ID sent to the user when they attempt to reset their password. */
        public  $password_reset_id;

        /** @var string                     When the password reset ID was created */
        public  $password_reset_timestamp;

        /** @var int                        Whether the user is active */
        public  $activated;

        /** @var string                     When the user was created */
        public  $created_at;

        /** @var string                     When the user last logged in */
        public  $last_login;

        /** @var string                     When the user last used their API key */
        public  $api_key_last_used;

        /**
         * Determine whether the password reset ID is set and has not timed out
         *
         * @return boolean                  true if the password reset ID is valid; false otherwise.
         */
        function is_password_reset_still_valid()
        {
            $valid = false;

            if (!empty($this->password_reset_id) && !empty($this->password_reset_timestamp) )
            {
                $datetime_reset_request = new DateTime($this->password_reset_timestamp);
                $datetime_now           = new DateTime();

                $interval = date_diff($datetime_now, $datetime_reset_request);

                // The password_reset_id is valid for 24 hours
                if ($interval->d === 0)
                {
                    return true;
                }
            }
            return false;
        }


        /**
         * Set the contents of the object from the given database row.
         *
         * @param array $row                An array containing the contents of the given database row.
         */
        function set_from_row($row)
        {
            if (isset( $row['username']) )
            {
                $this->username                 = $row['username'];
                $this->email                    = $row['email'];
                $this->hashed_password          = $row['password'];
                $this->roles                    = $row['roles'];
                $this->api_key                  = $row['api_key'];
                $this->confirmation_id          = $row['confirmation_id'];
                $this->password_reset_id        = $row['password_reset_id'];
                $this->password_reset_timestamp = $row['password_reset_timestamp'];
                $this->activated                = $row['activated'];
                $this->created_at               = $row['created_at'];
                $this->last_login               = $row['last_login'];
                $this->api_key_last_used        = $row['api_key_last_used'];
            }
        }

    }

?>