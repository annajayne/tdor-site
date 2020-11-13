<?php
    /**
     * Administrative command for viewing/administering user accounts.
     *
     */

    require_once('utils.php');
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
            $confirm        = '';

            if ($_SESSION['username'] == $user->username)
            {
                // This is the current user (an admin) - warn them!
                $confirm = "onClick=\"javascript: return confirm('Remove the $role_name role from $user->username?\\n\\nWARNING: This is your OWN account!!');\" ";
            }

            $url = "$remove_role_url:$role_name";

            $text .= "yes&nbsp;[<a $confirm href='$url'>no</a>]<br>";
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


    function add_user_role($users_table, $user, $role)
    {
        $user->roles = $role.$user->roles;

        $users_table->update_user($user);

        if ($_SESSION['username'] == $user->username)
        {
            // If this is the logged in user, update the session
            $_SESSION['roles'] = $user->roles;
        }
    }


    function remove_user_role($users_table, $user, $role)
    {
        $user->roles = str_replace($role, '', $user->roles);

        $users_table->update_user($user);

        if ($_SESSION['username'] == $user->username)
        {
            // If this is the logged in user, update the session
            $_SESSION['roles'] = $user->roles;
        }
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
                        add_user_role($users_table, $user, 'I');
                    }
                    redirect_to($base_url);
                    break;

                case 'remove_role:api':
                    if (strstr($user->roles, 'I') !== FALSE)
                    {
                        remove_user_role($users_table, $user, 'I');
                    }
                    redirect_to($base_url);
                    break;

                case 'add_role:editor':
                    if (strstr($user->roles, 'E') === FALSE)
                    {
                        add_user_role($users_table, $user, 'E');
                    }
                    redirect_to($base_url);
                    break;

                case 'remove_role:editor':
                    if (strstr($user->roles, 'E') !== FALSE)
                    {
                        remove_user_role($users_table, $user, 'E');
                    }
                    redirect_to($base_url);
                    break;

                case 'add_role:admin':
                    if (strstr($user->roles, 'A') === FALSE)
                    {
                        add_user_role($users_table, $user, 'A');
                    }
                    redirect_to($base_url);
                    break;

                case 'remove_role:admin':
                    if (strstr($user->roles, 'A') !== FALSE)
                    {
                        remove_user_role($users_table, $user, 'A');
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

        echo '<table style="overflow-x:auto; font-size: 0.8em;" cellpadding="5" border="1">';
        echo   '<tr>';
        echo     '<th>User</th>';
        echo     '<th>Email</th>';
        echo     '<th>Confirmed?</th>';
        echo     '<th>Active?</th>';
        echo     '<th>API?</th>';
        echo     '<th>Editor?</th>';
        echo     '<th>Admin?</th>';
        echo     '<th>Created</th>';
        echo     '<th>Last login</th>';
        echo     '<th/>';
        echo   '</tr>';

        foreach ($users as $user)
        {
            $api_role_text          = get_user_role_text($user, 'api',    'I');
            $editor_role_text       = get_user_role_text($user, 'editor', 'E');
            $admin_role_text        = get_user_role_text($user, 'admin',  'A');

            $confirmed_text         = empty($user->confirmation_id) ? 'yes' : 'no';

            $activated_text         = get_user_activated_text($user);

            $delete_warning         = "Delete user $user->username?";

            if ($_SESSION['username'] == $user->username)
            {
                // This is the current user (an admin) - warn them!
                $delete_warning    .= '\\n\\nWARNING: This is your OWN account!!';
            }

            $delete_url             = "$base_url&user=$user->username&operation=delete";
            $delete_link            = "<a onClick=\"javascript: return confirm('$delete_warning');\" href='$delete_url'>Delete</a>";

            $activate_link          = '';

            if (!$user->activated)
            {
                $url = "$base_url&user=$user->username&operation=activate";

                $activate_link = "<a href='$url'>yes</a>";
            }
            else
            {
                $confirm            = '';

                if ($_SESSION['username'] == $user->username)
                {
                    // This is the current user (an admin) - warn them!
                    $confirm        = "onClick=\"javascript: return confirm('Deactivate the account $user->username?\\n\\nWARNING: This is your OWN account!!');\" ";
                }

                $url                = "$base_url&user=$user->username&operation=deactivate";

                $activate_link      = "<a $confirm href='$url'>no</a>";
            }

            echo '<tr style="white-space: nowrap;">';
            echo   "<td>$user->username</td>";

            echo   '<td>';

            if ($_SESSION['username'] == $user->username)
            {
                // This is the current user - display without a link
                echo   $user->email;
            }
            else
            {
                // Otherwise display a mailto link
                echo   "<a href='mailto:$user->email'>$user->email</a>";
            }

            echo   '</td>';

            echo   "<td align='center'>$confirmed_text</td>";
            echo   "<td align='center'>$activated_text&nbsp;[$activate_link]</td>";
            echo   "<td align='center'>$api_role_text</td>";
            echo   "<td align='center'>$editor_role_text</td>";
            echo   "<td align='center'>$admin_role_text</td>";
            echo   "<td>$user->created_at</td>";
            echo   "<td>$user->last_login</td>";
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
