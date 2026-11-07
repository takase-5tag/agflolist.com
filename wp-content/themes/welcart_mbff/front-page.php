<?php get_header(); ?>

<main class="ag-platform">

  <!-- HERO: 製品画像とコピーを分離してレスポンシブ表示 -->
  <section class="ag-hero" aria-labelledby="ag-home-title">
    <div class="ag-hero__inner">
      <figure class="ag-hero__visual">
        <img src="<?php echo esc_url(home_url('/img/index/ag2026-hero.webp')); ?>" alt="MBFF・MAGICAL TANSA850の製品" width="1774" height="887" fetchpriority="high">
      </figure>
      <div class="ag-hero__copy">
        <p class="ag-hero__eyebrow">Beauty Partner Platform</p>
        <h1 id="ag-home-title">美容の仕事を、<br>もっと豊かに。</h1>
        <p class="ag-hero__lead">人と人をつなぎ、未来に伴走する。<br>ビューティーパートナープラットフォーム。</p>
        <p class="ag-hero__text">AG FLOLIST は、サロンの成長と信頼されるブランドづくりに向けて、<br class="pc">商品と教育、支援サービスをワンストップでご提供します。<br class="pc">経営をサポートし、あなたの想いを未来へつなぎます。</p>
        <div class="ag-buttons">
          <a href="<?php echo esc_url(home_url('/service/')); ?>" class="ag-btn ag-btn--fill">サービスを見る <span aria-hidden="true">›</span></a>
          <a href="<?php echo esc_url(home_url('/contact/')); ?>" class="ag-btn ag-btn--line">お問い合わせ <span aria-hidden="true">›</span></a>
        </div>
      </div>
      <span class="ag-hero__scroll" aria-hidden="true">SCROLL</span>
    </div>
  </section>

  <!-- 入口：Illustrator内の3種類のビジュアルを個別に最適化 -->
  <section class="ag-entrance" aria-labelledby="ag-entrance-title">
    <div class="ag-container">
      <div class="ag-section-intro">
        <div>
          <p class="ag-kicker">For you</p>
          <h2 id="ag-entrance-title">あなたに合わせた入口</h2>
        </div>
        <p>新しくお取引をご検討のサロン様、すでにお取引のあるサロン様、一般のお客様。<br class="pc">それぞれの方に向けて、必要な情報をご用意しています。</p>
      </div>
      <div class="ag-entrance__grid">
        <a href="<?php echo esc_url(home_url('/salon/')); ?>" class="ag-entrance__visual-card" aria-label="新規取引をご検討のサロン様：詳しく見る">
          <img src="<?php echo esc_url(home_url('/img/index/ag2026-entrance-1.webp')); ?>" alt="Hair / Esthe Salon・新規取引をご検討のサロン様" width="512" height="1024" loading="lazy">
          <span class="ag-entrance__go" aria-hidden="true">↗</span>
        </a>
        <a href="<?php echo esc_url(home_url('/partner-salon/')); ?>" class="ag-entrance__visual-card" aria-label="既存のお取引サロン様：専用ページへ">
          <img src="<?php echo esc_url(home_url('/img/index/ag2026-entrance-2.webp')); ?>" alt="AG Partner Salons・既存のお取引サロン様" width="512" height="1024" loading="lazy">
          <span class="ag-entrance__go" aria-hidden="true">↗</span>
        </a>
        <a href="<?php echo esc_url(home_url('/category/item/')); ?>" class="ag-entrance__visual-card" aria-label="一般のお客様：商品について詳しく見る">
          <img src="<?php echo esc_url(home_url('/img/index/ag2026-entrance-3.webp')); ?>" alt="Personal・一般のお客様" width="512" height="1024" loading="lazy">
          <span class="ag-entrance__go" aria-hidden="true">↗</span>
        </a>
      </div>
    </div>
  </section>

  <!-- サポートの流れ -->
  <section class="ag-flow">
    <div class="ag-container">
      <h2 class="ag-section-title">
        <span>AG FLOLIST</span>
        <span class="ag-section-title__ja">サポートの流れ</span>
      </h2>

      <div class="ag-flow__list">
        <?php
        $flows = [
          ['icon-select.svg', '商品選定', 'サロンに最適な<br>アイテムをご提案'],
          ['icon-intro.svg', '導入', 'スムーズな導入を<br>丁寧にサポート'],
          ['icon-study.svg', '一緒に学ぶ', '商品理解を深めながら、<br>お客様への提案につなげる'],
          ['icon-support.svg', '販売支援', '売れる仕組みづくりを<br>トータルで支援'],
          ['icon-growth.svg', '成長', '売上・信頼・未来を<br>ともに育てる'],
        ];

        foreach ($flows as $i => $flow) : ?>
          <div class="ag-flow__item">
            <div class="ag-flow__icon">
              <img src="<?php echo esc_url(home_url('/')); ?>/img/index/<?php echo esc_attr($flow[0]); ?>" alt="">
            </div>
            <h3><?php echo esc_html($flow[1]); ?></h3>
            <p><?php echo wp_kses_post($flow[2]); ?></p>
          </div>

          <?php if ($i < count($flows) - 1) : ?>
            <div class="ag-flow__arrow">
              <img src="<?php echo esc_url(home_url('/')); ?>/img/index/flow-arrow.svg" alt="">
            </div>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>

      <div class="ag-flow__more">
        <a href="<?php echo esc_url(home_url('/service/')); ?>" class="ag-btn ag-btn--line">サービスを詳しく見る <span>›</span></a>
      </div>
    </div>
  </section>

  <!-- NORIKO'S STORY -->
  <section class="ag-story" aria-labelledby="ag-story-title">
    <div class="ag-story__inner">
      <div class="ag-story__body">
        <p class="ag-story__en">Noriko's Story</p>
        <h2 id="ag-story-title"><span>この人から買いたい。</span><span>そう思っていただける、</span><span>存在でありたい。</span></h2>
        <p>商品やノウハウだけでなく、人と人との信頼を何より大切にしています。サロンの想いに寄り添い、ともに課題を解決し、成長を支えること。それが、AG FLOLIST の使命です。</p>
        <p>信頼されるパートナーとして、美容の仕事がもっと豊かに、もっと輝く未来をつくります。</p>
        <p class="ag-story__signature">営業部長 <strong>木須 法子</strong></p>
        <a href="<?php echo esc_url(home_url('/contact/')); ?>" class="ag-btn ag-btn--fill">ご相談・お問い合わせ <span aria-hidden="true">›</span></a>
        <a href="<?php echo esc_url(home_url('/message/')); ?>" class="ag17-home-message-link">代表メッセージを見る ›</a>
      </div>
      <figure class="ag-story__photo">
        <img src="<?php echo esc_url(home_url('/img/index/ag2026-story.webp')); ?>" alt="サロンオーナーのお話を伺う木須法子" width="1448" height="1086" loading="lazy">
      </figure>
    </div>
  </section>

  <!-- FOR SALON -->
  <section class="ag-salon" id="for-salon">
    <div class="ag-container">
      <h2 class="ag-heading">
        <span class="ag-heading__en">FOR SALON</span>
        <span class="ag-heading__ja">サロンの未来を、一緒につくる。</span>
      </h2>

      <div class="ag-salon__grid">
        <?php
        $supports = [
          ['support-open.svg', '開業・導入サポート', '開業準備から導入まで<br>伴走サポート'],
          ['support-study.svg', '学び・提案サポート', '商品理解を深めながら、<br>お客様への提案力を高める'],
          ['support-sales.svg', '販促サポート', 'メニュー設計・販促企画で<br>集客と売上を最大化'],
          ['support-customer.svg', '顧客管理サポート', '顧客データ活用で<br>リピート率を向上'],
          ['support-future.svg', 'これからを<br>一緒に考える', 'サロンの想いや課題に<br>寄り添い、より良い<br>未来づくりをサポート'],
        ];

        foreach ($supports as $support) : ?>
          <article class="ag-salon__card">
            <img src="<?php echo esc_url(home_url('/')); ?>/img/index/<?php echo esc_attr($support[0]); ?>" alt="">
            <h3><?php echo wp_kses_post($support[1]); ?></h3>
            <p><?php echo wp_kses_post($support[2]); ?></p>
          </article>
        <?php endforeach; ?>
      </div>

      <div class="ag-salon__more">
        <a href="<?php echo esc_url(home_url('/salon/')); ?>" class="ag-btn ag-btn--fill">サロン向けサービスを詳しく見る <span>›</span></a>
      </div>
    </div>
  </section>

  <!-- BRANDS -->
  <section class="ag-brands" aria-labelledby="ag-brands-title">
    <div class="ag-container">
      <div class="ag-section-intro ag-section-intro--center">
        <p class="ag-kicker">Brands</p>
        <h2 id="ag-brands-title">厳選した、信頼のブランド。</h2>
      </div>
      <div class="ag-brands__grid">
        <a href="<?php echo esc_url(home_url('/category/item/#mbff_cate')); ?>" class="ag-brand" aria-label="MBFFの商品を見る">
          <div class="ag-brand__image"><img src="<?php echo esc_url(home_url('/img/index/ag2026-brand-mbff.webp')); ?>" alt="MBFF スキンケア製品" width="1600" height="893" loading="lazy"></div>
          <div class="ag-brand__details"><span>Skin care</span>
            <h3>MBFF</h3>
            <p>肌本来の輝きへ導くスキンケア。</p><span class="ag-brand__link">ブランドを見る ›</span>
          </div>
        </a>
        <a href="<?php echo esc_url(home_url('/category/item/#tansa_cate')); ?>" class="ag-brand" aria-label="MAGICAL TANSA850の商品を見る">
          <div class="ag-brand__image"><img src="<?php echo esc_url(home_url('/img/index/ag2026-brand-tansa.webp')); ?>" alt="MAGICAL TANSA850の製品" width="1600" height="893" loading="lazy"></div>
          <div class="ag-brand__details"><span>Inner &amp; Body care</span>
            <h3>MAGICAL TANSA850</h3>
            <p>内側から美しさを育てるインナーケア。</p><span class="ag-brand__link">ブランドを見る ›</span>
          </div>
        </a>
      </div>
    </div>
  </section>

  <!-- CONTACT: shared V8 component -->
  <?php get_template_part('template-parts/ag-contact-cta'); ?>

</main>

<?php get_footer(); ?>