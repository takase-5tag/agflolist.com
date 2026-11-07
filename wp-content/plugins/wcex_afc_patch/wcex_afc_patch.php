<?php
/**
 * Plugin Name: WCEX Patch for AFC
 * Plugin URI: https://www.welcart.com/
 * Description: このプラグインはWelcart商品でAdvanced Custom Fields PROを利用できるようにするためのパッチです。
 * Version: 6.1.6
 * Author: Collne Inc.
 * Author URI: https://www.collne.com/
 *
 * @package WCEX Patch for AFC
 */

if ( ! defined( 'USCES_VERSION' ) ) {
	return;
}

/**
 * Patch to enable Advanced Custom Fields for Welcart items
 *
 * @return void
 */
function collne_afc_patch() {
	global $acf;
	if ( $acf ) {
		if ( false !== strpos( $acf->settings['slug'], 'pro' ) ) {
			acf_new_instance( 'acf_form_welcart_item_pro' );
		} else {
			acf_new_instance( 'acf_form_welcart_item' );
		}
	}
}
add_action( 'init', 'collne_afc_patch', 20 );

if ( ! class_exists( 'acf_form_welcart_item' ) ) :

	/**
	 * ACF for Welcart items.
	 */
	class acf_form_welcart_item {

		/**
		 * The first field groups style CSS.
		 *
		 * @var string
		 */
		var $style = '';

		/**
		 * Sets up the class functionality.
		 * __construct
		 *
		 * @date 5/03/2014
		 * @since 5.0.0
		 *
		 * @return void
		 */
		public function __construct() {

			// global.
			global $post, $pagenow, $typenow;

			// initialize on usces_itemedit screens.
			if ( 'admin.php' == $pagenow && isset( $_GET['page'] ) && 'usces_itemedit' == $_GET['page'] && isset( $_GET['post'] ) ) {
				$this->initialize();
			}
			if ( 'admin.php' == $pagenow && isset( $_GET['page'] ) && 'usces_itemnew' == $_GET['page'] ) {
				$this->initialize();
			}

			// save.
			add_filter( 'wp_insert_post_empty_content', array( $this, 'wp_insert_post_empty_content' ), 10, 2 );
			add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );
		}


		/**
		 * Sets up Form functionality.
		 * initialize
		 *
		 * @date 19/9/18
		 * @since 5.7.6
		 *
		 * @return void
		 */
		public function initialize() {

			// globals.
			global $typenow;

			// restrict specific post types.
			$restricted = array( 'acf-field-group', 'attachment' );
			if ( in_array( $typenow, $restricted ) ) {
				return;
			}

			// enqueue scripts.
			acf_enqueue_scripts();

			// actions.
			add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 10, 2 );
		}

		/**
		 * Adds ACF metaboxes for the given $post_type and $post.
		 * add_meta_boxes
		 *
		 * @date 19/9/18
		 * @since 5.7.6
		 *
		 * @param string  $post_type The post type.
		 * @param WP_Post $post The post being edited.
		 * @return void
		 */
		public function add_meta_boxes( $post_type, $post ) {
			if ( isset( $post->ID ) ) {
				wp_enqueue_media( array( 'post' => $post->ID ) );
			}
			// enqueue scripts uploader of acf.
			acf_enqueue_uploader();

			// Storage for localized postboxes.
			$postboxes = array();

			// Get field groups for this screen.
			$field_groups = acf_get_field_groups(
				array(
					'post_id'   => $post->ID,
					'post_type' => $post_type,
				)
			);

			// Loop over field groups.
			if ( $field_groups ) {
				foreach ( $field_groups as $field_group ) {

					// vars.
					$id       = "acf-{$field_group['key']}"; // acf-group_123.
					$title    = $field_group['title'];       // Group 1.
					$context  = $field_group['position'];    // normal, side, acf_after_title.
					$priority = 'high';                      // high, core, default, low.

					// Reduce priority for sidebar metaboxes for best position.
					if ( 'side' == $context ) {
						$priority = 'core';
					}

					/**
					 * Filters the metabox priority.
					 *
					 * @date 23/06/12
					 * @since 3.1.8
					 *
					 * @param string $priority The metabox priority (high, core, default, low).
					 * @param array  $field_group The field group array.
					 */
					$priority = apply_filters( 'acf/input/meta_box_priority', $priority, $field_group );

					// Localize data.
					$postboxes[] = array(
						'id'    => $id,
						'key'   => $field_group['key'],
						'style' => $field_group['style'],
						'label' => $field_group['label_placement'],
						'edit'  => acf_get_field_group_edit_link( $field_group['ID'] ),
					);

					// Add the meta box.
					add_meta_box( $id, $title, array( $this, 'render_meta_box' ), $post_type, $context, $priority, array( 'field_group' => $field_group ) );

				}

				// Set style from first field group.
				$this->style = acf_get_field_group_style( $field_groups[0] );

				// Localize postboxes.
				acf_localize_data(
					array(
						'postboxes' => $postboxes,
					)
				);
			}

			// remove postcustom metabox (removes expensive SQL query).
			if ( acf_get_setting( 'remove_wp_meta_box' ) ) {
				remove_meta_box( 'postcustom', false, 'normal' );
			}

			// Add hidden input fields.
			add_action( 'edit_form_after_title', array( $this, 'edit_form_after_title' ) );

			/**
			 * Fires after metaboxes have been added.
			 *
			 * @date 13/12/18
			 * @since 5.8.0
			 *
			 * @param string  $post_type The post type.
			 * @param WP_Post $post The post being edited.
			 * @param array   $field_groups The field groups added.
			 */
			do_action( 'acf/add_meta_boxes', $post_type, $post, $field_groups );
		}

		/**
		 * Called after the title adn before the content editor.
		 * edit_form_after_title
		 *
		 *  @date 19/9/18
		 *  @since 5.7.6
		 *
		 *  @return void
		 */
		public function edit_form_after_title() {

			// globals.
			global $post, $wp_meta_boxes;

			// render post data.
			acf_form_data(
				array(
					'screen'  => 'post',
					'post_id' => $post->ID,
				)
			);

			// render 'acf_after_title' metaboxes.
			do_meta_boxes( get_current_screen(), 'acf_after_title', $post );

			// render dynamic field group style.
			echo '<style type="text/css" id="acf-style">' . $this->style . '</style>';
		}

		/**
		 * Renders the ACF metabox HTML.
		 * render_meta_box
		 *
		 * @date 19/9/18
		 * @since 5.7.6
		 *
		 * @param WP_Post $post The post being edited.
		 * @param array   $metabox The add_meta_box() args.
		 * @return void
		 */
		public function render_meta_box( $post, $metabox ) {

			// vars.
			$id          = $metabox['id'];
			$field_group = $metabox['args']['field_group'];

			// Render fields.
			$fields = acf_get_fields( $field_group );
			acf_render_fields( $fields, $post->ID, 'div', $field_group['instruction_placement'] );
		}

		/**
		 * Allows WP to insert a new post without title or post_content if ACF data exists.
		 * wp_insert_post_empty_content
		 *
		 * @date 16/07/2014
		 * @since 5.0.1
		 *
		 * @param bool  $maybe_empty Whether the post should be considered "empty".
		 * @param array $postarr Array of post data.
		 * @return bool
		 */
		public function wp_insert_post_empty_content( $maybe_empty, $postarr ) {

			// return false and allow insert if '_acf_changed' exists.
			if ( $maybe_empty && acf_maybe_get_POST( '_acf_changed' ) ) {
				return false;
			}

			// return.
			return $maybe_empty;
		}

		/**
		 * Checks if the $post is allowed to be saved.
		 * Used to avoid triggering "acf/save_post" on dynamically created posts during save.
		 * allow_save_post
		 *
		 * @type function
		 * @date 26/06/2016
		 * @since 5.3.8
		 *
		 * @param WP_Post $post The post to check.
		 * @return bool
		 */
		public function allow_save_post( $post ) {

			// vars.
			$allow = true;

			// restrict post types.
			$restrict = array( 'auto-draft', 'revision', 'acf-field', 'acf-field-group' );
			if ( in_array( $post->post_type, $restrict ) ) {
				$allow = false;
			}

			// disallow if the $_POST ID value does not match the $post->ID.
			$form_post_id = (int) acf_maybe_get_POST( 'post_ID' );
			if ( $form_post_id && $form_post_id !== $post->ID ) {
				$allow = false;
			}

			// revision (preview).
			if ( 'revision' == $post->post_type ) {

				// allow if doing preview and this $post is a child of the $_POST ID.
				if ( acf_maybe_get_POST( 'wp-preview' ) == 'dopreview' && $form_post_id === $post->post_parent ) {
					$allow = true;
				}
			}

			// return.
			return $allow;
		}

		/**
		 * Triggers during the 'save_post' action to save the $_POST data.
		 * save_post
		 *
		 * @type function
		 * @date 23/06/12
		 * @since 1.0.0
		 *
		 * @param int     $post_id The post ID.
		 * @param WP_POST $post the post object.
		 * @return int
		 */
		public function save_post( $post_id, $post ) {

			// bail ealry if no allowed to save this post type.
			if ( ! $this->allow_save_post( $post ) ) {
				return $post_id;
			}

			// verify nonce.
			if ( ! acf_verify_nonce( 'post' ) ) {
				return $post_id;
			}

			// validate for published post (allow draft to save without validation).
			if ( 'publish' == $post->post_status ) {

				// bail early if validation fails.
				if ( ! acf_validate_save_post() ) {
					return;
				}
			}

			// save.
			acf_save_post( $post_id );

			// save revision.
			if ( post_type_supports( $post->post_type, 'revisions' ) ) {
				acf_save_post_revision( $post_id );
			}

			// return.
			return $post_id;
		}
	}
