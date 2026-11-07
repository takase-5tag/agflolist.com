<?php

/**
 * Functions
 *
 * @package Welcart
 * @subpackage Welcart_Basic
 */

if (! function_exists('wp_body_open')) {
  /**
   * Wp body open
   */
  function wp_body_open()
  {
    do_action('wp_body_open');
  }
}

if (! welcart_basic_is_active('usc-e-shop/usc-e-shop.php')) {
  add_action('admin_notices', 'welcart_basic_echo_message');
}

/**
 * Welcart basic echo message
 */
function welcart_basic_echo_message()
{
  echo '<div class="message error"><p>';
  echo wp_kses_post(__('Welcart Basic theme requires <strong>Welcart e-Commerce</strong>. Please <a href="plugins.php">enable Welcart e-Commerce</a>.', 'welcart_basic'));
  echo '</p></div>';
}

/**
 * Welcart basic is active
 *
 * @param string $plugin plugin.
 */
function welcart_basic_is_active($plugin)
{
  if (function_exists('is_plugin_active')) {
    return is_plugin_active($plugin);
  } else {
    return in_array(
      $plugin,
      get_option('active_plugins'),
      true
    );
  }
}

if (! function_exists('welcart_basic_setup')) {
  /**
   * After setup theme
   */
  function welcart_basic_setup()
  {

    load_theme_textdomain('welcart_basic', get_template_directory() . '/languages');

    add_theme_support('title-tag');

    register_nav_menus(
      array(
        'header' => __('Header Navigation', 'usces'),
        'footer' => __('Footer Navigation', 'usces'),
      )
    );

    add_theme_support(
      'custom-header',
      apply_filters(
        'welcart_basic_custom_header_args',
        array(
          'default-image' => get_template_directory_uri() . '/images/image-top.jpg',
          'width'         => '1000',
          'height'        => '400',
          'header-text'   => false,
        )
      )
    );
    register_default_headers(
      array(
        'basic-default' => array(
          'url'           => '%s/images/image-top.jpg',
          'thumbnail_url' => '%s/images/image-top.jpg',
        ),
      )
    );
  }
}
add_action('after_setup_theme', 'welcart_basic_setup');


/* AG FLOLIST V17: MESSAGE + JOURNAL (初期記事・タクソノミー・アイキャッチ) */
require_once get_template_directory() . '/inc/ag-editorial-v17.php';

if (! defined('USCES_VERSION')) {
  return;
}

/**
 * Require
 */
require get_template_directory() . '/inc/template-functions.php';
require get_template_directory() . '/inc/widget-customized.php';
require get_template_directory() . '/inc/front-customized.php';
require dirname(__FILE__) . '/inc/theme-customizer.php';

/**
 * Admin enqueue scripts
 *
 * @param string $hook hook.
 */
function welcart_basic_admin_enqueue($hook)
{
  if ('welcart-shop_page_usces_itemedit' === $hook || 'widgets.php' === $hook) {
    wp_enqueue_style('basic_admin_style', get_template_directory_uri() . '/css/admin.css', array(), '1.0');
  }
}
add_action('admin_enqueue_scripts', 'welcart_basic_admin_enqueue');

/**
 * Wp footer
 */
function welcart_theme_version()
{
  $themename = 'welcart_basic';
  $theme     = wp_get_theme($themename);
  $theme_ver = ! empty($theme) ? $theme->get('Version') : '0';
  echo '<!-- Type Basic : v' . esc_html($theme_ver) . " -->\n";
}
add_action('wp_footer', 'welcart_theme_version');

/**
 * Wp page menu args
 *
 * @param array $args args.
 * @return array
 */
function welcart_basic_page_menu_args($args)
{
  $args['show_home'] = true;
  return $args;
}
add_filter('wp_page_menu_args', 'welcart_basic_page_menu_args');

/**
 * Widgets init
 */
function welcart_basic_widgets_init()
{
  require get_template_directory() . '/widgets/item-list.php';

  register_widget('Basic_Item_List');

  register_sidebar(
    array(
      'name'          => __('Home Left Widget', 'welcart_basic'),
      'id'            => 'left-widget-area',
      'description'   => __('Widget area left of the top page footer top', 'welcart_basic'),
      'before_widget' => '<section id="%1$s" class="widget %2$s">',
      'after_widget'  => '</section>',
      'before_title'  => '<h3 class="widget_title">',
      'after_title'   => '</h3>',
    )
  );

  register_sidebar(
    array(
      'name'          => __('Home Center Widget', 'welcart_basic'),
      'id'            => 'center-widget-area',
      'description'   => __('Widget area center of the top page footer top', 'welcart_basic'),
      'before_widget' => '<section id="%1$s" class="widget %2$s">',
      'after_widget'  => '</section>',
      'before_title'  => '<h3 class="widget_title">',
      'after_title'   => '</h3>',
    )
  );

  register_sidebar(
    array(
      'name'          => __('Home Right Widget', 'welcart_basic'),
      'id'            => 'right-widget-area',
      'description'   => __('Widget area right of the top page footer top', 'welcart_basic'),
      'before_widget' => '<section id="%1$s" class="widget %2$s">',
      'after_widget'  => '</section>',
      'before_title'  => '<h3 class="widget_title">',
      'after_title'   => '</h3>',
    )
  );
  register_sidebar(
    array(
      'name'          => __('Sidebar Widget 1', 'welcart_basic'),
      'id'            => 'side-widget-area1',
      'description'   => apply_filters('welcart_basic_side_widgetarea1_description', __('Widget area Product Details page or category page or search page', 'welcart_basic')),
      'before_widget' => '<section id="%1$s" class="widget %2$s">',
      'after_widget'  => '</section>',
      'before_title'  => '<h3 class="widget_title">',
      'after_title'   => '</h3>',
    )
  );

  register_sidebar(
    array(
      'name'          => __('Sidebar Widget 2', 'welcart_basic'),
      'id'            => 'side-widget-area2',
      'description'   => __('Widget area of posts and pages', 'welcart_basic'),
      'before_widget' => '<section id="%1$s" class="widget %2$s">',
      'after_widget'  => '</section>',
      'before_title'  => '<h3 class="widget_title">',
      'after_title'   => '</h3>',
    )
  );
}
add_action('widgets_init', 'welcart_basic_widgets_init');

if (! function_exists('welcart_basic_scripts_styles')) {
  /**
   * Wp enqueue scripts
   */
  function welcart_basic_scripts_styles()
  {
    global $usces, $is_IE;

    $template_dir = get_template_directory_uri();

    wp_enqueue_style('wc-basic-style', get_stylesheet_uri(), array(), '1.7.7');
    wp_enqueue_style('font-awesome', $template_dir . '/font-awesome/font-awesome.min.css', array(), '1.0');

    if (defined('WCEX_POPLINK')) {
      if (welcart_basic_is_poplink_page()) {
        wp_enqueue_style('wc_basic_poplink', $template_dir . '/css/poplink.css', array(), '1.0');
      }
    }

    if ($is_IE) {
      wp_enqueue_style('wc-basic-ie', $template_dir . '/css/ie.css', array(), '1.0');
      wp_enqueue_script('wc_basic_css3', $template_dir . '/js/css3-mediaqueries.js', array(), '1.0', false);
      wp_enqueue_script('wc-basic_html5', $template_dir . '/js/html5shiv.js', array(), '1.0', false);
    }

    wp_enqueue_script('wc-basic-js', $template_dir . '/js/front-customized.js', array(), '1.0', false);
  }
}
add_action('wp_enqueue_scripts', 'welcart_basic_scripts_styles');

/**
 * Wp enqueue scripts
 */
function welcart_basic_luminous_scripts()
{
  if (is_singular()) {
    wp_enqueue_style('luminous-basic-css', get_theme_file_uri('/css/luminous-basic.css'), array(), '1.0');
    wp_enqueue_script('luminous', get_theme_file_uri('/js/luminous.min.js'), array(), '1.0', true);
    wp_enqueue_script('wc-basic_luminous', get_theme_file_uri('/js/wb-luminous.js'), array(), '1.0', true);
  }
}
add_action('wp_enqueue_scripts', 'welcart_basic_luminous_scripts');

if (! function_exists('basic_footer_styles')) {
  /**
   * Wp footer
   */
  function basic_footer_styles()
  {

    if (is_user_logged_in()) {
      wp_enqueue_style('logged-in-style', get_theme_file_uri('/css/logged-in.css'), array(), '1.0');
    }
  }
}
add_action('wp_footer', 'basic_footer_styles');

if (! function_exists('welcart_assistance_excerpt_length')) {
  /**
   * Excerpt length
   *
   * @param int $length length.
   * @return int
   */
  function welcart_assistance_excerpt_length($length)
  {
    return 10;
  }
}
if (! function_exists('welcart_assistance_excerpt_mblength')) {
  /**
   * Excerpt mblength
   *
   * @param int $length length.
   * @return int
   */
  function welcart_assistance_excerpt_mblength($length)
  {
    return 40;
  }
}
if (! function_exists('welcart_excerpt_length')) {
  /**
   * Excerpt length
   *
   * @param int $length length.
   * @return int
   */
  function welcart_excerpt_length($length)
  {
    return 40;
  }
}
add_filter('excerpt_length', 'welcart_excerpt_length');

if (! function_exists('welcart_excerpt_mblength')) {
  /**
   * Excerpt mblength
   *
   * @param int $length length.
   * @return int
   */
  function welcart_excerpt_mblength($length)
  {
    return 110;
  }
}
add_filter('excerpt_mblength', 'welcart_excerpt_mblength');

if (! function_exists('welcart_continue_reading_link')) {
  /**
   * Continue Reading Link
   *
   * @return string
   */
  function welcart_continue_reading_link()
  {
    return ' <a href="' . get_permalink() . '">' . __('Continue reading<span class="screen-reader-text"> "%s"</span>', 'welcart_basic') . '</a>';
  }
}

/**
 * Pre get posts
 *
 * @param object $query query.
 */
function welcart_basic_query($query)
{
  $item_cat    = get_category_by_slug('item');
  $item_cat_id = $item_cat->cat_ID;
  if (is_admin() || ! $query->is_main_query()) {
    return;
  }
  if (! $query->is_admin && $query->is_search && ! get_query_var('search_item')) {
    $query->set('category_name', 'item');
  }
}
add_action('pre_get_posts', 'welcart_basic_query');

/**
 * Search form
 *
 * @param string $form form.
 * @return string
 */
function welcart_basic_search_form($form)
{
  $form = '<form role="search" method="get" action="' . home_url('/') . '" >
		<div class="s-box">
			<input type="text" value="' . get_search_query() . '" name="s" id="s-text" class="search-text" />
			<input type="submit" id="s-submit" class="searchsubmit" value="&#xf002;" />
		</div>
	</form>';
  return $form;
}
add_filter('get_search_form', 'welcart_basic_search_form');

/**
 * Header search form
 */
function get_head_search_form()
{
  $form = '<form role="search" method="get" action="' . home_url('/') . '" >
		<div class="s-box">
			<input type="text" value="' . get_search_query() . '" name="s" id="head-s-text" class="search-text" />
			<input type="submit" id="head-s-submit" class="searchsubmit" value="&#xf002;" />
		</div>
	</form>';
  echo $form;
}

/**
 * Remove hooks
 */
remove_filter('usces_filter_cart_row', 'wcmb_cart_row_of_smartphone_wct', 10, 3);
remove_filter('usces_filter_confirm_row', 'wcmb_confirm_row_of_smartphone_wct', 10, 3);

/**
 * The post
 */
function welcart_basic_the_post()
{
  global $post;

  if ('item' === $post->post_mime_type) {
    if (defined('WCEX_AUTO_DELIVERY')) {
      $product           = wel_get_product($post->ID);
      $select_sku_switch = (! empty($product['select_sku_switch'])) ? (int) $product['select_sku_switch'] : 0;
      if (! defined('WCEX_SKU_SELECT') || 1 !== (int) $select_sku_switch) {
        remove_action('usces_action_single_item_outform', 'wcad_action_single_item_outform');
        add_action('usces_action_single_item_outform', 'welcart_basic_action_single_item_outform');
      } else {
        add_filter('wcex_sku_select_filter_single_item_autodelivery', 'welcart_basic_single_item_autodelivery_sku_select');
      }
    }
  }
}
add_action('the_post', 'welcart_basic_the_post', 9);

/**
 * Theme update checker
 */
// @set_site_transient( 'update_plugins', null );
require get_template_directory() . '/inc/theme-update-checker.php';
$welcart_theme_update_checker = new ThemeUpdateChecker(
  'welcart_basic',
  'http://www.welcart.com/update_info/themes/welcart_basic.json'
);


/******************/
//フロント側
/******************/
//商品詳細ページの販売価格
function get_member_discount_rates($member_id)
{
  $rates = [
    'liquid_supplement' => 0,
    'soy_flora_soap_professional' => 0,
    'soy_flora_soap_professional_mini' => 0,
    'mom_flora_serum_professinal' => 0,
    'tow_weeks_set' => 0,
    'mom_flora_serum_salon_use' => 0,
  ];

  if (empty($member_id)) {
    return $rates;
  }

  $member = wel_get_member($member_id);

  if ($member) {
    foreach ($rates as $key => $default_value) {
      $admb_key = 'admb_' . $key;
      if (isset($member[$admb_key]) && !empty($member[$admb_key])) {
        $clean_value = preg_replace('/[^0-9.]/', '', $member[$admb_key]);
        $rates[$key] = floatval($clean_value);
      }
    }
  }
  return $rates;
}

