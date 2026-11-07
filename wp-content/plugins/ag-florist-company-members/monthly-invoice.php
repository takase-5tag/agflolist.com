<?php

/** Monthly system-use fee statement for QR/fixed members and guest orders. */
if (!defined('ABSPATH')) {
    exit;
}

function agcm_invoice_month($raw)
{
    return is_string($raw) && preg_match('/^20\d{2}-(0[1-9]|1[0-2])$/D', $raw) ? $raw : wp_date('Y-m');
}
function agcm_invoice_preferences()
{
    $defaults = array(
        'recipient' => '株式会社フローリストジャパン',
        'issuer' => '株式会社ファイブタッグプラス',
        'issuer_address' => "〒850-0852\n長崎県長崎市万屋町2-20 2階",
        'issuer_tel' => '095-811-0155',
        'registration_no' => '',
        'extra_note' => 'システム利用料（商品売上の8%）に消費税10%を適用しています。',
    );
    $saved = get_option('agcm_invoice_preferences', array());
    return array_merge($defaults, is_array($saved) ? $saved : array());
}
function agcm_invoice_eligible_order($order)
{
    $status = strtolower((string)($order['order_status'] ?? ''));
    // Cancelled orders and estimates are not billable sales.
    if (strpos($status, 'cancel') !== false || strpos($status, 'estimate') !== false) {
        return false;
    }
    return true;
}
/** Reporting categories. Guest checkout is NOT a Welcart membership. */
function agcm_invoice_type_labels()
{
    return array(
        'company' => '企業別QR会員',
        'fixed'   => '固定URL会員',
        'guest'   => '一般販売（非会員）',
    );
}
function agcm_invoice_order_type($order)
{
    global $usces;
    $order_id = (int)($order['ID'] ?? 0);
    // Immutable order-time classification takes precedence for registered members.
    if ($order_id && $usces && method_exists($usces, 'get_order_meta_value')) {
        $saved = (string)$usces->get_order_meta_value('agcm_member_type', $order_id);
        if (in_array($saved, array('company', 'fixed', 'guest'), true)) {
            return $saved;
        }
    }
    $mem = (int)($order['mem_id'] ?? 0);
    // A guest order has no Welcart member ID. Do NOT treat an unknown
    // registered member as a guest; preserve the original unclassified count.
    if ($mem <= 0) {
        return 'guest';
    }
    return agcm_member_type($mem);
}
function agcm_invoice_subtotal($order)
{
    $cart = $order['order_cart'] ?? array();
    if (is_string($cart)) {
        $cart = maybe_unserialize($cart);
    }
    if (!is_array($cart)) {
        return 0;
    }
    $sum = 0.0;
    foreach ($cart as $row) {
        if (!is_array($row)) {
            continue;
        }
        $quantity = max(0, (int)($row['quantity'] ?? 0));
        $price = max(0, (float)($row['price'] ?? 0));
        $sum += $quantity * $price;
    }
    return $sum;
}
/** The original monthly report rounds the fee separately for 8%-goods and 10%-goods groups. */
function agcm_invoice_goods_tax_rate($row)
{
    foreach (array('tax_rate', 'taxrate', 'item_tax_rate', 'applicable_taxrate', 'tax') as $key) {
        if (!isset($row[$key]) || $row[$key] === '') {
            continue;
        }
        $value = (string)$row[$key];
        if (is_numeric($value)) {
            return (int)$value === 8 ? 8 : 10;
        }
        if (
            stripos($value, 'reduced') !== false || stripos($value, 'keigen') !== false
            || strpos($value, '軽減') !== false
        ) {
            return 8;
        }
    }
    $post = (int)($row['post_id'] ?? 0);
    $sku  = (string)($row['sku'] ?? $row['sku_code'] ?? '');
    if ($post && $sku && function_exists('wel_get_sku')) {
        $sku_data = wel_get_sku($post, $sku);
        if (is_array($sku_data)) {
            return agcm_invoice_goods_tax_rate_from_sku($sku_data);
        }
    }
    return 10;
}
function agcm_invoice_goods_tax_rate_from_sku($sku_data)
{
    foreach (array('tax_rate', 'taxrate', 'item_tax_rate', 'applicable_taxrate', 'tax') as $key) {
        if (!isset($sku_data[$key]) || $sku_data[$key] === '') {
            continue;
        }
        $v = (string)$sku_data[$key];
        if (is_numeric($v)) {
            return (int)$v === 8 ? 8 : 10;
        }
        if (stripos($v, 'reduced') !== false || stripos($v, 'keigen') !== false || strpos($v, '軽減') !== false) {
            return 8;
        }
    }
    return 10;
}
function agcm_invoice_amounts($product_subtotal)
{
    // Single-group calculation helper for unit tests and fallback.
    $fee = (int)ceil(max(0, (float)$product_subtotal) * 0.08);
    $tax = (int)round($fee * 0.10, 0, PHP_ROUND_HALF_UP);
    return array('fee' => $fee, 'tax' => $tax, 'total' => $fee + $tax);
}
function agcm_invoice_order_amounts($order)
{
    $cart = $order['order_cart'] ?? array();
    if (is_string($cart)) {
        $cart = maybe_unserialize($cart);
    }
    $groups = array(8 => 0.0, 10 => 0.0);
    if (is_array($cart)) {
        foreach ($cart as $row) {
            if (!is_array($row)) {
                continue;
            }
            $amount = max(0, (float)($row['price'] ?? 0)) * max(0, (int)($row['quantity'] ?? 0));
            $groups[agcm_invoice_goods_tax_rate($row)] += $amount;
        }
    }
    $result = array('fee' => 0, 'tax' => 0, 'total' => 0);
    foreach ($groups as $subtotal) {
        if ($subtotal <= 0) {
            continue;
        }
        $part = agcm_invoice_amounts($subtotal);
        foreach ($result as $key => $value) {
            $result[$key] += $part[$key];
        }
    }
    return $result;
}
function agcm_invoice_data($month)
{
    global $wpdb;
    $month = agcm_invoice_month($month);
    $start = $month . '-01 00:00:00';
    $next = (new DateTimeImmutable($month . '-01', wp_timezone()))->modify('+1 month')->format('Y-m-d') . ' 00:00:00';
    $table = $wpdb->prefix . 'usces_order';
    $orders = $wpdb->get_results($wpdb->prepare(
        "SELECT ID, mem_id, order_date, order_status, order_cart FROM {$table} WHERE order_date >= %s AND order_date < %s ORDER BY order_date ASC, ID ASC",
        $start,
        $next
    ), ARRAY_A);
    $summary_empty = array('item' => 0, 'fee' => 0, 'tax' => 0, 'total' => 0);
    $data = array(
        'month' => $month,
        'start' => $start,
        'end' => $next,
        'rows' => array(),
        'unclassified' => 0,
        'excluded' => 0,
        'counts' => array('company' => 0, 'fixed' => 0, 'guest' => 0),
        'types' => array('company' => $summary_empty, 'fixed' => $summary_empty, 'guest' => $summary_empty),
        'item' => 0,
        'fee' => 0,
        'tax' => 0,
        'total' => 0
    );
    foreach ((array)$orders as $order) {
        if (!agcm_invoice_eligible_order($order)) {
            ++$data['excluded'];
            continue;
        }
        $type = agcm_invoice_order_type($order);
        if (!isset($data['counts'][$type])) {
            ++$data['unclassified'];
            continue;
        }
        $item = agcm_invoice_subtotal($order);
        if ($item <= 0) {
            continue;
        }
        $fees = agcm_invoice_order_amounts($order);
        $order_no = function_exists('usces_get_deco_order_id')
            ? usces_get_deco_order_id((int)$order['ID']) : $order['ID'];
        $row = array(
            'id' => (int)$order['ID'],
            'no' => (string)$order_no,
            'date' => $order['order_date'],
            'type' => $type,
            'item' => $item,
            'fee' => $fees['fee'],
            'tax' => $fees['tax'],
            'total' => $fees['total']
        );
        $data['rows'][] = $row;
        ++$data['counts'][$type];
        foreach (array('item', 'fee', 'tax', 'total') as $k) {
            $data[$k] += $row[$k];
            $data['types'][$type][$k] += $row[$k];
        }
    }
    return $data;
}
function agcm_invoice_type_label($type)
{
    $labels = agcm_invoice_type_labels();
    return $labels[$type] ?? '未分類';
}
function agcm_invoice_number($num)
{
    return number_format((float)$num) . '円';
}
function agcm_invoice_url($month, $mode)
{
    $url = add_query_arg(array('page' => 'agcm-monthly-invoice', 'invoice_month' => $month, 'mode' => $mode), admin_url('admin.php'));
    return wp_nonce_url($url, 'agcm_export_invoice');
}
add_action('admin_menu', function () {
    add_menu_page(
        '月次請求書',
        '月次請求書',
        AGCM_MANAGE_CAP,
        'agcm-monthly-invoice',
        'agcm_invoice_admin',
        'dashicons-media-spreadsheet',
        4.8
    );
});
add_action('admin_post_agcm_invoice_settings', function () {
    if (!current_user_can(AGCM_MANAGE_CAP)) {
        wp_die('権限がありません。', '', array('response' => 403));
    }
    check_admin_referer('agcm_invoice_settings');
    $input = wp_unslash($_POST['invoice'] ?? array());
    $result = array();
    foreach (array_keys(agcm_invoice_preferences()) as $key) {
        $result[$key] = isset($input[$key]) && is_scalar($input[$key])
            ? sanitize_textarea_field($input[$key]) : '';
    }
    update_option('agcm_invoice_preferences', $result, false);
    wp_safe_redirect(add_query_arg(array('page' => 'agcm-monthly-invoice', 'saved' => '1'), admin_url('admin.php')));
    exit;
});
function agcm_invoice_admin()
{
    if (!current_user_can(AGCM_MANAGE_CAP)) {
        wp_die('権限がありません。');
    }
    $month = agcm_invoice_month(isset($_GET['invoice_month']) && is_string($_GET['invoice_month'])
        ? sanitize_text_field(wp_unslash($_GET['invoice_month'])) : wp_date('Y-m'));
    $data = agcm_invoice_data($month);
    $opts = agcm_invoice_preferences();
?>
    <div class="wrap agcm-invoice-admin">
        <h1>ファイブタッグプラス 月次請求書</h1>
        <p>企業別QR会員・固定URL会員・一般販売（非会員）の3区分の売上を集計します。システム利用料は商品合計の8％、利用料に対する消費税は10％です。一般販売についても同じ料率で算定します。</p>
        <?php if (!empty($_GET['saved'])) : ?><div class="notice notice-success">
                <p>請求書の発行情報を保存しました。</p>
            </div><?php endif; ?>
        <form method="get" style="margin:22px 0"><input type="hidden" name="page" value="agcm-monthly-invoice">
            <label for="agcm-month">対象月：</label> <input type="month" id="agcm-month" name="invoice_month" value="<?php echo esc_attr($month); ?>">
            <button type="submit" class="button button-primary">集計する</button>
        </form>
        <p><a class="button button-primary" target="_blank" rel="noopener" href="<?php echo esc_url(agcm_invoice_url($month, 'pdf')); ?>">請求書PDF</a>
            <a class="button" href="<?php echo esc_url(agcm_invoice_url($month, 'csv')); ?>">CSVを出力</a>
        </p>
        <h2>販売区分ごとの集計</h2>
        <table class="widefat striped" style="max-width:960px">
            <thead>
                <tr>
                    <th>販売区分</th>
                    <th>注文数</th>
                    <th>商品合計</th>
                    <th>利用料8%</th>
                    <th>利用料の消費税10%</th>
                    <th>請求額</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (array('company', 'fixed', 'guest') as $type) : $r = $data['types'][$type]; ?>
                    <tr>
                        <th><?php echo esc_html(agcm_invoice_type_label($type)); ?></th>
                        <td><?php echo (int)$data['counts'][$type]; ?>件</td>
                        <td><?php echo esc_html(agcm_invoice_number($r['item'])); ?></td>
                        <td><?php echo esc_html(agcm_invoice_number($r['fee'])); ?></td>
                        <td><?php echo esc_html(agcm_invoice_number($r['tax'])); ?></td>
                        <td><strong><?php echo esc_html(agcm_invoice_number($r['total'])); ?></strong></td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <th>合計</th>
                    <th><?php echo count($data['rows']); ?>件</th>
                    <th><?php echo esc_html(agcm_invoice_number($data['item'])); ?></th>
                    <th><?php echo esc_html(agcm_invoice_number($data['fee'])); ?></th>
                    <th><?php echo esc_html(agcm_invoice_number($data['tax'])); ?></th>
                    <th><?php echo esc_html(agcm_invoice_number($data['total'])); ?></th>
                </tr>
            </tbody>
        </table>
        <p class="description">対象外：会員種別未分類 <?php echo (int)$data['unclassified']; ?>件、キャンセル・見積 <?php echo (int)$data['excluded']; ?>件。注文時の会員種別を優先し、過去の登録会員の注文は現会員情報から判別します。一般販売は会員IDがない注文として判定し、会員種別不明の登録会員は一般販売に含めません。</p>
        <p class="description">※請求対象は商品代金そのものではなく、各販売区分の売上から算定したシステム利用料です。決済済み商品代金を再請求するものではありません。一般販売の決済状況・返品／返金は請求書確定前にWelcart注文一覧でも確認してください。</p>
        <h2>注文別明細</h2>
        <table class="widefat striped">
            <thead>
                <tr>
                    <th>注文日</th>
                    <th>注文番号</th>
                    <th>販売区分</th>
                    <th>商品売上</th>
                    <th>利用料8%</th>
                    <th>消費税10%</th>
                    <th>請求額</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['rows'] as $row): ?><tr>
                        <td><?php echo esc_html(str_replace('-', '/', substr((string)$row['date'], 0, 10))); ?></td>
                        <td><?php echo esc_html($row['no']); ?></td>
                        <td><?php echo esc_html(agcm_invoice_type_label($row['type'])); ?></td>
                        <td><?php echo esc_html(agcm_invoice_number($row['item'])); ?></td>
                        <td><?php echo esc_html(agcm_invoice_number($row['fee'])); ?></td>
                        <td><?php echo esc_html(agcm_invoice_number($row['tax'])); ?></td>
                        <td><?php echo esc_html(agcm_invoice_number($row['total'])); ?></td>
                    </tr>
                <?php endforeach;
                if (!$data['rows']): ?><tr>
                        <td colspan="7">対象となる注文はありません。</td>
                    </tr><?php endif; ?>
            </tbody>
        </table>
        <details style="max-width:850px;margin:30px 0 50px;">
            <summary><strong>請求書の宛先・発行者設定（PDFに反映）</strong></summary>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin:15px 0"><input type="hidden" name="action" value="agcm_invoice_settings">
                <?php wp_nonce_field('agcm_invoice_settings'); ?>
                <table class="form-table">
                    <tbody>
                        <?php foreach (array('recipient' => '宛先', 'issuer' => '発行者', 'issuer_address' => '発行者住所', 'issuer_tel' => '発行者電話番号', 'registration_no' => '適格請求書発行事業者登録番号', 'extra_note' => '備考') as $key => $label) : ?>
                            <tr>
                                <th scope="row"><label for="agcm-<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></label></th>
                                <td>
                                    <?php if ($key === 'issuer_address' || $key === 'extra_note'): ?>
                                        <textarea rows="3" class="large-text" id="agcm-<?php echo esc_attr($key); ?>" name="invoice[<?php echo esc_attr($key); ?>]"><?php echo esc_textarea($opts[$key]); ?></textarea>
                                    <?php else: ?><input class="regular-text" id="agcm-<?php echo esc_attr($key); ?>" name="invoice[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($opts[$key]); ?>">
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table><?php submit_button('請求書情報を保存'); ?>
            </form>
        </details>
    </div><?php
        }
        function agcm_invoice_pdf_html($data)
        {
            $opts = agcm_invoice_preferences();
            ob_start(); ?>
    <h1 style="font-size:18px;text-align:center;letter-spacing:3px">請求書</h1>
    <table cellpadding="5" width="100%">
        <tr>
            <td width="60%"><strong><?php echo esc_html($opts['recipient']); ?> 御中</strong><br><br>
                対象月：<?php echo esc_html($data['month']); ?><br>対象期間：<?php echo esc_html(substr($data['start'], 0, 10) . ' ～ ' . date('Y-m-d', strtotime($data['end'] . ' -1 day'))); ?>
            </td>
            <td width="40%">発行日：<?php echo esc_html(wp_date('Y年m月d日')); ?><br><br>
                <?php echo esc_html($opts['issuer']); ?><br><?php echo nl2br(esc_html($opts['issuer_address'])); ?><br>
                <?php echo esc_html($opts['issuer_tel']); ?><br><?php echo esc_html($opts['registration_no']); ?></td>
        </tr>
    </table>
    <br>
    <table border="1" cellpadding="6" width="100%">
        <tr style="background-color:#f3ebe4">
            <td width="65%"><strong>システム利用料 ご請求金額</strong></td>
            <td width="35%" align="right"><strong><?php echo esc_html(agcm_invoice_number($data['total'])); ?></strong></td>
        </tr>
    </table><br>
    <table cellpadding="5" border="1" width="100%">
        <thead>
            <tr style="background-color:#f6f1ed">
                <th width="24%">販売区分</th>
                <th width="15%">注文数</th>
                <th width="19%">商品売上</th>
                <th width="14%">利用料8%</th>
                <th width="14%">消費税10%</th>
                <th width="14%">請求額</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach (array('company', 'fixed', 'guest') as $type): $row = $data['types'][$type]; ?><tr>
                    <td><?php echo esc_html(agcm_invoice_type_label($type)); ?></td>
                    <td align="right"><?php echo (int)$data['counts'][$type]; ?></td>
                    <td align="right"><?php echo esc_html(number_format($row['item'])); ?></td>
                    <td align="right"><?php echo esc_html(number_format($row['fee'])); ?></td>
                    <td align="right"><?php echo esc_html(number_format($row['tax'])); ?></td>
                    <td align="right"><?php echo esc_html(number_format($row['total'])); ?></td>
                </tr><?php endforeach; ?>
            <tr>
                <td>合計</td>
                <td align="right"><?php echo count($data['rows']); ?></td>
                <td align="right"><?php echo esc_html(number_format($data['item'])); ?></td>
                <td align="right"><?php echo esc_html(number_format($data['fee'])); ?></td>
                <td align="right"><?php echo esc_html(number_format($data['tax'])); ?></td>
                <td align="right"><?php echo esc_html(number_format($data['total'])); ?></td>
            </tr>
        </tbody>
    </table>
    <h3>注文別明細</h3>
    <table cellpadding="4" border="1" width="100%">
        <thead>
            <tr style="background-color:#f6f1ed">
                <th width="15%">注文日</th>
                <th width="19%">注文番号</th>
                <th width="19%">販売区分</th>
                <th width="15%">商品売上</th>
                <th width="10%">利用料</th>
                <th width="10%">消費税</th>
                <th width="12%">合計</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data['rows'] as $row): ?><tr>
                    <td><?php echo esc_html(substr($row['date'], 0, 10)); ?></td>
                    <td><?php echo esc_html($row['no']); ?></td>
                    <td><?php echo esc_html(agcm_invoice_type_label($row['type'])); ?></td>
                    <td align="right"><?php echo esc_html(number_format($row['item'])); ?></td>
                    <td align="right"><?php echo esc_html(number_format($row['fee'])); ?></td>
                    <td align="right"><?php echo esc_html(number_format($row['tax'])); ?></td>
                    <td align="right"><?php echo esc_html(number_format($row['total'])); ?></td>
                </tr><?php endforeach; ?></tbody>
    </table>
    <p style="font-size:9px">※一般販売（非会員）を含む3区分の売上からシステム利用料8%を算定しています。請求するのは商品代金そのものではなくシステム利用料とその消費税10%です。商品税率別の売上を元に利用料の端数を計算します。送料・ポイント・商品税額は利用料算定の対象外です。決済状況や返品・返金は請求書確定前にご確認ください。</p>
    <p><?php echo nl2br(esc_html($opts['extra_note'])); ?></p>
