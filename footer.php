  <footer class="site-footer">
    <div class="site-footer__inner">

      <div class="site-footer__top">
        <div class="site-footer__brand">
          <a href="<?php echo esc_url(home_url('/')); ?>">
            <img src="<?php echo esc_url(home_url('/')); ?>img/common/logo.svg" alt="AG FLOLIST">
          </a>
          <p>美容の仕事を、もっと豊かに。<br>人と人をつなぎ、未来に伴走する。</p>
        </div>

        <nav class="site-footer__nav" aria-label="フッターナビゲーション">
          <ul>
            <li><a href="<?php echo esc_url(home_url('/')); ?>">ABOUT</a></li>
            <li><a href="<?php echo esc_url(home_url('/salon/')); ?>">SALON</a></li>
            <li><a href="<?php echo esc_url(home_url('/company/')); ?>">PARTNER SALON</a></li>
            <li><a href="<?php echo esc_url(home_url('/products/')); ?>">PRODUCTS</a></li>
            <li><a href="<?php echo esc_url(home_url('/journal/')); ?>">JOURNAL</a></li>
            <li><a href="<?php echo esc_url(home_url('/contact/')); ?>">CONTACT</a></li>
          </ul>
        </nav>
      </div>

      <div class="site-footer__bottom">
        <ul class="site-footer__policy">
          <li><a href="<?php echo esc_url(home_url('/agreement/')); ?>">利用規約</a></li>
          <li><a href="<?php echo esc_url(home_url('/guide/')); ?>">ご利用ガイド</a></li>
          <li><a href="<?php echo esc_url(home_url('/sctl/')); ?>">特定商取引法に関する表示</a></li>
          <li><a href="<?php echo esc_url(home_url('/policy/')); ?>">プライバシーポリシー</a></li>
        </ul>

        <p class="site-footer__copy">Copyright &copy; 2026 AG FLOLIST. All Rights Reserved.</p>
      </div>

    </div>
  </footer>

  </div>

  <p class="pagetop"><a href="javascript:;" aria-label="ページ上部へ">PAGE TOP</a></p>

  <script src="<?php echo esc_url(home_url('/')); ?>js/product.js"></script>

  <?php if (function_exists('usces_is_login') && usces_is_login()) : ?>
    <article class="my_account">
      <ul class="my_list">
        <li>
          <a href="<?php echo esc_url(home_url('/usces-member/')); ?>" aria-label="マイページ">
            <img src="<?php echo esc_url(home_url('/')); ?>img/common/user.svg" alt="">
          </a>
        </li>
        <li>
          <a href="<?php echo esc_url(home_url('/usces-cart/')); ?>" aria-label="カート">
            <img src="<?php echo esc_url(home_url('/')); ?>img/common/bag.svg" alt="">
          </a>
        </li>
      </ul>
    </article>
  <?php endif; ?>

  <?php wp_footer(); ?>
  </body>

  </html>