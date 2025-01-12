<?php
  $user_id = osc_logged_user_id();
  $user_name = osc_logged_user_name();

  if(osc_is_web_user_logged_in()) {
    $active_chat_id = isset($_COOKIE['ocActiveChat']) ? $_COOKIE['ocActiveChat'] : '';
  } else {
    $active_chat_id = '';
  }

  $chat_open = isset($_COOKIE['ocChatOpened']) ? $_COOKIE['ocChatOpened'] : 0;

  $chats = ModelOC::newInstance()->getAllChats($user_id);
  
  // WE HAVE LIST OF ALL MESSAGES, GET LIST OF ACTIVE CHAT IDs
  $active_chats = array_column($chats, 'pk_i_chat_id');
  $active_chats = array_unique($active_chats);

  // CHECK IF ACTIVE WINDOW STILL EXITS
  if(!in_array($active_chat_id, $active_chats)) {
    $active_chat_id = '';
  }

  $bans = ModelOC::newInstance()->getUserBans();

  // CHECK IF THERE IS UNREAD
  $unread_check = 0;
  foreach($chats as $c) {
    if($c['i_to_user_id'] == osc_logged_user_id() && $c['i_read'] == 0) {
      $unread_check = 1;
      break;
    }
  }
  
  $active_limit = osc_get_preference('refresh_user', 'plugin-online_chat');
  $limit_datetime = date('Y-m-d H:i:s', strtotime(' -' . $active_limit . ' seconds', time()));
?>


