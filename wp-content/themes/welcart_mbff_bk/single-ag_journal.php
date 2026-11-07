<?php

/** AG FLOLIST ジャーナル記事詳細 */
get_header();
?>
<main class="ag-editorial-v17 ag17-article-page" id="main-content">
  <?php while (have_posts()) : the_post();
    $topics = get_the_terms(get_the_ID(), 'ag_journal_topic');
    $category = ($topics && !is_wp_error($topics)) ? $topics[0]->name : 'ジャーナル';
  ?>
    <div class="ag17-shell ag17-article-page__inner">
      <nav class="ag17-breadcrumb" aria-label="パンくずリスト">
        <a href="<?php echo esc_url(home_url('/')); ?>">HOME</a><span aria-hidden="true">›</span>
        <a href="<?php echo esc_url(home_url('/journal/')); ?>">ジャーナル</a><span aria-hidden="true">›</span>
        <span><?php the_title(); ?></span>
      </nav>
      <article <?php post_class('ag17-article'); ?>>
        <header class="ag17-article__header">
          <span class="ag17-article__cat"><?php echo esc_html($category); ?></span>
          <h1><?php the_title(); ?></h1>
          <time datetime="<?php echo esc_attr(get_the_date('Y-m-d')); ?>"><?php echo esc_html(get_the_date('Y.m.d')); ?></time>
        </header>
        <?php if (has_post_thumbnail()) : ?>
          <figure class="ag17-article__image"><?php the_post_thumbnail('large', array('loading' => 'eager')); ?></figure>
        <?php endif; ?>
        <div class="ag17-article__content"><?php the_content();
                                            wp_link_pages(); ?></div>
      </article>
      <a class="ag17-back-link" href="<?php echo esc_url(home_url('/journal/')); ?>">‹ ジャーナル一覧に戻る</a>
    </div>
  <?php endwhile; ?>
</main>
<?php get_footer(); ?>