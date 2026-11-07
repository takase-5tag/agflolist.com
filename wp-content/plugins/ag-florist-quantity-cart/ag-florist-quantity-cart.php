<?php

/**
 * Plugin Name: AG FLOLIST 商品一覧 数量連動カート
 * Description: 企業別/固定URL会員のWelcart購入一覧に＋／－を追加。WCEX Widget Cart表示をAjaxで連動（WCEXのファイル改変なし）。
 * Version: 1.0.5-beta
 * Requires PHP: 7.4
 */
if (!defined('ABSPATH')) {
    exit;
}

define('AGFL_STEP_VERSION', '1.0.5-beta');

/** Welcart session token, NOT a WordPress visitor nonce shared by all non-WP users. */
function agfl_step_token()
{
    if (session_status() !== PHP_SESSION_ACTIVE || !isset($_SESSION) || !is_array($_SESSION)) {
        return '';
    }
    if (empty($_SESSION['agfl_step_token']) || !is_string($_SESSION['agfl_step_token'])) {
        $_SESSION['agfl_step_token'] = wp_generate_password(48, false, false);
    }
    return $_SESSION['agfl_step_token'];
}
function agfl_step_user_allowed()
{
    return function_exists('usces_is_login') && usces_is_login()
        && function_exists('agcm_current_member_type')
        && in_array(agcm_current_member_type(), array('company', 'fixed'), true);
}

function agfl_step_product_is_item($pid)
{
    if (get_post_status($pid) !== 'publish') {
        return false;
    }
    if (get_post_mime_type($pid) === 'item') {
        return true;
    }
    $root = get_category_by_slug('item');
    if (!$root || is_wp_error($root)) {
        return false;
    }
    $terms = wp_get_post_categories($pid);
    if (is_wp_error($terms)) {
        return false;
    }
    foreach ((array) $terms as $term_id) {
        if ((int)$term_id === (int)$root->term_id || term_is_ancestor_of((int)$root->term_id, (int)$term_id, 'category')) {
            return true;
        }
    }
    return false;
}

/** Only option-free SKUs are managed in the listing; option products use normal detail page. */
function agfl_step_supports_sku($pid, $sku)
{
    if (function_exists('agfl_trial_is_product') && agfl_trial_is_product($pid) && !agfl_trial_can_purchase()) {
        return false;
    }
    if (!agfl_step_product_is_item($pid) || !function_exists('agcm_sku_base')) {
        return false;
    }
    $base = agcm_sku_base($pid, $sku);
    if ($base === null || !is_numeric($base) || (float)$base <= 0) {
        return false;
    }
    if (function_exists('usces_get_opts')) {
        $opts = usces_get_opts($pid, 'name');
        if (is_array($opts) && count($opts)) {
            return false;
        }
    }
    return true;
}

/** Native session cart row, no custom serialization assumed. */
function agfl_step_find_row($pid, $sku)
{
    global $usces;
    if (!$usces || !isset($usces->cart) || !is_array($_SESSION['usces_cart'] ?? null)) {
        return array(null, 0);
    }
    foreach ($_SESSION['usces_cart'] as $serial => $row) {
        $decoded = @unserialize((string)$serial, array('allowed_classes' => false));
        if (!is_array($decoded) || count($decoded) !== 1) {
            continue;
        }
        $entry_id = array_key_first($decoded);
        if ((int)$entry_id !== $pid || !is_array($decoded[$entry_id]) || count($decoded[$entry_id]) !== 1) {
            continue;
        }
        $entry_sku = array_key_first($decoded[$entry_id]);
        if (rawurldecode((string)$entry_sku) !== $sku || $decoded[$entry_id][$entry_sku] !== 0) {
            continue;
        }
        return array((string)$serial, max(0, (int)($row['quant'] ?? 0)));
    }
    return array(null, 0);
}
function agfl_step_cart_snapshot()
{
    $items = array();
    $count = 0;
    $total = 0;
    foreach ((array)($_SESSION['usces_cart'] ?? array()) as $serial => $row) {
        if (!function_exists('agcm_serial_item')) {
            continue;
        }
        $item = agcm_serial_item($serial);
        if (!$item) {
            continue;
        }
        $pid = (int)$item[0];
        $sku = (string)$item[1];
        $qty = max(0, (int)($row['quant'] ?? 0));
        $price = (float)($row['price'] ?? 0);
        $count += $qty;
        $total += $price * $qty;
        $items[] = array(
            'post_id' => $pid,
            'sku' => $sku,
            'quantity' => $qty,
            'price' => $price
        );
    }
    return array(
        'items' => $items,
        'total_quantity' => $count,
        'subtotal' => (int)$total,
        'tansa_quantity' => function_exists('agcm_tansa_cart_quantity') ? agcm_tansa_cart_quantity() : 0
    );
}

