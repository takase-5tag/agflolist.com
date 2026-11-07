<?php
/** 初回限定トライアル専用ページ。対象判定はサーバー側の既存処理を使用。 */
if (!defined('ABSPATH')) { exit; }
// The membership plugin performs the redirect at template_redirect, before any HTML is sent.
// Fail closed if the trial guard is not active or if this is not an eligible account.
if (!function_exists('agfl_trial_can_purchase') || !agfl_trial_can_purchase()) {
    wp_safe_redirect(home_url('/category/item/'),302);
    exit;
}
add_action('wp_enqueue_scripts', function () {
    $relative='/css/ag-category-premium.css';
    $path=get_stylesheet_directory().$relative;
    if (is_file($path)) { wp_enqueue_style('ag-category-premium',get_stylesheet_directory_uri().$relative,array(),(string)filemtime($path)); }
    $relative='/css/ag-trial.css'; $path=get_stylesheet_directory().$relative;
    if (is_file($path)) { wp_enqueue_style('ag-trial',get_stylesheet_directory_uri().$relative,array('ag-category-premium'),(string)filemtime($path)); }
},999);
$trial_ids=function_exists('agfl_trial_category_ids') ? agfl_trial_category_ids() : array();
$products=$trial_ids ? get_posts(array('post_type'=>'post','post_status'=>'publish',
    'posts_per_page'=>-1,'orderby'=>'title','order'=>'ASC','suppress_filters'=>false,
    'tax_query'=>array(array('taxonomy'=>'category','field'=>'term_id',
        'terms'=>$trial_ids,'include_children'=>true,'operator'=>'IN')))) : array();
$products=array_values(array_filter((array)$products, static function ($candidate) {
    if (!$candidate instanceof WP_Post || !agfl_trial_is_product($candidate->ID)) { return false; }
    if (function_exists('wel_get_skus')) { $skus=wel_get_skus($candidate->ID); return is_array($skus) && count($skus)>0; }
    return true;
}));
$ag_member_type='fixed';
$ag_is_trial=true;
$ag_tansa_cart_count=0;
$heading='TRIAL';
get_header();
?>
<main id="main" class="ag-product-category ag-product-category--premium ag-product-category--trial">
  <div class="ag-list-container">
    <header class="ag-list-header ag-trial-header">
      <span class="ag-list-eyebrow">EXCLUSIVE TRIAL OFFER</span>
      <h1>初回限定トライアル</h1>
      <p>気になる商品を、初回限定の特別価格で。<br class="ag-list-break-pc">トライアル商品のご注文が初めての方にご利用いただけます。</p>
    </header>
    <div class="ag-trial-notice" role="note"><strong>初回限定のご案内</strong><span>こちらのトライアル商品は、初回のご注文に限りご購入いただけます。対象商品をご注文済みの場合はご利用いただけません。</span></div>
    <?php if (function_exists('agfl_step_user_allowed') && agfl_step_user_allowed()) : ?>
      <div class="ag-stepper-summary" aria-live="polite">
        <span>現在のカート：<strong data-stepper-total-items><?php $total=0; foreach ((array)($_SESSION['usces_cart']??array()) as $row){$total+=max(0,(int)($row['quant']??0));} echo (int)$total; ?></strong>点</span>
        <a href="<?php echo esc_url(defined('USCES_CART_URL')?USCES_CART_URL:home_url('/usces-cart/')); ?>">カートを確認する →</a>
      </div>
    <?php endif; ?>
    <section class="ag-list-section" id="trial" aria-labelledby="ag-trial-list-title">
      <header class="ag-list-section__head"><div class="ag-list-section__heading"><span class="ag-list-section__num">01</span><h2 id="ag-trial-list-title">トライアル商品</h2></div><small><?php echo esc_html(count($products)); ?> ITEMS</small></header>
      <div class="ag-list-items">
        <?php if ($products) : ?>
          <?php
          /**
           * 既存の商品カード・カート数量操作をそのまま利用しながら、
           * このページに限り詳細へのリンクを出力しない。
           * サイト全体で共有しているカード部品は変更しない。
           */
          $card_template = get_stylesheet_directory() . '/template-parts/ag-shop-product-cards.php';
          if (is_readable($card_template)) {
              ob_start();
              include $card_template;
              $trial_cards_html = ob_get_clean();

              // 商品詳細リンクを除去。画像と商品名は文字・画像のみ残す。
              // カート操作用のbuttonや各種data属性には手を加えない。
              $trial_cards_html = preg_replace_callback(
                  '~<a\b[^>]*>(.*?)</a>~si',
                  static function ($match) {
                      if (strpos($match[0], 'ag-list-product__detail') !== false) {
                          return '';
                      }
                      return $match[1];
                  },
                  $trial_cards_html
              );

              // 管理用の区分名が共通部品のテキストに含まれていても表示しない。
              // 数量変更に必要な商品ID・SKUなどのHTML属性値は書き換えない。
              $private_labels = array(
                  '②' . '固定' . 'URL' . '会員',
                  '固定' . 'URL' . '会員',
                  '固定' . '会員',
                  '会員' . '区分' . '②',
              );
              $trial_cards_html = preg_replace_callback(
                  '~(?<=>)[^<]+(?=<)~u',
                  static function ($match) use ($private_labels) {
                      return str_replace($private_labels, '会員さま', $match[0]);
                  },
                  $trial_cards_html
              );
              echo $trial_cards_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
          } else {
              echo '<p class="ag-list-empty">商品一覧を表示できませんでした。</p>';
          }
          ?>
        <?php else : ?><p class="ag-list-empty">現在、初回限定商品はありません。</p><?php endif; ?>
      </div>
    </section>
    <p class="ag-trial-catalog-link"><a href="<?php echo esc_url(home_url('/category/item/')); ?>">通常の商品一覧を見る →</a></p>
  </div>
</main>
<?php get_footer(); ?>
