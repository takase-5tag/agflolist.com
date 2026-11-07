<?php

/**
 * AG FLOLIST: membership visibility (read-only).
 * No payment/price hooks. Welcart's native member list is enhanced client-side,
 * not patched in usc-e-shop so Welcart upgrades don't overwrite it.
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Labels derive from saved Welcart member metadata, never from the browser or
 * a URL that was merely opened. Company association takes precedence.
 */
function agcm_member_directory_label($member_id)
{
    $member_id = (int)$member_id;
    $company_id = $member_id > 0 ? agcm_member_company_id($member_id) : 0;
    if ($company_id > 0) {
        $company = agcm_company($company_id);
        return array(
            'type' => 'company',
            'label' => '①企業別QR会員',
            'company' => $company ? wp_strip_all_tags($company->post_title) : '企業ID ' . $company_id,
            'company_id' => $company_id,
        );
    }
    if ($member_id > 0 && agcm_member_type($member_id) === 'fixed') {
        return array('type' => 'fixed', 'label' => '②固定URL会員', 'company' => '—', 'company_id' => 0);
    }
    return array('type' => '', 'label' => '未分類', 'company' => '—', 'company_id' => 0);
}

add_action('admin_menu', function () {
    add_submenu_page(
        'agcm-companies',
        '会員区分一覧',
        '会員区分一覧',
        AGCM_MANAGE_CAP,
        'agcm-member-directory',
        'agcm_member_directory_page'
    );
}, 21);

/** Query only existing Welcart members which were explicitly classified. */
function agcm_member_directory_query($type = 'all', $page = 1, $limit = 50)
{
    global $wpdb;
    $meta_table = $wpdb->prefix . 'usces_member_meta';
    $member_table = $wpdb->prefix . 'usces_member';
    $page = max(1, (int)$page);
    $limit = max(1, min(100, (int)$limit));
    $offset = ($page - 1) * $limit;
    // A fixed member later moved to a QR company must appear ONLY as a company member.
    $fixed_where = "m.meta_key = 'agcm_member_type' AND m.meta_value = 'fixed' AND NOT EXISTS (
        SELECT 1 FROM {$meta_table} co
        WHERE co.member_id = m.member_id AND co.meta_key = 'agcm_company_id'
        AND CAST(co.meta_value AS UNSIGNED) > 0
    )";
    $company_where = "m.meta_key = 'agcm_company_id' AND CAST(m.meta_value AS UNSIGNED) > 0";
    switch ($type) {
        case 'fixed':
            $where = '(' . $fixed_where . ')';
            break;
        case 'company':
            $where = '(' . $company_where . ')';
            break;
        default:
            $where = '(' . $fixed_where . ') OR (' . $company_where . ')';
    }
    $from = " FROM {$meta_table} m INNER JOIN {$member_table} members ON members.ID = m.member_id WHERE {$where} ";
    $total = (int)$wpdb->get_var("SELECT COUNT(DISTINCT m.member_id)" . $from);
    $ids = $wpdb->get_col($wpdb->prepare(
        "SELECT DISTINCT m.member_id" . $from . "ORDER BY m.member_id DESC LIMIT %d OFFSET %d",
        $limit,
        $offset
    ));
    return array(
        'ids' => is_array($ids) ? array_map('intval', $ids) : array(),
        'total' => $total,
        'page' => $page,
        'pages' => max(1, (int)ceil($total / $limit))
    );
}

function agcm_member_directory_page()
{
    if (!current_user_can(AGCM_MANAGE_CAP)) {
        wp_die('操作権限がありません。');
    }
    echo '<div class="wrap agcm-admin agcm-member-directory"><div class="agcm-heading"><div>';
    echo '<p class="agcm-eyebrow">AG FLOLIST / MEMBERSHIP</p><h1>会員区分一覧</h1>';
    echo '<p class="description">Welcart会員の登録区分と所属企業を確認できます。月次請求の登録区分も同じ会員データを参照します。</p>';
    echo '</div></div>';
    agcm_render_member_directory('all', true);
    echo '</div>';
}

