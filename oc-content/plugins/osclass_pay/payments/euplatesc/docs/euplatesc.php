<?php 
if($_POST['payment_mode'] == 'euplatesc_payment') :

$mid="testaccount";
$key="00112233445566778899aabbccddeeff";
require('euplatesc_functions.php'); ?>

<div align="center">
<form ACTION="https://secure.euplatesc.ro/tdsprocess/tranzactd.php" METHOD="POST" class="form-euplatesc" name="gateway" target="_self">

<!-- begin billing details -->
    <input name="fname" type="hidden" value="<?php echo $dataBill['fname'];?>" />
    <input name="lname" type="hidden" value="<?php echo $dataBill['lname'];?>" />
    <input name="country" type="hidden" value="<?php echo $dataBill['country'];?>" />
    <input name="company" type="hidden" value="<?php echo $dataBill['company'];?>" />
    <input name="city" type="hidden" value="<?php echo $dataBill['city'];?>" />
    <input name="add" type="hidden" value="<?php echo $dataBill['add'];?>" />
    <input name="email" type="hidden" value="<?php echo $dataBill['email'];?>" />
    <input name="phone" type="hidden" value="<?php echo $dataBill['phone'];?>" />
    <input name="fax" type="hidden" value="<?php echo $dataBill['fax'];?>" />
<!-- snd billing details -->

<!-- daca detaliile de shipping difera -->
<!-- begin shipping details -->
    <input name="sfname" type="hidden" value="<?php echo $dataShip['sfname'];?>" />
    <input name="slname" type="hidden" value="<?php echo $dataShip['slname'];?>" />
    <input name="scountry" type="hidden" value="<?php echo $dataShip['scountry'];?>" />
    <input name="scompany" type="hidden" value="<?php echo $dataShip['scompany'];?>" />
    <input name="scity" type="hidden" value="<?php echo $dataShip['scity'];?>" />
    <input name="sadd" type="hidden" value="<?php echo $dataShip['sadd'];?>" />
    <input name="semail" type="hidden" value="<?php echo $dataShip['semail'];?>" />
    <input name="sphone" type="hidden" value="<?php echo $dataShip['sphone'];?>" />
    <input name="sfax" type="hidden" value="<?php echo $dataShip['sfax'];?>" />

<!-- end shipping details -->

<input type="hidden" NAME="amount" VALUE="<?php echo  $dataAll['amount'] ?>" SIZE="12" MAXLENGTH="12" />
<input TYPE="hidden" NAME="curr" VALUE="<?php echo  $dataAll['curr'] ?>" SIZE="5" MAXLENGTH="3" />
<input type="hidden" NAME="invoice_id" VALUE="<?php echo  $dataAll['invoice_id'] ?>" SIZE="32" MAXLENGTH="32" />
<input type="hidden" NAME="order_desc" VALUE="<?php echo  $dataAll['order_desc'] ?>" SIZE="32" MAXLENGTH="50" />
<input TYPE="hidden" NAME="merch_id" SIZE="15" VALUE="<?php echo  $dataAll['merch_id'] ?>" />
<input TYPE="hidden" NAME="timestamp" SIZE="15" VALUE="<?php echo  $dataAll['timestamp'] ?>" />
<input TYPE="hidden" NAME="nonce" SIZE="35" VALUE="<?php echo  $dataAll['nonce'] ?>" />
<input TYPE="hidden" NAME="fp_hash" SIZE="40" VALUE="<?php echo  $dataAll['fp_hash'] ?>" />
	<p class="tx_red_mic">Transferring to EuPlatesc.ro gateway</p>
	<p><img src="https://www.euplatesc.ro/plati-online/tdsprocess/images/progress.gif" alt="" title="" onload="javascript:document.gateway.submit()"></p>
<p><a href="javascript:gateway.submit();" class="txtCheckout">Go Now!</a></p>
</form>                                                                 
</div>

<?php endif; ?>
