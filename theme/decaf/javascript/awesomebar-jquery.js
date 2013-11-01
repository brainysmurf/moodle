var iOS = ( navigator.userAgent.match(/(iPad|iPhone|iPod)/g) ? true : false );

$(function()
{	
	/*
	*	Settings
	*/
	var menuShowDelay = 200; //How long (in milliseconds) to wait before showing a menu
	var menuHideDelay = 500; //How long (in milliseconds) to keep menus open for after mouse leaves them
	var liHeight = 32; //How tall (in pixels) is each item in the menu
//	var menuSlideTime = 1000; //How long (in milliseconds) to animate the sliding when scrolling a menu
	var menuHoverScrollTime = 200; //When hovering over the more or less button in a collapsed menu, scroll 1 item every x milliseconds
	
	var touchEnabled = iOS || 'ontouchstart' in window || window.navigator.msPointerEnabled;

	//Open menus on click
	$('#awesomebar li').on('click',function(e)
	{
		clearTimeout(menuShowTimeout);
		clearTimeout(menuHideTimeout);
		openMenu(this);	
		e.stopPropagation();
	});
	
	$(document).on('click',':not(#awesomebar)',function(e)
	{
		closeAllMenus();
	});

	/*
	*	Delayed menu showing and hiding
	*/
	var menuShowTimeout;
	var menuHideTimeout;
	$('#awesomebar li').on('mouseenter',function(e)
	{
		//Begin hover
		clearTimeout(menuShowTimeout);
		clearTimeout(menuHideTimeout);
		var li = this;
		
		//Show this menu after menuShowDelay
		menuShowTimeout = setTimeout(function()
		{
			openMenu(li);
		},menuShowDelay);
		
		e.stopPropagation();
		
	}).on('mouseleave',function()
	{
		//End hover
		clearTimeout(menuShowTimeout);
		clearTimeout(menuHideTimeout);
		var li = this;
		
		//Hide this menu and its children after menuHideDelay
		menuHideTimeout = setTimeout(function()
		{
			//console.log('menuHideTimeout',$(li).text().substring(0,30));
			$(li).removeClass('hover').trigger('hide').find('.hover').removeClass('hover').trigger('hide');
		},menuHideDelay);
	});
	
	function closeAllMenus()
	{
		$('#awesomebar .hover').removeClass('hover').trigger('hide');
	}
	
	function openMenu( li )
	{
		//console.log('openMenu',$(li).text().substring(0,30));
		if ( $(li).closest('.openLeft').length < 1 )
		{
			$('#awesomebar .blurry').removeClass('blurry');
		}
		$('#awesomebar li.hover').removeClass('hover').trigger('hide'); //Close other open menus
		$(li).addClass('hover').trigger('show'); //Keep this menu open
		$(li).parents('li').addClass('hover').trigger('show'); //Keep the parents of this menu open
		
		//Move submenus so they aren't cut off
		$(li).children('ul').each(function()
		{
			repositionChildMenu(this);
			if ( $(this).hasClass('openLeft') )
			{
				//Select all this menus parent menus, except the first one and the horizontal awesomebar, and make them blurry
				var i = 0;
				$(this).parents('ul:not(#awesomebar > ul)').each(function()
				{
					i++;
					if ( i != 1 )
					{
						$(this).addClass('blurry');
					}
				});
			}
		});	
	}
	
		
	/*
	*	Calculate available screen space on page load and when window is resized
	*/
	var awesomebarHeight = $('#awesomebar').height();
	var viewportHeight;
	var maxMenuSpace;
	var maxMenuItems;
	
	function windowResized()
	{
		viewportHeight = document.documentElement.clientHeight;
		maxMenuSpace = viewportHeight - awesomebarHeight; //The maximum space a menu has to fit in
		maxMenuItems = Math.floor( maxMenuSpace / liHeight ); //How many menu items fit in that space
		if ( maxMenuItems < 4 ) { maxMenuItems = 4; }
		
		//Reset menus
		$('#awesomebar li ul').each(function()
		{
			$(this).children('.scroll-btn').remove();
			$(this).children('li').show();
			$(this).attr('data-offset',0);
		});
		
		//Scroll menus that are too long
		collapseMenus();
		enableMenuScrollOnHover();
		
		//Put submenus back to default so they can be respotiioned corretly when next opened
		$('#awesomebar li li ul').css('top','-1px');
		$('#awesomebar .openLeft').removeClass('openLeft');
		
		//Reposition submenus that were already open
		$('#awesomebar li li ul:visible').each(function()
		{
			repositionChildMenu(this);
		});	
	}
	
	//Bind function to window resize event
	//Has a 100ms delay so it only fires when the window has finished being resized instead of whie it's still beng resized
	var windowResizeTimeout;
	$(window).resize(function(){
		clearTimeout(windowResizeTimeout);
		windowResizeTimeout = setTimeout(windowResized, 100);
	});

	windowResized(); //Fire it once when page loads to set up initial sizes
	

	
	

	/*
	* If menus are too big to fit in the calculated available space, make them scrolly
	*/
	
	function collapseMenus()
	{
		//Hide elements from menus that are too big
		$('#awesomebar li ul').each(function()
		{
			var items = 0;
			$(this).children('li:not(.scroll-btn)').each(function()
			{
				++items;
				if ( items >= maxMenuItems ) //Using >= so that the last item gets hidden too, to make room for the 'next' button
				{
					$(this).hide();
				}
			});
			
			if ( items > maxMenuItems )
			{
				var overflow = items - maxMenuItems;
				$(this).children('.scroll-btn').remove();
				$(this).prepend( $('<li class="scroll-up scroll-btn"><i class="icon-caret-up"></i> <span>0 previous items</span></li>').hide() );
				$(this).append('<li class="scroll-btn scroll-down"><i class="icon-caret-down"></i> <span>'+overflow+' more item'+(overflow==1?'':'s')+'</span></li>');
				$(this).attr('data-offset',0);
				$(this).attr('data-total-items',items);
			}
		});
	}
	
	function scrollMenu( menu , howMany , newOffset )
	{
		if ( !newOffset )
		{
			var currentOffset = parseInt($(menu).attr('data-offset'));
			newOffset = currentOffset + howMany;
		}
		
		var maxToShowInThisMenu = maxMenuItems;
		
		var totalItems = parseInt($(menu).attr('data-total-items'));
		
		//When going down the first time, the first item gets replaced with the previous button, so scroll by 2
		if (newOffset>currentOffset && newOffset==1) { newOffset=2;} 
		//And when going back up, the scroll up button will disappear so work around that
		else if (newOffset<currentOffset && newOffset==1) { newOffset=0;}
		
		var remainingItems = totalItems - (newOffset+maxMenuItems);
		
		if ( newOffset > 0 )
		{
			maxToShowInThisMenu--; //Make room for previous button
		}
		
		if ( remainingItems > 0 )
		{
			maxToShowInThisMenu--; //Make room for next button
		}
		
		var newEnd = newOffset+maxToShowInThisMenu;
		var i = 0;
		$(menu).children('li:not(.scroll-btn)').each(function()
		{
			if ( i >= newOffset && i < newEnd  )
			{
				//$(this).stop().slideDown(menuSlideTime);
				$(this).show();
			}
			else
			{
				//$(this).stop().slideUp(menuSlideTime);
				$(this).hide();
			}
			++i;
		});
		
		$(menu).attr('data-offset',newOffset);
		
		if ( newOffset > 0 )
		{
			//$(menu).children('.scroll-up').stop().slideDown(menuSlideTime).children('span').text(newOffset);
			$(menu).children('.scroll-up').show().children('span').text(newOffset+' previous item'+(newOffset==1?'':'s'));
		}
		else
		{
			//$(menu).children('.scroll-up').stop().slideUp(menuSlideTime);
			$(menu).children('.scroll-up').hide();
		}
		
		if ( remainingItems>0 )
		{
			//$(menu).children('.scroll-down').stop().slideDown(menuSlideTime).children('span').text(remainingItems);
			$(menu).children('.scroll-down').show().children('span').text(remainingItems+' more item'+(remainingItems==1?'':'s'));
		}
		else
		{
			//$(menu).children('.scroll-down').stop().slideUp(menuSlideTime);
			$(menu).children('.scroll-down').hide();
		}
		
		//Put submenus back to default so they can be respotiioned corretly when next opened
		$('#awesomebar li li ul').css('top','-1px');
		
		//Reposition submenus that were already open
		$('#awesomebar li li ul:visible').each(function()
		{
			repositionChildMenu(this);
		});	
	}
	
	
	//Scroll on click
	$(document).on('click','#awesomebar .scroll-down',function()
	{	
		if ( !$(this).is(':visible') ) { return false; }
		scrollMenu( $(this).closest('ul') , +1 );
	});
	
	$(document).on('click','#awesomebar .scroll-up',function()
	{
		if ( !$(this).is(':visible') ) { return false; }
		scrollMenu( $(this).closest('ul') , -1 );
	});
	
	
	//Scroll on hover
	var menuHoverScrollInterval;
	function enableMenuScrollOnHover()
	{
		//On touch devices, hold on up/down buttons to scroll multiple items. On PCs, hover mouse over up/down buttons
		var startEvent = touchEnabled ? 'touchstart' : 'mouseenter';
		var stopEvent = touchEnabled ? 'touchend' : 'mouseleave';
	
		$('#awesomebar li.scroll-down, #awesomebar li.scroll-up').bind(startEvent,function()
		{	
			var btn = this;
			menuHoverScrollInterval = setInterval(function(){ $(btn).click(); },menuHoverScrollTime);
		}).bind(stopEvent,function()
		{
			clearInterval(menuHoverScrollInterval);
		});
	}
	



	
	/*
	*	When hovering over a list item, reposition its submenuto fit it on the screen better.
	*	Adjust the 'top' to move it up if it's too long
	*	To do: if it goes off the right of the screen, move it to the left
	*/
	
	function repositionChildMenu( menu )
	{
		var info = getMenuInfo(menu);
		//if menu is too long to fit in the window
		if ( info.cut )
		{
			if ( info.top > 0 && info.cssTop == -1 ) //(-1px is the default top position for submenus so that means it hasn't already been moved)
			{
				//If menu will all fit in the viewport, move it up only as much as we need. Or if it's still too big, move it to the top
				
				if ( info.height <= maxMenuSpace ) //Total menu height fits in available space
				{
					//Move it up by the number of pixels that were cut off
					var shiftTop = info.hiddenHeight + 10;
				}
				else
				{
					var shiftTop = info.top; //Shift it up by the number of pixels it was offset down before.
				}

				$(menu).css('top','-'+shiftTop+'px');
			}			
		}
		
		if ( info.cutHorizontal )
		{
			$(menu).addClass('openLeft');
		}
	}
	
	function getMenuInfo( menu )
	{
		var info = {};
		
		//Vertical Size
		info.height = $(menu).height();
		info.top = $(menu).offset().top - $(document).scrollTop() - awesomebarHeight; //Awkward because the awesomebar is position:fixed
		info.bottom = info.top + info.height;
		info.cssTop = parseInt($(menu).css('top'));
		info.cut = info.bottom > maxMenuSpace; //is the menu too big?
		info.visibleHeight = Math.min( (maxMenuSpace - info.top) , info.height );
		info.hiddenHeight = info.height - info.visibleHeight;
		
		//Items
		info.items = $(menu).children('li').length,
		info.visibleItems = Math.floor( info.visibleHeight / liHeight );
		info.hiddenItem = info.items - info.visibleitems;
		
		//Horizontal Size
		info.width = $(menu).width();
		info.left = $(menu).offset().left;
		info.cutHorizontal = info.left + info.width > $(window).width();
		
		return info;
	}
	
		
});