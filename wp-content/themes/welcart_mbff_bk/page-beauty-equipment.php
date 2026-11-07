<?php

/**
 * Template Name: 業務用美容機器（既存サロン様向け）
 * サロン用美容機器の紹介ページ。購入、価格、会員判定のPHPは変更しません。
 */
add_filter('body_class', static function ($classes) {
  $classes[] = 'agco-page';
  $classes[] = 'agme-page';
  return $classes;
});
get_header();
$agme_is_member = function_exists('usces_is_login') && usces_is_login();
?>
<div class="agco agme" id="agme-top">
  <header class="agco-header" aria-label="お取引サロン様専用メニュー">
    <div class="agco-header__inner">
      <a class="agco-header__logo" href="<?php echo esc_url(home_url('/')); ?>" aria-label="AG FLOLIST トップページ">
        <img src="<?php echo esc_url(home_url('/img/common/logo.svg')); ?>" alt="AG FLOLIST" width="185" height="48">
      </a>
      <span class="agco-header__rule" aria-hidden="true"></span>
      <nav class="agco-header__nav" id="agco-nav" aria-label="専用ページ内メニュー">
        <a href="<?php echo esc_url(home_url('/partner-salon/')); ?>">お取引サロン様専用ページ</a>
        <a href="#agme-products" aria-current="page">業務用美容機器</a>
        <a href="<?php echo esc_url(home_url('/partner-salon/#agco-quick')); ?>">追加発注</a>
        <a href="<?php echo esc_url(home_url('/partner-salon/#agco-info')); ?>">ご案内</a>
      </nav>
      <a class="agco-header__member" href="<?php echo esc_url(home_url('/usces-member/')); ?>">
        <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
          <circle cx="12" cy="8" r="3.4" />
          <path d="M5.3 20v-2.1a6.7 6.7 0 0 1 13.4 0V20" />
        </svg>
        <?php echo $agme_is_member ? 'マイページ' : '会員ログイン'; ?><span aria-hidden="true"> ›</span>
      </a>
      <button class="agco-header__toggle" type="button" aria-controls="agco-nav" aria-expanded="false" aria-label="専用メニューを開く"><span></span><span></span><span></span></button>
    </div>
  </header>
  <main id="agme-main">
    <section class="agme-hero" aria-labelledby="agme-title">
      <div class="agme-container agme-hero__layout">
        <div class="agme-hero__copy">
          <p class="agme-overline">AG FLOLIST <span>／</span> BEAUTY EQUIPMENT</p>
          <h1 id="agme-title">サロンの可能性を、<br>もっと広げる美容機器。</h1>
          <p>フェイシャルから脱毛・制毛、サロン設備まで。<br>施術メニューやお客様のニーズに寄り添う機器をご紹介します。</p>
          <a class="agme-text-link" href="#agme-products">商品ラインアップを見る <span aria-hidden="true">↓</span></a>
        </div>
        <div class="agme-hero__art" aria-hidden="true">
          <div class="agme-hero__ring"></div>
          <div class="agme-hero__shape"></div><span>Professional<br>Beauty<br>Equipment</span>
        </div>
      </div>
    </section>
    <nav class="agme-breadcrumb" aria-label="パンくずリスト">
      <div class="agme-container"><a href="<?php echo esc_url(home_url('/')); ?>">HOME</a><span aria-hidden="true"> / </span><a href="<?php echo esc_url(home_url('/partner-salon/')); ?>">既存のサロン様へ</a><span aria-hidden="true"> / </span><span aria-current="page">業務用美容機器</span></div>
    </nav>
    <section class="agme-section" id="agme-products" aria-labelledby="agme-list-title">
      <div class="agme-container">
        <div class="agme-section__intro">
          <div>
            <p class="agme-overline">EQUIPMENT LINEUP</p>
            <h2 id="agme-list-title">業務用美容機器<span>・関連製品</span></h2>
          </div>
          <p>目的に合わせて、理想の一台を。<br>ご導入に関するご質問もお気軽にご相談ください。</p>
        </div>
        <div class="agme-filters" role="group" aria-label="機器をカテゴリで絞り込む">
          <button type="button" class="is-active" data-filter="all" aria-pressed="true">すべて <span>08</span></button>
          <button type="button" data-filter="facial" aria-pressed="false">フェイシャル <span>03</span></button>
          <button type="button" data-filter="hair" aria-pressed="false">脱毛・制毛 <span>03</span></button>
          <button type="button" data-filter="home" aria-pressed="false">ホームケア <span>01</span></button>
          <button type="button" data-filter="salon" aria-pressed="false">サロン設備 <span>01</span></button>
        </div>
        <div class="agme-products" id="agme-list">
          <article class="agme-card" data-category="facial" id="agme-bhf">
            <div class="agme-card__media">
              <span class="agme-card__index">01 / 08</span>
              <img src="<?php echo esc_url(home_url('/img/machine/bhf.webp')); ?>" alt="BHF 製品写真（確認用）" loading="eager" decoding="async" width="560" height="390">
            </div>
            <div class="agme-card__main">
              <p class="agme-card__kind">FACIAL <span>— フェイシャル機</span></p>
              <h3>BHF</h3>
              <p class="agme-card__description">サロンのフェイシャルメニューを支えるトリートメントマシン。7MHzのハンドピースを備え、施術メニューの幅を広げます。</p>
              <details class="agme-card__spec">
                <summary>主な仕様・ラインアップ <span aria-hidden="true">＋</span></summary>
                <dl>
                  <div>
                    <dt>本体サイズ</dt>
                    <dd>460 × 316 × 351 mm</dd>
                  </div>
                  <div>
                    <dt>重量</dt>
                    <dd>8 kg</dd>
                  </div>
                  <div>
                    <dt>電源</dt>
                    <dd>AC100V・50/60Hz</dd>
                  </div>
                </dl>
              </details>
              <a class="agme-card__cta" href="<?php echo esc_url(home_url('/contact/')); ?>">導入について相談する <span aria-hidden="true">↗</span></a>
            </div>
          </article>
          <article class="agme-card" data-category="home" id="agme-oltreboost">
            <div class="agme-card__media">
              <span class="agme-card__index">02 / 08</span>
              <img src="<?php echo esc_url(home_url('/img/machine/oltreboost.webp')); ?>" alt="OLTREBOOST 製品写真（確認用）" loading="eager" decoding="async" width="560" height="390">
            </div>
            <div class="agme-card__main">
              <p class="agme-card__kind">HOME BEAUTY <span>— 家庭用光美容器</span></p>
              <h3>OLTREBOOST</h3>
              <p class="agme-card__description">業務用美容機器で培ったノウハウをもとに開発された家庭用光美容器。ホームケアの提案に役立つ選択肢です。</p>
              <details class="agme-card__spec">
                <summary>主な仕様・ラインアップ <span aria-hidden="true">＋</span></summary>
                <dl>
                  <div>
                    <dt>種類</dt>
                    <dd>家庭用光美容器</dd>
                  </div>
                </dl>
              </details>
              <a class="agme-card__cta" href="<?php echo esc_url(home_url('/contact/')); ?>">導入について相談する <span aria-hidden="true">↗</span></a>
            </div>
          </article>
          <article class="agme-card" data-category="hair" id="agme-oltre">
            <div class="agme-card__media">
              <span class="agme-card__index">03 / 08</span>
              <img src="<?php echo esc_url(home_url('/img/machine/oltre.webp')); ?>" alt="OLTRE シリーズ 製品写真（確認用）" loading="eager" decoding="async" width="560" height="390">
            </div>
            <div class="agme-card__main">
              <p class="agme-card__kind">OFF HAIR <span>— 業務用脱毛機</span></p>
              <h3>OLTRE シリーズ</h3>
              <p class="agme-card__description">サロンの脱毛・制毛メニューに対応する機器シリーズ。UNOとDUEから、設置環境や施術スタイルに合わせて検討できます。</p>
              <details class="agme-card__spec">
                <summary>主な仕様・ラインアップ <span aria-hidden="true">＋</span></summary>
                <dl>
                  <div>
                    <dt>OLTRE UNO</dt>
                    <dd>90 × 45 × 75 cm／66 kg</dd>
                  </div>
                  <div>
                    <dt>OLTRE DUE</dt>
                    <dd>30 × 53 × 64 cm／25 kg</dd>
                  </div>
                  <div>
                    <dt>電源</dt>
                    <dd>UNO：100V 20A／DUE：100V 15A</dd>
                  </div>
                </dl>
              </details>
              <a class="agme-card__cta" href="<?php echo esc_url(home_url('/contact/')); ?>">導入について相談する <span aria-hidden="true">↗</span></a>
            </div>
          </article>
          <article class="agme-card" data-category="facial" id="agme-metaject">
            <div class="agme-card__media">
              <span class="agme-card__index">04 / 08</span>
              <img src="<?php echo esc_url(home_url('/img/machine/metaject.webp')); ?>" alt="メタジェクト 製品写真（確認用）" loading="eager" decoding="async" width="560" height="390">
            </div>
            <div class="agme-card__main">
              <p class="agme-card__kind">FACIAL <span>— フェイシャル機</span></p>
              <h3>メタジェクト</h3>
              <p class="agme-card__description">エステの施術に新しい選択肢を加えるフェイシャル機器。肌の状態に合わせたトリートメント設計をサポートします。</p>
              <details class="agme-card__spec">
                <summary>主な仕様・ラインアップ <span aria-hidden="true">＋</span></summary>
                <dl>
                  <div>
                    <dt>本体サイズ</dt>
                    <dd>7 × 23 × 24 cm</dd>
                  </div>
                  <div>
                    <dt>重量</dt>
                    <dd>1.1 kg</dd>
                  </div>
                  <div>
                    <dt>電源</dt>
                    <dd>100–240V・7VA</dd>
                  </div>
                </dl>
              </details>
              <a class="agme-card__cta" href="<?php echo esc_url(home_url('/contact/')); ?>">導入について相談する <span aria-hidden="true">↗</span></a>
            </div>
          </article>
          <article class="agme-card" data-category="hair" id="agme-msft">
            <div class="agme-card__media">
              <span class="agme-card__index">05 / 08</span>
              <img src="<?php echo esc_url(home_url('/img/machine/msft.webp')); ?>" alt="M.S.F.T.KRYPTON S.E.V. 製品写真（確認用）" loading="eager" decoding="async" width="560" height="390">
            </div>
            <div class="agme-card__main">
              <p class="agme-card__kind">OFF HAIR <span>— 業務用脱毛機</span></p>
              <h3>M.S.F.T.KRYPTON S.E.V.</h3>
              <p class="agme-card__description">脱毛・制毛のトリートメントに使う多機能マシン。連射機能を備え、サロンの幅広い施術プランに対応します。</p>
              <details class="agme-card__spec">
                <summary>主な仕様・ラインアップ <span aria-hidden="true">＋</span></summary>
                <dl>
                  <div>
                    <dt>本体サイズ</dt>
                    <dd>21 × 44 × 52 cm</dd>
                  </div>
                  <div>
                    <dt>重量</dt>
                    <dd>16 kg</dd>
                  </div>
                  <div>
                    <dt>電源</dt>
                    <dd>100V・10A</dd>
                  </div>
                </dl>
              </details>
              <a class="agme-card__cta" href="<?php echo esc_url(home_url('/contact/')); ?>">導入について相談する <span aria-hidden="true">↗</span></a>
            </div>
          </article>
          <article class="agme-card" data-category="hair" id="agme-ps">
            <div class="agme-card__media">
              <span class="agme-card__index">06 / 08</span>
              <img src="<?php echo esc_url(home_url('/img/machine/ps.webp')); ?>" alt="P.S.KRYPTON 製品写真（確認用）" loading="eager" decoding="async" width="560" height="390">
            </div>
            <div class="agme-card__main">
              <p class="agme-card__kind">OFF HAIR <span>— 業務用脱毛機</span></p>
              <h3>P.S.KRYPTON</h3>
              <p class="agme-card__description">サロン向けの光美容機器。施術品質や使い勝手を重視したいサロン様に向けた機種です。</p>
              <details class="agme-card__spec">
                <summary>主な仕様・ラインアップ <span aria-hidden="true">＋</span></summary>
                <dl>
                  <div>
                    <dt>本体サイズ</dt>
                    <dd>103 × 48 × 50 cm</dd>
                  </div>
                  <div>
                    <dt>重量</dt>
                    <dd>65 kg</dd>
                  </div>
                  <div>
                    <dt>電源</dt>
                    <dd>100V・10A</dd>
                  </div>
                </dl>
              </details>
              <a class="agme-card__cta" href="<?php echo esc_url(home_url('/contact/')); ?>">導入について相談する <span aria-hidden="true">↗</span></a>
            </div>
          </article>
          <article class="agme-card" data-category="facial" id="agme-eporex">
            <div class="agme-card__media">
              <span class="agme-card__index">07 / 08</span>
              <img src="<?php echo esc_url(home_url('/img/machine/eporex.webp')); ?>" alt="EPOREX 製品写真（確認用）" loading="eager" decoding="async" width="560" height="390">
            </div>
            <div class="agme-card__main">
              <p class="agme-card__kind">FACIAL & BODY <span>— エレクトロポレーション</span></p>
              <h3>EPOREX</h3>
              <p class="agme-card__description">「イソフォレーシス」と呼ばれる独自の導入法を用いた美容機器。フェイシャルからボディまで幅広いトリートメントに対応します。</p>
              <details class="agme-card__spec">
                <summary>主な仕様・ラインアップ <span aria-hidden="true">＋</span></summary>
                <dl>
                  <div>
                    <dt>本体サイズ</dt>
                    <dd>55 × 26 × 23 cm</dd>
                  </div>
                  <div>
                    <dt>重量</dt>
                    <dd>6.8 kg（本体のみ）</dd>
                  </div>
                  <div>
                    <dt>電源</dt>
                    <dd>100V・40VA</dd>
                  </div>
                </dl>
              </details>
              <a class="agme-card__cta" href="<?php echo esc_url(home_url('/contact/')); ?>">導入について相談する <span aria-hidden="true">↗</span></a>
            </div>
          </article>
          <article class="agme-card" data-category="salon" id="agme-lec">
            <div class="agme-card__media">
              <span class="agme-card__index">08 / 08</span>
              <img src="<?php echo esc_url(home_url('/img/machine/lec.webp')); ?>" alt="Living Earth Crafts 製品写真（確認用）" loading="eager" decoding="async" width="560" height="390">
            </div>
            <div class="agme-card__main">
              <p class="agme-card__kind">SALON FURNITURE <span>— サロン用ベッド</span></p>
              <h3>Living Earth Crafts</h3>
              <p class="agme-card__description">世界各地のスパやリゾートでも採用されているサロン用ベッドブランド。電動・手動・ポータブルなど複数のタイプがあります。</p>
              <details class="agme-card__spec">
                <summary>主な仕様・ラインアップ <span aria-hidden="true">＋</span></summary>
                <dl>
                  <div>
                    <dt>ラインアップ</dt>
                    <dd>電動／手動／ポータブルベッド</dd>
                  </div>
                  <div>
                    <dt>関連製品</dt>
                    <dd>ベッド付属品</dd>
                  </div>
                </dl>
              </details>
              <a class="agme-card__cta" href="<?php echo esc_url(home_url('/contact/')); ?>">導入について相談する <span aria-hidden="true">↗</span></a>
            </div>
          </article>
        </div>
        <p class="agme-footnote">※仕様・ラインアップは参考情報です。最新の販売状況・価格・詳細についてはお問い合わせください。画像はお預かりした参考画像から作成した確認用です。</p>
      </div>
    </section>
    <section class="agme-support" aria-labelledby="agme-support-title">
      <div class="agme-container agme-support__inner">
        <div>
          <p class="agme-overline">YOUR BEAUTY PARTNER</p>
          <h2 id="agme-support-title">サロンに合う一台を、<br>一緒に考えます。</h2>
        </div>
        <div>
          <p>機器選びに迷われた際は、施術メニューや導入の目的をお聞かせください。<br>AG FLOLISTがご相談を承ります。</p>
          <a class="agme-support__button" href="<?php echo esc_url(home_url('/contact/')); ?>">美容機器についてお問い合わせ <span aria-hidden="true">↗</span></a>
        </div>
      </div>
    </section>
  </main>
  <footer class="agco-footer">
    <div class="agco-container agco-footer__inner">
      <a class="agco-footer__logo" href="<?php echo esc_url(home_url('/')); ?>" aria-label="AG FLOLIST トップページ"><img src="<?php echo esc_url(home_url('/img/common/logo.svg')); ?>" alt="AG FLOLIST" width="185" height="48"></a>
      <nav aria-label="サロン様向けフッターリンク"><a href="<?php echo esc_url(home_url('/partner-salon/')); ?>">専用ページに戻る</a><a href="<?php echo esc_url(home_url('/contact/')); ?>">お問い合わせ</a><a href="<?php echo esc_url(home_url('/policy/')); ?>">プライバシーポリシー</a></nav>
    </div>
  </footer>