//商品詳細ページの販売価格
global $custom_price_global;
$custom_price_global = [];
add_filter('usces_filter_the_item_price_cr', 'custom_item_price', 10, 2);
function custom_item_price($price, $post_id)
{
  global $custom_price_global;

  $product_sku = usces_the_itemSku('return');

  $sku_data = wel_get_sku($post_id, $product_sku);

  if (!empty($sku_data) && isset($sku_data['price'])) {
    $numeric_price = floatval($sku_data['price']);
  } else {
    $numeric_price = floatval(preg_replace('/[^0-9.]/', '', $price));
  }

  $discount_rate = 0;

  if (function_exists('wel_is_logged_in') && wel_is_logged_in()) {
    $mem_id = wel_logged_in_id();
    $member = wel_get_member($mem_id);

    $key = 'admb_' . $product_sku;

    if (!empty($member[$key])) {
      $discount_rate = floatval(preg_replace('/[^0-9.]/', '', $member[$key]));
    }
  }

  if ($discount_rate > 1) {
    $discount_rate = $discount_rate / 100;
  }

  $discounted_price = $numeric_price * (1 - $discount_rate);

  $custom_price_global['discounted_price'] = $discounted_price;

  return usces_crform($discounted_price, true, false, 'return');
}
// 商品詳細ページの税
add_filter('usces_filter_crform_the_itemPriceCr_taxincluded', 'debug_item_price_cr_taxincluded', 10, 7);
function debug_item_price_cr_taxincluded($result, $label_pre, $label, $symbol_pre, $symbol_post, $seperator_flag)
{
  global $custom_price_global;
  $discounted_price = isset($custom_price_global['discounted_price']) ? $custom_price_global['discounted_price'] : 0;

  $usces_tax = Welcart_Tax::get_instance();
  $tax_rate_standard = $usces_tax->tax_rate_standard;

  $result = $discounted_price + floor($discounted_price * $tax_rate_standard / 100);
  $result = '（税込 ' . '¥' . number_format($result) . '）';

  return $result;
}
//カートページの商品単価と商品合計金額
//add_filter('usces_filter_crform',  'my_filter_crform', 10, 2);
//function my_filter_crform($price, $float)
//{
//  if (is_page('usces-cart')) {
//
//    if (function_exists('wel_is_logged_in') && wel_is_logged_in()) {
//      $mem_id = wel_logged_in_id(); // ログインユーザーのIDを取得
//      $member = wel_get_member($mem_id); // 会員情報を取得
//
//      $liquid_supplement = isset($member['admb_liquid_supplement']) ? $member['admb_liquid_supplement'] : '0';
//      $soy_flora_soap_professional = isset($member['admb_soy_flora_soap_professional']) ? $member['admb_soy_flora_soap_professional'] : '0';
//      $soy_flora_soap_professional_mini = isset($member['admb_soy_flora_soap_professional_mini']) ? $member['admb_soy_flora_soap_professional_mini'] : '0';
//      $mom_flora_serum_professinal = isset($member['admb_mom_flora_serum_professinal']) ? $member['admb_mom_flora_serum_professinal'] : '0';
//      $tow_weeks_set = isset($member['admb_tow_weeks_set']) ? $member['admb_tow_weeks_set'] : '0';
//      $mom_flora_serum_salon_use = isset($member['admb_mom_flora_serum_salon_use']) ? $member['admb_mom_flora_serum_salon_use'] : '0';
//    } else {
//      $liquid_supplement = 0;
//      $soy_flora_soap_professional = 0;
//      $soy_flora_soap_professional_mini = 0;
//      $mom_flora_serum_professinal = 0;
//      $tow_weeks_set = 0;
//      $mom_flora_serum_salon_use = 0;
//    }
//
//    // 数値化
//    $numeric_price = floatval(preg_replace('/[^0-9.]/', '', $price));
//    $cart_items = wel_get_cart();
//    // カート内の商品の SKU 情報を取得
//    $skus = array_column($cart_items, 'sku');
//
//    // SKUに応じた掛け率を適用
//    if ($skus[0] === "liquid_supplement") {
//      $discounted_price = $numeric_price * (1 - $liquid_supplement);
//    } elseif ($skus[0] === "mom_flora_serum_professinal") {
//      $discounted_price = $numeric_price * (1 - $mom_flora_serum_professinal);
//    } elseif ($skus[0] === "soy_flora_soap_professional") {
//      $discounted_price = $numeric_price * (1 - $soy_flora_soap_professional);
//    } elseif ($skus[0] === "soy_flora_soap_professional_mini") {
//      $discounted_price = $numeric_price * (1 - $soy_flora_soap_professional_mini);
//    } elseif ($skus[0] === "tow_weeks_set") {
//      $discounted_price = $numeric_price * (1 - $tow_weeks_set);
//    } elseif ($skus[0] === "mom_flora_serum_salon_use") {
//      $discounted_price = $numeric_price * (1 - $mom_flora_serum_salon_use);
//    } else {
//      $discounted_price = $numeric_price;
//    }
//
//    // フィルタを一時的に無効化してから usces_crform() を呼び出す
//    remove_filter('usces_filter_crform', 'my_filter_crform', 10);
//    $formatted_price = usces_crform($discounted_price, true, false, 'return');
//    add_filter('usces_filter_crform', 'my_filter_crform', 10, 2);
//
//    return $formatted_price;
//  }
//  return $price;
//}
//function my_get_member_discount_rate_by_sku($sku)
//{
//  if (empty($sku)) {
//    return 0;
//  }
//
//  if (!function_exists('wel_is_logged_in') || !wel_is_logged_in()) {
//    return 0;
//  }
//
//  $member_id = wel_logged_in_id();
//  $member = wel_get_member($member_id);
//
//  if (empty($member)) {
//    return 0;
//  }
//
//  $key = 'admb_' . $sku;
//
//  if (empty($member[$key])) {
//    return 0;
//  }
//
//  $rate = floatval(preg_replace('/[^0-9.]/', '', $member[$key]));
//
//  if ($rate > 1) {
//    $rate = $rate / 100;
//  }
//
//  return $rate;
//}
//
//function my_apply_discount_price_to_usces_cart_session()
//{
//  if (empty($_SESSION['usces_cart']) || !is_array($_SESSION['usces_cart'])) {
//    return;
//  }
//
//  foreach ($_SESSION['usces_cart'] as $serial => $cart_row) {
//    $array = @unserialize($serial);
//
//    if (empty($array) || !is_array($array)) {
//      continue;
//    }
//
//    $ids = array_keys($array);
//    $post_id = isset($ids[0]) ? $ids[0] : 0;
//
//    if (empty($post_id) || empty($array[$post_id])) {
//      continue;
//    }
//
//    $skus = array_keys($array[$post_id]);
//    $sku = isset($skus[0]) ? $skus[0] : '';
//
//    if (empty($sku)) {
//      continue;
//    }
//
//    $sku_data = wel_get_sku($post_id, $sku);
//
//    if (empty($sku_data) || !isset($sku_data['price'])) {
//      continue;
//    }
//
//    $base_price = floatval($sku_data['price']);
//    $discount_rate = my_get_member_discount_rate_by_sku($sku);
//
//    $discounted_price = floor($base_price * (1 - $discount_rate));
//
//    $_SESSION['usces_cart'][$serial]['price'] = $discounted_price;
//    $_SESSION['usces_cart'][$serial]['unit_price'] = $discounted_price;
//  }
//}
//
//add_action('template_redirect', 'my_apply_discount_price_to_usces_cart_session', 20);
//購入履歴の単価
//add_filter('usces_filter_history_cart_row', 'custom_cart_item_price', 10, 5);
//function custom_cart_item_price($history_cart_row, $umhs, $cart_row, $i, $materials)
//{
//  global $wpdb;
//
//  // ログインしているユーザーの情報を取得
//  $member_id = wel_logged_in_id();
//  $rates_data = get_member_discount_rates($member_id);
//
//  $liquid_supplement = $rates_data['liquid_supplement'];
//  $soy_flora_soap_professional = $rates_data['soy_flora_soap_professional'];
//  $soy_flora_soap_professional_mini = $rates_data['soy_flora_soap_professional_mini'];
//  $mom_flora_serum_professinal = $rates_data['mom_flora_serum_professinal'];
//  $tow_weeks_set = $rates_data['tow_weeks_set'];
//
//  // 商品マスターから現在の登録単価を取得
//  $item_post_id = $cart_row['post_id'];
//  $item_sku_code = $cart_row['sku'];
//  $original_price = floatval($cart_row['price']); // フォールバック用
//
//  if (!empty($item_post_id) && !empty($item_sku_code)) {
//    $sku_data_from_master = wel_get_sku($item_post_id, $item_sku_code);
//    if ($sku_data_from_master && isset($sku_data_from_master['price'])) {
//      $original_price = floatval($sku_data_from_master['price']);
//    }
//  }
//
//  $sku = $cart_row['sku'];
//  $quantity = $cart_row['quantity'];
//
//  // SKUに応じた掛け率を適用して割引価格を計算
//  $discounted_price = $original_price;
//  if ($sku === "liquid_supplement") {
//    $discounted_price = $original_price * (1 - $liquid_supplement);
//  } elseif ($sku === "mom_flora_serum_professinal") {
//    $discounted_price = $original_price * (1 - $mom_flora_serum_professinal);
//  } elseif ($sku === "soy_flora_soap_professional") {
//    $discounted_price = $original_price * (1 - $soy_flora_soap_professional);
//  } elseif ($sku === "soy_flora_soap_professional_mini") {
//    $discounted_price = $original_price * (1 - $soy_flora_soap_professional_mini);
//  } elseif ($sku === "tow_weeks_set") {
//    $discounted_price = $original_price * (1 - $tow_weeks_set);
//  } elseif ($sku === "mom_flora_serum_salon_use") {
//    $discounted_price = $original_price * (1 - $mom_flora_serum_salon_use);
//  }
//
//  $formatted_price = usces_crform($discounted_price, true, false, 'return');
//  $formatted_subtotal = usces_crform($discounted_price * $quantity, true, false, 'return');
//
//  $history_cart_row = '
//        <tr>
//            <td class="cartrownum">' . ($i + 1) . '</td>
//            <td class="thumbnail">' . $materials['cart_thumbnail'] . '</td>
//            <td class="aleft productname" data-label="' . __('Items', 'usces') . '">' . $materials['cartItemName'] . '</td>
//            <td class="rightnum price" data-label="' . __('Unit price', 'usces') . '">' . $formatted_price . '</td>
//            <td class="rightnum quantity" data-label="' . __('Quantity', 'usces') . '">' . number_format($quantity) . '</td>
//            <td class="rightnum subtotal" data-label="' . __('Amount', 'usces') . '">' . $formatted_subtotal . '</td>
//        </tr>';
//  return $history_cart_row;
//}


//購入履歴の購入金額と税
add_filter('usces_filter_history_member_head', 'custom_total_price', 10, 2);
function custom_total_price($history_member_head, $umhs)
{
  global $wpdb;

  $order_id = $umhs['ID'];
  $order_data = wel_get_order($order_id);

  // DBに保存された合計金額と税額を直接取得
  $total_price_with_tax = isset($order_data['total_price']) ? floatval($order_data['total_price']) : 0;
  $total_tax_amount = isset($order_data['tax']) ? floatval($order_data['tax']) : 0;

  $formatted_total_price = usces_crform($total_price_with_tax, true, false, 'return');
  $formatted_total_tax = usces_crform($total_tax_amount, true, false, 'return');

  // お届け先情報を wel_get_order() で取得したデータから取得
  $order_data_delivery_shipping_address = isset($order_data['delivery']['shipping_address']) ? $order_data['delivery']['shipping_address'] : '';

  $history_member_head = preg_replace(
    '/<td class="rightnum purchase_price" data-label="[^"]*">[^<]*<\/td>/',
    '<td class="rightnum purchase_price" data-label="' . __('Purchase price', 'usces') . '">' . $formatted_total_price . '</td>',
    $history_member_head
  );
  $history_member_head = preg_replace(
    '/<td class="rightnum tax" data-label="[^"]*">[^<]*<\/td>/',
    '<td class="rightnum tax" data-label="' . __('Tax price', 'usces') . '">' . $formatted_total_tax . '</td>',
    $history_member_head
  );
  $history_member_head = preg_replace(
    '/<td class="rightnum shipping" data-label="[^"]*">[^<]*<\/td>/',
    '',
    $history_member_head
  );
  $history_member_head = preg_replace(
    '/<th class="historyrow shipping">[^<]*<\/th>/',
    '',
    $history_member_head
  );
  $history_member_head = preg_replace(
    '/<td class="rightnum cod" data-label="[^"]*">[^<]*<\/td>/',
    '',
    $history_member_head
  );
  $history_member_head = preg_replace(
    '/<th class="historyrow cod">[^<]*<\/th>/',
    '',
    $history_member_head
  );
  $history_member_head = preg_replace(
    '/<td class="rightnum discount" data-label="[^"]*">[^<]*<\/td>/',
    '<td class="rightnum destination" data-label="お届け先">' . esc_html($order_data_delivery_shipping_address) . '</td>',
    $history_member_head
  );
  $history_member_head = preg_replace(
    '/<th class="historyrow discount">[^<]*<\/th>/',
    '<th class="rightnum destination">お届け先</th>',
    $history_member_head
  );

  return $history_member_head;
}
// 購入情報の注文番号と合計金額をデータベース保存→管理画面表示用
//add_action('usces_action_reg_orderdata', 'custom_update_order_data_after_reg', 15, 1);
//function custom_update_order_data_after_reg($order_id)
//{
//  global $wpdb;
//
//  if (empty($order_id)) {
//    return;
//  }
//
//  $member_id = $wpdb->get_var($wpdb->prepare(
//    "SELECT mem_id FROM {$wpdb->prefix}usces_order WHERE ID = %d",
//    $order_id
//  ));
//
//  if (empty($member_id)) {
//    return;
//  }
//
//  $rates_data = get_member_discount_rates($member_id);
//  $liquid_supplement = $rates_data['liquid_supplement'];
//  $soy_flora_soap_professional = $rates_data['soy_flora_soap_professional'];
//  $soy_flora_soap_professional_mini = $rates_data['soy_flora_soap_professional_mini'];
//  $mom_flora_serum_professinal = $rates_data['mom_flora_serum_professinal'];
//  $tow_weeks_set = $rates_data['tow_weeks_set'];
//  $mom_flora_serum_salon_use = $rates_data['mom_flora_serum_salon_use'];
//
//  $calculated_total_price_tax_excluded = 0;
//
//  $order_cart_items = $wpdb->get_results($wpdb->prepare(
//    "SELECT ID, sku_code, price, quantity FROM {$wpdb->prefix}usces_ordercart WHERE order_id = %d",
//    $order_id
//  ), ARRAY_A);
//
//  if ($order_cart_items) {
//    foreach ($order_cart_items as $cart_item) {
//      $order_cart_item_db_id = $cart_item['ID'];
//      $sku = $cart_item['sku_code'];
//      $original_price_in_db = floatval($cart_item['price']);
//      $quantity = intval($cart_item['quantity']);
//
//      $discount_rate = 0;
//      if ($sku === "liquid_supplement") {
//        $discount_rate = $liquid_supplement;
//      } elseif ($sku === "mom_flora_serum_professinal") {
//        $discount_rate = $mom_flora_serum_professinal;
//      } elseif ($sku === "soy_flora_soap_professional") {
//        $discount_rate = $soy_flora_soap_professional;
//      } elseif ($sku === "soy_flora_soap_professional_mini") {
//        $discount_rate = $soy_flora_soap_professional_mini;
//      } elseif ($sku === "tow_weeks_set") {
//        $discount_rate = $tow_weeks_set;
//      } elseif ($sku === "mom_flora_serum_salon_use") {
//        $discount_rate = $mom_flora_serum_salon_use;
//      }
//
//      $discounted_price_to_save = $original_price_in_db * (1 - $discount_rate);
//
//      $wpdb->update(
//        $wpdb->prefix . 'usces_ordercart',
//        array(
//          'price' => $discounted_price_to_save,
//          'unitprice' => $discounted_price_to_save,
//        ),
//        array('ID' => $order_cart_item_db_id),
//        array('%f', '%f'),
//        array('%d')
//      );
//
//      $calculated_total_price_tax_excluded += $discounted_price_to_save * $quantity;
//    }
//  }
//
//  $usces_tax = Welcart_Tax::get_instance();
//  $tax_rate_standard = $usces_tax->tax_rate_standard;
//  $calculated_tax_amount = floor($calculated_total_price_tax_excluded * ($tax_rate_standard / 100));
//
//  $final_total_price = $calculated_total_price_tax_excluded + $calculated_tax_amount;
//
//  $wpdb->update(
//    $wpdb->prefix . 'usces_order',
//    array(
//      'total_price' => $final_total_price,
//      'total_items_price' => $calculated_total_price_tax_excluded,
//      'tax' => $calculated_tax_amount,
//    ),
//    array('ID' => $order_id),
//    array('%f', '%f', '%f'),
//    array('%d')
//  );
//}


