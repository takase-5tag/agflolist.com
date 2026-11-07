<?php

/** Shared card renderer for category.php and page-trial.php.
 * Required: $products, $heading, $ag_member_type, $ag_tansa_cart_count, $ag_is_trial.
 */
if (!defined('ABSPATH')) {
  exit;
}
?>
<?php foreach ($products as $product) :
  global $post, $usces;
  $post = $product;
  setup_postdata($post);
  $ag_post_id = (int) get_the_ID();
  if ($ag_is_trial && (!function_exists('agfl_trial_can_purchase') || !agfl_trial_can_purchase())) {
    continue;
  }
  if (function_exists('usces_the_item')) {
    usces_the_item();
  }
  $ag_tansa = function_exists('agcm_is_tansa_product')
    ? agcm_is_tansa_product($ag_post_id)
    : has_category('tansa_cate', $ag_post_id);
  $ag_show_tiers = (!$ag_is_trial && $ag_member_type === 'fixed' && $ag_tansa);

  $ag_subtitle = function_exists('get_field') ? get_field('sub_title', $ag_post_id) : '';
  $ag_intro = function_exists('get_field') ? get_field('line_text', $ag_post_id) : '';
  $ag_intro = is_string($ag_intro) ? wp_strip_all_tags($ag_intro) : '';
  if ($ag_intro === '') {
    $ag_intro = get_the_excerpt();
  }
  $ag_intro = trim(wp_strip_all_tags((string)$ag_intro));
  $ag_intro = wp_trim_words($ag_intro, 65, '…');

  $ag_image_html = '';
  if (function_exists('usces_the_itemImage')) {
    ob_start();
    usces_the_itemImage(0, 500, 500);
    $ag_image_html = trim((string) ob_get_clean());
    if (preg_match('~/(?:default|noimage)\.(?:png|jpe?g|webp)~i', $ag_image_html)) {
      $ag_image_html = '';
    }
  }
  if ($ag_image_html === '' && has_post_thumbnail()) {
    $ag_image_html = (string) get_the_post_thumbnail($ag_post_id, 'large', array('loading' => 'lazy'));
  }
  $ag_skus = function_exists('wel_get_skus') ? wel_get_skus($ag_post_id) : array();
  $ag_sku_count = is_array($ag_skus) ? count($ag_skus) : 0;
