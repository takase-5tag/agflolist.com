<!doctype html>
<html <?php language_attributes(); ?>>

<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="description" content="AG FLOLIST（エージーフローリスト）">
  <meta name="keywords" content="AG FLOLIST,フローリスト,美容室,サロン">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="format-detection" content="telephone=no">

  <link rel="stylesheet" href="<?php echo esc_url(home_url('/')); ?>css/common.css?v=20261106-5">
  <?php /* index.css は旧トップページ専用。新トップでは競合するため読み込まない */ ?>
  <?php if (!is_front_page()) : ?>
    <link rel="stylesheet" href="<?php echo esc_url(home_url('/')); ?>css/index.css?v=20261106-5">
  <?php endif; ?>
  <?php if (is_page(array('agreement', 'guide', 'sctl', 'policy'))) : ?>
    <link rel="stylesheet" href="<?php echo esc_url(home_url('/')); ?>css/ag-legal.css?v=20261106-5">
  <?php endif; ?>
  <link rel="stylesheet" href="<?php echo esc_url(home_url('/')); ?>js/slick/slick-theme.css">
  <link rel="stylesheet" href="<?php echo esc_url(home_url('/')); ?>js/slick/slick.css">

  <?php wp_head(); ?>
  <!-- AG FLOLIST V5: coordinated palette + staging-style navigation -->
  <link rel="stylesheet" href="<?php echo esc_url(home_url('/css/ag-site-2026-v5.css')); ?>?v=20261106-5">
  <?php if (is_page(array('partner-salon', 'company', 'beauty-equipment')) || is_page_template('page-partner-salon.php') || is_page_template('page-beauty-equipment.php')) : ?>
    <link rel="stylesheet" href="<?php echo esc_url(home_url('/css/ag-partner-salon-v7.css')); ?>?v=20261106-7">
  <?php endif; ?>

  <?php if (is_page('beauty-equipment') || is_page_template('page-beauty-equipment.php') || is_page('partner-salon') || is_page_template('page-partner-salon.php')) : ?>
    <link rel="stylesheet" href="<?php echo esc_url(home_url('/css/ag-beauty-equipment-v10.css')); ?>?v=20261107-10">
  <?php endif; ?>

  <!-- V17 editorial style: scoped to MESSAGE / JOURNAL; homepage message link and nav sizing also included -->
  <link rel="stylesheet" href="<?php echo esc_url(get_template_directory_uri() . '/css/ag-editorial-v17.css'); ?>?v=20261107-17">

  <!-- AG FLOLIST V8: common CTA, salon flow and partner logo -->
  <link rel="stylesheet" href="<?php echo esc_url(home_url('/css/ag-refine-v8.css')); ?>?v=20261107-1">

  <script async src="https://www.googletagmanager.com/gtag/js?id=G-5HHE1STDXC"></script>
  <script>
    window.dataLayer = window.dataLayer || [];

    function gtag() {
      dataLayer.push(arguments);
    }
    gtag('js', new Date());
    gtag('config', 'G-5HHE1STDXC');
  </script>
</head>

