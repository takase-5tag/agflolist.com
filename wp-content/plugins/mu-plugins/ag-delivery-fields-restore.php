<?php

/**
 * Plugin Name: AG FLOLIST - 配送項目表示の復旧
 * Description: テーマで非表示にしていたWelcartの配送方法・到着希望日・到着希望時間を標準表示に戻します。
 * Version: 1.0.0
 */
if (! defined('ABSPATH')) {
    exit;
}

// テーマの functions.php 読み込み後に、非表示化フィルターを取り外します。
add_action('after_setup_theme', function () {
    remove_filter('usces_filter_the_delivery_method', 'my_filter_the_delivery_method', 10);
    remove_filter('the_delivery_date', 'my_filter_the_delivery_date', 10);
    remove_filter('the_delivery_time', 'my_filter_the_delivery_time', 10);
}, 999);
