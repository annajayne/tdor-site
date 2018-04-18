<?php
    // MySQL model implementation
    //
    //
    class Post
    {
        // These attributes are public so that we can access them using $post->author etc. directly
        public $id;
        public $author;
        public $content;


        public function __construct($id, $author, $content)
        {
            $this->id      = $id;
            $this->author  = $author;
            $this->content = $content;
        }


        public static function all()
        {
            $list = array();
            
            $db = Db::getInstance();
            $result = $db->query('SELECT * FROM posts');

            foreach ($result->fetchAll() as $post)
            {
                $list[] = new Post($post['id'], $post['author'], $post['content']);
            }
            return $list;
        }


        public static function find($id)
        {
            $db = Db::getInstance();

            // Make sure that $id is an integer value
            $id = intval($id);

            $req = $db->prepare('SELECT * FROM posts WHERE id = :id');
            
            // the query was prepared, now we replace :id with our actual $id value
            $req->execute(array('id' => $id));
            if ($req)
            {
                $post = $req->fetch();

                return new Post($post['id'], $post['author'], $post['content']);
            }
            else
            {
                echo "<br>".$db->error;
            }
        }
    }

?>