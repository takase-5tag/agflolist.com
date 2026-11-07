<?php

/**
 * AG FLOLIST - Product category archive (Welcart)
 * File: /wp-content/themes/welcart_mbff/category.php
 *
 * 商品(item)カテゴリー配下は、指定の5カテゴリーをスラッグ順ではなく管理順で表示。
 * 商品以外のカテゴリーは通常の投稿一覧を表示します。
 */
$current_term = get_queried_object();
$item_root    = get_category_by_slug('item');
$is_product_category = $current_term instanceof WP_Term
  && $current_term->taxonomy === 'category'
  && $item_root instanceof WP_Term
  && (
    (int) $current_term->term_id === (int) $item_root->term_id
    || term_is_ancestor_of((int) $item_root->term_id, (int) $current_term->term_id, 'category')
  );

// 商品カテゴリーのときだけ専用CSSを、既存テーマCSSより後に読み込みます。
if ($is_product_category) {
  add_action('wp_enqueue_scripts', function () {
    // Support installation in either /css/ (recommended) or the theme root.
    $candidates = array('/css/ag-category-premium.css', '/ag-category-premium.css');
    foreach ($candidates as $relative) {
      $path = get_stylesheet_directory() . $relative;
      if (!is_file($path)) {
        continue;
      }
      wp_enqueue_style(
        'ag-category-premium',
        get_stylesheet_directory_uri() . $relative,
        array(),
        (string)filemtime($path)
      );
      return;
    }
    // Never enqueue a non-existent URL (prevents the previous 404).
    if (defined('WP_DEBUG') && WP_DEBUG) {
      error_log('AG FLOLIST: ag-category-premium.css missing from theme /css/ and root.');
    }
  }, 999);
  add_action('wp_enqueue_scripts', function () {
    $file = get_stylesheet_directory() . '/css/ag-shop-list-adjustments.css';
    if (is_file($file)) {
      wp_enqueue_style(
        'ag-shop-list-adjustments',
        get_stylesheet_directory_uri() . '/css/ag-shop-list-adjustments.css',
        array('ag-category-premium'),
        (string)filemtime($file)
      );
    }
  }, 1000);
  // 商品一覧用CSS: 表示整理（カート処理は既存のまま）。
  // ファイルが存在する場合のみ読み込むため、未配置時にも404になりません。
  add_action('wp_enqueue_scripts', function () {
    $relative = '/css/ag-category-editorial.css';
    $file = get_stylesheet_directory() . $relative;
    if (is_file($file)) {
      wp_enqueue_style(
        'ag-category-editorial',
        get_stylesheet_directory_uri() . $relative,
        array(),
        (string) filemtime($file)
      );
    }
  }, 1100);
  add_action('wp_enqueue_scripts', function () {
    $relative = '/js/ag-category-clean.js';
    $file = get_stylesheet_directory() . $relative;
    if (is_file($file)) {
      wp_enqueue_script(
        'ag-category-clean',
        get_stylesheet_directory_uri() . $relative,
        array(),
        (string) filemtime($file),
        true
      );
    }
  }, 1101);
  // 5カテゴリー表示時のナビと、登録前カテゴリーの表示用スタイル。
  add_action('wp_enqueue_scripts', function () {
    $relative = '/css/ag-category-order-v11.css';
    $path = get_stylesheet_directory() . $relative;
    if (is_file($path)) {
      wp_enqueue_style(
        'ag-category-order-v11',
        get_stylesheet_directory_uri() . $relative,
        array(),
        (string) filemtime($path)
      );
    }
  }, 1120);
}
get_header();

