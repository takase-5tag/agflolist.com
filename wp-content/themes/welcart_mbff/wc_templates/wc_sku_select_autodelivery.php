<?php
/**
 * Product Page Template
 * For WCEX SKU Select, WCEX Auto Delivery
 *
 * @package Welcart
 * @subpackage Welcart_Basic
 */

get_header();
?>

	<div id="primary" class="site-content">
		<div id="content" role="main">

		<?php
		if ( have_posts() ) :
			the_post();
			?>

			<article <?php post_class(); ?> id="post-<?php the_ID(); ?>">

				<header class="item-header">
					<h1 class="item_page_title"><?php the_title(); ?></h1>
				</header><!-- .item-header -->

				<div class="storycontent">

					<?php
					usces_remove_filter();
					usces_the_item();
					usces_have_skus();
					?>

					<div id="itempage">

						<div id="img-box">
							<div class="itemimg">
								<a href="<?php usces_the_itemImageURL( 0 ); ?>" <?php echo apply_filters( 'usces_itemimg_anchor_rel', null ); ?>>
									<?php usces_the_itemImage( 0, 335, 335, $post ); ?>
								</a>
								<?php do_action( 'usces_theme_favorite_icon' ); ?>
							</div>
						<?php
						$image_ids = usces_get_itemSubImageNums();
						if ( ! empty( $image_ids ) ) :
							?>
							<div class="itemsubimg">
							<?php foreach ( $image_ids as $image_id ) : ?>
								<a href="<?php usces_the_itemImageURL( $image_id ); ?>" <?php echo apply_filters( 'usces_itemimg_anchor_rel', null ); ?>>
									<?php usces_the_itemImage( $image_id, 135, 135, $post ); ?>
								</a>
							<?php endforeach; ?>
							</div>
							<?php
						endif;
						?>
						</div><!-- #img-box -->

						<div class="detail-box">
							<h2 class="item-name"><?php usces_the_itemName(); ?></h2>
							<div class="itemcode">(<?php usces_the_itemCode(); ?>)</div>
							<?php wel_campaign_message(); ?>
							<div class="item-description">
								<?php the_content(); ?>
							</div>

						</div><!-- .detail-box -->

						<div class="item-info">
							<?php
							$item_custom = usces_get_item_custom( $post->ID, 'list', 'return' );
							if ( $item_custom ) :
								echo wp_kses_post( $item_custom );
							endif;
							?>

							<?php do_action( 'usces_action_single_item_outform' ); ?>

						</div><!-- .item-info -->

						<?php usces_assistance_item( $post->ID, __( 'An article concerned', 'usces' ) ); ?>

					</div><!-- #itemspage -->
				</div><!-- .storycontent -->
			</article>

		<?php else : ?>

			<p><?php esc_html_e( 'Sorry, no posts matched your criteria.', 'usces' ); ?></p>

		<?php endif; ?>

		</div><!-- #content -->
	</div><!-- #primary -->

<?php
get_sidebar();
get_footer();
