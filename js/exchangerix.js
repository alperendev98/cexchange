/* (c) 2018 Exchangerix Software */

///---------(slider)-------------------------------------------------------------------------------------------------------
/*
$(document).ready(function(){	
	$("#slider").easySlider({
		auto: true, 
		continuous: true,
		numeric: true
	});
});	
*/
///---------(top)-------------------------------------------------------------------------------------------------------

 $(document).ready(function() {
	$(window).scroll(function() {
		if ($(this).scrollTop() > 100) {
			$('.scrollup').fadeIn();
		} else {
			$('.scrollup').fadeOut();
		}
		});
 
		$('.scrollup').click(function() {
			$("html, body").animate({ scrollTop: 0 }, 600);
			return false;
		});
});

///---------(tabs)------------------------------------------------------------------------------------------------------

$(document).ready(function(){

	$(".tab_content").hide(); // Hide all content
	$("#tabs li:first").addClass("active").show(); // Activate first tab
	$(".tab_content:first").show(); // Show first tab content

	$("#tabs li").click(function() {
		//	First remove class "active" from currently active tab
		$("#tabs li").removeClass('active');

		//	Now add class "active" to the selected/clicked tab
		$(this).addClass("active");

		//	Hide all tab content
		$(".tab_content").hide();

		//	Here we get the href value of the selected tab
		var selected_tab = $(this).find("a").attr("href");

		//	Show the selected tab content
		$(selected_tab).fadeIn();
		return false;
	});
});


$(document).ready(function(){
    $('.exchangerix_tooltip').tooltip();
    $('.itooltip').tooltip();
    $('#itooltip').tooltip();
});

///---------(scroll)--------------------------------------------------------------------------------------------------------

$(document).ready(function() {
	$("#next-button").click(function () {
	  $("#hide-text-block").toggle("slow");
	  $("#next-button").hide();
	  $("#prev-button").show();
	});
	$("#prev-button").click(function () {
	 $("#hide-text-block").hide();
	 $("#prev-button").hide();
	 $("#next-button").show();
	});
});


(function( $ ) {

    //Function to animate slider captions 
	function doAnimations( elems ) {
		//Cache the animationend event in a variable
		var animEndEv = 'webkitAnimationEnd animationend';
		
		elems.each(function () {
			var $this = $(this),
				$animationType = $this.data('animation');
			$this.addClass($animationType).one(animEndEv, function () {
				$this.removeClass($animationType);
			});
		});
	}
	
	//Variables on page load 
	var $myCarousel = $('#carousel-example-generic'),
		$firstAnimatingElems = $myCarousel.find('.item:first').find("[data-animation ^= 'animated']");
		
	//Initialize carousel 
	$myCarousel.carousel();
	
	//Animate captions in first slide on page load 
	doAnimations($firstAnimatingElems);
	
	//Pause carousel  
	$myCarousel.carousel('pause');
	
	//Other slides to be animated on carousel slide event 
	$myCarousel.on('slide.bs.carousel', function (e) {
		var $animatingElems = $(e.relatedTarget).find("[data-animation ^= 'animated']");
		doAnimations($animatingElems);
	});  
    $('#carousel-example-generic').carousel({
        interval:3000,
        pause: "false"
    });
	
})(jQuery);	


		/* href row */
		$(document).ready(function() {
		    $(".href-row").click(function(e) {
			    // if not want more link
			    if (e.target.id != "morediv" && !$(e.target).parents(".morediv").length)
			    { 
		        	window.location = $(this).data("href");
		        }
		    });
		});
		
		/* digits */
		$(function() {
  $('#staticParent').on('keydown', 'input[id*=child]', function(e){-1!==$.inArray(e.keyCode,[46,8,9,27,13,110,190])||/65|67|86|88/.test(e.keyCode)&&(!0===e.ctrlKey||!0===e.metaKey)||35<=e.keyCode&&40>=e.keyCode||(e.shiftKey||48>e.keyCode||57<e.keyCode)&&(96>e.keyCode||105<e.keyCode)&&e.preventDefault()});
}) /*'child'*/



		setInterval(function(){blink2()}, 2000);         
			function blink2() {
				$("#operator_live").fadeTo(100, 0.1).fadeTo(200, 1.0);
				$("#operator_live2").fadeTo(100, 0.1).fadeTo(200, 1.0);
			}
			
			
	/* modal */		
	$(document).on("click", ".open-ReserveDialog", function () {
     var myCurrId = $(this).data('id');
     var myname = $(this).data('id2');
     $(".modal-title #mname").html( myname );
     $(".modal-body #currId").val( myCurrId );
     $(".modal-body #currId").val( myCurrId );
	});
	
	/* Progress */
	$('.progress-bar-fill').delay(1000).queue(function () {
        $(this).css('width', '100%')
    });	
    
    
    //$('#signin').modal() 
   

/* top scroll */
$(document).ready(function () {
    $('#myCarousel').carousel({
        interval: 4500
    })
    $('.fdi-Carousel .item').each(function () {
        var next = $(this).next();
        if (!next.length) {
            next = $(this).siblings(':first');
        }
        next.children(':first-child').clone().appendTo($(this));

        if (next.next().length > 0) {
            next.next().children(':first-child').clone().appendTo($(this));
        }
        else {
            $(this).siblings(':first').children(':first-child').clone().appendTo($(this));
        }
    });
});		 

/*
	$(document).ready(function(){
            		$('#currency_send').on('change', function(e){
					   $('#currency_receive').val('');
					   var thisVal = $(this).val();
					   $('#currency_receive option').each(function(){
					      if(thisVal == $(this).attr('value')){
					         $(this).attr('disabled', 'disabled'); //$('#currency_receive option').addClass('disabled');
					         //$(this).remove();
					       }else{
					         $(this).removeAttr('disabled');
					       }
					   })
					});	
        });   
*/
        


$(document).ready(function(e) {
	new Clipboard('.clipboard');
});


jQuery(document).ready(function(){
var target = window.location.hash;
if ( target != '' ){
    var $target = jQuery(target); 
    jQuery('html, body').stop().animate({
    'scrollTop': $target.offset().top - 20},
    800, 
    'swing',function () {
    window.location.hash = target - 20 ;
    });
}
});


jQuery(document).ready(function(){
$('#exbutton').hover(function() {
	$('#resh').addClass('fa-spin');
}, function() {
	$('#resh').removeClass('fa-spin');
});
});
       