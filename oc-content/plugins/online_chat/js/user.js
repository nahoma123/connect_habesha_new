// compatibility fix
if (typeof ocUserId === 'undefined') { var ocUserId = 0; }
if (typeof ocRefreshMessage === 'undefined') { var ocRefreshMessage = 5000; }

$(document).ready(function(){

  // OPEN BAN LIST
  $('body').on('click', '.oc-gear, .oc-back-bans', function(e){
    e.preventDefault();

    if($('.oc-chat').hasClass('oc-bans-opened')) {
      $('.oc-chat').removeClass('oc-bans-opened');
    } else {
      $('.oc-chat').addClass('oc-bans-opened').removeClass('oc-init');
      ocShowAllChats();
    }
    
    $('.oc-chat-in .oc-before').hide(0);
  });



  // REMOVE USER BLOCK ON CHAT
  $('body').on('click', '.oc-ban-cancel', function(e){
    e.preventDefault();

    if($(this).closest('.oc-ban-row').attr('data-user-id') == '0') {
      var confirmRemove = confirm(ocRemoveBlockAll);
    } else {
      var confirmRemove = confirm(ocRemoveBlock);
    }
    
    if (confirmRemove) {
      var userId = $(this).closest('.oc-ban-row').attr('data-user-id');
      $(this).closest('.oc-ban-row').remove();
      
      if(parseInt(userId) <= 0) {
        $('.oc-ban-all').text(ocBlockAll).removeClass('oc-active');
      }

      $.ajax({
        type: "POST",
        url: ocAjaxUrl + "&ocRemoveBan=1&blockUserId=" + userId,
        success: function(data) {
          //console.log(data);
        }
      });
    }
  });



  // BLOCK ALL BUTTON
  $('body').on('click', '.oc-ban-all', function(e){
    e.preventDefault();

    if(!$(this).hasClass('oc-active')) {
      $(this).text(ocBlockAllActive).addClass('oc-active');
      ocAddBan(0, ocAllString);

      $.ajax({
        type: "POST",
        url: ocAjaxUrl + "&ocAddBanAll=1",
        success: function(data) {
          //console.log(data);
        }
      });
    } else {
      $(this).text(ocBlockAll).removeClass('oc-active');
      $('.oc-ban-row[data-user-id="0"]').remove();

      $.ajax({
        type: "POST",
        url: ocAjaxUrl + "&ocRemoveBanAll=1",
        success: function(data) {
          //console.log(data);
        }
      });
    }
  });



  // PERFORM CHAT OPTIONS
  $('body').on('click', 'div.oc-opt', function(e){
    e.preventDefault();

    if(!$(this).hasClass('oc-opt-success')) {
      var type = $(this).attr('data-options-action');
      var chatId = $(this).closest('.oc-chat-thread').attr('data-chat-id');
      var box = $(this).closest('.oc-chat-thread');

      // Block user
      if(type == 1) {
        var blockedId = box.find('.oc-message input[name="toId"]').val();
        var blockedName = box.find('.oc-message input[name="toName"]').val();

        $(this).text(ocOptBlock).addClass('oc-opt-success');
        ocAddBan(blockedId, blockedName);

        $.ajax({
          type: "POST",
          url: ocAjaxUrl + "&ocAddBan=1&chatId=" + chatId + "&blockedId=" + blockedId,
          success: function(data) {
            //console.log(data);
          }
        });

      // Email transcript to user
      } else if(type == 2) {
        $(this).text(ocOptEmail).addClass('oc-opt-success');

        $.ajax({
          type: "POST",
          url: ocAjaxUrl + "&ocMailChat=1&chatId=" + chatId,
          success: function(data) {
            console.log(data);
          }
        });
      }

      $(this).siblings('.oc-opt-ico').addClass('oc-opt-success');
    }
  });



  // OPEN CHAT OPTIONS
  $('body').on('click', '.oc-options', function(e){
    e.preventDefault();
    $(this).closest('.oc-chat-thread').find('.oc-options-list').show(0);
  });



  // CLOSE CHAT OPTIONS
  $('body').on('click', '.oc-opt-close', function(e){
    e.preventDefault();
    $(this).closest('.oc-options-list').hide(0);
  });



  // START CHAT
  $('body').on('click', '.oc-before a.oc-submit', function(e){
    e.preventDefault();

    $('.oc-chat').removeClass('oc-init');
    ocMinimizeChats();   // minimize existing chats
    $(this).closest('form.oc-form-first').submit();
  });



  // SUBMIT CHAT MESSAGE ON BUTTON PRESS
  $('body').on('click', '.oc-chat-thread a.oc-submit:not(.oc-disabled)', function(e){
    e.preventDefault();

    if($(this).siblings('textarea').val() != '') {
      $(this).closest('form.oc-form').submit();
    }
  });



  // SUBMIT CHAT MESSAGE ON BUTTON PRESS - disabled
  $('body').on('click', '.oc-chat-thread a.oc-submit.oc-disabled', function(e){
    e.preventDefault();
    return false;
  });


  // REMOVE UNREAD
  $('body').on('click', '.oc-chat', function(e){
    $(this).find('.oc-global-head').removeClass('oc-g-unread');
  });


  // CLOSE CHAT WINDOW
  $('body').on('click', '.oc-global-head > svg, .oc-global-head > span, .oc-global-head > .oc-ico, .oc-global-head > .oc-dir', function(e){
    e.preventDefault();
    
    $(this).closest('.oc-global-head').removeClass('oc-g-unread');
    $('.oc-chat').removeClass('oc-bans-opened');

    if($('.oc-chat').hasClass('oc-closed')) {
      createCookie('ocChatOpened', 1, 1);
      $('.oc-chat').addClass('oc-open').removeClass('oc-closed');
      ocRestoreChats();
    } else {
      createCookie('ocChatOpened', 0, 1);
      $('.oc-chat').addClass('oc-closed').removeClass('oc-open');
    }
    
    if($(this).hasClass('oc-dir')) {
      $('.oc-chat-in .oc-before').hide(0);
    }
  });



  // CLOSE CHAT
  $('body').on('click', '.oc-close', function(e){
    e.preventDefault();

    var confirmDelete = confirm(ocRemoveMessage);
    if (confirmDelete) {
      var chatId = $(this).closest('.oc-chat-thread').attr('data-chat-id');
      var box = $(this).closest('.oc-chat-thread');

      box.remove();
      ocRestoreChats();

      $.ajax({
        type: "POST",
        url: ocAjaxUrl + "&ocChatClose=1&chatId=" + chatId,
        success: function(data) {
          //console.log(data);
        }
      });
    }
  });



  // UPDATE MESSAGE AS READ
  $('body').on('click', '.oc-chat-thread .oc-head > div:not(.oc-options-list), .oc-chat-thread .oc-head > span, .oc-chat-thread .oc-body, .oc-chat-thread .oc-message', function(e){
    e.preventDefault();

    var box = $(this).closest('.oc-chat-thread');
    var chatId = box.attr('data-chat-id');
    box.removeClass('oc-unread');

    createCookie('ocActiveChat', chatId, 1);

    $.ajax({
      type: "POST",
      url: ocAjaxUrl + "&ocChatUpdateRead=1&chatId=" + chatId,
      success: function(data) {
        //console.log(data);
      }
    });
  });



  // CLOSE NEW CHAT WINDOW
  $('body').on('click', '.oc-back-new', function(e){
    e.preventDefault();

    $('.oc-chat').removeClass('oc-init');
    $('.oc-chat-in .oc-before').hide(0);
    ocRestoreChats();   // minimize existing chats
  });



  // CHAT FIRST MESSAGE SUBMIT VIA AJAX 
  $('body').on('submit', 'form.oc-form-first', function(e){
    e.preventDefault();

    var form = $(this);
    var toId = form.find('input[name="toUserId"]').val();
    var toName = form.find('input[name="toUserName"]').val();
    var toImage = form.find('input[name="toUserImage"]').val();
    var message = form.find('textarea').val();

    $.ajax({
      type: "POST",
      data: form.serialize(),
      url: ocAjaxUrl,
      dataType: 'json',

      success: function(response){
        var timestamp = response.timestamp;
        var chatId = response.chatId;
        var messageId = response.messageId;
        
        ocGenerateChat(chatId, messageId, toId, toName, toImage, message, 1, ocUserId);
        form.find('textarea').val('');
      },

      error: function(response){
        //console.log(response);
      },
    });
  });



  // INITIATE CHAT
  $('body').on('click', '.oc-chat-button, .oc-start-chat', function(e) {
    e.preventDefault();

    //if($(this).hasClass('oc-active') && $(this).hasClass('oc-online')) {
    if($(this).hasClass('oc-active')) {
      var toUserId = $(this).attr('data-to-user-id');
      var toUserName = $(this).attr('data-to-user-name');
      var toUserImage = $(this).attr('data-to-user-image');

      var box = $('.oc-chat-in .oc-before');

      ocMinimizeChats();   // minimize existing chats
      $('.oc-chat-in .oc-before').show(0);  // show form
      $('.oc-chat').removeClass('oc-closed').removeClass('oc-bans-opened').addClass('oc-init').addClass('oc-open');

      box.find('.oc-to-user-name').text(toUserName);
      box.find('input[name="toUserId"]').val(toUserId);
      box.find('input[name="toUserName"]').val(toUserName);
      box.find('input[name="toUserImage"]').val(toUserImage);
      box.find('textarea').focus();
    } else {
      return false;
    }
  });



  // GET NEW CHAT MESSAGES
  if(ocUserId > 0) {
    setInterval(function(){ 
      var fromId = ocUserId;

      // Test URL: https://plugins3.abprofitrade.eu/index.php?ajaxRequest=1&ajaxChat=1&ocGetLatest=1&fromId=14998

      $.ajax({
        type: "POST",
        url: ocAjaxUrl + '&ocGetLatest=1&fromId=' + fromId + '&ocLastCheck=' + $('.oc-chat input[name="ocLastCheck"]').val(),
        dataType: 'json',
        success: function(response) {
          var timestamp = response.timestamp;
          var data = response.chats;
          var newMessage = 0;
          
          $('.oc-chat input[name="ocLastCheck"]').val(timestamp);

          if(data.length > 0) {
            for(key in data) {
              var chatId = data[key].pk_i_chat_id;
              var box = $('.oc-chat-thread[data-chat-id="' + chatId + '"]');
              
              var messageId = data[key].pk_i_id;
              var messageBox = box.find('.oc-text[data-id="' + messageId + '"]');

              // IF NEW CHAT AND CHAT BOX DOES NOT EXISTS, GENERATE IT
              if(!box.length) {
                ocGenerateChat(chatId, data[key].pk_i_id, data[key].i_from_user_id, data[key].s_from_user_name, '', data[key].s_text, 2, ocUserId);

                if(data[key].i_from_user_id != ocUserId) {
                  newMessage = 1;
                }
              } else {
                var lastMsgElem = box.find('.oc-body > .oc-chat-offline').prev('.oc-text');
                
                // Create new message, if it does not exists yet
                if(messageBox.length <= 0) {
                  if(box.find('.oc-text[data-id="-1"]').length) {
                    box.find('.oc-text[data-id="-1"]').remove();
                  }
                  
                  box.find('.oc-body .oc-chat-offline').before('<div class="oc-text oc-new-A' + (data[key].i_from_user_id == ocUserId ? ' oc-me' : '') + '" data-id="' + messageId + '">' + data[key].s_text + '</div>');

                  if(data[key].i_from_user_id != ocUserId) {
                    newMessage = 1;
                  }
                }
                
                box.find('.oc-head span em').text(data[key].s_text);
                box.find('.oc-head span em i').remove();

                if(data[key].i_from_user_id != ocUserId) {
                  box.find('.oc-head span em').prepend('<i class="fa fa-angle-right"></i> ');
                }

                box.find('.oc-body').scrollTop(box.find('.oc-body')[0].scrollHeight); 
                box.addClass('oc-unread');
                // box.removeClass('oc-offline');
              }

              if(newMessage == 1) {
                $('.oc-global-head').addClass('oc-g-unread');
              }
              
              $('.oc-chat').removeClass('oc-bans-opened');
            }
          }
          
          // PLAY SOUND
          if(newMessage == 1) {
            ocPlayBeep();
            PageTitleNotification.On(ocNewMessage);
          }
        },

        error: function(data) {
          //console.log(data);
        }
      });  
    }, ocRefreshMessage);
  }



  // CHECK FOR CLOSED-ENDED CHATS
  if(ocUserId > 0) {
    setInterval(function(){ 
      var userId = ocUserId;

      $.ajax({
        type: "POST",
        url: ocAjaxUrl + "&ocClosedChat=1&userId=" + userId,
        dataType: 'json',
        success: function(data) {
          var length = data.length;

          if(length > 0) {
            for(key in data) {
              var chatId = data[key].pk_i_chat_id;
              var box = $('.oc-chat-thread[data-chat-id="' + chatId + '"]');

              if(box.length) {
                box.addClass('oc-ended');
                box.find('textarea').addClass('disabled').attr('disabled', true);
                box.find('.oc-submit').addClass('oc-disabled');
              }
            }
          }
        },

        error: function(data) {
          //console.log(data);
        }
      });  
    }, ocRefreshClosed);
  }



  // CHECK FOR THREAD USER STATUS
  if(ocUserId > 0) {
    setInterval(function(){ 
      ocRefreshChatUsersAvailability(ocUserId);
    }, ocRefreshUser);
  }



  // UPDATE LAST ACTIVE OF USER AFTER XY MINUTES
  if(ocUserId > 0) {
    setInterval(function(){ 
      $.ajax({
        type: "POST",
        url: ocAjaxUrl + "&ocLastActive=1",
        success: function(data) {
          //console.log('User last active datetime updated');
        }
      });  
    }, ocRefreshUser);
  }



  // CHECK INITIATE CHAT BUTTON USER STATUS
  setInterval(function(){
    if($('.oc-chat-button').length || $('.oc-start-chat').length) {
      var userIdsArray = [];
      var userIds = '';

      $('.oc-chat-button, .oc-start-chat').each(function(){
        userIdsArray.push( $(this).attr('data-to-user-id') );
      });

      userIds = userIdsArray.join(',');

      $.ajax({
        type: "POST",
        dataType: 'json',
        url: ocAjaxUrl + "&ocUserButton=1&userId=" + userIds,
        success: function(data) {
          for(key in data) {
            var userId = data[key].i_user_id;
            var active = parseInt(data[key].i_active);
            var button = $('.oc-chat-button[data-to-user-id="' + userId + '"], .oc-start-chat[data-to-user-id="' + userId + '"]');
            var status = $('.oc-user-status[data-user-id="' + userId + '"], .oc-chat-box[data-user-id="' + userId + '"]');

            if(button.length) {
              if(active == 1) {
                button.addClass('oc-online').removeClass('oc-offline');
              } else {
                button.addClass('oc-offline').removeClass('oc-online');
              }
            }


            if(status.length) {
              if(active == 1) {

                status.addClass('oc-online').removeClass('oc-offline');
              } else {
                status.addClass('oc-offline').removeClass('oc-online');
              }
            }
          }
        }
      });
    }
  }, ocRefreshUser);




  // CHAT SUBMIT VIA AJAX 
  $('body').on('submit', 'form.oc-form', function(e){
    e.preventDefault();

    var form = $(this);
    var box = $(this).closest('.oc-chat-thread');

    $.ajax({
      url: ocAjaxUrl,
      type: "POST",
      data: form.serialize(),
      dataType: 'json',
      success: function(response){
        box.find('.oc-body .oc-chat-offline').before('<div data-id="' + response.messageId + '" class="oc-text oc-new-B oc-me">' + ocEscapeHTML(form.find('textarea').val()) + '</div>');
        box.find('.oc-head span em').text(ocEscapeHTML(form.find('textarea').val()));
        form.find('textarea').val('').focus();
        box.find('.oc-body').scrollTop(box.find('.oc-body')[0].scrollHeight);
      },

      error: function(response){
        //console.log(response);
      },
    });
  });



  // RESTORE CHAT WINDOW
  $('body').on('click', '.oc-chat-thread .oc-head > span, .oc-chat-thread .oc-head > div:not(.oc-options-list), .oc-chat-thread .oc-body, .oc-chat-thread .oc-message', function(e){
    e.preventDefault();

    var thread = $(this).closest('.oc-chat-thread');
    var box = $(this).closest('.oc-chat-in');
    var chatId = $(this).attr('data-chat-id');

    $('.oc-chat-thread').removeClass('oc-on');
    box.addClass('oc-on');
    thread.addClass('oc-on');
    thread.removeClass('oc-unread');
    //thread.find('textarea').focus();

    thread.find('.oc-body').scrollTop(thread.find('.oc-body')[0].scrollHeight);
  });



  // BACK TO ALL CHATS
  $('body').on('click', '.oc-back', function(e){
    e.preventDefault();

    $('.oc-chat-in .oc-before').hide(0);
    ocShowAllChats();
  });



  // SCROLL CHAT TO BOTTOM
  $('.oc-chat-thread').each(function(){
    $(this).find('.oc-body').scrollTop($(this).find('.oc-body')[0].scrollHeight);
  });


  // SUBMIT FORM ON ENTER PRESS
  $('body').on('keypress', '.oc-chat-thread textarea:not(.disabled)', function(e){
    if (e.which == 13) {
      if($(this).val() != '') {
        $(this).closest('form.oc-form').submit();
      }

      return false;
    }
  });


  // TURN OFF NOTIFICATION
  $(window).on('blur focus click', function() {
    PageTitleNotification.Off();
  });
});


