<?php
     
        $pathname = 'tdor_deploy.zip';
        
        $zip = new ZipArchive;
        if ($zip->open($pathname) === TRUE)
        {
            $zip->extractTo('.');
            $zip->close();

            echo "Extracted $pathname<br>";
        }
        else
        {
            echo "Failed to extract $pathname<br>";
        }


?>