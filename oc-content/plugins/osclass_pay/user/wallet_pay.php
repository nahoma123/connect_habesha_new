<?php
  $url = '';
  
  if(osc_is_web_user_logged_in()) {
    $data = osp_get_custom(urldecode(Params::getParam('extra')));
    $product_type = explode('x', @$data['product']);
    $item_id = @$data['itemid'];
    $item = Item::newInstance()->findByPrimaryKey($item_id);
    $wallet = osp_get_wallet(osc_logged_user_id());
    
    if(osc_logged_user_id() == @$item['fk_i_user_id']) {
      if(osp_fee_is_allowed($product_type[0])) {
        $fee = osp_get_fee($product_type[0], $item_id);
      } else {
        $fee = 0;
      }
    }

    if($product_type[0] == OSP_TYPE_MULTIPLE) {
      $fee = @$data['amount'];
    }

    if($wallet['formatted_amount'] >= $fee) {

      $payment_id = ModelOSP::newInstance()->saveLog(
        urldecode(Params::getParam('desc')),   // concept
        'wallet_' . date('YmdHis'), // transaction code
        $fee,              // amount
        osp_currency(), // currency
        @$data['email'],             // payer's email
        @$data['user'],              // user
        osp_create_cart_string($product_type[1], @$data['user'], $item_id),  // cart string
        $product_type[0],           // product type
        'WALLET'                    //source
      );
      
      osp_wallet_update(osc_logged_user_id(), -$fee);

      if($product_type[0] <> '') {
        $payment_details = osp_prepare_payment_data($fee, $payment_id, @$data['user'], $product_type);   //amount, payment_id, user_id, product_type
        osp_pay_fee($payment_details);
        $url = osp_pay_url_redirect($product_type);
      }
    }
  }


  if($url <> '') {
    osc_add_flash_ok_message(__('Payment processed correctly', 'osclass_pay'));
    //osp_js_redirect_to($url);
    osp_redirect($url);

  } else {
    osc_add_flash_error_message(__('There were some errors, please try again later or contact the administrators', 'osclass_pay'));
    //osp_js_redirect_to(osc_route_url('osp-item'));
    osp_redirect(osc_route_url('osp-item'));

  }
?>