         $(document).ready(function() {
         
           $('#slides').superslides({
             animation: "slide",
             slide_speed: 10,
             pagination: true,
             hashchange: true,
             scrollable: true,
             play: 9000
           });
         
           
         
           });

         
     // Text Rotate 
         var TxtRotate = function(el, toRotate, period) {
           this.toRotate = toRotate;
           this.el = el;
           this.loopNum = 0;
           this.period = parseInt(period, 10) || 2000;
           this.txt = '';
           this.tick();
           this.isDeleting = false;
         };
         
         TxtRotate.prototype.tick = function() {
           var i = this.loopNum % this.toRotate.length;
           var fullTxt = this.toRotate[i];
         
           if (this.isDeleting) {
             this.txt = fullTxt.substring(0, this.txt.length - 1);
           } else {
             this.txt = fullTxt.substring(0, this.txt.length + 1);
           }
         
           this.el.innerHTML = '<span class="wrap">'+this.txt+'</span>';
         
           var that = this;
           var delta = 300 - Math.random() * 100;
         
           if (this.isDeleting) { delta /= 2; }
         
           if (!this.isDeleting && this.txt === fullTxt) {
             delta = this.period;
             this.isDeleting = true;
           } else if (this.isDeleting && this.txt === '') {
             this.isDeleting = false;
             this.loopNum++;
             delta = 500;
           }
         
           setTimeout(function() {
             that.tick();
           }, delta);
         };

     window.onload = function () {
         /*
           var elements = document.getElementsByClassName('txt-rotate');
           for (var i=0; i<elements.length; i++) {
             var toRotate = elements[i].getAttribute('data-rotate');
             var period = elements[i].getAttribute('data-period');
             if (toRotate) {
               new TxtRotate(elements[i], JSON.parse(toRotate), period);
             }
           }
           */
           // INJECT CSS
           var css = document.createElement("style");
           css.type = "text/css";
           //css.innerHTML = ".txt-rotate > .wrap { border-right: 1px solid #fff }";
           document.body.appendChild(css);
         };
         
         
           /*固定付款選擇頁表頭*/
//         $(document).ready(function() {
//               $("#nav").sticky({ topSpacing: 0 });
//           });
      



         $(window).load(function() {

             var $container = $('#container');
         /* 不使用
           $container.imagesLoaded( function(){
             $container.isotope({
               resizable: false,
               layoutMode : 'fitRows',
               itemSelector : '.item'
             });
           });
         */
           
           $('#options a').click(function(){
             var selector = $(this).attr('data-filter');
             $container.isotope({ filter: selector });
            
             $(this).parent().parent().find('.selected').removeClass('selected');
             $(this).addClass('selected');
             return false;
           });
         
         });

     // Onepage Nav adjust speed 
/* 不使用
     $('#top-nav').onePageNav({
         scrollSpeed: 1200,
         currentClass: 'active',
         changeHash: true,
         filter: ':not(.external)'
         });
*/
         
         			
  // JavaScript Document