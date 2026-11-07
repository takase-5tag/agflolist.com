$(function() {
$('video').each(function() {
var video = $('video').get(0);
video.muted = true;
});

$(document).ready(function(){
	var wH = $(window).height(); 
	var divH = $('#top-image').innerHeight();
	var hheight=$('header').height();
	if(wH > divH){
		$('#top-image').css('height',wH+'px'); 
	}
});

$(".pulldown h5").on("click", function() {
	$(this).next().slideToggle();
	$(this).toggleClass("active");
});

		
$('.slide__box').each(function() {
$('.slide__box').slick({
fade: true,
autoplay: true,
speed: 2500,
autoplaySpeed : 3000,
pauseOnFocus: false,
pauseOnHover: false,
arrows: false,
});
$('.top__visual__slide').slick({
fade: false,
autoplay: true,
speed: 2500,
autoplaySpeed : 3000,
pauseOnFocus: false,
pauseOnHover: false,
arrows: false,
});

});
});


	
	


document.addEventListener('DOMContentLoaded', () => {
  const HEADER_OFFSET = 200; // ← ヘッダー高さ分

  const links = document.querySelectorAll('.nav__list a[href*="#"]');

  links.forEach(a => {
    a.addEventListener('click', (e) => {
      const href = a.getAttribute('href');
      if (!href) return;

      const hashIndex = href.indexOf('#');
      if (hashIndex === -1) return;

      const hash = href.slice(hashIndex); // #about など
      if (!hash || hash === '#') return;

      const url = new URL(href, window.location.href);
      const samePage =
        url.origin === window.location.origin &&
        url.pathname.replace(/\/$/, '') === window.location.pathname.replace(/\/$/, '');

      if (!samePage) return;

      const target = document.querySelector(hash);
      if (!target) return;

      e.preventDefault();

      const y =
        target.getBoundingClientRect().top +
        window.pageYOffset -
        HEADER_OFFSET;

      window.scrollTo({
        top: y,
        behavior: 'smooth'
      });

      history.replaceState(null, '', hash);
    });
  });
});