//  会員データtotal_priceのみ
//global $global_order_prices;
//$global_order_prices = [];
//add_filter('usces_filter_get_total_price', 'my_usces_filter_get_total_price', 10, 2);
//function my_usces_filter_get_total_price($total_price, $cart)
//{
//  global $usces; // $usces を追加
//
//  // ログインユーザーのIDを取得
//  $member_id = wel_logged_in_id();
//
//  $rates_data = get_member_discount_rates($member_id);
//
//  $liquid_supplement = $rates_data['liquid_supplement'];
//  $soy_flora_soap_professional = $rates_data['soy_flora_soap_professional'];
//  $soy_flora_soap_professional_mini = $rates_data['soy_flora_soap_professional_mini'];
//  $mom_flora_serum_professinal = $rates_data['mom_flora_serum_professinal'];
//  $tow_weeks_set = $rates_data['tow_weeks_set'];
//  $mom_flora_serum_salon_use = $rates_data['mom_flora_serum_salon_use'];
//  $total_price = 0;
//  // カート内の商品ごとに掛け率を適用
//  foreach ($cart as $cart_row) {
//    $sku = isset($cart_row['sku_code']) ? $cart_row['sku_code'] : '';
//    $skuPrice = $cart_row['price'];
//    $quantity = $cart_row['quantity'];
//
//    $discounted_price = $skuPrice;
//    if ($sku === 'liquid_supplement') {
//      $discounted_price = $skuPrice * (1 - $liquid_supplement);
//    } elseif ($sku === 'mom_flora_serum_professinal') {
//      $discounted_price = $skuPrice * (1 - $mom_flora_serum_professinal);
//    } elseif ($sku === 'soy_flora_soap_professional') {
//      $discounted_price = $skuPrice * (1 - $soy_flora_soap_professional);
//    } elseif ($sku === 'soy_flora_soap_professional_mini') {
//      $discounted_price = $skuPrice * (1 - $soy_flora_soap_professional_mini);
//    } elseif ($sku === 'tow_weeks_set') {
//      $discounted_price = $skuPrice * (1 - $tow_weeks_set);
//    } elseif ($sku === 'mom_flora_serum_salon_use') {
//      $discounted_price = $skuPrice * (1 - $mom_flora_serum_salon_use);
//    }
//
//    $total_price += $discounted_price * $quantity;
//  }
//  //税
//  $usces_tax = Welcart_Tax::get_instance();
//  $tax_rate_standard = $usces_tax->tax_rate_standard;
//  $re_taxPrice = floor($total_price * ($tax_rate_standard / 100));
//
//  // この関数では order_id がないので global_order_prices には代入しない
//  // $order_id = isset($cart[0]['order_id']) ? $cart[0]['order_id'] : 'unknown';
//  // $global_order_prices[$order_id] = $re_taxPrice;
//
//  return $total_price;
//}
////受注リスト
global $total_price;
add_filter('usces_filter_get_ordercart_row', 'customize_ordercart_row_output', 9, 3);
function customize_ordercart_row_output($row, $order_id, $cart)
{
  global $wpdb, $total_price;
  //
  // // 注文情報からユーザーIDを取得
  // $order_meta = $wpdb->get_row($wpdb->prepare(
  //   "SELECT mem_id FROM {$wpdb->prefix}usces_order WHERE ID = %d",
  //   $order_id
  // ), ARRAY_A);
  // $member_id = $order_meta['mem_id'];
  //
  // // メタデータから掛け率を取得
  // $member_metas = $wpdb->get_results($wpdb->prepare(
  //   "SELECT meta_key, meta_value FROM {$wpdb->prefix}usces_member_meta WHERE member_id = %d",
  //   $member_id
  // ), ARRAY_A);
  //
  // $liquid_supplement = 0;
  // $soy_flora_soap_professional = 0;
  // $soy_flora_soap_professional_mini = 0;
  // $mom_flora_serum_professinal = 0;
  // $tow_weeks_set = 0;
  // $mom_flora_serum_salon_use = 0;
  //
  // foreach ($member_metas as $meta) {
  //   if ($meta['meta_key'] == 'admb_liquid_supplement') {
  //     $liquid_supplement = floatval($meta['meta_value']);
  //   }
  //   if ($meta['meta_key'] == 'admb_soy_flora_soap_professional') {
  //     $soy_flora_soap_professional = floatval($meta['meta_value']);
  //   }
  //   if ($meta['meta_key'] == 'admb_soy_flora_soap_professional_mini') {
  //     $soy_flora_soap_professional_mini = floatval($meta['meta_value']);
  //   }
  //   if ($meta['meta_key'] == 'admb_mom_flora_serum_professinal') {
  //     $mom_flora_serum_professinal = floatval($meta['meta_value']);
  //   }
  //   if ($meta['meta_key'] == 'admb_tow_weeks_set') {
  //     $tow_weeks_set = floatval($meta['meta_value']);
  //   }
  //   if ($meta['meta_key'] == 'admb_mom_flora_serum_salon_use') {
  //     $mom_flora_serum_salon_use = floatval($meta['meta_value']);
  //   }
  // }
  //
  // $total = 0;
  // $total_price = [];
  // // カート内の商品ごとに掛け率を適用し、$row を直接書き換え
  // foreach ($cart as $cart_row) {
  //   $sku = isset($cart_row['sku_code']) ? $cart_row['sku_code'] : ''; // SKUコードの取得
  //
  //   $items = wel_get_sku($cart_row['post_id'], $cart_row['sku_code']);
  //   // 元の単価
  //   if (isset($items['price'])) {
  //     $skuPrice = $items['price'];
  //   }
  //
  //   $cart_id = isset($cart_row['cart_id']) ? $cart_row['cart_id'] : ''; // カートIDの取得
  //   $quantity = $cart_row['quantity'];
  //
  //   if ($sku === 'liquid_supplement') {
  //     $discounted_price = $skuPrice * (1 - $liquid_supplement);
  //   } elseif ($sku === 'mom_flora_serum_professinal') {
  //     $discounted_price = $skuPrice * (1 - $mom_flora_serum_professinal);
  //   } elseif ($sku === 'soy_flora_soap_professional') {
  //     $discounted_price = $skuPrice * (1 - $soy_flora_soap_professional);
  //   } elseif ($sku === 'soy_flora_soap_professional_mini') {
  //     $discounted_price = $skuPrice * (1 - $soy_flora_soap_professional_mini);
  //   } elseif ($sku === 'tow_weeks_set') {
  //     $discounted_price = $skuPrice * (1 - $tow_weeks_set);
  //   } else {
  //     $discounted_price = $skuPrice;
  //   }
  //   // 値をデータベースに保存
  //   $database_prices[] = [
  //     'discounted_price' => $discounted_price,
  //   ];
  //
  //   // $row置換
  //   $pattern = '/<input name="skuPrice\[' . preg_quote($cart_id, '/') . '\]" class="text price" type="text" value="[^"]*"/';
  //   $replacement = '<input name="skuPrice[' . esc_attr($cart_id) . ']" class="text price" type="text" value="' . esc_attr(usces_crform($discounted_price, false, false, 'return', false)) . '"';
  //
  //   // 正規表現置換で $row を書き換え
  //   $row = preg_replace($pattern, $replacement, $row);
  //
  //   $discounted_price = $discounted_price * $quantity;
  //
  //   $total += $discounted_price;
  // }
  // 値をデータベースに保存
  // update_option('custom_prices', $database_prices);
  //$total_price['total'] = $total;
  //発送情報の取得追加
  //発送情報の取得追加
  $mem_ids = wel_get_order($order_id);
  $delivery_shipping_address = isset($mem_ids['delivery']['shipping_address']) ? $mem_ids['delivery']['shipping_address'] : '';

  $delivery_shipping_address_script = ''; // 初期化

  if (!empty($delivery_shipping_address)) {
    $delivery_shipping_address_script .= <<<HTML
    <script>
      jQuery(function(){
        jQuery(window).on('load',function(){
          jQuery(".deli_info select[name='custom_delivery[shipping_address]']").append(jQuery('<option />').val('{$delivery_shipping_address}').html('{$delivery_shipping_address}'));
          jQuery("[name='custom_delivery[shipping_address]'] option[value='{$delivery_shipping_address}']").prop('selected', true); 
        });
      });
    </script>
    HTML;

    echo $delivery_shipping_address_script;
  }

  return $row;
}

// 受注データ編集の消費税(10%のみ)
//add_filter('usces_filter_ordereditform_carttable', 'custom_usces_filter_ordereditform_carttable', 10, 2);
//function custom_usces_filter_ordereditform_carttable($cart_table, $filter_args)
//{
//  global $total_price;
//  $total = $total_price['total'];
//  // 値をデータベースに保存
//  update_option('custom_total_price', $total_price);
//
//  $usces_tax = Welcart_Tax::get_instance();
//  $tax_rate_standard = $usces_tax->tax_rate_standard;
//
//  $re_taxPrice =  $total * ($tax_rate_standard / 100);
//  // error_log($re_taxPrice);
//
//  $cart_table = preg_replace(
//    '/(<tr>\s*<td[^>]*>\s*10％消費税[^<]*<span[^>]*><\/span><\/td>\s*<td[^>]*><input[^>]*value=")([0-9.,]+)("[^>]*\/>\s*<\/td>)/',
//    '${1}' . $re_taxPrice . '${3}',
//    $cart_table,
//    1
//  );
//  $cart_table = preg_replace(
//    '/(<tr>\s*<td[^>]*>\s*消費税[^<]*<span[^>]*><\/span><\/td>\s*<td[^>]*><input[^>]*id="order_tax"[^>]*value=")([0-9.,]+)("[^>]*\/>\s*<\/td>)/',
//    '${1}' . $re_taxPrice . '${3}',
//    $cart_table,
//    1
//  );
//  return $cart_table;
//}
//


//メール
//add_filter('usces_filter_send_order_mail_meisai', 'my_filter_send_order_mail_meisai', 10, 4);
//function my_filter_send_order_mail_meisai($msg_meisai, $data, $cart, $entry)
//{
//  global $wpdb;
//
//  $order_id = $data['order_id'];
//
//  // 注文データから直接、保存されている合計金額と税額を取得し、メール内容を置換
//  $order_data = wel_get_order($order_id);
//  $total_price_with_tax_db = isset($order_data['total_price']) ? floatval($order_data['total_price']) : 0;
//  $total_tax_price_db = isset($order_data['tax']) ? floatval($order_data['tax']) : 0;
//  $total_items_price_tax_excluded_db = isset($order_data['total_items_price']) ? floatval($order_data['total_items_price']) : 0;
//
//  foreach ($cart as $cart_item_original) {
//    $sku = isset($cart_item_original['sku']) ? $cart_item_original['sku'] : '';
//    $original_price_db = isset($cart_item_original['price']) ? floatval($cart_item_original['price']) : 0;
//    $quantity = intval($cart_item_original['quantity']);
//
//    error_log('DEBUG: my_filter_send_order_mail_meisai - SKU: ' . $sku . ', Stored Price: ' . $original_price_db . ', Quantity: ' . $quantity);
//
//    $updated_msg_meisai = preg_replace(
//      '/(単価\s*¥)' . number_format($original_price_db) . '(\s*×\s*' . number_format($quantity) . ')/u',
//      '${1}' . number_format($original_price_db) . '${2}', // DB値をそのまま表示
//      $updated_msg_meisai,
//      1
//    );
//    $updated_msg_meisai = preg_replace(
//      '/(商品合計\s*:\s*¥)' . number_format($original_price_db * $quantity) . '/u',
//      '${1}' . number_format($original_price_db * $quantity), // DB値をそのまま表示
//      $updated_msg_meisai,
//      1
//    );
//  }
//
//  $total_items_price_formatted = usces_crform($total_items_price_tax_excluded_db, true, false, 'return');
//  $total_tax_formatted = usces_crform($total_tax_price_db, true, false, 'return');
//  $total_price_formatted = usces_crform($total_price_with_tax_db, true, false, 'return');
//  $eight_tax_formatted = usces_crform(0, true, false, 'return');
//
//  $updated_msg_meisai = preg_replace('/商品合計\s*:\s*¥[0-9,]+/u', '商品合計 : ' . $total_items_price_formatted, $updated_msg_meisai, 1);
//  $updated_msg_meisai = preg_replace('/消費税\s*:\s*¥[0-9,]+/u', '消費税 : ' . $total_tax_formatted, $updated_msg_meisai, 1);
//  $updated_msg_meisai = preg_replace('/お支払い金額\s*:\s*¥[0-9,]+/u', 'お支払い金額 : ' . $total_price_formatted, $updated_msg_meisai, 1);
//  $updated_msg_meisai = preg_replace('/10％対象\s*:\s*¥[0-9,]+/u', '10％対象 : ' . $total_items_price_formatted, $updated_msg_meisai, 1);
//  $updated_msg_meisai = preg_replace('/10％消費税\s*:\s*¥[0-9,]+/u', '10％消費税 : ' . $total_tax_formatted, $updated_msg_meisai, 1);
//  $updated_msg_meisai = preg_replace('/8％消費税\s*:\s*¥[0-9,]+/u', '8％消費税 : ' . $eight_tax_formatted, $updated_msg_meisai, 1);
//
//  return $updated_msg_meisai;
//}


