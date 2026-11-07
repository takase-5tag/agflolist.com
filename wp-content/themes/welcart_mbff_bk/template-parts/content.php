<?php

/**
 * Content Template
 *
 * @package Welcart
 * @subpackage Welcart_Basic
 */

?>

<article <?php post_class(); ?> id="post-<?php the_ID(); ?>">

	<h1 class="post__title"><?php the_title(); ?></h1>

	<?php
	if (is_single()) :
		if (! usces_is_item()) :
	?>
			<div class="entry-meta">
				<span class="date"><time><?php the_date(); ?></time></span>
				<span class="cat"><?php esc_html_e('カテゴリー'); ?> <?php the_category(','); ?></span>
			</div>
	<?php
		endif;
	endif;
	?>

	<div class="entry-content">
		<?php the_content(__('(more...)')); ?>
	</div><!-- .entry-content -->
	<div class="post-navigation">
		<div class="nav-prev"><?php previous_post_link('%link', '&lt; 前の記事'); ?></div>
		<div class="nav-next"><?php next_post_link('%link', '次の記事 &gt;'); ?></div>
	</div>
</article>