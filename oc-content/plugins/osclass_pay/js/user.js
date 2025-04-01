$(document).ready(function(){


  // SHOW/HIDE MORE DETAILS
  $('body').on('click', 'a.mb-more-details', function(e) { 
    e.preventDefault();
    
    if($(this).hasClass('mb-disabled')) {
      return false;
    }
    
    $(this).find('i').toggleClass('fa-caret-down fa-caret-up');
    $(this).closest('.mb-table-row').find('.mb-table-row-hidden').slideToggle(200);
  });
  

  // HIDE USER LIMITS BLOCK IN USER ACCOUNT
  $('body').on('click', '#osp-limit-box .osp-box-close', function(e) { 
    e.preventDefault();

    var url = $(this).attr('href');
    $(this).closest('#osp-limit-box').slideUp(200);
    
    $.ajax({
      url: url, 
      success: function(result){}
    });
    
    return false;
  });
  
  // UNIFORM SCRIPT FIX
  $('body').on('click', '#uniform-osp-item-promote', function(e) { 
    e.preventDefault();
    $(this).closest('form').submit();
  });
  
  // SEARCH BOX RESET PAGE ID
  $('body').on('change', '#osp-search-box input[type="text"], #osp-search-box input[type="number"], #osp-search-box select', function(e){
    $(this).closest('form').find('input[name="pageId"]').val('');
  });
  
  // USER LIMIT BOX - COPY TO CONTENT AND MAKE VISIBLE
  if(typeof ospTheme !== 'undefined' && $('.osp-limit-box-wrap').length) {
    if(ospTheme == 'gamma') {
      $('div.inside.user_account').prepend($('.osp-limit-box-wrap').html());
      $('#osp-limit-box').show(0);
    } else if(ospTheme == 'beta') {
      $('.body-ua #main').prepend($('.osp-limit-box-wrap').html());
      $('#osp-limit-box').show(0);
    } else {
      if($('div.content').length || $('section.content').length) {
        $('div.content, section.content').prepend($('.osp-limit-box-wrap').html());
        $('#osp-limit-box').show(0);
      } else if($('div#content').length) {
        $('div#content').prepend($('.osp-limit-box-wrap').html());
        $('#osp-limit-box').show(0);
      }
    }
  }

  // WEACCEPT IFRAME
  $('body').on('click', '.weaccept-btn-iframe', function(e){
    e.preventDefault();

    $(this).parent('li').find('#weaccept-iframe, #weaccept-overlay, #weaccept-close').fadeIn(200);
    window.scrollTo(0, 0);
    $('.tpd-tooltip').hide(0);
  });


  // HIDE WEACCEPT IFRAME WHEN CLICK OUTSIDE
  $('body').on('click', '#weaccept-overlay, #weaccept-close', function(e){
    e.preventDefault();

    $(this).parent('li').find('#weaccept-iframe, #weaccept-overlay, #weaccept-close').fadeOut(200);
    return false;
  });


  // USER ACCOUNT TABS FUNCTIONALITY
  $('body').on('click', '#osp-tab-menu > div', function(e){
    e.preventDefault();
    var tabId = $(this).attr('data-tab');
    $('#osp-tab-menu > div').removeClass('osp-active');
    $(this).addClass('osp-active');
    $('div.osp-tab').removeClass('osp-active');
    $('div.osp-tab[data-tab="' + tabId + '"]').addClass('osp-active');
  });


  // ON CLICK ALLOW TO CHANGE QUANTITY
  $('body').on('click', '.osp-cart-col.qty.osp-editable', function(e){
    if(!$(this).hasClass('osp-active')) {
      var qty = $(this).text().replace('x', '');
      $(this).addClass('osp-active').attr('old-qty', qty);
      $(this).html('<input type="text" name="cart-qty" value="' + qty + '"/>');
    }
  });


  // ON CLICKOUT OF QUANTITY, UPDATE CART
  $(document).mouseup(function(e){
    var container = $('.osp-cart-col.qty.osp-editable input[name="cart-qty"]');

    if(container.length) {
      if (!container.is(e.target) && container.has(e.target).length === 0) {
        var qty = parseInt(container.val()) - parseInt(container.closest('.osp-cart-col.qty').attr('old-qty'));
        var cart_code = container.closest('.osp-cart-row').attr('data-code');
        var cart_array = cart_code.split('x');

        cart_array[1] = qty;
  
        var new_cart = cart_array.join('x');
        var cart_url = ospAddCartURL.replace('1x2x3x4x5', new_cart);

        container.parent().removeClass('osp-active').html('<span>' + container.val() + 'x</span>');

        if(qty != 0) {
          window.location.href = cart_url;
        }
      }
    }
  });


  // DISABLE ANY ACTION WHEN CHECKBOX IS READONLY
  $('body').on('click', '.osp-promote-form label.osp-chk input[type="checkbox"]', function(e){
    if($(this).is('[readonly]')) {
      e.preventDefault();
      return false;
    }
  });
  
  // DISABLE ANY ACTION WHEN CLICK ON DISABLED PAY BUTTON
  $('body').on('click', '.osp-pay-button a.osp-disabled', function(e){
    e.preventDefault();
    return false;
  });


  // ON PROMOTION CHANGE, CHECK IT
  $('body').on('change', 'form[name="manage_promote_form"] select', function(e){
    $(this).closest('.osp-pb-line').find('.osp-chk input[type="checkbox"]').prop('checked', true);
  });


  // LOCATION ON CHANGE PUBLISH PAGE - UPDATE PROMOTION PRICES BASED ON REGION OR COUNTRY
  $('body').on('change', '[name="countryId"], [name="regionId"]', function(e){
    var id = $(this).val();
    ospPromoteUpdate(id, '1');
  });


  if(typeof ospLocationSection !== 'undefined' && ospLocationSection == 'item_item_add') {
    $('body').on('change', 'input[name="country"]', function(e){
      var id = $('input[name="countryId"]').val();
      ospPromoteUpdate(id, '2');
    });

    $('body').on('change', 'input[name="region"]', function(e){
      var id = $('input[name="regionId"]').val();
      ospPromoteUpdate(id, '3');
    });


    // veronika theme compatibility (run update after half second to wait for ajax)
    $('body').on('click', '#location-picker .shower .option', function(e){
      if($(this).hasClass('country')) {
        ospPromoteUpdate($(this).attr('data-country'), '4');
      } else if($(this).hasClass('region') || $(this).hasClass('city')) {
        ospPromoteUpdate($(this).attr('data-region'), '5');
      }
    });
  }


  // REPEAT CHANGE
  $('body').on('change', '#601_repeat', function(e){
    var block = $(this).closest('.osp-promote-form .osp-pb-line');
    var ptype = block.attr('data-type');

    if($('[name="regionId"]').val() != '') {
      var id = $('[name="regionId"]').val();
    } else {
      var id = $('[name="countryId"]').val();
    }

    var adjust = ospGetLocationAdjust(ptype, id);
    var price = 0;
    var newPrice = 0;
    var stringPrice = '';

    var durationPrice = ospPriceDeFormat(block.find('.osp-select1 select :selected').attr('data-price-current'));
    var repeat = block.find('.osp-select2 select :selected').val();
    var rDiscount = block.find('.osp-select2 select :selected').attr('data-repeat-discount');

    currentPrice = ospPriceDeFormat($('.finprice_' + ptype).attr('data-price-current'));
    newPrice = durationPrice * repeat;

    if(rDiscount != '') {
      newPrice = newPrice * rDiscount;    
    }

    $('.finprice_' + ptype).text(($('.finprice_' + ptype).text()).replace(ospPriceFormat(currentPrice), ospPriceFormat(newPrice)));
    $('.finprice_' + ptype).attr('data-price-current', ospPriceFormat(newPrice));
    $('.finprice_' + ptype).attr('data-price', ospPriceFormat(newPrice/adjust));
  });


  // DURATION CHANGE
  $('body').on('change', '#601_duration, #201_duration, #401_duration', function(e){
    var block = $(this).closest('.osp-promote-form .osp-pb-line');
    var ptype = block.attr('data-type');

    if($('[name="regionId"]').val() != '') {
      var id = $('[name="regionId"]').val();
    } else {
      var id = $('[name="countryId"]').val();
    }

    var adjust = ospGetLocationAdjust(ptype, id);
    var price = 0;
    var newPrice = 0;
    var stringPrice = '';

    if(block.find('.osp-select1 select').length) {
      var durationPrice = block.find('.osp-select1 select :selected').attr('data-price-current');
    } else {
      var durationPrice = block.find('.osp-select select :selected').attr('data-price-current');
    }

    currentPrice = ospPriceDeFormat($('.finprice_' + ptype).attr('data-price-current'));
    newPrice = ospPriceDeFormat(durationPrice);

    if(ptype == '601') {
      var repeat = block.find('.osp-select2 select :selected').val();
      var rDiscount = block.find('.osp-select2 select :selected').attr('data-repeat-discount');
      newPrice = newPrice  * repeat;

      if(rDiscount != '') {
        newPrice = newPrice * rDiscount;    
      }
    }

    $('.finprice_' + ptype).text(($('.finprice_' + ptype).text()).replace(ospPriceFormat(currentPrice), ospPriceFormat(newPrice)));
    $('.finprice_' + ptype).attr('data-price-current', ospPriceFormat(newPrice));
    $('.finprice_' + ptype).attr('data-price', ospPriceFormat(newPrice/adjust));
  });


  // SHOW-HIDE PROMOTE OPTIONS ON ITEMPAY PAGE
  $('body').on('click', '.osp-body-itempay .osp-promote-form .osp-h1', function(e){
    e.preventDefault();
    $(this).toggleClass('is-open');
    $(this).siblings('form').slideToggle(200);
  });


  // PROVIDE FUNCTIONALITY TO JQUERY DIALOGS, WHEN CLICK OUTSIDE - CLOSE
  $('body').on('click', '.ui-widget-overlay', function(e){
    $('.ui-dialog-content').dialog('close');
  });


  // GENERATE ADD TO CART URL ON ITEMS
  $('body').on('click', 'a.osp-item-to-cart', function(e){
    e.preventDefault();
    var enableAjaxLoad = 1;    // set to 0 in case of problems with adding products to cart

    var item = $(this).attr('data-item');
    var checks = $('.osp-options[data-item="' + item + '"]').find('input:checkbox:checked');
    var i = 0;
    var url = $(this).attr('href');

    checks.each(function(){
      if(i > 0 && $(this).attr('name') != '601_2') { url += '|'; }
      url += $(this).attr('code');
      i = i + 1;
    });


    if(enableAjaxLoad == 1) {
      var button = $(this);
      var buttonText = $(this).html();

      if(button.hasClass('osp-in-cart')) {
        window.location.href = ospButtonCartURL;
        return false;
      }

      if(i > 0) {
        button.addClass('osp-in-cart').attr('href', ospButtonCartURL).html('<i class="fa fa-check-circle"></i> ' + ospButtonInCart);
      } else {
        button.addClass('osp-notin-cart').html('<i class="fa fa-exclamation-circle osp-cart-problems"></i> ' + ospButtonNotInCart);

        setTimeout(function(){ 
          button.removeClass('osp-notin-cart').html(buttonText);
        }, 1500);

        return false;
      }

      // Now submit cart content via ajax, no need to reload page
      $.ajax({
        url: url, 
        success: function(result){
          console.log(result);
        }
      });

      return false;

    } else {
      if(i == 0) {
        return false;
      }

      window.location.href = url;
    }
  });


  // ITEM OPEN PROMOTE OPTIONS
  $('body').on('click', '.osp-promote', function(e){
    e.preventDefault();

    $(this).closest('.osp-item').addClass('open');
    $(this).closest('.osp-item').find('.osp-options').stop(false, false).fadeIn(100);
  });

  $(document).mouseup(function (e){
    var container = $('.osp-options');

    if (!container.is(e.target) && container.has(e.target).length === 0) {
      container.closest('.osp-item').removeClass('open');
      container.closest('.osp-item').find('.osp-options').stop(false, false).fadeOut(100);
    }
  });



  // USER TOOLTIPS
  Tipped.create('.osp-has-tooltip', { maxWidth: 200, radius: false, behavior: 'hide'});
  Tipped.create('.osp-has-tooltip-left', { maxWidth: 200, radius: false, position: 'topleft', behavior: 'hide'});
  Tipped.create('.osp-has-tooltip-right', { maxWidth: 360, radius: false, position: 'topright', behavior: 'hide'});

  
  // UPDATE GROUP PRICE BASED ON EXPIRATION DAYS
  $('body').on('change', 'select.osp-select-group', function(e){
    e.preventDefault();

    var elem = $(this).find(':selected');
    var days = parseFloat(elem.attr('data-days'));
    var price = parseFloat(elem.attr('data-price'));
    var price_text = elem.attr('data-price-formatted');
    var group = parseInt($(this).attr('data-group'));
    var repeat = parseInt($(this).val());
    
    //var days = parseFloat($('#osp_group_days_' + group).val());
    //var orig = parseFloat($('#osp_group_price_' + group).val());
    //var orig_last = parseFloat($('#osp_group_price_last_' + group).val()).toFixed(2);

    //var modif = parseFloat(orig * repeat).toFixed(2);
    //var price_text = $('.osp-group[data-group="' + group + '"]').find('.osp-price').text();
    //price_text = price_text.replace(orig_last, modif);

    $('.osp-group[data-group="' + group + '"]').find('.osp-price').text(price_text);
    //$('.osp-group[data-group="' + group + '"]').find('#osp_group_price_last_' + group).val(modif);
    //$('.osp-group[data-group="' + group + '"]').find('.osp-cost > span').text(repeat*days);
    $('.osp-group[data-group="' + group + '"]').find('.osp-cost > span').text(days);

    //osp_url_update($('.osp-group[data-group="' + group + '"]').find('a.osp_cart_add'), repeat, 2);
    //osp_url_update($('.osp-group[data-group="' + group + '"]').find('a.osp_cart_add'), parseInt(days * repeat), 4);
    osp_url_update($('.osp-group[data-group="' + group + '"]').find('a.osp_cart_add'), parseInt(days), 4);
  });

  
  // UPDATE ADD TO CART URL
  function osp_url_update(elem, new_value, position) {
    position = parseInt(position - 1);
    var url = elem.attr('href');
    var params = String(getURLParams(url, 'product'));   // get products
    var params_array = params.split('x');                // to array

    params_array[position] = new_value.toString();

    var new_params = params_array.join('x');
    var new_url = url.replace(params, new_params);

    elem.prop('href', new_url);
  }


  // GUARANTEE FUNCTIONALITY WHEN CLICK ON LABEL, IT MARK CHECKBOX
  // MAKE CHECKBOXES BEHAVE LIKE RADIO BUTTON GROUP
  $('body').on('click', '.osp-options label', function(e){
    e.preventDefault();

    var cb = $(this).find('input[type="checkbox"]');
    var cbp = $(this).closest('.osp-options');

    if(cb.prop('checked')) {
      cb.prop('checked', false);
 
      if (cb.prop('name').indexOf('601') >= 0) {
        var repub = 'input:checkbox[name^="601"]';
        $(repub).prop('checked', false);
      }
    } else {
      var group = 'input:checkbox[name="' + cb.attr('name') + '"]';
      $(group).prop('checked', false);
      cb.prop('checked', true);

      // Make sure if user select republish, that repeat or duration is checked at same time
      if (cb.prop('name').indexOf('601') >= 0 && cb.prop('checked')) {
        if(cb.prop('name') == '601_1') {
          var countChecked = cbp.find('input:checkbox[name="601_2"]:checked').length;
          var firstCheck = cbp.find('input:checkbox[name="601_2"]').first();
        } else {
          var countChecked = cbp.find('input:checkbox[name="601_1"]:checked').length;
          var firstCheck = cbp.find('input:checkbox[name="601_1"]').first();
        }

        if(countChecked == 0) {
          firstCheck.prop('checked', true);
        }
      }
    }
  });





  // ON LOAD CHECK PRICES
  //$('form[name="manage_promote_form"] .osp-chk input[type="checkbox"]:checked').each(function(e) {
  //  $(this).closest('.osp-pb-line').find('select').change();
  //});


});




