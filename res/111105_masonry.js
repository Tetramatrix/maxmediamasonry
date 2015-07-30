/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Chi Hoang <info@chihoang.de>
 *  All rights reserved
 *
 ***************************************************************/
( function() {
  
  var $j;
  var boxCount = 0,
      counter = 0;
  var brick_stack = [];
  
  function ajax () {
    // Menu ajax script
     $j("#tx-charbeitsbeispiele-pi1 #menu li").click(function() {
      $j.getJSON($j(this).find('a').attr('href'), function(json) {
        var container = $j('#tx-charbeitsbeispiele-pi1 #container');
        container.masonry();
        $j.each(json, function(idx, ele) {
          if (ele.killbit == "on") {
            if (ele.Additem == "Append") {
              container.append($j("#brickTemplate").tmpl(ele).css({
                "display": "block"
              })).masonry('reload');
            } else if (ele.Additem == "Prepend") {
              container.prepend($j("#brickTemplate").tmpl(ele).css({
                "display": "block"
              })).masonry('reload');
            }
          } else {
            $j('.brick').remove(":contains('" + ele.Headline + "')");
            container.masonry('reload');
          }
          container.imagesLoaded(function() {
            // bricks correct height
            var brick = $j("#tx-charbeitsbeispiele-pi1 #container .brick"); 
            brick.each(function() {
              var content = $j(this).find(">div");
              var img = $j(this).find("img");
               content.css({
                height: img.attr("height")
               });
            });
            brick.mousemove(function() {
              var content = $j(this).find(">div");
              var summary = $j(this).find(".teaser");
              var img = $j(this).find("img");
              if (!content.is(":animated") && summary.is(":not(:visible)")) {
                content.css({
                  height: img.attr("height"),
                  position: "relative",
                  top: -35 - summary.height()
                });
                summary.show();
                brick_stack.unshift(this);
                content.animate({
                  top: 0
                });
                while (brick_stack.length > 1) {
                  hide_summary(brick_stack.pop());
                }
              }
            });
            brick.mouseleave(function() {
              hide_summary(brick_stack.pop());
            });
          });
        });
      });
      return false; // don't follow the link!
    });
  }
  
  function reload () {
    $j(window).unload(function() {
    var reloadLink = $j("#tx-charbeitsbeispiele-pi1 #menu li:first").find("a"); 
    var theHref = reloadLink.attr('href').replace(/&killbit=.*/g, '');
    reloadLink.attr("href", theHref + "&screen=reload");
    $j.get(reloadLink.attr('href'), function(response) {
        // screen unloaded;    
      });
    });
    // Start masonry animated
   $j('#tx-charbeitsbeispiele-pi1 #container').masonry({
     itemSelector: '.brick',
     columnWidth: 390,
     isAnimated: !Modernizr.csstransitions,
     animationOptions: {
       duration: 500,
       easing: 'linear',
       queue: false
     }
  });

  // Menu hover script
  var menu = $j("#tx-charbeitsbeispiele-pi1 #menu li");
  menu.hover(
    function() {
      if ($j(this).hasClass('ref_no')) {
        $j(this).removeClass('ref_no').addClass('ref_hover');
      }
    }, function() {
      if ($j(this).hasClass('ref_hover')) {
        if ($j(this).hasClass('ref_act')) {
          $j(this).removeClass('ref_hover');
        } else {
          $j(this).removeClass('ref_hover').addClass('ref_no');
        }
      }
    });

  // Menu toggle script
  menu.toggle(

    function() {
      var n = $j(this).find('a').text();
    
      // Klick "Alle"-Button
      if (n == "Alle") {
        $j.each(menu, function(idx, ele) {
          // Alle anderen buttons off
          if ($j(ele).find('a').text() != "Alle") {
            $j(ele).removeClass('ref_act').addClass('ref_no');
            var theHref = $j(ele).find('a').attr("href").replace(/&killbit=.*/g, '');
            $j(ele).find('a').attr("href", theHref + "&killbit=off");
          } else {
            // Alle buttons on
            $j(ele).removeClass('ref_no').addClass('ref_act');
            var theHref = $j(ele).find('a').attr("href").replace(/&killbit=.*/g, '');
            $j(ele).find('a').attr("href", theHref + "&killbit=on");
          }
        });
      }
    
      var params = "&killbit=on";
    
      $j.each(menu, function(idx, ele) {
        if ($j(ele).find('a').text() == n) {
          $j(ele).removeClass('ref_no').addClass('ref_act');
          var theHref = $j(ele).find('a').attr("href").replace(/&killbit=.*/g, '');
          $j(ele).find('a').attr("href", theHref + params);
    
        } else if ($j(ele).find('a').text() == "Alle" && $j(ele).hasClass('ref_act')) {
          params += "&screen=clear";
          $j(ele).removeClass('ref_act').addClass('ref_no');
          var theHref = $j(ele).find('a').attr("href").replace(/&killbit=.*/g, '');
          $j(ele).find('a').attr("href", theHref + "&killbit=off");
        }
      });
      $j(this).trigger('mouseleave');
    },
    
    function() {
      var n = $j(this).find('a').text();
      if (n != "Alle") {
        var params = "&killbit=off";
        $j.each(menu, function(idx, ele) {
          if ($j(ele).find('a').text() == n) {
            if ($j(ele).hasClass('ref_no')) {
              $j(ele).removeClass('ref_no').addClass('ref_act');
              var theHref = $j(ele).find('a').attr("href").replace(/&killbit=.*/g, '');
              $j(ele).find('a').attr("href", theHref + "&killbit=on&screen=clear");
            } else {
              $j(ele).removeClass('ref_act').addClass('ref_no');
              var theHref = $j(ele).find('a').attr("href").replace(/&killbit=.*/g, '');
              $j(ele).find('a').attr("href", theHref + params);
            }
          }
          // Alle-Button Active
          if ($j(ele).hasClass('ref_act') && $j(ele).find('a').text() == "Alle") {
            // Clear Screen
            params += "&screen=clear";
            $j(ele).removeClass('ref_act').addClass('ref_no');
            var theHref = $j(ele).find('a').attr("href").replace(/&killbit=.*/g, '');
            $j(ele).find('a').attr("href", theHref + "&killbit=off");
    
          }
        });
      } else if (n == "Alle") {
        $j.each(menu, function(idx, ele) {
          if ($j(ele).find('a').text() == "Alle") {
            $j(this).removeClass('ref_no').addClass('ref_act');
            var theHref = $j(this).find('a').attr("href").replace(/&killbit=.*/g, '');
            $j(this).find('a').attr("href", theHref + "&killbit=on");
    
          } else if ($j(ele).find('a').text() != "Alle") {
            $j(ele).removeClass('ref_act').addClass('ref_no');
            var theHref = $j(ele).find('a').attr("href").replace(/&killbit=.*/g, '');
            $j(ele).find('a').attr("href", theHref + "&killbit=off");
          }
        });
      }
      $j(this).trigger('mouseleave');
    });

    // First time, Reload, Tab closed, Browser, Close, Cookie deleted, PHPSESSIONID deleted
    $j.getJSON($j("#tx-charbeitsbeispiele-pi1 #menu li:first").find("a").attr('href'), function(response) {
      // Start masonry animated
      var container = $j('#tx-charbeitsbeispiele-pi1 #container');
      container.masonry({
        itemSelector: '.brick',
        columnWidth: 390,
        isAnimated: false
      });
      boxCount = response.length;
      counter = 0;
      $j.each(response, function(idx, ele) {
        container.prepend($j("#brickTemplate").tmpl(ele)).masonry('reload');
        container.imagesLoaded(function() {
          ++counter;
          if (counter >= boxCount) {
            // Menu slidedown
            $j('#tx-charbeitsbeispiele-pi1 #menu').slideDown('slow', function() {
              // Animation complete.
            });
            // bricks correct height 
            var brick = $j("#tx-charbeitsbeispiele-pi1 #container .brick"); 
            brick.each(function() {
              var content = $j(this).find(">div");
              var img = $j(this).find("img");
               content.css({
                height: img.attr("height")
               });
            });
            // bricks fade in
            brick.each(function() {
              $j(this).delay(Math.floor(Math.random() * 1600)).fadeIn('slow', function() {
                // Animation complete
              });
            });
          }
          // Bind Mousemove
          brick.mousemove(function() {
            var content = $j(this).find(">div");
            var summary = $j(this).find(".teaser");
            var img = $j(this).find("img");
            if (!content.is(":animated") && summary.is(":not(:visible)")) {
              content.css({
                height: img.attr("height"),
                position: "relative",
                top: -35 - summary.height()
              });
              summary.show();
              brick_stack.unshift(this);
              content.animate({
                top: 0
              });
              while (brick_stack.length > 1) {
                hide_summary(brick_stack.pop());
              }
            }
          });
          // Bind mouseleave
          brick.mouseleave(function() {
            hide_summary(brick_stack.pop());
          });
        }); // ImagesLoadead
      }); // each
      var reloadLink = $j("#tx-charbeitsbeispiele-pi1 #menu li:first");
      reloadLink.removeClass('ref_no').addClass('ref_act');
      var theHref = reloadLink.find('a').attr("href").replace(/&killbit=.*/g, '');
      reloadLink.find('a').attr("href", theHref + "&killbit=off");
    });
  }

  // Privat function
  function hide_summary(ele) {
    var content = $j(ele).find(">div");
    var summary = $j(ele).find(".teaser");
    content.animate({
      top: -35 - summary.height()
    }, function() {
      content.css({
        position: "static"
      });
      summary.hide();
    });
  }
  
  // Public functions
  Arbeitsbeispiele.prototype.ajax = function ( )
  {
          return ajax();
  }
  
  Arbeitsbeispiele.prototype.reload = function ( )
  {
          return reload();
  }

  function Arbeitsbeispiele (anonymous)
  {
          $j = anonymous;
          return true;
  }
  window.Arbeitsbeispiele = Arbeitsbeispiele;
})();


var $ = $.noConflict();
$(document).ready(function() {
  var maxmedia = new Arbeitsbeispiele($);
  maxmedia.reload();
  maxmedia.ajax();
});