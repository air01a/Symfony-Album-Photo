/*
jQuery fullsizable plugin v2.0.2
  - take full available browser space to show images

(c) 2011-2014 Matthias Schmidt <http://m-schmidt.eu/>

Example Usage:
  $('a.fullsizable').fullsizable();

Options:
  **detach_id** (optional, defaults to null) - id of an element that will temporarely be set to ``display: none`` after the curtain loaded.
  **navigation** (optional, defaults to true) - show next and previous links when working with a set of images.
  **closeButton** (optional, defaults to true) - show a close link.
  **fullscreenButton** (optional, defaults to true) - show full screen button for native HTML5 fullscreen support in supported browsers.
  **openOnClick** (optional, defaults to true) - set to false to disable default behavior which fullsizes an image when clicking on a thumb.
  **clickBehaviour** (optional, 'next' or 'close', defaults to 'close') - whether a click on an opened image should close the viewer or open the next image.
  **preload** (optional, defaults to true) - lookup selector on initialization, set only to false in combination with ``reloadOnOpen: true`` or ``fullsizable:reload`` event.
  **reloadOnOpen** (optional, defaults to false) - lookup selector every time the viewer opens.
*/


(function() {
  var $, $image_holder, bindCurtainEvents, closeFullscreen, closeViewer, container_id, current_image, hasFullscreenSupport, hideChrome, image_holder_id, images, keyPressed, makeFullsizable, mouseMovement, mouseStart, nextImage, nextImageTog, openViewer, options, preloadImage, prepareCurtain, prevImage, resizeImage, showChrome, showImage, spinner_class, stored_scroll_position, toggleFullscreen, unbindCurtainEvents, autoPlay, changeTime;

  $ = jQuery;

  container_id = '#jquery-fullsizable';

  image_holder_id = '#fullsized_image_holder';

  comment_holder_id = '#fullsized_comment';

  spinner_class = 'fullsized_spinner';

  $image_holder = $('<div id="jquery-fullsizable"><div id="fullsized_image_holder"></div><div id="fullsized_comment"></div></div>');

  images = [];

  current_image = 0;

  oldX = 0;
  oldY = 0;
  options = null;

  stored_scroll_position = null;

  timer = null;
  var zt ;



  first=0
  resizeImage = function() {
    var image, _ref;
    if (this.oldX==0 || this.oldY==0) {
      this.oldY=$(window).height();
      this.oldX=$(window).width();
    }

    windowHeight=$(window).height()
    windowWidth=$(window).width()

    if (Math.abs((windowHeight-this.oldY))>0.10*this.oldY || Math.abs((windowWidth-this.oldX))>0.10*this.oldX) {
      this.oldY=windowHeight;
      this.oldX=windowWidth;


      this.reloadOnResize();
    }
    image = images[current_image];
    if ((_ref = image.ratio) == null) {

      image.ratio = (image.naturalHeight / image.naturalWidth).toFixed(2);
    }
    height = image.naturalHeight;
    width  = image.naturalWidth;
    
    scale=1;

    scaleY=windowHeight/height;
    scaleX=windowWidth/width;

    

    if (!(scaleY>1&&scaleX>1)) {
        if (scaleY<scaleX)
          scale=scaleY;
        else
          scale=scaleX;
    }
    $(image).width(width*scale);
    $(image).height(height*scale);
    



    return $(image).css('margin-top', ($(window).height() - height*scale) / 2);

  };

  keyPressed = function(e) {
    if (e.keyCode === 27) {
      closeViewer();
    }
    if (e.keyCode === 37) {
      prevImage(true);
    }
    if (e.keyCode === 39) {
      return nextImage(true);
    }
    if (e.keyCode === 187) {
	changeTime(1000);	
    }
    if (e.keyCode === 54) {
	changeTime(-1000);
    }

  };

  prevImage = function(shouldHideChrome) {
    if (shouldHideChrome == null) {
      shouldHideChrome = false;
    }
    if (current_image > 0) {
      return showImage(images[current_image - 1], -1, shouldHideChrome);
    }
  };

  nextImage = function(shouldHideChrome) {
    if (shouldHideChrome == null) {
      shouldHideChrome = false;
    }
    if (current_image < images.length - 1) {
      return showImage(images[current_image + 1], 1, shouldHideChrome);
    }
  };

  showImage = function(image, direction, shouldHideChrome) {
    if (direction == null) {
      direction = 1;
    }
    if (shouldHideChrome == null) {
      shouldHideChrome = false;
    }
    
    current_image = image.index;
    $(image_holder_id).hide();
    $(image_holder_id).html(image);
    if (image.alt.length>0){
    	$(comment_holder_id).html(image.alt);
    	$(comment_holder_id).show();
    } else
	$(comment_holder_id).hide();
    if (options.navigation) {
      if (shouldHideChrome === true) {
        hideChrome();
      } else {
        showChrome();
      }
    }
    if (image.loaded != null) {
      $(container_id).removeClass(spinner_class);
      resizeImage();
      $(image_holder_id).fadeIn('fast');
      autoPlay();
      return preloadImage(direction);
    } else {
      $(container_id).addClass(spinner_class);
      image.onload = function() {
	clearInterval();
        $(image_holder_id).fadeIn('slow', function() {
          return $(container_id).removeClass(spinner_class);
        });
	resizeImage();
        this.loaded = true;
        return preloadImage(direction);
      };
      return image.src = image.buffer_src+"&size="+Math.floor($(window).width()*0.95)+'x'+Math.floor($(window).height()*0.95);
    }
  };





  reloadOnResize = function() {
    clearTimeout(window.resizedFinished);

    window.resizedFinished = setTimeout(function(){
  
        images[current_image].src=images[current_image].buffer_src+"&size="+Math.floor($(window).width()*0.95)+'x'+Math.floor($(window).height()*0.95);
        $(image_holder_id).html(images[current_image]);

        for (_i = 0, _len = images.length; _i < _len; _i++) {
          image = images[_i];
          if (image.src!='' && i!=current_image)
          {
            image.src=image.buffer_src+"&size="+Math.floor($(window).width()*0.95)+'x'+Math.floor($(window).height()*0.95);
          }
        }},550);

  }

  preloadImage = function(direction) {
    var preload_image;

    if (direction === 1 && current_image < images.length - 1) {
      preload_image = images[current_image + 1];
    } else if ((direction === -1 || current_image === (images.length - 1)) && current_image > 0) {
      preload_image = images[current_image - 1];
    } else {
      return;
    }
    preload_image.onload = function() {
      return this.loaded = true;
    };
    if (preload_image.src === '') {
      return preload_image.src = preload_image.buffer_src+"&size="+Math.floor($(window).width()*0.95)+'x'+Math.floor($(window).height()*0.95);
    }
  };

  openViewer = function(image, opening_selector) {
    $('body').append($image_holder);
    $(window).bind('resize', resizeImage);
    showImage(image);
    return $(container_id).hide().fadeIn(function() {
      if (options.detach_id != null) {
        stored_scroll_position = $(window).scrollTop();
        $('#' + options.detach_id).css('display', 'none');
        resizeImage();
      }
      bindCurtainEvents();


     zt = new ZingTouch.Region(document.body,false,false);
		var ztElement = document.getElementById('jquery-fullsizable');
		zt.bind(ztElement, 'swipe', function(e){
			//Actions here
			direction = Math.floor(e.detail.data[0].currentDirection);
			if (direction<45 || direction>315)
				prevImage(true);
			if (direction>135 && direction<205)
        nextImage(true);

      if (direction>60 && direction<120) {
        (e.detail.events).forEach( _e => _e.originalEvent.preventDefault());
        closeViewer();
      }
        
      }, false);



      return $(document).trigger('fullsizable:opened', opening_selector);
    });
  };

  closeViewer = function() {
    autoPlay(false);
    var ztElement = document.getElementById('jquery-fullsizable');
    zt.unbind(ztElement);
    if (options.detach_id != null) {
      $('#' + options.detach_id).css('display', 'block');
      $(window).scrollTop(stored_scroll_position);
    }
    $(container_id).fadeOut(function() {
      return $image_holder.remove();
    });
    closeFullscreen();
    $(container_id).removeClass(spinner_class);
    unbindCurtainEvents();
    return $(window).unbind('resize', resizeImage);
  };

  makeFullsizable = function() {
    images.length = 0;
	$('#fullsized_go_next').unbind('click')
        $('#fullsized_go_prev').unbind('click')
    return $(options.selector).each(function() {
      
      var image;
      image = new Image;
      image.buffer_src = $(this).attr('href');
      image.index = images.length;
      image.id=image.index;
      image.alt=$(this).attr('alt');
      images.push(image);
	
      if (options.openOnClick) {
        return $(this).click(function(e) {
          e.preventDefault();
          if (options.reloadOnOpen) {
            makeFullsizable();
          }
          return openViewer(image, this);
        });
      }
      
    });
  };

  autoPlay = function (activate) {
	if (activate==true) {
		if (options.autoPlay==false) {
			
			$image_holder.append('<div id="fullsized_timeSlider"><span id="fullsized_timeSlider_txt">Temps de d&eacute;filement : '+options.autoPlayTime/1000+' s</span><div id="fullsized_timeSlider_slider"></div></div>');
			$( "#fullsized_timeSlider_slider" ).slider({
      				min: 1000,
			        max: 10000,
				value: options.autoPlayTime,
			      step: 1000,
			      slide: function( event, ui ) {
			        options.autoPlayTime=ui.value;
				$("#fullsized_timeSlider_txt").html('Temps : '+options.autoPlayTime/1000+' s');
			      }});
			options.autoPlay=true;
			$("#fullsized_play_id").addClass('fullsized_playing');


			var d = new Date();
			var n = d.getTime();

			 setTimeout(function() {
                                        if (options.autoPlay)
                                                nextImage(true);
                                },options.autoPlayTime);
		} else {
			
			options.autoPlay=false;
			$("#fullsized_play_id").removeClass('fullsized_playing');
			$("#fullsized_timeSlider" ).remove();
			showChrome();
		}
	} else if (activate==false) {
			options.autoPlay=false;
			$("#fullsized_play_id").removeClass('fullsized_playing');
			$( "#fullsized_timeSlider").remove();
			showChrome();
		} else if (activate==null){
			if (options.autoPlay) {
				var d = new Date();
	                        var n = d.getTime();

				setTimeout(function() { 
					if (options.autoPlay) 
						nextImage(true);
				},options.autoPlayTime);
			}
		}
  }

  changeTime = function(inc) {
	if (options.autoPlayTime+inc>0)
		options.autoPlayTime+=inc;
  }

  prepareCurtain = function() {
   $image_holder.append('<a id="fullsized_play_id" class="fullsized_play" href="#play"></a>'); 
   first+=1
   if (first==1) {
   $(document).on('click','#fullsized_play_id', function(e) {
	e.preventDefault();
	e.stopPropagation();
   	autoPlay(true);
   }); 
   }
   if (options.navigation) {
      $image_holder.append('<a id="fullsized_go_prev" href="#prev"></a><a id="fullsized_go_next" href="#next"></a>');
      if (first==1) {
      $(document).on('click', '#fullsized_go_prev', function(e) {
        e.preventDefault();
        e.stopPropagation();
	autoPlay(false);
        return prevImage(false);
      });
      }
     if (first==1) {
     	$(document).on('click', '#fullsized_go_next', function(e) {
     	//$('#fullsized_go_next').on('click',function(e) {
		e.preventDefault();
        	e.stopPropagation();
		autoPlay(false);
        	return nextImage(false);
      	});
     }
    }
    if (options.closeButton) {
      $image_holder.append('<a id="fullsized_close" href="#close"></a>');
      $(document).on('click', '#fullsized_close', function(e) {
        
        e.preventDefault();
        e.stopPropagation();
	autoPlay(false);
        return closeViewer();
      });
    }
    
	
    if (options.fullscreenButton && hasFullscreenSupport()) {
      $image_holder.append('<a id="fullsized_fullscreen" href="#fullscreen"></a>');
      $(document).on('click', '#fullsized_fullscreen', function(e) {
        e.preventDefault();
        e.stopPropagation();
        return toggleFullscreen();
      });
    }
    switch (options.clickBehaviour) {
      case 'close':
        return $(document).on('click', container_id, closeViewer);
      case 'next':
        return $(document).on('click', container_id, function() {
          return nextImageTog();
        });
    }
  };

  nextImageTog = function() {
	if (options.autoPlay) {
		return showChrome();
	} else {
		nextImage(false);
	}

  }

  bindCurtainEvents = function() {
    $(document).bind('keydown', keyPressed);
    $(document).bind('fullsizable:next', function() {
      return nextImage(true);
    });
    $(document).bind('fullsizable:prev', function() {
      return prevImage(true);
    });
    return $(document).bind('fullsizable:close', closeViewer);
  };

  unbindCurtainEvents = function() {
    $(document).unbind('keydown', keyPressed);
    $(document).unbind('fullsizable:next');
    $(document).unbind('fullsizable:prev');
    return $(document).unbind('fullsizable:close');
  };

  hideChrome = function() {
    var $chrome;

    $chrome = $image_holder.find('a');
    if ($chrome.is(':visible') === true) {
      $chrome.toggle(false);
      if ($("#fullsized_timeSlider").length!=0)
	$("#fullsized_timeSlider").toggle(false);
      return $image_holder.bind('mousemove', mouseMovement);
    }
  };

  mouseStart = null;

  mouseMovement = function(event) {
    var distance;

    if (mouseStart === null) {
      mouseStart = [event.clientX, event.clientY];
    }
    distance = Math.round(Math.sqrt(Math.pow(mouseStart[1] - event.clientY, 2) + Math.pow(mouseStart[0] - event.clientX, 2)));
    if (distance >= 10) {
      $image_holder.unbind('mousemove', mouseMovement);
      mouseStart = null;
      return showChrome();
    }
  };

  showChrome = function() {
    $('#fullsized_close, #fullsized_fullscreen').toggle(true);
    $('#fullsized_go_prev').toggle(current_image !== 0 && options.autoPlay!=true);
    $('#fullsized_play_id').toggle(true);
    if ($("#fullsized_timeSlider").length!=0)
        $("#fullsized_timeSlider").toggle(true);
    return $('#fullsized_go_next').toggle(current_image !== images.length - 1 && options.autoPlay!=true);
  };

  $.fn.fullsizable = function(opts) {
    options = $.extend({
      selector: this.selector,
      detach_id: null,
      navigation: true,
      closeButton: true,
      fullscreenButton: true,
      openOnClick: true,
      clickBehaviour: 'close',
      preload: true,
      reloadOnOpen: false,
      autoPlay: false,
      autoPlayTime: 4000
    }, opts || {});
    prepareCurtain();
    if (options.preload) {
      makeFullsizable();
    }
	
    $(document).bind('fullsizable:reload', makeFullsizable);
    $(document).bind('fullsizable:open', function(e, target) {
      var image, _i, _len, _results;
      if (options.reloadOnOpen) {
        makeFullsizable();
      }
      _results = [];
      for (_i = 0, _len = images.length; _i < _len; _i++) {
        image = images[_i];
        if (image.buffer_src === $(target).attr('href')) {
          _results.push(openViewer(image, target));
        } else {
          _results.push(void 0);
        }
      }
      return _results;
    });
    return this;
  };

  hasFullscreenSupport = function() {
    var fs_dom;

    fs_dom = $image_holder.get(0);
    if (fs_dom.requestFullScreen || fs_dom.webkitRequestFullScreen || fs_dom.mozRequestFullScreen) {
      return true;
    } else {
      return false;
    }
  };

  closeFullscreen = function() {

    return toggleFullscreen(true);
  };

  toggleFullscreen = function(force_close) {
    var fs_dom;

    fs_dom = $image_holder.get(0);
    if (fs_dom.requestFullScreen) {
      if (document.fullScreen || force_close) {
        return document.exitFullScreen();
      } else {
        return fs_dom.requestFullScreen();
      }
    } else if (fs_dom.webkitRequestFullScreen) {
      if (document.webkitIsFullScreen || force_close) {
        return document.webkitCancelFullScreen();
      } else {
        return fs_dom.webkitRequestFullScreen();
      }
    } else if (fs_dom.mozRequestFullScreen) {
      if (document.mozFullScreen || force_close) {
        return document.mozCancelFullScreen();
      } else {
        return fs_dom.mozRequestFullScreen();
      }
    }
  };

}).call(this);