<?php return ob_get_clean();
        }
        add_action('admin_init', function () {
            if (!is_admin() || !isset($_GET['page']) || $_GET['page'] !== 'agcm-monthly-invoice' || !isset($_GET['mode'])) {
                return;
            }
            if (!current_user_can(AGCM_MANAGE_CAP)) {
                wp_die('権限がありません。', '', array('response' => 403));
            }
            check_admin_referer('agcm_export_invoice');
            $month = agcm_invoice_month(isset($_GET['invoice_month']) && is_string($_GET['invoice_month']) ? sanitize_text_field(wp_unslash($_GET['invoice_month'])) : wp_date('Y-m'));
            $data = agcm_invoice_data($month);
            if ($_GET['mode'] === 'csv') {
                while (ob_get_level() > 0) {
                    ob_end_clean();
                }
                nocache_headers();
                header('Content-Type: text/csv; charset=UTF-8');
                header('Content-Disposition: attachment; filename="agcm_invoice_' . $month . '.csv"');
                $f = fopen('php://output', 'w');
                fwrite($f, "\xEF\xBB\xBF");
                fputcsv($f, array('対象月', '注文日', '注文番号', '販売区分', '商品売上', 'システム利用料8%', '利用料消費税10%', '請求額'));
                foreach ($data['rows'] as $r) {
                    // Escape cells that spreadsheet software could interpret as formulas.
                    $no = $r['no'];
                    if (preg_match('/^[=+\-@\t\r]/', $no)) {
                        $no = "'" . $no;
                    }
                    fputcsv($f, array(
                        $month,
                        substr($r['date'], 0, 10),
                        $no,
                        agcm_invoice_type_label($r['type']),
                        $r['item'],
                        $r['fee'],
                        $r['tax'],
                        $r['total']
                    ));
                }
                fclose($f);
                exit;
            }
            if ($_GET['mode'] === 'pdf') {
                $tcpdf = WP_PLUGIN_DIR . '/usc-e-shop/pdf/tcpdf/tcpdf.php';
                if (!is_file($tcpdf)) {
                    wp_die('WelcartのTCPDFファイルが見つかりません。CSVで確認するかPDFライブラリの設置場所を確認してください。');
                }
                require_once $tcpdf;
                if (!class_exists('TCPDF')) {
                    wp_die('TCPDFが読み込めません。');
                }
                $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
                $pdf->SetCreator('AG FLOLIST');
                $pdf->SetTitle('月次請求書 ' . $month);
                $pdf->SetMargins(10, 10, 10);
                $pdf->SetAutoPageBreak(true, 15);
                $pdf->AddPage();
                $pdf->SetFont('cid0jp', '', 9);
                $pdf->writeHTML(agcm_invoice_pdf_html($data), true, false, true, false, '');
                while (ob_get_level() > 0) {
                    ob_end_clean();
                }
                $pdf->Output('agcm_invoice_' . $month . '.pdf', 'I');
                exit;
            }
        });