</div>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const filters = document.querySelectorAll('.agme-filters button[data-filter]');
    const cards = document.querySelectorAll('.agme-card[data-category]');
    filters.forEach(function(button) {
      button.addEventListener('click', function() {
        const active = button.dataset.filter;
        filters.forEach(function(other) {
          const selected = other === button;
          other.classList.toggle('is-active', selected);
          other.setAttribute('aria-pressed', String(selected));
        });
        cards.forEach(function(card) {
          card.hidden = active !== 'all' && card.dataset.category !== active;
        });
      });
    });
    const menuButton = document.querySelector('.agme .agco-header__toggle');
    const menu = document.querySelector('.agme #agco-nav');
    if (menuButton && menu) {
      const close = function() {
        menu.classList.remove('is-open');
        menuButton.setAttribute('aria-expanded', 'false');
        menuButton.setAttribute('aria-label', '専用メニューを開く');
      };
      menuButton.addEventListener('click', function() {
        const show = menuButton.getAttribute('aria-expanded') !== 'true';
        menu.classList.toggle('is-open', show);
        menuButton.setAttribute('aria-expanded', String(show));
        menuButton.setAttribute('aria-label', show ? '専用メニューを閉じる' : '専用メニューを開く');
      });
      menu.querySelectorAll('a').forEach(function(a) {
        a.addEventListener('click', close);
      });
      document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') close();
      });
      document.addEventListener('click', function(e) {
        if (!e.target.closest('.agme .agco-header')) close();
      });
    }
  });
</script>
<?php get_footer(); ?>