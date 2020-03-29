<?php
    /**
     * MySQL model implementation classese for the "Posts" table.
     *
     */
    require_once('db_utils.php');



    /**
     * MySQL model implementation class for the "Posts" table.
     *
     */
    class Posts
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
        public function __construct($db, $table_name = 'posts')
        {
            $this->db         = $db;
            $this->table_name = $table_name;

            //TODO remove this ***TEMPORARY*** test code when we have real data
            if (DEV_INSTALL && table_exists($db, $this->table_name) )
            {
                drop_table($db, $this->table_name);
            }
            // **** End of ***TEMPORARY*** test code

            // Update DB table schema if necessary
            if (!table_exists($db, $this->table_name) )
            {
                $conn = get_connection($this->db);

                $this->create_table();

                if (DEV_INSTALL)
                {
                    $this->add_dummy_data();
                }
            }
        }



        /**
         * Create the "posts" table.
         *
         * @return boolean                  true if OK; false otherwise.
         */
        function create_table()
        {
            $conn = get_connection($this->db);

            $sql = "CREATE TABLE $this->table_name (id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                                                    author VARCHAR(255) NOT NULL,
                                                    title VARCHAR(255) NOT NULL,
                                                    timestamp DATETIME,
                                                    content TEXT NOT NULL)";

            if ($conn->query($sql) !== FALSE)
            {
                return true;
            }

            $this->error = $conn->error;

            return false;
        }


        /**
         * Get data on all posts.
         *
         * @return array                    An array of posts.
         */
        public function get_all()
        {
            $posts          = array();

            $this->error    = null;
            $conn           = get_connection($this->db);

            $sql            = "SELECT * FROM $this->table_name";

            $result         = $conn->query($sql);

            if ($result !== FALSE)
            {
                foreach ($result->fetchAll() as $row)
                {
                    $post    = new Post();

                    $post->set_from_row($row);

                    $posts[] = $post;
                }
            }
            else
            {
                $this->error = $conn->error;
            }
            return $posts;
        }


        /**
         * Get the given post.
         *
         * @param int      $id              The ID of the post to get.
         * @return Post                     The post corresponding to the specified id, or null if not found.
         */
        public function find($id)
        {
            $post = null;

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
                            $post = new Post();

                            $post->set_from_row($row);
                        }
                    }
                }
            }
            else
            {
                $this->error = $conn->error;
            }
            return $post;
        }



        /**
         * Add a post to the posts table of the database.
         *
         * @param Post $post                                The post to add.
         */
        public function add_post($post)
        {
            $conn = get_connection($this->db);

            $sql = "INSERT INTO $this->table_name (author, title, timestamp, content) VALUES (:author, :title, :timestamp, :content)";

            if ($stmt = $conn->prepare($sql) )
            {
                // Bind variables to the prepared statement as parameters
                $stmt->bindParam(':author',                     $post->author,              PDO::PARAM_STR);
                $stmt->bindParam(':title',                      $post->title,               PDO::PARAM_STR);
                $stmt->bindParam(':timestamp',                  $post->timestamp,           PDO::PARAM_STR);
                $stmt->bindParam(':content',                    $post->content,             PDO::PARAM_STR);

                // Attempt to execute the prepared statement
                if ($stmt->execute() )
                {
                    return true;
                }
            }
            return false;
        }


        /**
         * Add dummy data to the posts table of the database.
         *
         */
        private function add_dummy_data()
        {
            $post = new Post();

            $post->author       = 'author1';
            $post->title        = 'Test post 1';
            $post->timestamp    = '2020_03_29T11:59:00';
            $post->content      = "Any time scientists disagree, it's because we have insufficient data. Then we can agree on what kind of data to get; we get the data; and the data solves the problem. Either I'm right, or you're right, or we're both wrong. And we move on. That kind of conflict resolution does not exist in politics or religion.\n\n".
                                  "*For most of human civilization, the pace of innovation has been so slow that a generation might pass before a discovery would influence your life, culture or the conduct of nations*.\n\n".
                                  "I like to believe that science is becoming mainstream. It should have never been something that sort of geeky people do and no one else thinks about. Whether or not, it will always be what geeky people do. It should, as a minimum, be what everybody thinks about because science is all around us.\n\n".
                                  "So the history of discovery, particularly cosmic discovery, but discovery in general, scientific discovery, is one where at any given moment, there's a frontier. And there tends to be an urge for people, especially religious people, to assert that across that boundary, into the unknown, lies the handiwork of God. This shows up a lot.";


            $this->add_post($post);

            $post->author       = 'author2';
            $post->title        = 'Test post 2';
            $post->timestamp    = '2020_03_29T17:45:30';
            $post->content      = "**Asteroids have us in our sight**. The dinosaurs didn't have a space program, so they're not here to talk about this problem. We are, and we have the power to do something about it. I don't want to be the embarrassment of the galaxy, to have had the power to deflect an asteroid, and then not, and end up going extinct.\n\n".
                                  "It's actually the minority of religious people who rejects science or feel threatened by it or want to sort of undo or restrict the... where science can go. The rest, you know, are just fine with science. And it has been that way ever since the beginning.\n\n".
                                  "You will never find scientists leading armies into battle. You just won't. Especially not astrophysicists - we see the biggest picture there is. We understand how small we are in the cosmos. We understand how fragile and temporary our existence is here on Earth.\n\n".
                                  "Fortunately, there's another handy driver that has manifested itself throughout the history of cultures. The urge to want to gain wealth. That is almost as potent a driver as the urge to maintain your security. And that is how I view NASA going forward - as an investment in our economy.";

            $this->add_post($post);

            echo "<br>";
        }


    }



    /**
     * MySQL model implementation class for a single item (i.e. a "Post") within the "Posts" table.
     *
     */
    class Post
    {
        // These attributes are public so that we can access them using $post->author etc. directly

        /** @var int                        The id of the post. */
        public $id;

        /** @var string                     The title of the post. */
        public $title;

        /** @var string                     The author of the post. */
        public $author;

        /** @var string                     The timestamp of the post. */
        public $timestamp;

        /** @var string                     The content of the post. */
        public $content;

        /** @var string                     The permalink of the post. */
        public $permalink;


        /**
         * Set the contents of the object from the given database row.
         *
         * @param array $row                An array containing the contents of the given database row.
         */
        function set_from_row($row)
        {
            if (isset( $row['id']) )
            {
                $this->id           = $row['id'];
                $this->title        = $row['title'];
                $this->author       = $row['author'];
                $this->timestamp    = $row['timestamp'];
                $this->content      = $row['content'];

                $this->permalink    = "/?controller=posts&action=show&id=$this->id";
            }
        }


    }

?>