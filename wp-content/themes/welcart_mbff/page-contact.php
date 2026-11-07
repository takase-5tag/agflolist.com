<?php

/**
 * Template Name: お問い合わせ
 * AG FLOLIST / Contact Form 7 integration
 */
get_header();

$ag_contact_types = array(
  array('contact-store.svg', 'サロン物販について', 'サロンに合う商品を知りたい、店販をもっと強化したいなど、サロン物販に関するご相談はこちら。'),
  array('contact-product.svg', '商品導入について', 'MBFF・MAGICAL TANSA850をはじめ、商品の特徴や導入方法についてご案内します。'),
  array('contact-sales.svg', '提案・販売について', 'お客様への伝え方やカウンセリング、スタッフへの落とし込みなど、販売に関するお悩みをご相談いただけます。'),
  array('contact-company.svg', '企業・法人でのご利用について', '提携・提供会社様、企業向けスタッフ販売や福利厚生など、法人でのご利用についてはこちら。'),
  array('contact-line.svg', 'LINEで気軽に相談', 'ちょっと聞いてみたいことや、商品についての簡単なご質問などはLINEからもお気軽にご相談いただけます。'),
);
?>
<main class="ag-lower page-contact" id="main-content">
  <section class="ag-subhero" aria-labelledby="ag-contact-main-title">
    <div class="ag-subhero__inner">
      <div class="ag-subhero__copy">
        <p class="ag-contact__eyebrow">CONTACT</p>
        <h1 id="ag-contact-main-title">まずは、お気軽にご相談ください。</h1>
        <p>商品導入のこと、店販のこと、スタッフへの伝え方、<br class="ag-contact__pc-break">企業でのご活用についてなど、<br class="ag-contact__pc-break">気になることがございましたらお気軽にお問い合わせください。</p>
        <p>まだ具体的に決まっていなくても大丈夫です。<br>お話を伺いながら、一緒に整理していきます。</p>
      </div>
    </div>
  </section>

  <section class="ag-lower__section ag-contact-categories" aria-labelledby="ag-contact-categories-title">
    <div class="ag-lower__container">
      <h2 class="ag-lower__title" id="ag-contact-categories-title">ご相談内容をお選びください</h2>
      <div class="contact-types">
        <?php foreach ($ag_contact_types as $ag_type) : ?>
          <article class="contact-type">
            <div class="contact-type__icon">
              <img src="<?php echo esc_url(home_url('/img/contact/' . $ag_type[0])); ?>" alt="" loading="lazy" decoding="async">
            </div>
            <h3><?php echo esc_html($ag_type[1]); ?></h3>
            <p><?php echo esc_html($ag_type[2]); ?></p>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="ag-lower__section ag-contact-form-section" id="contact-form" aria-labelledby="ag-contact-form-title">
    <div class="ag-lower__container contact-form-wrap">
      <div class="contact-form-intro">
        <p class="contact-form-intro__en">INQUIRY</p>
        <h2 id="ag-contact-form-title">お問い合わせフォーム</h2>
        <p>以下のフォームに必要事項をご入力のうえ「送信する」ボタンをクリックしてください。</p>
        <p>内容確認のうえ、担当者よりご連絡いたします。</p>
      </div>
      <div class="contact-form-panel">
        <?php
        // Contact Form 7 に「AG FLOLIST お問い合わせ」というタイトルで作成すると自動表示します。
        $ag_cf7_id = "6dd9bfe";
        if (shortcode_exists('contact-form-7')) {
          $ag_cf7_forms = get_posts(array(
            'post_type' => 'wpcf7_contact_form',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'no_found_rows' => true,
          ));
          foreach ($ag_cf7_forms as $ag_cf7_candidate_id) {
            if ('AG FLOLIST お問い合わせ' === get_the_title($ag_cf7_candidate_id)) {
              $ag_cf7_id = (int) $ag_cf7_candidate_id;
              break;
            }
          }
        }
        if ($ag_cf7_id) {
          echo do_shortcode('[contact-form-7 id="' . $ag_cf7_id . '" title="AG FLOLIST お問い合わせ"]'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        } else {
          if (current_user_can('manage_options')) : ?>
            <p class="contact-form__setup-notice">Contact Form 7で「AG FLOLIST お問い合わせ」という名前のフォームを作成してください。設置説明書に記載のフォームタグを貼り付けると、この位置に表示されます。</p>
          <?php else : ?>
            <p class="contact-form__setup-notice">お問い合わせフォームは現在準備中です。</p>
        <?php endif;
        }
        ?>
      </div>
    </div>
  </section>

  <section class="ag-lower__section ag-contact-faq" aria-labelledby="ag-contact-faq-title">
    <div class="ag-lower__narrow">
      <h2 class="ag-lower__title" id="ag-contact-faq-title">よくあるご質問</h2>
      <div class="ag-faq">
        <details class="ag-faq__item">
          <summary class="ag-faq__q">まだ導入するか決めていなくても相談できますか？</summary>
          <div class="ag-faq__a">
            <p>もちろんです。<br>まずは情報収集やご相談だけでもお気軽にお問い合わせください。</p>
          </div>
        </details>
        <details class="ag-faq__item">
          <summary class="ag-faq__q">商品について詳しく聞くだけでも大丈夫ですか？</summary>
          <div class="ag-faq__a">
            <p>はい。<br>商品の特徴や使い方、サロンでのご提案方法などもご案内します。</p>
          </div>
        </details>
        <details class="ag-faq__item">
          <summary class="ag-faq__q">オンライン相談は可能ですか？</summary>
          <div class="ag-faq__a">
            <p>可能です。<br>ご都合の良い日時をお選びいただき、オンラインでお話しいただけます。</p>
          </div>
        </details>
        <details class="ag-faq__item">
          <summary class="ag-faq__q">何を相談すればいいかわからないのですが？</summary>
          <div class="ag-faq__a">
            <p>問題ありません。<br>現在の状況を伺いながら、一緒に課題や必要なことを整理していきます。</p>
          </div>
        </details>
      </div>
    </div>
  </section>

  <?php get_template_part('template-parts/ag-contact-cta'); ?>
</main>
<?php get_footer(); ?>