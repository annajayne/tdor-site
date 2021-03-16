<?php
    /**
     * Import the specified blogposts.
     *
     */


    class BlogImporter
    {
        /**
         * Add blogposts  to the database corresponding to the specified CSV items
         *
         * @param array $blogposts                  An array of blogposts to import
         * @param Reports $blog_table               The existing "blogposts" table.
         * @return DatabaseItemChangeDetails        Details of the blogposts added, deleted or updated.
         */
        public static function import_blogposts($blogposts, $blog_table)
        {
            $details                = new DatabaseItemChangeDetails;

            $current_timestamp      = gmdate("Y-m-d H:i:s");

            $db_exists              = db_exists($blog_table->db);
            $blog_table_exists = table_exists($blog_table->db, $blog_table->table_name);

            foreach ($blogposts as $blogpost)
            {
                $has_uid = !empty($blogpost->uid);

                if (!$has_uid)
                {
                    // If this blogpost has no UID, generate a new one which does not clash with existing entries
                    do
                    {
                        $uid                    = get_random_hex_string();

                        $id                     = ($db_exists && $blog_table_exists) ? $blog_table->find_id_from_uid($uid) : 0;       // Check for clashes with the table

                        if ($id == 0)
                        {
                            $blogpost->uid      = $uid;
                        }
                    } while (empty($blogpost->uid) );
                }

                $blogpost->permalink            = Blogposts::create_permalink($blogpost);
                $blogpost->created              = $current_timestamp;
                $blogpost->updated              = $current_timestamp;

                $new_blogpost                   = !$has_uid;
                $existing_blogpost              = false;
                $blogpost_updated               = false;

                $existing_id                    = 0;

                if ($has_uid && $blog_table_exists)
                {
                    $existing_id = $blog_table->get_id_from_uid($blogpost->uid);

                    if ($existing_id > 0)
                    {
                        $existing_blogpost = $blog_table->find($existing_id);

                        if (!empty($existing_blogpost->created) )
                        {
                            $blogpost->created = $existing_blogpost->created;
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

                            $blogpost->updated      = $current_timestamp;

                            $existing_blogpost      = true;
                            $blogpost_updated       = true;
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
                    $blog_table->add($blogpost);

                    $details->items_added[] = $blogpost;
                }
                else if ($blogpost_updated)
                {
                    $blog_table->update($blogpost);

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

            $blogpost                       = new BlogPost();

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


?>