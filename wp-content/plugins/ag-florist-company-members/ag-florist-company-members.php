<?php

/**
 * Plugin Name: AG FLOLIST 企業別会員・掛け率管理
 * Description: 企業別QR会員・固定URL会員・商品SKU別掛け率・TANSA数量掛け率・月次請求を管理します。
 * Version: 1.1.5-beta
 * Author: AG FLOLIST custom development
 * Requires PHP: 7.4
 */
if (!defined('ABSPATH')) {
    exit;
}

define('AGCM_VERSION', '1.1.5-beta');
define('AGCM_DIR', plugin_dir_path(__FILE__));
define('AGCM_URL', plugin_dir_url(__FILE__));

/**
 * Dedicated permission: only WP administrators and editors manage companies.
 * Do not use edit_posts here; authors and custom roles could also have it.
 */
define('AGCM_MANAGE_CAP', 'manage_agcm_companies');

function agcm_install_management_capability()
{
    foreach (array('administrator', 'editor') as $role_name) {
        $role = get_role($role_name);
        if ($role && !$role->has_cap(AGCM_MANAGE_CAP)) {
            $role->add_cap(AGCM_MANAGE_CAP);
        }
    }
    update_option('agcm_access_schema_version', '1', false);
}
register_activation_hook(__FILE__, 'agcm_install_management_capability');

// An in-place plugin update does not necessarily fire the activation hook.
// Grant editor access once after upgrading an already-active installation.
add_action('init', function () {
    if (is_admin() && get_option('agcm_access_schema_version') !== '1') {
        agcm_install_management_capability();
    }
}, -95);

/* Company records are managed through the restricted AGCM admin screen, not public queries. */
add_action('init', function () {
    // Legacy functions.php's stage-1 block must not intercept a verified invitation.
    // This plugin supplies a stricter replacement; leave the site's login CSS intact.
    remove_action('init', 'agFLOLIST_block_public_member_signup', 0);
    register_post_type('agcm_company', array(
        'label' => '企業',
        'public' => false,
        'show_ui' => false,
        'show_in_rest' => false,
        'exclude_from_search' => true,
        'supports' => array('title'),
        'capability_type' => 'post',
        'map_meta_cap' => true,
    ));
}, -100);

