<?php
  osp_user_menu('payments');

  $history = Params::getParam('history');  // 1 - this month, 2 - this year, 3 - all logs
  $history = ($history > 0 ? $history : 1);
  $payments = ModelOSP::newInstance()->getPaymentsByUser(osc_logged_user_id(), $history);
  $transfers = ModelOSP::newInstance()->getBankTransferByUserId(osc_logged_user_id());

  // Define bank options (placeholders for now)
  $banks = array(
    'cbe' => array('name' => 'CBE', 'iban' => osp_param('bt_iban_cbe') ?: 'CBE_TEST_IBAN'),
    'awash' => array('name' => 'Awash Bank', 'iban' => osp_param('bt_iban_awash') ?: 'AWASH_TEST_IBAN'),
    'mpesa' => array('name' => 'M-Pesa', 'iban' => osp_param('bt_account_mpesa') ?: 'MPESA_TEST_ACCOUNT'),
    'telebirr' => array('name' => 'Telebirr', 'iban' => osp_param('bt_account_telebirr') ?: 'TELEBIRR_TEST_ACCOUNT'),
    'abyssinia' => array('name' => 'Abyssinia Bank', 'iban' => osp_param('bt_iban_abyssinia') ?: 'ABYSSINIA_TEST_IBAN')
  );
?>

