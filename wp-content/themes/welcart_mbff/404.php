<?php

/**
 * AG FLOLIST - custom 404 page.
 *
 * @package Welcart_Basic
 */

// Keep the missing URL as an actual HTTP 404 (do not redirect visitors).
// Load 404-only styling without changing the shared header or functions.php.
wp_enqueue_style(
  'ag-FLOLIST-404',
  get_template_directory_uri() . '/css/ag-404.css',
  array('wc-basic-style'),
  '20261105'
);
get_header();
?>

<main id="primary" class="ag-404-main" role="main">
  <section class="ag-404-hero" aria-labelledby="ag-404-heading">
    <div class="ag-404-container">
      <nav class="ag-404-breadcrumb" aria-label="パンくずリスト">
        <a href="<?php echo esc_url(home_url('/')); ?>">HOME</a>
        <span class="ag-404-breadcrumb__divider" aria-hidden="true">/</span>
        <span aria-current="page">404 PAGE NOT FOUND</span>
      </nav>

      <div class="ag-404-hero__grid">
        <div class="ag-404-art" aria-hidden="true">
          <span class="ag-404-art__circle"></span>
          <span class="ag-404-art__number">404</span>
          <svg class="ag-404-art__flower" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 280 340" fill="none">
            <g stroke="currentColor" stroke-width="1.55" stroke-linecap="round" stroke-linejoin="round">
              <path d="M126 323C127 260 143 223 165 177C180 148 184 122 184 92" />
              <path d="M144 246C102 236 81 214 77 180C112 185 139 207 144 246Z" />
              <path d="M159 202C203 196 224 171 232 144C192 148 166 167 159 202Z" />
              <path d="M128 302C96 290 80 268 79 242C113 250 130 272 128 302Z" />
              <path d="M183 101C167 84 153 65 164 51C174 37 190 52 190 76C198 51 218 43 225 58C233 74 218 88 196 97C219 88 238 96 237 111C235 129 216 131 193 112C207 135 204 157 189 159C173 161 166 142 177 118C158 138 138 137 133 121C128 104 148 95 175 100" />
              <path d="M185 97C184 110 193 121 202 114C211 107 202 94 191 96C188 96 186 97 185 97Z" />
              <path d="M183 100C174 102 166 107 159 112M190 94C189 84 189 72 191 64M198 105C209 107 219 110 227 112M188 115C187 126 187 136 185 147" opacity=".65" />
              <path d="M72 176C61 168 57 152 64 144C72 134 84 141 86 159C92 144 106 139 113 150C120 161 109 174 94 177C108 180 114 193 106 202C97 212 84 202 83 190C80 207 66 214 57 205C47 195 56 183 72 176Z" />
              <path d="M79 174C72 180 77 190 85 188C93 185 91 175 84 173L79 174Z" />
              <path d="M83 188C97 214 108 224 131 230" />
            </g>
            <circle cx="219" cy="40" r="2.5" fill="currentColor" opacity=".55" />
            <circle cx="245" cy="80" r="2" fill="currentColor" opacity=".5" />
            <circle cx="49" cy="222" r="2" fill="currentColor" opacity=".4" />
          </svg>
          <span class="ag-404-art__caption">AG FLOLIST</span>
        </div>

        <div class="ag-404-copy">
          <p class="ag-404-copy__eyebrow">PAGE NOT FOUND</p>
          <h1 id="ag-404-heading">お探しのページが<br>見つかりませんでした。</h1>
          <span class="ag-404-copy__rule" aria-hidden="true"></span>
          <p class="ag-404-copy__description">
            申し訳ございません。<br>
            お探しのページは、移動または削除されたか、<br class="ag-404-desktop-only">
            URLが変更された可能性があります。<br>
            お手数ですが、以下のリンクよりお進みください。
          </p>
          <div class="ag-404-actions">
            <a class="ag-404-button ag-404-button--primary" href="<?php echo esc_url(home_url('/')); ?>">
              トップページへ戻る
              <span class="ag-404-button__arrow" aria-hidden="true">↗</span>
            </a>
            <a class="ag-404-button ag-404-button--secondary" href="<?php echo esc_url(home_url('/category/item/')); ?>">
              商品について見る
              <span class="ag-404-button__arrow" aria-hidden="true">↗</span>
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="ag-404-guide" aria-labelledby="ag-404-guide-heading">
    <div class="ag-404-container">
      <div class="ag-404-guide__heading">
        <p class="ag-404-guide__eyebrow">EXPLORE AG FLOLIST</p>
        <h2 id="ag-404-guide-heading">こちらのページもご覧ください</h2>
      </div>
      <div class="ag-404-guide__grid">
        <a class="ag-404-guide__item" href="<?php echo esc_url(home_url('/salon/')); ?>">
          <span class="ag-404-guide__en">SALON</span>
          <span class="ag-404-guide__jp">サロンの方へ</span>
          <span class="ag-404-guide__arrow" aria-hidden="true">↗</span>
        </a>
        <a class="ag-404-guide__item" href="<?php echo esc_url(home_url('/partner-salon/')); ?>">
          <span class="ag-404-guide__en">PARTNER</span>
          <span class="ag-404-guide__jp">既存のお取引サロン様へ</span>
          <span class="ag-404-guide__arrow" aria-hidden="true">↗</span>
        </a>
        <a class="ag-404-guide__item" href="<?php echo esc_url(home_url('/contact/')); ?>">
          <span class="ag-404-guide__en">CONTACT</span>
          <span class="ag-404-guide__jp">お問い合わせ</span>
          <span class="ag-404-guide__arrow" aria-hidden="true">↗</span>
        </a>
      </div>
    </div>
  </section>
</main>

<?php get_footer(); ?>