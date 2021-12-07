$(function () {			 
	/*$.datepicker.regional['zh-TW'] = {
		clearText: '清除', clearStatus: '清除已選日期',
		closeText: '關閉', closeStatus: '取消選擇',
		prevText: '<上一月', prevStatus: '顯示上個月',
		nextText: '下一月>', nextStatus: '顯示下個月',
		currentText: '今天', currentStatus: '顯示本月',
		monthNames: ['一月', '二月', '三月', '四月', '五月', '六月',
		'七月', '八月', '九月', '十月', '十一月', '十二月'],
		monthNamesShort: ['一', '二', '三', '四', '五', '六',
		'七', '八', '九', '十', '十一', '十二'],
		monthStatus: '選擇月份', yearStatus: '選擇年份',
		weekHeader: '周', weekStatus: '',
		dayNames: ['星期日', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六'],
		dayNamesShort: ['周日', '周一', '周二', '周三', '周四', '周五', '周六'],
		dayNamesMin: ['日', '一', '二', '三', '四', '五', '六'],
		dayStatus: '設定每周第一天', dateStatus: '選擇 m月 d日, DD',
		dateFormat: 'yy-mm-dd', firstDay: 1,
		initStatus: '請選擇日期', isRTL: false
	};*/

	//$.datepicker.setDefaults($.datepicker.regional['zh-TW']);
	try {
		$("#StartTime").datetimepicker({
			dateFormat: "yy-mm-dd",
			yearRange: "-20:+20",
			changeYear: true,
			changeMonth: true,
			timeText: '時間',
			hourText: '小時',
			minuteText: '分鐘',
			currentText: '今天',
			closeText : "確定",
			regional: 'zh-TW',
			showButtonPanel: true
		});
		$("#EndTime").datetimepicker({
			dateFormat: "yy-mm-dd",
			yearRange: "-20:+20",
			changeYear: true,
			changeMonth: true,
			timeText: '時間',
			hourText: '小時',
			minuteText: '分鐘',
			currentText: '今天',
			closeText : "確定",
			regional: 'zh-TW',
			showButtonPanel: true
		});
	}catch(err) {
	
	}
	
	var attr = getUrlParameter('attr');
	console.log(getUrlParameter('func'));
	if (attr == "commission") {
		$('.pageheader > h2').html('交易手續費');
		$('td > #EditJBox').each(function(){
			$(this).hide();
		});
		$('td > #DELJBox').each(function(){
			$(this).hide();
		});
		$('td > #SETCommissionJBox').each(function(){
			$(this).show();
		});
		
		$('td > #SETInterfacedJBox').each(function(){
			$(this).hide();
		});
		$('td > #SetRefusalJBox').each(function(){
			$(this).hide();
		});
	}
	
	if (attr == "interfaced") {
		$('.pageheader > h2').html('系統介接設定');
		$('td > #EditJBox').each(function(){
			$(this).hide();
		});
		$('td > #DELJBox').each(function(){
			$(this).hide();
		});
		$('td > #SETCommissionJBox').each(function(){
			$(this).hide();
		});
		
		$('td > #SETInterfacedJBox').each(function(){
			$(this).show();
		});
		
		$('td > #SetRefusalJBox').each(function(){
			$(this).hide();
		});
	}
	if (getUrlParameter('func') == "RefusalIP") {
		$('.pageheader > h2').html('拒絕交易IP管理');
		$('td > #EditJBox').each(function(){
			$(this).hide();
		});
		$('td > #DELJBox').each(function(){
			$(this).hide();
		});
		
		$('td > #SETCommissionJBox').each(function(){
			$(this).hide();
		});
		
		$('td > #SETInterfacedJBox').each(function(){
			$(this).hide();
		});
		
		$('td > #SetRefusalJBox').each(function(){
			$(this).show();
		});
	}
	$('.btn.btn-primary.added').on('click', function (Event) {
		//alert($("form").serialize())
		$('#divLoading').show();
		//console.log('data');
		$.post("admin.php?func=Added",$("form").serialize(),function (data){
			$('#divLoading').hide();
		  	//console.log(data);
			eval(data);
		});
	});
	
	$('.btn.btn-primary.addedbranch').on('click', function (Event) {
		var Sno = getUrlParameter('Sno');
		$('.form-group.form-group-sm.parentstore').show();
		$('#myCustUpdateModalLabel').html('增加分店資料');
		$('.btn.btn-primary.modify').hide();
		$('.btn.btn-primary.addedconfirmbranch').show();
		$('.btn.btn-primary.addedbranch').hide();
		$('.control-label.parentname').html($("input[type='text'][name='Name']").val());
		$('.control-label.parentcode').html($("input[type='text'][name='FirmCode']").val());
		$("input[name='ParentSno']").val(Sno);
		$("html, body").animate({ scrollTop: 0 }, 0);
	});
	
	$('.btn.btn-primary.addedconfirmbranch').on('click', function (Event) {
		//alert($("form").serialize())
		$('#divLoading').show();
		//console.log('data');
		$.post("admin.php?func=AddedBranch",$("form").serialize(),function (data){
			$('#divLoading').hide();
		  	//console.log(data);
			eval(data);
		});
	});
	
	$('.btn.btn-primary.modify').on('click', function (Event) {
		//alert($("form").serialize())
		$('#divLoading').show();
		var Sno = getUrlParameter('Sno');
		//console.log('data');
		$.post("admin.php?func=Modify&Sno="+Sno,$("form").serialize(),function (data){
			$('#divLoading').hide();
		  	console.log(data);
			eval(data);
		});
	});
	
	$('.btn.btn-primary.setcommission').on('click', function (Event) {
		//console.log(getUrlParameter('Sno'));
		$('#divLoading').show();
		//console.log('data');
		$.post("admin.php?func=SetCommission",$("form").serialize()+"&FirmSno="+getUrlParameter('Sno'),function (data){
			$('#divLoading').hide();
		  	console.log(data);
			eval(data);
		});
	});
	
	$('.btn.btn-primary.setinterfaced').on('click', function (Event) {
		//console.log(getUrlParameter('Sno'));
		$('#divLoading').show();
		//console.log('data');
		$.post("admin.php?func=SetInterfaced",$("form").serialize()+"&FirmSno="+getUrlParameter('Sno'),function (data){
			$('#divLoading').hide();
		  	console.log(data);
			eval(data);
		});
	});
	
	$('.btn.btn-primary.firmsetinterfaced').on('click', function (Event) {
		//console.log(getUrlParameter('Sno'));
		$('#divLoading').show();
		//console.log('data');
		$.post("admin.php?func=FirmSetInterfaced",$("form").serialize(),function (data){
			$('#divLoading').hide();
		  	console.log(data);
			eval(data);
		});
	});
	
	$('.btn.btn-primary.refusalip').on('click', function (Event) {
		//console.log(getUrlParameter('Sno'));
		$('#divLoading').show();
		//console.log('data');
		$.post("admin.php?func=RefusalIP",$("form").serialize(),function (data){
			$('#divLoading').hide();
		  	console.log(data);
			eval(data);
		});
	});

	$('.btn.btn-primary.setrefusalip').on('click', function (Event) {
		//console.log(getUrlParameter('Sno'));
		$('#divLoading').show();
		//console.log('data');
		$.post("admin.php?func=SetRefusalIP",$("form").serialize(),function (data){
			$('#divLoading').hide();
		  	console.log(data);
			eval(data);
		});
	});
	
	$('input[name="withdrawbutton"]').on('click', function (Event) {
		var filedata = window.location.href.substr(window.location.href.lastIndexOf("/") + 1);console.log(filedata);
		$(this).attr('disabled', 'disabled');
		//$(this).attr('value',' 资料送出中 ');
		$.post(filedata,$("form[name='WithdrawPointsForm']").serialize(),function (data){
			//var text = '[{ "firstName":"John" , "lastName":"Doe" }]';
			//var obj = JSON.parse(text);
			//alert(obj[0].firstName);
			
			console.log(data);
			eval(data);
			$('input[name="withdrawbutton"]').removeAttr('disabled');
		  });
	});
	
	$('input[name="generalbutton"]').on('click', function (Event) {
		$.post("admin.php?"+decodeURIComponent(window.location.search.substring(1)),$("form").serialize(),function (data){
			console.log(data);
			$('.table.table-condensed.table-striped.table-bordered.table-hover tbody tr').detach();
			$('.table.table-condensed.table-striped.table-bordered.table-hover tbody').append(data);
			$('.searchresults_count').html($('.table.table-condensed.table-striped.table-bordered.table-hover tbody tr').length);
		});
	});
	
	$('input[name="managebutton"]').on('click', function (Event) {
		GetWithdrawManage();		
	});
	
	$('input[name="insteadbutton"]').on('click', function (Event) {
		var result = { };
		$.each($('form[id="form1"]').serializeArray(), function() {
			result[this.name] = this.value;
		});
		console.log(result);
		post_to_url('admin.php?func=Instead', result);
	});
	
	$('select[id="ProductType"][data="Manage"],select[id="FirmSno"][data="Manage"]').on('change', function (Event) {
		console.log("Manage");
		$.post("admin.php?"+decodeURIComponent(window.location.search.substring(1)),"func=GetPoints&FirmSno="+$('select[id="FirmSno"][data="Manage"]').val()+"&ProductType="+$('select[id="ProductType"][data="Manage"]').val(),function (data){
			//console.log(data);
			$('div[id="surplus"]').html(data)
		});
	});
	$('select[id="ProductType"][data="General"]').on('change', function (Event) {
		//console.log("admin.php?"+decodeURIComponent(window.location.search.substring(1)));
		console.log("ProductType");
		$.post("admin.php?"+decodeURIComponent(window.location.search.substring(1)),"func=GetPoints&ProductType="+$(this).val(),function (data){
			//console.log(data);
			$('div[id="surplus"]').html(data)
		});
	});
	
	
});
function GetWithdrawManage() {
		$.post("admin.php?"+decodeURIComponent(window.location.search.substring(1)),$("form").serialize(),function (data){
			//console.log(data);
			$('.table.table-condensed.table-striped.table-bordered.table-hover tbody tr').detach();
			$('.table.table-condensed.table-striped.table-bordered.table-hover tbody').append(data);
			$('.searchresults_count').html($('.table.table-condensed.table-striped.table-bordered.table-hover tbody tr').length);
			var sno;
			$('a[id="Refund"]').on('click', function (Event) {
				sno= $(this).data('sno');
			});
			new jBox('Confirm', {
				content: '您確定要退款?',
				confirmButton: '確定退款!',
				cancelButton: '取消',
				confirm: function() {
					//$(this).dialog("close");
					$.post("admin.php?"+decodeURIComponent(window.location.search.substring(1)),"operate=Refund&Sno="+sno,function (data){
						console.log("postRefund " + data);
						if (data == "success") {
							GetWithdrawManage();
						}else {
							alert(data);
						}
					});
				}
			});
			/*$('a[id="Refund"]').on('click', function (Event) {
				console.log("Refund" + $(this).data('sno'));
			});*/
			$('a[id="ChangeStatus"]').on('click', function (Event) {
				//console.log("ChangeStatus");
				if (confirm("確定要變更")==true){ 
					$.post("admin.php?"+decodeURIComponent(window.location.search.substring(1)),"operate=SendChange&Status="+$(this).parent().prev().children().val()+"&Sno="+$(this).data('sno'),function (data){
						alert(data);
					});
					//alert($(this).parent().prev().children().val());
					//alert($(this).data('sno'));
				}
			});
		});
	}
function post_to_url(path, params, method) {
    method = method || "post"; // Set method to post by default, if not specified.

    // The rest of this code assumes you are not using a library.
    // It can be made less wordy if you use one.
    var form = document.createElement("form");
    form.setAttribute("method", method);
    form.setAttribute("action", path);
	form.setAttribute("target", "_blank");

    for(var key in params) {
        var hiddenField = document.createElement("input");
        hiddenField.setAttribute("type", "hidden");
        hiddenField.setAttribute("name", key);
        hiddenField.setAttribute("value", params[key]);

        form.appendChild(hiddenField);
    }

    document.body.appendChild(form);    // Not entirely sure if this is necessary
    form.submit();
}
var getUrlParameter = function getUrlParameter(sParam) {
    var sPageURL = decodeURIComponent(window.location.search.substring(1)),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
            return sParameterName[1] === undefined ? true : sParameterName[1];
        }
    }
};