if ($is_product_category) :
  $is_root_page = (int)$current_term->term_id === (int)$item_root->term_id;
  // 先方指定の順番。IDは環境ごとに異なるためスラッグで取得します。
  // 0件のカテゴリーも見出しとナビを表示し、商品登録後は自動で一覧に出します。
  $category_order = array(
    array('slug' => 'usually',               'heading' => 'TANSA'),
    array('slug' => 'mbff_cate',            'heading' => 'MBFF'),
    array('slug' => 'mbff_men',             'heading' => 'MBFF Men'),
    array('slug' => 'mbff_for_professional', 'heading' => 'MBFF for Professional'),
    array('slug' => 'others',               'heading' => 'その他'),
  );
  if ($is_root_page) {
    $catalog_order = $category_order;
  } else {
    // 子カテゴリーを開いた場合は、そのカテゴリーだけを表示します。
    $catalog_order = array(array(
      'slug' => (string) $current_term->slug,
      'heading' => in_array((string) $current_term->slug, array('usually', 'tansa_cate'), true) ? 'TANSA' : (string) $current_term->name,
    ));
  }
  $sections = array();
  $already_shown = array();
  foreach ($catalog_order as $settings) {
    $term = get_term_by('slug', $settings['slug'], 'category');
    // 以前の「TANSA」カテゴリー名だった環境用のフォールバック。
    if ((!$term instanceof WP_Term || is_wp_error($term)) && $settings['slug'] === 'usually') {
      $term = get_term_by('slug', 'tansa_cate', 'category');
    }
    if (!$term instanceof WP_Term || is_wp_error($term)) {
      continue;
    }
    // Restrict product listing to descendants of the main item category.
    if (
      (int)$term->term_id !== (int)$item_root->term_id
      && !term_is_ancestor_of((int)$item_root->term_id, (int)$term->term_id, 'category')
    ) {
      continue;
    }
    $posts = get_posts(array(
      'post_type' => 'post',
      'post_status' => 'publish',
      'posts_per_page' => -1,
      'orderby' => 'title',
      'order' => 'ASC',
      'suppress_filters' => false,
      'tax_query' => array(array(
        'taxonomy' => 'category',
        'field' => 'term_id',
        'terms' => array((int)$term->term_id),
        'include_children' => false
      ))
    ));
    $products = array();
    foreach ((array)$posts as $candidate) {
      if (!$candidate instanceof WP_Post) {
        continue;
      }
      if (isset($already_shown[(int)$candidate->ID])) {
        continue;
      }
      // Never show trial-exclusive products in the ordinary catalogue.
      // Fail closed for trial-category items even if the trial plugin is temporarily inactive.
      $candidate_categories = wp_get_post_categories((int)$candidate->ID);
      $trial_by_id = is_array($candidate_categories) && !is_wp_error($candidate_categories)
        && (bool)array_intersect(array_map('intval', $candidate_categories), array(9, 11, 12, 13));
      if ($trial_by_id || (function_exists('agfl_trial_is_product') && agfl_trial_is_product((int)$candidate->ID))) {
        continue;
      }
      if (function_exists('wel_get_skus')) {
        $rows = wel_get_skus((int)$candidate->ID);
        if (!is_array($rows) || !$rows) {
          continue;
        }
      }
      $products[] = $candidate;
      $already_shown[(int)$candidate->ID] = true;
    }
    $sections[] = array(
      'term' => $term,
      'products' => $products,
      'heading' => $settings['heading'],
      'anchor' => $settings['slug']
    );
  }
  // Mode is defined from Welcart login state; a guest is not a third membership.
  $ag_member_type = function_exists('agfl_v13_purchase_type')
    ? agfl_v13_purchase_type()
    : (function_exists('agcm_current_member_type') ? agcm_current_member_type() : '');
  $ag_tansa_cart_count = ($ag_member_type === 'fixed' && function_exists('agcm_tansa_cart_quantity'))
    ? (int)agcm_tansa_cart_quantity() : 0;
  $ag_is_trial = false;