/** Recompute ALL cart line prices when crossing fixed member's TANSA tier boundaries. */
function agfl_step_reprice()
{
    if (empty($_SESSION['usces_cart']) || !is_array($_SESSION['usces_cart'])) {
        return true;
    }
    if (!function_exists('agcm_serial_item') || !function_exists('agcm_member_price') || !function_exists('agcm_sku_base')) {
        return false;
    }
    foreach ($_SESSION['usces_cart'] as $serial => &$row) {
        if (!is_array($row)) {
            continue;
        }
        $item = agcm_serial_item((string)$serial);
        if (!$item) {
            return false;
        }
        $base = agcm_sku_base($item[0], $item[1]);
        if ($base === null || $base <= 0) {
            // An existing option/legacy line with positive effective price
            // may stay unchanged if no fixed-member TANSA tier depends on it.
            // Do not turn an unknown price into 0 or discount it twice.
            $is_fixed_tansa = agcm_current_member_type() === 'fixed'
                && function_exists('agcm_is_tansa_product') && agcm_is_tansa_product($item[0]);
            if (!$is_fixed_tansa && isset($row['price']) && (float)$row['price'] > 0) {
                continue;
            }
            return false;
        }
        $price = agcm_member_price($item[0], $item[1], $base);
        if ($price === null || !is_numeric($price) || (float)$price <= 0) {
            return false;
        }
        $row['price'] = $price;
        $row['unit_price'] = $price;
    }
    unset($row);
    return true;
}

/** Ensure Widget Cart generated checkout URL points to THIS site, not an older site's hard-coded URL. */
add_filter('widgetcart_filter_button', function ($html, $cart) {
    if (empty($cart) || !defined('USCES_CART_URL')) {
        return $html;
    }
    return '<div id="wdgctToCheckout"><a href="' . esc_url(USCES_CART_URL) . '">カートを確認する</a></div>';
}, 99, 2);

/** Render snapshot of all visible list prices after fixed-tier change. */
function agfl_step_prices_for_catalog()
{
    $prices = array();
    foreach ((array)($_POST['visible'] ?? array()) as $entry) {
        if (!is_array($entry)) {
            continue;
        }
        $pid = absint($entry['post_id'] ?? 0);
        $sku = sanitize_text_field(wp_unslash($entry['sku'] ?? ''));
        if (!$pid || $sku === '' || !agfl_step_supports_sku($pid, $sku)) {
            continue;
        }
        $base = agcm_sku_base($pid, $sku);
        $price = agcm_member_price($pid, $sku, $base);
        if (!is_numeric($price) || (float)$price <= 0) {
            continue;
        }
        $prices[] = array('post_id' => $pid, 'sku' => $sku, 'price' => (int)$price);
    }
    return $prices;
}

/** Render the fixed drawer from the same validated cart snapshot as the footer totals.
 * WCEX Widget Cart may be inactive; its widget markup must not be used as a
 * second source of truth for a custom +/- cart. No mutations are performed.
 */
function agfl_step_dock_lines_html($items)
{
    if (empty($items)) {
        return '<div class="agfl-dock-fallback"><p class="agfl-dock-empty">現在、カートに商品はありません。</p></div>';
    }
    $html = '<div class="agfl-dock-fallback"><ul class="agfl-dock-fallback-list">';
    foreach ((array)$items as $line) {
        if (!is_array($line)) {
            continue;
        }
        $pid   = absint($line['post_id'] ?? 0);
        $qty   = max(0, (int)($line['quantity'] ?? 0));
        $price = (float)($line['price'] ?? 0);
        if (!$pid || !$qty) {
            continue;
        }
        $name = get_the_title($pid);
        if (!is_string($name) || $name === '') {
            $name = '商品';
        }
        $html .= '<li>'
            . '<span class="agfl-dock-line-name">' . esc_html($name) . '</span>'
            . '<span class="agfl-dock-line-qty">' . esc_html((string)$qty) . '点</span>'
            . '<strong class="agfl-dock-line-amount">¥' . esc_html(number_format_i18n($price * $qty)) . '</strong>'
            . '</li>';
    }
    return $html . '</ul></div>';
}

