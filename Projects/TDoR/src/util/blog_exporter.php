<?php
    /**
     * Export the specified blogposts.
     *
     */
    require_once('misc.php');
    require_once('util/path_utils.php');                // For append_path()
    require_once('util/markdown_utils.php');            // For get_image_filenames_from_markdown()


    /**
     * Class to export blogposts.
     *
     */
    class BlogExporter
    {
        /** @var array                                  The blogposts to export. */
        public $blogposts;

        /** @var string                                The path of the blog media folder (blog/content/media). */
        public $media_folder_path;

        /** @var array                                  Blogpost file pathnames. */
        public $blogpost_file_pathnames;

        /** @var array                                  Media file pathnames. */
        public $media_pathnames;



        /**
         * Constructor
         *
         * @param Blogpost $blogposts                   An array of blogposts to export.
         * @param BlogTable $media_folder_path          The path of the blog media folder (blog/content/media).
         */
        public function __construct($blogposts, $media_folder_path)
        {
            $this->blogposts            = $blogposts;
            $this->media_folder_path    = $media_folder_path;
        }


        /**
         * Write the summary and content files for the specified blogpost
         *
         * @param Blogpost $blogpost                    The specified blogpost.
         * @param string $export_folder                 The path of the folder to write the files to. Note that the path should have a leading slash.
         * @return array                                The pathnames of the files created.
         */
        public function write_blogpost($blogpost, $export_folder)
        {
            $host                                   = raw_get_host();
            $root                                   = get_root_path();

            $blogpost_basename                      = $blogpost->permalink;
            $blogpost_basename                      = str_replace('/blog/', '', $blogpost_basename);
            $blogpost_basename                      = str_replace('/', '_', $blogpost_basename);

            $blogpost_summary_filename              = "$blogpost_basename.ini";
            $blogpost_summary_pathname              = "$export_folder/$blogpost_summary_filename";
            $blogpost_summary_full_pathname         = "$root/$blogpost_summary_pathname";

            $blogpost_contents_filename             = "$blogpost_basename.md";
            $blogpost_contents_pathname             = "$export_folder/$blogpost_contents_filename";
            $blogpost_contents_full_pathname        = "$root/$blogpost_contents_pathname";

            $media_path_prefix_original             = "/$this->media_folder_path/";
            $media_path_prefix_adjusted             = '';

            $blogpost->content                      = str_replace($media_path_prefix_original, $media_path_prefix_adjusted, $blogpost->content);
            $blogpost->thumbnail_filename           = str_replace($media_path_prefix_original, $media_path_prefix_adjusted, $blogpost->thumbnail_filename);

            $blogpost_summary                       = [];

            $blogpost_summary['title']              = $blogpost->title;
            $blogpost_summary['subtitle']           = $blogpost->subtitle;
            $blogpost_summary['author']             = $blogpost->author;
            $blogpost_summary['timestamp']          = $blogpost->timestamp;
            $blogpost_summary['draft']              = $blogpost->draft;
            $blogpost_summary['thumbnail_filename'] = $blogpost->thumbnail_filename;
            $blogpost_summary['thumbnail_caption']  = $blogpost->thumbnail_caption;
            $blogpost_summary['content_filename']   = $blogpost_contents_filename;
            $blogpost_summary['permalink']          = $host.$blogpost->permalink;


            unlink($blogpost_summary_full_pathname);
            unlink($blogpost_contents_full_pathname);

            // Write the metadata ini file
            write_ini_file($blogpost_summary_full_pathname, $blogpost_summary);

            // Write the markdown content file
            $fp = fopen($blogpost_contents_full_pathname, 'w');
            fwrite($fp, $blogpost->content);
            fclose($fp);

            return array($blogpost_summary_pathname, $blogpost_contents_pathname);
        }


        public function write_blogposts($export_folder)
        {
            $root                           = $_SERVER["DOCUMENT_ROOT"];

            $blogpost_file_pathnames        = [];

            foreach ($this->blogposts as $blogpost)
            {
                if (!$blogpost->deleted)
                {
                    $referenced_media_filenames = get_image_filenames_from_markdown($blogpost->content);

                    $pathnames = $this->write_blogpost($blogpost, $export_folder);

                    foreach ($pathnames as $pathname)
                    {
                        $blogpost_file_pathnames[] = $pathname;
                    }
                }
            }

            $this->blogpost_file_pathnames = $blogpost_file_pathnames;
        }


        /**
         * Create a zip archive of the exported blogpost files at the specified location.
         *
         * @param string $zip_file_pathname           The pathname of the zip file to create.
         */
        public function create_zip_archive($zip_file_pathname)
        {
            $root   = get_root_path();

            $zip    = new ZipArchive;

            $OK     = $zip->open($zip_file_pathname, ZipArchive::CREATE | ZipArchive::OVERWRITE);

            foreach ($this->blogpost_file_pathnames as $pathname)
            {
                $full_pathname = append_path($root, $pathname);

                $zip->addFile($full_pathname, basename($pathname) );
            }

            // Add support files.
            if (!empty($this->media_pathnames) )
            {
                foreach ($this->media_pathnames as $media_pathname)
                {
                    $media_filename = basename($media_pathname);

                    $extension = strtolower(pathinfo($media_pathname, PATHINFO_EXTENSION) );

                    if ( ($media_filename != '.') && ($media_filename != '..') && ($extension != 'txt') )
                    {
                        $media_file_zip_path = $media_pathname;

                        $media_file_full_pathname = "$root/$this->media_folder_path/$media_file_zip_path";

                        $zip->addFile($media_file_full_pathname, $media_file_zip_path);
                    }
                }
            }

            $zip->close();

            $this->cleanup();
        }


        /**
         * Cleanup any metadata or content files written to the export folder.
         *
         */
        private function cleanup()
        {
            foreach ($this->blogpost_file_pathnames as $pathname)
            {
                $full_pathname = append_path(get_root_path(), $pathname);

                unlink($full_pathname);
            }
        }

    }

?>