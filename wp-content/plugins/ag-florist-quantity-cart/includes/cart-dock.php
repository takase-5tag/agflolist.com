<?php

/** AG FLOLIST – fixed cart summary and WCEX Widget Cart slide-up drawer. */
if (!defined('ABSPATH')) {
  exit;
}

/** Scope: product archives and product detail. Cart itself has a full summary already. */
function agfl_dock_should_render()
{
  if (is_admin() || !agfl_step_user_allowed()) {
    return false;
  }
  if (is_page('trial')) {
    return true;
  }
  if (is_category()) {
    $term = get_queried_object();
    $item = get_category_by_slug('item');
    return $term && $item && isset($term->term_id) &&
      ((int)$term->term_id === (int)$item->term_id ||
        term_is_ancestor_of((int)$item->term_id, (int)$term->term_id, 'category'));
  }
  return is_singular('post') && agfl_step_product_is_item((int)get_queried_object_id());
}

/** The footer counter and detail rows MUST be read from one native session source. */
function agfl_dock_state()
{
  $snapshot = agfl_step_cart_snapshot();
  $cart = $snapshot['items'];
  $quantity = (int)$snapshot['total_quantity'];
  $subtotal = (float)$snapshot['subtotal'];
  $zero_price = false;
  foreach ($cart as $line) {
    if ((int)$line['quantity'] > 0 && (float)$line['price'] <= 0) {
      $zero_price = true;
      break;
    }
  }
  return compact('cart', 'quantity', 'subtotal', 'zero_price');
}

add_action('wp_enqueue_scripts', function () {
  if (!agfl_dock_should_render()) {
    return;
  }
  $base = plugin_dir_path(__DIR__) . 'assets/';
  $url = plugin_dir_url(__DIR__) . 'assets/';
  wp_enqueue_style('agfl-cart-dock', $url . 'cart-dock.css', array(), (string)filemtime($base . 'cart-dock.css'));
  wp_enqueue_script('agfl-cart-dock', $url . 'cart-dock.js', array(), (string)filemtime($base . 'cart-dock.js'), true);
}, 110);

add_action('wp_footer', function () {
  if (!agfl_dock_should_render()) {
    return;
  }
  $state = agfl_dock_state();
  $cart_url = defined('USCES_CART_URL') ? USCES_CART_URL : home_url('/usces-cart/');
  $visible = (int)$state['quantity'];
?>
  <aside id="agfl-cart-dock" class="agfl-cart-dock" aria-label="ショッピングカート" data-quantity="<?php echo esc_attr($visible); ?>">
    <div class="agfl-cart-dock__panel" id="agfl-cart-dock-panel" hidden>
      <div class="agfl-cart-dock__panel-head">
        <div><small>MY SHOPPING BAG</small>
          <h2>カートの中身</h2>
        </div>
        <button type="button" class="agfl-cart-dock__close" aria-label="カートの中身を閉じる" data-agfl-dock-close>×</button>
      </div>
      <div class="agfl-cart-dock__scroll">
        <section class="agfl-cart-dock__details" aria-label="カート明細">
          <div id="agfl-cart-dock-rows"><?php
                                        echo agfl_step_dock_lines_html($state['cart']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
                                        ?></div>
        </section>
        <p class="agfl-cart-dock__hint">数量の追加・削除は商品一覧の「＋・－」またはカート画面で行えます。</p>
        <p class="agfl-cart-dock__zero" data-agfl-dock-warning <?php if (!$state['zero_price']) : ?>hidden<?php endif; ?>>0円の商品が含まれています。ご注文前にカート価格をご確認ください。</p>
      </div>
      <a class="agfl-cart-dock__checkout" href="<?php echo esc_url($cart_url); ?>">カート画面へ進む <span aria-hidden="true">→</span></a>
    </div>
    <div class="agfl-cart-dock__bar">
      <div class="agfl-cart-dock__bar-inner">
        <div class="agfl-cart-dock__summary">
          <span class="agfl-cart-dock__bag" aria-hidden="true">▣</span>
          <span class="agfl-cart-dock__summary-label">カート <strong data-agfl-dock-quantity><?php echo esc_html(number_format_i18n($visible)); ?></strong>点</span>
          <span class="agfl-cart-dock__sep" aria-hidden="true"></span>
          <span class="agfl-cart-dock__subtotal-label">商品小計</span>
          <strong class="agfl-cart-dock__subtotal" data-agfl-dock-subtotal>¥<?php echo esc_html(number_format_i18n($state['subtotal'])); ?></strong>
        </div>
        <button type="button" class="agfl-cart-dock__toggle" data-agfl-dock-toggle aria-controls="agfl-cart-dock-panel" aria-expanded="false">カートの中身を見る <span aria-hidden="true">⌃</span></button>
      </div>
    </div>
  </aside>
<?php
}, 30);
