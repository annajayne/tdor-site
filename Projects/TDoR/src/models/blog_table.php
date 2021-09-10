<?php
    /**
     * MySQL model implementation classes for the "Blog" table.
     *
     */
    require_once('util/string_utils.php');                  // For get_first_n_words()
    require_once('util/datetime_utils.php');                // For date_str_to_iso()
    require_once('util/markdown_utils.php');                // For markdown_to_html()
    require_once('defines.php');                            // For BLOG_SUBTITLE_MAX_WORDS
    require_once('models/db_utils.php');



    /**
     * Class to encapsulate report query parameters.
     *
     */
    class BlogTableQueryParams
    {
        // These attributes are public so that we can access them using $report->author etc. directly

        /** @var string                  The start date. */
        public  $date_from;

        /** @var string                  The finish date. */
        public  $date_to;

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
            $this->date_from            = '';
            $this->date_to              = '';
            $this->include_drafts       = false;
            $this->include_deleted      = false;
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
                return '(DATE(timestamp) >= :date_from AND DATE(timestamp) <= :date_to)';
            }
            return '';
        }


        /**
         * Get an SQL condition encapsulating the value of the "draft" property
         *
         * @return string                   The SQL  corresponding to the specified condition.
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
         * @return string                   The SQL  corresponding to the specified condition.
         */
        public function get_deleted_reports_condition_sql()
        {
            if ($this->include_deleted)
            {
                return '';
            }
            return '(deleted!=1)';
        }

    }


    /**
     * MySQL model implementation class for the "blog" table.
     *
     */
    class BlogTable
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
        }


        /**
         * Create the "Blog" table.
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
                                                    title VARCHAR(512) NOT NULL,
                                                    subtitle VARCHAR(1024) NOT NULL,
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
         * Get the dates of blogposts.
         *
         * @param BlogTableQueryParams $query_params    Query parameters.
         * @return array                                An array of dates.
         */
        public function get_dates($query_params)
        {
            $dates                              = [];

            $this->error                        = null;
            $conn                               = get_connection($this->db);

            $deleted_condition_sql              = $query_params->get_deleted_reports_condition_sql();
            $drafts_condition_sql               = $query_params->get_draft_reports_condition_sql();

            $condition_sql                      = '';

            if (!empty($deleted_condition_sql) || !empty($drafts_condition_sql) )
            {
                $condition_sql      = 'WHERE ';
                $and_sql            = '';

                if (!empty($deleted_condition_sql) )
                {
                    $condition_sql .= $deleted_condition_sql;
                    $and_sql        = ' AND ';
                }

                if (!empty($drafts_condition_sql) )
                {
                    $condition_sql .= $and_sql.$drafts_condition_sql;
                    $and_sql        = ' AND ';
                }
            }

            $sql = "DISTINCT date(timestamp) FROM $this->table_name $condition_sql ORDER by timestamp DESC";

            $result = $conn->query($sql);

            if ($result !== FALSE)
            {
                foreach ($result->fetchAll() as $row)
                {
                    $dates[] = $row['date(timestamp)'];
                }
            }
            else
            {
                $this->error = $conn->error;
            }
            return $dates;
        }


        /**
         * Get the total number of blogposts matching the given query parameters.
         *
         * @param BlogTableQueryParams $query_params    Query parameters.
         * @return array                                The number of blogposts matching the query parameters.
         */
        public function get_count($query_params)
        {
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
                    $and_sql        = ' AND ';
                }

                if (!empty($drafts_condition_sql) )
                {
                    $condition_sql .= $and_sql.$drafts_condition_sql;
                }
            }

            $sql = "SELECT count(id) FROM $this->table_name $condition_sql";

            if ($stmt = $conn->prepare($sql) )
            {
                if ($stmt->execute() && ($stmt->rowCount() == 1) )
                {
                    if ($row = $stmt->fetch() )
                    {
                        return (int)$row[0];
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
         * Get all blogposts matching the given query parameters.
         *
         * @param BlogTableQueryParams $query_params    Query parameters.
         * @return array                                An array of Blogposts.
         */
        public function get_all($query_params)
        {
            $blogposts                          = [];

            $this->error                        = null;
            $conn                               = get_connection($this->db);

            $date_range_condition_sql           = $query_params->get_date_range_condition_sql();
            $deleted_condition_sql              = $query_params->get_deleted_reports_condition_sql();
            $drafts_condition_sql               = $query_params->get_draft_reports_condition_sql();

            $condition_sql                      = '';

            if (!empty($date_range_condition_sql) || !empty($deleted_condition_sql) || !empty($drafts_condition_sql) )
            {
                $condition_sql      = 'WHERE ';
                $and_sql            = '';

                if (!empty($date_range_condition_sql) )
                {
                    $condition_sql .= $and_sql.$date_range_condition_sql;
                    $and_sql        = ' AND ';
                }

                if (!empty($deleted_condition_sql) )
                {
                    $condition_sql .= $and_sql.$deleted_condition_sql;
                    $and_sql        = ' AND ';
                }

                if (!empty($drafts_condition_sql) )
                {
                    $condition_sql .= $and_sql.$drafts_condition_sql;
                    $and_sql        = ' AND ';
                }
            }

            $sql = "SELECT * FROM $this->table_name $condition_sql ORDER by timestamp DESC";

            if ($stmt = $conn->prepare($sql) )
            {
                // Bind variables as parameters to the prepared statement
                $query_params->bind_statement($stmt);

                // Attempt to execute the prepared statement
                if ($stmt->execute() )
                {
                    $rows = $stmt->fetchAll();

                    foreach ($rows as $row)
                    {
                        $blogpost               = new Blogpost();

                        $blogpost->set_from_row($row);

                        $blogpost->permalink    = self::create_permalink($blogpost);

                        $blogposts[]            = $blogpost;
                    }
                }
            }
            else
            {
                $this->error = $conn->error;
            }
            return $blogposts;
        }


        /**
         * Get the given blogpost, given a database row ID
         *
         * @param int      $id              The ID of the blogpost to get.
         * @return Blogpost                 The blogpost corresponding to the specified id, or null if not found.
         */
        public function find($id)
        {
            $blogpost = null;

            $this->error = null;

            $conn = get_connection($this->db);

            $sql = "SELECT * FROM $this->table_name WHERE id = :id";

            if ($stmt = $conn->prepare($sql) )
            {
                // Bind variables as parameters to the prepared statement and attempt to execute it
                $stmt->bindParam(':id', $id, PDO::PARAM_STR);

                if ($stmt->execute() )
                {
                    if ($stmt->rowCount() == 1)
                    {
                        if ($row = $stmt->fetch() )
                        {
                            $blogpost               = new Blogpost();

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
                // Bind variables as parameters to the prepared statement and attempt to execute it
                $stmt->bindParam(':uid', $uid, PDO::PARAM_STR);

                if ($stmt->execute() )
                {
                    if ($stmt->rowCount() == 1)
                    {
                        if ($row = $stmt->fetch() )
                        {
                            $blogpost = new Blogpost();

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
         * Add a blogpost to the Blog table of the database.
         *
         * @param Blogpost $blogpost                            The blogpost to add.
         */
        public function add($blogpost)
        {
            $conn = get_connection($this->db);

            $sql = "INSERT INTO $this->table_name (uid, draft, deleted, author, title, subtitle, thumbnail_filename, thumbnail_caption, timestamp, content, created, updated) VALUES (:uid, :draft, :deleted, :author, :title, :subtitle, :thumbnail_filename, :thumbnail_caption, :timestamp, :content, :created, :updated)";

            if ($stmt = $conn->prepare($sql) )
            {
                // Bind variables to the prepared statement as parameters
                $stmt->bindParam(':uid',                        $blogpost->uid,                 PDO::PARAM_STR);
                $stmt->bindParam(':draft',                      $blogpost->draft,               PDO::PARAM_BOOL);
                $stmt->bindParam(':deleted',                    $blogpost->deleted,             PDO::PARAM_BOOL);
                $stmt->bindParam(':author',                     $blogpost->author,              PDO::PARAM_STR);
                $stmt->bindParam(':title',                      $blogpost->title,               PDO::PARAM_STR);
                $stmt->bindParam(':subtitle',                   $blogpost->subtitle,            PDO::PARAM_STR);
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
         * @param Blogpost $blogpost        The blogpost to update.
         * @return boolean                  true if the blogpost was updated successfully; false otherwise.
         */
        public function update($blogpost)
        {
            $conn = get_connection($this->db);

            $sql = "UPDATE $this->table_name SET title = :title, subtitle = :subtitle, thumbnail_filename = :thumbnail_filename, thumbnail_caption = :thumbnail_caption, timestamp = :timestamp, content = :content, created = :created, updated = :updated, draft = :draft, deleted = :deleted WHERE (id = :id)";

            if ($stmt = $conn->prepare($sql) )
            {
                // Bind variables to the prepared statement as parameters
                $stmt->bindParam(':id',                         $blogpost->id,                  PDO::PARAM_INT);
                $stmt->bindParam(':draft',                      $blogpost->draft,               PDO::PARAM_BOOL );
                $stmt->bindParam(':deleted',                    $blogpost->deleted,             PDO::PARAM_BOOL );
                $stmt->bindParam(':title',                      $blogpost->title,               PDO::PARAM_STR);
                $stmt->bindParam(':subtitle',                   $blogpost->subtitle,            PDO::PARAM_STR);
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
         * @return boolean                  true if the blogpost was deleted successfully; false otherwise.
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
         * Purge the given blogpost.
         *
         * @param string $blogpost          The blogpost to purge.
         * @return boolean                  true if the blogpost was purged successfully; false otherwise.
         */
        public function purge($blogpost)
        {
            $conn = get_connection($this->db);

            $sql = "DELETE FROM $this->table_name WHERE id=$blogpost->id";

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
         * Sanitise the title of the given blogpost to create a filesystem-safe folder name
         *
         * @param string $title                 The title of a blogpost
         * @return string                       A sanitised lowercase version of $title.
         */
        public static function get_filesystem_safe_title($title)
        {
            $title_field    = strtolower(replace_accents($title) );

            $title_field    = str_replace(' ',                 '-',    $title_field);
            $title_field    = preg_replace('/[^a-zA-Z_0-9-]/', '',     $title_field);

            $title_field    = urlencode($title_field);                               // Just in case we missed anything...

            return $title_field;
        }


         /**
         * Create an appropriate permalink for the given blogpost.
         *
         * @param Blogpost $blogpost            The blogpost to create a permalink for.
         * @return string                       The corresponding permalink.
         */
        public static function create_permalink($blogpost)
        {
            if (ENABLE_FRIENDLY_URLS)
            {
                $date           = new DateTime($blogpost->timestamp);
                $date_field     = $date->format('Y/m/d');

                $title_field    = self::get_filesystem_safe_title($blogpost->title);

                return "/blog/$date_field/$title_field"."_$blogpost->uid";
            }
            return "/?controller=blog&action=show&id=$blogpost->id";
        }

    }



    /**
     * MySQL model implementation class for a single blogpost within the "Blog" table.
     *
     */
    class Blogpost
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

        /** @var string                     The subtitle of the blogpost. */
        public $subtitle;

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
            $this->uid                  = '';
            $this->draft                = true;
            $this->deleted              = false;
            $this->title                = '';
            $this->subtitle             = '';
            $this->author               = '';
            $this->timestamp            = null;
            $this->content              = '';
            $this->thumbnail_filename   = '';
            $this->thumbnail_caption    = '';
            $this->permalink            = '';
            $this->created              = null;
            $this->updated              = null;

        }

        /**
         * Set the contents of the object from the given database row.
         *
         * @param array $row                An array containing the contents of the given database row.
         */
        function set_from_row($row)
        {
            $this->id                           = isset($row['id']) ? (int)$row['id'] : 0;

            if (isset( $row['uid']) )
            {
                $this->uid                      = $row['uid'];
                $this->draft                    = ('0' != $row['draft']) ? true : false;
                $this->deleted                  = ('0' != $row['deleted']) ? true : false;
                $this->title                    = $row['title'];
                $this->subtitle             	= $row['subtitle'];
                $this->thumbnail_filename       = $row['thumbnail_filename'];
                $this->thumbnail_caption        = $row['thumbnail_caption'];
                $this->author                   = $row['author'];
                $this->timestamp                = $row['timestamp'];
                $this->content                  = $row['content'];
                $this->created                  = $row['created'];
                $this->updated                  = $row['updated'];
            }
        }


        /**
         * Set the contents of the object from the given blogpost.
         *
         * @param Blogpost $blogpost        The blogpost whose data should be copied.
         */
        function set_from_post($blogpost)
        {
            $this->id                           = $blogpost->id;
            $this->uid                          = $blogpost->uid;
            $this->draft                        = $blogpost->draft;
            $this->deleted                      = $blogpost->deleted;
            $this->title                        = $blogpost->title;
            $this->subtitle                     = $blogpost->subtitle;
            $this->thumbnail_filename           = $blogpost->thumbnail_filename;
            $this->thumbnail_caption            = $blogpost->thumbnail_caption;
            $this->author                       = $blogpost->author;
            $this->timestamp                    = $blogpost->timestamp;
            $this->content                      = $blogpost->content;
            $this->permalink                    = $blogpost->permalink;
            $this->created                      = $blogpost->created;
            $this->updated                      = $blogpost->updated;
        }


        /**
         * Get a subtitle for the blogpost
         *
         * @return string                                   The subtitle.
         */
        function get_subtitle()
        {
            $subtitle               = $this->subtitle;

            if (empty($subtitle) )
            {
                $lines              = explode(PHP_EOL, $this->content);

                $lines              = array_slice($lines, 0, 9);
                foreach ($lines as $line)
                {
                    $subtitle       = $subtitle.$line.PHP_EOL;
                }

                $subtitle           = markdown_to_html($subtitle);
                $subtitle           = str_replace("<br />", " ", $subtitle);
                $subtitle           = strip_tags($subtitle, "");

                $truncated_subtitle = trim(get_first_n_words($subtitle, BLOG_SUBTITLE_MAX_WORDS) );

                if ($truncated_subtitle != trim($subtitle) )
                {
                    $subtitle       = $truncated_subtitle.'...';
                }
            }
            else
            {
                $subtitle           = str_replace("<br />", " ", $subtitle);
                $subtitle           = strip_tags($subtitle, "");
            }
            return $subtitle;
        }

    }

?>