// GENERATE BAN
function ocAddBan(blockedId, blockedName) {
  var html = '<div class="oc-ban-row" data-user-id="' + blockedId + '"><div class="oc-ban-img"><img src="' + ocDefImg + '"/></div><div class="oc-ban-user">' + blockedName + '</div><i class="fa fa-trash-o oc-ban-cancel"></i></div>';
  $('.oc-ban-empty').before(html);
}



// CLOSE OPENED CHATS
function ocShowAllChats() {
  $('.oc-chat-thread').removeClass('oc-on');
  $('.oc-chat-in').removeClass('oc-on');
  createCookie('ocActiveChat', '', 1);
}


// FUNCTION TO MINIMIZE ALL CHATS
function ocMinimizeChats() {
  $('.oc-chat-in .oc-chat-thread').removeClass('oc-on');
  $('.oc-chat-in').addClass('oc-on');
}


// FUNCTION TO RESTORE ALL CHATS
function ocRestoreChats() {
  $('.oc-chat-in').removeClass('oc-on');
}


// FUNCTION TO GENERATE NEW CHAT WINDOW
function ocGenerateChat(chatId, messageId, toId, toName, toImage, message, type, userId = 0) {
  // type: 1 - generate chat to user that created chat thread; 2 - generate new chat to user that recieve chat 
  var prepare;
  var placeholder = $('.oc-chat-thread-placeholder');
  var original = $('.oc-chat-thread-placeholder').html();
  var fixMessage = '';

  placeholder.find('input[name="toId"]').val(toId);
  placeholder.find('input[name="toName"]').val(toName);
  placeholder.find('.oc-head span strong > span').text(toName);
  placeholder.find('.oc-text').prop('data-id', messageId);

  if(type == 1) {
    placeholder.find('div.oc-me').text(message);
    placeholder.find('.oc-head span em').text(message);
  } else {
    fixMessage = message.replace(/(<|&lt;)br\s*\/*(>|&gt;)/g,' ');

    placeholder.find('div.oc-me').text(ocEscapeHTMLRevert(fixMessage));
    placeholder.find('.oc-head span em').text(message.replace(/(<([^>]+)>)/ig,"")).prepend('<i class="fa fa-angle-right"></i> ');
    
  }

  if(toImage != '') {
    placeholder.find('.oc-head .oc-img-wrap img').attr('src', toImage);
  }

  if(type == 1) {
    $('.oc-chat-in .oc-chat-thread').removeClass('oc-on');
    $('.oc-chat-in').addClass('oc-on');
    placeholder.find('.oc-chat-thread').addClass('oc-on');
    placeholder.find('.oc-head span em i').remove();
  }

  if(type == 2) {
    placeholder.find('div.oc-me').removeClass('oc-me');
    placeholder.find('.oc-chat-thread').addClass('oc-unread');
  }

  prepare = $('.oc-chat-thread-placeholder').html();
  placeholder.html(original);

  //$('.oc-chat-in .oc-chat-thread-empty').before(prepare);
  $('.oc-chat-in .oc-before').after(prepare);
  $('.oc-chat-in > .oc-chat-thread[data-chat-id="-1"] input[name="chatId"]').val(chatId);
  $('.oc-chat-in > .oc-chat-thread[data-chat-id="-1"]').attr('data-chat-id', chatId);
  
  ocRefreshChatUsersAvailability(userId);
}



function ocRefreshChatUsersAvailability(userId) {
  if(userId > 0) {
    $.ajax({
      type: "POST",
      url: ocAjaxUrl + "&ocChatUsersAvailability=1&userId=" + userId,
      dataType: 'json',
      success: function(data) {
        var length = data.length;

        if(length > 0) {
          for(key in data) {
            var chatId = data[key].pk_i_chat_id;
            var active = data[key].i_active;
            var box = $('.oc-chat-thread[data-chat-id="' + chatId + '"]');

            if(box.length) {
              if(active == 1) {
                box.removeClass('oc-offline');

                // if(!box.hasClass('oc-ended')) {
                  // box.find('textarea').removeClass('disabled').attr('disabled', false);
                  // box.find('.oc-submit').removeClass('oc-disabled');
                // }
              } else {
                box.addClass('oc-offline');
                // box.find('textarea').addClass('disabled').attr('disabled', true);
                // box.find('.oc-submit').addClass('oc-disabled');
              }
            }
          }
        }
      },

      error: function(data) {
        //console.log(data);
      }
    });
  }
}


// MANAGE COOKIES VIA JAVASCRIPT
function createCookie(name, value, days) {
  var expires;

  if(days) {
    var date = new Date();
    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
    expires = "; expires=" + date.toGMTString();
  } else {
    expires = "";
  }

  document.cookie = encodeURIComponent(name) + "=" + encodeURIComponent(value) + expires + "; path=/";
}



// PLAY BEEP SOUND ON NEW MESSAGE
function ocPlayBeep() {
  var obj = document.createElement("audio");
  obj.src=ocBaseUrl + "oc-content/plugins/online_chat/audio/beep.mp3";
  obj.volume=0.30;
  obj.autoPlay=false;
  obj.preLoad=true;       
  obj.play();
}


// ESCAPE HTML ENTITIES IN JS
function ocEscapeHTML(s) {
  return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}



// REVERT ESCAPE HTML ENTITIES IN JS
function ocEscapeHTMLRevert(s) {
  return s.replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>');
}


// BROWSER BAR NOTIFICATION
var PageTitleNotification = {
  Vars:{
    OriginalTitle: document.title,
    Interval: null
  },  
  On: function(notification, intervalSpeed){
    var _this = this;
    _this.Vars.Interval = setInterval(function(){
       document.title = (_this.Vars.OriginalTitle == document.title)
                 ? notification
                 : _this.Vars.OriginalTitle;
    }, (intervalSpeed) ? intervalSpeed : 1000);
  },
  Off: function(){
    clearInterval(this.Vars.Interval);
    document.title = this.Vars.OriginalTitle;   
  }
}
