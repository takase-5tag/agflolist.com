<?php

/**
 * Template Name: 代表メッセージ
 * Slug: message
 */
get_header();
$ag17_image = get_template_directory_uri() . '/assets/ag-editorial/';
?>
<main class="ag-editorial-v17 ag-message-v17" id="main-content">
  <section class="ag17-hero ag17-hero--message" aria-labelledby="ag17-message-title">
    <img class="ag17-hero__picture" src="<?php echo esc_url($ag17_image . 'message-hero.webp'); ?>" alt="やわらかな光に包まれた花器と花々" width="528" height="278" fetchpriority="high">
    <div class="ag17-shell ag17-hero__content">
      <p class="ag17-overline">MESSAGE</p>
      <h1 id="ag17-message-title">代表メッセージ</h1>
      <p class="ag17-hero__subtitle">美容の未来を、ともに育てていく。</p>
    </div>
  </section>

  <section class="ag17-message-statement" aria-labelledby="ag17-message-lead">
    <div class="ag17-shell ag17-message-statement__inner">
      <h2 id="ag17-message-lead">商品を届けるだけでは、終わらない。<br>美容の未来を、ともに育てていく。</h2>
      <span class="ag17-rule" aria-hidden="true"></span>
      <div class="ag17-message-statement__text">
        <p>AG FLOLISTは、サロンの皆さまとともに、<br>美容の仕事がもっと豊かになることを目指しています。</p>
        <p>商品を届けるだけでなく、教育・提案・販促まで、<br>サロンの成長に寄り添い、ともに価値をつくるパートナーでありたい。</p>
        <p>それは、働くスタッフのやりがい、お客様の満足、<br>そして美容業界の持続的な発展につながると、私たちは考えています。</p>
      </div>
      <p class="ag17-message-statement__sign">AG FLOLIST <span>代表</span> <strong>田栗 憲哉</strong></p>
    </div>
  </section>

  <section class="ag17-values" aria-labelledby="ag17-values-heading">
    <div class="ag17-shell">
      <div class="ag17-section-title">
        <p class="ag17-overline">OUR VALUES</p>
        <h2 id="ag17-values-heading">私たちが大切にしている3つのこと</h2>
      </div>
      <div class="ag17-values__grid">
        <article class="ag17-value">
          <div class="ag17-value__icon" aria-hidden="true">
            <svg viewBox="0 0 64 64" width="62" height="62" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="32" cy="15" r="7" />
              <path d="M18 37v-4c0-8 6-13 14-13s14 5 14 13v4M10 38v-3c0-6 4-10 10-11M54 38v-3c0-6-4-10-10-11" />
              <circle cx="14" cy="20" r="5" />
              <circle cx="50" cy="20" r="5" />
              <path d="M17 51v-7c0-7 6-12 15-12s15 5 15 12v7M4 51v-9c0-5 4-8 9-8M60 51v-9c0-5-4-8-9-8" />
            </svg>
          </div>
          <h3>人を大切に</h3>
          <p>数字の先にいる<br>一人ひとりの想いに寄り添う。</p>
        </article>
        <article class="ag17-value">
          <div class="ag17-value__icon" aria-hidden="true">
            <svg viewBox="0 0 64 64" width="62" height="62" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <path d="M31 48C21 42 13 42 5 46V13c9-4 19-3 27 3 8-6 18-7 27-3v33c-8-4-16-4-27 2z" />
              <path d="M32 16v32M9 19c7-3 14-1 19 3M37 22c6-4 12-5 18-3" />
            </svg>
          </div>
          <h3>ともに学ぶ</h3>
          <p>商品の知識で、<br>お客様の価値に変える。</p>
        </article>
        <article class="ag17-value">
          <div class="ag17-value__icon" aria-hidden="true">
            <svg viewBox="0 0 64 64" width="62" height="62" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <path d="M32 56V30M32 38C16 38 8 28 7 14c14 0 24 7 25 20M32 38c16 0 24-10 25-24-14 0-24 7-25 20" />
            </svg>
          </div>
          <h3>未来を育てる</h3>
          <p>サロンのこれからを見据え、<br>長く続く関係をつくる。</p>
        </article>
      </div>
    </div>
  </section>

  <section class="ag17-message-role" aria-labelledby="ag17-role-title">
    <div class="ag17-shell">
      <div class="ag17-section-title">
        <p class="ag17-overline">OUR ROLE</p>
        <h2 id="ag17-role-title">私たちが届けたい価値</h2>
      </div>
      <p>AG FLOLISTは、確かなプロダクトと、<br>サロンの課題に寄り添う学び・仕組み・情報で、<br>美容の仕事の可能性を広げていきます。</p>
      <a class="ag17-outline-btn" href="<?php echo esc_url(home_url('/service/')); ?>">私たちの取り組みを見る <span aria-hidden="true">›</span></a>
    </div>
  </section>
</main>
<?php get_footer(); ?>