function agcm_companies()
{
    return get_posts(array('post_type' => 'agcm_company', 'post_status' => 'private', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC', 'suppress_filters' => true));
}
function agcm_company($id)
{
    if ((int)$id <= 0) {
        return null;
    }
    $post = get_post((int)$id);
    return ($post && $post->post_type === 'agcm_company' && $post->post_status === 'private') ? $post : null;
}
function agcm_enabled($id)
{
    return (bool)(agcm_company($id) && get_post_meta((int)$id, '_agcm_enabled', true) === '1');
}
function agcm_token()
{
    return bin2hex(random_bytes(32));
}
function agcm_invite_url($id)
{
    $token = (string)get_post_meta((int)$id, '_agcm_token', true);
    return $token ? add_query_arg('ag_invite', rawurlencode($token), home_url('/usces-member/')) : '';
}
function agcm_find_invite($token)
{
    if (!is_string($token) || !preg_match('/^[a-f0-9]{64}$/D', $token)) {
        return 0;
    }
    // Direct prepared lookup: WP_Query can hide private records from visitors.
    global $wpdb;
    $id = (int)$wpdb->get_var($wpdb->prepare(
        "SELECT p.ID FROM {$wpdb->posts} p INNER JOIN {$wpdb->postmeta} m ON m.post_id=p.ID
         WHERE p.post_type=%s AND p.post_status=%s AND m.meta_key=%s AND m.meta_value=%s LIMIT 1",
        'agcm_company',
        'private',
        '_agcm_token',
        $token
    ));
    return $id && agcm_enabled($id) ? $id : 0;
}
function agcm_is_member_path()
{
    $path = wp_parse_url((string)($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH);
    $expected = wp_parse_url(home_url('/usces-member/'), PHP_URL_PATH);
    return is_string($path) && is_string($expected) && untrailingslashit($path) === untrailingslashit($expected);
}
function agcm_issue_cookie($id)
{
    if (function_exists('agcm_clear_fixed_cookie')) {
        agcm_clear_fixed_cookie();
    }
    $expires = time() + 60 * 60 * 2;
    $payload = (int)$id . '.' . $expires;
    $token = (string)get_post_meta((int)$id, '_agcm_token', true);
    $signature = hash_hmac('sha256', $payload . '.' . $token, wp_salt('auth'));
    $path = wp_parse_url(home_url('/usces-member/'), PHP_URL_PATH);
    $path = is_string($path) && $path !== '' ? trailingslashit($path) : '/';
    setcookie('agcm_invitation', $payload . '.' . $signature, array(
        'expires' => $expires,
        'path' => $path,
        'secure' => is_ssl(),
        'httponly' => true,
        'samesite' => 'Lax'
    ));
    $_COOKIE['agcm_invitation'] = $payload . '.' . $signature;
}
function agcm_clear_cookie()
{
    $path = wp_parse_url(home_url('/usces-member/'), PHP_URL_PATH);
    $path = is_string($path) && $path !== '' ? trailingslashit($path) : '/';
    setcookie('agcm_invitation', '', array(
        'expires' => time() - 3600,
        'path' => $path,
        'secure' => is_ssl(),
        'httponly' => true,
        'samesite' => 'Lax'
    ));
    unset($_COOKIE['agcm_invitation']);
}
function agcm_invited_company()
{
    $value = $_COOKIE['agcm_invitation'] ?? '';
    if (!is_string($value) || !preg_match('/^(\d+)\.(\d+)\.([a-f0-9]{64})$/D', $value, $m)) {
        return 0;
    }
    $id = (int)$m[1];
    $expires = (int)$m[2];
    if ($expires < time() || !agcm_enabled($id)) {
        return 0;
    }
    $token = (string)get_post_meta($id, '_agcm_token', true);
    $expected = hash_hmac('sha256', $m[1] . '.' . $m[2] . '.' . $token, wp_salt('auth'));
    return hash_equals($expected, $m[3]) ? $id : 0;
}

/* Login is public. A new Welcart member can only register through a live enterprise invitation. */
add_action('init', function () {
    if (is_admin() || wp_doing_ajax() || (defined('REST_REQUEST') && REST_REQUEST)) {
        return;
    }
    $method = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));
    $on_member = agcm_is_member_path();
    if ($on_member && $method === 'GET' && isset($_GET['ag_invite'])) {
        $raw = wp_unslash($_GET['ag_invite']);
        $id = is_string($raw) ? agcm_find_invite($raw) : 0;
        nocache_headers();
        if (!$id) {
            agcm_clear_cookie();
            wp_safe_redirect(home_url('/usces-member/'), 302);
            exit;
        }
        agcm_issue_cookie($id);
        wp_safe_redirect(add_query_arg('usces_page', 'newmember', home_url('/usces-member/')), 302);
        exit;
    }
    $mode = (isset($_POST['member_regmode']) && is_string($_POST['member_regmode'])) ? sanitize_key(wp_unslash($_POST['member_regmode'])) : '';
    $page = (isset($_GET['usces_page']) && is_string($_GET['usces_page'])) ? sanitize_key(wp_unslash($_GET['usces_page'])) : '';
    $new_member_post = $method === 'POST' && (
        isset($_POST['regmember']) || in_array($mode, array('newmemberform', 'newmemberfromcart'), true)
    );
    // Registration during checkout isn't permitted; it needs a separate signup URL first.
    if ($new_member_post && (!$on_member || $mode === 'newmemberfromcart')) {
        wp_die('会員登録は企業専用URLまたは固定URL会員の登録URLから行ってください。', '新規登録の制限', array('response' => 403));
    }
    if (
        $on_member && ($page === 'newmember' || $new_member_post)
        && !agcm_invited_company() && !agcm_fixed_invited()
    ) {
        nocache_headers();
        if ($method === 'GET' || $method === 'HEAD') {
            wp_safe_redirect(home_url('/usces-member/'), 302);
            exit;
        }
        wp_die(
            '登録URLが無効か、登録画面の有効時間が切れています。登録用URLから開き直してください。',
            '新規登録の制限',
            array('response' => 403)
        );
    }
}, -80);

// Defense in depth: prevent alternate Welcart registration paths when possible.
add_filter('usces_filter_member_check', function ($message) {
    $mode = isset($_POST['member_regmode']) && is_string($_POST['member_regmode']) ? sanitize_key(wp_unslash($_POST['member_regmode'])) : '';
    if ($mode === 'newmemberform' && (!agcm_is_member_path() || (!agcm_invited_company() && !agcm_fixed_invited()))) {
        return $message . '専用の会員登録URLから登録してください。<br>';
    }
    return $message;
}, 999);
add_filter('usces_filter_member_check_fromcart', function ($message) {
    $mode = isset($_POST['member_regmode']) && is_string($_POST['member_regmode']) ? sanitize_key(wp_unslash($_POST['member_regmode'])) : '';
    if ($mode === 'newmemberfromcart') {
        return $message . '専用の会員登録URLから登録してください。<br>';
    }
    return $message;
}, 999);

add_action('usces_action_member_registered', function ($data, $member_id) {
    $id = agcm_invited_company();
    if (!$id || !is_numeric($member_id)) {
        return;
    }
    global $usces;
    if (!isset($usces) || !method_exists($usces, 'set_member_meta_value')) {
        return;
    }
    // Company is taken ONLY from the signed cookie; the browser form cannot pick its own company.
    $usces->set_member_meta_value('agcm_company_id', (string)$id, (int)$member_id);
    $usces->set_member_meta_value('agcm_member_type', 'company', (int)$member_id);
    // Preserve the existing theme's "csmb_company" field used by invoices/notifications.
    $usces->set_member_meta_value('csmb_company', get_the_title($id), (int)$member_id);
    agcm_clear_cookie();
}, 20, 2);

/* Member-company relationship stored in Welcart's member_meta table (not WP usermeta). */
function agcm_member_company_id($member_id)
{
    // Read the same Welcart member-meta table used by the company staff list.
    // This avoids inconsistent results between the admin listing and the storefront.
    $member_id = (int)$member_id;
    if ($member_id <= 0) {
        return 0;
    }
    global $wpdb;
    $table = $wpdb->prefix . 'usces_member_meta';
    $result = $wpdb->get_var($wpdb->prepare(
        "SELECT meta_value FROM {$table} WHERE member_id = %d AND meta_key = %s LIMIT 1",
        $member_id,
        'agcm_company_id'
    ));
    $id = is_scalar($result) ? (int)$result : 0;
    if (!$id) {
        // Compatibility with stores that have a different member-meta table layout.
        global $usces;
        if ($usces && method_exists($usces, 'get_member_meta_value')) {
            $id = (int)$usces->get_member_meta_value('agcm_company_id', $member_id);
        }
    }
    return agcm_company($id) ? $id : 0;
}
function agcm_current_member_id()
{
    // WP administrator sessions and Welcart customer sessions are different.
    $logged_in = (function_exists('wel_is_logged_in') && wel_is_logged_in()) ||
        (function_exists('usces_is_login') && usces_is_login());
    if (!$logged_in) {
        return 0;
    }
    if (function_exists('wel_logged_in_id')) {
        return (int)wel_logged_in_id();
    }
    return isset($_SESSION['usces_member']['ID']) ? (int)$_SESSION['usces_member']['ID'] : 0;
}
function agcm_current_company_id()
{
    $member_id = agcm_current_member_id();
    return $member_id ? agcm_member_company_id($member_id) : 0;
}
/**
 * Storefront access policy (v1.1.4).
 *
 * Guests may browse regular item archives, item details and Welcart checkout.
 * This plugin must NEVER force login for ordinary catalogue/cart requests.
 * Company/QR and fixed URL member registration still require signed invitations.
 * Member price calculation requires a verified Welcart session and member type;
 * guests always fall back to Welcart's original SKU prices.
 *
 * First-order trial items are NOT public purchases. Trial access and add-to-cart
 * checks remain enforced by ag-FLOLIST-quantity-cart/includes/trial-gate.php.
 *
 * Do not use WordPress admin login as an indication of a Welcart member.
 */
function agcm_shop_login_url()
{
    return home_url('/usces-member/');
}

/** A product may belong to the item category OR one of its descendants. */
function agcm_is_product_category_id($term_id)
{
    $item = get_category_by_slug('item');
    if (!$item || !$term_id) {
        return false;
    }
    $term_id = (int)$term_id;
    if ($term_id === (int)$item->term_id) {
        return true;
    }
    $ancestors = get_ancestors($term_id, 'category', 'taxonomy');
    return is_array($ancestors) && in_array((int)$item->term_id, array_map('intval', $ancestors), true);
}

function agcm_is_product_post($post_id)
{
    if (!$post_id || !function_exists('wp_get_post_categories')) {
        return false;
    }
    $categories = wp_get_post_categories((int)$post_id);
    if (!is_array($categories) || is_wp_error($categories)) {
        return false;
    }
    foreach ($categories as $id) {
        if (agcm_is_product_category_id($id)) {
            return true;
        }
    }
    return false;
}

/**
 * Default: no member login requirement for browsing or checkout.
 * Optional site-specific exceptions can be introduced by a separate, deliberate
 * plugin through the filter. The special trial product rules are independent.
 */
function agcm_shop_requires_login()
{
    if (is_page('usces-member')) {
        return false;
    }
    return (bool) apply_filters('agcm_shop_requires_login', false);
}

add_action('template_redirect', function () {
    if (is_admin() || wp_doing_ajax() || (defined('REST_REQUEST') && REST_REQUEST)) {
        return;
    }
    // No redirect for ordinary products, categories or /usces-cart/.
    if (!agcm_shop_requires_login() || agcm_current_member_id() > 0) {
        return;
    }

    // Only an explicitly configured protected route uses member login.
    $method = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));
    nocache_headers();
    wp_safe_redirect(agcm_shop_login_url(), $method === 'POST' ? 303 : 302);
    exit;
}, 0);

