<?php
     
        $pathname = 'tdor_deploy.zip';
        
        $zip = new ZipArchive;
        if ($zip->open($pathname) === TRUE)
        {
            $zip->extractTo('.');
            $zip->close();

            echo "Extracted $pathname<br><br>";

            echo '<a href="index.php?controller=pages&action=home"><b>Homepage</b></a><br><br>';
            
            echo '<a href="index.php?controller=pages&action=admin&target=rebuild"><b>Rebuild Database</b></a><br><br>';

            echo '<a href="index.php?controller=pages&action=admin&target=thumbnails"><b>Rebuild Thumbnails</b></a><br><br>';

            echo '<a href="index.php?controller=pages&action=admin&target=qrcodes"><b>Rebuild QR Codes</b></a><br><br>';

            echo '<a href="index.php?controller=pages&action=admin&target=geocode"><b>Geocode reports</b></a><br><br>';
        }
        else
        {
            echo "Failed to extract $pathname<br>";
        }


?>