/** Fresh server-side state, also used when navigating BACK from Welcart's cart page.
 * The sync action is read-only but requires the actual Welcart member session.
 * No user-supplied prices are accepted.
 */
function agfl_step_current_payload()
{
    $result = agfl_step_cart_snapshot();
    $result['prices'] = agfl_step_prices_for_catalog();
    $result['widget_html'] = agfl_step_dock_lines_html($result['items']);
    $result['token'] = agfl_step_token();
    return $result;
}

function agfl_step_ajax()
{
    if (!agfl_step_user_allowed()) {
        wp_send_json_error(array('message' => 'Welcart会員でログインしてください。'), 403);
    }
    // The browser can restore the product listing from BFCache after visiting
    // /usces-cart/. Do not trust the old page's token or quantities.
    // A read-only sync returns fresh values before any +/- operations.
    $operation = isset($_POST['op']) && is_string($_POST['op'])
        ? sanitize_key(wp_unslash($_POST['op'])) : '';
    if ($operation === 'sync') {
        wp_send_json_success(agfl_step_current_payload());
    }
    $actual = agfl_step_token();
    $submitted = isset($_POST['token']) && is_string($_POST['token']) ? wp_unslash($_POST['token']) : '';
    if ($actual === '' || !hash_equals($actual, $submitted)) {
        wp_send_json_error(array('message' => 'セッションが切れました。ページを更新してお試しください。'), 403);
    }
    $pid = absint($_POST['post_id'] ?? 0);
    $sku = isset($_POST['sku']) && is_string($_POST['sku']) ? sanitize_text_field(wp_unslash($_POST['sku'])) : '';
    $op = isset($_POST['op']) ? sanitize_key(wp_unslash($_POST['op'])) : '';
    if (!$pid || $sku === '' || !in_array($op, array('plus', 'minus'), true) || !agfl_step_supports_sku($pid, $sku)) {
        wp_send_json_error(array('message' => 'この商品は一覧から数量を変更できません。商品詳細からお手続きください。'), 400);
    }
    global $usces;
    if (!$usces || empty($usces->cart)) {
        wp_send_json_error(array('message' => 'Welcartが利用できません。'), 503);
    }
    if (session_status() !== PHP_SESSION_ACTIVE) {
        wp_send_json_error(array('message' => '会員セッションを確認できません（SESSION）。', 'code' => 'SESSION'), 409);
    }
    list($serial, $old_qty) = agfl_step_find_row($pid, $sku);
    $new_qty = $old_qty + ($op === 'plus' ? 1 : -1);
    if ($new_qty < 0) {
        $new_qty = 0;
    }
    if ($new_qty > 99) {
        wp_send_json_error(array('message' => '一度に設定できる数量は99点までです。'), 400);
    }
    if ($op === 'plus') {
        $stock = method_exists($usces, 'getItemZaikoStatusId') ? $usces->getItemZaikoStatusId($pid, $sku) : false;
        if ($stock === false || (int)$stock >= 2) {
            wp_send_json_error(array('message' => '在庫状況をご確認ください（STOCK_STATUS）。', 'code' => 'STOCK_STATUS'), 409);
        }
        // If a finite stock number is configured, do not allow quantity to exceed it.
        if (method_exists($usces, 'getItemZaikoNum')) {
            $available = $usces->getItemZaikoNum($pid, $sku);
            // Welcart commonly uses zaikonum=0 as "not quantity-managed" when
            // zaikostatus=0 (in stock). Do NOT interpret this as zero stock.
            // For 'low stock' (status=1), a finite zero still blocks purchase.
            if (
                $available !== false && $available !== '' && $available !== null
                && is_numeric($available) && (int)$available >= 0
            ) {
                $finite_limit = ((int)$available > 0) || ((int)$stock === 1);
                if ($finite_limit && $new_qty > (int)$available) {
                    wp_send_json_error(array('message' => '在庫数を超える数量は指定できません（STOCK_QUANTITY）。', 'code' => 'STOCK_QUANTITY'), 409);
                }
            }
        }
    }
    $previous_cart = (array)($_SESSION['usces_cart'] ?? array());
    if ($new_qty === 0) {
        if ($serial !== null) {
            $keys = array_keys((array)$_SESSION['usces_cart']);
            $index = array_search($serial, $keys, true);
            unset($_SESSION['usces_cart'][$serial]);
            if ($index !== false) {
                do_action('usces_cart_del_row', (int)$index);
                do_action('usces_action_after_cart_del_row', (int)$index);
            }
        }
        unset($_SESSION['usces_entry']['order']['usedpoint']);
    } else {
        // Use Welcart native cart insertion so its serial, validation and hooks remain intact.
        $previous_post = $_POST;
        $_POST = array(
            'inCart' => array($pid => array($sku => '1')),
            'quant' => array($pid => array($sku => $new_qty)),
        );
        $usces->cart->inCart();
        $_POST = $previous_post;
    }
    if (!agfl_step_reprice()) {
        $_SESSION['usces_cart'] = $previous_cart;
        wp_send_json_error(array('message' => '一部商品の基準価格を取得できませんでした（PRICE_LOOKUP）。変更は取り消しました。', 'code' => 'PRICE_LOOKUP'), 409);
    }
    // Detect legacy customized cart.class.php which can overwrite effective prices on get_cart().
    $native_rows = method_exists($usces->cart, 'get_cart') ? $usces->cart->get_cart() : array();
    foreach ((array)$native_rows as $row) {
        if (isset($row['quantity']) && (int)$row['quantity'] > 0 && (float)($row['price'] ?? 0) <= 0) {
            $_SESSION['usces_cart'] = $previous_cart;
            wp_send_json_error(array('message' => 'Welcart側で0円の単価が返されました（NATIVE_PRICE_ZERO）。カート側の価格処理を確認してください。', 'code' => 'NATIVE_PRICE_ZERO'), 409);
        }
    }
    wp_send_json_success(agfl_step_current_payload());
}
/**
 * IMPORTANT: Welcart switches PHP session names for is_admin() requests.
 * wp-admin/admin-ajax.php always runs with the admin session rather than the
 * storefront member session. Route POSTs via the public product category URL
 * (normal front-end request) so login and cart share one Welcart session.
 */
