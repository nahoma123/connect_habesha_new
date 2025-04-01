<?php
  // https://github.com/blockchain/receive-payments-demos/tree/master/php

  class BlockchainPayment {
    public function __construct() {}

    // CREATE BUTTON USED IN OSP_BUTTONS FUNCTION
    public static function button($amount = '0.00', $description = '', $itemnumber = '', $extra_array = null) {

      if(osp_currency() <> 'BTC') {
        $amount = osc_file_get_contents("https://blockchain.info/tobtc?currency=" . osp_currency() . "&value=" . $amount);
      }

      //$extra = osp_prepare_custom($extra_array).'|';
      //$extra .= 'pr,'.$itemnumber;
      $extra = 'us,'.osc_logged_user_id().'|pr,'.$itemnumber.'|am,'.$amount;    // pr = product, us = user, am = amount

      $AJAX_URL = osc_base_url(true) . '?page=ajax&action=runhook&hook=blockchain&extra=' . $extra;
    ?>


      <li class="payment bitcoin-btn">
        <!--<script type="text/javascript" src="https://blockchain.info/Resources/js/pay-now-button-v2.js"></script>-->

        <a class="blockchain-btn osp-has-tooltip"
          data-anonymous="false"
          data-shared="false"
          data-create-url="<?php echo $AJAX_URL; ?>"
          title="<?php echo osc_esc_html(__('You will get link to send funds', 'osclass_pay')); ?>"
          onclick="ospBlockchainDialog();"
        >
          <span><img src="<?php echo osp_url(); ?>img/payments/blockchain.png"/></span>
          <strong><?php _e('Pay with Blockchain', 'osclass_pay'); ?></strong>
          
          <div id="blockchain-overlay" class="osp-custom-overlay"></div>
          <div id="blockchain-dialog" class="osp-custom-dialog" style="display:none;">
            <div class="osp-inside">
              <div class="osp-top">
                <span><img src="<?php echo osp_url(); ?>img/payments/white/blockchain.png" alt="<?php echo osc_esc_html(__('Blockchain Payment', 'osclass_pay')); ?>"/></span>
                <div class="osp-close"><i class="fa fa-times"></i></div>
              </div>

              <div class="osp-bot">
                <div class="blockchain stage-begin" style="display:none!important;">
                  <img src="<?php echo osp_url(); ?>img/payments/blockchain.png">
                </div>

                <div class="blockchain stage-loading" style="text-align:center">
                  <!--<img src="<?php echo osc_base_url() . 'oc-content/plugins/' . osc_plugin_folder(__FILE__); ?>loading-large.gif">-->
                  <i class="fa fa-cog fa-spin fa-3x fa-fw"></i>
                </div>

                <div class="blockchain stage-ready">
                  <p align="center"><?php printf(__('Please send <strong>%f BTC</strong> to <br /> <b>[[address]]</b></p>', 'osclass_pay'), $amount); ?>
                  <p align="center" class="qr-code"></p>
                </div>

                <div class="blockchain stage-paid">
                  <i class="blockchain-success-icon blockchain-icon fa fa-check-circle"></i>
                  <?php _e('Payment Received <b>[[value]] BTC</b>. Thank You.', 'osclass_pay'); ?>
                </div>

                <div class="blockchain stage-error">
                  <i class="blockchain-error-icon blockchain-icon fa fa-times-circle"></i>
                  <span>[[error]]</span>
                </div>
              </div>
            </div>
          </div>
        </a>

        <script>
          function ospBlockchainDialog() {
            $('#blockchain-dialog').fadeIn(200).css('top', ($(document).scrollTop() + Math.round($(window).height()/10)) + 'px');
            $('#blockchain-overlay').fadeIn(200);
            $('.blockchain-btn').css('opacity', '1');
          }

          $('#blockchain-dialog .osp-close, #blockchain-overlay').on('click', function(e){ 
            e.stopPropagation();
            $('.osp-custom-dialog').fadeOut(200);
            $('#blockchain-overlay').fadeOut(200);
            $('.blockchain-btn').css('opacity', '');
          });

          $(document).ready(function(){
            $('.stage-paid').on('show', function(e){ 
              setTimeout(function(){
                location.reload();
              }, 2000);
            });
          });
        </script>
      </li>
    <?php
    }


    public static function ajaxPayment() {
      ob_get_clean(); // for json

      $extra = Params::getParam('extra');
      $data = osp_get_custom(Params::getParam('extra'));
      $product_type = explode('x', $data['pr']);

      if(!isset($data['pr'])) {
        print json_encode(array('input_address' => __('Missing product', 'osclass_pay') . ' - ' . $response['message'] ));
        die;
      }

      $extra = str_replace(',', '[o]', $extra);
      $extra = str_replace('|', '[p]', $extra);
      $CALLBACK_URL = osc_base_url() . 'oc-content/plugins/osclass_pay/payments/blockchain/callback.php?extra=' . $extra;

      $xpub = osp_param('blockchain_xpub');
      $key = osp_decrypt(osp_param('blockchain_key'));

      $resp = osc_file_get_contents("https://api.blockchain.info/v2/receive?key=" . $key . "&callback=" . urlencode($CALLBACK_URL) . "&xpub=" . $xpub);

      $response = json_decode($resp, true);

      if(!isset($response['address'])) {
        print json_encode(array('input_address' => __('Error API', 'osclass_pay') . ' - ' . $response['message'] . ' - ' . $response['description'] ));
        die;
      }

      print json_encode(array('input_address' => $response['address'] ));
      die;
    }



    public static function processPayment() {
      if(Params::getParam('test') == true) {
        return array(OSP_STATUS_FAILED, __('Test payment received, such payments cannot be accepted.', 'osclass_pay'));
      }

      $prepare_extra = Params::getParam('extra');
      $prepare_extra = str_replace('[-]', ' ', $prepare_extra);
      $prepare_extra = str_replace('[p]', '|', $prepare_extra);
      $prepare_extra = str_replace('[o]', ',', $prepare_extra);

      $data = osp_get_custom($prepare_extra);
      $transaction_hash = Params::getParam('transaction_hash');
      $value_in_btc = Params::getParam('value') / 100000000;
      $my_bitcoin_address = osp_param('blockchain_address');

      // if (Params::getParam('address') <> $my_bitcoin_address) {
      //   return array(OSP_STATUS_FAILED, __('Payment confirmation contains different Bitcoin address that is yours', 'osclass_pay'));
      // }

      // $hosts = gethostbynamel('blockchain.info');

      // foreach ($hosts as $ip) {
        // Check payment came from one of blockchain.info's IP
        // if ($_SERVER['REMOTE_ADDR'] == $ip) {
          $exists = ModelOSP::newInstance()->getPaymentByCode($transaction_hash, 'BLOCKCHAIN');
          
          if(isset($exists['pk_i_id'])) { 
            return array(OSP_STATUS_ALREADY_PAID, __('Product has already been paid', 'osclass_pay')); 
          }
          
          if ((is_numeric(Params::getParam('confirmations')) && Params::getParam('confirmations') >= 3 ) || Params::getParam('anonymous') == true) {
            $product_type = explode('x', $data['pr']);

            // SAVE TRANSACTION LOG
            $payment_id = ModelOSP::newInstance()->saveLog(
              sprintf(__('Pay cart items for %sBTC', 'osclass_pay'), $value_in_btc), //concept
              $transaction_hash, // transaction code
              $value_in_btc, //amount
              'BTC', //currency
              osc_logged_user_email(), // payer's email
              $data['us'], //user
              osp_create_cart_string($product_type[1], $data['us'], $product_type[2]), // cart string
              $product_type[0], //product type
              'BLOCKCHAIN' //source
            );
            

            // Pay it!
            $payment_details = osp_prepare_payment_data($value_in_btc, $payment_id, $data['us'], $product_type);   //amount, payment_id, user_id, product_type
            $pay_item = osp_pay_fee($payment_details);

            return array(OSP_STATUS_COMPLETED, __('Payment completed', 'osclass_pay'));
          } else {
            // Maybe we could do something here (the payment was correct, but it didnt get enought confirmations yet - we expect 3 confirmations)
            return array(OSP_STATUS_PENDING, __('Waiting for more confirmations (need 3).', 'osclass_pay'));
          }

          // break;
        // }
      // }

      // return array(OSP_STATUS_FAILED, __('Payment has not came from blockchain.info domains.', 'osclass_pay'));
    }
  }
?>