//配送先設定
add_filter('usces_filter_custom_field_input', 'my_filter_custom_field_input', 10, 4);
function my_filter_custom_field_input($html, $data, $custom_field, $position)
{
  $mem_id  = wel_logged_in_id();
  $member  = wel_get_member($mem_id);
  $prefs = isset($data['customer']['pref']) ? $data['customer']['pref'] : '';

  $csmb_shipping_address_01        = isset($member['csmb_shipping_address_01']) ? $member['csmb_shipping_address_01'] : '';
  $csmb_shipping_address_02        = isset($member['csmb_shipping_address_02']) ? $member['csmb_shipping_address_02'] : '';
  $csmb_shipping_address_03        = isset($member['csmb_shipping_address_03']) ? $member['csmb_shipping_address_03'] : '';
  $csmb_shipping_address_04        = isset($member['csmb_shipping_address_04']) ? $member['csmb_shipping_address_04'] : '';

  $csmb_shipping_address_01_name      = isset($member['csmb_shipping_address_01_name']) ? $member['csmb_shipping_address_01_name'] : '';
  $csmb_shipping_address_01_name02    = isset($member['csmb_shipping_address_01_name02']) ? $member['csmb_shipping_address_01_name02'] : '';
  $csmb_shipping_address_01_zips      = isset($member['csmb_shipping_address_01_zips']) ? $member['csmb_shipping_address_01_zips'] : '';
  $csmb_shipping_address_01_pref      = isset($member['csmb_shipping_address_01_pref']) ? $member['csmb_shipping_address_01_pref'] : '';
  $csmb_shipping_address_01_address01 = isset($member['csmb_shipping_address_01_address01']) ? $member['csmb_shipping_address_01_address01'] : '';
  $csmb_shipping_address_01_address02 = isset($member['csmb_shipping_address_01_address02']) ? $member['csmb_shipping_address_01_address02'] : '';
  $csmb_shipping_address_01_address03 = isset($member['csmb_shipping_address_01_address03']) ? $member['csmb_shipping_address_01_address03'] : '';
  $csmb_shipping_address_01_tel       = isset($member['csmb_shipping_address_01_tel']) ? $member['csmb_shipping_address_01_tel'] : '';

  $csmb_shipping_address_02_name      = isset($member['csmb_shipping_address_02_name']) ? $member['csmb_shipping_address_02_name'] : '';
  $csmb_shipping_address_02_name02    = isset($member['csmb_shipping_address_02_name02']) ? $member['csmb_shipping_address_02_name02'] : '';
  $csmb_shipping_address_02_zips      = isset($member['csmb_shipping_address_02_zips']) ? $member['csmb_shipping_address_02_zips'] : '';
  $csmb_shipping_address_02_pref      = isset($member['csmb_shipping_address_02_pref']) ? $member['csmb_shipping_address_02_pref'] : '';
  $csmb_shipping_address_02_address01 = isset($member['csmb_shipping_address_02_address01']) ? $member['csmb_shipping_address_02_address01'] : '';
  $csmb_shipping_address_02_address02 = isset($member['csmb_shipping_address_02_address02']) ? $member['csmb_shipping_address_02_address02'] : '';
  $csmb_shipping_address_02_address03 = isset($member['csmb_shipping_address_02_address03']) ? $member['csmb_shipping_address_02_address03'] : '';
  $csmb_shipping_address_02_tel       = isset($member['csmb_shipping_address_02_tel']) ? $member['csmb_shipping_address_02_tel'] : '';

  $csmb_shipping_address_03_name      = isset($member['csmb_shipping_address_03_name']) ? $member['csmb_shipping_address_03_name'] : '';
  $csmb_shipping_address_03_name02    = isset($member['csmb_shipping_address_03_name02']) ? $member['csmb_shipping_address_03_name02'] : '';
  $csmb_shipping_address_03_zips      = isset($member['csmb_shipping_address_03_zips']) ? $member['csmb_shipping_address_03_zips'] : '';
  $csmb_shipping_address_03_pref      = isset($member['csmb_shipping_address_03_pref']) ? $member['csmb_shipping_address_03_pref'] : '';
  $csmb_shipping_address_03_address01 = isset($member['csmb_shipping_address_03_address01']) ? $member['csmb_shipping_address_03_address01'] : '';
  $csmb_shipping_address_03_address02 = isset($member['csmb_shipping_address_03_address02']) ? $member['csmb_shipping_address_03_address02'] : '';
  $csmb_shipping_address_03_address03 = isset($member['csmb_shipping_address_03_address03']) ? $member['csmb_shipping_address_03_address03'] : '';
  $csmb_shipping_address_03_tel       = isset($member['csmb_shipping_address_03_tel']) ? $member['csmb_shipping_address_03_tel'] : '';

  $csmb_shipping_address_04_name      = isset($member['csmb_shipping_address_04_name']) ? $member['csmb_shipping_address_04_name'] : '';
  $csmb_shipping_address_04_name02    = isset($member['csmb_shipping_address_04_name02']) ? $member['csmb_shipping_address_04_name02'] : '';
  $csmb_shipping_address_04_zips      = isset($member['csmb_shipping_address_04_zips']) ? $member['csmb_shipping_address_04_zips'] : '';
  $csmb_shipping_address_04_pref      = isset($member['csmb_shipping_address_04_pref']) ? $member['csmb_shipping_address_04_pref'] : '';
  $csmb_shipping_address_04_address01 = isset($member['csmb_shipping_address_04_address01']) ? $member['csmb_shipping_address_04_address01'] : '';
  $csmb_shipping_address_04_address02 = isset($member['csmb_shipping_address_04_address02']) ? $member['csmb_shipping_address_04_address02'] : '';
  $csmb_shipping_address_04_address03 = isset($member['csmb_shipping_address_04_address03']) ? $member['csmb_shipping_address_04_address03'] : '';
  $csmb_shipping_address_04_tel       = isset($member['csmb_shipping_address_04_tel']) ? $member['csmb_shipping_address_04_tel'] : '';

  $meta = usces_has_custom_field_meta($custom_field);

  // カスタムフィールド情報が取得できない場合に備えて初期化
  $key_class = '';
  $positions = '';

  if (is_array($meta)) {
    foreach ($meta as $key => $entry) {
      $key_class = $key;
      $positions = (is_array($entry) && isset($entry['position']))
        ? $entry['position']
        : '';
    }
  }

  $csmb_shipping_address_01_text = '';
  $csmb_shipping_address_02_text = '';
  $csmb_shipping_address_03_text = '';
  $csmb_shipping_address_04_text = '';
  $delivery_office_01 = '';
  $delivery_office_02 = '';
  $delivery_office_03 = '';
  $delivery_office_04 = '';

  if ($positions == $position && $key_class == 'shipping_address' && $custom_field == 'delivery' && (!empty($csmb_shipping_address_01) || (!empty($csmb_shipping_address_02)) || (!empty($csmb_shipping_address_03) || (!empty($csmb_shipping_address_04))))) {
    if (!empty($csmb_shipping_address_01)) {
      $csmb_shipping_address_01_text .= '<option value="' . esc_attr($csmb_shipping_address_01) . '" id="delivery_office_01_serect">' . esc_html($csmb_shipping_address_01) . '</option>';
      $delivery_office_01 .= <<<HTML
            <div class="delivery_office_01" id="delivery_office_01">
            <input type='hidden' name='csmb_shipping_address_01_name' value="{$csmb_shipping_address_01_name}">
            <input type='hidden' name='csmb_shipping_address_01_name02' value="{$csmb_shipping_address_01_name02}">
            <input type='hidden' name='csmb_shipping_address_01_zips' value="{$csmb_shipping_address_01_zips}">
            <input type='hidden' name='csmb_shipping_address_01_pref' value="{$csmb_shipping_address_01_pref}">
            <input type='hidden' name='csmb_shipping_address_01_address01' value="{$csmb_shipping_address_01_address01}">
            <input type='hidden' name='csmb_shipping_address_01_address02' value="{$csmb_shipping_address_01_address02}">
            <input type='hidden' name='csmb_shipping_address_01_address03' value="{$csmb_shipping_address_01_address03}">
            <input type='hidden' name='csmb_shipping_address_01_address03' value="{$csmb_shipping_address_01_address03}">
            <input type='hidden' name='csmb_shipping_address_01_tel' value="{$csmb_shipping_address_01_tel}">
            </div>
            HTML;
    };
    if (!empty($csmb_shipping_address_02)) {
      $csmb_shipping_address_02_text .= '<option value="' . esc_attr($csmb_shipping_address_02) . '" id="delivery_office_02_serect">' . esc_html($csmb_shipping_address_02) . '</option>';
      $delivery_office_02 .= <<<HTML
            <div class="delivery_office_02" id="delivery_office_02">
            <input type='hidden' name='csmb_shipping_address_02_name' value="{$csmb_shipping_address_02_name}">
            <input type='hidden' name='csmb_shipping_address_02_name02' value="{$csmb_shipping_address_02_name02}">
            <input type='hidden' name='csmb_shipping_address_02_zips' value="{$csmb_shipping_address_02_zips}">
            <input type='hidden' name='csmb_shipping_address_02_pref' value="{$csmb_shipping_address_02_pref}">
            <input type='hidden' name='csmb_shipping_address_02_address01' value="{$csmb_shipping_address_02_address01}">
            <input type='hidden' name='csmb_shipping_address_02_address02' value="{$csmb_shipping_address_02_address02}">
            <input type='hidden' name='csmb_shipping_address_02_address03' value="{$csmb_shipping_address_02_address03}">
            <input type='hidden' name='csmb_shipping_address_02_address03' value="{$csmb_shipping_address_02_address03}">
            <input type='hidden' name='csmb_shipping_address_02_tel' value="{$csmb_shipping_address_02_tel}">
            </div>
            HTML;
    };
    if (!empty($csmb_shipping_address_03)) {
      $csmb_shipping_address_03_text .= '<option value="' . esc_attr($csmb_shipping_address_03) . '" id="delivery_office_03_serect">' . esc_html($csmb_shipping_address_03) . '</option>';
      $delivery_office_03 .= <<<HTML
            <div class="delivery_office_03" id="delivery_office_03">
            <input type='hidden' name='csmb_shipping_address_03_name' value="{$csmb_shipping_address_03_name}">
            <input type='hidden' name='csmb_shipping_address_03_name02' value="{$csmb_shipping_address_03_name02}">
            <input type='hidden' name='csmb_shipping_address_03_zips' value="{$csmb_shipping_address_03_zips}">
            <input type='hidden' name='csmb_shipping_address_03_pref' value="{$csmb_shipping_address_03_pref}">
            <input type='hidden' name='csmb_shipping_address_03_address01' value="{$csmb_shipping_address_03_address01}">
            <input type='hidden' name='csmb_shipping_address_03_address02' value="{$csmb_shipping_address_03_address02}">
            <input type='hidden' name='csmb_shipping_address_03_address03' value="{$csmb_shipping_address_03_address03}">
            <input type='hidden' name='csmb_shipping_address_03_address03' value="{$csmb_shipping_address_03_address03}">
            <input type='hidden' name='csmb_shipping_address_03_tel' value="{$csmb_shipping_address_03_tel}">
            </div>
            HTML;
    };
    if (!empty($csmb_shipping_address_04)) {
      $csmb_shipping_address_04_text .= '<option value="' . esc_attr($csmb_shipping_address_04) . '" id="delivery_office_04_serect">' . esc_html($csmb_shipping_address_04) . '</option>';
      $delivery_office_04 .= <<<HTML
            <div class="delivery_office_04" id="delivery_office_04">
            <input type='hidden' name='csmb_shipping_address_04_name' value="{$csmb_shipping_address_04_name}">
            <input type='hidden' name='csmb_shipping_address_04_name02' value="{$csmb_shipping_address_04_name02}">
            <input type='hidden' name='csmb_shipping_address_04_zips' value="{$csmb_shipping_address_04_zips}">
            <input type='hidden' name='csmb_shipping_address_04_pref' value="{$csmb_shipping_address_04_pref}">
            <input type='hidden' name='csmb_shipping_address_04_address01' value="{$csmb_shipping_address_04_address01}">
            <input type='hidden' name='csmb_shipping_address_04_address02' value="{$csmb_shipping_address_04_address02}">
            <input type='hidden' name='csmb_shipping_address_04_address03' value="{$csmb_shipping_address_04_address03}">
            <input type='hidden' name='csmb_shipping_address_04_address03' value="{$csmb_shipping_address_04_address03}">
            <input type='hidden' name='csmb_shipping_address_04_tel' value="{$csmb_shipping_address_04_tel}">
            </div>
            HTML;
    };
    $htmls = <<<HTML
        <tr class="customkey_shipping_address">
          <th scope="row"><em>＊</em>お届け先名称</th>
          <td colspan="2">
            <div class="flex">
            <select name="custom_delivery[shipping_address]" class="iopt_select" id="shipping_address_select">
            <option value="#NONE#">選択してください</option>
            {$csmb_shipping_address_01_text}
            {$csmb_shipping_address_02_text}
            {$csmb_shipping_address_03_text}
            {$csmb_shipping_address_04_text}
            </select>
            <div class="submits" id="registered_delivery">配送先住所読み込み</div>
             </div>
            {$delivery_office_01}
            {$delivery_office_02}
            {$delivery_office_03}
            {$delivery_office_04}
            <script>
            jQuery(function(){
                jQuery('#registered_delivery').on('click',function(){
                    var shipping_address_select = $('#shipping_address_select option:selected').attr('id');
                    if ('delivery_office_01_serect' == shipping_address_select){
                        var csmb_shipping_address_01_name      = $('input[name="csmb_shipping_address_01_name"]').val();
                        var csmb_shipping_address_01_name02    = $('input[name="csmb_shipping_address_01_name02"]').val();
                        var csmb_shipping_address_01_zips      = $('input[name="csmb_shipping_address_01_zips"]').val();
                        var csmb_shipping_address_01_pref      = $('input[name="csmb_shipping_address_01_pref"]').val();
                        var csmb_shipping_address_01_address01 = $('input[name="csmb_shipping_address_01_address01"]').val();
                        var csmb_shipping_address_01_address02 = $('input[name="csmb_shipping_address_01_address02"]').val();
                        var csmb_shipping_address_01_address03 = $('input[name="csmb_shipping_address_01_address03"]').val();
                        var csmb_shipping_address_01_tel       = $('input[name="csmb_shipping_address_01_tel"]').val();
                        $('#name1').val(csmb_shipping_address_01_name);
                        $('#name2').val(csmb_shipping_address_01_name02);
                        $('#name3').val('');
                        $('#name4').val('');
                        $('#zipcode').val(csmb_shipping_address_01_zips);
                        $("[name='delivery[pref]'] option[value='" + csmb_shipping_address_01_pref + "']").prop('selected', true);
                        $('#address1').val(csmb_shipping_address_01_address01);
                        $('#address2').val(csmb_shipping_address_01_address02);
                        $('#address3').val(csmb_shipping_address_01_address03);
                        $('#tel').val(csmb_shipping_address_01_tel);
                        $('#fax').val('');
                    }else if('delivery_office_02_serect' == shipping_address_select){
                        var csmb_shipping_address_02_name      = $('input[name="csmb_shipping_address_02_name"]').val();
                        var csmb_shipping_address_02_name02    = $('input[name="csmb_shipping_address_02_name02"]').val();
                        var csmb_shipping_address_02_zips      = $('input[name="csmb_shipping_address_02_zips"]').val();
                        var csmb_shipping_address_02_pref      = $('input[name="csmb_shipping_address_02_pref"]').val();
                        var csmb_shipping_address_02_address01 = $('input[name="csmb_shipping_address_02_address01"]').val();
                        var csmb_shipping_address_02_address02 = $('input[name="csmb_shipping_address_02_address02"]').val();
                        var csmb_shipping_address_02_address03 = $('input[name="csmb_shipping_address_02_address03"]').val();
                        var csmb_shipping_address_02_tel       = $('input[name="csmb_shipping_address_02_tel"]').val();
                        $('#name1').val(csmb_shipping_address_02_name);
                        $('#name2').val(csmb_shipping_address_02_name02);
                        $('#name3').val('');
                        $('#name4').val('');
                        $('#zipcode').val(csmb_shipping_address_02_zips);
                        $("[name='delivery[pref]'] option[value='" + csmb_shipping_address_02_pref + "']").prop('selected', true);
                        $('#address1').val(csmb_shipping_address_02_address01);
                        $('#address2').val(csmb_shipping_address_02_address02);
                        $('#address3').val(csmb_shipping_address_02_address03);
                        $('#tel').val(csmb_shipping_address_02_tel);
                        $('#fax').val('');
                    }else if('delivery_office_03_serect' == shipping_address_select){
                        var csmb_shipping_address_03_name      = $('input[name="csmb_shipping_address_03_name"]').val();
                        var csmb_shipping_address_03_name02    = $('input[name="csmb_shipping_address_03_name02"]').val();
                        var csmb_shipping_address_03_zips      = $('input[name="csmb_shipping_address_03_zips"]').val();
                        var csmb_shipping_address_03_pref      = $('input[name="csmb_shipping_address_03_pref"]').val();
                        var csmb_shipping_address_03_address01 = $('input[name="csmb_shipping_address_03_address01"]').val();
                        var csmb_shipping_address_03_address02 = $('input[name="csmb_shipping_address_03_address02"]').val();
                        var csmb_shipping_address_03_address03 = $('input[name="csmb_shipping_address_03_address03"]').val();
                        var csmb_shipping_address_03_tel       = $('input[name="csmb_shipping_address_03_tel"]').val();
                        $('#name1').val(csmb_shipping_address_03_name);
                        $('#name2').val(csmb_shipping_address_03_name02);
                        $('#name3').val('');
                        $('#name4').val('');
                        $('#zipcode').val(csmb_shipping_address_03_zips);
                        $("[name='delivery[pref]'] option[value='" + csmb_shipping_address_03_pref + "']").prop('selected', true);
                        $('#address1').val(csmb_shipping_address_03_address01);
                        $('#address2').val(csmb_shipping_address_03_address02);
                        $('#address3').val(csmb_shipping_address_03_address03);
                        $('#tel').val(csmb_shipping_address_03_tel);
                        $('#fax').val('');
                    }else if('delivery_office_04_serect' == shipping_address_select){
                        var csmb_shipping_address_04_name      = $('input[name="csmb_shipping_address_04_name"]').val();
                        var csmb_shipping_address_04_name02    = $('input[name="csmb_shipping_address_04_name02"]').val();
                        var csmb_shipping_address_04_zips      = $('input[name="csmb_shipping_address_04_zips"]').val();
                        var csmb_shipping_address_04_pref      = $('input[name="csmb_shipping_address_04_pref"]').val();
                        var csmb_shipping_address_04_address01 = $('input[name="csmb_shipping_address_04_address01"]').val();
                        var csmb_shipping_address_04_address02 = $('input[name="csmb_shipping_address_04_address02"]').val();
                        var csmb_shipping_address_04_address03 = $('input[name="csmb_shipping_address_04_address03"]').val();
                        var csmb_shipping_address_04_tel       = $('input[name="csmb_shipping_address_04_tel"]').val();
                        $('#name1').val(csmb_shipping_address_04_name);
                        $('#name2').val(csmb_shipping_address_04_name02);
                        $('#name3').val('');
                        $('#name4').val('');
                        $('#zipcode').val(csmb_shipping_address_04_zips);
                        $("[name='delivery[pref]'] option[value='" + csmb_shipping_address_04_pref + "']").prop('selected', true);
                        $('#address1').val(csmb_shipping_address_04_address01);
                        $('#address2').val(csmb_shipping_address_04_address02);
                        $('#address3').val(csmb_shipping_address_04_address03);
                        $('#tel').val(csmb_shipping_address_04_tel);
                        $('#fax').val('');
                    }
                });
              //名前ラベル変更
                jQuery("#name_row th").html("<em>＊</em>会社・部門");
                jQuery('#name_row .name_td:nth-of-type(1) span.member_name').attr('id', 'company1');
                jQuery('#name_row .name_td:nth-of-type(2) span.member_name').attr('id', 'company2');
                var member_name1 = jQuery('#name_row .name_td:nth-of-type(1) span.member_name').attr('id');
                var member_name2 = jQuery('#name_row .name_td:nth-of-type(2) span.member_name').attr('id');
                jQuery('#zipcode_row td').append('<input name="delivery[name1]" id="name1" type="hidden" value="　" onkeydown="if (event.keyCode == 13) {return false;}" style="ime-mode: active">')
                jQuery('#zipcode_row td').append('<input name="delivery[name2]" id="name2" type="hidden" value="　" onkeydown="if (event.keyCode == 13) {return false;}" style="ime-mode: active">')
                if (member_name1 == 'company1'){
                  jQuery('#company1').html('会社・部門1');
                }
                if (member_name2 == 'company2'){
                  jQuery('#company2').text('会社・部門2');
                }
                window.onload = function(){
                  jQuery('input[name="custom_delivery[shipping_address]"]').val('');
                  jQuery('input[name="delivery[name1]"]').val('　');
                  jQuery('input[name="delivery[name2]"]').val('　');
                  jQuery('input[name="delivery[name3]"]').val('');
                  jQuery('input[name="delivery[zipcode]"]').val('');
                  jQuery('input[name="delivery[pref]"]').val('');
                  jQuery('input[name="delivery[address1]"]').val('');
                  jQuery('input[name="delivery[address2]"]').val('');
                  jQuery('input[name="delivery[address3]"]').val('');
                  jQuery('input[name="delivery[tel]"]').val('');
                  jQuery('input[name="delivery[fax]"]').val('');
                }
            });
            </script>
          </td>
        </tr>
        HTML;
    return $htmls;
  } else if ($positions ==  $position && 'shipping_address' == $key_class && $custom_field == 'delivery') {

    $htmls = <<<HTML
        <tr class="customkey_shipping_address">
          <th scope="row"><em>＊</em>お届け先名称</th>
          <td colspan="2">
            <select name="custom_delivery[shipping_address]" class="iopt_select">
            <option value="登録情報なし。">登録情報なし。</option>
            </select>
          </td>
        </tr>
HTML;
    return $htmls;
  } else {
    return $html;
  }
}

