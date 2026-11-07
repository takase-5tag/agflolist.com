<?php

/**
 * Plugin Name: AG FLOLIST - Welcart ログイン保持
 * Description: Welcart の「ログイン情報を記憶」を既定でオンにし、保存済み認証Cookieから通常ページでも会員セッションを復元します（最大30日、ログアウト時は解除）。
 * Version: 1.0.0
 * Requires PHP: 7.4
 * Author: AG FLOLIST custom development
 */
if (! defined('ABSPATH')) {
    exit;
}

// Finite lifetime, rather than an indefinite login on a customer or shared device.
if (! defined('AGFL_LOGIN_KEEP_DAYS')) {
    define('AGFL_LOGIN_KEEP_DAYS', 30);
}

/**
 * Welcart itself stores the signed-in remember token in its "usces_cookie".
 * Extend ONLY an explicitly remembered member cookie; do not extend guest
 * cookies, plugin registration invitations or WordPress admin auth cookies.
 */
add_filter('usces_filter_set_cookie_timeout', function ($expires, $values, $key) {
    if (
        $key !== 'usces_cookie'
        || ! is_array($values)
        || ($values['rme'] ?? '') !== 'forever'
        || empty($values['name'])
    ) {
        return $expires;
    }
    return time() + (int) AGFL_LOGIN_KEEP_DAYS * DAY_IN_SECONDS;
}, 10, 3);

/**
 * Welcart's PHP session cookie lasts until the browser session ends. Its
 * built-in remember-me mechanism restores the session when the member/login
 * page is visited. Restore early on ordinary GET navigation as well, so the
 * members-only shop gate and the QR company label see the member immediately.
 *
 * Never authenticate directly from cookie content: let Welcart validate the
 * stored _forever token against its own membership table.
 */
function agfl_login_keep_restore()
{
    if (
        is_admin() || wp_doing_ajax()
        || (defined('REST_REQUEST') && REST_REQUEST)
        || (defined('DOING_CRON') && DOING_CRON)
    ) {
        return;
    }
    if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'GET') {
        return;
    }
    // Do not interfere with logout, special Welcart actions or an ongoing login.
    if (isset($_GET['usces_page'])) {
        $page = is_string($_GET['usces_page']) ? sanitize_key(wp_unslash($_GET['usces_page'])) : '';
        if (in_array($page, array('logout', 'login'), true)) {
            return;
        }
    }
    if (isset($_GET['uscesid']) || isset($_GET['member_login'])) {
        return;
    }
    global $usces;
    if (
        ! is_object($usces) || ! method_exists($usces, 'get_cookie')
        || ! method_exists($usces, 'member_login')
        || ! function_exists('usces_is_login')
    ) {
        return;
    }
    if (usces_is_login() || session_status() !== PHP_SESSION_ACTIVE) {
        return;
    }
    $cookie = $usces->get_cookie();
    if (
        ! is_array($cookie)
        || ($cookie['rme'] ?? '') !== 'forever'
        || empty($cookie['name'])
    ) {
        return;
    }

    // Core verifies the _forever token and repopulates the Welcart member session.
    $usces->member_login();
}
add_action('init', 'agfl_login_keep_restore', 20);

/**
 * The active welcart_mbff theme provides its own login form template, with a
 * native rememberme checkbox. Default it to checked, but let users uncheck it
 * on shared devices. This changes no credentials, passwords or core files.
 */
add_action('wp_footer', function () {
    if (! function_exists('is_page') || ! is_page('usces-member')) {
        return;
    }
?>
    <script id="agfl-login-keep-default-choice">
        (function() {
            var remember = document.querySelector('form#loginform input[name="rememberme"]');
            if (remember && !remember.disabled) {
                remember.checked = true;
            }
        }());
    </script>
<?php
}, 99);
