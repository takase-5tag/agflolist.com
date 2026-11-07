<?php

/**
 * @package Welcart
 * @subpackage Welcart_Basic
 */

get_header(); ?>

<!-- end header -->
<section id="sctl" class="ag-legal ag-legal--sctl">
  <div class="sctl_inner warp-850 ag-legal__container">
    <header class="ag-legal__header">
      <span class="ag-legal__eyebrow">LEGAL NOTICE</span>
      <h2>特定商取引法に関する表示</h2>
    </header>
    <article id="other" class="ag-legal__content">
      <table class="ag-legal__spec-table">
        <tbody>
          <tr>
            <th scope="row">店舗名</th>
            <td>株式会社フローリストジャパン</td>
          </tr>

          <tr>
            <th scope="row">所在地</th>
            <td>〒850-0852　長崎県長崎市万屋町2-20</td>
          </tr>

          <tr>
            <th scope="row">代表電話番号</th>
            <td><a href="tel:0958186333">095-801-3637</a></td>
          </tr>

          <tr>
            <th scope="row">運営販売責任者</th>
            <td>田栗　憲哉</td>
          </tr>

          <tr>
            <th scope="row">メールアドレス</th>
            <td><a href="mailto:top★agflolist.com">top★agflolist.com</a></td>
          </tr>

          <tr>
            <th scope="row">取扱商品</th>
            <td>美容関連商品</td>
          </tr>

          <tr>
            <th scope="row">追加手数料等の追加料金</th>
            <td>送料</td>
          </tr>

          <tr>
            <th scope="row">お支払方法</th>
            <td>クレジット払い</td>
          </tr>
          <tr>
            <th scope="row">販売価格</th>
            <td>各商品ページに記載の金額</td>
          </tr>

          <!--tr>
            <th scope="row">交換および返品について</th>
            <td>
              <p>お手元に届いた商品に、品違い、破損等があった場合は、当社サイト上の「お問い合わせ」までご連絡ください。但し、在庫がない場合は交換できない場合があります。交換できない場合、返金させて頂きます。
                また、商品の特性上、お客様都合による返品・交換はできないものとします。</p>
                
            </td>
          </tr-->
          <tr>
            <th scope="row">商品代金以外の必要料金</th>
            <td>
              <p>代引き手数料の金額を明記してください。<br>
                （例）代引手数料（税抜）：1万円未満300円、3万円未満400円<br>3万円以上600円、10万円以上30万円まで1000円</p>
            </td>
          </tr>
          <tr>
            <th scope="row">返品/不良品について</th>
            <td>
              <p>不良品や品違いの場合、返品送料は販売業者負担となる旨を明記ください。<br>
                例「万が一不良品や品違いであった場合は、商品到着後3日以内に弊社にご連絡の上、送料着払いにてご返品後、良品と交換もしくは返金いたします。</p>
            </td>
          </tr>
          <tr>
            <th scope="row">引渡時期</th>
            <td>
              <p>注文は1～3営業日以内に処理され、商品は10日以内に到着します。<br>
                但し、在庫がない場合は上記より遅れる場合があります。</p>
            </td>
          </tr>
          <tr>
            <th scope="row">退会手続きについて</th>
            <td>
              <p>
                マイページに「退会する」のボタンが表示されない場合、退会手続きはメールにてご連絡いただく必要がございます。<br>
                上記メール宛に会員番号、お名前を記載の上、退会希望の旨をご連絡ください。当社より退会手続きについてメールでご案内させていただきます。
              </p>
            </td>
          </tr>
          <tr>
            <th scope="row">販売対象地域</th>
            <td>
              <p>
                日本国内
              </p>
            </td>
          </tr>
        </tbody>
      </table>
    </article>
    <nav class="ag-legal__nav" aria-label="関連するご案内">
      <span class="ag-legal__nav-title">POLICY &amp; GUIDE</span>
      <ul class="ag-legal__nav-list">
        <li><a href="<?php echo esc_url(home_url('/agreement/')); ?>">利用規約</a></li>
        <li><a href="<?php echo esc_url(home_url('/guide/')); ?>">ご利用ガイド</a></li>
        <li><a href="<?php echo esc_url(home_url('/sctl/')); ?>" aria-current="page">特定商取引法に関する表示</a></li>
        <li><a href="<?php echo esc_url(home_url('/policy/')); ?>">プライバシーポリシー</a></li>
      </ul>
    </nav>
  </div>
</section>



<?php get_footer(); ?>