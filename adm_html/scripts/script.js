$(function () {			 
	$('#form').on('keypress', function (Event) {
		if ( Event.which == 13 )
		{
			$('button[id="LoginAllpay"]').click();
		}
	});

	$('button[id="LoginAllpay"]').on('click', function (Event) {
		//alert($("form").serialize())
		//$('#divLoading').show();
		$.post("admin.php?func=login",$("form").serialize(),function (data){
			console.log(data);
			eval(data);
			$('#LoginVerify').attr('src','img.php');
		}).fail(function(xhr, status, error) {
			//alert( "error"+xhr );
			console.log(xhr);
			alert(xhr.responseText)
		});
	});
});