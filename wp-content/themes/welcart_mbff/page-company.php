<?php

/**
 * 旧 /company/ の互換テンプレート。
 * 新しい固定ページ /partner-salon/ が公開されていれば301転送。
 * 未公開の場合は移行中の暫定措置として新デザインを表示。
 */
$ag_partner_target = get_page_by_path('partner-salon');
if ($ag_partner_target instanceof WP_Post && $ag_partner_target->post_status === 'publish') {
    wp_safe_redirect(get_permalink($ag_partner_target), 301, 'AG FLOLIST');
    exit;
}
require get_template_directory() . '/page-partner-salon.php';