?>
  <main id="main" class="ag-product-category ag-product-category--premium">
    <div class="ag-list-container">
      <header class="ag-list-header">
        <span class="ag-list-eyebrow">AG FLOLIST / PRODUCT COLLECTION</span>
        <h1><?php echo esc_html($is_root_page ? '商品一覧' : (in_array((string) $current_term->slug, array('usually', 'tansa_cate'), true) ? 'TANSA' : $current_term->name)); ?></h1>
        <p>美容と健やかな毎日を支える、厳選した商品をご紹介します。</p>
        <?php if ($ag_member_type === 'guest') : ?>
          <div class="ag-guest-banner" role="note">
            <div><strong>会員登録なしでお買い物いただけます</strong>
              <p>通常販売価格でご注文いただけます。ご購入時にお名前・お届け先・連絡先をご入力ください。</p>
            </div>
            <a class="ag-guest-banner__link" href="<?php echo esc_url(home_url('/usces-member/')); ?>">お取引サロン様の会員ログイン <span aria-hidden="true">↗</span></a>
          </div>
        <?php endif; ?>
      </header>
      <?php if ($is_root_page && function_exists('agfl_trial_can_purchase') && agfl_trial_can_purchase()) : ?>
        <p class="ag-list-trial-link"><a href="<?php echo esc_url(home_url('/trial/')); ?>">初回限定トライアル商品はこちら <span aria-hidden="true">→</span></a></p>
      <?php endif; ?>
      <?php if (!empty($sections)) : ?>
        <?php if (function_exists('agfl_step_user_allowed') && agfl_step_user_allowed()) : ?>
          <div class="ag-stepper-summary" aria-live="polite">
            <span>現在のカート：<strong data-stepper-total-items><?php
                                                          $count = 0;
                                                          foreach ((array)($_SESSION['usces_cart'] ?? array()) as $row) {
                                                            $count += max(0, (int)($row['quant'] ?? 0));
                                                          }
                                                          echo (int)$count;
                                                          ?></strong>点</span>
            <a href="<?php echo esc_url(defined('USCES_CART_URL') ? USCES_CART_URL : home_url('/usces-cart/')); ?>">カートを確認する →</a>
          </div>
        <?php endif; ?>
        <nav class="ag-list-nav ag-list-nav--ordered" aria-label="商品カテゴリーから探す">
          <span class="ag-list-nav__label">CATEGORY</span>
          <div class="ag-list-nav__links">
            <?php foreach ($sections as $section) : ?>
              <a href="#<?php echo esc_attr($section['anchor']); ?>"><?php echo esc_html($section['heading']); ?><span aria-hidden="true">↘</span></a>
            <?php endforeach; ?>
          </div>
        </nav>
        <?php foreach ($sections as $section_index => $section) :
          $term = $section['term'];
          $heading = $section['heading'];
          $products = $section['products']; ?>
          <?php
          // TANSA（旧『通常』）だけ既存の1列スタイルを維持します。
          // そのほかは既存の2列商品カード（レスポンシブ）を使用します。
          $ag_layout_class = in_array((string)$term->slug, array('usually', 'tansa_cate'), true)
            ? 'ag-list-section--tansa' : 'ag-list-section--grid';
          ?>
          <section class="ag-list-section <?php echo esc_attr($ag_layout_class); ?>" id="<?php echo esc_attr($section['anchor']); ?>" aria-labelledby="ag-section-<?php echo (int)$term->term_id; ?>" data-category-id="<?php echo (int)$term->term_id; ?>">
            <?php if ($term->slug !== $section['anchor']) : ?><span class="ag-list-anchor-alias" id="<?php echo esc_attr($term->slug); ?>" aria-hidden="true"></span><?php endif; ?>
            <?php // トップの既存リンク #tansa_cate を維持。 
            ?>
            <?php if ($section['anchor'] === 'usually' && $term->slug !== 'tansa_cate') : ?><span class="ag-list-anchor-alias" id="tansa_cate" aria-hidden="true"></span><?php endif; ?>
            <header class="ag-list-section__head">
              <div class="ag-list-section__heading">
                <span class="ag-list-section__num"><?php echo esc_html(sprintf('%02d', $section_index + 1)); ?></span>
                <h2 id="ag-section-<?php echo (int)$term->term_id; ?>"><?php echo esc_html($heading); ?></h2>
              </div><small><?php echo esc_html(count($products)); ?> ITEMS</small>
            </header>
            <div class="ag-list-items">
              <?php if (empty($products)) : ?>
                <p class="ag-list-empty ag-list-empty--category">現在、こちらのカテゴリーの商品を準備中です。</p>
              <?php else : ?>
                <?php include get_stylesheet_directory() . '/template-parts/ag-shop-product-cards.php'; ?>
              <?php endif; ?>
            </div>
          </section>
        <?php endforeach; ?>
      <?php else : ?><div class="ag-list-empty">現在、掲載中の商品はありません。</div><?php endif; ?>
    </div>
  </main>
<?php else : ?>
  <!-- 「お知らせ」など、商品以外のカテゴリーは通常の投稿一覧を維持 -->
  <main id="main" class="ag-product-category ag-product-category--news">
    <div class="ag-product-category__inner">
      <header class="ag-product-category__header">
        <span class="ag-product-category__eyebrow">JOURNAL & NEWS</span>
        <h1 class="ag-product-category__title"><?php single_cat_title(); ?></h1>
      </header>
      <?php if (have_posts()) : ?>
        <ul class="ag-product-category__news-list">
          <?php while (have_posts()) : the_post(); ?>
            <li><a href="<?php echo esc_url(get_permalink()); ?>">
                <time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date('Y.m.d')); ?></time>
                <strong><?php echo esc_html(get_the_title()); ?></strong>
                <span aria-hidden="true">→</span>
              </a></li>
          <?php endwhile; ?>
        </ul>
        <div class="ag-product-category__pagination"><?php the_posts_pagination(array('mid_size' => 1, 'prev_text' => '前へ', 'next_text' => '次へ')); ?></div>
      <?php else : ?>
        <p class="ag-product-category__empty">現在、記事はありません。</p>
      <?php endif; ?>
    </div>
  </main>
<?php endif; ?>

<?php get_footer(); ?>