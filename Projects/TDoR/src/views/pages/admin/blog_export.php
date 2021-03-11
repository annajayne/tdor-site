<?php
    /**
     * "Export Blog" implementation.
     *
     */


    require_once('models/blogposts.php');
    require_once('util/blogposts_exporter.php');



    $db                             = new db_credentials();
    $blogposts_table                = new BlogPosts($db);

    $query_params                   = new BlogpostsQueryParams();

    $query_params->include_drafts   = true;
    $query_params->include_deleted  = true;

    $blogposts                      = $blogposts_table->get_all($query_params);


    $ip                             = $_SERVER['REMOTE_ADDR'].'_';

    if (strpos($ip, ':') !== false)
    {
        $ip = '';
    }

    $newline                        = "\n";

    $date                           = date("Y-m-d\TH_i_s");

    $basename                       = 'blog_export';
    $filename                       = $basename.'_'.$ip.$date;

    $root                           = $_SERVER["DOCUMENT_ROOT"];

    $blog_content_folder            = '/blog/content';

    $blog_media_folder              = "$blog_content_folder/media";
    $export_folder                  = '/data/export';

    $zip_file_pathname              = "$export_folder/$filename.zip";
    $zip_file_full_pathname         = "$root/$zip_file_pathname";

    $exporter                       = new BlogpostsExporter($blogposts);

    if (file_exists($root.$blog_media_folder) )
    {
        $exporter->media_pathnames = recursive_scandir($root.$blog_media_folder);
    }

    $exporter->write_blogposts($blog_content_folder);

    $exporter->create_zip_archive($zip_file_full_pathname);

    ob_clean();
    ob_end_flush(); // Needed as otherwise Windows will report the zipfile to be corrupted (see https://stackoverflow.com/questions/13528067/zip-archive-sent-by-php-is-corrupted/13528263#13528263)

    if (file_exists($zip_file_full_pathname) )
    {
        header("Content-Description: File Transfer");
        header("Content-Type: text/plain");
        header("Content-Disposition: attachment; filename=" . basename($zip_file_full_pathname) );

        readfile($zip_file_full_pathname);
    }

?>