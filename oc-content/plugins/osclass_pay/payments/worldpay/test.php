<?php
namespace Worldpay;

define('ABS_PATH', dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/');
require_once ABS_PATH . 'oc-load.php';

require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'lib/init.php';


try {
  $worldpay = new Worldpay("T_S_01e9f284-4e8e-434f-988b-6338e6bb9802");
  $worldpay->disableSSLCheck(false);

$amount = 29.12;

  $response = $worldpay->createOrder(array(
            'directOrder' => true, // Order description of your choice
            'orderType' => 'ATM', 
            'orderDescription' => 'Pay cart items', // Order description of your choice
            'amount' => round($amount*100), // Amount in pence
            'is3DSOrder' => false, // 3DS
            'authorizeOnly' => false,
            'reusable' => false,
            //'siteCode' => $_POST['site-code'],
            'orderType' => 'ECOM', //Order Type: ECOM/MOTO/RECURRING
            'currencyCode' => 'USD', // Currency code
            'settlementCurrency' => 'USD', // Settlement currency code
            'name' => 'John Doe', // Customer name
            'shopperEmailAddress' => 'john@doe.com', // Shopper email address
            'billingAddress' => array(), // Billing address array
            'deliveryAddress' => array(), // Delivery address array
            'customerOrderCode' => 'fdsaf213fdaf21',

            'paymentMethod' => array(
                  "name" => 'Testing',
                  "expiryMonth" => '02',
                  "expiryYear" => '2020',
                  "cardNumber"=> '4444333322221111',
                  "cvc"=> '123'
            )
          
  ));

  echo '<pre>';
  print_r($response);
  echo '</pre>';


} catch (WorldpayException $e) {
    echo 'Error code: ' . $e->getCustomCode() . '<br/> 
    HTTP status code:' . $e->getHttpStatusCode() . '<br/> 
    Error description: ' . $e->getDescription()  . ' <br/>
    Error message: ' . $e->getMessage();
} catch (Exception $e) { 
    echo 'Error message: '. $e->getMessage();
}

?>



<script src="https://cdn.worldpay.com/v1/worldpay.js"></script>        

    <script type='text/javascript'>
    window.onload = function() {
      Worldpay.useTemplateForm({
        'clientKey':'T_C_75737540-1541-44d9-8009-3bec8fc3e2e6',
        'form':'paymentForm',
        'paymentSection':'paymentSection',
        'display':'inline',
        'reusable':true,
        'saveButton':false,
        'callback': function(obj) {
          if (obj && obj.token) {
            var _el = document.createElement('input');
            _el.value = obj.token;
            _el.type = 'hidden';
            _el.name = 'token';
            document.getElementById('paymentForm').appendChild(_el);
            document.getElementById('paymentForm').submit();
          }
        }
      });
    }
    </script>


<body>
<form action="/complete" id="paymentForm" method="post">
      <!-- all other fields you want to collect, e.g. name and shipping address -->
      <div id='paymentSection'></div>
      <div>
        <input type="submit" value="Place Order" onclick="Worldpay.submitTemplateForm()" />
      </div>
    </form>




<form action="/complete" id="paymentForm" method="post">

  <span id="paymentErrors"></span>

  <div class="form-row">
    <label>Name on Card</label>
    <input data-worldpay="name" name="name" type="text" />
  </div>
  <div class="form-row">
    <label>Card Number</label>
    <input data-worldpay="number" size="20" type="text" />
  </div>
  <div class="form-row">
    <label>Expiration (MM/YYYY)</label> 
    <input data-worldpay="exp-month" size="2" type="text" /> 
    <label> / </label>
    <input data-worldpay="exp-year" size="4" type="text" />
  </div>
  <div class="form-row">
    <label>CVC</label>
    <input data-worldpay="cvc" size="4" type="text" />
  </div>

  <input type="submit" value="Place Order" />

</form>
</body>
