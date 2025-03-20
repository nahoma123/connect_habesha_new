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

  $banks = array(
    'cbe' => array('name' => 'CBE', 'account' => osp_param('bt_iban_cbe') ?: 'CBE_TEST_IBAN'),
    'awash' => array('name' => 'Awash Bank', 'account' => osp_param('bt_iban_awash') ?: 'AWASH_TEST_IBAN'),
    'mpesa' => array('name' => 'M-Pesa', 'account' => osp_param('bt_account_mpesa') ?: 'MPESA_TEST_ACCOUNT'),
    'telebirr' => array('name' => 'Telebirr', 'account' => osp_param('bt_account_telebirr') ?: 'TELEBIRR_TEST_ACCOUNT'),
    'abyssinia' => array('name' => 'Abyssinia Bank', 'account' => osp_param('bt_iban_abyssinia') ?: 'ABYSSINIA_TEST_IBAN')
  );

  if($product_type[0] == OSP_TYPE_MULTIPLE && $amount >= $min) {
    $description = @$data['concept'];
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

    header('Content-Type: text/html; charset=UTF-8');
    echo '<!DOCTYPE html>';
    echo '<html>';
    echo '<head>';
    echo '<title>' . __('Bank Transfer Payment', 'osclass_pay') . '</title>';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '</head>';
    echo '<body>';
?>

<div class="osp-body osp-body-bank-selection">
  <div class="osp-container">
    <h2 class="osp-title"><?php _e('Complete Your Bank Transfer', 'osclass_pay'); ?></h2>
    <p class="osp-subtitle"><?php _e('Please select a bank to view payment instructions:', 'osclass_pay'); ?></p>
    
    <div class="osp-bank-buttons">
      <?php foreach($banks as $key => $bank) { ?>
        <button class="osp-bank-btn" data-bank="<?php echo $key; ?>" data-account="<?php echo osc_esc_html($bank['account']); ?>">
          <?php echo osc_esc_html($bank['name']); ?>
        </button>
      <?php } ?>
    </div>

    <div class="osp-transfer-detail">
      <ul class="osp-detail-list">
        <li><strong><?php _e('Transfer Amount:', 'osclass_pay'); ?></strong> <?php echo osp_format_price($amount); ?></li>
        <li><strong><?php _e('To Account:', 'osclass_pay'); ?></strong> <span class="osp-account"></span></li>
        <li><strong><?php _e('Variable Symbol:', 'osclass_pay'); ?></strong> <?php echo $variable_symbol; ?></li>
        <li><strong><?php _e('Transaction ID:', 'osclass_pay'); ?></strong> <?php echo $transaction_id; ?></li>
      </ul>
      <p class="osp-note"><?php _e('Once funds are received, your payment will be completed. This may take up to 3 days.', 'osclass_pay'); ?></p>
      <a href="<?php echo $url; ?>" class="osp-continue-btn"><?php _e('Continue', 'osclass_pay'); ?></a>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const bankButtons = document.querySelectorAll('.osp-bank-btn');
  const transferDetail = document.querySelector('.osp-transfer-detail');
  const accountSpan = document.querySelector('.osp-account');

  bankButtons.forEach(button => {
    button.addEventListener('click', function() {
      const selectedAccount = this.getAttribute('data-account');
      accountSpan.textContent = selectedAccount;
      transferDetail.style.display = 'block';
      
      // Highlight selected button
      bankButtons.forEach(btn => btn.classList.remove('active'));
      this.classList.add('active');
    });
  });
});
</script>

<style>
/* General Styles */
.osp-body-bank-selection {
  background-color: #f4f6f8;
  min-height: 50vh;
  display: flex;
  justify-content: center;
  align-items: center;
  margin: 0;
}

.osp-container {
  max-width: 600px;
  width: 100%;
  padding: 20px;
  background: #ffffff;
  border-radius: 10px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  text-align: center;
}

/* Title and Subtitle */
.osp-title {
  font-size: 24px;
  color: #333;
  margin-bottom: 10px;
}

.osp-subtitle {
  font-size: 16px;
  color: #666;
  margin-bottom: 20px;
}

/* Bank Buttons */
.osp-bank-buttons {
  font-family: "Comfortaa", sans-serif;
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 10px;
}

.osp-bank-btn {
  padding: 12px 20px;
  font-size: 14px;
  color: #fff;
  background-color: #007bff;
  border: none;
  border-radius: 25px;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.osp-bank-btn:hover {
  background-color: #0056b3;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.osp-bank-btn.active {
  background-color: #28a745;
  transform: scale(1.05);
}

/* Transfer Details */
.osp-transfer-detail {
  display: none;
  margin-top: 25px;
  padding: 20px;
  background: #f9f9f9;
  border-radius: 8px;
  border: 1px solid #e0e0e0;
  animation: fadeIn 0.3s ease-in;
  text-align: left;
}

.osp-detail-list {
  list-style: none;
  padding: 0;
  margin: 0 0 15px 0;
}

.osp-detail-list li {
  margin-bottom: 10px;
  font-size: 15px;
  color: #333;
}

.osp-detail-list strong {
  color: #007bff;
  min-width: 120px;
  display: inline-block;
}

.osp-note {
  font-size: 13px;
  color: #777;
  margin: 10px 0;
  text-align: center;
}

/* Continue Button */
.osp-continue-btn {
  display: inline-block;
  padding: 10px 20px;
  font-size: 14px;
  color: #fff;
  background-color: #28a745;
  text-decoration: none;
  border-radius: 25px;
  transition: background-color 0.3s ease;
}

.osp-continue-btn:hover {
  background-color: #218838;
}

/* Animation */
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

/* Responsive Design */
@media (max-width: 480px) {
  .osp-container {
    margin: 10px;
    padding: 15px;
  }
  .osp-title {
    font-size: 20px;
  }
  .osp-bank-btn {
    padding: 10px 15px;
    font-size: 13px;
  }
  .osp-detail-list li {
    font-size: 14px;
  }
  .osp-detail-list strong {
    min-width: 100px;
  }
}
</style>

<?php
    echo '</body>';
    echo '</html>';
    exit;
  } else {
    $url = osp_pay_url_redirect($product_type[0]);
    osc_add_flash_error_message(__('There was problem recognizing your product, please try bank transfer payment again from your cart.', 'osclass_pay'));
    osp_redirect($url);
  }
?>