var maxNum = 99999;  
var minNum = 0;  
var n = Math.floor(Math.random() * (maxNum - minNum + 1)) + minNum; 

var d = new Date(Date.now());
var MerTradeID = d.getFullYear().toString().substr(2,2) + (d.getMonth() + 1).toString() + d.getDate().toString().replace(/\b(\d)\b/g, "0$1") + padLeft(n, 5);

var n2 = Math.floor(Math.random() * (maxNum - minNum + 1)) + minNum; 
var MerProductID = document.getElementById("HashKey").value.substr(0,4) + padLeft(n2, 5); 

var n3 = Math.floor(Math.random() * (maxNum - minNum + 1)) + minNum; 
var MerUserID = "UserID" + padLeft(n3, 5); 

console.log(MerUserID);

document.getElementById("MerTradeID").value = MerTradeID;
document.getElementById("MerProductID").value = MerProductID;
document.getElementById("MerUserID").value = MerUserID;
document.getElementById("ItemName").value = "ItemName";


function padLeft(str, len) {
    str = '' + str;
    if (str.length >= len) {
        return str;
    } else {
        return padLeft("0" + str, len);
    }
}

function SelPaymentType() {	
	
	switch (document.querySelector('input[name="PaymentType"]:checked').value) {
		case "0":
			//document.getElementById("InstallmentDraw").style.display="inline";
			document.getElementById("form1").action = posturi+"CreditPayment.php";
			document.getElementsByName('Amount')[0].placeholder='請填寫金額;金額需大30元;分期金額需大於100元';
			document.getElementById("AmountLayout").style.display = 'flex';
			document.getElementById("ChoosePayment2").style.display = 'none';
			document.getElementById("Currency").innerHTML = '新台幣';
			break;
		case "1":
			//document.getElementById("InstallmentDraw").style.display="none";
			document.getElementById("form1").action = posturi+"VirAccountPayment.php";
			document.getElementsByName('Amount')[0].placeholder='請填寫金額;金額需大30元';
			document.getElementById("AmountLayout").style.display = 'flex';
			document.getElementById("ChoosePayment").style.display = 'none';
			document.getElementById("ChoosePayment2").style.display = 'none';
			document.getElementById("Currency").innerHTML = '新台幣';
			//console.log(document.getElementById("form1").action);
			break;
		case "2":
			//document.getElementById("InstallmentDraw").style.display="none";
			document.getElementById("form1").action = posturi+"StorePayment.php";
			document.getElementsByName('Amount')[0].placeholder='請填寫金額;金額需大30元';
			document.getElementById("AmountLayout").style.display = 'flex';
			document.getElementById("ChoosePayment").style.display = 'flex';
			document.getElementById("ChoosePayment2").style.display = 'none';
			document.getElementById("Currency").innerHTML = '新台幣';
			//console.log(document.getElementById("form1").action);
			break;
		case "3":
			//document.getElementById("InstallmentDraw").style.display="none";
			document.getElementById("form1").action =  posturi+"KGI12Payment.php";
			document.getElementsByName('Amount')[0].placeholder='請填寫金額';
			document.getElementById("AmountLayout").style.display = 'none';
			document.getElementById("ChoosePayment").style.display = 'none';
			document.getElementById("ChoosePayment2").style.display = 'none';
			document.getElementById("Currency").innerHTML ='新台幣';
			//document.getElementById("ChoosePayment").value = 'WebATM';
			//console.log(document.getElementById("form1").action);
			break;
		/*case "3":
			document.getElementById("InstallmentDraw").style.display="none";
			document.getElementById("form1").action = posturi+"WecahtPayment.php";
			document.getElementsByName('Amount')[0].placeholder='請填寫金額';
			document.getElementById("OnlineBank").style.display = 'none';
			document.getElementById("Currency").innerHTML ='人民幣';
			//console.log(document.getElementById("form1").action);
			break;*/
		case "4":
			//document.getElementById("InstallmentDraw").style.display="none";
			document.getElementById("form1").action =  posturi+"FirstPayment.php";
			document.getElementsByName('Amount')[0].placeholder='請填寫金額';
			document.getElementById("AmountLayout").style.display = 'none';
			document.getElementById("ChoosePayment").style.display = 'none';
			document.getElementById("ChoosePayment2").style.display = 'none';
			document.getElementById("Currency").innerHTML ='新台幣';
			break;
		case "5":
			//document.getElementById("InstallmentDraw").style.display="none";
			document.getElementById("form1").action = posturi+"TelecomPayment.php";
			document.getElementsByName('Amount')[0].placeholder='請填寫金額;金額需大30元';
			document.getElementById("AmountLayout").style.display = 'flex';
			document.getElementById("ChoosePayment").style.display = 'none';
			document.getElementById("ChoosePayment2").style.display = 'flex';
			document.getElementById("Currency").innerHTML = '新台幣';
			//console.log(document.getElementById("form1").action);
			break;
		/*case "4-1":
			document.getElementById("InstallmentDraw").style.display="none";
			document.getElementById("form1").action =  posturi+"OnlineBankPayment.php?mobile=1";
			document.getElementsByName('Amount')[0].placeholder='請填寫金額';
			document.getElementById("OnlineBank").style.display = 'inherit';
			document.getElementById("Currency").innerHTML ='人民幣';
			var drop = document.getElementById("BankCode");
			for (var i = 0; i < drop.length; i++){
				var option = drop.options[i];
				//console.log(option.value);
				
				//alert(option.value);
				if (option.value != 'ICBC' && option.value != 'ABC' && option.value != 'CCB' && option.value != 'CMBCHINA' && option.value != 'CIB' && option.value != 'CEB' && option.value != 'BOC' && option.value != 'SPDB' && option.value != 'GDFZYH' && option.value != 'POST') {
					option.setAttribute("hidden", "");
				}
			}
			//console.log(document.getElementById("form1").action);
			break;
		case "5":
			document.getElementById("InstallmentDraw").style.display="none";
			document.getElementById("form1").action = posturi+"AliPayment.php";
			document.getElementsByName('Amount')[0].placeholder='請填寫金額';
			document.getElementById("OnlineBank").style.display = 'none';
			document.getElementById("Currency").innerHTML ='人民幣';
			//console.log(document.getElementById("form1").action);
			break;
		case "6":
			document.getElementById("InstallmentDraw").style.display="none";
			document.getElementById("form1").action = posturi+"WecahtWapPayment.php";
			document.getElementsByName('Amount')[0].placeholder='請填寫金額';
			document.getElementById("OnlineBank").style.display = 'none';
			document.getElementById("Currency").innerHTML ='人民幣';
			//console.log(document.getElementById("form1").action);
			break;
		case "7":
			document.getElementById("InstallmentDraw").style.display="none";
			document.getElementById("form1").action =  posturi+"WebATMPayment.php";
			document.getElementsByName('Amount')[0].placeholder='請填寫金額';
			document.getElementById("OnlineBank").style.display = 'none';
			document.getElementById("Currency").innerHTML ='新台幣';
			document.getElementById("ChoosePayment").value = 'WebATM';
			//console.log(document.getElementById("form1").action);
			break;
		case "8":
			document.getElementById("InstallmentDraw").style.display="none";
			document.getElementById("form1").action =  posturi+"QQPayment.php";
			document.getElementsByName('Amount')[0].placeholder='請填寫金額';
			document.getElementById("OnlineBank").style.display = 'none';
			document.getElementById("Currency").innerHTML ='人民幣';
			//console.log(document.getElementById("form1").action);
			break;
		case "9":
			document.getElementById("InstallmentDraw").style.display="none";
			document.getElementById("form1").action =  posturi+"UnionPayment.php";
			document.getElementsByName('Amount')[0].placeholder='請填寫金額';
			document.getElementById("OnlineBank").style.display = 'none';
			document.getElementById("Currency").innerHTML ='人民幣';
			//console.log(document.getElementById("form1").action);
			break;*/
		case "10":
			document.getElementById("InstallmentDraw").style.display="none";
			document.getElementById("form1").action =  posturi+"QuickPayment.php";
			document.getElementsByName('Amount')[0].placeholder='請填寫金額';
			document.getElementById("OnlineBank").style.display = 'none';
			document.getElementById("Currency").innerHTML ='人民幣';
			//console.log(document.getElementById("form1").action);
			/*var drop = document.getElementById("BankCode");
			for (var i = 0; i < drop.length; i++){
				var option = drop.options[i];
				option.removeAttribute("hidden", "");
			}*/
			break;
	}
	
}