function agcm_products()
{
    // Welcart's ordinary products are WP posts in the 'item' category tree.
    $term = get_category_by_slug('item');
    if (!$term) {
        return array();
    }
    $posts = get_posts(array(
        'post_type' => 'post',
        'post_status' => 'publish',
        'numberposts' => -1,
        'cat' => (int)$term->term_id,
        'orderby' => 'title',
        'order' => 'ASC'
    ));
    $out = array();
    foreach ($posts as $p) {
        if (!function_exists('wel_get_skus')) {
            continue;
        }
        $skus = wel_get_skus($p->ID);
        if (!is_array($skus)) {
            continue;
        }
        foreach ($skus as $row) {
            $sku = isset($row['code']) ? (string)$row['code'] : (isset($row['sku']) ? (string)$row['sku'] : '');
            if ($sku === '') {
                continue;
            }
            $out[] = array(
                'post_id' => (int)$p->ID,
                'product' => get_the_title($p->ID),
                'sku' => $sku,
                'price' => isset($row['price']) ? (float)$row['price'] : 0
            );
        }
    }
    return $out;
}
function agcm_rate_key($post_id, $sku)
{
    return (int)$post_id . ':' . (string)$sku;
}
function agcm_rate($company_id, $post_id, $sku)
{
    // MBFF is a uniform 70% price for every verified QR/company member.
    // Existing per-SKU settings for these products are retained in storage
    // for rollback, but are not applied while this rule is active.
    if (function_exists('agcm_is_mbff_product') && agcm_is_mbff_product((int)$post_id)) {
        return 70.0;
    }
    $rates = get_post_meta($company_id, '_agcm_rates', true);
    if (!is_array($rates)) {
        return 100.0;
    }
    $key = agcm_rate_key($post_id, $sku);
    return isset($rates[$key]) && is_numeric($rates[$key]) ? max(0.0, min(100.0, (float)$rates[$key])) : 100.0;
}
function agcm_price($company_id, $post_id, $sku, $standard)
{
    if (!$company_id || !agcm_company($company_id)) {
        return (float)$standard;
    }
    return floor(max(0, (float)$standard) * agcm_rate($company_id, $post_id, $sku) / 100.0);
}
/** Resolve the original registered SKU selling price, never a cart-discounted price.
 * A failed SKU lookup must return null, not 0; otherwise a fixed member can
 * receive a free cart line after a legacy cart override.
 */