endif; // class_exists check.

if ( ! class_exists( 'acf_form_welcart_item_pro' ) ) :

	/**
	 * ACF Pro for Welcart items.
	 */
	class acf_form_welcart_item_pro {
		/**
		 * The first field groups style CSS.
		 *
		 * @var string
		 */
		var $style = '';

		/**
		 * Sets up the class functionality.
		 * __construct
		 *
		 * @date 5/03/2014
		 * @since 5.0.0
		 *
		 * @return void
		 */
		public function __construct() {

			// initialize on post edit screens.
			add_action( 'load-welcart-shop_page_usces_itemedit', array( $this, 'initialize' ) );
			add_action( 'load-welcart-shop_page_usces_itemnew', array( $this, 'initialize' ) );
			// add_action( 'load-post.php', array( $this, 'initialize' ) );
			// add_action( 'load-post-new.php', array( $this, 'initialize' ) );

			// save.
			add_filter( 'wp_insert_post_empty_content', array( $this, 'wp_insert_post_empty_content' ), 10, 2 );
			add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );
		}

		/**
		 * Sets up Form functionality.
		 * initialize
		 *
		 * @date 19/9/18
		 * @since 5.7.6
		 *
		 * @return void
		 */
		public function initialize() {

			// globals.
			global $typenow;

			// restrict specific post types.
			$restricted = array( 'acf-field-group', 'attachment' );
			if ( in_array( $typenow, $restricted ) ) {
				return;
			}

			// enqueue scripts.
			acf_enqueue_scripts();

			// actions.
			add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 10, 2 );
		}

		/**
		 * Adds ACF metaboxes for the given $post_type and $post.
		 * add_meta_boxes
		 *
		 * @date 19/9/18
		 * @since 5.7.6
		 *
		 * @param string  $post_type The post type.
		 * @param WP_Post $post The post being edited.
		 * @return void
		 */
		public function add_meta_boxes( $post_type, $post ) {
			if ( isset( $post->ID ) ) {
				wp_enqueue_media( array( 'post' => $post->ID ) );
			}
			// enqueue scripts uploader of acf.
			acf_enqueue_uploader();

			// Get field groups for this screen.
			$field_groups = acf_get_field_groups(
				array(
					'post_id'   => $post->ID,
					'post_type' => $post_type,
				)
			);

			// Loop over field groups.
			if ( $field_groups ) {
				foreach ( $field_groups as $field_group ) {

					// vars.
					$id       = "acf-{$field_group['key']}"; // acf-group_123.
					$title    = $field_group['title'];       // Group 1.
					$context  = $field_group['position'];    // normal, side, acf_after_title.
					$priority = 'high';                      // high, core, default, low.

					// Reduce priority for sidebar metaboxes for best position.
					if ( 'side' == $context ) {
						$priority = 'core';
					}

					/**
					 * Filters the metabox priority.
					 *
					 * @date 23/06/12
					 * @since 3.1.8
					 *
					 * @param string $priority The metabox priority (high, core, default, low).
					 * @param array $field_group The field group array.
					 */
					$priority = apply_filters( 'acf/input/meta_box_priority', $priority, $field_group );

					// Add the meta box.
					add_meta_box( $id, $title, array( $this, 'render_meta_box' ), $post_type, $context, $priority, array( 'field_group' => $field_group ) );
				}

				// Get style from first field group.
				$this->style = acf_get_field_group_style( $field_groups[0] );
			}

			// remove postcustom metabox (removes expensive SQL query).
			if ( acf_get_setting( 'remove_wp_meta_box' ) ) {
				remove_meta_box( 'postcustom', false, 'normal' );
			}

			// Add hidden input fields.
			add_action( 'edit_form_after_title', array( $this, 'edit_form_after_title' ) );

			/**
			 * Fires after metaboxes have been added.
			 *
			 * @date 13/12/18
			 * @since 5.8.0
			 *
			 * @param string  $post_type The post type.
			 * @param WP_Post $post The post being edited.
			 * @param array   $field_groups The field groups added.
			 */
			do_action( 'acf/add_meta_boxes', $post_type, $post, $field_groups );
		}

		/**
		 * Called after the title adn before the content editor.
		 * edit_form_after_title
		 *
		 * @date 19/9/18
		 * @since 5.7.6
		 *
		 * @return void
		 */
		public function edit_form_after_title() {

			// globals.
			global $post, $wp_meta_boxes;

			// render post data.
			acf_form_data(
				array(
					'screen'  => 'post',
					'post_id' => $post->ID,
				)
			);

			// render 'acf_after_title' metaboxes.
			do_meta_boxes( get_current_screen(), 'acf_after_title', $post );

			// render dynamic field group style.
			echo '<style type="text/css" id="acf-style">' . $this->style . '</style>';
		}

		/**
		 * Renders the ACF metabox HTML.
		 * render_meta_box
		 *
		 * @date 19/9/18
		 * @since 5.7.6
		 *
		 * @param WP_Post $post The post being edited.
		 * @param array   $metabox The add_meta_box() args.
		 * @return void
		 */
		public function render_meta_box( $post, $metabox ) {

			// vars.
			$id          = $metabox['id'];
			$field_group = $metabox['args']['field_group'];

			// Render fields.
			$fields = acf_get_fields( $field_group );
			acf_render_fields( $fields, $post->ID, 'div', $field_group['instruction_placement'] );

			// Create metabox localized data.
			$data = array(
				'id'    => $id,
				'key'   => $field_group['key'],
				'style' => $field_group['style'],
				'label' => $field_group['label_placement'],
				'edit'  => acf_get_field_group_edit_link( $field_group['ID'] ),
			);

			?>
			<script type="text/javascript">
			if( typeof acf !== 'undefined' ) {
				acf.newPostbox(<?php echo wp_json_encode( $data ); ?>);
			}
			</script>
			<?php
		}

		/**
		 * Allows WP to insert a new post without title or post_content if ACF data exists.
		 * wp_insert_post_empty_content
		 *
		 * @date 16/07/2014
		 * @since 5.0.1
		 *
		 * @param bool  $maybe_empty Whether the post should be considered "empty".
		 * @param array $postarr Array of post data.
		 * @return bool
		 */
		public function wp_insert_post_empty_content( $maybe_empty, $postarr ) {

			// return false and allow insert if '_acf_changed' exists.
			if ( $maybe_empty && acf_maybe_get_POST( '_acf_changed' ) ) {
				return false;
			}

			// return.
			return $maybe_empty;
		}

		/**
		 * Checks if the $post is allowed to be saved.
		 * Used to avoid triggering "acf/save_post" on dynamically created posts during save.
		 * allow_save_post
		 *
		 * @type function
		 * @date 26/06/2016
		 * @since 5.3.8
		 *
		 * @param WP_Post $post The post to check.
		 * @return bool
		 */
		public function allow_save_post( $post ) {

			// vars.
			$allow = true;

			// restrict post types.
			$restrict = array( 'auto-draft', 'revision', 'acf-field', 'acf-field-group' );
			if ( in_array( $post->post_type, $restrict ) ) {
				$allow = false;
			}

			// disallow if the $_POST ID value does not match the $post->ID.
			$form_post_id = (int) acf_maybe_get_POST( 'post_ID' );
			if ( $form_post_id && $form_post_id !== $post->ID ) {
				$allow = false;
			}

			// revision (preview).
			if ( 'revision' == $post->post_type ) {

				// allow if doing preview and this $post is a child of the $_POST ID.
				if ( acf_maybe_get_POST( 'wp-preview' ) == 'dopreview' && $form_post_id === $post->post_parent ) {
					$allow = true;
				}
			}

			// return.
			return $allow;
		}

		/**
		 * Triggers during the 'save_post' action to save the $_POST data.
		 * save_post
		 *
		 * @type function
		 * @date 23/06/12
		 * @since 1.0.0
		 *
		 * @param int     $post_id The post ID.
		 * @param WP_POST $post the post object.
		 * @return int
		 */
		public function save_post( $post_id, $post ) {

			// bail ealry if no allowed to save this post type.
			if ( ! $this->allow_save_post( $post ) ) {
				return $post_id;
			}

			// verify nonce.
			if ( ! acf_verify_nonce( 'post' ) ) {
				return $post_id;
			}

			// validate for published post (allow draft to save without validation).
			if ( 'publish' == $post->post_status ) {

				// bail early if validation fails.
				if ( ! acf_validate_save_post() ) {
					return;
				}
			}

			// save.
			acf_save_post( $post_id );

			// save revision.
			if ( post_type_supports( $post->post_type, 'revisions' ) ) {
				acf_save_post_revision( $post_id );
			}

			// return.
			return $post_id;
		}
	}
endif;
