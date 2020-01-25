<?php
    /**
     * Administrative command for viewing/administering user accounts.
     *
     */

    require_once('models/users.php');


    
    /**
     * Email notifier class.
     *
     */
    class EmailNotifier
    {
        /** @var string                     Host domain. */
        public  $host;

        /** @var string                     Admin email account. */
        public  $admin_email;


        /**
         * Constructor
         *
         * @param db_credentials $db        The credentials of the database.
         * @param array $table_name         The name of the table. The default is 'users'.
         */
        public function __construct()
        {
            $this->host         = raw_get_host();
            $this->admin_email  = NOTIFY_EMAIL_ADDRESS;
        }


        /**
         * Send an account registration confirmation link to a user.
         *
         * @param User $user                The user account in question.
         */
        public function send_user_account_registration_confirmation($user)
        {
            $confirmation_url   = $this->host.'/account/confirm_registration?id='.$user->confirmation_id;

            // When the user clicks the link, mark the account as "confirmed" and clear the confirmation key (but it still needs to be activated)
            // The confirmation page should inform the user that they will receive an email once the account has been activated.
            $subject            = 'Confirm your registration with '.$this->host;
            
            $html               = "<p>The user account <b>$user->username</b> ($user->email) has just been registered with <b><a href='$this->host'>$this->host</a></b>.</p>";
            $html              .= "<p><b><a href='$confirmation_url'>Click here to confirm your registration</a></b></p>";
            $html              .= "<p>If you did not create the user account, please ignore this message.</p>";

            send_email(SENDER_EMAIL_ADDRESS, $user->email, $subject, $html);

            return $confirmation_url;
        }


        /**
         * Notify the user that their account has been activated.
         *
         * @param User $user                The user account in question.
         */
        public function send_user_account_activated_confirmation($user)
        {
            $subject            = $this->host.' account activated';

            $html               = "<p>Your account <b>$user->username</b> with <b><a href='$this->host'>$this->host</a></b> has now been activated.</p>";
            $html              .= "<p><b><a href='$this->host/account'>Click here to login</a></b>.</p>";

            send_email(SENDER_EMAIL_ADDRESS, $user->email, $subject, $html);
        }


        /**
         * Notify the admin that a new user has been registered.
         *
         * @param User $user                The user account in question.
         */
        public function notify_admin_account_created($user)
        {
            $subject            = 'New user registered on '.$this->host;

            $html               = "<p>The user <b>$user->username</b> ($user->email) has just been created on <b><a href='$this->host'>$this->host</a></b>.</p>";
            $html              .= "<p><b><a href='$this->host/pages/admin?target=users'>Administer Users</a></b></p>";

            send_email(SENDER_EMAIL_ADDRESS, NOTIFY_EMAIL_ADDRESS, $subject, $html);
        }


        /**
         * Notify the admin that a new user has been confirmed.
         *
         * @param User $user                The user account in question.
         */
        public function notify_admin_account_confirmed($user)
        {
            $subject            = "User registeration confirmed on $this->host";
            
            $html               = "<p>The user <b>$user->username</b> ($user->email) has just been confirmed on <b><a href='$this->host'>$this->host</a></b>.</p>";
            $html              .= "<p><a href='$this->host/pages/admin?target=users'><b>Administer Users</b></a></p>";

            send_email(SENDER_EMAIL_ADDRESS, NOTIFY_EMAIL_ADDRESS, $subject, $html);
        }


    }


?>