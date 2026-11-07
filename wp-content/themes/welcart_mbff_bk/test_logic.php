<?php
// Isolated arithmetic/role test with WP/Welcart stubs. NOT a live checkout integration test.
error_reporting(E_ALL);
define('ABSPATH', '/tmp/');
define('WP_PLUGIN_DIR', '/tmp');
define('ARRAY_A', 'ARRAY_A');
$GLOBALS['test_member'] = 0;
$GLOBALS['test_members'] = array(1 => 'fixed', 2 => 'company', 3 => '');
$GLOBALS['company_prices'] = array('7:70' => 70);
class WP_Term
{
    public $term_id = 41;
    public $slug = 'tansa_cate';
}
class WP_Post
{
    public $post_type = 'agcm_company';
    public $post_status = 'private';
    public $ID = 7;
}
class MockDB
{
    public $last_id = 0;
    public $prefix = 'wp_';
    public $posts = 'wp_posts';
    public $postmeta = 'wp_postmeta';
    function prepare($sql, ...$args)
    {
        if (strpos($sql, 'meta_value FROM') !== false) {
            $this->last_id = (int)$args[0];
        }
        return $sql;
    }
    function get_var($sql)
    {
        return (isset($this->last_id) && $this->last_id === 2) ? '7' : '0';
    }
    function get_results($sql, $mode)
    {
        return $GLOBALS['mock_orders'] ?? [];
    }
}
$GLOBALS['wpdb'] = new MockDB();
class MockWelcart
{
    public $metas = [];
    public $orders = [];
    function get_member_meta_value($k, $member_id)
    {
        return $GLOBALS['test_members'][$member_id] ?? '';
    }
    function get_order_meta_value($k, $id)
    {
        return $this->orders[$id][$k] ?? '';
    }
    function set_member_meta_value($k, $v, $id)
    {
        $this->metas[$id][$k] = $v;
    }
}
$GLOBALS['usces'] = new MockWelcart();
function plugin_dir_path($f)
{
    return dirname($f) . '/';
}
function plugin_dir_url($f)
{
    return 'https://example.org/plugins/';
}
function register_activation_hook(...$args) {}
function add_action(...$args) {}
function add_filter(...$args) {}
function wp_parse_url($a, $part)
{
    return parse_url($a, $part);
}
function home_url($path = '')
{
    return 'https://example.org' . $path;
}
function get_category_by_slug($a)
{
    return $a === 'tansa_cate' ? new WP_Term() : null;
}
function wp_get_post_categories($post)
{
    return $post === 11 || $post === 12 ? [41] : [51];
}
function get_ancestors($id, $taxonomy, $type)
{
    return [];
}
function is_wp_error($v)
{
    return false;
}
function wel_is_logged_in()
{
    return $GLOBALS['test_member'] > 0;
}
function wel_logged_in_id()
{
    return $GLOBALS['test_member'];
}
function usces_is_login()
{
    return $GLOBALS['test_member'] > 0;
}
function get_post($id)
{
    return (int)$id === 7 ? new WP_Post() : null;
}
function get_post_meta($id, $key, $single)
{
    if ($key === '_agcm_rates') return ['11:ABC' => 70];
    return null;
}
function wp_date($fmt)
{
    return date($fmt);
}
function wp_timezone()
{
    return new DateTimeZone('Asia/Tokyo');
}
function maybe_unserialize($v)
{
    return unserialize($v, ['allowed_classes' => false]);
}
function wel_get_sku($post, $sku)
{
    return ['price' => 10000];
}
function agcm_assert($n, $expected, $actual)
{
    if ($expected !== $actual && !(is_numeric($expected) && is_numeric($actual) && abs($expected - $actual) < 0.001)) {
        throw new Exception("$n expected $expected got $actual");
    }
    echo "OK $n => $actual\n";
}
require dirname(__FILE__) . '/ag-FLOLIST-company-members/ag-FLOLIST-company-members.php';
foreach ([1 => 65, 5 => 65, 6 => 55, 11 => 55, 12 => 50, 20 => 50] as $quantity => $rate) {
    agcm_assert('rate ' . $quantity, $rate, agcm_fixed_tansa_rate($quantity));
}
function fill_cart($n)
{
    $_SESSION['usces_cart'] = [serialize([11 => ['ABC' => 0]]) => ['quant' => $n], serialize([99 => ['MBFF' => 0]]) => ['quant' => 20]];
}
$_SESSION = [];
$GLOBALS['test_member'] = 1;
foreach ([1 => 6500, 5 => 6500, 6 => 5500, 11 => 5500, 12 => 5000] as $n => $expected) {
    fill_cart($n);
    agcm_assert('fixed ' . $n, $expected, agcm_member_price(11, 'ABC', 10000));
    agcm_assert('fixed MBFF ' . $n, 10000.0, agcm_member_price(99, 'MBFF', 10000));
}
$GLOBALS['test_member'] = 2;
agcm_assert('company rate 70', 7000, agcm_member_price(11, 'ABC', 10000));
$GLOBALS['test_member'] = 3;
agcm_assert('other unknown', null, agcm_member_price(11, 'ABC', 10000));
$bill = agcm_invoice_amounts(100000);
agcm_assert('fee 8%', 8000, $bill['fee']);
agcm_assert('service VAT 10%', 800, $bill['tax']);
$mix = agcm_invoice_order_amounts(['order_cart' => serialize([['price' => 100, 'quantity' => 1, 'tax_rate' => 8], ['price' => 100, 'quantity' => 1, 'tax_rate' => 10]])]);
agcm_assert('mixed groups rounded fee', 16, $mix['fee']);
agcm_assert('mixed tax', 2, $mix['tax']);
$GLOBALS['mock_orders'] = [
    ['ID' => 101, 'mem_id' => 1, 'order_date' => '2026-10-10 12:00:00', 'order_status' => 'new', 'order_cart' => serialize([['price' => 10000, 'quantity' => 1, 'tax_rate' => 8]])],
    ['ID' => 102, 'mem_id' => 2, 'order_date' => '2026-10-11 12:00:00', 'order_status' => 'new', 'order_cart' => serialize([['price' => 20000, 'quantity' => 1, 'tax_rate' => 10]])],
    ['ID' => 103, 'mem_id' => 3, 'order_date' => '2026-10-11 12:00:00', 'order_status' => 'new', 'order_cart' => serialize([['price' => 999, 'quantity' => 1]])],
    ['ID' => 104, 'mem_id' => 1, 'order_date' => '2026-10-11 12:00:00', 'order_status' => 'cancel', 'order_cart' => serialize([['price' => 999, 'quantity' => 1]])]
];
$data = agcm_invoice_data('2026-10');
agcm_assert('included orders', 2, count($data['rows']));
agcm_assert('company count', 1, $data['counts']['company']);
agcm_assert('fixed count', 1, $data['counts']['fixed']);
agcm_assert('unclassified', 1, $data['unclassified']);
agcm_assert('cancelled excluded', 1, $data['excluded']);
agcm_assert('invoice service fees', 2400, $data['fee']);
agcm_assert('invoice VAT', 240, $data['tax']);
echo "All isolated tests passed.\n";
