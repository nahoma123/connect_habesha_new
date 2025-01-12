<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="<?php echo eps_language_dir(); ?>" lang="<?php echo str_replace('_', '-', osc_current_user_locale()); ?>">
<head>
  <?php osc_current_web_theme_path('head.php'); ?>
  <meta name="robots" content="noindex, nofollow" />
  <meta name="googlebot" content="noindex, nofollow" />
  <script type="text/javascript" src="<?php echo osc_current_web_theme_js_url('jquery.validate.min.js'); ?>"></script>
</head>





<body id="user-recover" class="pre-account recover">
  <?php UserForm::js_validation(); ?>
  <?php osc_current_web_theme_path('header.php'); ?>

<?php
// Database connection details
define('DB_HOST', 'localhost');
define('DB_USER', 'u609444707_6txkv');
define('DB_PASSWORD', '[t5>:XctG');
define('DB_NAME', 'u609444707_NvDH4');
define('DB_TABLE_PREFIX', 'osxw_');

// Create a connection
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Check if phone number is provided
if (isset($_POST['phone'])) {
    $phone = trim($_POST['phone']);

    // Prepare the SQL query
    $stmt = $mysqli->prepare("SELECT s_email FROM " . DB_TABLE_PREFIX . "t_user WHERE s_phone_mobile = ?");
    $stmt->bind_param('s', $phone);
    $stmt->execute();
    $stmt->bind_result($email);
    $stmt->fetch();

    if ($email) {
        echo json_encode(['email' => $email]);
    } else {
        echo json_encode(['email' => '']);
    }

    $stmt->close();
}

// Close the connection
$mysqli->close();
?>


  <section class="container">
    <div class="box">
      <h1><?php _e('Reset password', 'epsilon'); ?></h1>
      
      <a class="alt-action" href="<?php echo osc_user_login_url(); ?>"><?php _e('You already know password? Login to your account', 'epsilon'); ?> &#8594;</a>

      <form action="<?php echo osc_base_url(true) ; ?>" method="post" >
        <input type="hidden" name="page" value="login" />
        <input type="hidden" name="action" value="recover_post" />
        <div class="row">
          <label for="email"><?php _e('Phone', 'epsilon') ; ?></label> 
          <span class="input-box">
              <input id="s_phone_mobile" type="text" name="s_phone_mobile" value="" autocomplete="off" required="true" class="valid">
          </span>
        </div>
        
        <div class="row" style="display:none;">
          <label for="email"><?php _e('E-mail', 'epsilon') ; ?></label> 
          <span class="input-box"><?php UserForm::email_text(); ?></span>
        </div>
        
        <?php osc_run_hook('user_recover_form'); ?>
        
        <?php eps_show_recaptcha('recover_password'); ?>

        <button type="submit" class="btn"><?php _e('Send a new password', 'epsilon') ; ?></button>
        
        <a class="alt-action2" href="<?php echo osc_register_account_url(); ?>"><?php _e('Create a new account', 'epsilon'); ?></a>
      </form>
    </div>
  </div>

  <?php osc_current_web_theme_path('footer.php'); ?>
  
  


  
  <script type="text/javascript">
    $(document).ready(function(){
      $('input[name="s_email"]').attr('placeholder', '<?php echo osc_esc_js(__('your.email@dot.com', 'epsilon')); ?>').attr('required', true).prop('type', 'email');
    });
    
    
    
document.getElementById('s_phone_mobile').addEventListener('keyup', function () {
    const phone = this.value.trim();

    if (phone.length > 0) {
        fetch('fetch_email.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `phone=${encodeURIComponent(phone)}`,
        })
            .then(response => response.json())
            .then(data => {
                if (data.email) {
                    document.querySelector('[name="s_email"]').value = data.email;
                } else {
                    document.querySelector('[name="s_email"]').value = '';
                }
            })
            .catch(error => console.error('Error fetching email:', error));
    } else {
        document.querySelector('[name="s_email"]').value = '';
    }
});




  </script>
</body>
</html>