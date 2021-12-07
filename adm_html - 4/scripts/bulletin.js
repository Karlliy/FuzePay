$(function () {		

	$.datepicker.regional['zh-TW'] = {
		clearText: '清除', clearStatus: '清除已选日期',
		closeText: '关闭', closeStatus: '取消选择',
		prevText: '<上一月', prevStatus: '显示上个月',
		nextText: '下一月>', nextStatus: '显示下个月',
		currentText: '今天', currentStatus: '显示本月',
		monthNames: ['一月', '二月', '三月', '四月', '五月', '六月',
		'七月', '八月', '九月', '十月', '十一月', '十二月'],
		monthNamesShort: ['一', '二', '三', '四', '五', '六',
		'七', '八', '九', '十', '十一', '十二'],
		monthStatus: '选择月份', yearStatus: '选择年份',
		weekHeader: '周', weekStatus: '',
		dayNames: ['星期日', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六'],
		dayNamesShort: ['周日', '周一', '周二', '周三', '周四', '周五', '周六'],
		dayNamesMin: ['日', '一', '二', '三', '四', '五', '六'],
		dayStatus: '设定每周第一天', dateStatus: '选择 m月 d日, DD',
		dateFormat: 'yy-mm-dd', firstDay: 1,
		initStatus: '请选择日期', isRTL: false
	};
	
	$("#ReleaseTime").datetimepicker({
		dateFormat: "yy-mm-dd",
		yearRange: "-20:+20",
		changeYear: true,
		changeMonth: true,
		monthNames: ['一月', '二月', '三月', '四月', '五月', '六月',
		'七月', '八月', '九月', '十月', '十一月', '十二月'],
		monthNamesShort: ['一', '二', '三', '四', '五', '六',
		'七', '八', '九', '十', '十一', '十二'],
		monthStatus: '选择月份', yearStatus: '选择年份',
		weekHeader: '周', weekStatus: '',
		dayNames: ['星期日', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六'],
		dayNamesShort: ['周日', '周一', '周二', '周三', '周四', '周五', '周六'],
		dayNamesMin: ['日', '一', '二', '三', '四', '五', '六'],
		timeText: '时间',
		hourText: '小时',
		minuteText: '分钟',
		currentText: '今天',
		closeText : "确定",
		showButtonPanel: true
	});
			 
	$('.btn.btn-primary.added').on('click', function (Event) {
		//alert($("form").serialize())
		$('#divLoading').show();
		//console.log('data');
		$.post("admin.php?func=Added",$("form").serialize() + CKEDITOR.instances['Detail'].getData(),function (data){
			$('#divLoading').hide();
		  	//alert(data);
			eval(data);
		});
	});
	
	$('.btn.btn-primary.modify').on('click', function (Event) {
		//alert($("form").serialize())
		$('#divLoading').show();
		var Sno = getUrlParameter('Sno');
		var PostData = "";
		$.each($("form").serializeArray(), function(i, field){
			if (field.name != "Detail")
			PostData += field.name + "=" + field.value + "&";
		});
		
		PostData += "&Detail=" + CKEDITOR.instances['Detail'].getData();
		//(PostData);
		$.post("admin.php?func=Modify&Sno="+Sno,PostData,function (data){
			$('#divLoading').hide();
		  	//console.log(data);
			eval(data);
		});
	});
});

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