//確認画面出力調整
add_filter('usces_filter_apply_addressform_confirm',  'my_filter_apply_addressform_confirm', 10, 3);
function my_filter_apply_addressform_confirm($addressform_confirm, $type, $data)
{
  //処理
  global $usces, $usces_settings;
  $options   = get_option('usces');
  $form      = $options['system']['addressform'];
  $nameform  = $usces_settings['nameform'][$form];
  $applyform = usces_get_apply_addressform($form);
  $addressform_confirm   = '';
  switch ($type) {
    case 'confirm':
    case 'member':
      $values = $data;
      break;
    case 'customer':
    case 'delivery':
      $values = $data[$type];
      break;
  }
  $data['type']        = $type;
  $values['country']   = ! empty($values['country']) ? $values['country'] : usces_get_local_addressform();
  $values              = $usces->stripslashes_deep_post($values);
  $target_market_count = (isset($options['system']['target_market']) && is_array($options['system']['target_market'])) ? count($options['system']['target_market']) : 1;
  if ('confirm' === $type) {

    $addressform_confirm          .= usces_custom_field_info($data, 'customer', 'name_pre', 'return');
    $addressform_confirm          .= '<tr class="name-row member-name-row"><th>' . apply_filters('usces_filters_addressform_name_label', __('Full name', 'usces'), $type, $values, $applyform) . '</th><td>' . esc_html(sprintf(_x('%s', 'honorific', 'usces'), (esc_html($values['customer']['name1']) . ' ' . esc_html($values['customer']['name2'])))) . '</td></tr>';
    $furigana          = ('' === (trim($values['customer']['name3']) . trim($values['customer']['name4']))) ? '' : sprintf(_x('%s', 'honorific', 'usces'), (esc_html($values['customer']['name3']) . ' ' . esc_html($values['customer']['name4'])));
    $furigana_customer = '<tr class="furikana-row member-furikana-row"><th>' . __('furigana', 'usces') . '</th><td>' . $furigana . '</td></tr>';
    $addressform_confirm          .= apply_filters('usces_filter_furigana_confirm_customer', $furigana_customer, $type, $values);
    $addressform_confirm          .= usces_custom_field_info($data, 'customer', 'name_after', 'return');
    $addressform_confirm          .= '<tr class="zipcode-row member-zipcode-row"><th>' . __('Zip/Postal Code', 'usces') . '</th><td>' . esc_html($values['customer']['zipcode']) . '</td></tr>';
    if (1 < $target_market_count) {
      $customer_country = (! empty($usces_settings['country'][$values['customer']['country']])) ? $usces_settings['country'][$values['customer']['country']] : '';
      $addressform_confirm         .= '<tr class="country-row member-country-row"><th>' . __('Country', 'usces') . '</th><td>' . esc_html($customer_country) . '</td></tr>';
    }
    $customer_pref = (__('-- Select --', 'usces') === $values['customer']['pref'] || '-- Select --' === $values['customer']['pref']) ? '' : $values['customer']['pref'];
    $addressform_confirm      .= '
 <tr class="states-row member-states-row"><th>' . __('Province', 'usces') . '</th><td>' . esc_html($customer_pref) . '</td></tr>
 <tr class="address1-row member-address1-row"><th>' . __('city', 'usces') . '</th><td>' . esc_html($values['customer']['address1']) . '</td></tr>
 <tr class="address2-row member-address2-row"><th>' . __('numbers', 'usces') . '</th><td>' . esc_html($values['customer']['address2']) . '</td></tr>
 <tr class="address3-row member-address3-row"><th>' . __('building name', 'usces') . '</th><td>' . esc_html($values['customer']['address3']) . '</td></tr>
 <tr class="tel-row member-tel-row"><th>' . __('Phone number', 'usces') . '</th><td>' . esc_html($values['customer']['tel']) . '</td></tr>
 <tr class="fax-row member-fax-row"><th>' . __('FAX number', 'usces') . '</th><td>' . esc_html($values['customer']['fax']) . '</td></tr>';
    //global $usces, $usces_settings;$addressform_confirm      .= usces_custom_field_info( $data, 'customer', 'fax_after', 'return' );

    //$addressform_confirm = '';
    if (isset($values['delivery'])) {
      $addressform_confirm  .= '<tr class="ttl"><td colspan="2"><h3>' . __('Shipping address information', 'usces') . '</h3></td></tr>';
      $addressform_confirm .= usces_custom_field_info($data, 'delivery', 'name_pre', 'return');


      if (!empty($addressform_confirm)) {
        $addressform_confirm .= '<tr class="name-row delivery-name-row"><th>会社・部門</th><td>会社・部門1 : ' . (esc_html($values['delivery']['name1']) . '<br>会社・部門2  : ' . esc_html($values['delivery']['name2'])) . '</td></tr>';
      } else {
        $addressform_confirm .= '<tr class="name-row delivery-name-row"><th>' . apply_filters('usces_filters_addressform_name_label', __('Full name', 'usces'), $type, $values, $applyform) . '</th><td>' . sprintf(_x('%s', 'honorific', 'usces'), (esc_html($values['delivery']['name1']) . ' ' . esc_html($values['delivery']['name2']))) . '</td></tr>';
      }
      $deli_furigana          = ('' === (trim($values['delivery']['name3']) . trim($values['delivery']['name4']))) ? '' : sprintf(_x('%s', 'honorific', 'usces'), (esc_html($values['delivery']['name3']) . ' ' . esc_html($values['delivery']['name4'])));
      $furigana_delivery      = '<tr class="furikana-row delivery-furikana-row"><th>' . __('furigana', 'usces') . '</th><td>' . $deli_furigana . '</td></tr>';
      $addressform_confirm .= apply_filters('usces_filter_furigana_confirm_delivery', $furigana_delivery, $type, $values);
      $addressform_confirm .= usces_custom_field_info($values, 'delivery', 'name_after', 'return');
      $addressform_confirm .= '<tr class="zipcode-row delivery-zipcode-row"><th>' . __('Zip/Postal Code', 'usces') . '</th><td>' . esc_html($values['delivery']['zipcode']) . '</td></tr>';
      if (1 < $target_market_count) {
        $shipping_country       = (! empty($usces_settings['country'][$values['delivery']['country']])) ? $usces_settings['country'][$values['delivery']['country']] : '';
        $addressform_confirm .= '<tr class="country-row delivery-country-row"><th>' . __('Country', 'usces') . '</th><td>' . esc_html($shipping_country) . '</td></tr>';
      }
      $delivery_pref          = (__('-- Select --', 'usces') === $values['delivery']['pref'] || '-- Select --' === $values['delivery']['pref']) ? '' : $values['delivery']['pref'];
      $addressform_confirm .= '
   <tr class="states-row delivery-states-row"><th>' . __('Province', 'usces') . '</th><td>' . esc_html($delivery_pref) . '</td></tr>
   <tr class="address1-row delivery-address1-row"><th>' . __('city', 'usces') . '</th><td>' . esc_html($values['delivery']['address1']) . '</td></tr>
   <tr class="address2-row delivery-address2-row"><th>' . __('numbers', 'usces') . '</th><td>' . esc_html($values['delivery']['address2']) . '</td></tr>
   <tr class="address3-row delivery-address3-row"><th>' . __('building name', 'usces') . '</th><td>' . esc_html($values['delivery']['address3']) . '</td></tr>
   <tr class="tel-row delivery-tel-row"><th>' . __('Phone number', 'usces') . '</th><td>' . esc_html($values['delivery']['tel']) . '</td></tr>
   <tr class="fax-row delivery-fax-row"><th>' . __('FAX number', 'usces') . '</th><td>' . esc_html($values['delivery']['fax']) . '</td></tr>';
      $addressform_confirm .= usces_custom_field_info($data, 'delivery', 'fax_after', 'return');
    }
  } else {
    $addressform_confirm .= usces_custom_field_input($data, $type, 'name_pre', 'return');
    $addressform_confirm .= '<tr id="name_row" class="inp1">
 <th width="127" scope="row">' . usces_get_essential_mark('name1', $data) . apply_filters('usces_filters_addressform_name_label', __('Full name', 'usces'), $type, $values, $applyform) . '</th>';
    if ($nameform) {
      $addressform_confirm .= '<td class="name_td"><span class="member_name">' . __('Given name', 'usces') . '</span><input name="' . $type . '[name2]" id="name2" type="text" value="' . esc_attr($values['name2']) . '" onKeyDown="if (event.keyCode == 13) {return false;}" style="ime-mode: active" /></td>';
      $addressform_confirm .= '<td class="name_td"><span class="member_name">' . __('Familly name', 'usces') . '</span><input name="' . $type . '[name1]" id="name1" type="text" value="' . esc_attr($values['name1']) . '" onKeyDown="if (event.keyCode == 13) {return false;}" style="ime-mode: active" /></td>';
    } else {
      $addressform_confirm .= '<td class="name_td"><span class="member_name">' . __('Familly name', 'usces') . '</span><input name="' . $type . '[name1]" id="name1" type="text" value="' . esc_attr($values['name1']) . '" onKeyDown="if (event.keyCode == 13) {return false;}" style="ime-mode: active" /></td>';
      $addressform_confirm .= '<td class="name_td"><span class="member_name">' . __('Given name', 'usces') . '</span><input name="' . $type . '[name2]" id="name2" type="text" value="' . esc_attr($values['name2']) . '" onKeyDown="if (event.keyCode == 13) {return false;}" style="ime-mode: active" /></td>';
    }
    $addressform_confirm .= '</tr>';
    $furigana = '<tr id="furikana_row" class="inp1">
 <th scope="row">' . usces_get_essential_mark('name3', $data) . __('furigana', 'usces') . '</th>';
    if ($nameform) {
      $furigana .= '<td><span class="member_furigana">' . _x('Given name', 'furigana', 'usces') . '</span><input name="' . $type . '[name4]" id="name4" type="text" value="' . esc_attr($values['name4']) . '" onKeyDown="if (event.keyCode == 13) {return false;}" style="ime-mode: active" /></td>';
      $furigana .= '<td><span class="member_furigana">' . _x('Familly name', 'furigana', 'usces') . '</span><input name="' . $type . '[name3]" id="name3" type="text" value="' . esc_attr($values['name3']) . '" onKeyDown="if (event.keyCode == 13) {return false;}" style="ime-mode: active" /></td>';
    } else {
      $furigana .= '<td><span class="member_furigana">' . _x('Familly name', 'furigana', 'usces') . '</span><input name="' . $type . '[name3]" id="name3" type="text" value="' . esc_attr($values['name3']) . '" onKeyDown="if (event.keyCode == 13) {return false;}" style="ime-mode: active" /></td>';
      $furigana .= '<td><span class="member_furigana">' . _x('Given name', 'furigana', 'usces') . '</span><input name="' . $type . '[name4]" id="name4" type="text" value="' . esc_attr($values['name4']) . '" onKeyDown="if (event.keyCode == 13) {return false;}" style="ime-mode: active" /></td>';
    }
    $furigana .= '</tr>';
    $addressform_confirm  .= apply_filters('usces_filter_furigana_form', $furigana, $type, $values);
    $addressform_confirm .= usces_custom_field_input($data, $type, 'name_after', 'return');
    $addressform_confirm  .= '<tr id="zipcode_row">
 <th scope="row">' . usces_get_essential_mark('zipcode', $data) . __('Zip/Postal Code', 'usces') . '</th>
 <td colspan="2"><input name="' . $type . '[zipcode]" id="zipcode" type="text" value="' . esc_attr($values['zipcode']) . '" onKeyDown="if (event.keyCode == 13) {return false;}" style="ime-mode: inactive" />' . usces_postal_code_address_search($type) . apply_filters('usces_filter_addressform_zipcode', null, $type) . apply_filters('usces_filter_after_zipcode', '100-1000', $applyform) . '</td>
 </tr>';
    if (1 < $target_market_count) {
      $addressform_confirm .= '<tr id="country_row">
   <th scope="row">' . usces_get_essential_mark('country', $data) . __('Country', 'usces') . '</th>
   <td colspan="2">' . uesces_get_target_market_form($type, $values['country']) . apply_filters('usces_filter_after_country', null, $applyform) . '</td>
   </tr>';
    } else {
      $addressform_confirm .= '<input type="hidden" name="' . $type . '[country]" id="' . $type . '_country" value="' . $options['system']['target_market'][0] . '">';
    }
    $addressform_confirm .= '<tr id="states_row">
 <th scope="row">' . usces_get_essential_mark('states', $data) . __('Province', 'usces') . '</th>
 <td colspan="2">' . usces_pref_select($type, $values) . apply_filters('usces_filter_after_states', null, $applyform) . '</td>
 </tr>
 <tr id="address1_row" class="inp2">
 <th scope="row">' . usces_get_essential_mark('address1', $data) . __('city', 'usces') . '</th>
 <td colspan="2"><input name="' . $type . '[address1]" id="address1" type="text" value="' . esc_attr($values['address1']) . '" onKeyDown="if (event.keyCode == 13) {return false;}" style="ime-mode: active" />' . apply_filters('usces_filter_after_address1', __('Kitakami Yokohama', 'usces'), $applyform) . '</td>
 </tr>
 <tr id="address2_row">
 <th scope="row">' . usces_get_essential_mark('address2', $data) . __('numbers', 'usces') . '</th>
 <td colspan="2"><input name="' . $type . '[address2]" id="address2" type="text" value="' . esc_attr($values['address2']) . '" onKeyDown="if (event.keyCode == 13) {return false;}" style="ime-mode: active" />' . apply_filters('usces_filter_after_address2', '3-24-555', $applyform) . '</td>
 </tr>
 <tr id="address3_row">
 <th scope="row">' . usces_get_essential_mark('address3', $data) . __('building name', 'usces') . '</th>
 <td colspan="2"><input name="' . $type . '[address3]" id="address3" type="text" value="' . esc_attr($values['address3']) . '" onKeyDown="if (event.keyCode == 13) {return false;}" style="ime-mode: active" />' . apply_filters('usces_filter_after_address3', __('tuhanbuild 4F', 'usces'), $applyform) . '</td>
 </tr>
 <tr id="tel_row">
 <th scope="row">' . usces_get_essential_mark('tel', $data) . __('Phone number', 'usces') . '</th>
 <td colspan="2"><input name="' . $type . '[tel]" id="tel" type="text" value="' . esc_attr($values['tel']) . '" onKeyDown="if (event.keyCode == 13) {return false;}" style="ime-mode: inactive" />' . apply_filters('usces_filter_after_tel', '1000-10-1000', $applyform) . '</td>
 </tr>
 <tr id="fax_row">
 <th scope="row">' . usces_get_essential_mark('fax', $data) . __('FAX number', 'usces') . '</th>
 <td colspan="2"><input name="' . $type . '[fax]" id="fax" type="text" value="' . esc_attr($values['fax']) . '" onKeyDown="if (event.keyCode == 13) {return false;}" style="ime-mode: inactive" />' . apply_filters('usces_filter_after_fax', '1000-10-1000', $applyform) . '</td>
 </tr>';
  }


  return $addressform_confirm;
}


