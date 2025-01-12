<?php
class PagseguroPayment {
  public function __construct(){}

  public static function button($amount = '0.00', $description = '', $itemnumber = '', $extra_array = null) {
    require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'lib/PagSeguroLibrary.php';

    $charge = new PagSeguroPaymentRequest();
    //$charge->setRedirectUrl(osc_base_url() . 'oc-content/plugins/' . osc_plugin_folder(__FILE__) . 'return.php?extra=' . $extra_array);
    $charge->setRedirectUrl(osc_base_url() . 'oc-content/plugins/' . osc_plugin_folder(__FILE__) . 'return.php');
    $charge->setCurrency('BRL');
    $charge->addItem($itemnumber, $description, 1, $amount);
    
    
    try {
      $credentials = PagSeguroConfig::getAccountCredentials();
      $lightbox = osp_param('pagseguro_lightbox');

      $code = $charge->register($credentials, false);
      echo '<li class="payment pagseguro-btn"><a class="osp-has-tooltip" title="' . ($lightbox == 1 ? osc_esc_html(__('Form to enter payment details will pop-up', 'osclass_pay')) : osc_esc_html(__('You will be redirected to PagSeguro', 'osclass_pay'))) . '" href="' . ($lightbox == 1 ? '#' : $code) . '" onclick="' . ($lightbox == 1 ? 'PagSeguroLightbox(\'<?php echo $code; ?>\');return false;' : '') . '"><span><img src="' . osp_url() . 'img/payments/pagseguro.png"/></span><strong>' . __('Pay with PagSeguro', 'osclass_pay') . '</strong></a></li>';

    } catch (PagSeguroServiceException $e) {
      if(OSP_DEBUG) {
        //$emailtext = osp_array_to_string($e->getMessage());
        //mail(osc_contact_email() , 'OSCLASS PAY - PAGSEGURO DEBUG RESPONSE', $emailtext);
      }

      osp_to_console('PagSeguro Error: ' . $e->getMessage());
    }
  }



  // AJAX FUNCTION TO PROCESS PAYMENT, HOOKED TO AJAX_PAGSEGURO
  public static function processNotification() {
    require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'lib/PagSeguroLibrary.php';

    $code = trim(Params::getParam('notificationCode'));
    $type = trim(Params::getParam('notificationType'));

    if ($code <> '' && $type <> '') {
      $notificationType = new PagSeguroNotificationType($type);
      $strType = strtoupper($notificationType->getTypeFromValue());
      if($strType == 'TRANSACTION') {
        $response = self::processPayment($code);      // manage payment itself
      } else {
        $response = array(OSP_STATUS_FAILED, $notificationType->getValue());
      }
    } else {
      $response = array(OSP_STATUS_FAILED);
    }

    return $response;

    /* This should not be needed as it is just notification!!!

    $status = $response[0];
    
    if ($status == OSP_STATUS_COMPLETED) {
      osc_add_flash_ok_message(sprintf(__('Success! Please write down this transaction ID in case you have any problem: %s', 'osclass_pay'), Params::getParam('pagseguro_transaction_id')));
    } else if ($status == OSP_STATUS_ALREADY_PAID) {
      osc_add_flash_warning_message(__('Warning! This payment was already paid.', 'osclass_pay'));
    } else if ($status == OSP_STATUS_AMOUNT_ZERO) {
      osc_add_flash_error_message(__('You are trying to pay zero amount.', 'osclass_pay'));
    } else {
      osc_add_flash_error_message(__('There were an error processing your payment.', 'osclass_pay'));
    }


    osp_js_redirect_to(osp_pay_url_redirect());
    */
  }



  public static function processPayment($code, $transaction_id = '') {
    require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'lib/PagSeguroLibrary.php';

    $credentials = PagSeguroConfig::getAccountCredentials();

    try {
      if($transaction_id <> '' && $code == '0') {
        $transaction = PagSeguroTransactionSearchService::searchByCode($credentials, $transaction_id);
      } else {
        $transaction = PagSeguroNotificationService::checkTransaction($credentials, $code);
      }

      $status = $transaction->getStatus()->getValue();
      
      // https://dev.pagseguro.uol.com.br/v1/docs/recorrencia-guia-tabelas-de-status-e-erros
      // 0 - Initiated, 1 - Waiting, 3 - Paid, 4 - Paid, but no return, 5 - Disputed, 6 - Returned, 7 - Canceled
      if($status == 0 || $status == 1) {
        return array(OSP_STATUS_PENDING);
      } else if($status <> 3 && $status <> 4) {
        return array(OSP_STATUS_FAILED, sprintf(__('Invalid transaction status: %s', 'osclass_pay'), $status));
      }

      Params::setParam('pagseguro_transaction_id', $transaction->getCode());
  
      $item = $transaction->getItems();
      $item = $item[0];
      $concept = $item->getDescription();
      $itemnumber = $item->getId();
      $product_type = explode('x', $itemnumber);

      $user_id = osc_is_web_user_logged_in() ? osc_logged_user_id() : ($product_type[1] == 1 ? $product_type[2] : '');
      $ps_user = $transaction->getSender();
      $email = $ps_user->getEmail();

      $payment = ModelOSP::newInstance()->getPaymentByCode($transaction->getCode(), 'PAGSEGURO');

      if(!$payment) { 
        // SAVE TRANSACTION LOG
        $payment_id = ModelOSP::newInstance()->saveLog(
          $concept, //concept
          $transaction->getCode(), // transaction code
          $transaction->getGrossAmount(), //amount
          'BRL', //currency
          $email, // payer's email
          $user_id, //user
          osp_create_cart_string($product_type[1], $user_id, $product_type[2]), // cart string
          $product_type[0], //product type
          'PAGSEGURO' //source
        );


        // Pay it!
        $payment_details = osp_prepare_payment_data($transaction->getGrossAmount(), $payment_id, $user_id, $product_type);   //amount, payment_id, user_id, product_type
        $pay_item = osp_pay_fee($payment_details);

        return array(OSP_STATUS_COMPLETED, $transaction);
      }
      
      return array(OSP_STATUS_ALREADY_PAID, $transaction);
    } catch (PagSeguroServiceException $e) {
      return array(OSP_STATUS_FAILED, $e); 
    }
  }
}


class PagSeguroConfigWrapper {
  public static function getConfig(){
    $PagSeguroConfig = array();
    $PagSeguroConfig['credentials'] = array();
    $PagSeguroConfig['credentials']['email'] = osp_param('pagseguro_email');

    $PagSeguroConfig['credentials']['token']['production'] = osp_param('pagseguro_token');
    $PagSeguroConfig['credentials']['appId']['production'] = osp_param('pagseguro_application_id');
    $PagSeguroConfig['credentials']['appKey']['production'] = osp_decrypt(osp_param('pagseguro_application_key'));

    $PagSeguroConfig['credentials']['token']['sandbox'] = osp_param('pagseguro_sb_token');
    $PagSeguroConfig['credentials']['appId']['sandbox'] = osp_param('pagseguro_sb_application_id');
    $PagSeguroConfig['credentials']['appKey']['sandbox'] = osp_decrypt(osp_param('pagseguro_sb_application_key'));

    $PagSeguroConfig['application'] = array();
    $PagSeguroConfig['application']['charset'] = "UTF-8";

    $PagSeguroConfig['environment'] = (osp_param('pagseguro_sandbox') == 1 ? 'sandbox' : 'production');

    $PagSeguroConfig['log'] = array();
    $PagSeguroConfig['log']['active'] = false;
    $PagSeguroConfig['log']['fileLocation'] = "";

    return $PagSeguroConfig;
  }
}
?>