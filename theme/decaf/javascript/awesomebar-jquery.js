//$(function()
//{
	
	/*
	*	Settings
	*/
	var menuCloseTime = 3000; //How long (in milliseconds) to keep menus open for after mouse leaves them
	var liHeight = 32; //How tall (in pixels) is each item in the menu
	var menuSlideTime = 100; //How long (in milliseconds) to animate the sliding when scrolling a menu
	var menuHoverScrollTime = 200; //When hovering over the more or less button in a collapsed menu, scroll 1 item every x milliseconds
	
	
	
	
	/*
	*	Keep menus open for longer
	*/
	var menuCloseTimeout;
	$('#awesomebar li').on('mouseenter',function()
	{
		//Begin hover
		$('#awesomebar li.extended-hover').removeClass('extended-hover'); //Close other menus
		$(this).addClass('extended-hover'); //Keep this menu open
		$(this).parents('li').addClass('extended-hover'); //Keep the parents of this menu open

		//Stop the timer to automatically close
		clearTimeout(menuCloseTimeout);
	}).on('mouseleave',function()
	{
		//End hover - start the timer to automatically close
		menuCloseTimeout = setTimeout(function()
		{
			$('#awesomebar li.extended-hover').removeClass('extended-hover');
		},menuCloseTime);
	});
	
	
	
	
	
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
				$(this).prepend( $('<li class="scroll-up scroll-btn"><i class="icon-caret-up"></i> <span></span> previous items</li>').hide() );
				$(this).append('<li class="scroll-btn scroll-down"><i class="icon-caret-down"></i> <span>'+overflow+'</span> more items</li>');
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
		
		maxToShowInThisMenu = maxMenuItems;
		
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
			$(menu).children('.scroll-up').show().children('span').text(newOffset);
		}
		else
		{
			//$(menu).children('.scroll-up').stop().slideUp(menuSlideTime);
			$(menu).children('.scroll-up').hide();
		}
		
		if ( remainingItems>0 )
		{
			//$(menu).children('.scroll-down').stop().slideDown(menuSlideTime).children('span').text(remainingItems);
			$(menu).children('.scroll-down').show().children('span').text(remainingItems);
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
		scrollMenu( $(this).closest('ul') , +1 );
	});
	
	$(document).on('click','#awesomebar .scroll-up',function()
	{
		scrollMenu( $(this).closest('ul') , -1 );
	});
	
	
	//Scroll on hover
	var menuHoverScrollInterval;
	function enableMenuScrollOnHover()
	{
		$('#awesomebar li.scroll-down, #awesomebar li.scroll-up').hover(function()
		{	
			var btn = this;
			menuHoverScrollInterval = setInterval(function(){ $(btn).click(); },menuHoverScrollTime);
		},function()
		{
			clearInterval(menuHoverScrollInterval);
		});
	}
	



	
	/*
	*	When hovering over a list item, reposition its submenuto fit it on the screen better.
	*	By this point, menus that are too long to fit no matter where they are will have already been turned into scrolly ones
	*/
	
	$('#awesomebar li li').each(function() //Select all submenus (not initial dropdowns)
	{
		$(this).mouseover(function(e)
		{
			e.stopPropagation();
			$(this).children('ul').each(function()
			{
				repositionChildMenu(this);
			});	
		});
	});	
	
	function repositionChildMenu( menu )
	{
		info = getMenuInfo(menu);
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
	}
	
	function getMenuInfo( menu )
	{
		var info = {};
		
		//Size
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
		
		return info;
	}
	
		
//});
