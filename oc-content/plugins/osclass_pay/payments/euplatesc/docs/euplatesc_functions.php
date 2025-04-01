<?php
function hmacsha1($key,$data) {
   $blocksize = 64;
   $hashfunc  = 'md5';
   
   if(strlen($key) > $blocksize)
     $key = pack('H*', $hashfunc($key));
   
   $key  = str_pad($key, $blocksize, chr(0x00));
   $ipad = str_repeat(chr(0x36), $blocksize);
   $opad = str_repeat(chr(0x5c), $blocksize);
   
   $hmac = pack('H*', $hashfunc(($key ^ $opad) . pack('H*', $hashfunc(($key ^ $ipad) . $data))));
   return bin2hex($hmac);

}

// ===========================================================================================
function euplatesc_mac($data, $key)
{
  $str = NULL;

  foreach($data as $d)
  {
   	if($d === NULL || strlen($d) == 0)
  	  $str .= '-'; // valorile nule sunt inlocuite cu -
  	else
  	  $str .= strlen($d) . $d;
  }
     
  // ================================================================
  $key = pack('H*', $key); // convertim codul secret intr-un string binar
  // ================================================================

// echo " $str " ;

  return hmacsha1($key, $str);
}

$pkgId = Params::getParam('pkgId');
$conn = getConnection() ;
$packageIfo = $conn->osc_dbFetchResult("select * from %st_packages where package_id=%d AND status='1' ",DB_TABLE_PREFIX,$pkgId);

$currency = $packageIfo['currency_code'];
$currency = explode(":", $currency);
$currency = $currency[0];

$email = $_POST['email'];

  $dataAll = array(
			'amount'      => $packageIfo['package_cost'],                                                   //suma de plata
			'curr'        => $currency,                                                   // moneda de plata
			'invoice_id'  => str_pad(substr(mt_rand(), 0, 7), 7, '0', STR_PAD_LEFT),  // numarul comenzii este generat aleator. inlocuiti cuu seria dumneavoastra
			'order_desc'  => $packageIfo['package_name'],                                            //descrierea comenzii
                     // va rog sa nu modificati urmatoarele 3 randuri
			'merch_id'    => $mid,                                                    // nu modificati
			'timestamp'   => gmdate("YmdHis"),                                        // nu modificati
 			'nonce'       => md5(microtime() . mt_rand()),                            //nu modificati
); 
  
  $dataAll['fp_hash'] = strtoupper(euplatesc_mac($dataAll,$key));

//completati cu valorile dvs
$dataBill = array(
			'fname'	   => '',      // nume
			'lname'	   => '',   // prenume
			'country'  => '',      // tara
			'company'  => '',   // firma
			'city'	   => '',      // oras
			'add'	     => '',    // adresa
			'email'	   => $email,     // email
			'phone'	   => '',   // telefon
			'fax'	     => '',       // fax
);
$dataShip = array(
			'sfname'       => '',     // nume
			'slname'       => '',  // prenume
			'scountry'     => '',     // tara
			'scompany'     => '',  // firma
			'scity'	       => '',     // oras
			'sadd'         => '',      // adresa
			'semail'       => '',    // email
			'sphone'       => '',  // telefon
			'sfax'	       => '',      // fax
);
