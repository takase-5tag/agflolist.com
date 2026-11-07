<?php

/**
 * AG FLOLIST common contact CTA | V8
 * Shared by front-page, salon, service and contact templates.
 */
$ag_cta_contact_url = home_url('/contact/');
$ag_cta_primary_url = is_page('contact') ? '#contact-form' : $ag_cta_contact_url;
$ag_cta_form_url = is_page('contact') ? '#contact-form' : $ag_cta_contact_url . '#contact-form';
?>
<section class="ag-unified-cta" id="ag-contact-cta" aria-labelledby="ag-unified-cta-title">
  <div class="ag-unified-cta__inner">
    <div class="ag-unified-cta__copy">
      <h2 id="ag-unified-cta-title">まずは、話すことから。</h2>
      <p>どんなことでも、私たちにご相談ください。<br>あなたの未来を、ともに考えます。</p>
    </div>
    <div class="ag-unified-cta__actions">
      <a class="ag-unified-cta__button ag-unified-cta__button--primary" href="<?php echo esc_url($ag_cta_primary_url); ?>">
        <span class="ag-unified-cta__button-icon" aria-hidden="true">✉</span><span>お問い合わせはこちら</span>
      </a>
      <a class="ag-unified-cta__button ag-unified-cta__button--secondary" href="<?php echo esc_url($ag_cta_form_url); ?>">
        <span>オンライン相談について相談する</span><span class="ag-unified-cta__button-arrow" aria-hidden="true">›</span>
      </a>
    </div>
  </div>
</section>