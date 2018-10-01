
/* Copyright (c) Ex changerix . com */
/* Exchangerix SITE SOFTWARE */

//--------(check all checkboxes)---------------------------------------------------------------------------------------

	var checked = false;
	function checkAll()
	{
		var myform = document.getElementById("form2");
		
		if (checked == false) { checked = true }else{ checked = false }
		for (var i=0; i<myform.elements.length; i++) 
		{
			myform.elements[i].checked = checked;
		}
	}

$(document).ready(function() {
	var checkboxes = $("input[type='checkbox']"), submitButt = $("input[id^=GoButton]");
    checkboxes.click(function() {
            submitButt.attr("disabled", !checkboxes.is(":checked"));
    });
});

$(document).ready(function() {
	var checkboxes = $("input[type='checkbox']"), submitButt = $("button[id^=GoButton]");
    checkboxes.click(function() {
            submitButt.attr("disabled", !checkboxes.is(":checked"));
    });
});

$(document).ready(function(){
	$(".alert-success").show().delay(2500).fadeOut('slow');
	$('.note').tooltip({placement: 'right'});
	$('.tooltips').tooltip();
	$('[id^=itooltip]').tooltip();
	$('[data-toggle="popover"]').popover();
});


//--------(tabs)---------------------------------------------------------------------------------------
var hash = document.location.hash;
var prefix = "";// tab_
if (hash) {
    $('.nav-tabs a[href="'+hash.replace(prefix,"")+'"]').tab('show');
} 

// Change hash for page-reload
$('.nav-tabs a').on('shown.bs.tab', function (e) {
    window.location.hash = e.target.hash.replace("#", "#" + prefix);
});

/*
$(document).ready(function () {
  $('[data-toggle="offcanvas"]').click(function () {
    $('.row-offcanvas').toggleClass('active')
  });
});
*/


/*
		$("#auto_rate").change(function () {
		  var selected_option = $('#auto_rate').val();

		  if (selected_option == 1)
		  {
			$('#exchange_rate_box').hide();
			$('#exchange_rate_box2').hide();
		  }
		  else
		  {
			$('#exchange_rate_box').show();
			$('#exchange_rate_box2').show();
		  }
		})
*/

		$("#currency").change(function () {
		  var selected_option = $('#currency').val();

		  if (selected_option == 'other')
			$('#other_currency').show();
		  else
			  $('#other_currency').hide();
		})
		

		$("#import_form").submit(function() {
			$('#loading_image').show(); 
			$(':submit',this).attr('disabled','disabled'); // disable double submits
			//$(':submit',this).hide();
			return true;
		});


$('#datetimepicker1').datetimepicker({format:'YYYY-MM-DD HH:mm a'});
$('#datetimepicker2').datetimepicker({format:'YYYY-MM-DD HH:mm a'});





        $(document).ready(function(){
                  
                  $('#from_currency').change(function(){
	                  var selected = $("#from_currency option:selected").text();
	                  selected = selected.split(" ");
	                  selected = selected[selected.length - 1];
                    // $("#from_currency option:selected").text();
                   //alert($("#from_currency option:selected").text());
                   //document.getElementById('loading_image').value = $("#from_currency option:selected").text();
                   $('#curr_box').hide();
                   if ($("#from_currency option:selected").val() != "")
                   {
                   	  $('#curr_box').html( selected );
				   	  $('#curr_box').show();
				   	}
				   else
				   {
					   $('#curr_box').hide();
					   $('#curr_box2').hide();
					}				   	
				   	
                  //$("#loading_image").hide();
				  // $('#' + selected).show();
				  });
				  
				$('#to_currency').change(function(){
	                 var selected = $("#to_currency option:selected").text();
	                  selected = selected.split(" ");
	                  selected = selected[selected.length - 1];	                 
                    // $("#from_currency option:selected").text();
                   //alert($("#from_currency option:selected").text());
                   //document.getElementById('loading_image').value = $("#from_currency option:selected").text();
                   $('#curr_box2').hide();
				   if ($("#to_currency option:selected").val() != "")
                   {                   
                   	$('#curr_box2').html( selected );
				   	$('#curr_box2').show();
				   }
				   else
				   {
					   $('#curr_box2').hide();
					}
                  //$("#loading_image").hide();
				  // $('#' + selected).show();
            	});
            	
            	
            		$('#from_currency').on('change', function(e){
					   $('#to_currency').val('');
					   var thisVal = $(this).val();
					   $('#to_currency option').each(function(){
					      if(thisVal == $(this).attr('value')){
					         $(this).attr('disabled', 'disabled');
					         //$(this).remove();
					       }else{
					         $(this).removeAttr('disabled');
					       }
					   })
					});
								
            		/*
	            	$('#to_currency').on('change', function(e){
					   //$('#from_currency').val('');
					   var thisVal = $(this).val();
					   $('#from_currency option').each(function(){
					      if(thisVal == $(this).attr('value')){
					         $(this).attr('disabled', 'disabled');
					         //$(this).remove();
					       }else{
					         $(this).removeAttr('disabled');
					       }
					   })
					});
					*/
            	
        });


		setInterval(function(){blink2()}, 2000);         
			function blink2() {
				$("#operator_live").fadeTo(100, 0.1).fadeTo(200, 1.0);
			}


$(document).ready(function () {			
		$(".show_more").click(function (){
		   $('.other_list').show('slow');
		   $(".show_more").hide();
		});
		
		$(".show_more2").click(function (){
		   $('.other_list2').show('slow');
		   $(".show_more2").hide();
		});		
});	

		

/*
$(document).ready(function () {
    $('a[rel="nofollow"]').contents().unwrap();
});​​​​​​​​​​​​​​​​​
*/
