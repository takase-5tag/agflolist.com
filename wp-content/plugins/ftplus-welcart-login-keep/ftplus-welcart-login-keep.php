<?php
/**
 * Plugin Name: FTP Welcart ログイン保持
 * Description: Welcart会員の「ログイン情報を記憶」を初期チェックし、保存済み認証Cookieによるログイン状態を通常ページでも復元します（標準30日）。
 * Version: 1.1.0
 * Requires PHP: 7.4
 * Author: FTP（株式会社ファイブタッグプラス）
 * Text Domain: ftplus-welcart-login-keep
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Change this constant before activation if a site needs a shorter period.
 * This affects newly issued / refreshed Welcart remember-me cookies only.
 */
if ( ! defined( 'FTPLUS_WELCART_LOGIN_KEEP_DAYS' ) ) {
    define( 'FTPLUS_WELCART_LOGIN_KEEP_DAYS', 30 );
}

/**
 * Only Welcart's persistent member-authentication cookie is extended.
 * Guest cookies and other plugins' cookies keep their original expiry.
 * Default args keep the callback compatible with older Welcart filters.
 */
function ftplus_welcart_login_keep_timeout( $expires, $values = array(), $key = 'usces_cookie' ) {
    if ( 'usces_cookie' !== $key || ! is_array( $values ) ) {
        return $expires;
    }
    if ( 'forever' !== ( $values['rme'] ?? '' ) || empty( $values['name'] ) ) {
        return $expires;
    }

    $days = max( 1, min( 90, (int) FTPLUS_WELCART_LOGIN_KEEP_DAYS ) );
    return time() + $days * DAY_IN_SECONDS;
}
add_filter( 'usces_filter_set_cookie_timeout', 'ftplus_welcart_login_keep_timeout', 10, 3 );

/**
 * Restore a Welcart member session when visiting regular frontend pages.
 * Welcart validates the saved _forever token in the database. This plugin
 * never uses the cookie value as an ID and never creates a parallel auth flow.
 */
function ftplus_welcart_login_keep_restore() {
    if ( ! defined( 'USCES_VERSION' ) || is_admin() || wp_doing_ajax() ) {
        return;
    }
    if ( ( defined( 'REST_REQUEST' ) && REST_REQUEST )
        || ( defined( 'DOING_CRON' ) && DOING_CRON ) ) {
        return;
    }
    if ( 'GET' !== strtoupper( (string) ( $_SERVER['REQUEST_METHOD'] ?? 'GET' ) ) ) {
        return;
    }

    // Do not silently re-authenticate while the user is logging out or in.
    if ( isset( $_GET['usces_page'] ) ) {
        $page = is_string( $_GET['usces_page'] ) ? sanitize_key( wp_unslash( $_GET['usces_page'] ) ) : '';
        if ( in_array( $page, array( 'login', 'logout' ), true ) ) {
            return;
        }
    }
    // Old Welcart SSL handoff URLs must be processed by Welcart itself.
    if ( isset( $_GET['uscesid'] ) || isset( $_GET['member_login'] ) ) {
        return;
    }

    global $usces;
    if ( ! is_object( $usces )
        || ! method_exists( $usces, 'get_cookie' )
        || ! method_exists( $usces, 'member_login' )
        || ! function_exists( 'usces_is_login' ) ) {
        return;
    }
    if ( isset( $usces->options['membersystem_state'] )
        && 'activate' !== $usces->options['membersystem_state'] ) {
        return;
    }
    if ( usces_is_login() || PHP_SESSION_ACTIVE !== session_status() ) {
        return;
    }

    $cookie = $usces->get_cookie();
    if ( ! is_array( $cookie )
        || 'forever' !== ( $cookie['rme'] ?? '' )
        || empty( $cookie['name'] ) ) {
        return;
    }

    // The standard Welcart method verifies the token and restores the session.
    $usces->member_login();
}
add_action( 'init', 'ftplus_welcart_login_keep_restore', 20 );

/**
 * Mark the remember-me checkbox by default on any *Welcart* member login
 * form, regardless of the site's theme or member-page slug.
 * Visitors can uncheck it before signing in on a shared computer.
 */
function ftplus_welcart_login_keep_default_checkbox() {
    if ( ! defined( 'USCES_VERSION' ) ) {
        return;
    }
    ?>
    <script id="ftplus-welcart-login-keep-default">
    (function () {
      var forms = document.querySelectorAll('form');
      for (var i = 0; i < forms.length; i++) {
        var form = forms[i];
        // Welcart uses "loginmail", unlike WordPress's own login form.
        if (!form.querySelector('input[name="loginmail"]')) continue;
        var checkbox = form.querySelector('input[type="checkbox"][name="rememberme"]');
        if (checkbox && !checkbox.disabled) checkbox.checked = true;
      }
    }());
    </script>
    <?php
}
add_action( 'wp_footer', 'ftplus_welcart_login_keep_default_checkbox', 99 );

/** Notice if Welcart is not currently active. */
function ftplus_welcart_login_keep_notice() {
    if ( defined( 'USCES_VERSION' ) || ! current_user_can( 'activate_plugins' ) ) {
        return;
    }
    echo '<div class="notice notice-warning"><p>FTP Welcart ログイン保持：Welcart e-Commerce を有効化してください。</p></div>';
}
add_action( 'admin_notices', 'ftplus_welcart_login_keep_notice' );
