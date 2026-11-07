<!doctype html>
<html <?php language_attributes(); ?>>

<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="description" content="AG FLOLIST（エージーフローリスト）">
  <meta name="keywords" content="AG FLOLIST,フローリスト,美容室,サロン">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="format-detection" content="telephone=no">

  <link rel="stylesheet" href="<?php echo esc_url(home_url('/')); ?>css/common.css">
  <link rel="stylesheet" href="<?php echo esc_url(home_url('/')); ?>css/index.css">
  <?php if (is_page(array('agreement', 'guide', 'sctl', 'policy'))) : ?>
    <link rel="stylesheet" href="<?php echo esc_url(home_url('/')); ?>css/ag-legal.css?v=1.0.1">
  <?php endif; ?>
  <link rel="stylesheet" href="<?php echo esc_url(home_url('/')); ?>js/slick/slick-theme.css">
  <link rel="stylesheet" href="<?php echo esc_url(home_url('/')); ?>js/slick/slick.css">

  <?php wp_head(); ?>
  <!-- AG FLOLIST 2026-11 design refresh: /css/ はサイト直下 -->
  <link rel="stylesheet" href="<?php echo esc_url(home_url('/css/ag-refresh-20261106.css')); ?>?v=20261106-2">

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
            <li><a href="<?php echo esc_url(home_url('/')); ?>"><span>ABOUT</span><small>私たちについて</small></a></li>
            <li><a href="<?php echo esc_url(home_url('/salon/')); ?>"><span>SALON</span><small>サロンの方へ</small></a></li>
            <li><a href="<?php echo esc_url(home_url('/company/')); ?>"><span>PARTNER</span><small>既存のサロン様へ</small></a></li>
            <li><a href="<?php echo esc_url(home_url('/products/')); ?>"><span>PRODUCTS</span><small>商品について</small></a></li>
            <li><a href="<?php echo esc_url(home_url('/journal/')); ?>"><span>JOURNAL</span><small>読みもの</small></a></li>
            <li class="site-header__contact"><a href="<?php echo esc_url(home_url('/contact/')); ?>"><span>CONTACT</span><small>お問い合わせ</small></a></li>
          </ul>
        </nav>

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
            <li><a href="<?php echo esc_url(home_url('/')); ?>"><span>ABOUT</span><small>私たちについて</small></a></li>
            <li><a href="<?php echo esc_url(home_url('/salon/')); ?>"><span>SALON</span><small>サロンの方へ</small></a></li>
            <li><a href="<?php echo esc_url(home_url('/company/')); ?>"><span>PARTNER</span><small>既存のサロン様へ</small></a></li>
            <li><a href="<?php echo esc_url(home_url('/products/')); ?>"><span>PRODUCTS</span><small>商品について</small></a></li>
            <li><a href="<?php echo esc_url(home_url('/journal/')); ?>"><span>JOURNAL</span><small>読みもの</small></a></li>
            <li><a href="<?php echo esc_url(home_url('/contact/')); ?>"><span>CONTACT</span><small>お問い合わせ</small></a></li>
          </ul>
        </nav>

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

        menu.querySelectorAll('a').forEach(function(link) {
          link.addEventListener('click', closeMenu);
        });
      });
    </script>