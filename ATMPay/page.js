// JavaScript Document

$(function() { 


    $(document).on('click', 'input[id="SendVer"]', function (Event) {

        //alert($('form[name="data"]').serialize());
        $.post('VerifyMobile',"Mobile="+$('input[id="Mobile"]').val(),function (data) {
            try {
                var obj = JSON.parse(data);
                // console.log(obj[0]);
                if (obj["ReturnCode"] == "Ok") {
                    $('input[id="SendVer"]').hide();
                    
                    $('input[id="Mobile"]').attr("readonly","readonly");
                    alert('驗證碼己發送至您的手機,請於3分鐘內進行驗證');
                    $('tr[id="Verify"]').show();
                    $('div[id="VerifyButton"]').show();
                }else {
                    alert(obj["ReturnMessage"]);
                }
            }
            catch (e) {
                //eval(data);
                console.log(data);
            }
                    
        }).fail(function(xhr, status, error) {
            //alert( "error"+xhr );
            console.log(xhr);
            alert(xhr.responseText)
        });
    });

    $(document).on('click', 'a[id="VerifyConfirm"]', function (Event) {
        $.post('VerifyCode',"VerifyCode="+$('input[id="VerificationCode"]').val(),function (data) {
            try {
                var obj = JSON.parse(data);
                // console.log(obj[0]);
                if (obj["ReturnCode"] == "Ok") {
                    alert('驗證成功！');

                    var result = { };
                    $.each($('form[name="data"]').serializeArray(), function() {
                        result[this.name] = this.value;
                    });
                    result["Token"] = obj["Token"]
                    //console.log(result);
                    post_to_url(window.location.href.substr(window.location.href.lastIndexOf("/") + 1), result);

                }else {
                    alert(obj["ReturnMessage"]);
                }
            }
            catch (e) {
                //eval(data);
                console.log(data);
            }
                    
        }).fail(function(xhr, status, error) {
            //alert( "error"+xhr );
            console.log(xhr);
            alert(xhr.responseText)
        });
        console.log(order_buffer);
    });
});
function post_to_url(path, params, method) {
    method = method || "post"; // Set method to post by default, if not specified.

    // The rest of this code assumes you are not using a library.
    // It can be made less wordy if you use one.
    var form = document.createElement("form");
    form.setAttribute("method", method);
    form.setAttribute("action", path);
	form.setAttribute("target", "");

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
// accordion 效果
    $(document).ready(function() {

        //syntax highlighter
        hljs.tabReplace = '    ';
        hljs.initHighlightingOnLoad();

        $.fn.slideFadeToggle = function(speed, easing, callback) {
            return this.animate({opacity: 'toggle', height: 'toggle'}, speed, easing, callback);
        };

        //accordion
        $('.accordion').accordion({
            defaultOpen: 'section1',
            cookieName: 'accordion_nav',
            speed: 'slow',
            animateOpen: function (elem, opts) { //replace the standard slideUp with custom function
                elem.next().stop(true, true).slideFadeToggle(opts.speed);
            },
            animateClose: function (elem, opts) { //replace the standard slideDown with custom function
                elem.next().stop(true, true).slideFadeToggle(opts.speed);
            }
        });

    });




	