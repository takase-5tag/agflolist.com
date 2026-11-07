
$(function(){
//ハンバーガーメニュー
$('.menu__hamburger__button').each(function() {
    $('.menu__hamburger__button').on("click touchend", function(event) {
        event.preventDefault();
        if ($('.menu__box').hasClass('is-active')) {
            $('.menu__box').removeClass('is-active');
            $('.menu__line').removeClass('active');
            $('body,html').css('overflow', 'inherit');
        } else {
            $('.menu__box').addClass('is-active');
            $('.menu__line').addClass('active');
            $('body,html').css('overflow', 'hidden');
        }
    });
    $('.menu__hamburger__button a').on('click', function() {
        $('.menu__box').removeClass('is-active');
        $('.menu__line').removeClass('active');
        $('body,html').css('overflow', 'inherit');
    });
});
//登録画面見出し挿入
jQuery('#wc_newmemberform .customer_form,#wc_member .customer_form').before('<h4>基本登録情報</h4>');
jQuery('.customkey_shipping_address_01').before('<tr><td colspan="3" style="padding: 25px;border: none;font-size: 18px;text-align: center;">配送先情報1</td></tr>');
jQuery('.customkey_shipping_address_02').before('<tr><td colspan="3" style="padding: 25px;border: none;font-size: 18px;text-align: center;">配送先情報2</td></tr>');
jQuery('.customkey_shipping_address_03').before('<tr><td colspan="3" style="padding: 25px;border: none;font-size: 18px;text-align: center;">配送先情報3</td></tr>');
jQuery('.customkey_shipping_address_04').before('<tr><td colspan="3" style="padding: 25px;border: none;font-size: 18px;text-align: center;">配送先情報4</td></tr>');
jQuery(window).on('load',function(){
jQuery('#delivery_table').css({'display':'table'});

});
});