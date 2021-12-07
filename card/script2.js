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
