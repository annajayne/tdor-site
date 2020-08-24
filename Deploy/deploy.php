<?php
    /**
     * Deployment script.
     *
     * If the configuration file CONFIG_FILE_PATH has been configured with a site deployment password,
     * a form will be displayed asking for the password. If entered correctly, the zipfile DEPLOY_ZIPFILE
     * will be extracted into the site home directory.
     */


    function show_menu()
    {
        echo '<p>&nbsp;</p>';

        echo '<p><a href="/"><b>Homepage</b></a></p>';

        echo '<p><a href="/pages/admin?target=users"><b>Administer Users</b></a></p>';

        echo '<p><a href="/pages/admin?target=cleanup"><b>Cleanup Data</b></a></p>';

        echo '<p><a href="/pages/admin?target=rebuild"><b>Rebuild Database</b></a></p>';

        echo '<p><a href="/pages/admin?target=thumbnails"><b>Rebuild Thumbnails</b><a></p>';

        echo '<p><a href="/pages/admin?target=qrcodes"><b>Rebuild QR Codes</b></a></p>';

        echo '<p><a href="/pages/admin?target=geocode"><b>Geocode reports</b></a></p';
    }


    function deploy($pathname)
    {
        $zip = new ZipArchive;
        if ($zip->open($pathname) === TRUE)
        {
            $zip->extractTo('.');
            $zip->close();

            return true;
        }
        return false;
    }



    define('CONFIG_FILE_PATH',                  '/config/tdor.ini');
    define('DEPLOY_ZIPFILE',                    'tdor_deploy.zip');

    $ini_file_pathname = $_SERVER['DOCUMENT_ROOT'].CONFIG_FILE_PATH;

    $site_password  = '';
    $deploy         = false;

    if (file_exists($ini_file_pathname) )
    {
        $config = parse_ini_file($ini_file_pathname, TRUE);

        if ($config)
        {
            $site_password = $config['Deploy']['password'];
        }
    }
?>


<html>
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Deploy</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css" />
    <style type="text/css">
        body
        {
            font: 14px sans-serif;
        }

        .success
        {
            color:green;
        }
        
        .error
        {
            color:red;
        }
        
        .wrapper
        {
            width: 350px;
            padding: 20px;
        }
        </style>
  </head>
  <body>
<?php

    echo '<div class="wrapper">';
    echo '<h3>Deploy Site</h3>';

    if (!empty($site_password) )
    {
        $show_form  = true;
        $error      = '';

        if (isset($_POST['password']) )
        {
            $password = $_POST["password"];

            if ($password == $site_password)
            {
                echo '<p>Deployment password entered correctly</p>';

                $deploy = true;

                $show_form = false;
            }
            else
            {
                $error = 'Incorrect deployment password entered.';
            }
        }


        if ($show_form)
        {
            echo '<p>Please enter the site deployment password to continue.</p>';
            echo '<p>&nbsp;</p>';

            echo '<form action="'.htmlspecialchars($_SERVER["PHP_SELF"]).'" class="form-horizontal" method="post">';
            echo   '<div class="form-group">';

            echo     '<label for="password" class="col-sm-4 control-label">Password:</label>';
            echo     '<div class="col-sm-8">';
            echo       '<input type="password" class="form-control" id="password" name="password">';

            if (!empty($error) )
            {
                echo       '<br><p class="error">'.$error.'</p>';
            }
            echo     '</div>';
            echo   '</div>';

            echo   '<div class="col-sm-offset-4 col-sm-8">';
            echo     '<input type="submit" class="btn btn-default" class="form-control" value="OK" >';
            echo   '</div>';

            echo '</form>';
        }
    }
    else
    {
        // If a config has not yet been set, deploy anyway
        $deploy = true;
    }

    if ($deploy)
    {
        $pathname = DEPLOY_ZIPFILE;

        if (deploy(pathname) )
        {
            echo "<p class='success'>Extracted $pathname</p>";
        }
        else
        {
            echo "<p class='error'>Failed to extract $pathname</p>";
        }

        show_menu();;
    }

    echo '</div>';
?>
  </body>
</html>
