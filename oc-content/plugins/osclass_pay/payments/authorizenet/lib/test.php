 <?php
    require_once 'lib/_AuthorizeNetLoad.php'; 
    define("AUTHORIZENET_API_LOGIN_ID", "3TbE28uae4d");
    define("AUTHORIZENET_TRANSACTION_KEY", "52j9SKy3u89Dt9U8");
    define("AUTHORIZENET_SANDBOX", true);
    $sale = new AuthorizeNetAIM;
    $sale->amount = "5.99";
    $sale->card_num = '6011000000000012';
    $sale->exp_date = '04/15';
    $response = $sale->authorizeAndCapture();
    if ($response->approved) {
        $transaction_id = $response->transaction_id;
    }

print_r($response);
    ?>