function agcm_sku_base($post_id, $sku)
{
    $post_id = (int)$post_id;
    if ($post_id < 1 || !is_string($sku) || $sku === '') {
        return null;
    }
    $codes = array_unique(array(rawurldecode($sku), $sku));
    $found_zero = false;
    global $usces;
    foreach ($codes as $code) {
        $candidates = array();
        if (function_exists('wel_get_sku')) {
            $candidates[] = wel_get_sku($post_id, $code);
        }
        if (function_exists('wel_get_skus')) {
            $all = wel_get_skus($post_id, 'code');
            $candidates[] = is_array($all) ? ($all[$code] ?? null) : null;
        }
        if ($usces && method_exists($usces, 'get_skus')) {
            $all = $usces->get_skus($post_id, 'code');
            $candidates[] = is_array($all) ? ($all[$code] ?? null) : null;
        }
        foreach ($candidates as $row) {
            if (!is_array($row) || !isset($row['price']) || !is_numeric($row['price'])) {
                continue;
            }
            $amount = (float)$row['price'];
            if (!is_finite($amount) || $amount < 0) {
                continue;
            }
            if ($amount > 0) {
                return $amount;
            }
            $found_zero = true;
        }
    }
    return $found_zero ? 0.0 : null;
}
function agcm_serial_item($serial)
{
    if (!is_string($serial) || strlen($serial) > 10000) {
        return null;
    }
    $data = @unserialize($serial, array('allowed_classes' => false));
    if (!is_array($data) || count($data) !== 1) {
        return null;
    }
    $post_id = array_key_first($data);
    if (!is_numeric($post_id) || !is_array($data[$post_id]) || count($data[$post_id]) !== 1) {
        return null;
    }
    $sku = array_key_first($data[$post_id]);
    return array((int)$post_id, rawurldecode((string)$sku));
}

