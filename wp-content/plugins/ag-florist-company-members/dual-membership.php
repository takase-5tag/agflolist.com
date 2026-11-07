<?php
/** Fixed registration, Welcart membership type, TANSA tier and MBFF fixed pricing. */
if (!defined('ABSPATH')) { exit; }

/** Shared/static URL. This is an OPEN signup URL by design: distribute only to intended customers. */
function agcm_fixed_registration_url() {
    return add_query_arg('ag_fixed', 'register', home_url('/usces-member/'));
}
function agcm_fixed_cookie_path() {
    $path = wp_parse_url(home_url('/usces-member/'), PHP_URL_PATH);
    return is_string($path) && $path !== '' ? trailingslashit($path) : '/';
}
function agcm_clear_fixed_cookie() {
    setcookie('agcm_fixed_registration', '', array('expires' => time()-3600,
        'path' => agcm_fixed_cookie_path(), 'secure' => is_ssl(), 'httponly' => true, 'samesite' => 'Lax'));
    unset($_COOKIE['agcm_fixed_registration']);
}
function agcm_start_fixed_registration() {
    $expires = time()+7200;
    $payload = 'fixed.' . $expires;
    $sig = hash_hmac('sha256', $payload, wp_salt('auth'));
    agcm_clear_cookie(); // A user cannot register simultaneously under both schemes.
    $value = $payload . '.' . $sig;
    setcookie('agcm_fixed_registration', $value, array('expires' => $expires,
        'path' => agcm_fixed_cookie_path(), 'secure' => is_ssl(), 'httponly' => true, 'samesite' => 'Lax'));
    $_COOKIE['agcm_fixed_registration'] = $value;
}
function agcm_fixed_invited() {
    $value = $_COOKIE['agcm_fixed_registration'] ?? '';
    if (!is_string($value) || !preg_match('/^fixed\.(\d+)\.([a-f0-9]{64})$/D', $value, $matches)) { return false; }
    if ((int)$matches[1] < time()) { return false; }
    $expected = hash_hmac('sha256', 'fixed.'.$matches[1], wp_salt('auth'));
    return hash_equals($expected, $matches[2]);
}
// Execute just before the company-only registration guard, on the same Welcart registration path.
add_action('init', function () {
    if (is_admin() || wp_doing_ajax() || !agcm_is_member_path()) { return; }
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') { return; }
    if (!isset($_GET['ag_fixed']) || !is_string($_GET['ag_fixed']) || wp_unslash($_GET['ag_fixed']) !== 'register') { return; }
    agcm_start_fixed_registration();
    nocache_headers();
    wp_safe_redirect(add_query_arg('usces_page','newmember', home_url('/usces-member/')), 302);
    exit;
}, -90);

// The company registration handler is in the existing plugin; this second handler is disjoint.
add_action('usces_action_member_registered', function ($data, $member_id) {
    if (agcm_invited_company() || !agcm_fixed_invited() || !is_numeric($member_id)) { return; }
    global $usces;
    if (!$usces || !method_exists($usces, 'set_member_meta_value')) { return; }
    $usces->set_member_meta_value('agcm_member_type', 'fixed', (int)$member_id);
    $usces->set_member_meta_value('agcm_company_id', '', (int)$member_id);
    agcm_clear_fixed_cookie();
}, 15, 2);

/** Unknown/legacy members do not automatically become fixed members. */
function agcm_member_type($member_id) {
    $member_id = (int)$member_id;
    if ($member_id < 1) { return ''; }
    $company_id = agcm_member_company_id($member_id);
    if ($company_id) { return 'company'; }
    global $usces;
    if (!$usces || !method_exists($usces, 'get_member_meta_value')) { return ''; }
    $type = (string)$usces->get_member_meta_value('agcm_member_type', $member_id);
    return $type === 'fixed' ? 'fixed' : '';
}
function agcm_current_member_type() {
    $id = agcm_current_member_id();
    return $id ? agcm_member_type($id) : '';
}

function agcm_is_tansa_product($post_id) {
    $post_id=(int)$post_id;
    if ($post_id<1) { return false; }
    $assigned=wp_get_post_categories($post_id);
    if (!is_array($assigned) || is_wp_error($assigned)) { return false; }
    $assigned=array_map('intval',$assigned);
    // Trial categories are a FIRST ORDER offer. A trial item must never
    // additionally enter the quantity-tier price calculation, even if it
    // was also tagged with the normal TANSA category.
    if (function_exists('agfl_trial_is_product') && agfl_trial_is_product($post_id)) { return false; }
    foreach ($assigned as $term_id) {
        if (in_array($term_id,array(9,11,12,13),true)) { return false; }
    }
    // The migrated site uses category ID 3 (name: 通常) for TANSA lineup.
    // Keep compatibility with the previous tansa_cate slug as well.
    $target_ids=array(3);
    $legacy=get_category_by_slug('tansa_cate');
    if ($legacy instanceof WP_Term && !is_wp_error($legacy)) { $target_ids[]=(int)$legacy->term_id; }
    $target_ids=array_unique($target_ids);
    foreach ($assigned as $term_id) {
        foreach ($target_ids as $target) {
            if ($term_id===(int)$target) { return true; }
            $ancestors=get_ancestors($term_id,'category','taxonomy');
            if (is_array($ancestors) && in_array((int)$target,array_map('intval',$ancestors),true)) { return true; }
        }
    }
    return false;
}
/**
 * MBFF: only WordPress category IDs 5, 15 and 16, including descendants.
 * Trial products keep their existing separate promotional price rules.
 * This tests product taxonomy, never the URL or the visible category label.
 */
