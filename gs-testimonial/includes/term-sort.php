<?php

namespace GSTM;

/**
 * Protect direct access
 */
if (!defined('ABSPATH')) exit;

if (!class_exists('Term_Sort')) :

	class Term_Sort
	{

		var $ppp = '-1';

		public $data = [];

		public function __construct($data)
		{

			$this->data = array_map(function ($item) {
				return array_merge($this->get_default_data(), $item);
			}, $data);

			add_filter('plugins_loaded', array($this, 'alter_terms_table'), 0);
			add_action('admin_menu', array($this, 'enable_sort'));
			add_filter('get_terms_orderby', array($this, 'get_terms_orderby'), 1, 2);
			add_filter('terms_clauses', array($this, 'terms_clauses'), 10, 3);

			if ($this->is_pro()) {
				add_action('wp_ajax_update_taxonomy_order', array($this, 'update_taxonomy_order'));
			}

			// Sortable
			add_filter('posts_orderby', array($this, 'order_posts'));
			add_action('admin_enqueue_scripts', array($this, 'sort_group_scripts'));

			if ($this->is_pro()) {
				add_action('wp_ajax_sort_gstm', array($this, 'save_sort_order'));
			}
		}

		public function is_pro()
		{
			return gstm_fs()->is_paying_or_trial();
		}

		public function alter_terms_table()
		{

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
		public function enable_sort()
		{
			add_submenu_page('edit.php?post_type=gs_testimonial', 'Sort Order', 'Sort Order', 'manage_options', 'sort_order_gs_testimonial', array($this, 'dhf_sort'), 3);
		}


		public function dhf_sort()
		{

		$object_type = '';	
		$object_type = isset($_GET['gs_object_type']) ? sanitize_key( wp_unslash( $_GET['gs_object_type'] ) ) : 'sort_gs_testimonial';
		
		if ( $object_type === '' ) {
			$object_type = 'sort_gs_testimonial';
		}
		
		?>
		
		<style>
				.gs-plugins--sort-links {
					-webkit-box-align: center;
					-ms-flex-align: center;
					-webkit-align-items: center;
					align-items: center;
					background: #fff;
					-webkit-box-shadow: 0 1px 10px rgba(63, 66, 87, .06);
					box-shadow: 0 1px 10px rgba(63, 66, 87, .06);
					display: -webkit-box;
					display: -webkit-flex;
					display: -ms-flexbox;
					display: flex;
					gap: 24px;
					margin: 0 0 0 -20px;
					padding: 0 30px
				}

				.gs-plugins--sort-links a {
					border-bottom: 2px solid transparent;
					-webkit-box-shadow: none;
					box-shadow: none;
					color: #515365;
					font-size: 16px;
					font-weight: 500;
					line-height: 64px;
					outline: none;
					text-decoration: none;
					-webkit-transition: color .3s;
					-o-transition: color .3s;
					transition: color .3s;
					cursor: pointer;
				}

				.gs-plugins--sort-links a.gs-sort-active {
					border-color: #5e6be5;
					color: #242429
				}


				.gs-testimonial-disable--term-inner {
					-webkit-box-pack: center;
					-ms-flex-pack: center;
					display: -webkit-box;
					display: -webkit-flex;
					display: -ms-flexbox;
					display: flex;
					height: 100%;
					-webkit-justify-content: center;
					justify-content: center;
					left: 0;
					position: absolute;
					top: 66px;
					width: 100%;
					z-index: 999;
					background: #f2f2f257;
				}

				.gs-testimonial-disable--term-message {
					-ms-flex-item-align: start;
					-webkit-align-self: flex-start;
					align-self: flex-start;
					background: #6472ef;
					-webkit-border-radius: 3px;
					border-radius: 3px;
					-webkit-box-shadow: 0 0 50px rgba(89, 97, 109, .1);
					box-shadow: 0 0 50px rgba(89, 97, 109, .1);
					color: #fff;
					font-size: 18px;
					letter-spacing: 1px;
					margin-top: 30vh;
					padding: 20px 100px
				}

				.gs-testimonial-disable--term-message a {
					color: #fff;
					font-weight: 600;
					text-decoration: none
				}

				.is_pro_active {
					opacity: .4
				}
		</style>

			<div class="gs-plugins--sort-page">

				<div class="gs-plugins--sort-links">
					<a class="<?php echo $object_type === 'sort_gs_testimonial' ? 'gs-sort-active' : ''; ?>" href="<?php echo esc_url(admin_url('edit.php?post_type=gs_testimonial&page=sort_order_gs_testimonial&gs_object_type=sort_gs_testimonial')); ?>"><?php echo esc_html('Testimonial', 'gs-testimonial'); ?></a>
					<a class="<?php echo $object_type === 'sort_group_gs_testimonial' ? 'gs-sort-active' : ''; ?>" href="<?php echo esc_url(admin_url('edit.php?post_type=gs_testimonial&page=sort_order_gs_testimonial&gs_object_type=sort_group_gs_testimonial')); ?>"><?php echo esc_html('Group', 'gs-testimonial'); ?></a>
				</div>

				<div class="gs-plugins--sort-content">
					<?php if ($object_type === 'sort_gs_testimonial') : ?>
						<?php $this->gs_sort_tstm_members(); ?>
					<?php elseif ($object_type === 'sort_group_gs_testimonial') : ?>
						<?php $this->gs_sort_member_groups(); ?>
					<?php endif; ?>
				</div>

			</div>

		<?php
		}


		public function gs_sort_member_groups()
		{

			$this->print_pro_message();

		?>

			<div class="wrap gs-tsm--sortable_group <?php echo $this->is_pro() ? 'wrap-active' : ''; ?>">

				<?php

				$terms = get_terms('gs_testimonial_category');

				if (!empty($terms)) : ?>
					<div id="icon-edit" class="icon32" style="display: flex; width: 100%; gap: 40px; flex-wrap: wrap;">
						<div class="gsteam-sort--left-area" style="flex: 1 0 auto; width: 670px;">
							<h2><?php echo __('Step 1: Drag & Drop to rearrange Groups', 'gs-testimonial'); ?> <img src="<?php bloginfo('url'); ?>/wp-admin/images/loading.gif" id="loading-animation" /></h2>

							<ul id="group-sortable-list">
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
						</div>

						<?php if ($this->ppp != -1) echo '<p>Latest ' . esc_html($this->ppp) . ' shown</p>'; ?>

						<div class="gsteam-sort--right-area">

							<h3><?php esc_html_e('Step 2: Query Settings for Groups', 'gsbookshowcase'); ?></h3>

							<div style="background: #fff; border-radius: 6px; padding: 30px; box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.12); font-size: 1.3em; line-height: 1.6;">

								<ol style="list-style: numeric; padding-left: 20px; margin: 0">
									<li><?php echo esc_html__('Create or Edit a Shortcode From', 'gs-testimonial'); ?> <strong><?php echo esc_html__('Solid Testimonial > Shortcode', 'gs-testimonial'); ?></strong>.</li>
									<li><?php echo esc_html__('Then proceed to the 3rd tab labeled', 'gs-testimonial'); ?> <strong><?php echo esc_html__('Query Settings', 'gs-testimonial'); ?></strong>.</li>
									<li><?php echo esc_html__('Set', 'gs-testimonial'); ?> <strong><?php echo esc_html__('Group Order by', 'gs-testimonial'); ?></strong> <?php echo esc_html__('to', 'gs-testimonial'); ?> <strong><?php echo esc_html__('Custom Order', 'gs-testimonial'); ?></strong>.</li>
									<li><?php echo esc_html__('Set', 'gs-testimonial'); ?> <strong><?php echo esc_html__('Group Order', 'gs-testimonial'); ?></strong> <?php echo esc_html__('to', 'gs-testimonial'); ?> <strong><?php echo esc_html__('ASC', 'gs-testimonial'); ?></strong>.</li>
								</ol>

								<ul style="list-style: circle; padding-left: 20px; margin-top: 20px">
									<li><?php echo esc_html__('Follow', 'gs-testimonial'); ?> <a href="https://docs.gsplugins.com/gs-testimonial-slider/manage-the-testimonials/sort-order/" target="_blank"><?php echo esc_html__('Documentation', 'gs-testimonial'); ?></a> <?php echo esc_html__('to learn more.', 'gs-testimonial'); ?></li>
									<li><a href="https://www.gsplugins.com/contact/" target="_blank"><?php echo esc_html__('Contact us', 'gs-testimonial'); ?></a> <?php echo esc_html__('for support.', 'gs-testimonial'); ?></li>
								</ul>

							</div>

						</div>

					</div>
			</div><!-- #wrap -->

		<?php

		}


		public function gs_sort_tstm_members()
		{

			$this->print_pro_message();

			$sortable = new \WP_Query('post_type=gs_testimonial&posts_per_page=' . $this->ppp . '&orderby=menu_order&order=ASC');
		?>

			<div class="wrap <?php echo gstm_fs()->can_use_premium_code() ? 'wrap-active' : ''; ?>">
				<div id="icon-edit" class="icon32" style="display: flex; width: 100%; gap: 40px; flex-wrap: wrap;">
					<div class="gsteam-sort--left-area" style="flex: 1 0 auto; width: 570px;">
						<?php printf('<h2>%s<img src="%s" id="loading-animation" /></h2>', esc_html__('Step 1: Drag & Drop to rearrange Testimonials', 'gs-testimonial'), esc_url(get_bloginfo('url') . '/wp-admin/images/loading.gif')); ?>

						<?php if ($sortable->have_posts()) : ?>

							<ul id="sortable-list">
								<?php
								while ($sortable->have_posts()) :

									$sortable->the_post();
									$term_obj_list = get_the_terms(get_the_ID(), 'gs_testimonial_category');
									$terms_string  = '';

									if (is_array($term_obj_list) || is_object($term_obj_list)) {
										$terms_string = join('</span><span>', wp_list_pluck($term_obj_list, 'name'));
									}

									if (! empty($terms_string)) {
										$terms_string = '<span>' . $terms_string . '</span>';
									}

								?>
									<li id="<?php the_id(); ?>">
										<div class="sortable-content sortable-icon"><i class="fas fa-arrows-alt" aria-hidden="true"></i></div>

										<?php if (has_post_thumbnail()) : ?>
											<div class="sortable-content sortable-thumbnail">
												<?php
												$thumbnail = get_the_post_thumbnail(
													get_the_ID(),
													'thumbnail',
													array(
														'class'    => 'gstm-testimonial-thumbnail',
														'alt'      => esc_attr(get_the_title()),
														'itemprop' => 'image',
													)
												);

												if (! empty($thumbnail)) {
													echo $thumbnail;
												} else {
													printf('<img src="%s" />', esc_url(GSTM_PLUGIN_URI . '/assets/img/no_img.png'));
												}
												?>
											</div>
										<?php else : ?>
											<div class="sortable-content sortable-thumbnail">
												<img src="<?php echo esc_url(GSTM_PLUGIN_URI . '/assets/img/no_img.png'); ?>" />
											</div>
										<?php endif; ?>

										<div class="sortable-content sortable-title"><?php echo esc_html(the_title());  ?></div>
										<div class="sortable-content sortable-category"><?php echo wp_kses_post($terms_string) ?></div>
									</li>

								<?php endwhile;
								wp_reset_postdata();
								?>
							</ul>

						<?php else : ?>

							<div class="notice notice-warning" style="margin-top: 30px;">
								<h3><?php echo esc_html__('No Testimonial Found!', 'gs-testimonial'); ?></h3>
								<p style="font-size: 14px;"><?php echo esc_html__('We didn\'t find any testimonials.</br>Please add some testimonials to sort them.', 'gs-testimonial'); ?></p>
								<a href="<?php echo esc_url(admin_url('post-new.php?post_type=gs_testimonial')); ?>" style="margin-top: 10px; margin-bottom: 20px;" class="button button-primary button-large"><?php echo esc_html__('Add Member', 'gs-testimonial'); ?></a>
							</div>

						<?php endif; ?>
					</div>

					<div class="gsteam-sort--right-area">

						<h3><?php esc_html_e('Step 2: Query Settings for Testimonial', 'gsbookshowcase'); ?></h3>

						<div style="background: #fff; border-radius: 6px; padding: 30px; box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.12); font-size: 1.3em; line-height: 1.6; margin-top: 30px">

							<ol style="list-style: numeric; padding-left: 20px; margin: 0">
								<li><?php echo esc_html__('Create or Edit a Shortcode From', 'gs-testimonial'); ?> <strong><?php echo esc_html__('Solid Testimonial > Shortcode', 'gs-testimonial'); ?></strong>.</li>
								<li><?php echo esc_html__('Then proceed to the 3rd tab labeled', 'gs-testimonial'); ?> <strong><?php echo esc_html__('Query Settings', 'gs-testimonial'); ?></strong>.</li>
								<li><?php echo esc_html__('Set', 'gs-testimonial'); ?> <strong><?php echo esc_html__('Order By', 'gs-testimonial'); ?></strong> <?php echo esc_html__('as', 'gs-testimonial'); ?> <strong><?php echo esc_html__('Custom Order', 'gs-testimonial'); ?></strong>.</li>
								<li><?php echo esc_html__('Set', 'gs-testimonial'); ?> <strong><?php echo esc_html__('Order ', 'gs-testimonial'); ?></strong> <?php echo esc_html__('as', 'gs-testimonial'); ?> <strong><?php echo esc_html__('ASC', 'gs-testimonial'); ?></strong>.</li>
							</ol>

							<ul style="list-style: circle; padding-left: 20px; margin-top: 20px">
								<li><?php echo esc_html__('Follow', 'gs-testimonial'); ?> <a href="https://docs.gsplugins.com/gs-testimonial-slider/manage-the-testimonials/sort-order/" target="_blank"><?php echo esc_html__('Documentation', 'gs-testimonial'); ?></a> <?php echo esc_html__('to learn more.', 'gs-testimonial'); ?></li>
								<li><a href="https://www.gsplugins.com/contact/" target="_blank"><?php echo esc_html__('Contact us', 'gs-testimonial'); ?></a> <?php echo esc_html__('for support.', 'gs-testimonial'); ?></li>
							</ul>

						</div>

					</div>
				</div>
			</div><!-- #wrap -->

			<?php
		}


		public function print_pro_message()
		{
			if (! gstm_fs()->can_use_premium_code()) : ?>
				<div class="gs-testimonial-disable--term-pages">
					<div class="gs-testimonial-disable--term-inner">
						<div class="gs-testimonial-disable--term-message"><a href="https://www.gsplugins.com/product/gs-testimonial/#pricing"><?php echo esc_html__('Upgrade to PRO', 'gs-books-showcase'); ?></a></div>
					</div>
				</div>
<?php endif;
		}


		/**
		 * Add JS and CSS to admin
		 */
		public function sort_group_scripts($hook)
		{

			if ($hook === 'gs_testimonial_page_sort_order_gs_testimonial') {

				wp_enqueue_style(
					'gstm-sort-order',
					GSTM_PLUGIN_URI . '/assets/admin/css/sortorder.min.css',
					[],
					GSTM_VERSION
				);

				wp_enqueue_script(
					'gstm-sort-order',
					GSTM_PLUGIN_URI . '/assets/admin/js/sort-group.min.js',
					array('jquery', 'jquery-ui-sortable'),
					GSTM_VERSION,
					true
				);

				wp_enqueue_script(
					'gstm-sort',
					GSTM_PLUGIN_URI . '/assets/admin/js/sort.min.js',
					array('jquery', 'jquery-ui-sortable'),
					GSTM_VERSION,
					true
				);

				wp_localize_script('gstm-sort-order', '_gstm_sort_group', array('nonce' => wp_create_nonce('_gstm_save_sort_group_order_')));
				wp_localize_script('gstm-sort', '_gstm_sort_data', array('nonce' => wp_create_nonce('_gstm_save_sort_order_gs_')));
			}
		}


		public function get_terms_orderby($orderby, $args)
		{

			if (empty($args['taxonomy'])) return $orderby;

			if ($this->is_pro() && in_array('gs_testimonial_category', $args['taxonomy'])) {
				if (isset($args['orderby']) && $args['orderby'] == "term_order" && $orderby != "term_order") return "t.term_order";
			}

			return $orderby;
		}

		public function terms_clauses($clauses, $taxonomies, $args)
		{

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

		public function update_taxonomy_order()
		{

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
		public function order_posts($orderby)
		{
			global $wpdb;
			global $wp_query;

			if (! isset($wp_query) || ! is_main_query()) return $orderby;

			if (is_post_type_archive('gs_testimonial')) {
				$orderby = "{$wpdb->posts}.menu_order, {$wpdb->posts}.post_date DESC";
			}

			return ($orderby);
		}

		/**
		 * Save the sort order to database
		 */
		public function save_sort_order()
		{

			if (empty($_POST['_nonce']) || ! wp_verify_nonce($_POST['_nonce'], '_gstm_save_sort_order_gs_')) {
				wp_send_json_error(__('Unauthorised Request', 'gs-testimonial'), 401);
			}

			global $wpdb;
			$order   = explode(',', $_POST['order']);
			$counter = 0;

			foreach ($order as $post_id) {
				$post_id = intval($post_id);

				$update = $wpdb->update(
					$wpdb->posts,
					array('menu_order' => $counter),
					array('ID' => $post_id)
				);
				$counter++;
			}

			return wp_send_json_success();
		}

		public function get_default_data()
		{

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
