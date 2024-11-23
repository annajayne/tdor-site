<?php
    /**
     * Contact page
     *
     */

    require_once('util/utils.php');





    /**
     * Class to hold contact form details
     *
     */
    class ContactFormParms
    {
        /** @var string                  Name. */
        public $name = '';

        /** @var string                  Email address. */
        public $email = '';

        /** @var string                  Organisation. */
        public $organisation = '';

        /** @var string                  Subject. */
        public $subject = '';

        /** @var string                  Message. */
        public $message = '';
    }



    /**
     * Display the contact form
     *
     * @param string $params            Parameters to prefill the form
     * @param string $site_config       The site configuration
     * @param boolean $captcha_err      true if the "Please complete the captcha below before sending." message should be displayed
     */
    function contact_form($params, $site_config, $captcha_err = false)
    {
        $recaptcha_site_key = $site_config['reCaptcha']['site_key'];

        $form_url           = '/pages/contact?confirm=1';

        $subject_prompts    = array(CONTACT_SUBJECT_GENERAL =>          '',
                                    CONTACT_SUBJECT_MEDIA =>            '',
                                    CONTACT_SUBJECT_RESOURCES =>        'If you are looking for particular resources, please let us know. Although the site already provides memorial cards and a slideshow, we may be able to help in other ways.',
                                    CONTACT_SUBJECT_REPORT_DETAILS =>   'We try to make all reports as complete as reasonably possible, but it is all too easy to miss something. If you need to submit a correction please do not forget to include the URL of the report you are submitting a correction for.',
                                    CONTACT_SUBJECT_NEW_REPORT =>       'To add a new report, we need only basic details (e.g. name, location, approx date and a summary of what happened), but the more information you can give the more complete the resulting page will be. Remember to include links where possible and tell us if you want to be credited.',
                                    CONTACT_SUBJECT_HELPING_OUT =>      'Thank you for offering to help out. Please tell us how you would like to help, and a little bit about yourself - including any language, research or programming etc. skills you think might be relevant. Remember to include any contact details/social media handles you think appropriate.',
                                    CONTACT_SUBJECT_SOMETHING_ELSE =>   '');
?>
        <script>
            function on_subject_changed()
            {
                const subjectEl = document.getElementById('subject');
                const textareaEl = document.querySelector('textarea');

                var placeholder = '';

                try
                {
                    const dict =
                    {
<?php
                        foreach ($subject_prompts as $subject => $prompt)
                        {
                            echo "'$subject': '$prompt',\n";
                        }
?>
                    }

                    placeholder = dict[subjectEl.value];
                }
                catch (e)
                {
                }

                if (typeof placeholder === 'undefined')
                {
                    placeholder = '';
                }

                textareaEl.placeholder = placeholder;
            }
        </script>


        <h2>Contact Us</h2>

        <div style="grid_12">
          <p>Please feel free to contact us by email via the form below if you would like to contact us, help out, notify us of a correction or send us additional information relating to a report presented on this site (or indeed tell us about someone we've lost who isn't yet listed).</p>

          <p>Alternatively you can also send a direct email to <a href="mailto:tdor@translivesmatter.info"><b>tdor@translivesmatter.info</b></a>, tweet or send a direct message to <b>TDoRInfo</b> on <a href="https://bsky.app/profile/tdorinfo.bsky.social" target="_blank" rel="noopener">BlueSky</a> or <a href="https://twitter.com/tdorinfo" target="_blank" rel="noopener">Twitter</a>. Links to relevant news reports can also be posted to <a href="https://www.facebook.com/groups/1570448163283501/" target="_blank" rel="noopener"><b>Trans Violence News</b></a> (a private group, membership of which requires admin approval) on Facebook.</p>


          <?php
            if ($captcha_err)
            {
                echo '<p style="color:#F00000"><b>Please complete the captcha below before sending.</b></p>';
            }
          ?>
          <form name="contact_form" action="<?php echo $form_url; ?>" method="POST" enctype="multipart/form-data" >

            <p>
              Please tell us your name: <span class="smallertext"><sup>*</sup></span><br>
              <input type="text" name="name" required size="78" value="<?php echo $params->name; ?>" /><br />
            </p>

            <p>
              Please enter your email address: <span class="smallertext"><sup>*</sup></span><br>
              <input type="text" name="email" required size="78" value="<?php echo $params->email; ?>" /><br>
            </p>

            <p>
              Please enter the name of your organisation (if applicable):<br>
              <input type="text" name="organisation" size="78" value="<?php echo $params->organisation; ?>" /><br />
            </p>

            <p>
              What would you like to ask or tell us about? <span class="smallertext"><sup>*</sup></span><br>
              <input type="text" list="subjects" name="subject" id="subject" required size="78" value="<?php echo $params->subject; ?>" onkeyup="javascript: on_subject_changed()" /><br />
              <datalist id="subjects">
                <option value="<?php echo CONTACT_SUBJECT_GENERAL; ?>" />
                <option value="<?php echo CONTACT_SUBJECT_MEDIA; ?>" />
                <option value="<?php echo CONTACT_SUBJECT_RESOURCES; ?>" />
                <option value="<?php echo CONTACT_SUBJECT_REPORT_DETAILS; ?>" />
                <option value="<?php echo CONTACT_SUBJECT_NEW_REPORT; ?>" />
                <option value="<?php echo CONTACT_SUBJECT_HELPING_OUT; ?>" />
                <option value="<?php echo CONTACT_SUBJECT_SOMETHING_ELSE; ?>" />
              </datalist>
            </p>

            <div style="display:none;">
              <p>
                Website *<br>
                <input type="text" name="website" size="78"><br />
              </p>
            </div>

            <p>
              Please type your message below: <span class="smallertext"><sup>*</sup></span><br>
              <textarea name="message" required rows="20" cols="80"><?php echo $params->message; ?></textarea>
            </p>

            <p class="smallertext"><sup>*</sup> required fields</p>

            <div class="g-recaptcha" data-sitekey="<?php echo $recaptcha_site_key; ?>"></div>
            <div align="left">
              <input type="submit" name="submit" class="button-blue" value="Send" />
            </div>
          </form>

          <!--- Preload the placeholder text for the body -->
          <script>on_subject_changed();</script>
        </div>
<?php
    }


    function contact_confirmation($probable_spam)
    {
        // Display a confirmation and send the message. DONT show the form again.
        $name           = $_POST["name"];
        $email          = $_POST["email"];
        $organisation   = $_POST["organisation"];
        $subject        = $_POST["subject"];
        $message        = $_POST["message"];

        $full_message   = "Enquiry via https://tdor.translivesmatter.info/pages/contact from: ".$name." (".$email.")\r\n\r\nOrganisation: ".$organisation."\r\n\r\n".$message;

        $dest_email     = 'tdor@translivesmatter.info';

        $headers        = "From: $email\r\nReply-To: $email\r\n";

        $sentOK         = false;

        if (!$probable_spam)
        {
            $headers   .= "Cc: $email\r\n";

            $sentOK     = mail($dest_email,
                               $subject,
                               $full_message,
                               $headers);
        }

        if ($sentOK === TRUE)
        {
            echo '<h2>Message sent</h2>';
            echo '<br><br><p>Thank you for writing to us. We will get back to you as soon as we can.</p>';
        }
        else
        {
            echo '<h2>Message could not be sent</h2>';
            echo '<p>Sorry - your message could not be sent. Please let us know this happened by writing to us directly at <a href="mailto:tdor@translivesmatter.info">tdor@translivesmatter.info</a>.</p>';
            echo '<p>A copy of your message is given below for convenience.</p>';
        }

        $display_message = str_replace("\r\n", "<br />", htmlspecialchars($message) );

        echo '<table border="0" class="contact_confirmation" width="90%" cellpadding="10">';
        echo   "<tr><th width='150' align='left'>From:</th><td>".$name." (".$email.")</td></tr>";
        echo   "<tr><th align='left'>Organisation:</th><td>".$organisation."</td></tr>";
        echo   "<tr><th align='left'>Subject:</th><td>".$subject."</td></tr>";
        echo   "<tr><th align='left' valign='top'>Message:</th><td>".$display_message."</td></tr>";
        echo '</table>';

        $params = 'name='.urlencode($name);
        $params .= '&email='.urlencode($email);
        $params .= '&organisation='.urlencode($organisation);
        $params .= '&subject='.urlencode($subject);
        $params .= '&message='.urlencode($message);

        echo "<br><p><a href='/pages/contact?$params'>Oops! I forgot to mention something...</a></p>";
    }



    echo '<script src="https://www.google.com/recaptcha/api.js" async defer></script>';

    $site_config = get_config();

    if (isset($_POST['submit']) )
    {
        $captcha_response   = $_POST['g-recaptcha-response'];

        $captcha_ok         = !empty($captcha_response);

        if ($captcha_ok)
        {
            // Verify the captcha - see https://www.kaplankomputing.com/blog/tutorials/recaptcha-php-demo-tutorial/
            $secret_key = $site_config['reCaptcha']['secret_key'];

            $captcha_ok = verify_recaptcha_v2($captcha_response, $secret_key);
        }

        if ($captcha_ok)
        {
            // Display a confirmation and log/send the message.
            //
            // Note that the 'website' field is a honeypot. Only bots are likely to fill in this field, so we mark those as possible spam.
            // ref: https://www.gravityforms.com/rip-captcha/
            $probable_spam = !empty($_POST["website"]) ? true : false;

            contact_confirmation($probable_spam);
        }
        else
        {
            // If the captcha failed, redisplay the contact form.
            $params = new ContactFormParms();

            $params->name           = $_POST["name"];
            $params->email          = $_POST["email"];
            $params->organisation   = $_POST["organisation"];
            $params->subject        = $_POST["subject"];
            $params->message        = $_POST["message"];

            contact_form($params, $site_config, !$captcha_ok);
        }
    }
    else
    {
        $params = new ContactFormParms();

        $params->name               = isset($_GET['name'])          ? urldecode($_GET['name']) : '';
        $params->email              = isset($_GET['email'])         ? urldecode($_GET['email']) : '';
        $params->organisation       = isset($_GET['organisation'])  ? urldecode($_GET['organisation']) : '';
        $params->subject            = isset($_GET['subject'])       ? urldecode($_GET['subject']) : '';
        $params->message            = isset($_GET['message'])       ? urldecode($_GET['message']) : '';

        contact_form($params, $site_config);
    }
?>
