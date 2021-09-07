<?php
    /**
     * MySQL model implementation classes for the PageMetadata table.
     *
     */



    /**
     * MySQL model implementation class for the PageMetadata table.
     *
     */
    class PageMetadataTable
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
        public function __construct($db, $table_name = 'page_metadata')
        {
            $this->db         = $db;
            $this->table_name = $table_name;

            if (!table_exists($this->db, $this->table_name) )
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

            $sql = "CREATE TABLE $this->table_name( id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                                                    uid VARCHAR(50) NOT NULL UNIQUE,
                                                    url VARCHAR(255) NOT NULL UNIQUE,
                                                    host VARCHAR(64) NOT NULL,
                                                    site_name VARCHAR(128) NOT NULL,
                                                    title VARCHAR(512) NOT NULL,
                                                    description VARCHAR(1024) NOT NULL,
                                                    image_url VARCHAR(255),
                                                    timestamp DATETIME)";

            if ($conn->query($sql) !== FALSE)
            {
                return true;
            }

            $this->error = $conn->error;

            return false;
        }


        /**
         * Get all items.
         *
         * @return array                    An array of database entries.
         */
        public function get_all()
        {
            $metadata  		= array();

            $this->error    = null;
            $conn           = get_connection($this->db);

            $sql            = "SELECT * FROM $this->table_name";

            $result         = $conn->query($sql);

            if ($result !== FALSE)
            {
                foreach ($result->fetchAll() as $row)
                {
                    $item = new PageMetadataItem;

                    $item->set_from_row($row);

                    $metadata[] = $item;
                }
            }
            else
            {
                $this->error = $conn->error;
            }
            return $metadata;
        }


        /**
         * Get the given user.
         *
         * @param string $url               The URL for which metadata should be retrieved
         * @return PageMetadataItem         The database entry corresponding to the specified url, or null if not found.
         */
        public function get_metadata($url)
        {
            $page_metadata = null;

            $this->error = null;

            $conn = get_connection($this->db);

            $sql = "SELECT * FROM $this->table_name WHERE (url = :url)";

            if ($stmt = $conn->prepare($sql) )
            {
                $stmt->bindParam(':url', $url, PDO::PARAM_STR);

                if ($stmt->execute() )
                {
                    $n = $stmt->rowCount();

                    if ($stmt->rowCount() == 1)
                    {
                        if ($row = $stmt->fetch() )
                        {
                            $page_metadata = new PageMetadataItem;

                            $page_metadata->set_from_row($row);
                        }
                    }
                }
            }
            else
            {
                $this->error = $conn->error;
            }
            return $page_metadata;
        }


        /**
         * Add the given metadata.
         *
         * @param PageMetadataItem $page_metadata  	The item to add.
         * @return boolean                  		true if the item was added successfully; false otherwise.
         */
        public function add_metadata($page_metadata)
        {
            $conn = get_connection($this->db);

            $sql = "INSERT INTO $this->table_name (uid, url, host, site_name, title, description, image_url, timestamp) VALUES (:uid, :url, :host, :site_name, :title, :description, :image_url, :timestamp)";

            if ($stmt = $conn->prepare($sql) )
            {
                // Bind variables to the prepared statement as parameters
                $stmt->bindParam(':uid',           $page_metadata->uid,                 PDO::PARAM_STR);
                $stmt->bindParam(':url',           $page_metadata->url,             	PDO::PARAM_STR);
                $stmt->bindParam(':host',          $page_metadata->host,                PDO::PARAM_STR);
                $stmt->bindParam(':site_name',     $page_metadata->site_name,           PDO::PARAM_STR);
                $stmt->bindParam(':title',         $page_metadata->title,             	PDO::PARAM_STR);
                $stmt->bindParam(':description',   $page_metadata->description,         PDO::PARAM_STR);
                $stmt->bindParam(':image_url',     $page_metadata->image_url,           PDO::PARAM_STR);
                $stmt->bindParam(':timestamp',     $page_metadata->timestamp,           PDO::PARAM_STR);

                // Attempt to execute the prepared statement
                if ($stmt->execute() )
                {
                    return true;
                }
            }
            return false;
        }


        /**
         * Update the given item.
         *
         * @param PageMetadataItem $page_metadata	The item to update.
         * @return boolean                  	    true if the item was updated successfully; false otherwise.
         */
        public function update_metadata($page_metadata)
        {
            $conn = get_connection($this->db);

            $sql = "UPDATE $this->table_name SET uid = :uid, url = :url, host = :host, site_name = :site_name, title = :title, description = :description, image_url = :image_url, timestamp = :timestamp WHERE (url = :url)";

            if ($stmt = $conn->prepare($sql) )
            {
                // Bind variables to the prepared statement as parameters
                $stmt->bindParam(':uid',           $page_metadata->uid,                 PDO::PARAM_STR);
                $stmt->bindParam(':url',           $page_metadata->url,             	PDO::PARAM_STR);
                $stmt->bindParam(':host',          $page_metadata->host,                PDO::PARAM_STR);
                $stmt->bindParam(':site_name',     $page_metadata->site_name,           PDO::PARAM_STR);
                $stmt->bindParam(':title',         $page_metadata->title,             	PDO::PARAM_STR);
                $stmt->bindParam(':description',   $page_metadata->description,         PDO::PARAM_STR);
                $stmt->bindParam(':image_url',     $page_metadata->image_url,           PDO::PARAM_STR);
                $stmt->bindParam(':timestamp',     $page_metadata->timestamp,           PDO::PARAM_STR);

                // Attempt to execute the prepared statement
                if ($stmt->execute() )
                {
                    return true;
                }
            }
            return false;
        }


        /**
         * Delete the given item.
         *
         * @param PageMetadataItem $page_metadata   The item to delete.
         * @return boolean                          true if the item was deleted successfully; false otherwise.
         */
        public function delete_metadata($page_metadata)
        {
            $conn = get_connection($this->db);

            $sql = "DELETE FROM $this->table_name WHERE (url = :url)";

            if ($stmt = $conn->prepare($sql) )
            {
                // Bind variables to the prepared statement as parameters
                $stmt->bindParam(':url',           $page_metadata->url,            PDO::PARAM_STR);

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
     * MySQL model implementation class for a specific item in the page metadata table.
     *
     */
    class PageMetadataItem extends LinkPreviewMetadata
    {
        /** @var string                     The name of the user */
        public  $id;

        /** @var string                     The name of the user */
        public  $uid;

        /** @var string                     The name of the site. */
        public  $site_name;

        /** @var string                     The title of the page. */
        public  $title;

        /** @var string                     A description of the page. */
        public  $description;

        /** @var string                     The URL of the page. */
        public  $url;

        /** @var string                     The URL of the associated link preview image, if any. */
        public  $image_url;



        /**
         * Set the contents of the object from the given database row.
         *
         * @param array $row                An array containing the contents of the given database row.
         */
        function set_from_row($row)
        {
            if (isset( $row['url']) )
            {
                $this->id                       = $row['id'];
                $this->uid                      = $row['uid'];
                $this->url                      = $row['url'];
                $this->host                     = $row['host'];
                $this->site_name                = $row['site_name'];
                $this->title                    = $row['title'];
                $this->description              = $row['description'];
                $this->image_url                = $row['image_url'];
                $this->timestamp                = $row['timestamp'];
            }
        }


    }

?>