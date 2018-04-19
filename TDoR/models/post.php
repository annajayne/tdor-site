<?php
    // MySQL model implementation
    //
    //
    class Post
    {
        // These attributes are public so that we can access them using $post->author etc. directly
        public $id;
        public  $name;
        public  $age;
        public  $photo_filename;
        public  $photo_source;
        public  $year;
        public  $month;
        public  $day;
        public  $tgeu_ref;
        public  $location;
        public  $country;
        public  $cause;
        public  $description;


        private static function get_item_from_row($row)
        {
            $item = new Post();

            $item->id               =  $row['id'];
            $item->name             =  $row['name'];
            $item->age              =  $row['age'];
            $item->photo_filename   =  $row['photo_filename'];
            $item->photo_source     =  $row['photo_source'];
            $item->year             =  $row['year'];
            $item->month            =  $row['month'];
            $item->day              =  $row['day'];
            $item->tgeu_ref         =  $row['tgeu_ref'];
            $item->location         =  $row['location'];
            $item->country          =  $row['country'];
            $item->cause            =  $row['cause'];
            $item->description      =  $row['description'];

            return $item;
        }


        public static function all()
        {
            $list = array();

            $db = Db::getInstance();
            $result = $db->query('SELECT * FROM incidents');

            foreach ($result->fetchAll() as $row)
        {
                $item = Post::get_item_from_row($row);

                $list[] = $item;
            }
            return $list;
        }


        public static function find($id)
        {
            $db = Db::getInstance();

            // Make sure that $id is an integer value
            $id = intval($id);

            $sql = "SELECT * FROM incidents WHERE id = $id";

            $req = $db->query($sql);
            
            if ($req)
            {
                $row = $req->fetch();

                $item = Post::get_item_from_row($row);

                return $item;
            }
            else
            {
                echo "<br>".$db->error;
            }
        }
    }

?>