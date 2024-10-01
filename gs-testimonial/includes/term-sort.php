<?php

namespace GSTM;

/**
 * Protect direct access
 */
if (!defined('ABSPATH')) exit;

if (!class_exists('Term_Sort')) :

	class Term_Sort {

		var $ppp = '-1';

		public $data = [];

		public function __construct($data) {

			$this->data = array_map(function ($item) {
				return array_merge( $this->get_default_data(), $item );
			}, $data );

			add_filter('plugins_loaded', array($this, 'alter_terms_table'), 0);
			add_action('admin_menu', array( $this, 'enable_sort' ) );
			add_filter('get_terms_orderby', array($this, 'get_terms_orderby'), 1, 2);
			add_filter('terms_clauses', array($this, 'terms_clauses'), 10, 3);

			if( $this->is_pro() ) {
				add_action('wp_ajax_update_taxonomy_order', array($this, 'update_taxonomy_order'));
			}

			// Sortable
			add_filter('posts_orderby', array($this, 'order_posts'));
			add_action('admin_enqueue_scripts', array($this, 'sort_group_scripts'));

			if( $this->is_pro() ) {				
				add_action('wp_ajax_sort_gstm', array($this, 'save_sort_order'));
			}
		}

		public function is_pro() {
			return gstm_fs()->is_paying_or_trial();
		}

		public function alter_terms_table() {

			if (!$this->is_pro()) return;

			if (get_site_option('gsp_terms_table_altered', false) !== false) return;

			global $wpdb;

			//check if the menu_order column exists;
			$query = "SHOW COLUMNS FROM $wpdb->terms LIKE 'term_order'";
			$result = $wpdb->query($query);

			if ($result == 0) {
				$query = "ALTER TABLE $wpdb->terms ADD `term_order` INT( 4 ) NULL DEFAULT '0'";
				$result = $wpdb->query($query);

				update_site_option('gsp_terms_table_altered', true);
			}
		}

		/**
		 * Add Sort menu
		 */
		public function enable_sort() {

			foreach ($this->data as $data) {

				if ($data['is_taxonomy']) {

					add_submenu_page('edit.php?post_type=' . $data['post_type'], $data['title'], $data['menu_title'], 'edit_posts', 'sort_' . $data['post_type'], function () use ($data) {
						$this->sort_tstm_members($data);
					}, 10 );
				} else {

					add_submenu_page('edit.php?post_type=' . $data['post_type'], $data['title'], $data['menu_title'], 'edit_posts', 'sort_group_' . $data['post_type'], function () use ($data) {
						$this->sort_member_groups($data);
					}, 3 );
				}
			}
		}

		/**
		 * Add JS and CSS to admin
		 */
		public function sort_group_scripts($hook) {

			if( ! in_array( $hook, [ 'gs_testimonial_page_sort_group_gs_testimonial', 'gs_testimonial_page_sort_gs_testimonial' ] ) ) return;

			wp_enqueue_style(
				'gstm-sort-order',
				GSTM_PLUGIN_URI . '/assets/admin/css/sortorder.min.css',
				[],
				GSTM_VERSION
			);

			if(  $hook === 'gs_testimonial_page_sort_group_gs_testimonial' ) {
				wp_enqueue_script(
					'gstm-sort-order',
					GSTM_PLUGIN_URI . '/assets/admin/js/sort-group.min.js',
					array( 'jquery', 'jquery-ui-sortable' ),
					GSTM_VERSION,
					true
				);

				wp_localize_script(
					'gstm-sort-order',
					'_gstm_sort_group',
					array( 'nonce' => wp_create_nonce( '_gstm_save_sort_group_order_' ) )
				);
			}

			if( $hook === 'gs_testimonial_page_sort_gs_testimonial' ) {
				wp_enqueue_script(
					'gstm-sort',
					GSTM_PLUGIN_URI . '/assets/admin/js/sort.min.js',
					array( 'jquery', 'jquery-ui-sortable' ),
					GSTM_VERSION,
					true
				);

				wp_localize_script(
					'gstm-sort',
					'_gstm_sort_data',
					array( 'nonce' => wp_create_nonce( '_gstm_save_sort_order_gs_' ) )
				);
			}
			
		}

		/**
		 * Display Sort admin page
		 */
		public function sort_member_groups($data) {

			if (!$this->is_pro()) : ?>

				<div class="gs-testimonial-disable--term-pages">
					<div class="gs-tsm-disable--term-inner">
						<div class="gs-tsm-disable--term-message">Pro Only</div>
					</div>
				</div>

			<?php endif; ?>

			<div class="wrap gs-tsm--sortable_group <?php echo $this->is_pro() ? 'wrap-active' : ''; ?>">

				<div id="icon-edit" class="icon32"></div>
				<h2><?php echo __('Custom Order for', 'gs-testimonial') . ': ' . esc_html($data['title']); ?> <img src="<?php bloginfo('url'); ?>/wp-admin/images/loading.gif" id="loading-animation" /></h2>

				<?php

				$terms = get_terms('gs_testimonial_category');

				if (!empty($terms)) : ?>

					<ul id="sortable-list" style="max-width: 600px;">
						<?php foreach ($terms as $term) : ?>

							<li id="<?php echo esc_attr($term->term_id); ?>">
								<div class="sortable-content sortable-icon"><i class="fas fa-arrows-alt" aria-hidden="true"></i></div>
								<div class="sortable-content sortable-title"><?php echo esc_html($term->name); ?></div>
								<div class="sortable-content sortable-group"><span><?php echo absint($term->count) . ' ' . 'Members'; ?></span></div>
							</li>

						<?php endforeach; ?>
					</ul>

				<?php else : ?>

					<div class="notice notice-warning" style="margin-top: 30px;">
						<h3><?php _e('No Testimonial Found!', 'gs-testimonial'); ?></h3>
						<p style="font-size: 14px;"><?php _e('We didn\'t find any testimonial.</br>Please add some testimonials to sort them.', 'gs-testimonial'); ?></p>
						<a href="<?php echo admin_url('post-new.php?post_type=gs_testimonial'); ?>" style="margin-top: 10px; margin-bottom: 20px;" class="button button-primary button-large"><?php _e('Add Member', 'gs-testimonial'); ?></a>
					</div>

				<?php endif; ?>

				<?php if ($this->ppp != -1) echo '<p>Latest ' . esc_html($this->ppp) . ' shown</p>'; ?>

			</div><!-- #wrap -->

			<?php

		}

		/**
		 * Display Sort admin page
		 */
		public function sort_tstm_members($data) {

			if ( ! gstm_fs()->can_use_premium_code() ) : ?>

				<div class="gs-testimonial-disable--term-pages">
					<div class="gs-tsm-disable--term-inner">
						<div class="gs-tsm-disable--term-message">Pro Only</div>
					</div>
				</div>
	
			<?php endif;
	
			$sortable = new \WP_Query( 'post_type=' . $data['post_type'] . '&posts_per_page=' . $this->ppp . '&orderby=menu_order&order=ASC' );
			?>
			<div class="wrap <?php echo gstm_fs()->can_use_premium_code() ? 'wrap-active' : ''; ?>">
				<div id="icon-edit" class="icon32"></div>
				<?php printf( '<h2>%s : %s<img src="%s" id="loading-animation" /></h2>', __( 'Custom Order for', 'gs-testimonial' ), $data['title'], get_bloginfo( 'url' ) . '/wp-admin/images/loading.gif' ); ?>
	
				<?php if ( $sortable->have_posts() ) : ?>
		
					<ul id="sortable-list">
						<?php
						while ( $sortable->have_posts() ) :
	
							$sortable->the_post();
							$term_obj_list = get_the_terms( get_the_ID(), 'gs_testimonial_category' );
							$terms_string  = '';
	
							if ( is_array( $term_obj_list ) || is_object( $term_obj_list ) ) {
								$terms_string = join( '</span><span>', wp_list_pluck( $term_obj_list, 'name' ) );
							}
	
							if ( ! empty( $terms_string ) ) {
								$terms_string = '<span>' . $terms_string . '</span>';
							}
	
							?>
							
							<li id="<?php the_id(); ?>">
								<div class="sortable-content sortable-icon"><i class="fas fa-arrows-alt" aria-hidden="true"></i></div>
	
								<?php if ( has_post_thumbnail() ) : ?>
								<div class="sortable-content sortable-thumbnail">
									<?php
										$thumbnail = get_the_post_thumbnail(
											get_the_ID(),
											'thumbnail',
											array(
												'class'    => 'gstm-testimonial-thumbnail',
												'alt'      => get_the_title(),
												'itemprop' => 'image',
											)
										);
	
									if ( ! empty( $thumbnail ) ) {
										echo $thumbnail;
									} else {
										printf( '<img src="%s" />', GSTM_PLUGIN_URI . '/assets/img/no_img.png' );
									}
									?>
								</div>
								<?php else : ?>
									<div class="sortable-content sortable-thumbnail">
										<img src="<?php echo GSTM_PLUGIN_URI . '/assets/img/no_img.png'; ?>" />
									</div>
								<?php endif; ?>
	
								<div class="sortable-content sortable-title"><?php the_title(); ?></div>
								<div class="sortable-content sortable-category"><?php echo $terms_string; ?></div>
							</li>
				
						<?php endwhile; ?>
					</ul>
				
				<?php else : ?>
					
					<div class="notice notice-warning" style="margin-top: 30px;">
						<h3><?php _e( 'No Testimonial Found!', 'gs-testimonial' ); ?></h3>
						<p style="font-size: 14px;"><?php _e( 'We didn\'t find any testimonials.</br>Please add some testimonials to sort them.', 'gs-testimonial' ); ?></p>
						<a href="<?php echo admin_url( 'post-new.php?post_type=gs_testimonial' ); ?>" style="margin-top: 10px; margin-bottom: 20px;" class="button button-primary button-large"><?php _e( 'Add Member', 'gs-testimonial' ); ?></a>
					</div>
	
				<?php endif; ?>
	
				<?php
				if ( $this->ppp != -1 ) {
					echo '<p>Latest ' . $this->ppp . ' shown</p>';}
				?>
		
			</div><!-- #wrap -->
		
			<?php
		}

		public function get_terms_orderby($orderby, $args) {

			if (empty($args['taxonomy'])) return $orderby;

			if ($this->is_pro() && in_array('gs_testimonial_category', $args['taxonomy'])) {
				if (isset($args['orderby']) && $args['orderby'] == "term_order" && $orderby != "term_order") return "t.term_order";
			}

			return $orderby;
		}

		public function terms_clauses($clauses, $taxonomies, $args) {

			if (empty($args['taxonomy'])) return $clauses;

			if (!$this->is_pro() || !in_array('gs_testimonial_category', $args['taxonomy'])) return $clauses;

			$options = [
				'adminsort' => '1',
				'autosort' => '1',
			];

			// if admin make sure use the admin setting
			if (is_admin()) {
				// return if use orderby columns
				if (isset($_GET['orderby']) && $_GET['orderby'] !=  'term_order') return $clauses;
				if ($options['adminsort'] == "1") $clauses['orderby'] = 'ORDER BY t.term_order';
				return $clauses;
			}

			// if autosort, then force the menu_order
			if ($options['autosort'] == 1 && (!isset($args['ignore_term_order']) || (isset($args['ignore_term_order']) && $args['ignore_term_order'] !== TRUE))) {
				$clauses['orderby'] = 'ORDER BY t.term_order';
			}

			return $clauses;
		}

		public function update_taxonomy_order() {

			if (!$this->is_pro()) wp_send_json_error();

			if (empty($_POST['_nonce']) || !wp_verify_nonce($_POST['_nonce'], '_gstm_save_sort_group_order_')) {
				wp_send_json_error(__('Unauthorised Request', 'gs-testimonial'), 401);
			}


			global $wpdb;

			$order = explode(',', sanitize_text_field($_POST['order']));
			$counter = 0;

			foreach ($order as $term_id) {
				$wpdb->update($wpdb->terms, array('term_order' => $counter), array('term_id' => (int) $term_id));
				$counter++;
			}

			return wp_send_json_success();
		}

		/**
		 * Alter the query on front and backend to order posts as desired.
		 */
		public function order_posts($orderby) {
			global $wpdb;
			global $wp_query;
			
			if ( ! isset($wp_query) || ! is_main_query() ) return $orderby;

			if ( is_post_type_archive('gs_testimonial') ) {
				$orderby = "{$wpdb->posts}.menu_order, {$wpdb->posts}.post_date DESC";
			}

			return ($orderby);
		}

		/**
		 * Save the sort order to database
		 */
		public function save_sort_order() {

			if ( empty( $_POST['_nonce'] ) || ! wp_verify_nonce( $_POST['_nonce'], '_gstm_save_sort_order_gs_' ) ) {
				wp_send_json_error( __( 'Unauthorised Request', 'gs-testimonial' ), 401 );
			}

			global $wpdb;
			$order   = explode( ',', $_POST['order'] );
			$counter = 0;
	
			foreach ( $order as $post_id ) {
				$wpdb->update(
					$wpdb->posts,
					array( 'menu_order' => $counter ),
					array( 'ID' => $post_id )
				);
				$counter++;
			}
	
			return wp_send_json_success();
		}

		public function get_default_data() {

			return [
				'post_type'  => 'gs_testimonial',
				'title'      => 'Sort Groups',
				'menu_title' => 'Sort Group Order',
				'menu_slug'  => 'sort_tax_group',
				'is_taxonomy' => false
			];
		}
	}

endif;
