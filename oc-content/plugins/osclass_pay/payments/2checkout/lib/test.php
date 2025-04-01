<!DOCTYPE html>
<html lang="en">
<head>
    <title>Example Form</title>
    <script type="text/javascript" src="https://www.2checkout.com/checkout/api/2co.min.js"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
</head>
<body>
    <form id="myCCForm" action="" method="post">
        <input name="token" type="hidden" value="" />
        <input id="ccNo" type="text" value="4000000000000002" autocomplete="off" required />
        <input id="expMonth" type="text" size="2" required value="12"/>
        <input id="expYear" type="text" size="4" required value="2016"/>
        <input id="cvv" type="text" value="123" autocomplete="off" required />
        <input type="submit" value="Submit Payment" />
    </form>
<script>
var successCallback = function(data) {
    var myForm = document.getElementById('myCCForm');
    myForm.token.value = data.response.token.token;
    myForm.submit();
};

var errorCallback = function(data) {
    if (data.errorCode === 200);
    else alert(data.errorMsg);
};

var tokenRequest = function() {
    var args = {
        sellerId: "901294338",
        publishableKey: "D0E54E96-FBB9-4A9C-98F0-81359D3FE574",
        ccNo: $("#ccNo").val(),
        cvv: $("#cvv").val(),
        expMonth: $("#expMonth").val(),
        expYear: $("#expYear").val()
    };
    TCO.requestToken(successCallback, errorCallback, args);
};

$(function() {
    TCO.loadPubKey('production');
    $("#myCCForm").submit(function(e) {
        tokenRequest();
        return false;
    });
});

</script>
</body>
</html>
<?php
if(isset($_POST['token'])){
    require_once("Twocheckout.php");
    Twocheckout::privateKey('7EF672B0-9F5E-499E-B4AE-6CBEC67277E1');
    Twocheckout::sellerId('901294338');
    Twocheckout::verifySSL(false);  // this is set to true by default
    Twocheckout::sandbox(true);
    Twocheckout::format('json');
    try {
        $charge = Twocheckout_Charge::auth(array(
            "sellerId" => "901294338",
            "merchantOrderId" => "123",
            "token" => $_POST['token'],
            "currency" => 'USD',
            "total" => '10.00',
            "billingAddr" => array(
                "name" => 'Testing Tester',
                "addrLine1" => '123 Test St',
                "city" => 'Columbus',
                "state" => 'OH',
                "zipCode" => '43123',
                "country" => 'USA',
                "email" => 'testingtester@2co.com',
                "phoneNumber" => '555-555-5555'
            ),
            "shippingAddr" => array(
                "name" => 'Testing Tester',
                "addrLine1" => '123 Test St',
                "city" => 'Columbus',
                "state" => 'OH',
                "zipCode" => '43123',
                "country" => 'USA',
                "email" => 'testingtester@2co.com',
                "phoneNumber" => '555-555-5555'
            )
        ));
        echo '<pre>';print_r(json_decode($charge));echo'</pre>';
    } catch (Twocheckout_Error $e) {echo $e->getMessage();}
}
?>