<div class="osp-body osp-body-payments">
  <div id="osp-tab-menu">
    <a href="<?php echo osc_route_url('osp-payments', array('history' => 1)); ?>" <?php echo $history == 1 ? 'class="osp-active"' : ''; ?>><?php _e('Last month', 'osclass_pay'); ?></a>
    <a href="<?php echo osc_route_url('osp-payments', array('history' => 2)); ?>" <?php echo $history == 2 ? 'class="osp-active"' : ''; ?>><?php _e('Last year', 'osclass_pay'); ?></a>
    <a href="<?php echo osc_route_url('osp-payments', array('history' => 3)); ?>" <?php echo $history == 3 ? 'class="osp-active"' : ''; ?>><?php _e('All payments', 'osclass_pay'); ?></a>
  </div>
    
  <div class="osp-h2">
    <?php 
      if($history == 1) {
        _e('List of all payments you have made on our site in last month.', 'osclass_pay');
      } else if ($history == 2) {
        _e('List of all payments you have made on our site in last year.', 'osclass_pay');
      } else {
        _e('List of all payments you have made on our site.', 'osclass_pay');
      }
    ?>
  </div>

  <!-- Debugging: Show transfer count -->
  <div>DEBUG: Number of transfers = <?php echo count($transfers); ?></div>

  <div class="osp-table-payments osp-table-transfers">
    <div class="osp-head-row">
      <div class="osp-col source"><?php _e('Source', 'osclass_pay'); ?></div>
      <div class="osp-col code"><?php _e('Transaction ID', 'osclass_pay'); ?></div>
      <div class="osp-col concept"><?php _e('Description', 'osclass_pay'); ?></div>
      <div class="osp-col amount"><?php _e('Amount', 'osclass_pay'); ?></div>
      <div class="osp-col date"><?php _e('Date', 'osclass_pay'); ?></div>
      <div class="osp-col details"> </div>
    </div>

    <?php if(count($payments) > 0 || count($transfers) > 0) { ?>
      <div class="osp-table-wrap">
        <?php if(count($transfers) > 0) { ?>
          <div class="osp-row osp-row-title"><?php _e('Initiated bank transfer payments - AWAITING YOUR PAYMENT!', 'osclass_pay'); ?></div>
          
          <!-- Bank Selection Buttons (Forced Visible) -->
          <div class="osp-bank-selection">
            <p><?php _e('Select a bank to view payment instructions:', 'osclass_pay'); ?></p>
            <div class="osp-bank-buttons">
              <?php foreach($banks as $key => $bank) { ?>
                <button class="osp-bank-btn" data-bank="<?php echo $key; ?>" data-iban="<?php echo osc_esc_html($bank['iban']); ?>">
                  <?php echo osc_esc_html($bank['name']); ?>
                </button>
              <?php } ?>
            </div>
          </div>

          <?php foreach($transfers as $t) { ?>
            <?php 
              $bt_tooltip = sprintf(__('Payment in progress. We are awaiting your bank transfer. <br/>Transaction ID: %s <br/>Variable Symbol: %s <br/> Amount: %s <br/>Once funds are on our account, we complete your payment.', 'osclass_pay'), $t['s_transaction'], $t['s_variable'], osp_format_price($t['f_price'])); 
            ?>

            <div class="osp-row">
              <div class="osp-col source bt-pending osp-has-tooltip" title="<?php echo osc_esc_html($bt_tooltip); ?>"><i class="fa fa-hourglass-o"></i> <?php echo __('Awaiting', 'osclass_pay'); ?></div>
              <div class="osp-col code osp-has-tooltip" title="<?php echo osc_esc_html($t['s_transaction']); ?>"><?php echo $t['s_transaction']; ?></div>
              <div class="osp-col concept osp-has-tooltip" title="<?php echo osc_esc_html($t['s_description']); ?>"><?php echo $t['s_description']; ?></div>
              <div class="osp-col amount"><?php echo osp_format_price($t['f_price'], 9, osp_currency()); ?></div>
              <div class="osp-col date osp-has-tooltip" title="<?php echo osc_esc_html($t['dt_date']); ?>"><?php echo date('j. M', strtotime($t['dt_date'])); ?></div>
              <div class="osp-col details">
                <?php if(osp_cart_string_to_title($t['s_cart']) <> '') { ?>
                  <i class="fa fa-search osp-has-tooltip-right" title="<?php echo osc_esc_html(osp_cart_string_to_title($t['s_cart'])); ?>"></i>
                <?php } ?>
              </div>
              
              <div class="osp-transfer-detail" style="display:none;">
                <?php echo sprintf(__('Transfer amount <b>%s</b> to account <b><span class="osp-iban"></span></b> with variable symbol <b>%s</b>', 'osclass_pay'), osp_format_price($t['f_price'], 9, osp_currency()), $t['s_variable']); ?>
              </div>
            </div>
          <?php } ?>
        <?php } else { ?>
          <div>DEBUG: No transfers found, buttons should still appear below for testing.</div>
          <div class="osp-bank-selection">
            <p><?php _e('Select a bank (test mode):', 'osclass_pay'); ?></p>
            <div class="osp-bank-buttons">
              <?php foreach($banks as $key => $bank) { ?>
                <button class="osp-bank-btn" data-bank="<?php echo $key; ?>" data-iban="<?php echo osc_esc_html($bank['iban']); ?>">
                  <?php echo osc_esc_html($bank['name']); ?>
                </button>
              <?php } ?>
            </div>
          </div>
        <?php } ?>

        <?php if(count($payments) > 0) { ?>
          <?php if(count($transfers) > 0) { ?>
            <div class="osp-row osp-row-title osp-title-alt"><?php _e('Completed payments', 'osclass_pay'); ?></div>
          <?php } ?>
          <?php foreach($payments as $p) { ?>
            <div class="osp-row">
              <div class="osp-col source <?php echo osc_esc_html(strtolower($p['s_source'])); ?>"><?php echo $p['s_source']; ?></div>
              <div class="osp-col code osp-has-tooltip" title="<?php echo osc_esc_html($p['s_code']); ?>"><?php echo $p['s_code']; ?></div>
              <div class="osp-col concept osp-has-tooltip" title="<?php echo osc_esc_html($p['s_concept']); ?>"><?php echo $p['s_concept']; ?></div>
              <div class="osp-col amount"><?php echo osp_format_price($p['i_amount']/1000000000000, 9, $p['s_currency_code']); ?></div>
              <div class="osp-col date osp-has-tooltip" title="<?php echo osc_esc_html($p['dt_date']); ?>"><?php echo date('j. M', strtotime($p['dt_date'])); ?></div>
              <div class="osp-col details">
                <?php if(osp_cart_string_to_title($p['s_cart']) <> '') { ?>
                  <i class="fa fa-search osp-has-tooltip-right" title="<?php echo osc_esc_html(osp_cart_string_to_title($p['s_cart'])); ?>"></i>
                <?php } ?>
              </div>
            </div>
          <?php } ?>
        <?php } ?>
      </div>
    <?php } else { ?>
      <div class="osp-row osp-empty">
        <i class="fa fa-warning"></i><span><?php _e('No payments has been found', 'osclass_pay'); ?></span>
      </div>
    <?php } ?>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  console.log('JavaScript loaded'); // Debugging
  const bankButtons = document.querySelectorAll('.osp-bank-btn');
  console.log('Found ' + bankButtons.length + ' bank buttons'); // Debugging
  const transferDetails = document.querySelectorAll('.osp-transfer-detail');
  const ibanSpans = document.querySelectorAll('.osp-iban');

  bankButtons.forEach(button => {
    button.addEventListener('click', function() {
      const selectedIban = this.getAttribute('data-iban');
      console.log('Selected IBAN: ' + selectedIban); // Debugging
      
      ibanSpans.forEach(span => {
        span.textContent = selectedIban;
      });
      
      transferDetails.forEach(detail => {
        detail.style.display = 'block';
      });
    });
  });
});
</script>

<style>
.osp-bank-selection p { margin: 10px 0; }
.osp-bank-buttons button { padding: 5px 10px; margin: 5px; background-color: #007bff; color: white; border: none; cursor: pointer; }
.osp-bank-buttons button:hover { background-color: #0056b3; }
.osp-transfer-detail { margin-top: 5px; color: #333; }
</style>