?>
  <article class="ag-list-product" id="ag-product-<?php echo $ag_post_id; ?>">
    <div class="ag-list-product__visual">
      <a href="<?php echo esc_url(get_permalink($ag_post_id)); ?>" aria-label="<?php echo esc_attr(get_the_title($ag_post_id)); ?>の詳細を見る">
        <?php if ($ag_image_html !== '') : ?>
          <?php echo wp_kses_post($ag_image_html); ?>
        <?php else : ?>
          <span class="ag-list-product__placeholder" aria-label="商品画像準備中">
            <span>AG FLOLIST</span><small>IMAGE COMING SOON</small>
          </span>
        <?php endif; ?>
      </a>
    </div>
    <div class="ag-list-product__content">
      <span class="ag-list-product__label"><?php echo esc_html($ag_is_trial ? 'FIRST ORDER / TRIAL' : $heading); ?> / AG FLOLIST</span>
      <h3><a href="<?php echo esc_url(get_permalink($ag_post_id)); ?>"><?php echo esc_html(get_the_title($ag_post_id)); ?></a></h3>
      <?php if (is_string($ag_subtitle) && trim($ag_subtitle) !== '') : ?>
        <p class="ag-list-product__subtitle"><?php echo esc_html($ag_subtitle); ?></p>
      <?php endif; ?>
      <?php if ($ag_intro !== '') : ?>
        <p class="ag-list-product__intro"><?php echo esc_html($ag_intro); ?></p>
      <?php endif; ?>
      <a class="ag-list-product__detail" href="<?php echo esc_url(get_permalink($ag_post_id)); ?>">商品の詳細を見る <span aria-hidden="true">↗</span></a>

      <div class="ag-list-product__purchase">
        <?php if ($ag_show_tiers) : ?>
          <div class="ag-list-tiers" aria-label="TANSA数量別販売掛け率">
            <div class="ag-list-tiers__heading">
              <strong>まとめ買いでお得に</strong>
              <small>カート内のTANSA合計：<span data-tansa-cart-count><?php echo (int)$ag_tansa_cart_count; ?></span>点</small>
            </div>
            <div class="ag-list-tiers__grid">
              <?php foreach (array(array(1, 5, 65, '1〜5点'), array(6, 11, 55, '6〜11点'), array(12, null, 50, '12点以上')) as $tier) :
                $ag_active = ($ag_tansa_cart_count >= $tier[0] && ($tier[1] === null || $ag_tansa_cart_count <= $tier[1]));
              ?>
                <div class="ag-list-tiers__item<?php echo $ag_active ? ' is-current' : ''; ?>" data-min="<?php echo (int)$tier[0]; ?>" data-max="<?php echo $tier[1] === null ? 999999 : (int)$tier[1]; ?>">
                  <small><?php echo esc_html($tier[3]); ?></small>
                  <strong><?php echo (int)$tier[2]; ?>%</strong>
                </div>
              <?php endforeach; ?>
            </div>
            <p class="ag-list-tiers__note">掛け率はカート内のTANSA合計数量で変わります。＋・－操作後に全対象商品の価格が更新されます。</p>
          </div>
        <?php elseif ($ag_member_type === 'company') : ?>
          <p class="ag-list-product__member-note">企業ごとに設定された契約価格を表示しています。</p>
        <?php endif; ?>

        <?php if ($ag_sku_count > 0 && function_exists('usces_have_skus')) : ?>
          <div class="ag-list-product__form">
            <?php
            $ag_rendered_skus = 0;
            while (usces_have_skus()) :
              $ag_rendered_skus++;
              if ($ag_rendered_skus > max(30, $ag_sku_count + 1)) {
                break;
              } // guard unexpected Welcart loop state
              $ag_sku = isset($usces->itemsku['code']) ? (string) $usces->itemsku['code'] : '';
              // Logged-out visitors: show only the Welcart SKU's normal sale price.
              // Contract prices/quantity discounts must not leak into public view.
              $ag_base = null;
              if ($ag_member_type === 'guest') {
                if (isset($usces->itemsku['price']) && is_numeric($usces->itemsku['price'])) {
                  $ag_base = (float) $usces->itemsku['price'];
                }
              } else {
                $ag_base = $ag_sku !== '' && function_exists('agcm_sku_base') ? agcm_sku_base($ag_post_id, $ag_sku) : null;
                if ($ag_base === null && isset($usces->itemsku['price']) && is_numeric($usces->itemsku['price'])) {
                  $ag_base = (float) $usces->itemsku['price'];
                }
              }
              $ag_effective = $ag_base;
              if ($ag_member_type !== 'guest' && $ag_base !== null && $ag_sku !== '' && function_exists('agcm_member_price')) {
                $ag_computed = agcm_member_price($ag_post_id, $ag_sku, $ag_base);
                if ($ag_computed !== null) {
                  $ag_effective = (float) $ag_computed;
                }
              }
              $ag_valid_price = $ag_base !== null && $ag_base > 0 && $ag_effective !== null && $ag_effective > 0;
              $ag_discount = $ag_valid_price && $ag_effective < $ag_base;
              $ag_sku_label = isset($usces->itemsku['name']) ? (string)$usces->itemsku['name'] : '';
            ?>
              <div class="ag-list-product__sku">
                <?php if ($ag_sku_count > 1 && $ag_sku_label !== '') : ?>
                  <p class="ag-list-product__sku-label"><?php echo esc_html($ag_sku_label); ?></p>
                <?php endif; ?>
                <?php if ($ag_valid_price) : ?>
                  <div class="ag-list-product__price-block">
                    <span class="ag-list-product__price-title"><?php echo $ag_member_type === 'guest' ? '一般販売価格' : ($ag_is_trial ? '初回限定価格' : (($ag_member_type === 'company') ? '企業契約価格' : (($ag_show_tiers) ? '会員価格（現在の数量段階）' : '販売価格'))); ?></span>
                    <div class="ag-list-product__price-line">
                      <?php if ($ag_discount) : ?><del>¥<?php echo esc_html(number_format_i18n($ag_base)); ?></del><?php endif; ?>
                      <strong data-stepper-price>¥<?php echo esc_html(number_format_i18n($ag_effective)); ?></strong><small>（税別）</small>
                    </div>
                  </div>
                  <div class="ag-list-product__actions ag-list-product__actions--stepper">
                    <?php if (function_exists('agfl_step_supports_sku') && agfl_step_supports_sku($ag_post_id, $ag_sku) && function_exists('agfl_step_user_allowed') && agfl_step_user_allowed()) :
                      $ag_existing_qty = function_exists('agfl_step_find_row') ? agfl_step_find_row($ag_post_id, $ag_sku)[1] : 0;
                    ?>
                      <div class="ag-stepper" data-post-id="<?php echo (int)$ag_post_id; ?>"
                        data-sku="<?php echo esc_attr($ag_sku); ?>" data-tansa="<?php echo $ag_tansa ? '1' : '0'; ?>">
                        <span class="ag-stepper__label">カート数量</span>
                        <div class="ag-stepper__controls" role="group" aria-label="<?php echo esc_attr(get_the_title($ag_post_id)); ?> の数量変更">
                          <button type="button" data-stepper-op="minus" aria-label="1点減らす" <?php disabled($ag_existing_qty < 1); ?>>−</button>
                          <output data-stepper-number aria-live="polite"><?php echo (int)$ag_existing_qty; ?></output>
                          <button type="button" data-stepper-op="plus" aria-label="1点増やす">＋</button>
                        </div>
                      </div>
                      <p class="ag-stepper__help">＋・－で自動的にカートに反映されます</p>
                    <?php elseif ($ag_member_type === 'guest') : ?>
                      <a class="ag-guest-product-button" href="<?php echo esc_url(get_permalink($ag_post_id)); ?>">商品詳細からカートに入れる <span aria-hidden="true">→</span></a>
                    <?php else : ?>
                      <p class="ag-list-product__price-error">この商品は<a href="<?php echo esc_url(get_permalink($ag_post_id)); ?>">商品詳細ページ</a>からご購入ください。</p>
                    <?php endif; ?>
                  </div>
                <?php else : ?>
                  <p class="ag-list-product__price-error">価格を取得できません。<a href="<?php echo esc_url(get_permalink($ag_post_id)); ?>">商品詳細でご確認ください</a>。</p>
                <?php endif; ?>
              </div>
            <?php endwhile; ?>
          </div>
        <?php else : ?>
          <p class="ag-list-product__price-error">購入は<a href="<?php echo esc_url(get_permalink($ag_post_id)); ?>">商品詳細ページ</a>からお願いいたします。</p>
        <?php endif; ?>
      </div>
    </div>
  </article>
<?php endforeach;
wp_reset_postdata(); ?>