<?php
    /**
     * MySQL model implementation classes for the "BlogPosts" table.
     *
     */
    require_once('db_utils.php');



    /**
     * Class to encapsulate report query parameters.
     *
     */
    class BlogpostsQueryParams
    {
        // These attributes are public so that we can access them using $report->author etc. directly

        /** @var boolean                 Whether draft blogposts should be included. */
        public  $include_drafts;

        /** @var boolean                 Whether deleted blogposts should be included. */
        public  $include_deleted;



        /**
         * Constructor
         *
         */
        public function __construct()
        {
            $this->include_drafts   = false;
            $this->include_deleted  = false;
        }


       /**
         * Get an SQL condition encapsulating the value of the "draft" property
         *
         * @return string                   The SQL  corresponding to the given draft condition.
         */
        public function get_draft_reports_condition_sql()
        {
            if ($this->include_drafts)
            {
                return '';
            }
            return '(draft!=1)';
        }


       /**
         * Get an SQL condition encapsulating the value of the "deleted" property
         *
         * @return string                   The SQL  corresponding to the given draft condition.
         */
        public function get_deleted_reports_condition_sql()
        {
            if ($this->include_deleted)
            {
                return '';
            }
            return '(deleted=1)';
        }

    }


    /**
     * MySQL model implementation class for the "blog" table.
     *
     */
    class BlogPosts
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
        public function __construct($db, $table_name = 'blog')
        {
            $this->db         = $db;
            $this->table_name = $table_name;

            if (!table_exists($this->db, $this->table_name) )
            {
                $this->create_table();
            }

            $conn = get_connection($db);

            // If the "thumbnail_filename" column doesn't exist, create it.
            if (!column_exists($db, $this->table_name, 'thumbnail_filename') )
            {
                $sql = "ALTER TABLE `$this->table_name` ADD `thumbnail_filename` VARCHAR(255) DEFAULT '/images/tdor_candle_jars.jpg' AFTER title";

                if ($conn->query($sql) !== FALSE)
                {
                    log_text("Thumbnail_filename column added to $this->table_name table");
                }
            }

            // If the "thumbnail_caption" column doesn't exist, create it.
            if (!column_exists($db, $this->table_name, 'thumbnail_caption') )
            {
                $sql = "ALTER TABLE `$this->table_name` ADD `thumbnail_caption` VARCHAR(255) DEFAULT 'Memorial candles at a TDoR vigil' AFTER thumbnail_filename";

                if ($conn->query($sql) !== FALSE)
                {
                    log_text("Thumbnail_caption column added to $this->table_name table");
                }
            }

            // If the "created" and "updated" columns don't exist, create them.
            if (!column_exists($db, $this->table_name, 'created') )
            {
                $sql = "ALTER TABLE `$this->table_name` ADD `created` DATETIME AFTER content";

                if ($conn->query($sql) !== FALSE)
                {
                    log_text("created column added to $this->table_name table");
                }

                $sql = "ALTER TABLE `$this->table_name` ADD `updated` DATETIME AFTER created";

                if ($conn->query($sql) !== FALSE)
                {
                    log_text("updated column added to $this->table_name table");
                }
            }
        }


        /**
         * Create the "BlogPosts" table.
         *
         * @return boolean                  true if OK; false otherwise.
         */
        function create_table()
        {
            $conn = get_connection($this->db);

            $sql = "CREATE TABLE $this->table_name (id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                                                    uid VARCHAR(8) NOT NULL,
                                                    draft BOOL NOT NULL,
                                                    deleted BOOL NOT NULL,
                                                    author VARCHAR(255) NOT NULL,
                                                    title VARCHAR(255) NOT NULL,
                                                    thumbnail_filename VARCHAR(255) NOT NULL,
                                                    thumbnail_caption VARCHAR(255) NOT NULL,
                                                    timestamp DATETIME NOT NULL,
                                                    content TEXT NOT NULL,
                                                    created DATETIME NOT NULL,
                                                    updated DATETIME NOT NULL,
                                                    UNIQUE KEY (`uid`) )";

            if ($conn->query($sql) !== FALSE)
            {
                return true;
            }

            $this->error = $conn->error;

            return false;
        }


        /**
         * Get data on all BlogPosts.
         *
         * @param BlogpostsQueryParams $query_params    An array of BlogPosts.
         * @return array                                An array of BlogPosts.
         */
        public function get_all($query_params)
        {
            $BlogPosts              = array();

            $this->error            = null;
            $conn                   = get_connection($this->db);

            $deleted_condition_sql  = $query_params->get_deleted_reports_condition_sql();
            $drafts_condition_sql   = $query_params->get_draft_reports_condition_sql();

            $condition_sql          = '';

            if (!empty($deleted_condition_sql) || !empty($drafts_condition_sql) )
            {
                $condition_sql      = 'WHERE ';
                $and_sql            = '';

                if (!empty($deleted_condition_sql) )
                {
                    $condition_sql .= $deleted_condition_sql;
                    $and_sql            = ' AND ';
                }

                if (!empty($drafts_condition_sql) )
                {
                    $condition_sql .= $and_sql.$drafts_condition_sql;
                }
            }

            $sql    = "SELECT * FROM $this->table_name $condition_sql ORDER by timestamp DESC";

            $result = $conn->query($sql);

            if ($result !== FALSE)
            {
                foreach ($result->fetchAll() as $row)
                {
                    $blogpost               = new BlogPost();

                    $blogpost->set_from_row($row);

                    $blogpost->permalink    = self::create_permalink($blogpost);

                    $BlogPosts[]            = $blogpost;
                }
            }
            else
            {
                $this->error = $conn->error;
            }
            return $BlogPosts;
        }


        /**
         * Get the given blogpost, given a database row ID
         *
         * @param int      $id              The ID of the blogpost to get.
         * @return BlogPost                 The blogpost corresponding to the specified id, or null if not found.
         */
        public function find($id)
        {
            $blogpost = null;

            $this->error = null;

            $conn = get_connection($this->db);

            $sql = "SELECT * FROM $this->table_name WHERE id = :id";

            if ($stmt = $conn->prepare($sql) )
            {
                // Bind variables as parameters to the prepared statement
                // and attempt to execute the prepared statement
                $stmt->bindParam(':id', $id, PDO::PARAM_STR);

                if ($stmt->execute() )
                {
                    if ($stmt->rowCount() == 1)
                    {
                        if ($row = $stmt->fetch() )
                        {
                            $blogpost               = new BlogPost();

                            $blogpost->set_from_row($row);

                            $blogpost->permalink    = self::create_permalink($blogpost);
                        }
                    }
                }
            }
            else
            {
                $this->error = $conn->error;
            }
            return $blogpost;
        }


        /**
         * Locate the ID of a blogpost, given its UID.
         *
         * @param string      $uid          The UID of the blogpost to locate.
         * @return int                      The ID of the blogpost corresponding to the specified UID, or 0 if not found.
         */
        public function get_id_from_uid($uid)
        {
            $id             = 0;

            $this->error    = null;

            $conn           = get_connection($this->db);

            $sql            = "SELECT id FROM $this->table_name WHERE uid = :uid";

            if ($stmt = $conn->prepare($sql) )
            {
                // Bind variables as parameters to the prepared statement
                // and attempt to execute the prepared statement
                $stmt->bindParam(':uid', $uid, PDO::PARAM_STR);

                if ($stmt->execute() )
                {
                    if ($stmt->rowCount() == 1)
                    {
                        if ($row = $stmt->fetch() )
                        {
                            $blogpost = new BlogPost();

                            $blogpost->set_from_row($row);

                            $id = $blogpost->id;
                        }
                    }
                }
            }
            else
            {
                $this->error = $conn->error;
            }
            return $id;
        }


        /**
         * Add a blogpost to the BlogPosts table of the database.
         *
         * @param BlogPost $blogpost                            The blogpost to add.
         */
        public function add_post($blogpost)
        {
            $conn = get_connection($this->db);

            $sql = "INSERT INTO $this->table_name (uid, draft, deleted, author, title, thumbnail_filename, thumbnail_caption, timestamp, content, created, updated) VALUES (:uid, :draft, :deleted, :author, :title, :thumbnail_filename, :thumbnail_caption, :timestamp, :content, :created, :updated)";

            if ($stmt = $conn->prepare($sql) )
            {
                // Bind variables to the prepared statement as parameters
                $stmt->bindParam(':uid',                        $blogpost->uid,                 PDO::PARAM_STR);
                $stmt->bindParam(':draft',                      $blogpost->draft,               PDO::PARAM_BOOL);
                $stmt->bindParam(':deleted',                    $blogpost->deleted,             PDO::PARAM_BOOL);
                $stmt->bindParam(':author',                     $blogpost->author,              PDO::PARAM_STR);
                $stmt->bindParam(':title',                      $blogpost->title,               PDO::PARAM_STR);
                $stmt->bindParam(':thumbnail_filename',         $blogpost->thumbnail_filename,  PDO::PARAM_STR);
                $stmt->bindParam(':thumbnail_caption',          $blogpost->thumbnail_caption,   PDO::PARAM_STR);
                $stmt->bindParam(':timestamp',                  $blogpost->timestamp,           PDO::PARAM_STR);
                $stmt->bindParam(':content',                    $blogpost->content,             PDO::PARAM_STR);
                $stmt->bindParam(':created',                    $blogpost->created,             PDO::PARAM_STR);
                $stmt->bindParam(':updated',                    $blogpost->updated,             PDO::PARAM_STR);

                // Attempt to execute the prepared statement
                if ($stmt->execute() )
                {
                    return true;
                }
            }
            return false;
        }


        /**
         * Update the given blogpost.
         *
         * @param BlogPost $blogpost        The blogpost to update.
         * @return boolean                  true if the blogpost was updated successfully; false otherwise.
         */
        public function update_post($blogpost)
        {
            $conn = get_connection($this->db);

            $sql = "UPDATE $this->table_name SET title = :title, thumbnail_filename = :thumbnail_filename, thumbnail_caption = :thumbnail_caption, timestamp = :timestamp, content = :content, created = :created, updated = :updated, draft = :draft, deleted = :deleted  WHERE (id = :id)";

            if ($stmt = $conn->prepare($sql) )
            {
                // Bind variables to the prepared statement as parameters
                $stmt->bindParam(':id',                         $blogpost->id,                  PDO::PARAM_INT);
                $stmt->bindParam(':draft',                      $blogpost->draft,               PDO::PARAM_INT);
                $stmt->bindParam(':deleted',                    $blogpost->deleted,             PDO::PARAM_INT);
                $stmt->bindParam(':title',                      $blogpost->title,               PDO::PARAM_STR);
                $stmt->bindParam(':thumbnail_filename',         $blogpost->thumbnail_filename,  PDO::PARAM_STR);
                $stmt->bindParam(':thumbnail_caption',          $blogpost->thumbnail_caption,   PDO::PARAM_STR);
                $stmt->bindParam(':timestamp',                  $blogpost->timestamp,           PDO::PARAM_STR);
                $stmt->bindParam(':content',                    $blogpost->content,             PDO::PARAM_STR);
                $stmt->bindParam(':created',                    $blogpost->created,             PDO::PARAM_STR);
                $stmt->bindParam(':updated',                    $blogpost->updated,             PDO::PARAM_STR);

                // Attempt to execute the prepared statement
                if ($stmt->execute() )
                {
                    return true;
                }
            }
            return false;
        }


