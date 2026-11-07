<?php

/**
 * Template Name: ジャーナル
 * /journal/ は固定ページ。CPT ag_journal の記事のみを表示する。
 */
get_header();
$ag17_image = get_template_directory_uri() . '/assets/ag-editorial/';
$ag17_current_topic = isset($_GET['journal_category']) ? sanitize_title(wp_unslash($_GET['journal_category'])) : '';
$ag17_topics = array(
  'salon-management' => 'サロン経営',
  'beauty-health' => '美容と健康',
  'products' => '商品を知る',
  'people-work' => '人と仕事',
  'news' => 'お知らせ',
);
if ($ag17_current_topic && !isset($ag17_topics[$ag17_current_topic])) $ag17_current_topic = '';
$ag17_current_page = isset($_GET['ag_page']) ? max(1, absint(wp_unslash($_GET['ag_page']))) : 1;
$ag17_featured = null;
$ag17_exclude = array();
if (!$ag17_current_topic) {
  $ag17_feature_query = new WP_Query(array(
    'post_type' => 'ag_journal',
    'post_status' => 'publish',
    'posts_per_page' => 1,
    'meta_key' => '_ag17_featured',
    'meta_value' => '1',
    'no_found_rows' => true,
    'ignore_sticky_posts' => true,
  ));
  if ($ag17_feature_query->have_posts()) {
    $ag17_featured = ($ag17_current_page === 1) ? $ag17_feature_query->posts[0] : null;
    $ag17_exclude[] = (int)$ag17_feature_query->posts[0]->ID;
  }
  wp_reset_postdata();
}
$ag17_args = array(
  'post_type' => 'ag_journal',
  'post_status' => 'publish',
  'posts_per_page' => 9,
  'paged' => $ag17_current_page,
  'post__not_in' => $ag17_exclude,
  'ignore_sticky_posts' => true,
);
if ($ag17_current_topic) $ag17_args['tax_query'] = array(array('taxonomy' => 'ag_journal_topic', 'field' => 'slug', 'terms' => $ag17_current_topic));
$ag17_articles = new WP_Query($ag17_args);
?>
<main class="ag-editorial-v17 ag-journal-v17" id="main-content">
  <section class="ag17-hero ag17-hero--journal" aria-labelledby="ag17-journal-title">
    <img class="ag17-hero__picture" src="<?php echo esc_url($ag17_image . 'journal-hero.webp'); ?>" alt="本の上に置かれたマグカップ" width="508" height="315" fetchpriority="high">
    <div class="ag17-shell ag17-hero__content">
      <p class="ag17-overline">JOURNAL</p>
      <h1 id="ag17-journal-title">ジャーナル</h1>
      <p class="ag17-hero__subtitle">美しさの、その先にあるもの。</p>
      <p class="ag17-hero__description">美容・サロン経営・商品・働き方など、<br>日々の仕事と暮らしが少し豊かになる<br>ヒントをお届けします。</p>
    </div>
  </section>

  <div class="ag17-shell ag17-journal-main">
    <nav class="ag17-journal-filter" aria-label="記事カテゴリー">
      <a class="<?php echo !$ag17_current_topic ? 'is-current' : ''; ?>" <?php echo !$ag17_current_topic ? 'aria-current="page"' : ''; ?> href="<?php echo esc_url(get_permalink()); ?>">すべて</a>
      <?php foreach ($ag17_topics as $slug => $name) : ?>
        <a class="<?php echo $ag17_current_topic === $slug ? 'is-current' : ''; ?>" <?php echo $ag17_current_topic === $slug ? 'aria-current="page"' : ''; ?> href="<?php echo esc_url(add_query_arg('journal_category', $slug, get_permalink())); ?>"><?php echo esc_html($name); ?></a>
      <?php endforeach; ?>
    </nav>

    <?php if ($ag17_featured instanceof WP_Post) :
      $ag17_feature_terms = get_the_terms($ag17_featured, 'ag_journal_topic');
      $ag17_feature_term = ($ag17_feature_terms && !is_wp_error($ag17_feature_terms)) ? $ag17_feature_terms[0]->name : 'ジャーナル';
    ?>
      <article class="ag17-feature">
        <a class="ag17-feature__image" href="<?php echo esc_url(get_permalink($ag17_featured)); ?>">
          <?php if (has_post_thumbnail($ag17_featured)) echo get_the_post_thumbnail($ag17_featured, 'large', array('loading' => 'eager')); ?>
        </a>
        <div class="ag17-feature__body">
          <p class="ag17-overline">FEATURE</p>
          <h2><a href="<?php echo esc_url(get_permalink($ag17_featured)); ?>"><?php echo esc_html(get_the_title($ag17_featured)); ?></a></h2>
          <p><?php echo esc_html(get_the_excerpt($ag17_featured)); ?></p>
          <a class="ag17-text-link" href="<?php echo esc_url(get_permalink($ag17_featured)); ?>">記事を読む <span aria-hidden="true">›</span></a>
        </div>
      </article>
    <?php endif; ?>

    <?php if ($ag17_articles->have_posts()) : ?>
      <div class="ag17-journal-grid">
        <?php while ($ag17_articles->have_posts()) : $ag17_articles->the_post();
          $ag17_card_terms = get_the_terms(get_the_ID(), 'ag_journal_topic');
          $ag17_card_term = ($ag17_card_terms && !is_wp_error($ag17_card_terms)) ? $ag17_card_terms[0]->name : 'ジャーナル';
        ?>
          <article <?php post_class('ag17-journal-card'); ?>>
            <a class="ag17-journal-card__image" href="<?php the_permalink(); ?>">
              <?php if (has_post_thumbnail()) {
                the_post_thumbnail('medium_large', array('loading' => 'lazy'));
              } else {
                echo '<span class="ag17-journal-card__placeholder">AG FLOLIST</span>';
              } ?>
            </a>
            <div class="ag17-journal-card__meta"><span><?php echo esc_html($ag17_card_term); ?></span></div>
            <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
            <time datetime="<?php echo esc_attr(get_the_date('Y-m-d')); ?>"><?php echo esc_html(get_the_date('Y.m.d')); ?></time>
          </article>
        <?php endwhile;
        wp_reset_postdata(); ?>
      </div>
      <?php
      $ag17_pagination = paginate_links(array(
        'total' => $ag17_articles->max_num_pages,
        'current' => $ag17_current_page,
        'base' => trailingslashit(get_permalink()) . '%_%',
        'format' => '?ag_page=%#%',
        'add_args' => $ag17_current_topic ? array('journal_category' => $ag17_current_topic) : false,
        'prev_text' => '‹ 前へ',
        'next_text' => '次へ ›',
        'type' => 'list',
      ));
      if ($ag17_pagination) : ?>
        <nav class="ag17-journal-pagination" aria-label="記事一覧のページ移動"><?php echo wp_kses_post($ag17_pagination); ?></nav>
      <?php endif; ?>
    <?php else : ?>
      <p class="ag17-journal-empty">このカテゴリーの記事は準備中です。</p>
    <?php endif; ?>

    <section class="ag17-partner-cta" aria-labelledby="ag17-partner-title">
      <img src="<?php echo esc_url($ag17_image . 'journal-partner.webp'); ?>" alt="やわらかな光と植物の影" loading="lazy" width="331" height="207">
      <div>
        <p class="ag17-overline">FOR SALON PARTNERS</p>
        <h2 id="ag17-partner-title">サロンの未来を、一緒につくる。</h2>
        <p>商品選びから提案支援まで、AG FLOLISTのサポートをご紹介します。</p>
        <a class="ag17-outline-btn" href="<?php echo esc_url(home_url('/salon/')); ?>">サロン向けサービスを見る <span aria-hidden="true">›</span></a>
      </div>
    </section>
  </div>
</main>
<?php get_footer(); ?>