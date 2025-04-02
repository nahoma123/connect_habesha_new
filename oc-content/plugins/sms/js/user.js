$(document).ready(function() {

  setInterval(function(){ 

    // REMOVE DROP BOX IF THERE IS JUST 1 COUNTRY
    if($('.intl-tel-input ul.country-list').find('li.country:not(.preferred)').length <= 1) {
      $('.intl-tel-input ul.country-list').remove();
      $('.intl-tel-input .iti-arrow').remove();
    }

    // REMOVE DISABLED FLAG ON BUTTON IF ENTERED PHONE IS VALID
    if($('input[name="phoneNumber"]').hasClass('valid')) {
      $('button.sms-send-code').removeClass('disabled').prop('disabled', false);
    } else if($('input[name="phoneNumber"]').hasClass('error')) {
      $('button.sms-send-code').addClass('disabled').prop('disabled', true);
    }
  } , 200);

  
  // RESEND SMS FROM STEP 2
  $('body').on('click', 'a.sms-button-resend', function(e){
    e.preventDefault();

    if(!$(this).hasClass('disabled')) {
      $('.sms-verify .sms-step1 .sms-button').removeClass('loading').removeClass('disabled');

      $(this).addClass('loading');
      $(this).find('.txt').text(smsResendSent);
      $(this).find('.counter').text('');

      $('.sms-verify .sms-step1 .sms-button').click();
    }

    return false;
  });


  // VALIDATE VERIFICATION FORM - STEP 1
  setInterval(function(){ 
    if($('.sms-verify .sms-step1 input.error').length || $('.sms-verify .sms-step1 input.sms-input-field').val() == '') {
      $('.sms-verify .sms-step1 .sms-button').addClass('disabled').prop('disabled', true);
    } else {
      if(!$('.sms-verify .sms-step1 .sms-button').hasClass('loading') && $('.sms-verify .sms-step1 .sms-button').is(':disabled') && !$('.sms-verify .sms-step1 input.error').length || ($('.sms-verify .sms-row.sms-error').text()).trim().length) {   // code resent, do not enable button
        $('.sms-verify .sms-step1 .sms-button').removeClass('disabled').removeClass('loading').prop('disabled', false);
      }
    }
  } , 200);


  $('body').on('click', '.sms-verify .sms-step1 .sms-button', function(e){
    if($(this).hasClass('disabled')) {
      $('.sms-verify .sms-step1 input.error:visible:first').focus();
      return false;

    } else {

      // SEND SMS, SAVE PHONE NUMBER TO USER PROFILE, GENERATE VERIFICATION CODE
      e.preventDefault();

      $(this).addClass('disabled').addClass('loading').prop('disabled', true);

      $('.sms-phone-code-sent').text($(this).closest('form').find('input[name="phoneNumber"]').val());
      $('.sms-verify .sms-error').hide(0).text('');

      $.ajax({
        url: $(this).closest('form').attr('action'),
        type: "POST",
        dataType: "JSON",
        cache: false,
        data: $(this).closest('form').serialize(),
        success: function(response){
          //console.log(response);

          if(response && response.status && response.status == 'OK') {
            $('.sms-verify .sms-step1').fadeOut(100, function() {
              $('.sms-verify .sms-step2').fadeIn(100);

              // demo mode
              if(response.message != '') {
                $('.sms-verify .sms-success').show(0).html(response.message);
              }

              $('.sms-verify .sms-step1 .sms-button').removeClass('disabled').removeClass('loading').prop('disabled', false);
            });

          } else {
            $('.sms-verify .sms-error').show(0).html(response.message);
          }  

          //$('.sms-verify .sms-step1 .sms-button').removeClass('disabled').removeClass('loading').prop('disabled', false);


          // countdown to send new verification sms
          var counter = 60;
          var interval = setInterval(function() {
            counter--;

            if(counter <= 0) {
              clearInterval(interval);
              $('.sms-button-resend').removeClass('disabled').removeClass('loading').prop('disabled', false);
              $('.sms-button-resend .txt').html(smsResendReady);  
              $('.sms-button-resend .counter').html('');  

            } else {
              $('.sms-button-resend').addClass('disabled').removeClass('loading').prop('disabled', true);
              $('.sms-button-resend .txt').html(smsResendStart);  
              $('.sms-button-resend .counter').html(counter);
            }
          }, 1000);
 
        },
        error: function(response) {
          //$('.sms-verify .sms-error').show(0).text(response);
          $(this).removeClass('disabled').removeClass('loading').prop('disabled', false);
          $('.sms-verify .sms-error').show(0).text(response.responseText == '' ? 'ERROR' : response.responseText);

          // countdown to send new verification sms
          var counter = 60;
          var interval = setInterval(function() {
            counter--;

            if(counter <= 0) {
              clearInterval(interval);
              $('.sms-button-resend').removeClass('disabled').removeClass('loading').prop('disabled', false);
              $('.sms-button-resend .txt').html(smsResendReady);  
              $('.sms-button-resend .counter').html('');  

            } else {
              $('.sms-button-resend').addClass('disabled').removeClass('loading').prop('disabled', true);
              $('.sms-button-resend .txt').html(smsResendStart);  
              $('.sms-button-resend .counter').html(counter);
            }
          }, 1000);

          console.log(response);
        }
      });

     }
  });




  // VALIDATE VERIFICATION FORM - STEP 2
  setInterval(function(){ 
    var hasEmpty = false;

    $('.sms-verify .sms-step2 input.sms-code').each(function() {
      if($(this).val() == '') {
        hasEmpty = true;
      }

      if(hasEmpty) {
        $('.sms-verify .sms-step2 .sms-button').addClass('disabled').prop('disabled', true);
      } else {
        $('.sms-verify .sms-step2 .sms-button').removeClass('disabled').removeClass('loading').prop('disabled', false);
      }
    });
  } , 200);


  $('body').on('click', '.sms-verify .sms-step2 .sms-button', function(e){
    if($(this).hasClass('disabled')) {
      $('.sms-verify .sms-step2 input:empty:visible:first').focus();
      return false;

    } else {

      // VERIFY CODE
      e.preventDefault();

      $(this).addClass('disabled').addClass('loading').prop('disabled', true);

      $.ajax({
        url: $(this).closest('form').attr('action'),
        type: "POST",
        dataType: "JSON",
        data: $(this).closest('form').serialize(),
        success: function(response){
          //console.log(response);

          if(response && response.status && response.status == 'OK') {
            $('.sms-verify .sms-error').hide(0).text('');
            $('.sms-verify .sms-success').show(0).text(response.message);

            setTimeout(function(){ 
              window.location.href = response.url;
            }, 1500);

            $('.sms-verify .sms-step2').fadeOut(200);
          } else {
            $('.sms-verify .sms-error').show(0).text(response.message);
          }  
 
        },
        error: function(response) {
          $('.sms-verify .sms-error').show(0).text(response);
          $(this).removeClass('disabled').removeClass('loading').prop('disabled', false);

          console.log('ERROR', response);
        }
      });
    }
  });


  // COPY PHONE NUMBER BETWEEN STEPS
  $('body').on('keyup change', '.sms-verify .sms-step1 input[name="phoneNumber"]', function(){
    $('.sms-verify .sms-step2 input[name="phoneNumber"]').val($(this).val());
  });


  // ON CODE ENTER MOVE TO NEXT
  $('body').on('keyup', '.sms-verify .sms-step2 input.sms-code', function(){
    $(this).next('input.sms-code').focus().select();
  });


  // ADD CLASS TO CHANGE BACKGROUND
  if($('.sms-verify').length) {
    $('body').addClass('sms-customize-body');
  }

});