//会員登録通知メールカスタマイズ
add_filter('usces_filter_send_regmembermail_message',  'my_filter_send_regmembermail_message', 10, 2);
function my_filter_send_regmembermail_message($message, $user)
{
  //処理
  global $usces;
  $args = func_get_args();
  $mail_data         = usces_mail_data();
  //$user = $args[1];
  $message = $mail_data['header']['membercomp'] . "\r\n\r\n";
  //echo '<pre>';
  //print_r($user);
  //echo  '</pre>';
  $message .= '登録情報下記の内容になります。' . "\r\n";
  $message .= '---------------' . "\r\n";
  $message .= '会員番号：' . trim($user['ID'])  . "\r\n";
  $message .= '会社名：' . trim($user['csmb_company']) . "\r\n";
  $message .= 'ご担当名前：' . trim($user['name1']) . trim($user['name2']) . "\r\n";
  $message .= 'メールアドレス:' . trim($user['mailaddress1']) . "\r\n";
  $message .= '〒' . trim($user['zipcode']) . "\r\n";
  $message .= '住所：' . trim($user['pref']) . trim($user['address1']) . trim($user['address2']) . trim($user['address3']) . "\r\n";
  $message .= '電話番号:' . trim($user['tel']) . "\r\n";
  if (!empty($user['fax'])) {
    $message .= 'FAX:' . trim($user['fax']) . "\r\n";
    $message .= '---------------' . "\r\n\r\n";
  } else {
    $message .= '---------------' . "\r\n\r\n";
  }
  if (!empty($user['csmb_shipping_address_01']) || !empty($user['csmb_shipping_address_01_name']) || !empty($user['csmb_shipping_address_01_name02']) || !empty($user['csmb_shipping_address_01_zips']) || !empty($user['csmb_shipping_address_01_pref']) || !empty($user['csmb_shipping_address_01_address01']) || !empty($user['csmb_shipping_address_01__address02']) || !empty($user['csmb_shipping_address_01__address03']) || !empty($user['csmb_shipping_address_01__tel'])) {
    $message .= 'お届け先名称1' . "\r\n";
    $message .= '---------------' . "\r\n";
    $message .= 'お届け先名称1:' . trim($user['csmb_shipping_address_01']) . "\r\n";
    $message .= '〒' . trim($user['csmb_shipping_address_01_zips']) . "\r\n";
    $message .= '住所:' . trim($user['csmb_shipping_address_01_pref']) . trim($user['csmb_shipping_address_01_address01']) . trim($user['csmb_shipping_address_01_address02']) . trim($user['csmb_shipping_address_01_address03']) . "\r\n";
    $message .= '電話番号:' . trim($user['csmb_shipping_address_01_tel']) . "\r\n";
    $message .= '---------------' . "\r\n\r\n";
  }
  if (!empty($user['csmb_shipping_address_02']) || !empty($user['csmb_shipping_address_02_name']) || !empty($user['csmb_shipping_address_02_name02']) || !empty($user['csmb_shipping_address_02_zips']) || !empty($user['csmb_shipping_address_02_pref']) || !empty($user['csmb_shipping_address_02_address01']) || !empty($user['csmb_shipping_address_02__address02']) || !empty($user['csmb_shipping_address_02__address03']) || !empty($user['csmb_shipping_address_02__tel'])) {
    $message .= 'お届け先名称2' . "\r\n";
    $message .= '---------------' . "\r\n";
    $message .= 'お届け先名称2:' . trim($user['csmb_shipping_address_02']) . "\r\n";
    $message .= '〒' . trim($user['csmb_shipping_address_02_zips']) . "\r\n";
    $message .= '住所:' . trim($user['csmb_shipping_address_02_pref']) . trim($user['csmb_shipping_address_02_address01']) . trim($user['csmb_shipping_address_02_address02']) . trim($user['csmb_shipping_address_02_address03']) . "\r\n";
    $message .= '電話番号:' . trim($user['csmb_shipping_address_02_tel']) . "\r\n";
    $message .= '---------------' . "\r\n\r\n";
  }
  if (!empty($user['csmb_shipping_address_03']) || !empty($user['csmb_shipping_address_03_name']) || !empty($user['csmb_shipping_address_03_name02']) || !empty($user['csmb_shipping_address_03_zips']) || !empty($user['csmb_shipping_address_03_pref']) || !empty($user['csmb_shipping_address_03_address01']) || !empty($user['csmb_shipping_address_03__address02']) || !empty($user['csmb_shipping_address_03__address03']) || !empty($user['csmb_shipping_address_03__tel'])) {
    $message .= 'お届け先名称3' . "\r\n";
    $message .= '---------------' . "\r\n";
    $message .= 'お届け先名称3:' . trim($user['csmb_shipping_address_03']) . "\r\n";
    $message .= '〒' . trim($user['csmb_shipping_address_03_zips']) . "\r\n";
    $message .= '住所:' . trim($user['csmb_shipping_address_03_pref']) . trim($user['csmb_shipping_address_03_address01']) . trim($user['csmb_shipping_address_03_address02']) . trim($user['csmb_shipping_address_03_address03']) . "\r\n";
    $message .= '電話番号:' . trim($user['csmb_shipping_address_03_tel']) . "\r\n";
    $message .= '---------------' . "\r\n\r\n";
  }
  if (!empty($user['csmb_shipping_address_04']) || !empty($user['csmb_shipping_address_04_name']) || !empty($user['csmb_shipping_address_04_name02']) || !empty($user['csmb_shipping_address_04_zips']) || !empty($user['csmb_shipping_address_04_pref']) || !empty($user['csmb_shipping_address_04_address01']) || !empty($user['csmb_shipping_address_04__address02']) || !empty($user['csmb_shipping_address_04__address03']) || !empty($user['csmb_shipping_address_04__tel'])) {
    $message .= 'お届け先名称4' . "\r\n";
    $message .= '---------------' . "\r\n";
    $message .= 'お届け先名称4:' . trim($user['csmb_shipping_address_04']) . "\r\n";
    $message .= '〒' . trim($user['csmb_shipping_address_04_zips']) . "\r\n";
    $message .= '住所:' . trim($user['csmb_shipping_address_04_pref']) . trim($user['csmb_shipping_address_04_address01']) . trim($user['csmb_shipping_address_04_address02']) . trim($user['csmb_shipping_address_04_address03']) . "\r\n";
    $message .= '電話番号:' . trim($user['csmb_shipping_address_04_tel']) . "\r\n";
    $message .= '---------------' . "\r\n\r\n";
  }


  $message .= $mail_data['footer']['membercomp'];

  return $message;
}


//管理側会員登録通知メール

add_filter('usces_filter_send_regmembermail_notice',  'my_filter_send_regmembermail_notice', 10, 2);

function my_filter_send_regmembermail_notice($message, $user)
{
  $args = func_get_args();
  //$mail_data         = usces_mail_data();
  $user = $args[1];
  $message  = __('New sign-in processing was completed.', 'usces') . "\r\n\r\n";
  $message .= '【登録情報】' . "\r\n";
  $message .= '---------------' . "\r\n";
  $message .= '会員番号：' . trim($user['ID'])  . "\r\n";
  $message .= '会社名：' . trim($user['csmb_company']) . "\r\n";
  $message .= 'ご担当名前：' . trim($user['name1']) . trim($user['name2']) . "\r\n";
  $message .= 'メールアドレス:' . trim($user['mailaddress1']) . "\r\n";
  $message .= '〒' . trim($user['zipcode']) . "\r\n";
  $message .= '住所：' . trim($user['pref']) . trim($user['address1']) . trim($user['address2']) . trim($user['address3']) . "\r\n";
  $message .= '電話番号:' . trim($user['tel']) . "\r\n";
  if (!empty($user['fax'])) {
    $message .= 'FAX:' . trim($user['fax']) . "\r\n";
    $message .= '---------------' . "\r\n\r\n";
  } else {
    $message .= '---------------' . "\r\n\r\n";
  }
  if (!empty($user['csmb_shipping_address_01']) || !empty($user['csmb_shipping_address_01_name']) || !empty($user['csmb_shipping_address_01_name02']) || !empty($user['csmb_shipping_address_01_zips']) || !empty($user['csmb_shipping_address_01_pref']) || !empty($user['csmb_shipping_address_01_address01']) || !empty($user['csmb_shipping_address_01__address02']) || !empty($user['csmb_shipping_address_01__address03']) || !empty($user['csmb_shipping_address_01__tel'])) {
    $message .= 'お届け先名称1' . "\r\n";
    $message .= '---------------' . "\r\n";
    $message .= 'お届け先名称1:' . trim($user['csmb_shipping_address_01']) . "\r\n";
    $message .= '〒' . trim($user['csmb_shipping_address_01_zips']) . "\r\n";
    $message .= '住所:' . trim($user['csmb_shipping_address_01_pref']) . trim($user['csmb_shipping_address_01_address01']) . trim($user['csmb_shipping_address_01_address02']) . trim($user['csmb_shipping_address_01_address03']) . "\r\n";
    $message .= '電話番号:' . trim($user['csmb_shipping_address_01_tel']) . "\r\n";
    $message .= '---------------' . "\r\n\r\n";
  }
  if (!empty($user['csmb_shipping_address_02']) || !empty($user['csmb_shipping_address_02_name']) || !empty($user['csmb_shipping_address_02_name02']) || !empty($user['csmb_shipping_address_02_zips']) || !empty($user['csmb_shipping_address_02_pref']) || !empty($user['csmb_shipping_address_02_address01']) || !empty($user['csmb_shipping_address_02__address02']) || !empty($user['csmb_shipping_address_02__address03']) || !empty($user['csmb_shipping_address_02__tel'])) {
    $message .= 'お届け先名称2' . "\r\n";
    $message .= '---------------' . "\r\n";
    $message .= 'お届け先名称2:' . trim($user['csmb_shipping_address_02']) . "\r\n";
    $message .= '〒' . trim($user['csmb_shipping_address_02_zips']) . "\r\n";
    $message .= '住所:' . trim($user['csmb_shipping_address_02_pref']) . trim($user['csmb_shipping_address_02_address01']) . trim($user['csmb_shipping_address_02_address02']) . trim($user['csmb_shipping_address_02_address03']) . "\r\n";
    $message .= '電話番号:' . trim($user['csmb_shipping_address_02_tel']) . "\r\n";
    $message .= '---------------' . "\r\n\r\n";
  }
  if (!empty($user['csmb_shipping_address_03']) || !empty($user['csmb_shipping_address_03_name']) || !empty($user['csmb_shipping_address_03_name02']) || !empty($user['csmb_shipping_address_03_zips']) || !empty($user['csmb_shipping_address_03_pref']) || !empty($user['csmb_shipping_address_03_address01']) || !empty($user['csmb_shipping_address_03__address02']) || !empty($user['csmb_shipping_address_03__address03']) || !empty($user['csmb_shipping_address_03__tel'])) {
    $message .= 'お届け先名称3' . "\r\n";
    $message .= '---------------' . "\r\n";
    $message .= 'お届け先名称3:' . trim($user['csmb_shipping_address_03']) . "\r\n";
    $message .= '〒' . trim($user['csmb_shipping_address_03_zips']) . "\r\n";
    $message .= '住所:' . trim($user['csmb_shipping_address_03_pref']) . trim($user['csmb_shipping_address_03_address01']) . trim($user['csmb_shipping_address_03_address02']) . trim($user['csmb_shipping_address_03_address03']) . "\r\n";
    $message .= '電話番号:' . trim($user['csmb_shipping_address_03_tel']) . "\r\n";
    $message .= '---------------' . "\r\n\r\n";
  }
  if (!empty($user['csmb_shipping_address_04']) || !empty($user['csmb_shipping_address_04_name']) || !empty($user['csmb_shipping_address_04_name02']) || !empty($user['csmb_shipping_address_04_zips']) || !empty($user['csmb_shipping_address_04_pref']) || !empty($user['csmb_shipping_address_04_address01']) || !empty($user['csmb_shipping_address_04__address02']) || !empty($user['csmb_shipping_address_04__address03']) || !empty($user['csmb_shipping_address_04__tel'])) {
    $message .= 'お届け先名称4' . "\r\n";
    $message .= '---------------' . "\r\n";
    $message .= 'お届け先名称4:' . trim($user['csmb_shipping_address_04']) . "\r\n";
    $message .= '〒' . trim($user['csmb_shipping_address_04_zips']) . "\r\n";
    $message .= '住所:' . trim($user['csmb_shipping_address_04_pref']) . trim($user['csmb_shipping_address_04_address01']) . trim($user['csmb_shipping_address_04_address02']) . trim($user['csmb_shipping_address_04_address03']) . "\r\n";
    $message .= '電話番号:' . trim($user['csmb_shipping_address_04_tel']) . "\r\n";
    $message .= '---------------' . "\r\n\r\n";
  }
  return $message;
}


