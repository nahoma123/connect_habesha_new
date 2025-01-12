<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="<?php echo eps_language_dir(); ?>" lang="<?php echo str_replace('_', '-', osc_current_user_locale()); ?>">
<head>
  <?php osc_current_web_theme_path('head.php'); ?>
  <meta name="robots" content="noindex, nofollow" />
  <meta name="googlebot" content="noindex, nofollow" />
  <script type="text/javascript" src="<?php echo osc_current_web_theme_js_url('jquery.validate.min.js'); ?>"></script>
  <style>
.input-box input {
  padding-right: 40px; /* Add space for the icon */
}

.toggle-password {
  font-size: 18px;
  color: #333;
  user-select: none;
}
</style>
</head>

<body id="user-forgot" class="pre-account forgot">
  <?php UserForm::js_validation(); ?>
  <?php osc_current_web_theme_path('header.php'); ?>

  <section class="container">
    <div class="box">
      <h1><?php _e('Change password', 'epsilon'); ?></h1>

      <a class="alt-action" href="<?php echo osc_user_login_url(); ?>"><?php _e('You already know password? Login to your account', 'epsilon'); ?> &#8594;</a>

      <form action="<?php echo osc_base_url(true); ?>" method="post">
        <input type="hidden" name="page" value="login" />
        <input type="hidden" name="action" value="forgot_post" />
        <input type="hidden" name="userId" value="<?php echo osc_esc_html(Params::getParam('userId')); ?>" />
        <input type="hidden" name="code" value="<?php echo osc_esc_html(Params::getParam('code')); ?>" />
        
     <div class="row">
  <label for="new_password"><?php _e('New password', 'epsilon'); ?></label>
  <div class="input-box" style="position: relative;">
    <input type="password" id="new_password" name="new_password" value="" style="width: 100%; padding-right: 40px;" />
    <span class="toggle-password" onclick="togglePassword('new_password')" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;">
      👁️
    </span>
  </div>
</div>

<div class="row">
  <label for="repeat_password"><?php _e('Repeat password', 'epsilon'); ?></label>
  <div class="input-box" style="position: relative;">
    <input type="password" id="repeat_password" name="new_password2" value="" style="width: 100%; padding-right: 40px;" />
    <span class="toggle-password" onclick="togglePassword('repeat_password')" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;">
      👁️
    </span>
  </div>
</div>
        <?php osc_run_hook('user_forgot_password_form'); ?>

        <button type="submit" class="btn"><?php _e('Submit', 'epsilon'); ?></button>
        
        <a class="alt-action2" href="<?php echo osc_register_account_url(); ?>"><?php _e('Create a new account', 'epsilon'); ?></a>
      </form>
    </div>
  </div>

  <?php osc_current_web_theme_path('footer.php') ; ?>
  
<script>
function togglePassword(inputId) {
  const input = document.getElementById(inputId);
  const icon = input.nextElementSibling;

  if (input.type === 'password') {
    input.type = 'text';
    icon.textContent = '🙈'; // Change icon to indicate hiding
  } else {
    input.type = 'password';
    icon.textContent = '👁️'; // Change icon to indicate showing
  }
}
</script>

</body>
</html>