add_action('template_redirect', function () {
    if (!isset($_GET['agfl_stepper']) || (string)$_GET['agfl_stepper'] !== '1') {
        return;
    }
    // Respond only on the product-category archive, never on other page URLs.
    if (!is_category()) {
        return;
    }
    $term = get_queried_object();
    $root = get_category_by_slug('item');
    if (
        !$root || !$term || !isset($term->term_id)
        || ((int)$term->term_id !== (int)$root->term_id
            && !term_is_ancestor_of((int)$root->term_id, (int)$term->term_id, 'category'))
    ) {
        return;
    }
    nocache_headers();
    if (strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'POST') {
        wp_send_json_error(array('message' => 'POSTのみ受け付けます。'), 405);
    }
    agfl_step_ajax();
}, -1000);

/** No AJAX caching and always include scripts only on item archive. */
add_action('wp_enqueue_scripts', function () {
    if (!is_category() && !is_page('trial')) {
        return;
    }
    $term = get_queried_object();
    $item = get_category_by_slug('item');
    if (is_page('trial')) {
        $term = $item;
    }
    if (!$item || !$term || !isset($term->term_id) || !($term->term_id === $item->term_id
        || term_is_ancestor_of($item->term_id, $term->term_id, 'category'))) {
        return;
    }
    $uri = plugin_dir_url(__FILE__) . 'assets/stepper.js';
    $script_file = plugin_dir_path(__FILE__) . 'assets/stepper.js';
    wp_enqueue_script(
        'agfl-product-stepper',
        $uri,
        array(),
        file_exists($script_file) ? (string)filemtime($script_file) : AGFL_STEP_VERSION,
        true
    );
    $token = agfl_step_token();
    $category_url = get_category_link((int)$term->term_id);
    if (is_wp_error($category_url) || !$category_url) {
        return;
    }
    $endpoint_url = add_query_arg('agfl_stepper', '1', $category_url);
    wp_add_inline_script('agfl-product-stepper', 'window.agflStepper=' . wp_json_encode(array(
        'url' => $endpoint_url,
        'token' => $token,
        'member' => agfl_step_user_allowed()
    )) . ';', 'before');
}, 99);

/** ② fixed members' one-time trial offer and purchase restrictions. */
require_once plugin_dir_path(__FILE__) . 'includes/trial-gate.php';

/** WCEX Widget Cart fixed footer integration (no vendor-plugin modifications). */
require_once plugin_dir_path(__FILE__) . 'includes/cart-dock.php';
