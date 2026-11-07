<?php
/**
 * Index Template
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
		while ( have_posts() ) :
			the_post();
			?>
		<article <?php post_class(); ?> id="post-<?php the_ID(); ?>">
			<h2 class="entry-title"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h2>

			<div class="entry-meta">
				<span class="date"><time><?php the_time( get_option( 'date_format' ) ); ?></time></span>
				<span class="cat"><?php esc_html_e( 'Filed under:' ); ?> <?php the_category( ',' ); ?></span>
				<span class="tag"><?php the_tags( __( 'Tags: ' ) ); ?></span>
				<span class="author"><?php the_author(); ?><?php edit_post_link( __( 'Edit This' ) ); ?></span>
			</div><!-- .entry-meta -->

			<div class="entry-content">
				<?php the_content(); ?>
			</div><!-- .entry-content -->
		</article>
			<?php
		endwhile;
	else :
		?>
		<p><?php esc_html_e( 'Sorry, no posts matched your criteria.', 'usces' ); ?></p>
	<?php endif; ?>

		<?php
		$args           = array(
			'type'      => 'list',
			'prev_text' => __( ' &laquo; ', 'welcart_basic' ),
			'next_text' => __( ' &raquo; ', 'welcart_basic' ),
		);
		$paginate_links = paginate_links( $args );
		if ( $paginate_links ) :
			?>
			<div class="pagination_wrapper">
				<?php echo wp_kses_post( $paginate_links ); ?>
			</div><!-- .pagenation-wrapper -->
			<?php
		endif;
		?>

		</div><!-- #content -->
	</div><!-- #primary -->

<?php
get_sidebar( 'home' );
get_footer();
