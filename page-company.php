<?php
/* Template Name: 既存のお取引サロン様へ */
get_header();
?>
<main class="ag-order" id="main-content">
  <!-- Illustrator 2026/11/06：既存サロン専用注文ページに置き換え -->
  <section class="ag-order__hero" aria-labelledby="ag-order-title">
    <div class="ag-order__hero-inner">
      <p class="ag-kicker">SALON ORDER</p>
      <h1 id="ag-order-title">お取引サロン様専用ページ</h1>
      <p>MBFF・MAGICAL TANSA850 をお取引中の<br class="pc">サロン様向けの商品・ご注文情報をご案内します。<br>いつもの商品を、スムーズにお選びいただけます。</p>
    </div>
  </section>

  <section class="ag-order__catalog" aria-labelledby="ag-order-products">
    <div class="ag-container">
      <div class="ag-section-intro ag-section-intro--center">
        <p class="ag-kicker">For our partners</p>
        <h2 id="ag-order-products">お取引サロン様の商品一覧</h2>
        <p>下記のブランドから商品をご覧いただけます。</p>
      </div>
      <div class="ag-order__grid">
        <article class="ag-order__card">
          <div class="ag-order__card-image"><img src="<?php echo esc_url(home_url('/img/index/ag2026-brand-mbff.webp')); ?>" alt="MBFF スキンケアの製品" width="1600" height="893" loading="lazy"></div>
          <div class="ag-order__card-copy">
            <p class="ag-order__card-kicker">Skin care</p>
            <h3>MBFF</h3>
            <p>サロンでのご提案を支える、上質なスキンケアシリーズ。</p>
            <a href="<?php echo esc_url(home_url('/category/item/#mbff_cate')); ?>" class="ag-btn ag-btn--fill">MBFFの商品を見る <span aria-hidden="true">›</span></a>
          </div>
        </article>
        <article class="ag-order__card">
          <div class="ag-order__card-image"><img src="<?php echo esc_url(home_url('/img/index/ag2026-brand-tansa.webp')); ?>" alt="MAGICAL TANSA850の製品" width="1600" height="893" loading="lazy"></div>
          <div class="ag-order__card-copy">
            <p class="ag-order__card-kicker">Inner &amp; Body care</p>
            <h3>MAGICAL TANSA850</h3>
            <p>毎日の美容と健康を内側からサポートするシリーズ。</p>
            <a href="<?php echo esc_url(home_url('/category/item/#tansa_cate')); ?>" class="ag-btn ag-btn--fill">TANSA850の商品を見る <span aria-hidden="true">›</span></a>
          </div>
        </article>
      </div>
    </div>
  </section>

  <section class="ag-order__support">
    <div class="ag-container ag-order__support-grid">
      <div>
        <p class="ag-kicker">Member support</p>
        <h2>いつもの商品を、<br>スムーズに追加注文。</h2>
        <p>ご注文には会員ログインが必要です。取引やご注文に関するご不明点は、お問い合わせください。</p>
      </div>
      <div class="ag-order__support-actions">
        <a href="<?php echo esc_url(home_url('/usces-member/')); ?>" class="ag-btn ag-btn--fill">会員ログイン・マイページ <span aria-hidden="true">›</span></a>
        <a href="<?php echo esc_url(home_url('/contact/')); ?>" class="ag-btn ag-btn--line">お問い合わせ <span aria-hidden="true">›</span></a>
      </div>
    </div>
  </section>
</main>
<?php get_footer(); ?>
