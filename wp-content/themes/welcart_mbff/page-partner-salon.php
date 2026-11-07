<?php

/**
 * Template Name: 既存のサロン様へ（商品・追加発注）
 * /partner-salon/ - 2026 partner-salon design, reference structure
 * Welcartの会員判定・価格処理・注文処理は変更しません。
 */
add_filter('body_class', static function ($classes) {
  $classes[] = 'agco-page';
  return $classes;
});
get_header();
$agco_member_logged_in = function_exists('usces_is_login') && usces_is_login();
$agco_catalog_url = home_url('/category/item/');
$agco_member_url = home_url('/usces-member/');
$agco_order_url = $agco_member_logged_in ? $agco_catalog_url : $agco_member_url;
$agco_mbff_url  = $agco_member_logged_in ? home_url('/category/item/#mbff_cate')  : $agco_member_url;
$agco_tansa_url = $agco_member_logged_in ? home_url('/category/item/#tansa_cate') : $agco_member_url;
?>
<div class="agco" id="agco-top">
  <header class="agco-header" aria-label="お取引サロン様専用メニュー">
    <div class="agco-header__inner">
      <a class="agco-header__logo" href="<?php echo esc_url(home_url('/')); ?>" aria-label="AG FLOLIST トップページ"><img src="<?php echo esc_url(home_url('/img/common/logo.svg')); ?>" alt="AG FLOLIST" width="185" height="48"></a>
      <span class="agco-header__rule" aria-hidden="true"></span>
      <nav class="agco-header__nav" id="agco-nav" aria-label="専用ページ内メニュー">
        <a href="#agco-products">商品一覧 <span aria-hidden="true">⌄</span></a>
        <a href="<?php echo esc_url(home_url('/beauty-equipment/')); ?>">業務用美容機器 <span aria-hidden="true">↗</span></a>
        <a href="#agco-quick">追加発注</a>
        <a href="#agco-info">ご案内 <span aria-hidden="true">⌄</span></a>
      </nav>
      <a class="agco-header__member" href="<?php echo esc_url($agco_member_url); ?>">
        <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
          <circle cx="12" cy="8" r="3.4" />
          <path d="M5.3 20v-2.1a6.7 6.7 0 0 1 13.4 0V20" />
        </svg>
        <?php echo $agco_member_logged_in ? 'マイページ' : '会員ログイン'; ?><span aria-hidden="true"> ›</span>
      </a>
      <button class="agco-header__toggle" type="button" aria-controls="agco-nav" aria-expanded="false" aria-label="専用メニューを開く">
        <span></span><span></span><span></span>
      </button>
    </div>
  </header>

  <main id="agco-main">
    <section class="agco-hero" aria-labelledby="agco-title">
      <div class="agco-hero__inner">
        <p class="agco-eyebrow">SALON ORDER</p>
        <h1 id="agco-title">お取引サロン様専用ページ</h1>
        <p>MBFF と MAGICAL TANSA850 をお取り扱いの<br class="agco-pc">サロン様向けの、仕入れ・追加発注の専用ページです。<br>会員ログイン後、会員区分に応じた価格でご注文いただけます。</p>
      </div>
    </section>

    <section class="agco-products" id="agco-products" aria-label="お取り扱いブランド">
      <div class="agco-container">
        <div class="agco-products__grid">
          <article class="agco-product">
            <div class="agco-product__image">
              <img src="<?php echo esc_url(home_url('/img/index/ag2026-brand-mbff.webp')); ?>" alt="MBFF スキンケア製品" width="1600" height="893" loading="eager">
            </div>
            <div class="agco-product__content">
              <p class="agco-product__kicker">SKIN CARE</p>
              <h2>MBFF</h2>
              <p class="agco-product__description">サロン品質のスキンケアで、<br>肌本来の輝きへ導きます。</p>
              <a class="agco-button agco-button--solid" href="<?php echo esc_url($agco_mbff_url); ?>">MBFFの商品を見る <span aria-hidden="true">›</span></a>
            </div>
          </article>
          <article class="agco-product">
            <div class="agco-product__image agco-product__image--warm">
              <img src="<?php echo esc_url(home_url('/img/index/ag2026-brand-tansa.webp')); ?>" alt="MAGICAL TANSA850 製品" width="1600" height="893" loading="eager">
            </div>
            <div class="agco-product__content">
              <p class="agco-product__kicker">INNER &amp; BODY CARE</p>
              <h2>MAGICAL TANSA850</h2>
              <p class="agco-product__description">美しさと健やかさを支える、<br>インナー＆ボディケアブランド。</p>
              <a class="agco-button agco-button--solid" href="<?php echo esc_url($agco_tansa_url); ?>">TANSAの商品を見る <span aria-hidden="true">›</span></a>
            </div>
          </article>
        </div>
      </div>
    </section>

    <section class="agme-partner-teaser" aria-labelledby="agme-teaser-title">
      <div class="agco-container agme-partner-teaser__box">
        <div class="agme-partner-teaser__mark" aria-hidden="true">EQUIPMENT</div>
        <div class="agme-partner-teaser__copy">
          <p>PROFESSIONAL BEAUTY EQUIPMENT</p>
          <h2 id="agme-teaser-title">業務用美容機器</h2><span>フェイシャル・脱毛・制毛からサロン設備まで。<br>施術メニューに合わせて選べる美容機器をご紹介します。</span>
        </div>
        <a href="<?php echo esc_url(home_url('/beauty-equipment/')); ?>" class="agme-partner-teaser__link">美容機器一覧を見る <span aria-hidden="true">↗</span></a>
      </div>
    </section>

    <section class="agco-quick" id="agco-quick" aria-labelledby="agco-quick-title">
      <div class="agco-container">
        <div class="agco-quick__panel">
          <div class="agco-quick__intro">
            <svg viewBox="0 0 32 32" width="39" height="39" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.4" aria-hidden="true">
              <path d="M3 5h3l4 17h15l3-12H8" />
              <circle cx="12" cy="27" r="1.4" />
              <circle cx="24" cy="27" r="1.4" />
            </svg>
            <div>
              <p class="agco-small-en">QUICK ORDER</p>
              <h2 id="agco-quick-title">いつもの商品を、簡単に追加発注。</h2>
            </div>
          </div>
          <div class="agco-quick__action">
            <p>定番のMBFF・MAGICAL TANSA850を、<br>スムーズにお選びいただけます。</p>
            <a class="agco-button agco-button--solid" href="<?php echo esc_url($agco_order_url); ?>">追加発注はこちら <span aria-hidden="true">›</span></a>
          </div>
        </div>
      </div>
    </section>

    <section class="agco-information" id="agco-info" aria-labelledby="agco-information-title">
      <div class="agco-container agco-information__inner">
        <div class="agco-information__heading">
          <p class="agco-small-en">INFORMATION</p>
          <h2 id="agco-information-title">ご案内</h2>
        </div>
        <ul class="agco-information__list">
          <li><a href="<?php echo esc_url(home_url('/guide/')); ?>"><span class="agco-information__tag">ご利用案内</span><span class="agco-information__text">ご注文方法・お支払いについて</span><span class="agco-information__arrow" aria-hidden="true">›</span></a></li>
          <li><a href="<?php echo esc_url($agco_member_url); ?>"><span class="agco-information__tag">会員様へ</span><span class="agco-information__text">会員情報の確認・ログインはこちら</span><span class="agco-information__arrow" aria-hidden="true">›</span></a></li>
          <li><a href="<?php echo esc_url(home_url('/contact/')); ?>"><span class="agco-information__tag">ご相談</span><span class="agco-information__text">ご注文・商品に関するお問い合わせ</span><span class="agco-information__arrow" aria-hidden="true">›</span></a></li>
        </ul>
      </div>
    </section>
  </main>

  <footer class="agco-footer">
    <div class="agco-container agco-footer__inner">
      <a class="agco-footer__logo" href="<?php echo esc_url(home_url('/')); ?>" aria-label="AG FLOLIST トップページ"><img src="<?php echo esc_url(home_url('/img/common/logo.svg')); ?>" alt="AG FLOLIST" width="185" height="48"></a>
      <nav aria-label="サロン様向けフッターリンク">
        <a href="<?php echo esc_url(home_url('/guide/')); ?>">ご利用ガイド</a>
        <a href="<?php echo esc_url(home_url('/contact/')); ?>">お問い合わせ</a>
        <a href="<?php echo esc_url(home_url('/policy/')); ?>">プライバシーポリシー</a>
      </nav>
    </div>
  </footer>
</div>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const btn = document.querySelector('.agco-header__toggle');
    const nav = document.getElementById('agco-nav');
    if (!btn || !nav) return;

    function setOpen(open) {
      btn.setAttribute('aria-expanded', String(open));
      btn.setAttribute('aria-label', open ? '専用メニューを閉じる' : '専用メニューを開く');
      nav.classList.toggle('is-open', open);
    }
    btn.addEventListener('click', function() {
      setOpen(btn.getAttribute('aria-expanded') !== 'true');
    });
    nav.querySelectorAll('a').forEach(function(a) {
      a.addEventListener('click', function() {
        setOpen(false);
      });
    });
    document.addEventListener('keydown', function(event) {
      if (event.key === 'Escape') setOpen(false);
    });
    document.addEventListener('click', function(event) {
      if (!event.target.closest('.agco-header')) setOpen(false);
    });
  });
</script>
<?php get_footer(); ?>