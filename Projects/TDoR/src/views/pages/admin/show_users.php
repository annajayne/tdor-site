<?php
    /**
     * Administrative command for viewing/administering user accounts.
     *
     */

    require_once('models/users.php');



    function get_user_role_text($user)
    {
        $text               ='';
        
        $base_url           = get_users_base_url()."&user=$user->username";
        
        $add_role_url       =  $base_url.'&operation=add_role';
        $remove_role_url    =  $base_url.'&operation=remove_role';

        $is_editor = false;
        $is_admin = false;
        
        $is_editor  = (strstr($user->roles, 'E') !== FALSE);
        $is_admin   = (strstr($user->roles, 'A') !== FALSE);
        
        if ($is_editor)
        {
            $url = "$remove_role_url:editor";

            $text = "Editor [<a href='$url'>Remove</a>]<br>";
        }
        else
        {
            $url = "$add_role_url:editor";

            $text = "<span class='disabled_role'>Editor</span> [<a href='$url'>Add</a>]<br>";
        }

        if ($is_admin)
        {
            $url = "$remove_role_url:admin";

            $text .= "Admin [<a href='$url'>Remove</a>]<br>";
        }
        else
        {
            $url = "$add_role_url:admin";

            $text = "<span class='disabled_role'>Admin</span> [<a href='$url'>Add</a>]<br>";
        }
    
        //$text = trim($text, '; ');
        
        return $text;
    }
    

    function get_user_activated_text($user)
    {
        return $user->activated ? 'yes' : '<span class="disabled_role">no</span>';
    }
    
    
    function get_users_base_url()
    {
        return '/pages/admin?target=users';
    }


    function do_user_operation($username, $operation)
    {
        $db             = new db_credentials();
        $users_table    = new Users($db);
           
        $base_url       =  get_users_base_url();

        $user           = $users_table->get_user($username);
        
        if (!empty($user->username) )
        {
            switch ($operation)
            {
                case 'activate':
                    $user->activated = 1;

                    $users_table->update_user($user);
                    
                    redirect_to($base_url);
                    break;

                case 'deactivate':
                    $user->activated = 0;

                    $users_table->update_user($user);
                    
                    redirect_to($base_url);
                    break;

                case 'add_role:editor':
                    if (strstr($user->roles, 'E') === FALSE)
                    {
                        $user->roles = 'E'.$user->roles;
                        $users_table->update_user($user);
                    }                   
                    redirect_to($base_url);
                    break;

                case 'remove_role:editor':
                    if (strstr($user->roles, 'E') !== FALSE)
                    if (strstr($user->roles, 'E') !== FALSE)
                    {
                        $user->roles = str_replace('E', '', $user->roles);
                        $users_table->update_user($user);
                    }                   
                    redirect_to($base_url);
                    break;

                default:
                    echo "ERROR: Unsupported operation '$operation' on user $user->username<br>";
                    break;
            }
        }
    }
    
    
    function do_show_users($users)
    {
        $base_url =  get_users_base_url();

        echo '<h3>Administer Users</h3><br>';
        
        echo '<table>';
        echo   '<tr>';
        echo     '<th>User Name</th>';
        echo     '<th>Active?</th>';
        echo     '<th>Roles</th>';
        echo     '<th>Created</th>';
        echo     '<th></th>';
        echo   '</tr>';
        
        foreach ($users as $user)
        {
            $roles          = get_user_role_text($user);
            $activated_text = get_user_activated_text($user);

            $activate_link = '';
            
            if (!$user->activated)
            {
                $url = "$base_url&user=$user->username&operation=activate";

                $activate_link = "<a href='$url'>Activate</a>";
            }
            else
            {
                $url = "$base_url&user=$user->username&operation=deactivate";

                $activate_link = "<a href='$url'>Deactivate</a>";
            }
            
            echo '<tr>';
            echo   "<td>$user->username</td>";
            echo   "<td align='center'>$activated_text</td>";
            echo   "<td>$roles</td>";
            echo   "<td>$user->created_at</td>";
            echo   "<td>$activate_link</td>";
            echo '</tr>';
        }
    
        echo '</table>';
        echo '<p>&nbsp;</p>';
    }
    
    
    function show_users()
    {
        $user_param         = isset($_GET['user'])      ? $_GET['user']         : '';
        $operation_param    = isset($_GET['operation']) ? $_GET['operation']    : '';
        
        if (!empty($user_param) && !empty($operation_param) )
        {
            do_user_operation($user_param, $operation_param);
        }
        else
        {
            $db             = new db_credentials();
            $users_table    = new Users($db);
            
            $users          = $users_table->get_all();
            
            do_show_users($users);
        }        
    }

?>