// GET LOCATION ADJUST
function ospGetLocationAdjust(ptype, locId = '') {
  if(locId != '' && typeof ospLoc !== 'undefined') {
    var code_reg = 'R_' + locId + '_' + ptype;
    var code_ctr = 'C_' + locId + '_' + ptype;

    if(typeof ospLoc[code_reg] !== 'undefined') {
      return adjust = 1 + ospLoc[code_reg]/100;
    }
  
    if(typeof ospLoc[code_ctr] !== 'undefined') {
      return adjust = 1 + ospLoc[code_ctr]/100;
    }
  }

  return adjust = 1;
}


// UPDATE PROMOTE PRICES BASED ON REGION OR COUNTRY
function ospPromoteUpdate(id, cmd = '') {
  if(typeof id !== 'undefined' && id != '') {
    $('.osp-promote-form .osp-pb-line').each(function(){
      var block = $(this);
      var ptype = block.attr('data-type');

      var adjust = ospGetLocationAdjust(ptype, id);
      var price = 0;
      var newPrice = 0;
      var stringPrice = '';
      var oldText = '';
      var newText = '';
      
      if(ptype == '101' || ptype == '501' || ptype == '201' || ptype == '401' || ptype == '601') {
        currentPrice = ospPriceDeFormat($('.finprice_' + ptype).attr('data-price-current'));
        newPrice = ospPriceDeFormat($('.finprice_' + ptype).attr('data-price')) * adjust;
        $('.finprice_' + ptype).text(($('.finprice_' + ptype).text()).replace(ospPriceFormat(currentPrice), ospPriceFormat(newPrice)));
        $('.finprice_' + ptype).attr('data-price-current', ospPriceFormat(newPrice));
      } 

      if(ptype == '201' || ptype == '401' || ptype == '601') {
        block.find('select > option').each(function(){
          oCurrentPrice = String($(this).attr('data-price-current'));
          oNewPrice = ospPriceDeFormat($(this).attr('data-price-orig')) * adjust;
          oldText = $(this).text();
          newText = oldText.replace(ospPriceFormat(oCurrentPrice), ospPriceFormat(oNewPrice));

          $(this).text(newText);
          $(this).attr('data-price-current', ospPriceFormat(oNewPrice));
        });
      } 
    });

    if(typeof ospIsDebug !== 'undefined') {
      if(ospIsDebug) {
        console.log('Prices reloaded for location ' + id + ', command ' + cmd);
      }
    }
  }
}