<body <?php body_class(); ?>>
  <?php wp_body_open(); ?>

  <div id="wrap">

    <header class="site-header">
      <div class="site-header__inner">

        <div class="site-header__logo">
          <a href="<?php echo esc_url(home_url('/')); ?>">
            <img src="<?php echo esc_url(home_url('/')); ?>img/common/logo.svg" alt="AG FLOLIST">
          </a>
        </div>

        <nav class="site-header__nav" aria-label="グローバルナビゲーション">
          <ul class="site-header__nav-list">
            <li><a href="<?php echo esc_url(home_url('/')); ?>"><span>私たちについて</span></a></li>
            <li><a href="<?php echo esc_url(home_url('/message/')); ?>" <?php if (is_page('message')) echo 'aria-current="page"'; ?>><span>代表メッセージ</span></a></li>
            <li><a href="<?php echo esc_url(home_url('/salon/')); ?>"><span>サロンの方へ</span></a></li>
            <li><a href="<?php echo esc_url(home_url('/partner-salon/')); ?>"><span>既存のサロン様へ</span></a></li>
            <li><a href="<?php echo esc_url(home_url('/category/item/')); ?>"><span>商品について</span></a></li>
            <li><a href="<?php echo esc_url(home_url('/journal/')); ?>" <?php if (is_page('journal') || is_singular('ag_journal')) echo 'aria-current="page"'; ?>><span>読みもの</span></a></li>
          </ul>
        </nav>
        <div class="site-header__actions" aria-label="会員・お問い合わせ">
          <a class="site-header__login" href="<?php echo esc_url(home_url('/usces-member/')); ?>">
            <?php echo (function_exists('usces_is_login') && usces_is_login()) ? 'マイページ' : '会員ログイン'; ?>
          </a>
          <a class="site-header__contact-link" href="<?php echo esc_url(home_url('/contact/')); ?>">お問い合わせ <span aria-hidden="true">↗</span></a>
        </div>

        <button class="menu-toggle" type="button" aria-label="メニューを開く" aria-controls="global-menu" aria-expanded="false">
          <span></span>
          <span></span>
          <span></span>
        </button>

      </div>
    </header>

    <div id="global-menu" class="sp-menu" aria-hidden="true">
      <div class="sp-menu__inner">
        <div class="sp-menu__head">
          <a class="sp-menu__logo" href="<?php echo esc_url(home_url('/')); ?>">
            <img src="<?php echo esc_url(home_url('/')); ?>img/common/logo.svg" alt="AG FLOLIST">
          </a>
        </div>

        <nav aria-label="スマートフォンメニュー">
          <ul class="sp-menu__list">
            <li><a href="<?php echo esc_url(home_url('/')); ?>"><span>私たちについて</span><small>About</small></a></li>
            <li><a href="<?php echo esc_url(home_url('/message/')); ?>" <?php if (is_page('message')) echo 'aria-current="page"'; ?>><span>代表メッセージ</span><small>Message</small></a></li>
            <li><a href="<?php echo esc_url(home_url('/salon/')); ?>"><span>サロンの方へ</span><small>Salon</small></a></li>
            <li><a href="<?php echo esc_url(home_url('/partner-salon/')); ?>"><span>既存のサロン様へ</span><small>Partner salon</small></a></li>
            <li><a href="<?php echo esc_url(home_url('/category/item/')); ?>"><span>商品について</span><small>Products</small></a></li>
            <li><a href="<?php echo esc_url(home_url('/journal/')); ?>" <?php if (is_page('journal') || is_singular('ag_journal')) echo 'aria-current="page"'; ?>><span>読みもの</span><small>Journal</small></a></li>
          </ul>
        </nav>
        <div class="sp-menu__actions">
          <a class="sp-menu__contact" href="<?php echo esc_url(home_url('/contact/')); ?>">お問い合わせ <span aria-hidden="true">↗</span></a>
          <a class="sp-menu__login" href="<?php echo esc_url(home_url('/usces-member/')); ?>">
            <?php echo (function_exists('usces_is_login') && usces_is_login()) ? 'マイページ' : '会員ログイン'; ?> <span aria-hidden="true">↗</span>
          </a>
        </div>

        <ul class="sp-menu__sub">
          <li><a href="<?php echo esc_url(home_url('/agreement/')); ?>">利用規約</a></li>
          <li><a href="<?php echo esc_url(home_url('/guide/')); ?>">ご利用ガイド</a></li>
          <li><a href="<?php echo esc_url(home_url('/sctl/')); ?>">特定商取引法に関する表示</a></li>
          <li><a href="<?php echo esc_url(home_url('/policy/')); ?>">プライバシーポリシー</a></li>
        </ul>
      </div>
    </div>

    <script>
      document.addEventListener('DOMContentLoaded', function() {
        const button = document.querySelector('.menu-toggle');
        const menu = document.querySelector('.sp-menu');
        if (!button || !menu) return;

        function closeMenu() {
          button.classList.remove('is-open');
          menu.classList.remove('is-open');
          button.setAttribute('aria-expanded', 'false');
          button.setAttribute('aria-label', 'メニューを開く');
          menu.setAttribute('aria-hidden', 'true');
          document.body.classList.remove('menu-open');
        }

        button.addEventListener('click', function() {
          const open = !menu.classList.contains('is-open');
          button.classList.toggle('is-open', open);
          menu.classList.toggle('is-open', open);
          button.setAttribute('aria-expanded', open ? 'true' : 'false');
          button.setAttribute('aria-label', open ? 'メニューを閉じる' : 'メニューを開く');
          menu.setAttribute('aria-hidden', open ? 'false' : 'true');
          document.body.classList.toggle('menu-open', open);
        });

        document.addEventListener('keydown', function(event) {
          if (event.key === 'Escape' && menu.classList.contains('is-open')) {
            closeMenu();
            button.focus();
          }
        });
        menu.querySelectorAll('a').forEach(function(link) {
          link.addEventListener('click', closeMenu);
        });
      });
    </script>