<div id="oc-chat" class="oc-chat<?php if($chat_open <> 1) { ?> oc-closed<?php } else { ?> oc-open<?php } ?>">
  <input type="hidden" name="ocLastCheck" value="<?php echo osc_esc_html(date('Y-m-d H:i:s')); ?>"/>

  <div class="oc-global-head<?php if($unread_check == 1) { ?> oc-g-unread<?php } ?>">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 512 512"><path d="M448 0H64C28.7 0 0 28.7 0 64v288c0 35.3 28.7 64 64 64h96v84c0 7.1 5.8 12 12 12 2.4 0 4.9-.7 7.1-2.4L304 416h144c35.3 0 64-28.7 64-64V64c0-35.3-28.7-64-64-64zm16 352c0 8.8-7.2 16-16 16H288l-12.8 9.6L208 428v-60H64c-8.8 0-16-7.2-16-16V64c0-8.8 7.2-16 16-16h384c8.8 0 16 7.2 16 16v288zm-96-216H144c-8.8 0-16 7.2-16 16v16c0 8.8 7.2 16 16 16h224c8.8 0 16-7.2 16-16v-16c0-8.8-7.2-16-16-16zm-96 96H144c-8.8 0-16 7.2-16 16v16c0 8.8 7.2 16 16 16h128c8.8 0 16-7.2 16-16v-16c0-8.8-7.2-16-16-16z"/></svg>

    <span><?php _e('Chat', 'online_chat'); ?></span>

    <?php if(osc_is_web_user_logged_in()) { ?>
      <span class="oc-alt"><?php _e('New message!', 'online_chat'); ?></span>
    <?php } ?>

    <i class="fa fa-times oc-dir"></i>

    <?php if(osc_is_web_user_logged_in()) { ?>
      <i class="fa fa-gear oc-gear"></i>
    <?php } ?>
  </div>


  <?php if(!osc_is_web_user_logged_in()) { ?>
    <div class="oc-not-logged">
      <i class="fa fa-sign-in"></i>
      <span><?php _e('Login to start chatting', 'online_chat'); ?></span>
      <a href="<?php echo osc_user_login_url(); ?>"><?php _e('Sign in', 'online_chat'); ?></a>
    </div>
  <?php } else { ?>
    <div class="oc-chat-in<?php if($active_chat_id <> '' && $active_chat_id > 0) { ?> oc-on<?php } ?>">

      <div class="oc-bans">
        <div class="oc-bans-head">
          <div><i class="fa fa-angle-left oc-back-bans"></i><?php _e('Blocked users', 'online_chat'); ?></div>

          <?php if(!oc_check_bans_all()) { ?>
            <a href="#" class="oc-ban-all"><?php _e('Block all', 'online_chat'); ?></a>
          <?php } else { ?>
            <a href="#" class="oc-ban-all oc-active"><?php _e('Cancel full block', 'online_chat'); ?></a>
          <?php } ?>
        </div>

        <div class="oc-bans-ins">
          <?php if(count($bans) > 0) { ?>
            <?php foreach($bans as $b) { ?>
              <div class="oc-ban-row" data-user-id="<?php echo $b['i_block_user_id']; ?>">
                <div class="oc-ban-img"><img src="<?php echo oc_get_picture($b['i_block_user_id']); ?>"/></div>
                <div class="oc-ban-user"><?php echo $b['s_name']; ?></div>
                <i class="fa fa-trash oc-ban-cancel"></i>
              </div>
            <?php } ?>
          <?php } ?>

          <div class="oc-ban-empty"><?php _e('You do not block any users', 'online_chat'); ?></div>
        </div>
      </div>


      
      <div class="oc-chat-thread-placeholder" style="display:none;">

        <!-- PLACEHOLDER FOR NEW CHATS -->

        <div class="oc-chat-thread oc-offline" data-chat-id="-1">
          <div class="oc-started">
            <div class="oc-head">
              <i class="fa fa-angle-left oc-back"></i>
              <div class="oc-img-wrap">
                <img src="<?php echo oc_get_picture(0); ?>"/>
              </div>
              <span>
                <strong>
                  <span></span>
                  <i class="oc-check" style="display:none;"></i>
                </strong>
                <em><i class="fa fa-angle-right"></i></em>
              </span>

              <i class="fa fa-trash oc-close"></i>
              <i class="fa fa-gear oc-options"></i>

              <div class="oc-options-list">
                <i class="fa fa-gear oc-opt-ico"></i>
                <div data-options-action="1" class="oc-opt"><?php _e('Block this user', 'online_chat'); ?></div>
                <div data-options-action="2" class="oc-opt"><?php _e('Mail me chat transcript', 'online_chat'); ?></div>
                <i class="fa fa-mail-reply oc-opt-close"></i>
              </div>
            </div>

            <div class="oc-body">
              <div data-id="-1" class="oc-text oc-me"></div>

              <span class="oc-chat-offline"><?php _e('User is offline', 'online_chat'); ?></span>
              <span class="oc-chat-ended"><?php _e('Chat has ended', 'online_chat'); ?></span>
            </div>

            <div class="oc-message">
              <form name="oc-form" class="oc-form" action="<?php echo osc_base_url(); ?>" method="POST">
                <input type="hidden" name="ocChat" value="1"/>
                <input type="hidden" name="chatId" value="-1"/>
                <input type="hidden" name="fromId" value="<?php echo $user_id; ?>"/>
                <input type="hidden" name="fromName" value="<?php echo osc_esc_html($user_name); ?>"/>
                <input type="hidden" name="toId" value=""/>
                <input type="hidden" name="toName" value=""/>

                <textarea name="text" id="text" placeholder="<?php echo osc_esc_html(__('Type your message here', 'online_chat')); ?>"></textarea>

                <a href="#" class="oc-submit">
                  <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="30" height="30" x="0px" y="0px" viewBox="0 0 512 512" xml:space="preserve"> <g> <g> <path d="M481.508,210.336L68.414,38.926c-17.403-7.222-37.064-4.045-51.309,8.287C2.86,59.547-3.098,78.551,1.558,96.808 L38.327,241h180.026c8.284,0,15.001,6.716,15.001,15.001c0,8.284-6.716,15.001-15.001,15.001H38.327L1.558,415.193 c-4.656,18.258,1.301,37.262,15.547,49.595c14.274,12.357,33.937,15.495,51.31,8.287l413.094-171.409 C500.317,293.862,512,276.364,512,256.001C512,235.638,500.317,218.139,481.508,210.336z"/> </g> </g> </svg>
                </a>
              </form>
            </div>
          </div>
        </div>
      </div>


      
      <div class="oc-before">

        <!-- CREATE NEW CHAT BOX -->

        <div class="oc-head">
          <i class="fa fa-angle-left oc-back-new"></i>
          <?php echo __('Chat with', 'online_chat'); ?> <span class="oc-to-user-name"><?php _e('User', 'online_chat'); ?></span>
        </div>

        <div class="oc-message">
          <form name="oc-form-first" class="oc-form-first" action="<?php echo osc_base_url(); ?>" method="POST">
            <input type="hidden" name="ocStartChat" value="1"/>
            <input type="hidden" name="toUserId" value=""/>
            <input type="hidden" name="toUserName" value=""/>
            <input type="hidden" name="toUserImage" value=""/>

            <label for="userName"><?php _e('Your name', 'online_chat'); ?></label>
            <input type="hidden" name="userId" value="<?php echo $user_id; ?>"/>
            <input type="text" name="userName" id="userName" value="<?php echo osc_esc_html($user_name); ?>"/>

            <label for="text"><?php _e('Message', 'online_chat'); ?></label>
            <textarea name="text" id="text" placeholder=""></textarea>

            <a href="#" class="oc-submit"><?php _e('Start chatting', 'online_chat'); ?></a>

          </form>
        </div>
      </div>


      
      <?php foreach($active_chats as $chat_id) { ?>
        <?php
          // FIND LATEST CHAT MESSAGE
          $chat_unread = 0;
          foreach($chats as $c) {
            if($c['pk_i_chat_id'] == $chat_id) {
              $chat_last = $c;

              if($c['i_to_user_id'] == osc_logged_user_id() && $c['i_read'] == 0) {
                $chat_unread = 1;
              }
            }
          }

          // GET SECOND USER OF CHAT
          if(isset($chat_last['i_from_user_id']) && $user_id == $chat_last['i_from_user_id']) {
            $to_user_id = $chat_last['i_to_user_id'];
            $to_user_name = $chat_last['s_to_user_name'];
          } else {
            $to_user_id = $chat_last['i_from_user_id'];
            $to_user_name = $chat_last['s_from_user_name'];
          }
          
          //$to_user = ModelOC::newInstance()->getUserLastActive($to_user_id);
          //$to_user_online = (date('Y-m-d H:i:s', strtotime($to_user['dt_access_date'])) >= $limit_datetime ? true : false);

          // CHECK UNREAD CHAT
          //if(isset($chat_last['i_read']) && $chat_last['i_read'] == 0 && $chat_last['i_to_user_id'] == osc_logged_user_id()) {
          if($chat_unread == 1) {
            $unread_class = ' oc-unread';
          } else {
            $unread_class = '';
          }
        ?>

        <div class="oc-chat-thread<?php echo $unread_class; ?><?php if($active_chat_id == $chat_id) { ?> oc-on<?php } ?><?php if(oc_check_availability($to_user_id) == 0) { ?> oc-offline<?php } ?><?php if($chat_last['i_end'] <> 0) { ?> oc-ended<?php } ?>" data-chat-id="<?php echo $chat_id; ?>">
          <div class="oc-started">
            <div class="oc-head">
              <i class="fa fa-angle-left oc-back"></i>
              <div class="oc-img-wrap">
                <img src="<?php echo oc_get_picture($to_user_id); ?>"/>
              </div>
              <span>
                <strong>
                  <span><?php echo $to_user_name; ?></span>
                  <i class="oc-check"></i>
                </strong>
                <em>
                  <?php if($chat_last['i_from_user_id'] <> osc_logged_user_id()) { ?>
                    <i class="fa fa-angle-right"></i>
                  <?php } ?>

                  <?php echo $chat_last['s_text']; ?>
                </em>
              </span>

              <i class="fa fa-trash oc-close"></i>
              <i class="fa fa-gear oc-options"></i>

              <div class="oc-options-list">
                <i class="fa fa-gear oc-opt-ico"></i>
                <div data-options-action="1" class="oc-opt<?php if(oc_check_bans($to_user_id)) { ?> oc-opt-success<?php } ?>">
                  <?php 
                    if(oc_check_bans($to_user_id)) { 
                      _e('This user is blocked', 'online_chat'); 
                    } else { 
                      _e('Block this user', 'online_chat'); 
                    } 
                  ?>
                </div>
                <div data-options-action="2" class="oc-opt"><?php _e('Mail me chat transcript', 'online_chat'); ?></div>
                <i class="fa fa-mail-reply oc-opt-close"></i>
              </div>
            </div>

            <div class="oc-body">
              <?php foreach($chats as $c) { ?>
                <?php if($c['pk_i_chat_id'] == $chat_id) { ?>
                  <?php if(isset($hist['pk_i_chat_id']) && $hist['pk_i_chat_id'] == $chat_id) { ?>
                    <?php if(date('Y-m-d H:i:s', strtotime(' +5 minutes', strtotime($hist['dt_datetime']))) < date('Y-m-d H:i:s', strtotime($c['dt_datetime']))) { ?>
                      <span class="oc-time"><span><?php echo date('H:i', strtotime($c['dt_datetime'])); ?></span></span>
                    <?php } ?>
                  <?php } ?>

                  <div data-id="<?php echo osc_esc_html($c['pk_i_id']); ?>" class="oc-text oc-not-new<?php if($c['i_from_user_id'] == $user_id) { ?> oc-me<?php } ?>"><?php echo $c['s_text']; ?></div>

                  <?php $hist = $c; ?>
                <?php } ?>
              <?php } ?>

              <span class="oc-chat-offline"><?php _e('User is offline', 'online_chat'); ?></span>
              <span class="oc-chat-ended"><?php _e('Chat has ended', 'online_chat'); ?></span>
            </div>

            <div class="oc-message">
              <form name="oc-form" class="oc-form" action="<?php echo osc_base_url(); ?>" method="POST">
                <input type="hidden" name="ocChat" value="1"/>
                <input type="hidden" name="chatId" value="<?php echo $chat_id; ?>"/>
                <input type="hidden" name="fromId" value="<?php echo $user_id; ?>"/>
                <input type="hidden" name="fromName" value="<?php echo osc_esc_html($user_name); ?>"/>
                <input type="hidden" name="toId" value="<?php echo $to_user_id; ?>"/>
                <input type="hidden" name="toName" value="<?php echo osc_esc_html($to_user_name); ?>"/>

                <textarea name="text" id="text" placeholder="<?php echo osc_esc_html(__('Type your message here', 'online_chat')); ?>" <?php if($chat_last['i_end'] <> 0) { ?>class="disabled" disabled="disabled"<?php } ?>></textarea>

                <a href="#" class="oc-submit<?php if($chat_last['i_end'] <> 0) { ?> oc-disabled<?php } ?>">
                  <svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="30" height="30" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 512 512" xml:space="preserve"> <g> <g> <path d="M481.508,210.336L68.414,38.926c-17.403-7.222-37.064-4.045-51.309,8.287C2.86,59.547-3.098,78.551,1.558,96.808 L38.327,241h180.026c8.284,0,15.001,6.716,15.001,15.001c0,8.284-6.716,15.001-15.001,15.001H38.327L1.558,415.193 c-4.656,18.258,1.301,37.262,15.547,49.595c14.274,12.357,33.937,15.495,51.31,8.287l413.094-171.409 C500.317,293.862,512,276.364,512,256.001C512,235.638,500.317,218.139,481.508,210.336z"/> </g> </g> </svg>
                </a>
              </form>
            </div>
          </div>
        </div>
      <?php } ?>

      <div class="oc-chat-thread-empty"><?php _e('There are no chats yet', 'online_chat'); ?></div>
    </div>
  <?php } ?>
</div>

<style>
  .oc-body > div:not(.oc-me):after {background-image:url('<?php echo osc_base_url(); ?>oc-content/plugins/online_chat/img/no-user.png'); }
</style>