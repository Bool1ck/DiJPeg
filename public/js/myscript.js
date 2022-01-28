// JavaScript Document

$(document).ready(function() {
    
    $('.upload').find('.head').on('click', function() {
        if ($('.upload').find('.wrapper').css('display') == 'none') {
            $('.upload').find('.wrapper').css({'display':'block'});
        } else {
            $('.upload').find('.wrapper').css({'display':'none'});
        }
    });
	
	if ($('.data').find('img').length) {
		$('.convert').css({'display':'block'});
	} else {
		$('.convert').css({'display':'none'});
	}
	
    $('form[name=convert]').on('submit', function() {
        event.preventDefault();
		$.ajax({
			url:'app/functions/convert.php',
			type:'POST',
			data: ({id : $(document).find('form[name=convert]').attr('id'), width : $(document).find('input[id=size]').val(), contrast : (parseInt($(document).find('input[id=contrast]').val(), 10) - 256), brightness : (parseInt($(document).find('input[id=brightness]').val(), 10) - 256)}),
			dataType:"html",
			success: renewimg
		});
    });
	
	function renewimg(data) {
		$('.imgfield').empty();
		$('.imgfield').append(data);
    }
	
	$('#contrast, #brightness').on('mouseup', function() {
        $(this).parents('.set').find('.val').empty();
        $(this).parents('.set').find('.val').append(parseInt($(this).val(), 10) - 256);
    });
	
	$('#size').on('mouseup', function() {
        $(this).parents('.set').find('.val').empty();
        $(this).parents('.set').find('.val').append($(this).val());
    });
	
	$(document).on('submit', 'form[name=save]',function() {
		event.preventDefault();
		$.ajax({
			url:'app/functions/addtogalery.php',
			type:'POST',
			data: ({id : $(document).find('form[name=convert]').attr('id'), width : $(document).find('input[id=size]').val(), contrast : (parseInt($(document).find('input[id=contrast]').val(), 10) - 256), brightness : (parseInt($(document).find('input[id=brightness]').val(), 10) - 256)}),
			dataType:"html",
			success: function(){
                location.reload();
                location.href = "index.php";
            }
		});
	});
	
	$(document).on('change','input[type=range]', function() {
		$(document).find('form[name=save]').remove();
	});
    
    $('input[type=file]').change(function() {
        $('input[name=sub]').removeAttr('disabled');
    });
    
    $('.a_thumb').on('click', function() {
        event.preventDefault();
        $.ajax({
			url:'app/functions/dijpeggalery.php',
			type:'POST',
			data: ({id : $(this).attr('id')}),
			dataType:"html",
			success: dijpeggalery
		});
    });
    
    function dijpeggalery(data) {
		$('.imgfield').empty();
		$('.imgfield').append(data);
    }
	
});