<?php
/**
 * DLSeller Auto Billing Page Template
 * For WCEX DL Seller
 *
 * @package Welcart
 * @subpackage Welcart_Basic
 */

wp_enqueue_script( 'jquery' );
get_header();
?>

	<div id="primary" class="site-content">
		<div id="content" class="member-page" role="main">

		<?php
		if ( have_posts() ) :
			usces_remove_filter();
			?>

			<article class="post" id="wc_<?php usces_page_name(); ?>">

				<h1 class="member_page_title"><?php esc_html_e( 'Automatic Recurring Billing Information', 'dlseller' ); ?></h1>

				<div id="memberpages">
					<ul class="member_submenu">
						<li class="member-top"><a href="<?php usces_url( 'member' ); ?>"><?php esc_html_e( 'Back to the member page.', 'usces' ); ?></a></li>
						<li><?php do_action( 'usces_action_auto_billing_submenu_list' ); ?></li>
					</ul>
					<div class="whitebox">
						<div id="memberinfo">

							<div class="header_explanation">
								<?php do_action( 'dlseller_action_auto_billing_page_header' ); ?>
							</div>
							<div class="currency_code"><?php esc_html_e( 'Currency', 'usces' ); ?> : <?php usces_crcode(); ?></div>

							<?php dlseller_subscription_list(); ?>

							<div class="footer_explanation">
								<?php do_action( 'dlseller_action_auto_billing_page_footer' ); ?>
							</div>

						</div><!-- end of memberinfo -->
					</div><!-- end of whitebox -->
				</div><!-- end of memberpages -->
			</article><!-- end of post -->

		<?php else : ?>

			<p><?php esc_html_e( 'Sorry, no posts matched your criteria.', 'usces' ); ?></p>

		<?php endif; ?>
		</div><!-- #content -->
	</div><!-- #primary -->

<?php
get_footer();
