$(document).ready(function() {
  if(typeof phlIsLogin !== 'undefined' && typeof phlEnable !== 'undefined') {
    if(phlIsLogin == 1 && phlEnable == 1) {
      $('form').each(function() {
        if($(this).find('input[name="page"]').length) {
          if($(this).find('input[name="page"]').val() == 'login') {
            $(this).find('label[for="email"]').text(phlEmailLabel);
            $(this).find('input[name="email"]').prop({type:"text"});
          }
        }
      });
    }
  }
});