/* Real cart price: Welcart uses this filter during cart insertion/update. */
add_filter('usces_filter_realprice', function ($price, $serial) {
    $item = agcm_serial_item($serial);
    if (!$item) {
        return $price;
    }
    $base = agcm_sku_base($item[0], $item[1]);
    if ($base === null || ($base <= 0 && is_numeric($price) && (float)$price > 0)) {
        return $price;
    }
    $effective = agcm_member_price($item[0], $item[1], $base);
    return $effective === null ? $price : $effective;
}, 25, 2);

/* Ensure the transactional price isn't overwritten by other discount hooks.
 * These are server-side calculated prices, never client-submitted skuPrice data.
 */
function agcm_cart_override_price($price, $serial)
{
    $item = agcm_serial_item($serial);
    if (!$item) {
        return $price;
    }
    $base = agcm_sku_base($item[0], $item[1]);
    if ($base === null || ($base <= 0 && is_numeric($price) && (float)$price > 0)) {
        return $price;
    }
    $effective = agcm_member_price($item[0], $item[1], $base);
    return $effective === null ? $price : $effective;
}
add_filter('usces_filter_inCart_price', 'agcm_cart_override_price', 999, 2);
add_filter('usces_filter_upCart_price', 'agcm_cart_override_price', 999, 2);

/* Compatibility hook used only by the provided previously customized
 * /usc-e-shop/classes/cart.class.php. Unmodified Welcart keeps its native
 * session price and needs no additional core modification.
 */
add_filter('agcm_cart_company_price', function ($default, $post_id, $sku) {
    $base = agcm_sku_base($post_id, $sku);
    if ($base === null) {
        return $default;
    }
    $effective = agcm_member_price($post_id, $sku, $base);
    return $effective === null ? $default : $effective;
}, 10, 3);

/* Reconcile an existing cart on login, reload, or company rate change.
 * Works with Welcart's cart session, not only its HTML price display.
 * Note: GP/campaign extensions still require acceptance testing on staging.
 */
function agcm_sync_cart()
{
    global $usces;
    if (
        is_admin() || !$usces || !isset($usces->cart)
        || !method_exists($usces->cart, 'get_cart')
    ) {
        return;
    }
    if (
        session_status() !== PHP_SESSION_ACTIVE || empty($_SESSION['usces_cart'])
        || !is_array($_SESSION['usces_cart'])
    ) {
        return;
    }
    $type = agcm_current_member_type();
    $had_discount = !empty($_SESSION['agcm_last_member_type'])
        || !empty($_SESSION['agcm_last_company_id']);
    if ($type === '' && !$had_discount) {
        return;
    }
    // Using raw session keys avoids calling get_cart() recursively from cart price hooks.
    foreach ($_SESSION['usces_cart'] as $serial => &$entry) {
        $item = agcm_serial_item($serial);
        if (!$item || !is_array($entry)) {
            continue;
        }
        $base = agcm_sku_base($item[0], $item[1]);
        if ($base === null || ($base <= 0 && !empty($entry['price']))) {
            continue;
        }
        $price = agcm_member_price($item[0], $item[1], $base);
        $effective = $price === null ? $base : $price;
        $entry['price'] = $effective;
        $entry['unit_price'] = $effective;
    }
    unset($entry);
    if ($type !== '') {
        $_SESSION['agcm_last_member_type'] = $type;
    } else {
        unset($_SESSION['agcm_last_member_type']);
    }
    unset($_SESSION['agcm_last_company_id']);
}
add_action('wp_loaded', 'agcm_sync_cart', 99);
add_action('template_redirect', 'agcm_sync_cart', 1);
add_action('usces_action_after_inCart', 'agcm_sync_cart', 10);
add_action('usces_action_after_upCart', 'agcm_sync_cart', 10);

