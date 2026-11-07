<?php
/* Template Name: サロンの方へ */
// /salon/ の「おすすめ」欄だけに適用。共通CSSやWelcartは触れません。
wp_enqueue_style(
  'ag-salon-check-v9',
  home_url('/css/ag-salon-check-v9.css'),
  array(),
  '20261107-3'
);
get_header();
?>
<!-- AG-FLOLIST-HERO-V4 / SALON -->
<main class="ag-lower page-salon">
  <section class="ag-subhero">
    <div class="ag-subhero__inner">
      <div class="ag-subhero__copy">
        <p class="ag-subhero__eyebrow">SALON</p>
        <h1>サロンの物販を、<br>もっと自然に。<br>もっと成果につながる形へ。</h1>
        <p>AG FLOLIST は、商品を卸して終わりではありません。<br>サロンのお客様やメニュー、スタッフ体制に合わせて、<br>商品選びから伝え方、販売の仕組みづくりまで一緒に考えます。<br>無理に売るのではなく、お客様に必要なものを自然にご提案<br>できる店販づくりをサポートします。</p>
      </div>
    </div>
  </section>

  <section class="ag-lower__section">
    <div class="ag-lower__container">
      <h2 class="ag-lower__title">こんなお悩みはありませんか？</h2>
      <div class="salon-trouble">
        <article class="salon-trouble__card"><img src="<?php echo esc_url(home_url('/')); ?>img/salon/trouble-sales.svg" alt="">
          <h3>店販をもっと伸ばしたい</h3>
          <p>商品は置いているけれど、なかなか販売につながらない。</p>
        </article>
        <article class="salon-trouble__card"><img src="<?php echo esc_url(home_url('/')); ?>img/salon/trouble-staff.svg" alt="">
          <h3>スタッフによって提案に差がある</h3>
          <p>商品知識や伝え方が統一できていない。</p>
        </article>
        <article class="salon-trouble__card"><img src="<?php echo esc_url(home_url('/')); ?>img/salon/trouble-product.svg" alt="">
          <h3>サロンに合う商品がわからない</h3>
          <p>たくさんの商品から、何を選べばいいか迷っている。</p>
        </article>
        <article class="salon-trouble__card"><img src="<?php echo esc_url(home_url('/')); ?>img/salon/trouble-customer.svg" alt="">
          <h3>お客様への伝え方を知りたい</h3>
          <p>押し売りにならず、自然におすすめできる方法を知りたい。</p>
        </article>
      </div>
    </div>
  </section>

  <section class="ag-lower__section">
    <div class="ag-lower__container">
      <h2 class="ag-lower__title">AG FLOLIST が一緒に考えます</h2>
      <div class="salon-support">
        <article class="salon-support__item"><img src="<?php echo esc_url(home_url('/')); ?>img/salon/support-select.svg" alt="">
          <h3>商品選び</h3>
          <p>サロンのお客様層やメニュー、今後強化したい内容を伺いながら、サロンに合った商品をご提案します。</p>
        </article>
        <article class="salon-support__item"><img src="<?php echo esc_url(home_url('/')); ?>img/salon/support-study.svg" alt="">
          <h3>学び・提案サポート</h3>
          <p>商品の特徴やおすすめする理由を共有し、スタッフみんなが自信を持ってお客様へご提案できるようサポートします。</p>
        </article>
        <article class="salon-support__item"><img src="<?php echo esc_url(home_url('/')); ?>img/salon/support-store.svg" alt="">
          <h3>店販づくり</h3>
          <p>商品を置くだけではなく、カウンセリングや売り場、伝え方まで含めて販売につながる流れを一緒に考えます。</p>
        </article>
        <article class="salon-support__item"><img src="<?php echo esc_url(home_url('/')); ?>img/salon/support-promo.svg" alt="">
          <h3>販促サポート</h3>
          <p>キャンペーンやPOP、SNSなど、お客様に商品の魅力を知っていただくための見せ方や企画をご提案します。</p>
        </article>
        <article class="salon-support__item"><img src="<?php echo esc_url(home_url('/')); ?>img/salon/support-follow.svg" alt="">
          <h3>継続フォロー</h3>
          <p>導入後も状況を伺いながら、うまくいっていることや課題を一緒に確認し、より良い形へ整えていきます。</p>
        </article>
      </div>
    </div>
  </section>

  <!-- BRANDS：トップページと同じ構造・アンカーリンク -->
  <section class="ag-brands" aria-labelledby="ag-salon-brands-title">
    <div class="ag-container">
      <h2 class="ag-heading" id="ag-salon-brands-title">
        <span class="ag-heading__en">BRANDS</span>
        <span class="ag-heading__ja">厳選した、信頼のブランド。</span>
      </h2>
      <div class="ag-brands__grid">
        <a href="<?php echo esc_url(home_url('/category/item/#mbff_cate')); ?>" class="ag-brand">
          <img src="<?php echo esc_url(home_url('/img/index/ag2026-brand-mbff.webp')); ?>" alt="MBFF" loading="lazy" decoding="async">
        </a>
        <a href="<?php echo esc_url(home_url('/category/item/#tansa_cate')); ?>" class="ag-brand">
          <img src="<?php echo esc_url(home_url('/img/index/ag2026-brand-tansa.webp')); ?>" alt="MAGICAL TANSA850" loading="lazy" decoding="async">
        </a>
      </div>
    </div>
  </section>

  <section class="ag-lower__section">
    <div class="ag-lower__container">
      <h2 class="ag-lower__title">導入までの流れ</h2>
      <div class="salon-flow">
        <article class="salon-flow__item"><span class="salon-flow__num">01</span>
          <h3>まずは相談</h3>
          <p>現在の店販状況やお悩み、お客様層などをお聞かせください。</p>
        </article>
        <article class="salon-flow__item"><span class="salon-flow__num">02</span>
          <h3>商品・方法をご提案</h3>
          <p>サロン様に合った商品や販売方法をご提案します。</p>
        </article>
        <article class="salon-flow__item"><span class="salon-flow__num">03</span>
          <h3>商品を知る</h3>
          <p>商品の特徴や魅力、お客様への伝え方を一緒に共有します。</p>
        </article>
        <article class="salon-flow__item"><span class="salon-flow__num">04</span>
          <h3>店販スタート</h3>
          <p>カウンセリングや売り場づくりなど、サロン様に合った形でスタートします。</p>
        </article>
        <article class="salon-flow__item"><span class="salon-flow__num">05</span>
          <h3>一緒に振り返る</h3>
          <p>導入後の反応や販売状況を確認し、次の取り組みを一緒に考えます。</p>
        </article>
      </div>
      <!-- V9 | RECOMMENDED FOR YOUR SALON -->
      <section class="salon-check" aria-labelledby="salon-check-title">
        <div class="salon-check__intro">
          <p class="salon-check__eyebrow">FOR YOUR SALON</p>
          <h2 id="salon-check-title" class="salon-check__title">こんなサロン様に<br>おすすめです</h2>
          <p class="salon-check__lead">一つでも当てはまったら、<br>まずはお気軽にご相談ください。</p>
        </div>
        <ul class="salon-check__list">
          <li class="salon-check__item"><span class="salon-check__icon" aria-hidden="true">✓</span><span class="salon-check__text">店販をこれから強化していきたい</span><span class="salon-check__number" aria-hidden="true">01</span></li>
          <li class="salon-check__item"><span class="salon-check__icon" aria-hidden="true">✓</span><span class="salon-check__text">お客様への提案方法を見直したい</span><span class="salon-check__number" aria-hidden="true">02</span></li>
          <li class="salon-check__item"><span class="salon-check__icon" aria-hidden="true">✓</span><span class="salon-check__text">スタッフ全員で商品提案に取り組みたい</span><span class="salon-check__number" aria-hidden="true">03</span></li>
          <li class="salon-check__item"><span class="salon-check__icon" aria-hidden="true">✓</span><span class="salon-check__text">商品導入後も相談できる相手がほしい</span><span class="salon-check__number" aria-hidden="true">04</span></li>
          <li class="salon-check__item"><span class="salon-check__icon" aria-hidden="true">✓</span><span class="salon-check__text">サロンに合った商品を探している</span><span class="salon-check__number" aria-hidden="true">05</span></li>
          <li class="salon-check__item"><span class="salon-check__icon" aria-hidden="true">✓</span><span class="salon-check__text">無理な販売ではなく、信頼につながる店販をしたい</span><span class="salon-check__number" aria-hidden="true">06</span></li>
        </ul>
      </section>
    </div>
  </section>
  <?php get_template_part('template-parts/ag-contact-cta'); ?>
</main>
<?php get_footer(); ?>