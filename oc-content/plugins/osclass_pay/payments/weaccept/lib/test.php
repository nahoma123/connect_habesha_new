<?php
  define('ABS_PATH', dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))))) . '/');
  require_once ABS_PATH . 'oc-load.php';

  require_once osc_lib_path() . 'plugins/osclass_pay/payments/weaccept/lib/WeacceptLibrary.php';

  $integration_id = osp_param('weaccept_integration_id');
  $iframe_id = osp_param('weaccept_iframe_id');
  $api_key = osp_decrypt(osp_param('weaccept_api_key'));

  $amount = 25;
  $order_id = rand(1, 100000000);
  $user = User::newInstance()->findByPrimaryKey(1);


  $weaccept = new WeacceptLibrary();



  // 1. AUTHENTICATE
  $auth = $weaccept->authPaymob($api_key);

echo '<pre>';
print_r($auth);
echo '</pre>';
echo '---------<br/><br/>';

  // 2. ORDER REGISTRATION REQUEST
  $order = $weaccept->makeOrderPaymob(
    $auth->token, // this is token from step 1.
    $auth->profile->id, // this is the merchant id from step 1.
    $amount * 100, // total amount by cents/piasters.
    $order_id // your (merchant) order id.
  );

echo '<pre>';
print_r($order);
echo '</pre>';
echo '---------<br/><br/>';


  // 3. PAYMENT KEY GENERATION REQUEST
  $payment_key = $weaccept->getPaymentKeyPaymob(
    $integration_id,  // your integration ID
    $auth->token, // from step 1.
    $order->amount_cents, // total amount by cents/piasters.
    $order->id, // paymob order id from step 2.
    $user['s_email'], // optional
    $user['s_name'], // optional, firstname
    $user['s_name'], // optional, lastname
    ($user['s_phone_mobile'] <> '' ? $user['s_phone_mobile'] : $user['s_phone_land']), // optional
    $user['s_city'], // optional
    $user['s_country'] // optional
  );
  

echo '<pre>';
print_r($payment_key);
echo '</pre>';
echo '---------<br/><br/>';


  // 4. PREPARE YOUR CLIENT CODE

?>



<form id="paymob_checkout" class="nocsrf">
    <label for="">Card number</label>
      <input type="text" value="4987654321098769" paymob_field="card_number">
      <br>
      <label for="">Card holdername</label>
      <input type="text" value="Test Account" paymob_field="card_holdername">
      <br>
      <label for="">Card month</label>
      <input type="text" value="05" paymob_field="card_expiry_mm">
      <br>
      <label for="">Card year</label>
      <input type="text" value="21" paymob_field="card_expiry_yy">
      <br>
      <label for="">Card cvn</label>
      <input type="text" value="123" paymob_field="card_cvn">
      <input type="hidden" value="CARD" paymob_field="subtype">
      <input type="checkbox" value="tokenize" name="save card"> <label for="save card">save card</label>

      <input type="submit" value="Pay">
      <br>
</form>



<iframe width="800" height="600" src="https://accept.paymobsolutions.com/api/acceptance/iframes/<?php echo $iframe_id; ?>?payment_token=<?php echo $payment_key->token; ?>"></iframe>



<?php
/*
checking response.


NOTIFICAITON
--
https://your.server.com/paymob_notification_callback?hmac=afdb235dsa...
parse_url($url, PHP_URL_PATH) == '/paymob_notification_callback'


RETURN URL
--
https://your.server.com/paymob_txn_response_callback?id=76&pending=False&amount_cents=100&success=Tru..
parse_url($url, PHP_URL_PATH) == '/paymob_txn_response_callback'

*/
?>

