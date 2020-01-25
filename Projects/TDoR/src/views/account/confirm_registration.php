<?php
    /**
     * Confirm account registration page
     *
     */

    require_once('models/users.php');
    require_once('util/email_notifier.php');


    $confirmation_id = (isset($_GET['id']) ) ? $_GET['id'] : '';


    echo '<h2>Confirm Registration</h2>';
    
    $db             = new db_credentials();

    $users_table    = new Users($db);

    if (!empty($confirmation_id) )
    {
        // Lookup the ID in the users table to locate the corresponding user.
    
        // If found, display a confirmation message and clear the confirmation id in the databse.
        // Thereafter, the account can be activated by an admin.
        $user = $users_table->get_user_from_confirmation_id($confirmation_id);
        
        if (!empty($user->username) )
        {
            $user->confirmation_id = '';
            
            $users_table->update_user($user);
            
            $notifier = new EmailNotifier();

            if ($user->activated)
            {
                // If an admin pre-activated the account from the "Show Users" admin page, tell the user they can login now.
                $notifier->send_user_account_activated_confirmation($user);

                echo '<p>Your account has been activated. <a ref="/account/login">Click here to login</a>.</p>';
            }
            else
            {
                // Otherwise, let them know they'll have to wait until an admin activates their account.
                echo '<p><b>Your account registration has been confirmed.</b> You should receive an email once your account has been activated.</p>';
            }

            // Notify the admin that the user has confirmed
            $notifier->notify_admin_account_confirmed($user);
        }
        else
        {
            echo '<p>Sorry, something went wrong and your account registration could not be confirmed. Please <a href="/pages/contact">contact us</a> for assistance.</p>';
        }
    }

?>