/** Shared roster inside fixed signup page and plugin-wide directory. */
function agcm_render_member_directory($default_type = 'all', $show_filters = true)
{
    if (!current_user_can(AGCM_MANAGE_CAP)) {
        return;
    }
    $allowed = array('all', 'company', 'fixed');
    $type = $show_filters && isset($_GET['agcm_type']) && is_string($_GET['agcm_type'])
        ? sanitize_key(wp_unslash($_GET['agcm_type'])) : $default_type;
    if (!in_array($type, $allowed, true)) {
        $type = $default_type;
    }
    $page = isset($_GET['agcm_paged']) ? max(1, absint($_GET['agcm_paged'])) : 1;
    $data = agcm_member_directory_query($type, $page);
    $base = add_query_arg('page', $show_filters ? 'agcm-member-directory' : 'agcm-fixed-members', admin_url('admin.php'));
?>
    <section class="agcm-card agcm-roster-card">
        <div class="agcm-card-title">
            <h2><?php echo esc_html($show_filters ? '登録済み会員一覧' : '② 固定URL会員の登録一覧'); ?></h2>
            <span class="agcm-roster-count"><?php echo esc_html(number_format_i18n($data['total'])); ?> 名</span>
        </div>
        <?php if ($show_filters) : ?>
            <nav class="agcm-roster-filters" aria-label="会員区分の絞り込み">
                <?php foreach (array('all' => '両方の会員', 'company' => '①企業別QR会員', 'fixed' => '②固定URL会員') as $key => $text) : ?>
                    <a class="<?php echo $type === $key ? 'is-selected' : ''; ?>" href="<?php echo esc_url(add_query_arg(array('agcm_type' => $key, 'agcm_paged' => 1), $base)); ?>"><?php echo esc_html($text); ?></a>
                <?php endforeach; ?>
            </nav>
        <?php endif; ?>
        <div class="agcm-roster-scroll">
            <table class="widefat striped agcm-member-table agcm-roster-table">
                <thead>
                    <tr>
                        <th scope="col">会員ID</th>
                        <th scope="col">登録区分</th>
                        <th scope="col">所属企業</th>
                        <th scope="col">会員氏名</th>
                        <th scope="col">メールアドレス</th>
                        <th scope="col">詳細</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['ids'] as $member_id) :
                        $member = function_exists('wel_get_member') ? wel_get_member($member_id) : false;
                        if (!is_array($member) || !$member) {
                            continue;
                        }
                        $label = agcm_member_directory_label($member_id);
                        $name = trim((string)($member['name1'] ?? '') . ' ' . (string)($member['name2'] ?? ''));
                        $email = agcm_member_email($member_id, $member);
                        $details_url = add_query_arg(array('page' => 'usces_memberlist', 'member_action' => 'edit', 'member_id' => $member_id), admin_url('admin.php'));
                    ?>
                        <tr>
                            <td><?php echo esc_html($member_id); ?></td>
                            <td><span class="agcm-kind agcm-kind--<?php echo esc_attr($label['type'] ?: 'unknown'); ?>"><?php echo esc_html($label['label']); ?></span></td>
                            <td><?php if ($label['company_id'] && agcm_company($label['company_id'])) : ?>
                                    <a href="<?php echo esc_url(agcm_admin_url($label['company_id'])); ?>"><?php echo esc_html($label['company']); ?></a>
                                <?php else : echo esc_html($label['company']);
                                endif; ?>
                            </td>
                            <td><?php echo esc_html($name); ?></td>
                            <td><?php echo esc_html($email); ?></td>
                            <td><a href="<?php echo esc_url($details_url); ?>">Welcartで確認</a></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$data['ids']) : ?><tr>
                            <td colspan="6">この区分の会員はまだ登録されていません。</td>
                        </tr><?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if ($data['pages'] > 1) : ?>
            <nav class="agcm-roster-pages" aria-label="会員一覧のページ切り替え">
                <?php if ($data['page'] > 1) : ?><a class="button" href="<?php echo esc_url(add_query_arg(array('agcm_type' => $type, 'agcm_paged' => $data['page'] - 1), $base)); ?>">前へ</a><?php endif; ?>
                <span><?php echo esc_html($data['page'] . ' / ' . $data['pages']); ?>ページ</span>
                <?php if ($data['page'] < $data['pages']) : ?><a class="button" href="<?php echo esc_url(add_query_arg(array('agcm_type' => $type, 'agcm_paged' => $data['page'] + 1), $base)); ?>">次へ</a><?php endif; ?>
            </nav>
        <?php endif; ?>
        <p class="description">この一覧はWelcartに保存された会員情報・登録区分を表示します。手動登録等で区分の付いていない会員はここには表示されず、Welcart会員リストでは「未分類」になります。</p>
    </section>
<?php
}

/** Ajax returns classification only for rows already shown by Welcart. */
add_action('wp_ajax_agcm_member_row_labels', function () {
    if (!current_user_can(AGCM_MANAGE_CAP)) {
        wp_send_json_error('権限がありません。', 403);
    }
    check_ajax_referer('agcm_member_row_labels', 'nonce');
    $ids = isset($_POST['member_ids']) && is_array($_POST['member_ids']) ? wp_unslash($_POST['member_ids']) : array();
    if (count($ids) > 100) {
        wp_send_json_error('件数が多すぎます。', 400);
    }
    $result = array();
    foreach ($ids as $raw) {
        if (!is_scalar($raw)) {
            continue;
        }
        $id = absint($raw);
        if (!$id || !function_exists('wel_get_member')) {
            continue;
        }
        $member = wel_get_member($id);
        if (!is_array($member) || !$member) {
            continue;
        }
        $result[$id] = agcm_member_directory_label($id);
    }
    wp_send_json_success($result);
});

add_action('admin_enqueue_scripts', function () {
    $page = isset($_GET['page']) && is_string($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
    if (!current_user_can(AGCM_MANAGE_CAP)) {
        return;
    }
    if (in_array($page, array('agcm-member-directory', 'agcm-fixed-members'), true)) {
        wp_enqueue_style('agcm-admin', AGCM_URL . 'assets/admin.css', array(), AGCM_VERSION);
    }
    if ($page !== 'usces_memberlist') {
        return;
    }
    wp_enqueue_style('agcm-member-directory', AGCM_URL . 'assets/member-directory.css', array(), AGCM_VERSION);
    wp_enqueue_script('agcm-member-directory', AGCM_URL . 'assets/member-directory.js', array(), AGCM_VERSION, true);
    wp_localize_script('agcm-member-directory', 'agcmMemberDirectory', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('agcm_member_row_labels'),
    ));
});
