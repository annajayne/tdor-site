<?php
    /**
     * Import the specified blogposts.
     *
     */
    require_once('util/path_utils.php');                // For append_path()
    require_once('util/markdown_utils.php');            // For get_image_filenames_from_markdown()
    require_once('models/items_change_details.php');    // For DatabaseItemsChangeDetails


    /**
     * Blogpost importer class.
     *
     * This class imports zip archives containing blogposts.
     *
     * To customise the format of the files contained in the archive, override or modify the following functions:
     *
     *      is_blogpost_metadata_file()
     *      read_blogpost_metadata_file()
     *
     */
    class BlogImporter
    {
        /** @var BlogTable                          The "Blog" table. */
        public  $blog_table;

        /** @var string                             The path of the blog content folder (blog/content). */
        public  $content_folder_path;

        /** @var string                             The path of the blog import folder (blog/content/import). */
        public  $import_folder_path;



        /**
         * Constructor
         *
         * @param BlogTable $blog_table             The "Blog" table.
         * @param BlogTable $content_folder_path    The path of the blog content folder (blog/content).
         * @param BlogTable $import_folder_path     The path of the blog import folder (blog/content/import).
         */
        public function __construct($blog_table, $content_folder_path, $import_folder_path)
        {
            $this->blog_table           = $blog_table;
            $this->content_folder_path  = $content_folder_path;
            $this->import_folder_path   = $import_folder_path;
        }


        /**
         * Upload zipfiles to the specified folder
         *
         * @param string $dest_folder_path      The destination folder.
         * @return array                        The pathnames of the files uploaded.
         */
        public function upload_zipfiles($dest_folder_path)
        {
            $uploaded_pathnames  = [];

            // TODO: the code in this loop is IDENTICAL to that used by tdor.translivesmatter.info to import reports. We should consider consolidating it.
            foreach ($_FILES["zipfiles"]["error"] as $key => $error)
            {
                // We use basename() on the file name as it could help prevent filesystem traversal attacks
                $target_filename = basename($_FILES["zipfiles"]["name"][$key]);

                if ($error == UPLOAD_ERR_OK)
                {
                    $temp_file_pathname = $_FILES["zipfiles"]["tmp_name"][$key];

                    $target_pathname    = append_path($dest_folder_path, $target_filename);

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


        /**
         * Import blogposts from the specified zipfile.
         *
         * @param string $zipfile_pathname      The pathname of the uploaded zipfile
         * @return array                        The pathnames of the files uploaded.
         *
         * This following operations are performed:
         *
         *   1. Extract the zipfile to a temporary folder.
         *   2. Identify any extracted `*.ini` files.
         *   3. For each `*.md` file referenced in a `.ini` file:
         *       - Parse and identify the paths of any image links. If any are relative (to the path of the `.md` file), adjust them.
         *       - Move the referenced media files to the destination folder (e.g. `/blog/content/media/<blogpost name>-<uid>`).
         *       - Import the blogpost and move media files to the destination folder (e.g. `/blog/content/media/<blogpost name>-<uid>`).
         *   4. Delete the temporary folder (EXTENSION).
         */
        public function import_zipfile($zipfile_pathname)
        {
            echo "Checking $zipfile_pathname<br>";

            $zipfile_name = pathinfo($zipfile_pathname, PATHINFO_FILENAME);
            $zipfile_ext = pathinfo($zipfile_pathname, PATHINFO_EXTENSION);

            $zipfile_extract_folder = "$this->content_folder_path/import/".$zipfile_name;

            $change_details = new DatabaseItemsChangeDetails;

            if (0 == strcasecmp('zip', $zipfile_ext) )
            {
                $files_in_archive = extract_zipfile($zipfile_pathname, $zipfile_extract_folder);

                foreach ($files_in_archive as $archived_filename)
                {
                    echo "&nbsp;&nbsp;&nbsp;&nbsp;$archived_filename<br>";

                    if ($this->is_blogpost_metadata_file($archived_filename) )
                    {
                        $blogpost_filenames[] = $archived_filename;
                    }
                    else if ($this->is_media_file($archived_filename) )
                    {
                        $media_filenames[] = $archived_filename;
                    }
                }

                $blogposts = [];


                foreach ($blogpost_filenames as $blogpost_filename)
                {
                    // Read the blogpost metadata and content
                    $blogposts_read = $this->read_blogpost_metadata_file(append_path($zipfile_extract_folder, $blogpost_filename) );

                    foreach ($blogposts_read as $blogpost)
                    {
                        // Import media files and adjust the paths referenced in the blogpost
                        $blogpost_media_folder_path = $this->get_blogpost_media_folder_path($blogpost);

                        $blogpost = $this->copy_blogpost_media($blogpost, $media_filenames, $zipfile_extract_folder, $blogpost_media_folder_path);

                        $blogposts[] = $blogpost;
                    }
                }

                $change_details = $this->import_blogposts($blogposts);
            }
            return $change_details;
        }


        /**
         * Read the properties of a blogpost from its metadata and content files
         *
         * @param string $metadata_file_pathname    The pathame of the blogpost's metadata file.
         * @return array                            The blogposts read.
         */
        function read_blogpost_metadata_file($metadata_file_pathname)
        {
            $blogposts = [];

            if ($this->is_blogpost_metadata_ini_file($metadata_file_pathname) )
            {
                $blogposts[] = $this->read_blogpost_metadata_ini_file($metadata_file_pathname);
            }
            return $blogposts;
        }



        /**
         * Add the specified blogposts to the database.
         *
         * @param array $blogposts                  An array of blogposts to import
         * @return DatabaseItemsChangeDetails       Details of the blogposts added, deleted or updated.
         */
        public function import_blogposts($blogposts)
        {
            $change_details     = new DatabaseItemsChangeDetails;

            $current_timestamp  = gmdate("Y-m-d H:i:s");

            $db_exists          = db_exists($this->blog_table->db);
            $blog_table_exists  = table_exists($this->blog_table->db, $this->blog_table->table_name);

            if ($db_exists && $blog_table_exists)
            {
                foreach ($blogposts as $blogpost)
                {
                    $has_uid                    = !empty($blogpost->uid);

                    $blogpost->created          = $current_timestamp;
                    $blogpost->updated          = $current_timestamp;

                    $new_blogpost               = !$has_uid;
                    $updated_blogpost           = false;

                    $existing_id                = 0;

                    if ($has_uid)
                    {
                        $existing_id = $this->blog_table->get_id_from_uid($blogpost->uid);

                        if ($existing_id > 0)
                        {
                            $blogpost->id       = $existing_id;
                            $existing_blogpost  = $this->blog_table->find($existing_id);

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

                                $blogpost->updated  = $current_timestamp;

                                $updated_blogpost   = true;
                            }
                        }
                        else
                        {
                            // If the blogpost has a uid but doesn't exist in the table, treat it as a new one.
                            $new_blogpost       = true;
                        }
                    }
                    else
                    {
                        // Allocate UID
                        $blogpost->uid = $this->blog_table->create_uid();
                    }

                    // Update or generate permalink as required
                    $blogpost->permalink = BlogTable::create_permalink($blogpost);

                    // Update the database
                    if ($new_blogpost)
                    {
                        $has_permalink_msg  = $has_uid ? '' : ' [<i>Warning: no permalink defined. This could cause duplicate entries</i>]';

                        echo "&nbsp;&nbsp;<b>Adding blogpost $blogpost->timestamp / $blogpost->title</b> $has_permalink_msg<br>";

                        $this->blog_table->add($blogpost);

                        $change_details->items_added[] = $blogpost;
                    }
                    else if ($updated_blogpost)
                    {
                        $this->blog_table->update($blogpost);

                        $change_details->items_updated[] = $blogpost;
                    }
                }
            }
            else
            {
                echo "<b>Unable to import blogposts - blogpost table does not exist</b><br>";
            }
            return $change_details;
        }


        /**
         * Return the path of the folder which should contain the media files for the given blogpost
         *
         * @param Blogpost $blogpost                The blogpost.
         * @param BlogTable $content_folder_path    The path where media files for the blogpost should be stored.
         */
        private function get_blogpost_media_folder_path($blogpost)
        {
            $blogpost_folder_name = BlogTable::get_filesystem_safe_title($blogpost->title);

            return "$this->content_folder_path/media/$blogpost_folder_name";
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
                 ($blogpost1->subtitle                      == $blogpost2->subtitle) &&
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
         * Is the specified filename a supported blogpost media file?
         *
         * @param string $filename              The filename.
         * @return boolean                      true if the file is a supported blogpost media file; false otherwise.
         */
        private function is_media_file($filename)
        {
            $fileext = pathinfo($filename, PATHINFO_EXTENSION);

            if (0 == strcasecmp('jpg', $fileext) )
            {
                return true;
            }

            if (0 == strcasecmp('png', $fileext) )
            {
                return true;
            }

            if (0 == strcasecmp('gif', $fileext) )
            {
                return true;
            }
            return false;
        }


        /**
         * Copy the media files referenced in a blogpost from $zipfile_extract_folder to the destination folder (e.g. `/blog/content/media/<blogpost name>-<uid>`).
         *
         * @param Blogpost $blogpost                    The blogpost
         * @param array $media_filenames                The filenames of the available media files.
         * @param string $media_source_folder_path      The source path for media files associated with the blogpost (i.e. the folder where the zipfile containing the blogpost was extracted)
         * @param string $media_dest_folder_path        The destination path for media files associated with the blogpost.
         * @return Blogpost                             The blogpost, with paths adjusted to take into account of the moved files.
         */
        private function copy_blogpost_media($blogpost, $media_filenames, $media_source_folder_path, $media_dest_folder_path)
        {
            $root_path = get_root_path();

            $referenced_media_filenames = get_image_filenames_from_markdown($blogpost->content);

            foreach ($referenced_media_filenames as $referenced_media_filename)
            {
                $filename = pathinfo($referenced_media_filename, PATHINFO_BASENAME);

                $source_pathname = "$media_source_folder_path/$referenced_media_filename";

                $dest_pathname = "$media_dest_folder_path/$filename";

                if (!file_exists($media_dest_folder_path) )
                {
                    mkdir(append_path($root_path, $media_dest_folder_path), 0755, true);
                }

                if (file_exists($source_pathname) )
                {
                    // TODO consider changing this to copy() - this will allow multiple blogposts to have their own copy of the same image if required (this makes image management far easier!)
                    rename(append_path($root_path, $source_pathname), append_path($root_path, $dest_pathname) );
                }

                // Adjust any references to the media file in the blogpost content
                $blogpost->content              = str_replace($referenced_media_filename, '/'.$dest_pathname, $blogpost->content);
                $blogpost->thumbnail_filename   = str_replace($referenced_media_filename, '/'.$dest_pathname, $blogpost->thumbnail_filename);
            }
            return $blogpost;
        }


        /**
         * Is the specified filename a supported blogpost metadata file?
         *
         * @param string $filename              The filename.
         * @return boolean                      true if the file is a supported blogpost metadata file; false otherwise.
         */
        private function is_blogpost_metadata_file($filename)
        {
            if ($this->is_blogpost_metadata_ini_file($filename) )
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
        private function read_blogpost_metadata_ini_file($pathname)
        {
            $full_pathname = append_path(get_root_path(), $pathname);

            $folder_full_path = pathinfo($full_pathname, PATHINFO_DIRNAME);

            if (file_exists($full_pathname) )
            {
                $items = parse_ini_file($full_pathname, TRUE);

                $item_datetime                  = new DateTime($items['timestamp'], new DateTimeZone('UTC') );

                $blogpost                       = new Blogpost();

                $blogpost->title                = $items['title'];

                if (isset($items['subtitle']) )
                {
                    $blogpost->subtitle         = $items['subtitle'];
                }

                $blogpost->timestamp            = $item_datetime->format("Y-m-d H:i:s");
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

                $content_filename               = basename($items['content_filename']);

                // Read the content file
                $blogpost->content              = file_get_contents(append_path($folder_full_path, $content_filename) );

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


        /**
         * Is the specified filename a blogpost.ini metadata file?
         *
         * @param string $filename              The filename.
         * @return boolean                      true if the file is a blogpost.ini metadata file; false otherwise.
         */
        private function is_blogpost_metadata_ini_file($filename)
        {
            $fileext = pathinfo($filename, PATHINFO_EXTENSION);

            if (0 == strcasecmp('ini', $fileext) )
            {
                return true;
            }
            return false;
        }


    }
?>