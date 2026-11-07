<?php

/**
 * AG FLOLIST - First order trial rules (only fixed URL Welcart members).
 * Rules apply to all trial-category products; never rely only on the page template.
 */
if (!defined('ABSPATH')) {
    exit;
}

/** Previous category migration IDs: trial(9), first-limited A(11), B(12), C(13).
 * Configure only on the migrated shop. Descendants are also included.
 */
function agfl_trial_category_ids()
{
    $ids = apply_filters('agfl_trial_category_ids', array(9, 11, 12, 13));
    $result = array();
    foreach ((array)$ids as $id) {
        $term = get_term((int)$id, 'category');
        if ($term instanceof WP_Term && !is_wp_error($term)) {
            $result[] = (int)$term->term_id;
        }
    }
    return array_values(array_unique($result));
}

function agfl_trial_is_product($post_id)
{
    $post_id = (int)$post_id;
    if (!$post_id) {
        return false;
    }
    $categories = wp_get_post_categories($post_id);
    if (is_wp_error($categories) || !is_array($categories)) {
        return false;
    }
    $trial_ids = agfl_trial_category_ids();
    foreach ($categories as $cat_id) {
        if (in_array((int)$cat_id, $trial_ids, true)) {
            return true;
        }
        foreach ($trial_ids as $base_id) {
            if (term_is_ancestor_of($base_id, (int)$cat_id, 'category')) {
                return true;
            }
        }
    }
    return false;
}

/** Has this Welcart member EVER placed a non-cancelled, non-estimate trial order? */
function agfl_trial_member_has_purchased($member_id)
{
    static $cache = array();
    $member_id = (int)$member_id;
    if ($member_id < 1) {
        return false;
    }
    if (array_key_exists($member_id, $cache)) {
        return $cache[$member_id];
    }
    // Fail closed if history cannot be verified (don't give a second trial by accident).
    $cache[$member_id] = true;
    global $usces;
    if (!$usces || !method_exists($usces, 'get_member_history')) {
        return true;
    }
    $history = $usces->get_member_history($member_id, false);
    if (!is_array($history)) {
        return true;
    }
    foreach ($history as $order) {
        if (!is_array($order)) {
            continue;
        }
        $status = strtolower((string)($order['order_status'] ?? ''));
        if (strpos($status, 'cancel') !== false || strpos($status, 'estimate') !== false || strpos($status, 'trash') !== false) {
            continue;
        }
        foreach ((array)($order['cart'] ?? array()) as $row) {
            $pid = is_array($row) ? (int)($row['post_id'] ?? 0) : 0;
            if ($pid > 0 && agfl_trial_is_product($pid)) {
                return true;
            }
        }
    }
    $cache[$member_id] = false;
    return false;
}

function agfl_trial_can_purchase()
{
    if (!function_exists('agcm_current_member_id') || !function_exists('agcm_current_member_type')) {
        return false;
    }
    $id = (int)agcm_current_member_id();
    return $id > 0 && agcm_current_member_type() === 'fixed' && !agfl_trial_member_has_purchased($id);
}

/** If the member came from trial while logged out, remember destination in storefront session. */
add_action('template_redirect', function () {
    if (is_admin() || wp_doing_ajax()) {
        return;
    }
    $trial_page = is_page('trial');
    $trial_product = is_singular('post') && agfl_trial_is_product(get_queried_object_id());
    $trial_archive = is_category() && (
        in_array((int)get_queried_object_id(), agfl_trial_category_ids(), true) ||
        (function () {
            foreach (agfl_trial_category_ids() as $cid) {
                if (term_is_ancestor_of($cid, (int)get_queried_object_id(), 'category')) {
                    return true;
                }
            }
            return false;
        })()
    );
    if (!$trial_page && !$trial_product && !$trial_archive) {
        return;
    }
    if (agfl_trial_can_purchase()) {
        if ($trial_archive) {
            wp_safe_redirect(home_url('/trial/'), 302);
            exit;
        }
        return;
    }
    // A logged-in company member or an already-used fixed member goes to normal products.
    $current_id = function_exists('agcm_current_member_id') ? (int)agcm_current_member_id() : 0;
    if ($current_id > 0) {
        wp_safe_redirect(home_url('/category/item/'), 302);
        exit;
    }
    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION['agfl_trial_login_destination'] = true;
    }
    $login = function_exists('agcm_shop_login_url') ? agcm_shop_login_url() : home_url('/usces-member/');
    wp_safe_redirect($login, 302);
    exit;
}, 3);

/** Welcart login is separate from WordPress login. Only redirect a real login operation. */
add_action('usces_action_after_login', function () {
    $was_trial_request = !empty($_SESSION['agfl_trial_login_destination']);
    if ($was_trial_request) {
        unset($_SESSION['agfl_trial_login_destination']);
    }
    $is_login_post = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? '')) === 'POST'
        && function_exists('agcm_is_member_path') && agcm_is_member_path();
    if (!$was_trial_request && !$is_login_post) {
        return;
    }
    // New fixed members go straight to the trial page once; members who have already
    // placed an eligible trial order use Welcart's usual login destination.
    if (agfl_trial_can_purchase()) {
        wp_safe_redirect(home_url('/trial/'), 303);
        exit;
    }
    // An already-used fixed member goes to the standard catalogue on next login.
    if ($was_trial_request || ($is_login_post && function_exists('agcm_current_member_type')
        && agcm_current_member_type() === 'fixed')) {
        wp_safe_redirect(home_url('/category/item/'), 303);
        exit;
    }
}, 30);

/** Native Welcart add-to-cart validation. */
add_filter('usces_filter_incart_check', function ($messages, $post_id, $sku) {
    if (!agfl_trial_is_product((int)$post_id) || agfl_trial_can_purchase()) {
        return $messages;
    }
    if (!is_array($messages)) {
        $messages = array();
    }
    if (!isset($messages[$post_id]) || !is_array($messages[$post_id])) {
        $messages[$post_id] = array();
    }
    $messages[$post_id][$sku] = '初回限定商品は②固定URL会員の初回注文に限ります。';
    return $messages;
}, 99, 3);

/** Prevent checkout when a member changed or previously bought a trial item. */
add_filter('usces_filter_cart_check', function ($message) {
    foreach ((array)($_SESSION['usces_cart'] ?? array()) as $serial => $line) {
        $item = function_exists('agcm_serial_item') ? agcm_serial_item((string)$serial) : null;
        if ($item && agfl_trial_is_product((int)$item[0]) && !agfl_trial_can_purchase()) {
            $warn = '初回限定トライアル商品を購入できません。通常の商品一覧からご注文ください。';
            return (is_string($message) ? $message : '') . $warn;
        }
    }
    return $message;
}, 99);
