<?php
  $url = '';
  
  $data = osp_get_custom(urldecode(Params::getParam('extra')));

  $user_id = @$data['user'];
  $item_id = @$data['itemid'];
  $email = @$data['email'];
  $product_type = explode('x', @$data['product']);
  $amount = round(@$data['amount'], 2);
  $min = (osp_param('bt_min') > 0 ? osp_param('bt_min') : 0);
  $url = osp_pay_url_redirect($product_type);

  $checksum = osp_create_checksum($item_id, $user_id, $email, $amount);

  if($checksum != @$data['checksum']) {
    osc_add_flash_error_message(__('Data checksum has failed, payment was cancelled.', 'osclass_pay'));
    osp_redirect($url);
  } else if($user_id > 0 && osc_is_web_user_logged_in() && osc_logged_user_id() != $user_id) {
    osc_add_flash_error_message(__('Bank transfer data are related to different user, payment was cancelled.', 'osclass_pay'));
    osp_redirect($url);
  }
  
  if($product_type[0] == OSP_TYPE_MULTIPLE && $amount >= $min) {
    $description = @$data['concept'];
    $account = osp_param('bt_iban');
    $variable_symbol = mb_generate_rand_int(8);

    $transaction_id = ModelOSP::newInstance()->createBankTransfer(
      $variable_symbol,
      osp_create_cart_string($product_type[1], $user_id, $item_id),
      $description,
      $amount,
      $user_id,
      urldecode(Params::getParam('extra'))
    );

    osp_email_new_bt($transaction_id);

    osp_cart_drop($user_id);

    osc_add_flash_info_message(sprintf(__('Payment in progress. We are awaiting your bank transfer to our account. <br/>Transaction ID: %s <br/>IBAN: %s <br/>Variable Symbol: %s <br/> Amount: %s <br/>Once funds are on our account, we complete your payment. Note that bank transfer can take up to 3 days.', 'osclass_pay'), $transaction_id, $account, $variable_symbol, osp_format_price($amount)));
    //osp_js_redirect_to($url);
    osp_redirect($url);

  } else {

    $url = osp_pay_url_redirect($product_type[0]);
    osc_add_flash_error_message(__('There was problem recognizing your product, please try bank transfer payment again from your cart.', 'osclass_pay'));
    //osp_js_redirect_to($url);
    osp_redirect($url);

  }
?>