function agcm_is_mbff_product($post_id) {
    $post_id = (int)$post_id;
    if ($post_id < 1) { return false; }
    if (function_exists('agfl_trial_is_product') && agfl_trial_is_product($post_id)) { return false; }
    $assigned = wp_get_post_categories($post_id);
    if (!is_array($assigned) || is_wp_error($assigned)) { return false; }
    $targets = array(5, 15, 16);
    foreach ($assigned as $term_id) {
        $term_id = (int)$term_id;
        if (in_array($term_id, $targets, true)) { return true; }
        $ancestors = get_ancestors($term_id, 'category', 'taxonomy');
        if (is_array($ancestors) && array_intersect($targets, array_map('intval', $ancestors))) { return true; }
    }
    return false;
}

/** Count TANSA units of all SKUs/products; never count MBFF or other product categories. */
function agcm_tansa_cart_quantity() {
    $total = 0;
    if (empty($_SESSION['usces_cart']) || !is_array($_SESSION['usces_cart'])) { return 0; }
    foreach ($_SESSION['usces_cart'] as $serial => $row) {
        $item = agcm_serial_item((string)$serial);
        if (!$item || !agcm_is_tansa_product($item[0])) { continue; }
        $total += max(0, (int)($row['quant'] ?? 0));
    }
    return $total;
}
/** 1-5 -> 65%, 6-11 -> 55%, 12+ -> 50%. Zero cart defaults to first tier for preview. */
function agcm_fixed_tansa_rate($quantity) {
    return $quantity >= 12 ? 50 : ($quantity >= 6 ? 55 : 65);
}
/** Returns null for unclassified membership; never reduce an already reduced unit price again. */
function agcm_member_price($post_id, $sku, $standard) {
    $base = max(0, (float)$standard);
    $kind = agcm_current_member_type();
    // Compute once from the original Welcart SKU selling price. Never compound
    // a previous QR/company rate, fixed TANSA tier, or cart discount.
    if (($kind === 'company' || $kind === 'fixed') && agcm_is_mbff_product((int)$post_id)) {
        return floor($base * 70 / 100);
    }
    if ($kind === 'company') {
        return agcm_price(agcm_current_company_id(), (int)$post_id, (string)$sku, $base);
    }
    if ($kind === 'fixed') {
        if (!agcm_is_tansa_product((int)$post_id)) { return $base; }
        return floor($base * agcm_fixed_tansa_rate(agcm_tansa_cart_quantity()) / 100);
    }
    return null;
}

// Snapshot a member's classification at order time so reports are stable if they later change companies.
add_action('usces_post_reg_orderdata', function ($order_id) {
    $type = agcm_current_member_type();
    global $usces;
    if (!$order_id || $type === '' || !$usces || !method_exists($usces, 'set_order_meta_value')) { return; }
    $usces->set_order_meta_value('agcm_member_type', $type, (int)$order_id);
    if ($type === 'company') {
        $usces->set_order_meta_value('agcm_company_id', agcm_current_company_id(), (int)$order_id);
    }
}, 10, 1);

add_action('admin_menu', function () {
    add_submenu_page('agcm-companies', '固定URL会員', '固定URL会員', AGCM_MANAGE_CAP,
        'agcm-fixed-members', 'agcm_fixed_member_admin');
}, 20);
function agcm_fixed_member_admin() {
    if (!current_user_can(AGCM_MANAGE_CAP)) { wp_die('操作権限がありません。'); }
    ?>
    <div class="wrap"><h1>固定URL会員登録</h1>
      <p>このURLから登録した固定会員は、TANSA数量別掛け率に加え、MBFF 3カテゴリー（ID 5・15・16）を一律70％で購入できます。</p>
      <label for="agcm-fixed-url"><strong>固定会員登録URL</strong></label><br>
      <input id="agcm-fixed-url" class="large-text code" type="text" readonly onclick="this.select()" value="<?php echo esc_attr(agcm_fixed_registration_url()); ?>">
      <p class="description">この登録URLを知っている人は登録できます。公開範囲にご注意ください。</p>
      <h2>TANSA商品のみ・合算数量別掛け率</h2>
      <table class="widefat striped" style="max-width:720px"><thead><tr><th>カート内TANSA個数</th><th>販売掛け率</th></tr></thead>
        <tbody><tr><td>1〜5個</td><td>65%（35%引き）</td></tr><tr><td>6〜11個</td><td>55%（45%引き）</td></tr><tr><td>12個以上</td><td>50%（50%引き）</td></tr></tbody></table>
      <p>MBFF（カテゴリーID 5・15・16）は通常売価の70％（数量を問わず固定）。TANSAは従来の数量段階別掛け率、その他は通常売価です。</p>
      <?php if (function_exists('agcm_render_member_directory')) : ?>
        <div class="agcm-fixed-roster">
          <?php agcm_render_member_directory('fixed', false); ?>
        </div>
      <?php endif; ?>
    </div>
    <?php
}
