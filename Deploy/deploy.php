<?php
     
        $pathname = 'tdor_deploy.zip';
        
        $zip = new ZipArchive;
        if ($zip->open($pathname) === TRUE)
        {
            $zip->extractTo('.');
            $zip->close();

            echo "Extracted $pathname<br><br>";

            echo '<a href="index.php?controller=pages&action=home"><b>Homepage</b></a><br><br>';
            
            echo '<a href="index.php?controller=pages&action=rebuild"><b>Rebuild Database</b></a>';
        }
        else
        {
            echo "Failed to extract $pathname<br>";
        }


?>