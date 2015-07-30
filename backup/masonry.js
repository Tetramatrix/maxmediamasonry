
/***************************************************************
*  Copyright notice
*
*  (c) 2011 Chi Hoang <info@chihoang.de>
*  All rights reserved
*
***************************************************************/
var brick_stack = [];
 
function hide_summary (ele)
{
  var content = $(ele).find(">div");
  var summary = $(ele).find(".summary");
  content.animate({
      top: 0 - summary.height()
  }, function() {
      content.css({
          position: "static"
      });
      summary.hide();
  })
}

$(document).ready(function()
{ 
  // First time
  $.get( $("#menu li:first").find("a").attr('href'), function(response) 
  {
    // Start masonry animated
    $('#container').masonry({
      itemSelector: '.brick',
      columnWidth: 100,
      isAnimated: false
    }); 
    $.each(eval ("("+response+")"), function(idx, ele)
    {
      $('#container').prepend($("#brickTemplate").tmpl(ele)).masonry('reload');
      $('#container').imagesLoaded(function()
      {
        $('.brick').mousemove(function()
        {
          var content = $(this).find(">div");
          var summary = $(this).find(".summary");
          if (!content.is(":animated") && summary.is(":not(:visible)"))
          {
            content.css({
                position: "relative",
                top: 0 - summary.height()
            });
            summary.show();
            brick_stack.unshift(this);
            content.animate({
                top: 0
            })
            while (brick_stack.length > 1)
            {
              hide_summary(brick_stack.pop());
            }
          }
        });
      });
    });
    $("#menu li:first").removeClass('ref_no').addClass('ref_act');
    var theHref = $("#menu li:first").find('a').attr("href").replace(/&switch=.*/g,'');
    $("#menu li:first").find('a').attr("href", theHref + "&switch=off");
  });

  //$("#container").css({"visibility":"visible"});
  //$('#container').coinslider({
  //  width: 1200,
  //  height: 1800,
  //  navigation: false,
  //  delay: 5000,
  //  links: false
  //});
 
  // Start masonry animated
  $('#container').masonry({
    itemSelector: '.brick',
    columnWidth: 100,
    isAnimated: !Modernizr.csstransitions,
    animationOptions:
    {
         duration: 500,
         easing: 'linear',
         queue: false
    }
  });
  
  // Menu hover script
  $("#menu li").hover(
    function () {
      
      if ( $(this).hasClass('ref_no') )
      {
        $(this).removeClass('ref_no').addClass('ref_hover');  
      }
    }, 
    function () {
      if ( $(this).hasClass('ref_hover') )
      {
        if ( $(this).hasClass('ref_act') )
        {
          $(this).removeClass('ref_hover');
        } else
        {
          $(this).removeClass('ref_hover').addClass('ref_no');
        }
      }
    } 
  );
  
  // Menu toggle script
  $(".refid").toggle(
    function ()
    {
        var n = $(this).text();
        
        // Klick "Alle"-Button
        if ( n == "Alle" )
        {
          $.each( $("#menu li"), function (idx, ele)
          {
            // Alle anderen buttons off
            if ( $(ele).find('a').text() != "Alle" )
            {
              $(ele).removeClass('ref_act').addClass('ref_no');
              var theHref = $(ele).find('a').attr("href").replace(/&switch=.*/g,'');
              $(ele).find('a').attr("href", theHref + "&switch=off");
            } else
            {
              // Alle buttons on
              $(ele).removeClass('ref_no').addClass('ref_act');
              var theHref = $(ele).find('a').attr("href").replace(/&switch=.*/g,'');
              $(ele).find('a').attr("href", theHref + "&switch=on");
            }
          });
        }
        
        var params = "&switch=on";
        
        $.each( $("#menu li"), function (idx, ele)
        {
          if ( $(ele).find('a').text() == n )
          {
            $(ele).removeClass('ref_no').addClass('ref_act');
            var theHref = $(ele).find('a').attr("href").replace(/&switch=.*/g,'');
            $(ele).find('a').attr("href", theHref + params);
         
          } else if ( $(ele).find('a').text() == "Alle" &&
                $(ele).hasClass('ref_act')         
                    )
          {
            params += "&screen=clear";
            $(ele).removeClass('ref_act').addClass('ref_no');
            var theHref = $(ele).find('a').attr("href").replace(/&switch=.*/g,'');
            $(ele).find('a').attr("href", theHref + "&switch=off"); 
          }
        });
    },
    
    function ()
    {
      var n = $(this).text();
      if ( n != "Alle" )
      {
        var params = "&switch=off";
        $.each( $("#menu li"), function (idx, ele)
        {
          if ( $(ele).find('a').text() == n )
          {
            if ( $(ele).hasClass('ref_no') )
            {
              $(ele).removeClass('ref_no').addClass('ref_act');
              var theHref = $(ele).find('a').attr("href").replace(/&switch=.*/g,'');
              $(ele).find('a').attr("href", theHref + "&switch=on&screen=clear");  
            } else
            {
            $(ele).removeClass('ref_act').addClass('ref_no');
            var theHref = $(ele).find('a').attr("href").replace(/&switch=.*/g,'');
            $(ele).find('a').attr("href", theHref + params);
            }
          }
          // Alle-Button Active
          if ( $(ele).hasClass('ref_act') &&
                $(ele).find('a').text() == "Alle" 
                )
          {
            // Clear Screen
            params += "&screen=clear";
            $(ele).removeClass('ref_act').addClass('ref_no');
            var theHref = $(ele).find('a').attr("href").replace(/&switch=.*/g,'');
            $(ele).find('a').attr("href", theHref + "&switch=off");
            
          }
        });
      } else if ( n == "Alle" )
      {            
        $.each( $("#menu li"), function (idx, ele)
        {
          if ( $(ele).find('a').text() == "Alle" )
          {
            $(this).removeClass('ref_no').addClass('ref_act');
            var theHref = $(this).find('a').attr("href").replace(/&switch=.*/g,'');
            $(this).find('a').attr("href", theHref + "&switch=on");
        
          } else if ( $(ele).find('a').text() != "Alle" )
          {
            $(ele).removeClass('ref_act').addClass('ref_no');
            var theHref = $(ele).find('a').attr("href").replace(/&switch=.*/g,'');
            $(ele).find('a').attr("href", theHref + "&switch=off");
          }    
        });   
      }
    }
  );

  // Menu ajax script
  $(".refid").click(function()
  {
    $.get( $(this).attr('href'), function(response)
    {  
      $('#container').masonry(); 
      $.each(eval("("+response+")"), function(idx, ele)
      {
        if ( ele.switch == "on" )
        {
          if ( ele.Additem == "Append" )
          {
            $('#container').append($("#brickTemplate").tmpl(ele)).masonry('reload');  
          } else if ( ele.Additem == "Prepend" )
          {
            $('#container').prepend($("#brickTemplate").tmpl(ele)).masonry('reload'); 
          }
        } else
        {                        
          $('.brick').remove(":contains('"+ele.Headline+"')");
          $('#container').masonry('reload');
        }
        $('#container').imagesLoaded(function()
        {          
          $('.brick').mousemove(function()
          {
            var content = $(this).find(">div");
            var summary = $(this).find(".summary");
            if (!content.is(":animated") && summary.is(":not(:visible)"))
            {
              content.css({
                  position: "relative",
                  top: 0 - summary.height()
              });
              summary.show();
              brick_stack.unshift(this);
              content.animate({
                  top: 0
              })
              while (brick_stack.length > 1)
              {
                hide_summary(brick_stack.pop());
              }
            }
          });
        });
      });
    });
    return false; // don't follow the link!
  });
});