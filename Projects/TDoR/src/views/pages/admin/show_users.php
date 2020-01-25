<?php
    /**
     * Administrative command for viewing/administering user accounts.
     *
     */

    require_once('models/users.php');
    require_once('util/email_notifier.php');



    function get_user_role_text($user, $role_name, $role_abbrev)
    {
        $text               = '';
        
        $base_url           = get_users_base_url()."&user=$user->username";
        
        $add_role_url       =  $base_url.'&operation=add_role';
        $remove_role_url    =  $base_url.'&operation=remove_role';

        $has_role           = (strstr($user->roles, $role_abbrev) !== FALSE);
        
        if ($has_role)
        {
            $url = "$remove_role_url:$role_name";

            $text .= "yes&nbsp;[<a href='$url'>no</a>]<br>";
        }
        else
        {
            $url = "$add_role_url:$role_name";

            $text .= "<span class='disabled_role'>no</span>&nbsp;[<a href='$url'>yes</a>]<br>";
        }
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

                    if (empty($user->confirmation_id) )
                    {
                        $notifier = new EmailNotifier();
    
                        $notifier->send_user_account_activated_confirmation($user);
                    }
                    redirect_to($base_url);
                    break;

                case 'deactivate':
                    $user->activated = 0;

                    $users_table->update_user($user);
                    
                    redirect_to($base_url);
                    break;

                case 'add_role:api':
                    if (strstr($user->roles, 'I') === FALSE)
                    {
                        $user->roles = 'I'.$user->roles;
                        $users_table->update_user($user);
                    }                   
                    redirect_to($base_url);
                    break;

                case 'remove_role:api':
                    if (strstr($user->roles, 'I') !== FALSE)
                    {
                        $user->roles = str_replace('I', '', $user->roles);
                        $users_table->update_user($user);
                    }                   
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
                    {
                        $user->roles = str_replace('E', '', $user->roles);
                        $users_table->update_user($user);
                    }                   
                    redirect_to($base_url);
                    break;

                case 'add_role:admin':
                    if (strstr($user->roles, 'A') === FALSE)
                    {
                        $user->roles = 'A'.$user->roles;
                        $users_table->update_user($user);
                    }                   
                    redirect_to($base_url);
                    break;

                case 'remove_role:admin':
                    if (strstr($user->roles, 'A') !== FALSE)
                    {
                        $user->roles = str_replace('A', '', $user->roles);
                        $users_table->update_user($user);
                    }                   
                    redirect_to($base_url);
                    break;

                case 'delete':
                    $users_table->delete_user($user);

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

        echo '<h2>Administer Users</h2><br>';
        
        echo '<table style="overflow-x:auto;">';
        echo   '<tr>';
        echo     '<th>User</th>';
        echo     '<th>Email</th>';
        echo     '<th>Confirmed?</th>';
        echo     '<th>Active?</th>';
        echo     '<th>API?</th>';
        echo     '<th>Editor?</th>';
        echo     '<th>Admin?</th>';
        echo     '<th>Created</th>';
        echo     '<th/>';
        echo   '</tr>';
        
        foreach ($users as $user)
        {
            $api_role_text      = get_user_role_text($user, 'api',    'I');
            $editor_role_text   = get_user_role_text($user, 'editor', 'E');
            $admin_role_text    = get_user_role_text($user, 'admin',  'A');
            
            $confirmed_text     = empty($user->confirmation_id) ? 'yes' : 'no';

            $activated_text     = get_user_activated_text($user);

            $delete_url         = "$base_url&user=$user->username&operation=delete";
            $delete_link        = "<a onClick=\"javascript: return confirm('Delete user $user->username?');\" href='$delete_url'>Delete</a>";

            $activate_link      = '';
            
            if (!$user->activated)
            {
                $url = "$base_url&user=$user->username&operation=activate";

                $activate_link = "<a href='$url'>yes</a>";
            }
            else
            {
                $url = "$base_url&user=$user->username&operation=deactivate";

                $activate_link = "<a href='$url'>no</a>";
            }
            
            echo '<tr style="white-space: nowrap;">';
            echo   "<td>$user->username</td>";
            echo   "<td><a href='mailto:$user->email'>$user->email</a></td>";
            echo   "<td align='center'>$confirmed_text</td>";
            echo   "<td align='center'>$activated_text&nbsp;[$activate_link]</td>";
            echo   "<td>$api_role_text</td>";
            echo   "<td>$editor_role_text</td>";
            echo   "<td>$admin_role_text</td>";
            echo   "<td>$user->created_at</td>";
            echo   "<td>[$delete_link]</td>";
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
