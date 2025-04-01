$(document).ready(function(){

  // ON LOCALE CHANGE RELOAD PAGE
  $('body').on('change', 'select.mb-select-locale', function(e){
    window.location.replace($(this).attr('rel') + "&ospLocale=" + $(this).val());
  });


  // REVIEWER NOTE ON BANNER
  $('body').on('click', '.mb-table-banner .mb-banner-accept, .mb-table-banner .mb-banner-reject', function(e){
    e.preventDefault();

    $(this).siblings('form').fadeOut(0);

    if($(this).hasClass('mb-banner-accept')) {
      $(this).siblings('form.mb-approve-form').fadeIn(100).css('margin-right', '-59px');
    } else {
      $(this).siblings('form.mb-reject-form').fadeIn(100).css('margin-right', '-88px');;
    }
  });

  $(document).mouseup(function(e){
    var container = $('form.mb-banner-comment');

    if (!container.is(e.target) && container.has(e.target).length === 0) {
      container.fadeOut(0);
    }
  });


  // SHOW BANNER DETAILS
  $('body').on('click', '.mb-table-banner .mb-expand', function(e){
    $(this).find('i').toggleClass('fa-angle-down fa-angle-up');
    $(this).closest('.mb-line').siblings('.mb-next-line').slideToggle(0);
  });


  // GO TO BANK TRANSFERS
  $('body').on('click', '#mb-move-to-bt', function(e){
    $("html,body").animate({scrollTop: $('.mb-transfer').offset().top - 80}, 200);
  });


  // OPEN PAYMENT METHOD CONFIG
  $('body').on('click', '.mb-method-name', function(e){
    e.preventDefault();

    var block = $(this).parent();

    if(block.hasClass('open')) {
      block.removeClass('open');
    } else {
      $('.mb-method').removeClass('open');
      block.addClass('open');
   }

    return false;
  });


  // USER LOOKUP GROUP
  var name = $('.mb-group-lookup input[name="name"]');

  if(name.length) {
    name.prop('autocomplete', 'off');
    
    name.autocomplete({
      source: group_lookup_base,
      minLength: 0,
      select: function (event, ui) {
        if (ui.item.id == '') {
          return false;
        } else {
          $.getJSON(
            group_lookup_url + ui.item.id,
            {'s_username': name.val()},
            function(data){console.log(data);
              if(data.user.id != 0) {
                $('#name').val(data.user.s_name);
                $('#email').val(data.user.s_email);

                if(data.group.pk_i_id > 0) {
                  $('select#group_update').val(data.group.pk_i_id);
                  $('#group_name').val(data.group.s_name);
                  $('#group_color').css('background', data.group.s_color);
                  $('#expire').val(data.group.dt_expire);
                } else {
                  $('select#group_update').val('');
                  $('#group_name').val('');
                  $('#group_color').css('background', '#F5F8F9');
                  $('#expire').val('');
                }
              } else {
                $('.mb-error-block').val(group_lookup_error);
              }
            }
          );
        }

        $('#id').val(ui.item.id);
        $('select#group_update').val('');
        $('#group_name').val('');
        $('#group_color').css('background', '#F5F8F9');
        $('#expire').val('');
      },
      search: function () {
        $('#id').val('');
        $('select#group_update').val('');
        $('#group_name').val('');
        $('#group_color').css('background', '#F5F8F9');
        $('#expire').val('');
      }
    });

    $('.ui-autocomplete').css('zIndex', 10000);
  }


  // USER LOOKUP WALLET
  var name = $('.mb-user-lookup input[name="name"]');

  if(name.length) {
    name.prop('autocomplete', 'off');
    
    name.autocomplete({
      source: user_lookup_base,
      minLength: 0,
      select: function (event, ui) {
        if (ui.item.id == '') {
          return false;
        } else {
          $.getJSON(
            user_lookup_url + ui.item.id,
            {'s_username': name.val()},
            function(data){
              if(data.user.id != 0) {
                $('#name').val(data.user.name);
                $('#email').val(data.user.email);
                $('#account_amount').val(data.amount);
              } else {
                $('.mb-error-block').val(user_lookup_error);
              }
            }
          );
        }

        $('#id').val(ui.item.id);
      },
      search: function () {
        $('#id').val('');
      }
    });

    $('.ui-autocomplete').css('zIndex', 10000);
  }



  // USER LOOKUP SELLER
  var name = $('.mb-seller-lookup input[name="name"]');

  if(name.length) {
    name.prop('autocomplete', 'off');
    
    name.autocomplete({
      source: seller_lookup_base,
      minLength: 0,
      select: function (event, ui) {
        if (ui.item.id == '') {
          return false;
        } else {
          $.getJSON(
            seller_lookup_url + ui.item.id,
            {'s_username': name.val()},
            function(data){
              if(data.user.id != 0) {
                $('#name').val(data.user.name);
                $('#email').val(data.user.email);

                if(seller_array.indexOf(data.user.id) >= 0) {
                  $('#seller_update option[value="1"]').prop('selected', true);
                }
              } else {
                $('.mb-error-block').val(seller_lookup_error);
              }
            }
          );
        }

        $('#id').val(ui.item.id);
      },
      search: function () {
        $('#id').val('');
      }
    });

    $('.ui-autocomplete').css('zIndex', 10000);
  }


  // REMOVE EMPTY LINE ON GROUP
  $('body').on('click', 'a.mb-group-remove.mb-group-new-line', function(e){
    e.preventDefault();

    $(this).closest('.mb-table-row').slideUp(200);
  });


  // ADD NEW LINE FOR GROUP
  $('body').on('click', 'a.mb-add-group', function(e){
    e.preventDefault();

    var id = -(1000 + Math.floor(Math.random()*10000));
    var placeholder = $('.mb-group-placeholder').html().replace(/-999/g, id);

    $('.mb-table-group').append(placeholder);
    $('.mb-table-group').find('.mb-table-row').last().slideDown(200);
  });


  // REMOVE EMPTY LINE ON PACK
  $('body').on('click', 'a.mb-pack-remove.mb-pack-new-line', function(e){
    e.preventDefault();

    $(this).closest('.mb-table-row').slideUp(200);
  });


  // ADD NEW LINE FOR PACK
  $('body').on('click', 'a.mb-add-pack', function(e){
    e.preventDefault();

    var id = -(1000 + Math.floor(Math.random()*10000));
    var placeholder = $('.mb-pack-placeholder').html().replace(/-999/g, id);

    $('.mb-table-pack').append(placeholder);
    $('.mb-table-pack').find('.mb-table-row').last().slideDown(200);
  });


  // SHOW - HIDE REGIONS
  $('body').on('click', '.mb-table-location > .mb-table-row .mb-location-name', function(e){
    e.preventDefault();
    var level = $(this).parent('.mb-table-row').attr('data-level');
    var id = $(this).parent('.mb-table-row').attr('data-country-code');
    //var parent = $(this).parent('.mb-table-row').attr('data-parent-id');

    if(level == 1) {
      $('.mb-table-location > .mb-table-row[data-country-code="' + id + '"][data-level="2"]').slideToggle(200);
    }
  });


  // CLEAR LOCATION VALUES
  $('body').on('click', '.mb-location-clear', function(e){
    e.preventDefault();

    $('.mb-table-location> .mb-table-row input').each(function(){
      if($(this).hasClass('mb-input-bold') || !$(this).prop('disabled')) {
        $(this).removeClass('mb-input-bold').val('').prop('disabled', false);
      }
    });
  });


  // SHOW ALL LOCATION VALUES
  $('body').on('click', '.mb-location-all', function(e){
    e.preventDefault();

    $('.mb-table-location> .mb-table-row').slideDown(200);
  });


  // SHOW DIFFERENT VALUES BY CATEGORY
  $('body').on('click', '.mb-location-difference', function(e){
    e.preventDefault();

    $('.mb-table-location> .mb-table-row').each(function(){
      var row = $(this);
      var hide = true;

      row.find('input').each(function(){
        if($(this).hasClass('mb-input-bold') || !$(this).prop('disabled')) {
          hide = false;
        }
      });

      if(hide) {
        row.slideUp(200);
      } else {
        row.slideDown(200);
      }
    });
  });


  // CLEAR CATEGORY VALUES
  $('body').on('click', '.mb-category-clear', function(e){
    e.preventDefault();

    $('.mb-table-category > .mb-table-row input').each(function(){
      if($(this).hasClass('mb-input-bold') || !$(this).prop('disabled') || $(this).parent().hasClass('mb-input-dsbl')) {
        $(this).parent().removeClass('mb-input-dsbl');
        $(this).removeClass('mb-input-bold').val('').prop('disabled', false);
      }
    });
  });


  // SHOW ALL CATEGORY VALUES
  $('body').on('click', '.mb-category-all', function(e){
    e.preventDefault();

    $('.mb-table-category > .mb-table-row').slideDown(200);
  });


  // SHOW DIFFERENT VALUES BY CATEGORY
  $('body').on('click', '.mb-category-difference', function(e){
    e.preventDefault();

    $('.mb-table-category > .mb-table-row').each(function(){
      var row = $(this);
      var hide = true;

      row.find('input').each(function(){
        if($(this).hasClass('mb-input-bold') || !$(this).prop('disabled')) {
          hide = false;
        }
      });

      if(hide) {
        row.slideUp(200);
      } else {
        row.slideDown(200);
      }
    });
  });


  // UPDATE SUBCATEGORY PRICES BASED ON PARENT
  $('body').on('keyup', '.mb-table-category input', function(e){
    var level = $(this).closest('.mb-table-row').attr('data-level');
    var parent = $(this).closest('.mb-table-row').attr('data-parent-id');
    var cat = $(this).closest('.mb-table-row').attr('data-category-id');
    var hours = $(this).parent('div').attr('data-hours');
    var fee = $(this).val();

    if(level == 1) {
      $('.mb-table-category > .mb-table-row[data-parent-id="' + cat + '"]').slideDown(200);
      $('.mb-table-category > .mb-table-row[data-parent-id="' + cat + '"] div[data-hours="' + hours + '"]').removeClass('mb-input-dsbl');
      $('.mb-table-category > .mb-table-row[data-parent-id="' + cat + '"] div[data-hours="' + hours + '"] input').prop('disabled', false).css('opacity', 1).css('background', '#ffffff').val(fee);
    }
  });


  // SHOW - HIDE SUBCATEGORIES
  $('body').on('click', '.mb-table-category > .mb-table-row .mb-category-name', function(e){
    e.preventDefault();
    var level = $(this).parent('.mb-table-row').attr('data-level');
    var id = $(this).parent('.mb-table-row').attr('data-category-id');
    var parent = $(this).parent('.mb-table-row').attr('data-parent-id');

    if(level == 1) {
      $('.mb-table-category > .mb-table-row[data-parent-id="' + id + '"]').slideToggle(200);
    }
  });

  // ENABLE DISABLED INPUT
  $('body').on('click', '.mb-category-price, .mb-location-uplift', function(e){
    e.preventDefault();
    $(this).removeClass('mb-input-dsbl');
    $(this).find('input').prop('disabled', false).css('opacity', 1).css('background', '#ffffff').select();
  });


  // CATEGORY MULTI SELECT
  $('.mb-row-select-multiple select').change(function(){
    $(this).closest('.mb-row-select-multiple').find('input[type="hidden"]').val($(this).val());
  });


  // HELP TOPICS
  $('#mb-help > .mb-inside > .mb-row.mb-help > div').each(function(){
    var cl = $(this).attr('class');
    $('label.' + cl + ' span, .mb-table-head > div.' + cl + ' span').addClass('mb-has-tooltip').prop('title', $(this).text());
  });

  $('.mb-row label').click(function() {
    var cl = $(this).attr('class');
    var pos = $('#mb-help > .mb-inside > .mb-row.mb-help > div.' + cl).offset().top - $('.navbar').outerHeight() - 12;;
    $('html, body').animate({
      scrollTop: pos
    }, 1400, function(){
      $('#mb-help > .mb-inside > .mb-row.mb-help > div.' + cl).addClass('mb-help-highlight');
    });

    return false;
  });


  // ON-CLICK ANY ELEMENT REMOVE HIGHLIGHT
  $('body, body *').click(function(){
    $('.mb-help-highlight').removeClass('mb-help-highlight');
  });


  // GENERATE TOOLTIPS
  Tipped.create('.mb-has-tooltip', { maxWidth: 200, radius: false, behavior: 'hide'});
  Tipped.create('.mb-has-tooltip-light', { maxWidth: 200, radius: false, behavior: 'hide'});
  Tipped.create('.mb-has-tooltip-user', { maxWidth: 350, radius: false, size: 'medium', behavior: 'hide'});


  // CHECKBOX & RADIO SWITCH
  $.fn.bootstrapSwitch.defaults.size = 'small';
  $.fn.bootstrapSwitch.defaults.labelWidth = '0px';
  $.fn.bootstrapSwitch.defaults.handleWidth = '50px';

  $(".element-slide").bootstrapSwitch();



  // MARK ALL
  $('input.mb_mark_all').click(function(){
    if ($(this).is(':checked')) {
      $('input[name^="' + $(this).val() + '"]').prop( "checked", true );
    } else {
      $('input[name^="' + $(this).val() + '"]').prop( "checked", false );
    }
  });

});