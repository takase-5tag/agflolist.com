<?php
// Lightweight non-WordPress contract test for +/- + TANSA tier repricing.
define('ABSPATH', __DIR__);
$_SESSION = array('usces_cart' => array(), 'usces_entry' => array());
$member = 'fixed';
$prices = array(100 => 10000, 200 => 15000);
function add_filter() {}
function add_action() {}
function get_post_status($id)
{
  return 'publish';
}
function get_post_mime_type($id)
{
  return 'item';
}
function get_category_by_slug($x)
{
  return (object)['term_id' => 2];
}
function is_wp_error($x)
{
  return false;
}
function usces_get_opts($id, $o)
{
  return array();
}
function agcm_sku_base($pid, $sku)
{
  global $prices;
  return $prices[$pid] ?? null;
}
function agcm_current_member_type()
{
  global $member;
  return $member;
}
function agcm_serial_item($serial)
{
  $a = unserialize($serial);
  $id = array_key_first($a);
  $sku = array_key_first($a[$id]);
  return [$id, $sku];
}
function agcm_tansa_cart_quantity()
{
  $n = 0;
  foreach ($_SESSION['usces_cart'] as $key => $row) {
    $item = agcm_serial_item($key);
    if ($item[0] == 100) $n += $row['quant'];
  }
  return $n;
}
function agcm_member_price($pid, $sku, $base)
{
  global $member;
  if ($member === 'company') return floor($base * .7);
  if ($pid !== 100) return $base;
  $qty = agcm_tansa_cart_quantity();
  return floor($base * (($qty >= 12) ? 0.50 : (($qty >= 6) ? 0.55 : 0.65)));
}
class StubCart
{
  public $serial;
  function inCart()
  {
    $id = array_key_first($_POST['inCart']);
    $sku = array_key_first($_POST['inCart'][$id]);
    $serial = serialize(array($id => array($sku => 0)));
    $this->serial = $serial;
    $_SESSION['usces_cart'][$serial] = array('quant' => (int)$_POST['quant'][$id][$sku], 'price' => agcm_sku_base($id, $sku));
  }
  function get_cart()
  {
    $ret = [];
    foreach ($_SESSION['usces_cart'] as $serial => $row) {
      $item = agcm_serial_item($serial);
      $ret[] = array('post_id' => $item[0], 'sku' => $item[1], 'quantity' => $row['quant'], 'price' => $row['price']);
    }
    return $ret;
  }
}
class StubUsces
{
  public $cart;
  function __construct()
  {
    $this->cart = new StubCart();
  }
  function getItemZaikoStatusId()
  {
    return 0;
  }
}
$usces = new StubUsces();
require __DIR__ . '/ag-FLOLIST-quantity-cart/ag-FLOLIST-quantity-cart.php';
function assert_eq($actual, $expect, $context)
{
  if ($actual !== $expect) {
    fwrite(STDERR, "FAIL $context expected " . var_export($expect, true) . " actual " . var_export($actual, true) . "\n");
    exit(1);
  }
  echo "PASS $context\n";
}
$serial = serialize([100 => ['SKU-T' => 0]]);
$serial_b = serialize([200 => ['SKU-M' => 0]]);
$_SESSION['usces_cart'][$serial] = array('quant' => 5, 'price' => 0);
assert_eq(agfl_step_find_row(100, 'SKU-T')[1], 5, 'detect native serial & qty');
assert_eq(agfl_step_reprice(), true, 'reprice 5x TANSA');
assert_eq($_SESSION['usces_cart'][$serial]['price'], 6500.0, '5x TANSA rate 65');
$_SESSION['usces_cart'][$serial]['quant'] = 6;
agfl_step_reprice();
assert_eq($_SESSION['usces_cart'][$serial]['price'], 5500.0, '6x TANSA rate 55');
$_SESSION['usces_cart'][$serial]['quant'] = 12;
agfl_step_reprice();
assert_eq($_SESSION['usces_cart'][$serial]['price'], 5000.0, '12x TANSA rate 50');
$_SESSION['usces_cart'][$serial_b] = array('quant' => 2, 'price' => 0);
agfl_step_reprice();
assert_eq($_SESSION['usces_cart'][$serial_b]['price'], 15000, 'MBFF 100 percent fixed');
$member = 'company';
agfl_step_reprice();
assert_eq($_SESSION['usces_cart'][$serial]['price'], 7000.0, 'company rate overrides TANSA quantities');
assert_eq(agfl_step_supports_sku(100, 'SKU-T'), true, 'valid published product');
assert_eq(agfl_step_supports_sku(900, 'MISSING'), false, 'missing item blocked');