/* Welcart passes ($formatted_price, $sku_price, $out) to this filter, NOT $post_id.
 * Always resolve product ID from the active WordPress post. Former v1.0.1
 * interpreted the original SKU price (15000) as a post ID, so no company
 * price was displayed. A company without a verified member login is ignored.
 */
add_filter('usces_filter_the_item_price_cr', function ($formatted, $sku_price = null, $out = '') {
    if (agcm_current_member_type() === '') {
        return $formatted;
    }
    global $post, $usces, $custom_price_global;
    $post_id = isset($post->ID) ? (int)$post->ID : 0;
    $sku = isset($usces->itemsku['code']) ? (string)$usces->itemsku['code'] : '';
    if (!$post_id || $sku === '') {
        return $formatted;
    }
    $base = agcm_sku_base($post_id, $sku);
    if ($base === null) {
        return $formatted;
    }
    $price = agcm_member_price($post_id, $sku, $base);
    if ($price === null) {
        return $formatted;
    }
    if (is_array($custom_price_global)) {
        $custom_price_global['discounted_price'] = $price;
    }
    return function_exists('usces_crform') ? usces_crform($price, true, false, 'return') : '¥' . number_format($price);
}, 999, 3);

/* First SKU price shown in product lists; independent of individual member logic. */
add_filter('usces_filter_the_first_price', function ($price, $post_arg = null, $skus = null, $out = '') {
    if (agcm_current_member_type() === '') {
        return $price;
    }
    global $post;
    // Welcart versions differ in filter argument shapes. Never treat a price
    // as a post ID; resolve the real product post and its first SKU.
    $post_id = is_object($post_arg) && isset($post_arg->ID) ? (int)$post_arg->ID
        : ((is_numeric($post_arg) && (int)$post_arg > 0) ? (int)$post_arg
            : (isset($post->ID) ? (int)$post->ID : 0));
    if (!$post_id || !function_exists('wel_get_skus')) {
        return $price;
    }
    $all_skus = wel_get_skus($post_id);
    if (!is_array($all_skus) || !$all_skus) {
        return $price;
    }
    $first = reset($all_skus);
    $sku = is_array($first) ? (string)($first['code'] ?? $first['sku'] ?? '') : '';
    if ($sku === '') {
        return $price;
    }
    $base = agcm_sku_base($post_id, $sku);
    if ($base === null) {
        return $price;
    }
    $value = agcm_member_price($post_id, $sku, $base);
    if ($value === null) {
        return $price;
    }
    return ($out === 'return') ? $value : number_format($value);
}, 99, 4);

add_action('wp_enqueue_scripts', function () {
    if (is_page('usces-member')) {
        wp_enqueue_style('agcm-register', AGCM_URL . 'assets/register.css', array(), AGCM_VERSION);
    }
}, 50);
add_filter('the_content', function ($html) {
    if (is_admin() || !is_page('usces-member')) {
        return $html;
    }
    $page = (isset($_GET['usces_page']) && is_string($_GET['usces_page'])) ? sanitize_key(wp_unslash($_GET['usces_page'])) : '';
    $id = $page === 'newmember' ? agcm_invited_company() : 0;
    if ($id) {
        return '<div class="agcm-invite-notice"><strong>' . esc_html(get_the_title($id)) . ' 様専用 新規会員登録</strong><span>この画面から登録すると、所属企業の価格設定が自動適用されます。</span></div>' . $html;
    }
    if ($page === 'newmember' && agcm_fixed_invited()) {
        return '<div class="agcm-invite-notice"><strong>固定URL会員 新規登録</strong><span>TANSA商品は数量別掛け率、MBFF（ID 5・15・16）は通常売価の70％が適用されます。</span></div>' . $html;
    }
    return $html;
}, 99);

