<?php
    /**
     * Import the specified blogposts.
     *
     */



    /**
     * Blogpost importer class.
     *
     * This class imports zip archives containing blogposts.
     *
     * To customise the format of the files contained in the archive, override or modify the following functions:
     *
     *      can_import_file()
     *      read_blogpost_files()
     *
     */
    class BlogImporter
    {
        /** @var BlogTable                          The "Blog" table. */
        public  $blog_table;

        /** @var string                             The path of the blog content folder (blog/content). */
        public  $content_folder_path;


        /**
         * Constructor
         *
         * @param BlogTable $blog_table             The "Blog" table.
         * @param BlogTable $content_folder_path    The path of the blog content folder (blog/content).
         */
        public function __construct($blog_table, $content_folder_path)
        {
            $this->blog_table           = $blog_table;
            $this->content_folder_path  = $content_folder_path;
        }


        public function upload_zipfiles()
        {
            $uploaded_pathnames  = array();

            // TODO: the code in this loop is IDENTICAL to that used by tdor.translivesmatter.info to import reports. We should consider consolidating it.
            foreach ($_FILES["zipfiles"]["error"] as $key => $error)
            {
                // We use basename() on the file name as it could help prevent filesystem traversal attacks
                $target_filename = basename($_FILES["zipfiles"]["name"][$key]);

                if ($error == UPLOAD_ERR_OK)
                {
                    $temp_file_pathname = $_FILES["zipfiles"]["tmp_name"][$key];

                    $target_pathname    = "$this->content_folder_path/$target_filename";

                    // If the target file exists, replace it
                    if (file_exists($target_pathname) )
                    {
                        unlink($target_pathname);
                    }
                    if (move_uploaded_file($temp_file_pathname, $target_pathname) )
                    {
                        $uploaded_pathnames[] = $target_pathname;
                    }
                }
                else
                {
                    echo "Unable to upload $target_filename. Error code $error<br>";
                }
            }
            return $uploaded_pathnames;
        }


        public function can_import_file($filename)
        {
            $fileext = pathinfo($filename, PATHINFO_EXTENSION);

            if (0 == strcasecmp('ini', $fileext) )
            {
                return true;
            }

            // EXTENSION FOR RIVERBLADE/ANNASPLACE BLOGS
            if (0 == strcasecmp('xml', $fileext) )
            {
                return true;
            }
            // END EXTENSION FOR RIVERBLADE/ANNASPLACE BLOGS
            return false;
        }


        public function import_zipfile($zipfile_pathname)
        {
            $details = new DatabaseItemChangeDetails;

            echo "Checking $zipfile_pathname<br>";

            $zipfile_ext = pathinfo($zipfile_pathname, PATHINFO_EXTENSION);

            if (0 == strcasecmp('zip', $zipfile_ext) )
            {
                extract_zipfile($zipfile_pathname, $this->content_folder_path);

                $archive = new ZipArchive();

                $archive->open($zipfile_pathname);

                $import_filenames = array();

                for ($i = 0; $i < $archive->numFiles; ++$i)
                {
                    $stat = $archive->statIndex( $i );

                    $archived_filename = $stat['name'];

                    echo "&nbsp;&nbsp;&nbsp;&nbsp;$archived_filename<br>";

                    if ($this->can_import_file($archived_filename) )
                    {
                        $import_filenames[] = $archived_filename;
                    }
                }

                $blogposts = array();

                foreach ($import_filenames as $filename)
                {
                    $blogposts_read = $this->read_blogpost_files("$this->content_folder_path/$filename");

                    foreach ($blogposts_read as $blogpost)
                    {
                        $blogposts[] = $blogpost;
                    }
                }

                $details = $this->import_blogposts($blogposts);
            }
            return $details;
        }


        function read_blogpost_files($import_file_pathname)
        {
            $blogposts = array();

            $fileext = pathinfo($import_file_pathname, PATHINFO_EXTENSION);

            if (0 == strcasecmp('ini', $fileext) )
            {
                $blogposts[] = $this->read_blogpost_ini_file($import_file_pathname);
            }
            return $blogposts;
        }


        /**
         * Add blogposts to the database corresponding to the specified CSV items
         *
         * @param array $blogposts                  An array of blogposts to import
         * @return DatabaseItemChangeDetails        Details of the blogposts added, deleted or updated.
         */
        public function import_blogposts($blogposts)
        {
            $details            = new DatabaseItemChangeDetails;

            $current_timestamp  = gmdate("Y-m-d H:i:s");

            $db_exists          = db_exists($this->blog_table->db);
            $blog_table_exists  = table_exists($this->blog_table->db, $this->blog_table->table_name);

            foreach ($blogposts as $blogpost)
            {
                $has_uid = !empty($blogpost->uid);

                if (!$has_uid)
                {
                    // If this blogpost has no UID, generate a new one which does not clash with existing entries
                    do
                    {
                        $uid                = get_random_hex_string();

                        $id                 = ($db_exists && $blog_table_exists) ? $this->blog_table->get_id_from_uid($uid) : 0;       // Check for clashes with the table

                        if ($id == 0)
                        {
                            $blogpost->uid  = $uid;
                        }
                    } while (empty($blogpost->uid) );
                }

                $blogpost->permalink        = BlogTable::create_permalink($blogpost);
                $blogpost->created          = $current_timestamp;
                $blogpost->updated          = $current_timestamp;

                $new_blogpost               = !$has_uid;
                $existing_blogpost          = false;
                $blogpost_updated           = false;

                $existing_id                = 0;

                if ($has_uid && $blog_table_exists)
                {
                    $existing_id = $this->blog_table->get_id_from_uid($blogpost->uid);

                    if ($existing_id > 0)
                    {
                        $existing_blogpost = $this->blog_table->find($existing_id);

                        if (!empty($existing_blogpost->created) )
                        {
                            $blogpost->created= $existing_blogpost->created;
                        }

                        if (!empty($existing_blogpost->updated) )
                        {
                            $blogpost->updated = $existing_blogpost->updated;
                        }

                        // If the entries are different, update the "last_updated" field
                        if (self::blogpost_contents_match($blogpost, $existing_blogpost) )
                        {
                            echo "&nbsp;&nbsp;Unchanged blogpost $blogpost->title ($blogpost->timestamp)<br>";
                        }
                        else
                        {
                            echo "&nbsp;&nbsp;<b>Updating blogpost $blogpost->title ($blogpost->timestamp)</b><br>";

                            $blogpost->updated  = $current_timestamp;

                            $existing_blogpost  = true;
                            $blogpost_updated   = true;
                        }
                    }
                    else
                    {
                        $new_blogpost = true;
                    }
                }

                if ($new_blogpost)
                {
                    $has_permalink_msg = '';

                    if (!$has_uid)
                    {
                        $has_permalink_msg = ' [<i>Warning: no permalink defined. This could cause duplicate entries</i>]';
                    }

                    echo "&nbsp;&nbsp;<b>Adding blogpost $blogpost->timestamp / $blogpost->title</b> $has_permalink_msg<br>";
                }

                if ($new_blogpost)
                {
                    $this->blog_table->add($blogpost);

                    $details->items_added[] = $blogpost;
                }
                else if ($blogpost_updated)
                {
                    $this->blog_table->update($blogpost);

                    $details->items_updated[] = $blogpost;
                }
            }
            return $details;
        }


        /**
         * Determine whether the contents of the two blogposts match.
         *
         * Note that the id, uid and permalink are *not* matched in this context.
         *
         * @param Report $blogpost1             The first blogpost.
         * @param Report $blogpost2             The second blogpost.
         * @return boolean                      true if the two blogposts match; false otherwise.
         */
        private static function blogpost_contents_match($blogpost1, $blogpost2)
        {
            if ( ($blogpost1->title                         == $blogpost2->title) &&
                 ($blogpost1->thumbnail_filename            == $blogpost2->thumbnail_filename) &&
                 ($blogpost1->thumbnail_caption             == $blogpost2->thumbnail_caption) &&
                 ($blogpost1->author                        == $blogpost2->author) &&
                 (date_str_to_iso($blogpost1->timestamp)    == date_str_to_iso($blogpost2->timestamp) ) &&
                 ($blogpost1->content                       == $blogpost2->content) &&
                 ($blogpost1->draft                         == $blogpost2->draft) &&
                 ($blogpost1->deleted                       == $blogpost2->deleted) )
            {
                return true;
            }
            return false;
        }


        /**
         * Read the specified blogpost metadata file (and the associated blogpost content file).
         *
         * @param string $pathname      The filename of the metadata ini file.
         * @return Blogpost             The blogpost read from the files
         */
        function read_blogpost_ini_file($pathname)
        {
            $full_pathname = get_root_path().'/'.$pathname;

            $folder_full_path = pathinfo($full_pathname, PATHINFO_DIRNAME);

            if (file_exists($full_pathname) )
            {
                $items = parse_ini_file($full_pathname, TRUE);

                $blogpost                       = new Blogpost();

                $blogpost->title                = $items['title'];
                $blogpost->timestamp            = $items['timestamp'];
                $blogpost->author               = $items['author'];
                $blogpost->thumbnail_filename   = $items['thumbnail_filename'];
                $blogpost->thumbnail_caption    = $items['thumbnail_caption'];
                $blogpost->permalink            = $items['permalink'];

                if (isset($items['draft']) )
                {
                    $blogpost->draft            = ( ('0' != $items['draft']) || ('true' == $items['draft']) ) ? true : false;
                }

                if (isset($items['deleted']) )
                {
                    $blogpost->deleted            = ( ('0' != $items['deleted']) || ('true' == $items['deleted']) ) ? true : false;
                }

                $content_filename               = $items['content_filename'];

                // Read the content file
                $blogpost->content              = file_get_contents($folder_full_path.'/'.$content_filename);

                // Parse the permalink and extract the uid (or "slug")
                if (!empty($blogpost->permalink) )
                {
                    $uid_len = 8;

                    if (strlen($blogpost->permalink) > $uid_len)
                    {
                        $blogpost->uid = substr($blogpost->permalink, -$uid_len);
                    }
                }
                return $blogpost;
            }
            return null;
        }

    }

?>