//新規登録時のメール認証の本文を変更
//add_filter('usces_filter_send_verifymembermail_message',  'my_filter_send_verifymembermail_message', 10, 2);
//function my_filter_send_verifymembermail_message($message, $user)
//{
//  global $usces;
//  $remaining_hour    = $this->get_remaining_hour();
//  //$message  .= sprintf(__('Thank you for registering to %s.', 'usces'), get_option('blogname')) . "\r\n\r\n";
//  //$message .= 'MBFF受注発注システム へのご登録手続ありがとうございます。' . "\r\n";
//  //$message .= '下記URLにアクセスしていただくことで、お客さまの会員へのご登録が完了いたします。' . "\r\n\r\n";
//  //$message .= '完了のメールを受信するまでは、手続が終わっていませんのでご注意ください。' . "\r\n\r\n";
//  //$message .= sprintf(__('The following URL is valid for %d hours. If it expires, please try again from the beginning.', 'usces'), $remaining_hour) . "\r\n\r\n";
//  //$message .= $this->get_verify_url($user) . "\r\n\r\n";
//
//  //$message .= '--------------------------------' . "\r\n";
//  //$message .= __('Please delete this email if you were not aware that you were going to receive it.', 'usces') . "\r\n";
//  //$message .= '--------------------------------' . "\r\n\r\n";
//  //$message .= get_option('blogname') . "\r\n";
//
//  return $message;
//}
//

//完了画面
//add_filter('usces_filter_membercompletion_page_footer', 'my_usces_filter_membercompletion_page_footer');
//function my_usces_filter_membercompletion_page_footer($footer)
//{
//  $member_compmode = $this->page;
//
//
//  if ('newcompletion' === $member_compmode) {
//    $footer .= '<p>新規ご登録ありがとうございます。<br>ご登録が完了しました。</p>';
//  }
//  return $footer;
//}

//配送方法削除
//フロントには見せたくないのでhiddenで処理
add_filter('usces_filter_the_delivery_method', 'my_filter_the_delivery_method', 10, 2);
function my_filter_the_delivery_method($html, $deli_id)
{
  //処理
  $html = "<input type='hidden' name='offer[delivery_method]' id='delivery_method_select' class='delivery_time' onkeydown='if (event.keyCode == 13) {return false;}' value='0' ";
  return $html;
}



//到着希望日削除
add_filter('the_delivery_date', 'my_filter_the_delivery_date');
function my_filter_the_delivery_date($html)
{
  //処理
  $html = "";
  return $html;
}
//配送タイム削除
add_filter('the_delivery_time', 'my_filter_the_delivery_time');
function my_filter_the_delivery_time($html)
{
  //処理
  $html = "";
  return $html;
}



//add_filter('usces_filter_key_unserialize_options', 'custom_key_unserialize_options', 10, 4);
//function custom_key_unserialize_options($new_opt, $post_id, $sku_code, $serial)
//{
//  global $usces;
//
//  // ログイン中の会員IDから掛け率を取得
//  $member_id = wel_logged_in_id();
//  $rates_data = get_member_discount_rates($member_id);
//
//  // 掛け率を取得
//  $liquid_supplement = $rates_data['liquid_supplement'];
//  $soy_flora_soap_professional = $rates_data['soy_flora_soap_professional'];
//  $soy_flora_soap_professional_mini = $rates_data['soy_flora_soap_professional_mini'];
//  $mom_flora_serum_professinal = $rates_data['mom_flora_serum_professinal'];
//  $tow_weeks_set = $rates_data['tow_weeks_set'];
//  $mom_flora_serum_salon_use = $rates_data['mom_flora_serum_salon_use'];
//
//  // カートデータ取得
//  $cart_item = isset($_SESSION['usces_cart'][$serial]) ? $_SESSION['usces_cart'][$serial] : null;
//
//  if (!$cart_item) return $new_opt;
//
//  // 既に original_price が存在していれば、割引済みと判断してスキップ
//  if (isset($cart_item['original_price'])) {
//    return $new_opt;
//  }
//
//  $original_price = isset($cart_item['price']) ? floatval($cart_item['price']) : 0;
//  $discounted_price = $original_price; // デフォルト
//
//  // SKUに応じて割引価格を計算
//  if ($sku_code === "liquid_supplement") {
//    $discounted_price = $original_price * (1 - $liquid_supplement);
//  } elseif ($sku_code === "mom_flora_serum_professinal") {
//    $discounted_price = $original_price * (1 - $mom_flora_serum_professinal);
//  } elseif ($sku_code === "soy_flora_soap_professional") {
//    $discounted_price = $original_price * (1 - $soy_flora_soap_professional);
//  } elseif ($sku_code === "soy_flora_soap_professional_mini") {
//    $discounted_price = $original_price * (1 - $soy_flora_soap_professional_mini);
//  } elseif ($sku_code === "tow_weeks_set") {
//    $discounted_price = $original_price * (1 - $tow_weeks_set);
//  } elseif ($sku_code === "mom_flora_serum_salon_use") {
//    $discounted_price = $original_price * (1 - $mom_flora_serum_salon_use);
//  }
//
//  // セッションに割引価格と元価格を保存（元価格を記録して二重割引防止）
//  $_SESSION['usces_cart'][$serial]['original_price'] = $original_price;
//  $_SESSION['usces_cart'][$serial]['price'] = $discounted_price;
//
//  return $new_opt;
//}

// 月次請求書の一覧・PDF・CSV・メール送信機能を削除（企業別運用へ移行）

//受注リスト管理画面名所変更

add_action('admin_footer', function () {
?>
  <script>
    jQuery(function($) {

      function mbffReplaceOrderLabels() {
        $('td.label').each(function() {
          var text = $.trim($(this).text());

          if (text === '到着希望日') {
            $(this).text('到着予定日');
          }

          if (text === '発送予定日') {
            $(this).text('発送日');
          }
        });
      }

      mbffReplaceOrderLabels();

      setTimeout(mbffReplaceOrderLabels, 500);
      setTimeout(mbffReplaceOrderLabels, 1000);

    });
  </script>
  <?php
});


//個別納品書にPDFに発送先名追加
add_filter('usces_filter_pdf_order_data', 'mbff_add_shipping_address_to_nohin_pdf', 20);

function mbff_add_shipping_address_to_nohin_pdf($data)
{
  if (empty($_REQUEST['type']) || $_REQUEST['type'] !== 'nohin') {
    return $data;
  }

  if (empty($data->order['ID']) || !function_exists('wel_get_order')) {
    return $data;
  }

  $order_data = wel_get_order($data->order['ID']);

  $shipping_address = $order_data['delivery']['shipping_address'] ?? '';

  if (!empty($shipping_address)) {
    $data->deliveri['name1'] = $shipping_address;
    $data->deliveri['name2'] = '';
  }

  return $data;
}

//印鑑印字PDF
add_filter('usces_filter_pdf_invoice_sign', 'mbff_add_invoice_pdf_sign');

function mbff_add_invoice_pdf_sign($sign_image)
{
  $seal_path = get_stylesheet_directory() . '/assets/img/common/kakuin.png';

  if (file_exists($seal_path)) {
    return $seal_path;
  }

  return $sign_image;
}
add_filter('usces_filter_all_option_pdf', 'mbff_nohin_pdf_unit_to_item_name', 10, 6);

function mbff_nohin_pdf_unit_to_item_name($optstr, $options, $post_id, $sku, $advance, $cart_row)
{
  if (empty($_REQUEST['type']) || $_REQUEST['type'] !== 'nohin') {
    return $optstr;
  }

  global $usces;

  $unit = trim($usces->getItemSkuUnit($post_id, $sku));

  if (
    $unit === '' ||
    $unit === 'ロット数を入力してください。' ||
    $unit === '数量を入力してください。'
  ) {
    return $optstr;
  }

  return $optstr . '単位：' . $unit . "\n";
}

add_filter('usces_filter_pdf_payment_method', 'mbff_nohin_add_shipping_date', 999, 3);

function mbff_nohin_add_shipping_date($siharai, $type, $data)
{
  if ($type !== 'nohin') {
    return $siharai;
  }

  global $wpdb;

  $shipping_date = $wpdb->get_var(
    $wpdb->prepare(
      "SELECT order_delidue_date
       FROM {$wpdb->prefix}usces_order
       WHERE ID = %d",
      $data->order['ID']
    )
  );

  if (empty($shipping_date)) {
    return $siharai;
  }

  return '発送日：' .
    date_i18n('Y年n月j日', strtotime($shipping_date))
    . '　' . $siharai;
}



/**
 * SVGアップロードを許可
 */
function my_allow_svg_upload($mimes)
{
  // 管理者のみSVGを許可
  if (current_user_can('manage_options')) {
    $mimes['svg'] = 'image/svg+xml';
    $mimes['svgz'] = 'image/svg+xml';
  }

  return $mimes;
}
add_filter('upload_mimes', 'my_allow_svg_upload');


/**
 * SVGのファイルタイプ判定を補正
 */
function my_fix_svg_filetype($data, $file, $filename, $mimes)
{
  $filetype = wp_check_filetype($filename, $mimes);

  if ($filetype['ext'] === 'svg') {
    $data['ext']  = 'svg';
    $data['type'] = 'image/svg+xml';
  }

  if ($filetype['ext'] === 'svgz') {
    $data['ext']  = 'svgz';
    $data['type'] = 'image/svg+xml';
  }

  return $data;
}
add_filter('wp_check_filetype_and_ext', 'my_fix_svg_filetype', 10, 4);


/**
 * メディアライブラリでSVGをプレビュー表示
 */
