function jBox2(url) {
	$.jBox("iframe:" + url, {
		top: 10,
		border: 50, 
		id: 'user',
		title: null,
		iframeScrolling: 'yes',
		showScrolling: true,
		width: 900,
		height: 'auto',
		loaded: function (h) { 
		if ($('#jbox-iframe').contents().find('#form').height() > $(window).height() - 100) {
				$('#jbox-iframe').css('height',  $(window).height() - 200);
			}else {
				$('#jbox-iframe').css('height', $('#jbox-iframe').contents().find('#form').height());
			}
			console.log($(window).height() + "|"+ $('#jbox-iframe').contents().find('#form').height()); 
		},
		buttons: {}
	});
}

$(function () {	
	
	if (decodeURIComponent(window.location.search.substring(1)) == "") {
		//console.log("a"+decodeURIComponent(window.location.search.substring(1)));
		new jBox('Confirm', {
			content: '您確定撥款?',
			confirmButton: '確定',
			cancelButton: '取消'
		});
	}
	try {	
		$("#table-1").floatThead({
			position: 'fixed'
		});
	}catch(err) {
	
	}
	var attr = getUrlParameter('attr');
	var func = getUrlParameter('func');
	//console.log(func + 'attr ='+attr);
	if (func == "reconcile" || func == "delay" || func == "invoice" || (func == "funding" && attr == null)) {
		//alert(func);
		$.post("admin.php?"+decodeURIComponent(window.location.search.substring(1)),'',function (data){
			//$('#divLoading').hide();
		  	//console.log("a"+data);
			//eval(data);
			$('.table.table-condensed.table-striped.table-bordered.table-hover tbody tr').detach();
			$('.table.table-condensed.table-striped.table-bordered.table-hover tbody').append(data);
			$('.searchresults_count').html($('.table.table-condensed.table-striped.table-bordered.table-hover tbody tr').length);
			new jBox('Confirm', {
				content: '您確定撥款?',
				confirmButton: '確定',
				cancelButton: '取消'
			});
		});
	}			
	if (func == "Holiday") {
		$.post("admin.php?"+decodeURIComponent(window.location.search.substring(1)),'',function (data){
			//$('#divLoading').hide();
		  	//console.log("a"+data);
			//eval(data);
			$('.table.table-condensed.table-striped.table-bordered.table-hover tbody tr').detach();
			$('.table.table-condensed.table-striped.table-bordered.table-hover tbody').append(data);
			$('.searchresults_count').html($('.table.table-condensed.table-striped.table-bordered.table-hover tbody tr').length);
			var sno;
			$('input[id="DelHolidayButton"]').on('click', function (Event) {
				sno = $(this).data('sno');
			});
			
			new jBox('Confirm', {
				content: '您確定刪除休假日?',
				confirmButton: '確定',
				cancelButton: '取消',
				confirm: function() {
					$.post("admin.php?"+decodeURIComponent(window.location.search.substring(1)),"Sno="+sno+"&operate=DelHoliday",function (data){
						//console.log(data);
						eval(data);
					});
					
				}
			});
		});
	}
	if (func == "invoice") {
		var today = new Date();
		console.log(today.getMonth() +1);
		$('#Year').val(today.getFullYear());
		if ((today.getMonth() + 1) < 10) {
			$('#Month').val("0" + (today.getMonth() +1));
		}else {
			$('#Month').val((today.getMonth() +1));
		}
		
	}
			
	$('.pull.btn.btn-info.btn').on('click', function (Event) {
		//alert($("form").serialize());
		//$('#divLoading').show();
		//console.log($("form").serialize());
		$.post("admin.php?"+decodeURIComponent(window.location.search.substring(1)),$("form").serialize(),function (data){
			console.log(data);
			$('.table.table-condensed.table-striped.table-bordered.table-hover tbody tr').detach();
			$('.table.table-condensed.table-striped.table-bordered.table-hover tbody').append(data);
			$('.searchresults_count').html($('.table.table-condensed.table-striped.table-bordered.table-hover tbody tr').length);
			new jBox('Confirm', {
				content: '您確定撥款?',
				confirmButton: '確定',
				cancelButton: '取消'
			});
		});
	});
	$('input[id="AddHolidayButton"]').on('click', function (Event) {
		$.post("admin.php?"+decodeURIComponent(window.location.search.substring(1)),$("form").serialize()+"&operate=AddHoliday",function (data){
			console.log(data);
			eval(data);
		});
	});
	$.datepicker.regional['zh-TW'] = {
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
	};

	$.datepicker.setDefaults($.datepicker.regional['zh-TW']);
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
	$('.btn.btn-primary.added').on('click', function (Event) {
		//alert($("form").serialize())
		$('#divLoading').show();
		//console.log('data');
		$.post("admin.php?func=Added",$("form").serialize(),function (data){
			$('#divLoading').hide();
		  	//alert(data);
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
	
	$('.btn.btn-primary.setfundingdetail').on('click', function (Event) {
		console.log(getUrlParameter('Sno'));
		$('#divLoading').show();
		//console.log('data');
		$.post("admin.php?func=Detail",$("form").serialize()+"&Sno="+getUrlParameter('Sno'),function (data){
			$('#divLoading').hide();
		  	console.log(data);
			eval(data);
		});
	});
	
	$('.btn.btn-primary.setremark').on('click', function (Event) {
		//alert($("form").serialize())
		$('#divLoading').show();
		var Sno = getUrlParameter('Sno');
		//console.log('data');
		$.post("admin.php?func=SetRemark",$("form").serialize()+"&Sno="+Sno,function (data){
			$('#divLoading').hide();
		  	console.log(data);
			eval(data);
		});
	});	
	
	$('input[name="Transfer"]').blur(function(){
		console.log($('td[id="TotalAmount"]').html());
		$('td[id="RealFunding"]').html((parseFloat($('td[id="TotalAmount"]').html()) - parseFloat($('td[id="TotalFee"]').html()) - parseFloat($(this).val()) - parseFloat($('input[name="Other"]').val())));
	});
	
	$('input[name="Other"]').blur(function(){
		console.log($('td[id="TotalAmount"]').html());
		$('td[id="RealFunding"]').html((parseFloat($('td[id="TotalAmount"]').html()) - parseFloat($('td[id="TotalFee"]').html()) - parseFloat($('input[name="Transfer"]').val()) - parseFloat($(this).val())));
	});
	
	if (func == "delaydetail") {
		
		$('.datepick').each(function(i) {
		this.id = 'datepicker' + i;
		//console.log('datepicker' + i);
		}).datepicker({
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
		
		$('a[id="ChERbtn"]').each(function(i) {
			//console.log("i"+i);
			$(this).on('click', function () {
				//console.log('Sno='+$(this).prev().prev().val() + "&ChExpectedRecordedDate="+$(this).prev().val());
				$.post("admin.php?"+decodeURIComponent(window.location.search.substring(1)),'Sno='+$(this).prev().prev().val() + "&ChExpectedRecordedDate="+$(this).prev().val(),function (data){
					eval(data);
				});
				//console.log('Sno='+$(this).prev().val() + "  Val"+$(this).val());
			});
		});
	}
	
	$('button[id="InvoiceRemarkSave"]').on('click', function (Event) {
		//alert($("form").serialize())
		$('#divLoading').show();
		var FirmSno = getUrlParameter('Sno');
		var InvoiceDate = getUrlParameter('IYear') + "-"+getUrlParameter('IMonth');
		//console.log(InvoiceDate);
		$.post("admin.php?func=InvoiceDetail",$("form").serialize()+"&FirmSno="+FirmSno+"&InvoiceDate="+InvoiceDate,function (data){
			$('#divLoading').hide();
		  	console.log(data);
			eval(data);
		});
	});	
	
	$('input[id="AlterDataButton"]').on('click', function (Event) {
		var Sno = getUrlParameter('Sno');
		console.log('Sno');
		$.post("admin.php?func=Alter",$("form").serialize()+"&Sno="+Sno,function (data){
			//$('#divLoading').hide();
		  	console.log(data);
			eval(data);
		});
	});
	
	$('input[id="IncreaseDataButton"]').on('click', function (Event) {
		var Sno = getUrlParameter('Sno');
		console.log('Sno');
		$.post("admin.php?func=Increase",$("form").serialize()+"&Sno="+Sno,function (data){
			//$('#divLoading').hide();
		  	console.log(data);
			eval(data);
		});
	});
});
function Refund(Sno) {
	//console.log('Sno='+Sno);
	$.post("admin.php?func=Refund",'Sno='+Sno,function (data){
			//console.log(data);
			eval(data);
			
	});
}

function RefundProcess(Sno, State) {
	$.post("admin.php?func=RefundProcess",'Sno='+Sno+"&State="+State,function (data){
			//console.log(data);
			eval(data);
			
	});
}

function Funding(FirmSno, Period) {
	console.log(FirmSno+'|'+Period);
	$.post("admin.php?func=setfunding",'FirmSno='+FirmSno+"&Period="+Period,function (data){
			console.log(data);
			eval(data);
			$.post("admin.php?"+decodeURIComponent(window.location.search.substring(1)),'',function (data){
			//$('#divLoading').hide();
		  	//console.log(data);
			//eval(data);
			$('.table.table-condensed.table-striped.table-bordered.table-hover tbody tr').detach();
			$('.table.table-condensed.table-striped.table-bordered.table-hover tbody').append(data);
			
			new jBox('Confirm', {
				content: '您確定撥款?',
				confirmButton: '確定',
				cancelButton: '取消'
			});
		});
	});
		
}

function Audit(FirmSno, Period) {
	//console.log(FirmSno+'|'+Period);
	$.post("admin.php?func=setaudit",'FirmSno='+FirmSno+"&Period="+Period,function (data){
			//console.log(data);
			eval(data);
			$.post("admin.php?"+decodeURIComponent(window.location.search.substring(1)),'',function (data){
			//$('#divLoading').hide();
		  	//console.log(data);
			//eval(data);
			$('.table.table-condensed.table-striped.table-bordered.table-hover tbody tr').detach();
			$('.table.table-condensed.table-striped.table-bordered.table-hover tbody').append(data);
			
			new jBox('Confirm', {
				content: '您確定撥款?',
				confirmButton: '確定',
				cancelButton: '取消'
			});
		});
	});
		
}

function Resend(Sno) {
	
	//console.log('Sno='+Sno);
	$.post("admin.php?func=Resend",'Sno='+Sno,function (data){
			//console.log(data);
			eval(data);
			
	});
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