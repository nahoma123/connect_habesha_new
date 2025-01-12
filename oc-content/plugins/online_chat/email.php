<?php

// Create email when listing is rejected by admin
function oc_email_transcript($chat_id) {
  oc_include_mailer();

  $mPages = new Page() ;
  $aPage = $mPages->findByInternalName('onc_email_transcript');
  $locale = osc_current_user_locale() ;
  $content = array();
  
  if(isset($aPage['locale'][$locale]['s_title'])) {
    $content = $aPage['locale'][$locale];
  } else {
    $content = current($aPage['locale'] <> '' ? $aPage['locale'] : array());
  }
  
  $from_id = osc_logged_user_id();
  
  $chat = ModelOC::newInstance()->getChatById($chat_id);
  $user = User::newInstance()->findByPrimaryKey($from_id);
  $first = $chat[0];

  if($from_id == $first['i_from_user_id']) {
    $from_name = $first['s_from_user_name'];
  } else {
    $from_name = $first['s_to_user_name'];
  }


  $chat_content = '<table>';

  foreach($chat as $c) {
    $chat_content .= '<tr>';
    $chat_content .= '<td><strong>' . $c['s_from_user_name'] . '</strong></td>';
    $chat_content .= '<td>&nbsp;&nbsp;&nbsp;</td>';
    $chat_content .= '<td><span style="font-size:11px;color:#999;">' . date('H:i', strtotime($c['dt_datetime'])) . '</span></td>';
    $chat_content .= '<td>&nbsp;&nbsp;&nbsp;</td>';
    $chat_content .= '<td> ' . $c['s_text'] . ' </td>';
    $chat_content .= '</tr>';
  }

  $chat_content .= '</table>';
  $chat_content .= '--';


  $words   = array();
  $words[] = array( '{CHAT}', '{CONTACT_NAME}', '{WEB_TITLE}' );
  $words[] = array( $chat_content, $from_name, osc_page_title() ) ;

  $title = osc_mailBeauty($content['s_title'], $words) ;
  $body  = osc_mailBeauty($content['s_text'], $words) ;


  $email_build = array(
    'subject'  => $title, 
    'to' => $user['s_email'], 
    'to_name'  => $from_name,
    'body' => $body,
    'alt_body' => $body
  );

print_r($email_build);

  osc_sendMail($email_build);
}

?>