function my_svg_admin_preview_css()
{
  echo '<style>
        .attachment-266x266,
        .thumbnail img[src$=".svg"] {
            width: 100% !important;
            height: auto !important;
        }

        .media-icon img[src$=".svg"] {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
    </style>';
}
add_action('admin_head', 'my_svg_admin_preview_css');


/* ==========================================================
 * MBFF 商品ラインナップ
 *
 * CPT UI
 * 投稿タイプ：lineup
 *
 * Smart Custom Fields
 * ========================================================== */


/**
 * SCFフィールドを取得
 *
 * SCFプラグインが読み込まれていない場合は
 * get_post_meta()へ切り替えます。
 */
if (!function_exists('mbff_lineup_get_field')) {
  function mbff_lineup_get_field($field_name, $post_id)
  {
    if (class_exists('SCF')) {
      return SCF::get($field_name, $post_id);
    }

    return get_post_meta($post_id, $field_name, true);
  }
}


/**
 * SCF画像フィールドから添付ファイルIDを取得
 */
if (!function_exists('mbff_lineup_get_image_id')) {
  function mbff_lineup_get_image_id($image)
  {
    if (empty($image)) {
      return 0;
    }

    /*
         * 添付ファイルID
         */
    if (is_numeric($image)) {
      return absint($image);
    }

    /*
         * WP_Postオブジェクト
         */
    if ($image instanceof WP_Post) {
      return absint($image->ID);
    }

    /*
         * 配列
         */
    if (is_array($image)) {
      /*
             * 複数値または入れ子になっている場合
             */
      if (isset($image[0])) {
        return mbff_lineup_get_image_id($image[0]);
      }

      foreach (
        array(
          'ID',
          'id',
          'attachment_id',
          'post_id',
        ) as $key
      ) {
        if (!empty($image[$key])) {
          return absint($image[$key]);
        }
      }

      if (!empty($image['url'])) {
        return absint(
          attachment_url_to_postid($image['url'])
        );
      }
    }

    /*
         * 画像URL
         */
    if (
      is_string($image) &&
      filter_var($image, FILTER_VALIDATE_URL)
    ) {
      return absint(
        attachment_url_to_postid($image)
      );
    }

    return 0;
  }
}


/**
 * SCF画像フィールドから画像URLを取得
 */
if (!function_exists('mbff_lineup_get_image_url')) {
  function mbff_lineup_get_image_url($image, $size = 'full')
  {
    $image_id = mbff_lineup_get_image_id($image);

    if ($image_id) {
      $image_url = wp_get_attachment_image_url(
        $image_id,
        $size
      );

      if ($image_url) {
        return $image_url;
      }
    }

    if (is_array($image)) {
      if (isset($image[0])) {
        return mbff_lineup_get_image_url(
          $image[0],
          $size
        );
      }

      if (!empty($image['url'])) {
        return $image['url'];
      }
    }

    if (
      is_string($image) &&
      filter_var($image, FILTER_VALIDATE_URL)
    ) {
      return $image;
    }

    return '';
  }
}


/**
 * 画像を出力
 */
if (!function_exists('mbff_lineup_render_image')) {
  function mbff_lineup_render_image(
    $image,
    $alt = '',
    $class_name = ''
  ) {
    $image_id  = mbff_lineup_get_image_id($image);
    $image_url = mbff_lineup_get_image_url($image);

    $attributes = array(
      'alt'     => $alt,
      'loading' => 'lazy',
    );

    if ($class_name) {
      $attributes['class'] = $class_name;
    }

    if ($image_id) {
      return wp_get_attachment_image(
        $image_id,
        'full',
        false,
        $attributes
      );
    }

    if ($image_url) {
      return sprintf(
        '<img src="%s" alt="%s" class="%s" loading="lazy">',
        esc_url($image_url),
        esc_attr($alt),
        esc_attr($class_name)
      );
    }

    return '';
  }
}


/**
 * 関連投稿フィールドから商品投稿IDを取得
 */
if (!function_exists('mbff_lineup_get_product_id')) {
  function mbff_lineup_get_product_id($product)
  {
    if (empty($product)) {
      return 0;
    }

    if ($product instanceof WP_Post) {
      return absint($product->ID);
    }

    if (is_numeric($product)) {
      return absint($product);
    }

    if (is_array($product)) {
      /*
             * SCFの関連投稿が配列で返る場合
             */
      if (isset($product[0])) {
        return mbff_lineup_get_product_id(
          $product[0]
        );
      }

      foreach (
        array(
          'ID',
          'id',
          'post_id',
        ) as $key
      ) {
        if (!empty($product[$key])) {
          return absint($product[$key]);
        }
      }
    }

    return 0;
  }
}


/**
 * チェックボックスの判定
 */
if (!function_exists('mbff_lineup_is_checked')) {
  function mbff_lineup_is_checked($value)
  {
    if (is_array($value)) {
      return !empty($value);
    }

    return in_array(
      $value,
      array(
        true,
        1,
        '1',
        'true',
        'yes',
        'on',
      ),
      true
    );
  }
}


/**
 * 商品ラインナップを表示
 */
if (!function_exists('mbff_display_lineup')) {
  function mbff_display_lineup($group, $title)
  {
    $lineup_query = new WP_Query(array(
      'post_type'      => 'lineup',
      'post_status'    => 'publish',
      'posts_per_page' => -1,

      /*
             * CPT UIで「ページ属性」を有効にすると
             * 管理画面の「順序」で並べ替え可能
             */
      'orderby' => array(
        'menu_order' => 'ASC',
        'date'       => 'ASC',
      ),

      'meta_query' => array(
        array(
          'key'     => 'lineup_group',
          'value'   => $group,
          'compare' => '=',
        ),
      ),
    ));

    if (!$lineup_query->have_posts()) {
      return;
    }
  ?>

    <section
      class="lineup__section"
      data-lineup-group="<?php echo esc_attr($group); ?>">
      <h2 class="lineup__heading">
        <?php echo esc_html($title); ?>
      </h2>

      <div class="lineup__list">

        <?php
        while ($lineup_query->have_posts()) :
          $lineup_query->the_post();

          $lineup_id = get_the_ID();

          $lead = mbff_lineup_get_field(
            'lineup_lead',
            $lineup_id
          );

          $image = mbff_lineup_get_field(
            'lineup_image',
            $lineup_id
          );

          $logo = mbff_lineup_get_field(
            'lineup_logo',
            $lineup_id
          );

          $logo_width = absint(
            mbff_lineup_get_field(
              'lineup_logo_width',
              $lineup_id
            )
          );

          $product_name = mbff_lineup_get_field(
            'lineup_product_name',
            $lineup_id
          );

          $copy = mbff_lineup_get_field(
            'lineup_copy',
            $lineup_id
          );

          $label = mbff_lineup_get_field(
            'lineup_label',
            $lineup_id
          );

          $product = mbff_lineup_get_field(
            'lineup_product',
            $lineup_id
          );

          $no_link = mbff_lineup_get_field(
            'lineup_no_link',
            $lineup_id
          );

          /*
                     * 商品名
                     */
          if (!$product_name) {
            $product_name = get_the_title(
              $lineup_id
            );
          }

          /*
                     * ラベル
                     */
          if ($label === '') {
            $label = 'サロン専売品';
          }

          /*
                     * Welcart商品リンク
                     */
          $product_id  = mbff_lineup_get_product_id(
            $product
          );

          $product_url = '';

          if (
            $product_id &&
            get_post_status($product_id) === 'publish' &&
            !mbff_lineup_is_checked($no_link)
          ) {
            $product_url = get_permalink(
              $product_id
            );
          }

          /*
                     * 商品画像とロゴ画像
                     */
          $product_image_html =
            mbff_lineup_render_image(
              $image,
              $product_name,
              'lineup__product-image'
            );

          $logo_image_html =
            mbff_lineup_render_image(
              $logo,
              '',
              'lineup__logo-image'
            );

          $logo_style = '';

          if ($logo_width) {
            $logo_style = sprintf(
              'max-width:%dpx;',
              $logo_width
            );
          }
        ?>

          <article class="lineup__list__item">

            <?php if ($product_url) : ?>

              <a
                class="lineup__item-link"
                href="<?php echo esc_url($product_url); ?>">

              <?php else : ?>

                <div class="lineup__item-link">

                <?php endif; ?>

                <p class="lineup__lead">
                  <?php if ($lead) : ?>
                    <?php echo esc_html($lead); ?>
                  <?php else : ?>
                    <span aria-hidden="true">&nbsp;</span>
                  <?php endif; ?>
                </p>

                <figure class="lineup__figure">

                  <?php if ($product_image_html) : ?>

                    <?php
                    echo wp_kses_post(
                      $product_image_html
                    );
                    ?>

                  <?php else : ?>

                    <div class="lineup__no-image">
                      NO IMAGE
                    </div>

                  <?php endif; ?>

                  <?php if ($logo_image_html) : ?>

                    <div
                      class="lineup__logo"
                      <?php if ($logo_style) : ?>
                      style="<?php echo esc_attr($logo_style); ?>"
                      <?php endif; ?>>
                      <?php
                      echo wp_kses_post(
                        $logo_image_html
                      );
                      ?>
                    </div>

                  <?php endif; ?>

                </figure>

                <div class="lineup__caption">

                  <h3 class="lineup__product-name">
                    <?php
                    echo esc_html(
                      $product_name
                    );
                    ?>
                  </h3>

                  <?php if ($copy) : ?>

                    <p class="lineup__copy">
                      <?php
                      echo nl2br(
                        esc_html($copy)
                      );
                      ?>
                    </p>

                  <?php endif; ?>

                </div>

                <?php if ($product_url) : ?>

              </a>

            <?php else : ?>

      </div>

    <?php endif; ?>

    <?php if ($label) : ?>

      <div class="text__caption">
        <div class="text__caption__ja">
          <?php echo esc_html($label); ?>
        </div>
      </div>

    <?php endif; ?>

    </article>

  <?php endwhile; ?>

  </div>
    </section>

  <?php
    wp_reset_postdata();
  }
}


/* =========================================================
   AG FLOLIST 下層ページ CSS
   CSS設置場所：/css/
========================================================= */
function agFLOLIST_lower_page_styles()
{

  $css_url = home_url('/css/');

  /* 下層共通CSS */
  if (
    is_page('service') ||
    is_page('salon') ||
    is_page('company') ||
    is_page('contact')
  ) {
    wp_enqueue_style(
      'ag-lower-common',
      $css_url . 'lower-common.css',
      array(),
      null
    );
  }

  /* サービス */
  if (is_page('service')) {
    wp_enqueue_style(
      'ag-service',
      $css_url . 'service.css',
      array('ag-lower-common'),
      null
    );
  }

  /* サロン */
  if (is_page('salon')) {
    wp_enqueue_style(
      'ag-salon-page',
      $css_url . 'salon.css',
      array('ag-lower-common'),
      null
    );
  }

  /* 提携・提供会社 */
  if (is_page('company')) {
    wp_enqueue_style(
      'ag-company-page',
      $css_url . 'company.css',
      array('ag-lower-common'),
      null
    );
  }

  /* お問い合わせ */
  if (is_page('contact')) {
    wp_enqueue_style(
      'ag-contact-page',
      $css_url . 'contact.css',
      array('ag-lower-common'),
      null
    );
  }
}
add_action('wp_enqueue_scripts', 'agFLOLIST_lower_page_styles', 30);

/* =========================================================
 * AG FLOLIST | 会員登録の一般公開停止（第1段階）
 *
 * 対象：/usces-member/?usces_page=newmember
 * GET/HEAD → 通常の会員ログインへ302リダイレクト
 * 直接POST → 登録処理に入る前に403で停止
 * ※企業ごとの招待URLは第2段階で認証処理を実装する。
 * ========================================================= */
function agFLOLIST_block_public_member_signup()
{
  if (is_admin()) {
    return;
  }

  $request_uri = isset($_SERVER['REQUEST_URI']) && is_string($_SERVER['REQUEST_URI'])
    ? wp_unslash($_SERVER['REQUEST_URI'])
    : '';
  $request_path = wp_parse_url($request_uri, PHP_URL_PATH);
  $member_path  = wp_parse_url(home_url('/usces-member/'), PHP_URL_PATH);

  if (!is_string($request_path) || !is_string($member_path)) {
    return;
  }

  // サイトがサブディレクトリにある場合にも対応する。
  if (untrailingslashit($request_path) !== untrailingslashit($member_path)) {
    return;
  }

  $usces_page = isset($_GET['usces_page']) && is_string($_GET['usces_page'])
    ? sanitize_key(wp_unslash($_GET['usces_page']))
    : '';
  $regmode = isset($_POST['member_regmode']) && is_string($_POST['member_regmode'])
    ? sanitize_key(wp_unslash($_POST['member_regmode']))
    : '';

  $method = isset($_SERVER['REQUEST_METHOD']) && is_string($_SERVER['REQUEST_METHOD'])
    ? strtoupper($_SERVER['REQUEST_METHOD'])
    : 'GET';

  $is_signup_page = ($usces_page === 'newmember');
  $is_signup_post = ($method === 'POST') && (
    isset($_POST['regmember']) ||
    strpos($regmode, 'newmember') === 0
  );

  if (!$is_signup_page && !$is_signup_post) {
    return;
  }

  nocache_headers();

  if ($method === 'GET' || $method === 'HEAD') {
    wp_safe_redirect(home_url('/usces-member/'), 302, 'AG FLOLIST');
    exit;
  }

  // POSTをログイン画面へリダイレクトすると保存可否が曖昧になるため明示的に拒否する。
  wp_die(
    '一般向けの会員登録は受け付けておりません。企業からご案内する専用URLをご利用ください。',
    '会員登録を停止しています',
    array('response' => 403)
  );
}
add_action('init', 'agFLOLIST_block_public_member_signup', 0);

/* 会員ログインページ専用CSS（/css/ 配下に設置、モバイルファースト） */
function agFLOLIST_enqueue_member_login_styles()
{
  if (!is_page('usces-member')) {
    return;
  }
  wp_enqueue_style(
    'agFLOLIST-member-login',
    home_url('/css/ag-member-login.css'),
    array(),
    '1.0.0'
  );
}
add_action('wp_enqueue_scripts', 'agFLOLIST_enqueue_member_login_styles', 40);


/**
 * AG FLOLIST: display the registered company for signed-in QR members.
 * Welcart member login is distinct from WordPress administrator login.
 */
function agFLOLIST_show_qr_company_badge()
{
  if (
    is_admin() || !function_exists('agcm_current_member_type')
    || !function_exists('agcm_current_company_id')
    || !function_exists('agcm_company')
  ) {
    return;
  }

  // Company (QR invitation) accounts only; fixed URL accounts are excluded.
  if (agcm_current_member_type() !== 'company') {
    return;
  }

  $company_id = (int) agcm_current_company_id();
  if ($company_id <= 0) {
    return;
  }
  $company = agcm_company($company_id);
  if (!$company || empty($company->post_title)) {
    return;
  }
  ?>
  <style id="agFLOLIST-qr-company-badge-css">
    .agFLOLIST-qr-company-badge {
      position: fixed;
      right: 18px;
      bottom: 80px;
      z-index: 9900;
      box-sizing: border-box;
      max-width: min(360px, calc(100vw - 32px));
      padding: 11px 17px;
      border: 1px solid rgba(27, 54, 53, .12);
      border-radius: 12px;
      background: rgba(255, 255, 255, .96);
      box-shadow: 0 6px 22px rgba(20, 40, 38, .13);
      color: #263b36;
      font-size: 13px;
      line-height: 1.55;
      overflow-wrap: anywhere;
      pointer-events: none;
    }

    .agFLOLIST-qr-company-badge__label {
      display: block;
      margin-bottom: 2px;
      color: #64736d;
      font-size: 10px;
      letter-spacing: .06em;
    }

    .agFLOLIST-qr-company-badge__name {
      font-weight: 700;
    }

    @media (max-width: 767px) {
      .agFLOLIST-qr-company-badge {
        right: 12px;
        bottom: calc(80px + env(safe-area-inset-bottom, 0px));
        max-width: min(260px, calc(100vw - 24px));
        padding: 9px 12px;
        font-size: 12px;
        border-radius: 10px;
      }
    }

    @media print {
      .agFLOLIST-qr-company-badge {
        display: none !important;
      }
    }
  </style>
  <div class="agFLOLIST-qr-company-badge" aria-label="登録企業">
    <span class="agFLOLIST-qr-company-badge__label">ログイン中の登録企業</span>
    <span class="agFLOLIST-qr-company-badge__name"><?php echo esc_html($company->post_title); ?></span>
  </div>
<?php
}
add_action('wp_footer', 'agFLOLIST_show_qr_company_badge', 30);


/* ==========================================================
 * AG FLOLIST | 旧 /company/ -> /partner-salon/
 * 公開済みの移転先がある場合のみ 301。既存の会員・注文処理は変更しない。
 * ========================================================== */
if (!function_exists('agFLOLIST_redirect_old_company_url')) {
  function agFLOLIST_redirect_old_company_url()
  {
    if (is_admin() || wp_doing_ajax() || is_preview()) return;
    if (!isset($_SERVER['REQUEST_URI']) || !is_string($_SERVER['REQUEST_URI'])) return;
    $requested = wp_parse_url(wp_unslash($_SERVER['REQUEST_URI']), PHP_URL_PATH);
    $legacy = wp_parse_url(home_url('/company/'), PHP_URL_PATH);
    if (!is_string($requested) || !is_string($legacy)) return;
    if (untrailingslashit($requested) !== untrailingslashit($legacy)) return;
    $new_page = get_page_by_path('partner-salon');
    if (!$new_page instanceof WP_Post || $new_page->post_status !== 'publish') return;
    $new_url = get_permalink($new_page);
    if (!$new_url) return;
    wp_safe_redirect($new_url, 301, 'AG FLOLIST');
    exit;
  }
  add_action('template_redirect', 'agFLOLIST_redirect_old_company_url', 1);
}


/* AG FLOLIST V17 MESSAGE + JOURNAL */
require_once get_template_directory() . '/inc/ag-editorial-v17.php';