/**
         * Delete the given blogpost.
         *
         * @param string $blogpost          The blogpost to delete.
         * @return boolean                  true if the blogpost was delete successfully; false otherwise.
         */
        public function delete($blogpost)
        {
            $conn = get_connection($this->db);

            $sql = "UPDATE $this->table_name SET deleted=1 WHERE id=$blogpost->id";

            $result = $conn->query($sql);

            if ($result)
            {
                return true;
            }

            $this->error = $conn->error;

            return false;
        }


        /**
         * Create a new UID which is guaranteed to not be in use in the database.
         *
         * @return string                   A string containing the new UID (8 hex digits).
         */
        public function create_uid()
        {
            $uid = '';

            do
            {
                // Generate a new uid and check for clashes with existing entries
                $uid    = get_random_hex_string();

                $id     = $this->get_id_from_uid($uid); // Check for clashes with existing entries

                if ($id != 0)
                {
                    $uid = '';
                }
            } while (empty($uid) );

            return $uid;
        }


        /**
         * Create an appropriate permalink for the given blogpost.
         *
         * @param BlogPost $blogpost            The blogpost to create a permalink for.
         * @return string                       The corresponding permalink.
         */
        public static function create_permalink($blogpost)
        {
            if (ENABLE_FRIENDLY_URLS)
            {
                $date           = new DateTime($blogpost->timestamp);
                $date_field     = $date->format('Y/m/d');

                $title_field    = strtolower(replace_accents($blogpost->title) );

                $title_field    = str_replace(' ',                 '-',    $title_field);
                $title_field    = preg_replace('/[^a-zA-Z_0-9-]/', '',     $title_field);

                $title_field    = urlencode($title_field);                               // Just in case we missed anything...

                return "/blog/$date_field/$title_field"."_$blogpost->uid";
            }
            return "/?controller=blog&action=show&id=$blogpost->id";
        }


        /**
         * Add dummy data to the BlogPosts table of the database.
         *
         * TODO remove this ***TEMPORARY*** test code when we have real data.
         *
         */
        public function add_dummy_data()
        {
            if (table_exists($this->db, $this->table_name) )
            {
                drop_table($this->db, $this->table_name);
            }

            $this->create_table();

            $blogpost = new BlogPost();

            $blogpost->uid                  = $this->create_uid();
            $blogpost->draft                = false;
            $blogpost->author               = 'author1';
            $blogpost->title                = 'Test post 1';
            $blogpost->thumbnail_filename   = '/images/tdor_candle_jars.jpg';
            $blogpost->thumbnail_caption   = 'tdor_candle_jars';
            $blogpost->timestamp            = '2020_03_29T11:59:00';
            $blogpost->content              = "Any time scientists disagree, it's because we have insufficient data. Then we can agree on what kind of data to get; we get the data; and the data solves the problem. Either I'm right, or you're right, or we're both wrong. And we move on. That kind of conflict resolution does not exist in politics or religion.\n\n".
                                              "*For most of human civilization, the pace of innovation has been so slow that a generation might pass before a discovery would influence your life, culture or the conduct of nations*.\n\n".
                                              "I like to believe that science is becoming mainstream. It should have never been something that sort of geeky people do and no one else thinks about. Whether or not, it will always be what geeky people do. It should, as a minimum, be what everybody thinks about because science is all around us.\n\n".
                                              "So the history of discovery, particularly cosmic discovery, but discovery in general, scientific discovery, is one where at any given moment, there's a frontier. And there tends to be an urge for people, especially religious people, to assert that across that boundary, into the unknown, lies the handiwork of God. This shows up a lot.";
            $blogpost->created              = $blogpost->timestamp;
            $blogpost->updated              = $blogpost->created;

            $this->add_post($blogpost);

            $blogpost->uid                  = $this->create_uid();
            $blogpost->draft                = true;
            $blogpost->author               = 'author2';
            $blogpost->title                = 'Test post 2';
            $blogpost->thumbnail_filename   = '/images/tdor_candle_jars.jpg';
            $blogpost->thumbnail_caption   = 'tdor_candle_jars';
            $blogpost->timestamp            = '2020_04_02T17:45:30';
            $blogpost->content              = "**Asteroids have us in our sight**. The dinosaurs didn't have a space program, so they're not here to talk about this problem. We are, and we have the power to do something about it. I don't want to be the embarrassment of the galaxy, to have had the power to deflect an asteroid, and then not, and end up going extinct.\n\n".
                                              "It's actually the minority of religious people who rejects science or feel threatened by it or want to sort of undo or restrict the... where science can go. The rest, you know, are just fine with science. And it has been that way ever since the beginning.\n\n".
                                              "You will never find scientists leading armies into battle. You just won't. Especially not astrophysicists - we see the biggest picture there is. We understand how small we are in the cosmos. We understand how fragile and temporary our existence is here on Earth.\n\n".
                                              "Fortunately, there's another handy driver that has manifested itself throughout the history of cultures. The urge to want to gain wealth. That is almost as potent a driver as the urge to maintain your security. And that is how I view NASA going forward - as an investment in our economy.";
            $blogpost->created              = $blogpost->timestamp;
            $blogpost->updated              = $blogpost->created;

            $this->add_post($blogpost);
        }

    }



    /**
     * MySQL model implementation class for a single blogpost within the "BlogPosts" table.
     *
     */
    class BlogPost
    {
        // These attributes are public so that we can access them using $blogpost->author etc. directly

        /** @var int                        The id of the blogpost. */
        public $id;

        /** @var string                     The uid of the blogpost. */
        public $uid;

        /** @var boolean                    true if the blogpost is a draft; false otherwise. */
        public $draft;

        /** @var boolean                    true if the blogpost has been deleted; false otherwise. */
        public $deleted;

        /** @var string                     The title of the blogpost. */
        public $title;

        /** @var string                     The thumbnail which should be displayed for the blogpost. */
        public $thumbnail_filename;

        /** @var string                     The caption for the thumbnail which should be displayed for the blogpost. */
        public $thumbnail_caption;

        /** @var string                     The author of the blogpost. */
        public $author;

        /** @var string                     The timestamp of the blogpost. */
        public $timestamp;

        /** @var string                     The content of the blogpost. */
        public $content;

        /** @var string                     The permalink of the blogpost. */
        public $permalink;

        /** @var string                     When the blogpost was created. */
        public $created;

        /** @var string                     When the blogpost was last updated. */
        public $updated;


        /**
         * Constructor
         *
         */
        public function __construct()
        {
            $this->id                   = 0;
            $this->draft                = true;
            $this->deleted              = false;
            $this->title                = '';
            $this->author               = '';
            $this->content              = '';
            $this->thumbnail_filename   = '';
            $this->thumbnail_caption    = '';
            $this->permalink            = '';
        }

        /**
         * Set the contents of the object from the given database row.
         *
         * @param array $row                An array containing the contents of the given database row.
         */
        function set_from_row($row)
        {
            $this->id               = isset($row['id']) ? $row['id'] : 0;

            if (isset( $row['uid']) )
            {
                $this->uid                  = $row['uid'];
                $this->draft                = $row['draft'];
                $this->deleted              = $row['deleted'];
                $this->title                = $row['title'];
                $this->thumbnail_filename   = $row['thumbnail_filename'];
                $this->thumbnail_caption    = $row['thumbnail_caption'];
                $this->author               = $row['author'];
                $this->timestamp            = $row['timestamp'];
                $this->content              = $row['content'];
                $this->created              = $row['created'];
                $this->updated              = $row['updated'];
            }
        }


        /**
         * Set the contents of the object from the given blogpost.
         *
         * @param BlogPost $blogpost        The blogpost whose data should be copied.
         */
        function set_from_post($blogpost)
        {
            $this->id                       = $blogpost->id;
            $this->uid                      = $blogpost->uid;
            $this->draft                    = $blogpost->draft;
            $this->deleted                  = $blogpost->deleted;
            $this->title                    = $blogpost->title;
            $this->thumbnail_filename       = $blogpost->thumbnail_filename;
            $this->thumbnail_caption        = $blogpost->thumbnail_caption;
            $this->author                   = $blogpost->author;
            $this->timestamp                = $blogpost->timestamp;
            $this->content                  = $blogpost->content;
            $this->permalink                = $blogpost->permalink;
            $this->created                  = $blogpost->created;
            $this->updated                  = $blogpost->updated;
        }


    }

?>