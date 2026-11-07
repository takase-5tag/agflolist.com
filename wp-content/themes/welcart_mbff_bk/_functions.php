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
global $custom_price_global;
$custom_price_global = [];
add_filter('usces_filter_the_item_price_cr', 'custom_item_price', 10, 2);
function custom_item_price($price, $post_id)
{
  global $custom_price_global;

  if (function_exists('wel_is_logged_in') && wel_is_logged_in()) {
    $mem_id = wel_logged_in_id(); // ログインユーザーのIDを取得
    $member = wel_get_member($mem_id); // 会員情報を取得

    //管理画面で登録した各商品の課税率
    $liquid_supplement = isset($member['admb_liquid_supplement']) ? $member['admb_liquid_supplement'] : '0';
    $soy_flora_soap_professional = isset($member['admb_soy_flora_soap_professional']) ? $member['admb_soy_flora_soap_professional'] : '0';
    $soy_flora_soap_professional_mini = isset($member['admb_soy_flora_soap_professional_mini']) ? $member['admb_soy_flora_soap_professional_mini'] : '0';
    $mom_flora_serum_professinal = isset($member['admb_mom_flora_serum_professinal']) ? $member['admb_mom_flora_serum_professinal'] : '0';
    $tow_weeks_set = isset($member['admb_tow_weeks_set']) ? $member['admb_tow_weeks_set'] : '0';
  } else {
    $liquid_supplement = 0;
    $soy_flora_soap_professional = 0;
    $soy_flora_soap_professional_mini = 0;
    $mom_flora_serum_professinal = 0;
    $tow_weeks_set = 0;
  }

  // ¥マークなどを排除して数値化
  $numeric_price = floatval(preg_replace('/[^0-9.]/', '', $price));

  $product_sku = usces_the_itemSku('return'); // SKUを取得

  // SKUに応じた掛け率を適用
  if ($product_sku === "liquid_supplement") {
    $discounted_price = $numeric_price * (1 - $liquid_supplement);
  } elseif ($product_sku === "mom_flora_serum_professinal") {
    $discounted_price = $numeric_price * (1 - $mom_flora_serum_professinal);
  } elseif ($product_sku === "soy_flora_soap_professional") {
    $discounted_price = $numeric_price * (1 - $soy_flora_soap_professional);
  } elseif ($product_sku === "soy_flora_soap_professional_mini") {
    $discounted_price = $numeric_price * (1 - $soy_flora_soap_professional_mini);
  } elseif ($product_sku === "tow_weeks_set") {
    $discounted_price = $numeric_price * (1 - $tow_weeks_set);
  } else {
    $discounted_price = $numeric_price;
  }

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
add_filter('usces_filter_crform',  'my_filter_crform', 10, 2);
function my_filter_crform($price, $float)
{
  if (is_page('usces-cart')) {

    if (function_exists('wel_is_logged_in') && wel_is_logged_in()) {
      $mem_id = wel_logged_in_id(); // ログインユーザーのIDを取得
      $member = wel_get_member($mem_id); // 会員情報を取得

      $liquid_supplement = isset($member['admb_liquid_supplement']) ? $member['admb_liquid_supplement'] : '0';
      $soy_flora_soap_professional = isset($member['admb_soy_flora_soap_professional']) ? $member['admb_soy_flora_soap_professional'] : '0';
      $soy_flora_soap_professional_mini = isset($member['admb_soy_flora_soap_professional_mini']) ? $member['admb_soy_flora_soap_professional_mini'] : '0';
      $mom_flora_serum_professinal = isset($member['admb_mom_flora_serum_professinal']) ? $member['admb_mom_flora_serum_professinal'] : '0';
      $tow_weeks_set = isset($member['admb_tow_weeks_set']) ? $member['admb_tow_weeks_set'] : '0';
    } else {
      $liquid_supplement = 0;
      $soy_flora_soap_professional = 0;
      $soy_flora_soap_professional_mini = 0;
      $mom_flora_serum_professinal = 0;
      $tow_weeks_set = 0;
    }

    // 数値化
    $numeric_price = floatval(preg_replace('/[^0-9.]/', '', $price));
    $cart_items = wel_get_cart();
    // カート内の商品の SKU 情報を取得
    $skus = array_column($cart_items, 'sku');

    // SKUに応じた掛け率を適用
    if ($skus[0] === "liquid_supplement") {
      $discounted_price = $numeric_price * (1 - $liquid_supplement);
    } elseif ($skus[0] === "mom_flora_serum_professinal") {
      $discounted_price = $numeric_price * (1 - $mom_flora_serum_professinal);
    } elseif ($skus[0] === "soy_flora_soap_professional") {
      $discounted_price = $numeric_price * (1 - $soy_flora_soap_professional);
    } elseif ($skus[0] === "soy_flora_soap_professional_mini") {
      $discounted_price = $numeric_price * (1 - $soy_flora_soap_professional_mini);
    } elseif ($skus[0] === "tow_weeks_set") {
      $discounted_price = $numeric_price * (1 - $tow_weeks_set);
    } else {
      $discounted_price = $numeric_price;
    }

    // フィルタを一時的に無効化してから usces_crform() を呼び出す
    remove_filter('usces_filter_crform', 'my_filter_crform', 10);
    $formatted_price = usces_crform($discounted_price, true, false, 'return');
    add_filter('usces_filter_crform', 'my_filter_crform', 10, 2);

    return $formatted_price;
  }
  return $price;
}

//購入履歴の単価
add_filter('usces_filter_history_cart_row', 'custom_cart_item_price', 10, 5);
function custom_cart_item_price($history_cart_row, $umhs, $cart_row, $i, $materials)
{

  // ログインしているユーザーの情報を取得
  if (function_exists('wel_is_logged_in') && wel_is_logged_in()) {
    $mem_id = wel_logged_in_id(); // ログインユーザーのIDを取得
    $member = wel_get_member($mem_id); // 会員情報を取得

    // 会員情報から掛け率を取得
    $liquid_supplement = isset($member['admb_liquid_supplement']) ? $member['admb_liquid_supplement'] : '0';
    $soy_flora_soap_professional = isset($member['admb_soy_flora_soap_professional']) ? $member['admb_soy_flora_soap_professional'] : '0';
    $soy_flora_soap_professional_mini = isset($member['admb_soy_flora_soap_professional_mini']) ? $member['admb_soy_flora_soap_professional_mini'] : '0';
    $mom_flora_serum_professinal = isset($member['admb_mom_flora_serum_professinal']) ? $member['admb_mom_flora_serum_professinal'] : '0';
    $tow_weeks_set = isset($member['admb_tow_weeks_set']) ? $member['admb_tow_weeks_set'] : '0';
  } else {
    $liquid_supplement = 0;
    $soy_flora_soap_professional = 0;
    $soy_flora_soap_professional_mini = 0;
    $mom_flora_serum_professinal = 0;
    $tow_weeks_set = 0;
  }

  // カート内の商品のSKUを取得
  $sku = $cart_row['sku'];
  $price = $cart_row['price'];  // 単価を取得
  $quantity = $cart_row['quantity'];  // 数量を取得

  // 価格を数値化
  $numeric_price = floatval(preg_replace('/[^0-9.]/', '', $price));

  // SKUに応じた掛け率を適用して割引価格を計算
  if ($sku === "liquid_supplement") {
    $discounted_price = $numeric_price * (1 - $liquid_supplement);
  } elseif ($sku === "mom_flora_serum_professinal") {
    $discounted_price = $numeric_price * (1 - $mom_flora_serum_professinal);
  } elseif ($sku === "soy_flora_soap_professional") {
    $discounted_price = $numeric_price * (1 - $soy_flora_soap_professional);
  } elseif ($sku === "soy_flora_soap_professional_mini") {
    $discounted_price = $numeric_price * (1 - $soy_flora_soap_professional_mini);
  } elseif ($sku === "tow_weeks_set") {
    $discounted_price = $numeric_price * (1 - $tow_weeks_set);
  } else {
    $discounted_price = $numeric_price;
  }

  // 割引後の単価をフォーマット
  $formatted_price = usces_crform($discounted_price, true, false, 'return');
  // 割引後の合計金額をフォーマット
  $formatted_subtotal = usces_crform($discounted_price * $quantity, true, false, 'return');

  // カートの内容を再構築
  $history_cart_row = '
        <tr>
            <td class="cartrownum">' . ($i + 1) . '</td>
            <td class="thumbnail">' . $materials['cart_thumbnail'] . '</td>
            <td class="aleft productname" data-label="' . __('Items', 'usces') . '">' . $materials['cartItemName'] . '</td>
            <td class="rightnum price" data-label="' . __('Unit price', 'usces') . '">' . $formatted_price . '</td>
            <td class="rightnum quantity" data-label="' . __('Quantity', 'usces') . '">' . number_format($quantity) . '</td>
            <td class="rightnum subtotal" data-label="' . __('Amount', 'usces') . '">' . $formatted_subtotal . '</td>
        </tr>';

  return $history_cart_row;
}

//購入履歴の購入金額と税
add_filter('usces_filter_history_member_head', 'custom_total_price', 10, 2);
function custom_total_price($history_member_head, $umhs)
{

  // ログインしているユーザーの情報を取得
  if (function_exists('wel_is_logged_in') && wel_is_logged_in()) {
    $mem_id = wel_logged_in_id(); // ログインユーザーのIDを取得
    $member = wel_get_member($mem_id); // 会員情報を取得

    // 会員情報から掛け率を取得
    $liquid_supplement = isset($member['admb_liquid_supplement']) ? $member['admb_liquid_supplement'] : '0';
    $soy_flora_soap_professional = isset($member['admb_soy_flora_soap_professional']) ? $member['admb_soy_flora_soap_professional'] : '0';
    $soy_flora_soap_professional_mini = isset($member['admb_soy_flora_soap_professional_mini']) ? $member['admb_soy_flora_soap_professional_mini'] : '0';
    $mom_flora_serum_professinal = isset($member['admb_mom_flora_serum_professinal']) ? $member['admb_mom_flora_serum_professinal'] : '0';
    $tow_weeks_set = isset($member['admb_tow_weeks_set']) ? $member['admb_tow_weeks_set'] : '0';
  } else {
    $liquid_supplement = 0;
    $soy_flora_soap_professional = 0;
    $soy_flora_soap_professional_mini = 0;
    $mom_flora_serum_professinal = 0;
    $tow_weeks_set = 0;
  }


  // $order_data = wel_get_order($order_id);

  $total_price = 0;

  //標準の消費税
  $usces_tax = Welcart_Tax::get_instance();
  $tax_rate_standard = $usces_tax->tax_rate_standard;

  if (isset($umhs['cart']) && is_array($umhs['cart'])) {
    //商品がカートにあるだけ繰り返し
    foreach ($umhs['cart'] as $cart_item) {
      // 商品情報の取得
      $price = isset($cart_item['price']) ? floatval(preg_replace('/[^0-9.]/', '', $cart_item['price'])) : 0;
      $sku = isset($cart_item['sku_code']) ? $cart_item['sku_code'] : '';
      $quantity = isset($cart_item['quantity']) ? intval($cart_item['quantity']) : 1;
      $cprice =  $price * $quantity;
      //送付先
      $order_id = $cart_item['order_id'];
      $order_data = wel_get_order($order_id);
      $order_data_delivery_shipping_address = $order_data['delivery']['shipping_address'];

      // SKUに応じた掛け率を適用して割引価格を計算
      if ($sku === "liquid_supplement") {
        $discounted_price = $price * (1 - $liquid_supplement);
      } elseif ($sku === "mom_flora_serum_professinal") {
        $discounted_price = $price * (1 - $mom_flora_serum_professinal);
      } elseif ($sku === "soy_flora_soap_professional") {
        $discounted_price = $price * (1 - $soy_flora_soap_professional);
      } elseif ($sku === "soy_flora_soap_professional_mini") {
        $discounted_price = $price * (1 - $soy_flora_soap_professional_mini);
      } elseif ($sku === "tow_weeks_set") {
        $discounted_price = $price * (1 - $tow_weeks_set);
      } else {
        $discounted_price = $price;
      }

      // 商品の単価×数量で合計金額を計算
      $total_price += $discounted_price * $quantity;
    }
  }

  //消費税込みにする
  $total_price_tax = $total_price * ($tax_rate_standard / 100);
  $formatted_total_tax = usces_crform(floor($total_price_tax), true, false, 'return');
  $total_price = $total_price + $total_price_tax;

  // 合計金額をフォーマット
  $formatted_total_price = usces_crform($total_price, true, false, 'return');


  // `purchase_price` の列を置き換え
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
    '<td class="rightnum destination" data-label="お届け先">' . $order_data_delivery_shipping_address . '</td>',
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
add_action('usces_action_reg_orderdata', 'save_order_total_price_data', 10, 1);
function save_order_total_price_data($order_data)
{
  // 注文IDが配列に存在するか確認
  if (is_array($order_data) && isset($order_data['order_id'])) {

    $order_id = $order_data['order_id']; // 注文IDを取得

    // 顧客情報を取得
    $mem_id = isset($order_data['entry']['customer']['ID']) ? $order_data['entry']['customer']['ID'] : null;

    if ($mem_id) {
      // 会員情報を取得
      $member = wel_get_member($mem_id);

      $liquid_supplement = isset($member['admb_liquid_supplement']) ? $member['admb_liquid_supplement'] : '1';
      $soy_flora_soap_professional = isset($member['admb_soy_flora_soap_professional']) ? $member['admb_soy_flora_soap_professional'] : '1';
      $soy_flora_soap_professional_mini = isset($member['admb_soy_flora_soap_professional_mini']) ? $member['admb_soy_flora_soap_professional_mini'] : '1';
      $mom_flora_serum_professinal = isset($member['admb_mom_flora_serum_professinal']) ? $member['admb_mom_flora_serum_professinal'] : '1';
      $tow_weeks_set = isset($member['admb_tow_weeks_set']) ? $member['admb_tow_weeks_set'] : '1';

      // 掛け率情報を保存
      update_post_meta($order_id, '_custom_liquid_supplement', $liquid_supplement);
      update_post_meta($order_id, '_custom_soy_flora_soap_professional', $soy_flora_soap_professional);
      update_post_meta($order_id, '_custom_soy_flora_soap_professional_mini', $soy_flora_soap_professional_mini);
      update_post_meta($order_id, '_custom_mom_flora_serum_professinal', $mom_flora_serum_professinal);
      update_post_meta($order_id, '_custom_tow_weeks_set', $tow_weeks_set);

      // 商品情報を取得し、合計金額を計算
      if (isset($order_data['cart']) && is_array($order_data['cart'])) {
        $items = $order_data['cart']; // カート内容を取得
        $total_price = 0;

        // 複数商品の場合に対応
        foreach ($items as $item) {
          $sku = isset($item['sku']) ? $item['sku'] : '';
          $price = isset($item['price']) ? floatval($item['price']) : 0;
          $quantity = isset($item['quantity']) ? intval($item['quantity']) : 1;

          if ($sku === "liquid_supplement") {
            $discounted_price = $price * (1 - $liquid_supplement);
          } elseif ($sku === "mom_flora_serum_professinal") {
            $discounted_price = $price * (1 - $mom_flora_serum_professinal);
          } elseif ($sku === "soy_flora_soap_professional") {
            $discounted_price = $price * (1 - $soy_flora_soap_professional);
          } elseif ($sku === "soy_flora_soap_professional_mini") {
            $discounted_price = $price * (1 - $soy_flora_soap_professional_mini);
          } elseif ($sku === "tow_weeks_set") {
            $discounted_price = $price * (1 - $tow_weeks_set);
          } else {
            $discounted_price = $price;
          }
          $total_price += $discounted_price * $quantity;
        }

        update_post_meta($order_id, '_custom_total_price', $total_price);
      }
    }
  }
}





add_filter('usces_member_discount_label', 'my_member_discount_label', 10, 2);
function my_member_discount_label($label, $umhs_id)
{
  //処理

  //echo '<pre>';
  //print_r($umhs_id);
  //echo  '</pre>';
  //$label = "aaaaa";
  return $label;
}


//  会員データtotal_priceのみ
global $global_order_prices;
$global_order_prices = [];
add_filter('usces_filter_get_total_price',  'my_usces_filter_get_total_price', 10, 2);
function my_usces_filter_get_total_price($total_price, $cart)
{
  global $global_order_prices, $wpdb;

  $order_id = $cart[0]['order_id'];
  $order_meta = $wpdb->get_row($wpdb->prepare(
    "SELECT mem_id FROM {$wpdb->prefix}usces_order WHERE ID = %d",
    $order_id
  ), ARRAY_A);
  $member_id = $order_meta['mem_id'];
  // メタデータから掛け率を取得
  $member_metas = $wpdb->get_results($wpdb->prepare(
    "SELECT meta_key, meta_value FROM {$wpdb->prefix}usces_member_meta WHERE member_id = %d",
    $member_id
  ), ARRAY_A);

  $liquid_supplement = 0;
  $soy_flora_soap_professional = 0;
  $soy_flora_soap_professional_mini = 0;
  $mom_flora_serum_professinal = 0;
  $tow_weeks_set = 0;

  if ($member_metas) {
    foreach ($member_metas as $meta) {
      if ($meta['meta_key'] == 'admb_liquid_supplement') {
        $liquid_supplement = floatval($meta['meta_value']);
      }
      if ($meta['meta_key'] == 'admb_soy_flora_soap_professional') {
        $soy_flora_soap_professional = floatval($meta['meta_value']);
      }
      if ($meta['meta_key'] == 'admb_soy_flora_soap_professional_mini') {
        $soy_flora_soap_professional_mini = floatval($meta['meta_value']);
      }
      if ($meta['meta_key'] == 'admb_mom_flora_serum_professinal') {
        $mom_flora_serum_professinal = floatval($meta['meta_value']);
      }
      if ($meta['meta_key'] == 'admb_tow_weeks_set') {
        $tow_weeks_set = floatval($meta['meta_value']);
      }
    }
  }

  $total_price = 0;
  // カート内の商品ごとに掛け率を適用
  foreach ($cart as $cart_row) {
    $sku = isset($cart_row['sku_code']) ? $cart_row['sku_code'] : ''; // SKUコードの取得
    $skuPrice = $cart_row['price']; // 元の単価
    $quantity = $cart_row['quantity']; // 数量

    if ($sku === 'liquid_supplement') {
      $discounted_price = $skuPrice;
    } elseif ($sku === 'mom_flora_serum_professinal') {
      $discounted_price = $skuPrice;
    } elseif ($sku === 'soy_flora_soap_professional') {
      $discounted_price = $skuPrice;
    } elseif ($sku === 'soy_flora_soap_professional_mini') {
      $discounted_price = $skuPrice;
    } elseif ($sku === 'tow_weeks_set') {
      $discounted_price = $skuPrice;
    } else {
      $discounted_price = $skuPrice;
    }
    $total_price += $discounted_price * $quantity;
  }
  //税
  $usces_tax = Welcart_Tax::get_instance();
  $tax_rate_standard = $usces_tax->tax_rate_standard;
  $re_taxPrice = floor($total_price * ($tax_rate_standard / 100));

  $order_id = isset($cart[0]['order_id']) ? $cart[0]['order_id'] : 'unknown'; // 注文IDを取得
  $global_order_prices[$order_id] = $re_taxPrice;

  return $total_price;
}

//受注リスト
add_filter('usces_filter_orderlist_detail', 'my_usces_filter_orderlist_detail', 10, 3);
function my_usces_filter_orderlist_detail($list_detail, $data, $curent_url)
{
  global $wpdb;

  $order_id = $data['ID'];
  $order_meta = $wpdb->get_row(
    $wpdb->prepare(
      "SELECT mem_id FROM {$wpdb->prefix}usces_order WHERE ID = %d",
      $order_id
    ),
    ARRAY_A
  );

  $member_id = $order_meta['mem_id'];
  // メンバーIDに基づいて `usces_member_meta` テーブルからカスタムメタデータを取得
  $member_metas = $wpdb->get_results(
    $wpdb->prepare(
      "SELECT meta_key, meta_value FROM {$wpdb->prefix}usces_member_meta WHERE member_id = %d",
      $member_id
    ),
    ARRAY_A
  );

  $liquid_supplement = 0;
  $soy_flora_soap_professional = 0;
  $soy_flora_soap_professional_mini = 0;
  $mom_flora_serum_professinal = 0;
  $tow_weeks_set = 0;
  foreach ($member_metas as $meta) {
    if ($meta['meta_key'] == 'admb_liquid_supplement') {
      $liquid_supplement = floatval($meta['meta_value']);
    }
    if ($meta['meta_key'] == 'admb_soy_flora_soap_professional') {
      $soy_flora_soap_professional = floatval($meta['meta_value']);
    }
    if ($meta['meta_key'] == 'admb_soy_flora_soap_professional_mini') {
      $soy_flora_soap_professional_mini = floatval($meta['meta_value']);
    }
    if ($meta['meta_key'] == 'admb_mom_flora_serum_professinal') {
      $mom_flora_serum_professinal = floatval($meta['meta_value']);
    }
    if ($meta['meta_key'] == 'admb_tow_weeks_set') {
      $tow_weeks_set = floatval($meta['meta_value']);
    }
  }

  $order_items = $wpdb->get_results(
    $wpdb->prepare(
      "SELECT * FROM {$wpdb->prefix}usces_ordercart WHERE order_id = %d",
      $order_id
    ),
    ARRAY_A
  );

  $order_totals = [];
  foreach ($order_items as $item) {
    $sku_code = $item['sku_code'];
    $quantity = $item['quantity'];

    $items = wel_get_sku($item['post_id'], $sku_code);
    // 元の単価
    if (isset($items['price'])) {
      $price = $items['price'];
    }
    $order_id = $item['order_id'];

    if ($sku_code === 'liquid_supplement') {
      $discounted_price = $price * (1 - $liquid_supplement);
    } elseif ($sku_code === 'mom_flora_serum_professinal') {
      $discounted_price = $price * (1 - $mom_flora_serum_professinal);
    } elseif ($sku_code === 'soy_flora_soap_professional') {
      $discounted_price = $price * (1 - $soy_flora_soap_professional);
    } elseif ($sku_code === 'soy_flora_soap_professional_mini') {
      $discounted_price = $price * (1 - $soy_flora_soap_professional_mini);
    } elseif ($sku_code === 'tow_weeks_set') {
      $discounted_price = $price * (1 - $tow_weeks_set);
    } else {
      $discounted_price = $price;
    }
    $total_price = $discounted_price * $quantity;

    //税込み
    $usces_tax = Welcart_Tax::get_instance();
    $tax_rate_standard = $usces_tax->tax_rate_standard;
    $total_price = $total_price + floor($total_price * ($tax_rate_standard / 100));

    // 同じ注文IDの商品は同じ配列に合計値を保持
    if (!isset($order_totals[$order_id])) {
      $order_totals[$order_id] = [];
    }
    $order_totals[$order_id][] = $total_price; // 各商品の合計金額
  }

  //1回の買い物の合計値を$final_totalに出力
  foreach ($order_totals as $order_id => $prices) {
    $final_total = array_sum($prices);
  }

  $list_detail = preg_replace(
    '/<td class="price">[^<]*<\/td>/',
    '<td class="price">¥' . number_format($final_total) . '</td>',
    $list_detail
  );

  return $list_detail;
}

// 受注データ編集
global $total_price;
add_filter('usces_filter_get_ordercart_row', 'customize_ordercart_row_output', 9, 3);
function customize_ordercart_row_output($row, $order_id, $cart)
{
  global $wpdb, $total_price;

  // 注文情報からユーザーIDを取得
  $order_meta = $wpdb->get_row($wpdb->prepare(
    "SELECT mem_id FROM {$wpdb->prefix}usces_order WHERE ID = %d",
    $order_id
  ), ARRAY_A);
  $member_id = $order_meta['mem_id'];

  // メタデータから掛け率を取得
  $member_metas = $wpdb->get_results($wpdb->prepare(
    "SELECT meta_key, meta_value FROM {$wpdb->prefix}usces_member_meta WHERE member_id = %d",
    $member_id
  ), ARRAY_A);

  $liquid_supplement = 0;
  $soy_flora_soap_professional = 0;
  $soy_flora_soap_professional_mini = 0;
  $mom_flora_serum_professinal = 0;
  $tow_weeks_set = 0;

  foreach ($member_metas as $meta) {
    if ($meta['meta_key'] == 'admb_liquid_supplement') {
      $liquid_supplement = floatval($meta['meta_value']);
    }
    if ($meta['meta_key'] == 'admb_soy_flora_soap_professional') {
      $soy_flora_soap_professional = floatval($meta['meta_value']);
    }
    if ($meta['meta_key'] == 'admb_soy_flora_soap_professional_mini') {
      $soy_flora_soap_professional_mini = floatval($meta['meta_value']);
    }
    if ($meta['meta_key'] == 'admb_mom_flora_serum_professinal') {
      $mom_flora_serum_professinal = floatval($meta['meta_value']);
    }
    if ($meta['meta_key'] == 'admb_tow_weeks_set') {
      $tow_weeks_set = floatval($meta['meta_value']);
    }
  }

  $total = 0;
  $total_price = [];
  // カート内の商品ごとに掛け率を適用し、$row を直接書き換え
  foreach ($cart as $cart_row) {
    $sku = isset($cart_row['sku_code']) ? $cart_row['sku_code'] : ''; // SKUコードの取得

    $items = wel_get_sku($cart_row['post_id'], $cart_row['sku_code']);
    // 元の単価
    if (isset($items['price'])) {
      $skuPrice = $items['price'];
    }

    $cart_id = isset($cart_row['cart_id']) ? $cart_row['cart_id'] : ''; // カートIDの取得
    $quantity = $cart_row['quantity'];

    if ($sku === 'liquid_supplement') {
      $discounted_price = $skuPrice * (1 - $liquid_supplement);
    } elseif ($sku === 'mom_flora_serum_professinal') {
      $discounted_price = $skuPrice * (1 - $mom_flora_serum_professinal);
    } elseif ($sku === 'soy_flora_soap_professional') {
      $discounted_price = $skuPrice * (1 - $soy_flora_soap_professional);
    } elseif ($sku === 'soy_flora_soap_professional_mini') {
      $discounted_price = $skuPrice * (1 - $soy_flora_soap_professional_mini);
    } elseif ($sku === 'tow_weeks_set') {
      $discounted_price = $skuPrice * (1 - $tow_weeks_set);
    } else {
      $discounted_price = $skuPrice;
    }
    // 値をデータベースに保存
    $database_prices[] = [
      'discounted_price' => $discounted_price,
    ];

    // $row置換
    $pattern = '/<input name="skuPrice\[' . preg_quote($cart_id, '/') . '\]" class="text price" type="text" value="[^"]*"/';
    $replacement = '<input name="skuPrice[' . esc_attr($cart_id) . ']" class="text price" type="text" value="' . esc_attr(usces_crform($discounted_price, false, false, 'return', false)) . '"';

    // 正規表現置換で $row を書き換え
    $row = preg_replace($pattern, $replacement, $row);

    $discounted_price = $discounted_price * $quantity;

    $total += $discounted_price;
  }
  // 値をデータベースに保存
  update_option('custom_prices', $database_prices);

  $total_price['total'] = $total;
  //発送情報の取得追加
  $mem_ids = wel_get_order($order_id);
  $delivery_shipping_address = $mem_ids['delivery']['shipping_address'];
  if (!empty($delivery_shipping_address)) {
    echo $delivery_shipping_address_script .= <<<HTML
   <script>
    jQuery(function(){
        jQuery(window).on('load',function(){
        jQuery(".deli_info select[name='custom_delivery[shipping_address]'").append(jQuery('<option />').val('{$delivery_shipping_address}').html('{$delivery_shipping_address}'));
        jQuery("[name='custom_delivery[shipping_address]'] option[value='{$delivery_shipping_address}']").prop('selected', true);
    });
    });
   </script>
   HTML;
  }
  //
  return $row;
}

// 受注データ編集の消費税(10%のみ)
add_filter('usces_filter_ordereditform_carttable', 'custom_usces_filter_ordereditform_carttable', 10, 2);
function custom_usces_filter_ordereditform_carttable($cart_table, $filter_args)
{
  global $total_price;
  $total = $total_price['total'];
  // 値をデータベースに保存
  update_option('custom_total_price', $total_price);

  $usces_tax = Welcart_Tax::get_instance();
  $tax_rate_standard = $usces_tax->tax_rate_standard;

  $re_taxPrice =  $total * ($tax_rate_standard / 100);
  // error_log($re_taxPrice);

  $cart_table = preg_replace(
    '/(<tr>\s*<td[^>]*>\s*10％消費税[^<]*<span[^>]*><\/span><\/td>\s*<td[^>]*><input[^>]*value=")([0-9.,]+)("[^>]*\/>\s*<\/td>)/',
    '${1}' . $re_taxPrice . '${3}',
    $cart_table,
    1
  );
  $cart_table = preg_replace(
    '/(<tr>\s*<td[^>]*>\s*消費税[^<]*<span[^>]*><\/span><\/td>\s*<td[^>]*><input[^>]*id="order_tax"[^>]*value=")([0-9.,]+)("[^>]*\/>\s*<\/td>)/',
    '${1}' . $re_taxPrice . '${3}',
    $cart_table,
    1
  );
  return $cart_table;
}

//メール
add_filter('usces_filter_send_order_mail_meisai',  'my_filter_send_order_mail_meisai', 10, 4);
function my_filter_send_order_mail_meisai($msg_meisai, $data, $cart, $entry)
{
  global $wpdb;

  $mem_id = isset($data['mem_id']) ? intval($data['mem_id']) : 0;

  if ($mem_id) {
    $member_metas = $wpdb->get_results(
      $wpdb->prepare(
        "SELECT meta_key, meta_value FROM {$wpdb->prefix}usces_member_meta WHERE member_id = %d",
        $mem_id
      ),
      ARRAY_A
    );

    $liquid_supplement = 0;
    $soy_flora_soap_professional = 0;
    $soy_flora_soap_professional_mini = 0;
    $mom_flora_serum_professinal = 0;
    $tow_weeks_set = 0;

    foreach ($member_metas as $meta) {
      if ($meta['meta_key'] == 'admb_liquid_supplement') {
        $liquid_supplement = floatval($meta['meta_value']);
      }
      if ($meta['meta_key'] == 'admb_soy_flora_soap_professional') {
        $soy_flora_soap_professional = floatval($meta['meta_value']);
      }
      if ($meta['meta_key'] == 'admb_soy_flora_soap_professional_mini') {
        $soy_flora_soap_professional_mini = floatval($meta['meta_value']);
      }
      if ($meta['meta_key'] == 'admb_mom_flora_serum_professinal') {
        $mom_flora_serum_professinal = floatval($meta['meta_value']);
      }
      if ($meta['meta_key'] == 'admb_tow_weeks_set') {
        $tow_weeks_set = floatval($meta['meta_value']);
      }
    }
  } else {
    error_log("IDが見つかりませんでした。");
  }

  $total_discounted_price = 0;
  $updated_msg_meisai = $msg_meisai;

  foreach ($cart as $cart_row) {
    $sku = isset($cart_row['sku']) ? $cart_row['sku'] : '';
    $skuPrice = $cart_row['price'];
    $quantity = $cart_row['quantity'];

    if ($sku === 'liquid_supplement') {
      $discounted_price = $skuPrice * (1 - $liquid_supplement);
    } elseif ($sku === 'mom_flora_serum_professinal') {
      $discounted_price = $skuPrice * (1 - $mom_flora_serum_professinal);
    } elseif ($sku === 'soy_flora_soap_professional') {
      $discounted_price = $skuPrice * (1 - $soy_flora_soap_professional);
    } elseif ($sku === 'soy_flora_soap_professional_mini') {
      $discounted_price = $skuPrice * (1 - $soy_flora_soap_professional_mini);
    } elseif ($sku === 'tow_weeks_set') {
      $discounted_price = $skuPrice * (1 - $tow_weeks_set);
    } else {
      $discounted_price = $skuPrice;
    }

    $discounted_price_formatted = '¥' . number_format($discounted_price);
    $item_total_price = $discounted_price * $quantity;
    $item_total_price_formatted = '¥' . number_format($item_total_price);

    error_log($discounted_price_formatted);
    error_log($item_total_price);
    error_log($item_total_price_formatted);

    $updated_msg_meisai = preg_replace(
      '/単価\s¥' . number_format($skuPrice) . '\s×\s' . $quantity . '/',
      '単価 ' . $discounted_price_formatted . ' × ' . $quantity,
      $updated_msg_meisai,
      1
    );

    $updated_msg_meisai = preg_replace(
      '/商品合計\s:\s¥' . number_format($skuPrice * $quantity) . '/',
      '商品合計 : ' . $item_total_price_formatted,
      $updated_msg_meisai,
      1
    );

    $total_discounted_price += $item_total_price;
  }

  //消費税
  $usces_tax = Welcart_Tax::get_instance();
  $tax_rate_standard = $usces_tax->tax_rate_standard;
  $total_tax_price = $total_discounted_price * ($tax_rate_standard / 100);

  $total_items_price_formatted = '¥' . number_format($total_discounted_price);
  $total_tax_formatted = '¥' . number_format($total_tax_price);
  $total_price_formatted = '¥' . number_format($total_discounted_price + $total_tax_price);
  $eight_tax_formatted = '¥' . '0';

  $updated_msg_meisai = preg_replace('/商品合計\s*:\s*¥[0-9,]+/u', '商品合計 : ' . $total_items_price_formatted, $updated_msg_meisai);
  $updated_msg_meisai = preg_replace('/消費税\s*:\s*¥[0-9,]+/u', '消費税 : ' . $total_tax_formatted, $updated_msg_meisai);
  $updated_msg_meisai = preg_replace('/お支払い金額\s*:\s*¥[0-9,]+/u', 'お支払い金額 : ' . $total_price_formatted, $updated_msg_meisai);
  $updated_msg_meisai = preg_replace('/10％対象\s*:\s*¥[0-9,]+/u', '10％対象 : ' . $total_items_price_formatted, $updated_msg_meisai);
  $updated_msg_meisai = preg_replace('/10％消費税\s*:\s*¥[0-9,]+/u', '10％消費税 : ' . $total_tax_formatted, $updated_msg_meisai);
  $updated_msg_meisai = preg_replace('/8％消費税\s*:\s*¥[0-9,]+/u', '8％消費税 : ' . $eight_tax_formatted, $updated_msg_meisai);

  return $updated_msg_meisai;
}



//配送先設定



add_filter('usces_filter_custom_field_input',  'my_filter_custom_field_input', 10, 4);
function my_filter_custom_field_input($html, $data, $custom_field, $position)
{
  //$meid = $data['customer']['ID'];
  $mem_id  = wel_logged_in_id();
  $member  = wel_get_member($mem_id);
  $prefs = $data['customer']['pref'];
  $csmb_shipping_address_01           = $member['csmb_shipping_address_01'];
  $csmb_shipping_address_02           = $member['csmb_shipping_address_02'];
  $csmb_shipping_address_03           = $member['csmb_shipping_address_03'];

  //['csmb_shipping_address_01'];
  //配送先住所取得
  $csmb_shipping_address_01_name      = $member['csmb_shipping_address_01_name'];
  $csmb_shipping_address_01_name02    = $member['csmb_shipping_address_01_name02'];
  $csmb_shipping_address_01_zips      = $member['csmb_shipping_address_01_zips'];
  $csmb_shipping_address_01_pref      = $member['csmb_shipping_address_01_pref'];
  $csmb_shipping_address_01_address01 = $member['csmb_shipping_address_01_address01'];
  $csmb_shipping_address_01_address02 = $member['csmb_shipping_address_01_address02'];
  $csmb_shipping_address_01_address03 = $member['csmb_shipping_address_01_address03'];
  $csmb_shipping_address_01_tel       = $member['csmb_shipping_address_01_tel'];
  //['csmb_shipping_address_02'];
  $csmb_shipping_address_02_name      = $member['csmb_shipping_address_02_name'];
  $csmb_shipping_address_02_name02    = $member['csmb_shipping_address_02_name02'];
  $csmb_shipping_address_02_zips      = $member['csmb_shipping_address_02_zips'];
  $csmb_shipping_address_02_pref      = $member['csmb_shipping_address_02_pref'];
  $csmb_shipping_address_02_address01 = $member['csmb_shipping_address_02_address01'];
  $csmb_shipping_address_02_address02 = $member['csmb_shipping_address_02_address02'];
  $csmb_shipping_address_02_address03 = $member['csmb_shipping_address_02_address03'];
  $csmb_shipping_address_02_tel       = $member['csmb_shipping_address_02_tel'];
  //['csmb_shipping_address_03'];
  $csmb_shipping_address_03_name      = $member['csmb_shipping_address_03_name'];
  $csmb_shipping_address_03_name02    = $member['csmb_shipping_address_03_name02'];
  $csmb_shipping_address_03_zips      = $member['csmb_shipping_address_03_zips'];
  $csmb_shipping_address_03_pref      = $member['csmb_shipping_address_03_pref'];
  $csmb_shipping_address_03_address01 = $member['csmb_shipping_address_03_address01'];
  $csmb_shipping_address_03_address02 = $member['csmb_shipping_address_03_address02'];
  $csmb_shipping_address_03_address03 = $member['csmb_shipping_address_03_address03'];
  $csmb_shipping_address_03_tel       = $member['csmb_shipping_address_03_tel'];

  $meta = usces_has_custom_field_meta($custom_field);
  foreach ($meta as $key => $entry) {
    $key_class = $key;
    $positions = $entry['position'];
  }

  if ($positions == $position && $key_class == 'shipping_address' && $custom_field == 'delivery' && (!empty($csmb_shipping_address_01) || (!empty($csmb_shipping_address_02)) || (!empty($csmb_shipping_address_03)))) {
    //配送先設定がある場合
    if (!empty($csmb_shipping_address_01)) {
      $csmb_shipping_address_01_text  .= '<option value="' . esc_attr($csmb_shipping_address_01) . '" id="delivery_office_01_serect">' . esc_html($csmb_shipping_address_01) . '</option>';
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
      $csmb_shipping_address_02_text  .= '<option value="' . esc_attr($csmb_shipping_address_02) . '" id="delivery_office_02_serect">' . esc_html($csmb_shipping_address_02) . '</option>';
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
      $csmb_shipping_address_03_text  .= '<option value="' . esc_attr($csmb_shipping_address_03) . '" id="delivery_office_03_serect">' . esc_html($csmb_shipping_address_03) . '</option>';
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
            </select>
            <div class="submits" id="registered_delivery">配送先住所読み込み</div>
             </div>
            {$delivery_office_01}
            {$delivery_office_02}
            {$delivery_office_03}
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


function getcatorder($query)
{

  if (is_admin() || ! $query->is_main_query())
    return;
  //$post_data = $wpdb->get_row("SELECT post_title FROM $wpdb->posts WHERE post_id = 9999");

  if ($query->is_category(2)) {


    //try {
    //    // データベースへ接続
    //    $dsn = 'mysql:dbname=mbff_btob;host=localhost;charset=utf8';
    //    $user = 'mbff_btob_user';
    //    $password = 'Le1s^q999st';
    //    $pdo = new PDO($dsn, $user, $password);
    //    //$item_codes = [];
    //    foreach ($pdo->query('select * FROM mbff_btob_usces_item') as $row) {
    //        $item_codes = $row['itemCode'];
    //        $item_codestest[] = $item_codes;
    //    }
    //    //echo $item_codestest;
    //
    //    //echo $item_codes;
    //} catch (PDOException $Exception) { //エラー処理
    //    //die('接続エラー：' . $Exception->getMessage());
    //}
    //$item_codes_ = array('2024', '2025');


    //$query->set('posts_per_page', '2');
    //$query->set('orderby', 'usces_the_itemCode()');
    //$query->set('meta_key', $item_codestest);
    //$query->set('order', 'ACF');
    return;
  }
}
add_action('pre_get_posts', 'getcatorder');


//発送完了メールの内容 デフォルトのフックをキャンセル
add_action('init', 'replace_order_item_ajax');
function replace_order_item_ajax()
{
  // 既存のアクションを解除
  remove_action('wp_ajax_order_item_ajax', 'order_item_ajax');

  // 新しいアクションを追加
  add_action('wp_ajax_order_item_ajax', 'custom_order_item_ajax');
}

// 発送完了メール カスタムフック
/**
 * Order item ajax
 */
function custom_order_item_ajax()
{
  global $usces;
  // データベースから値を取得
  $price_data = get_option('custom_prices', []);
  $total_price = get_option('custom_total_price', ['total' => 0]);
  $tax = $total_price['total'] * 0.1;
  $tax_format = number_format($total_price['total'] * 0.1);
  $total_price_format = number_format($total_price['total']);
  $total = $total_price['total'] + $tax;
  $total_format = number_format($total);

  if (! current_user_can('wel_manage_order')) {
    wp_die(__('You do not have sufficient privileges to perform this operation.', 'usces'));
  }

  if ('order_item_ajax' !== wp_unslash($_POST['action'])) {
    die(0);
  }

  $res   = false;
  $_POST = $usces->stripslashes_deep_post($_POST);
  $mode  = sanitize_text_field(wp_unslash($_POST['mode']));

  switch ($mode) {
    case 'completionMail': //該当箇所
    case 'orderConfirmMail':
    case 'changeConfirmMail':
    case 'receiptConfirmMail':
    case 'mitumoriConfirmMail':
    case 'cancelConfirmMail':
    case 'otherConfirmMail':
      check_ajax_referer('order_edit', 'wc_nonce');
      $order_id = (int) wp_unslash($_POST['order_id']);
      $res      = usces_order_confirm_message($order_id);
      // 書き換え
      $counter = 1;
      foreach ($price_data as $key => $data) {
        if (isset($data['discounted_price'])) {
          $price = number_format($data['discounted_price']);
          error_log($price);
          // カウンタを2桁でフォーマット（例: 01, 02, ...）
          $formatted_counter = str_pad($counter, 2, '0', STR_PAD_LEFT);

          $unique_price = "単価{$formatted_counter} ¥{$price}";
          $res = preg_replace('/単価 ¥[\d,]+/', $unique_price, $res, 1);

          $counter++;
        }
      }
      $res = preg_replace('/商品合計 : ¥[\d,]+/', '商品合計：¥' . $total_format, $res);
      $res = preg_replace('/(?<!％)消費税 : ¥[\d,]+/', '消費税：¥ ' . $tax_format, $res);
      $res = preg_replace('/お支払い金額 : ¥[\d,]+/', 'お支払い金額：¥' . $total_format, $res);
      $res = preg_replace('/10％対象 : ¥[\d,]+/', '10％対象：¥' . $total_price_format, $res);
      $res = preg_replace('/10％消費税 : ¥[\d,]+/', '10％消費税：¥' . $tax_format, $res);
      break;
    case 'sendmail':
      // Nonce('wc_send_mail_order_nonce') verification is executed in the usces_ajax_send_mail function.
      $res = usces_ajax_send_mail();
      break;
    case 'get_order_item':
      check_ajax_referer('order_edit', 'wc_nonce');
      $itemcode = sanitize_text_field(wp_unslash($_POST['itemcode']));
      $res      = get_order_item($itemcode);
      break;
    case 'get_item_select_option':
      check_ajax_referer('order_edit', 'wc_nonce');
      $cat_id = (int) wp_unslash($_POST['cat_id']);
      $res    = usces_get_item_select_option($cat_id);
      break;
    case 'ordercheckpost':
      check_ajax_referer('order_edit', 'wc_nonce');
      $res = usces_update_ordercheck();
      break;
    case 'getmember':
      check_ajax_referer('order_edit', 'wc_nonce');
      $res = usces_get_member_neworder();
      break;
    case 'recalculation':
      check_ajax_referer('order_edit', 'wc_nonce');
      $change_taxrate = (isset($_POST['change_taxrate'])) ? sanitize_text_field(wp_unslash($_POST['change_taxrate'])) : '';
      $order_id       = (int) wp_unslash($_POST['order_id']);
      $mem_id         = (int) wp_unslash($_POST['mem_id']);
      $res            = usces_order_recalculation($order_id, $mem_id, $_POST['post_ids'], $_POST['prices'], $_POST['quants'], $_POST['cart_ids'], $_POST['upoint'], $_POST['shipping_charge'], $_POST['cod_fee'], $_POST['discount'], $change_taxrate);
      break;
    case 'recalculation_reduced':
      check_ajax_referer('order_edit', 'wc_nonce');
      $change_taxrate = (isset($_POST['change_taxrate'])) ? sanitize_text_field(wp_unslash($_POST['change_taxrate'])) : '';
      $order_id       = (int) wp_unslash($_POST['order_id']);
      $mem_id         = (int) wp_unslash($_POST['mem_id']);
      $res            = usces_order_recalculation_reduced($order_id, $mem_id, $_POST['post_ids'], $_POST['prices'], $_POST['quants'], $_POST['cart_ids'], $_POST['upoint'], $_POST['shipping_charge'], $_POST['cod_fee'], $_POST['discount_standard'], $_POST['discount_reduced'], $change_taxrate);
      break;
    case 'get_settlement_log':
      check_ajax_referer('order_list', 'wc_nonce');
      $res = usces_get_settlement_log();
      break;
    case 'get_settlement_log_detail':
      check_ajax_referer('order_list', 'wc_nonce');
      $log_key = sanitize_text_field(wp_unslash($_POST['log_key']));
      $res     = usces_get_settlement_log_detail($log_key);
      break;
    case 'search_settlement_log':
      check_ajax_referer('order_list', 'wc_nonce');
      $log_key = sanitize_text_field(wp_unslash($_POST['log_key']));
      $res     = usces_get_settlement_log($log_key);
      break;
    case 'delete_settlement_log':
      check_ajax_referer('order_list', 'wc_nonce');
      $log_key = sanitize_text_field(wp_unslash($_POST['log_key']));
      usces_delete_settlement_log($log_key);
      $res = usces_get_settlement_log();
      break;
    case 'delete_settlement_log_all':
      check_ajax_referer('order_list', 'wc_nonce');
      usces_delete_settlement_log();
      $res = usces_get_settlement_log();
      break;
    case 'revival_order_data':
      check_ajax_referer('order_list', 'wc_nonce');
      $log_key       = sanitize_text_field(wp_unslash($_POST['log_key']));
      $register_date = sanitize_text_field(wp_unslash($_POST['register_date']));
      $res           = usces_revival_order_data($log_key, $register_date);
      break;
    case 'get_settlement_error_log':
      check_ajax_referer('order_list', 'wc_nonce');
      $res = usces_get_settlement_error_log();
      break;
    case 'get_settlement_error_log_detail':
      check_ajax_referer('order_list', 'wc_nonce');
      $log_id = sanitize_text_field(wp_unslash($_POST['log_id']));
      $res    = usces_get_settlement_error_log_detail($log_id);
      break;
    case 'delete_settlement_error_log':
      check_ajax_referer('order_list', 'wc_nonce');
      $log_id = sanitize_text_field(wp_unslash($_POST['log_id']));
      usces_delete_settlement_error_log($log_id);
      $res = usces_get_settlement_error_log();
      break;
    case 'delete_settlement_error_log_all':
      check_ajax_referer('order_list', 'wc_nonce');
      usces_delete_settlement_error_log();
      $res = usces_get_settlement_error_log();
      break;
    case 'reset_settlement_notice':
      check_ajax_referer('order_list', 'wc_nonce');
      $res = usces_reset_settlement_notice();
      break;
  }

  $res = wel_esc_script(apply_filters('usces_filter_order_item_ajax', $res));

  if (false === $res) {
    die(0);
  }

  die($res);
}