/* Temporary read-only diagnostic on a product page: ?agcm_price_debug=1
 * Available only to a logged-in WordPress administrator.
 */
add_action('wp_footer', function () {
    if (
        !current_user_can('manage_options') || !isset($_GET['agcm_price_debug']) ||
        (string)$_GET['agcm_price_debug'] !== '1'
    ) {
        return;
    }
    global $post, $usces;
    $member_id = agcm_current_member_id();
    $company = $member_id ? agcm_member_company_id($member_id) : 0;
    $post_id = isset($post->ID) ? (int)$post->ID : 0;
    $sku = isset($usces->itemsku['code']) ? (string)$usces->itemsku['code'] : '';
    if ($sku === '' && $post_id && function_exists('wel_get_skus')) {
        $rows = wel_get_skus($post_id);
        $first = is_array($rows) && $rows ? reset($rows) : array();
        $sku = is_array($first) ? (string)($first['code'] ?? $first['sku'] ?? '') : '';
    }
    $base = ($post_id && $sku !== '') ? agcm_sku_base($post_id, $sku) : null;
    $rate = ($company && $base !== null) ? agcm_rate($company, $post_id, $sku) : null;
    $member_type = function_exists('agcm_current_member_type') ? agcm_current_member_type() : '';
    $fixed_quantity = function_exists('agcm_tansa_cart_quantity') ? agcm_tansa_cart_quantity() : 0;
    $expected = ($base !== null && function_exists('agcm_member_price') && $sku !== '')
        ? agcm_member_price($post_id, $sku, $base) : null;
    $session_price = null;
    if (!empty($_SESSION['usces_cart']) && is_array($_SESSION['usces_cart'])) {
        foreach ($_SESSION['usces_cart'] as $serial => $entry) {
            $item = agcm_serial_item((string)$serial);
            if ($item && $item[0] === $post_id && $item[1] === $sku) {
                $session_price = is_array($entry) && isset($entry['price']) ? $entry['price'] : null;
                break;
            }
        }
    }
    $details = array(
        '会員種別' => $member_type === 'company' ? '①企業別QR' : ($member_type === 'fixed' ? '②固定URL' : '未分類'),
        'TANSA合計数量' => (string)$fixed_quantity,
        '計算後単価' => $expected === null ? '未取得' : (string)$expected,
        'カートセッション単価' => $session_price === null ? '未取得' : (string)$session_price,
        'Welcart会員ログイン' => $member_id > 0 ? 'はい' : 'いいえ',
        'Welcart会員ID' => (string)$member_id,
        '企業ID' => (string)$company,
        '現在の企業名' => $company ? get_the_title($company) : '未取得',
        '商品投稿ID' => (string)$post_id,
        'SKU' => $sku !== '' ? $sku : '未取得',
        '通常価格' => $base === null ? '未取得' : (string)$base,
        '掛け率' => $rate === null ? '未取得' : $rate . '%',
        '期待販売価格' => $rate === null ? '未取得' : (string)agcm_price($company, $post_id, $sku, $base),
        '会員用価格フィルタ' => has_filter('usces_filter_the_item_price_cr') ? '登録あり' : '未登録',
        'セッション状態' => session_status() === PHP_SESSION_ACTIVE ? '有効' : '無効',
    );
    echo '<aside style="position:relative;z-index:9999;max-width:850px;margin:30px auto;padding:18px;background:#fffbea;border:2px solid #d68b00;color:#262626;font:14px/1.8 sans-serif;">';
    echo '<strong>AG FLOLIST 企業価格診断（管理者のみ表示）</strong><table style="width:100%;border-collapse:collapse;margin-top:10px">';
    foreach ($details as $label => $value) {
        echo '<tr><th style="text-align:left;width:40%;border-bottom:1px solid #ddd;padding:4px">' . esc_html($label) . '</th><td style="border-bottom:1px solid #ddd;padding:4px">' . esc_html($value) . '</td></tr>';
    }
    echo '</table></aside>';
}, 1000);

require_once AGCM_DIR . 'dual-membership.php';
require_once AGCM_DIR . 'monthly-invoice.php';
require_once AGCM_DIR . 'admin.php';
require_once AGCM_DIR . 'member-directory.php';