// GET URL PARAMETERS
function getURLParams(url, k){
  var p={};
  //location.search.replace(/[?&]+([^=&]+)=([^&]*)/gi,function(s,k,v){p[k]=v})
  url.replace(/[?&]+([^=&]+)=([^&]*)/gi,function(s,k,v){p[k]=v});
  return k?p[k]:p;
}


// REPLACE AFTER XY CHARACTER
String.prototype.ospReplaceAfter=function(sOld, sNew, index = 5) {
  if(this.length > index) {
    return this.substr(0, index) + (this.substr(index)).replace(sOld, sNew);
  } else {
    return this;
  }
}


// GET DECIMAL PLACES
function ospGetDecimals() {
  if($('input[name="ospDecimals"]').length) {
    return $('input[name="ospDecimals"]').val();
  }

  return 2;
}


// GET DECIMAL SYMBOL
function ospGetDecimalSymbol() {
  if($('input[name="ospDecimalSymbol"]').length) {
    return $('input[name="ospDecimalSymbol"]').val();
  }

  return '.';
}


// GET THOUSANDS SEPARATOR
function ospGetThousandSymbol() {
  if($('input[name="ospThousandSymbol"]').length) {
    return $('input[name="ospThousandSymbol"]').val();
  }

  return ' ';
}


// FORMAT PRICE
function ospPriceFormat(num) {
  num = String(num).replace(' ', '').replace(ospGetThousandSymbol(), '').replace(ospGetDecimalSymbol(), '.');
  numF = parseFloat(num);

  return (
    numF
      .toFixed(ospGetDecimals())              // set decimal digits
      .replace('.', ospGetDecimalSymbol())    // set decimal point
      .replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1' + ospGetThousandSymbol())
  ) 
}


// DE-FORMAT PRICE
function ospPriceDeFormat(num) {
  num = String(num);

  if (num.indexOf(ospGetThousandSymbol()) >= 0) {
    num = num.split(ospGetThousandSymbol()).join("");   // remove thousands separator
  }

  num = num.replace(ospGetDecimalSymbol(), '.')         // set decimal point to "."

  return parseFloat(num);
}