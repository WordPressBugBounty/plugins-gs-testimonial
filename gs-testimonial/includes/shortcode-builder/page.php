<?php

$routes = [
	[
		'slug'  => '/',
		'title' => __('Shortcodes', 'gs-testimonial')
	],
	[
		'slug'  => '/shortcode',
		'title' => __( 'Create New', 'gs-testimonial' )
	],
	[
		'slug'  => '/preferences',
		'title' => __( 'Preferences', 'gs-testimonial' )
	]
];

?>
<div class="app-container">
	<div class="main-container">		
		<div id="gstm-shortcode-app">
			<header class="gstm-header">
				<div class="gs-containeer-f">
					<div class="gs-roow">
						<div class="logo-area gs-col-xs-6">
							<router-link to="/">
								<img src="<?php echo GSTM_PLUGIN_URI .  '/assets/img/logo.svg'; ?>" alt="Solid Testimonials Logo">
							</router-link>
						</div>
						<div class="menu-area gs-col-xs-6 text-right">
							<ul>
								<?php
								foreach($routes as $route) { ?>
									<router-link to=<?php echo esc_attr($route['slug']); ?> custom v-slot="{ isActive, href, navigate, isExactActive }">
										<li :class="[isActive ? 'router-link-active' : '', isExactActive ? 'router-link-exact-active' : '']">
											<a :href="href" @click="navigate" @keypress.enter="navigate" role="link"><?php echo esc_html($route['title']); ?></a>
										</li>
									</router-link>									
								<?php
								}
								?>								
							</ul>
						</div>
					</div>
				</div>
			</header>

			<div class="gstm-app-view-container">
				<router-view :key="$route.fullPath"></router-view>
			</div>

		</div>		
	</div>
</div>