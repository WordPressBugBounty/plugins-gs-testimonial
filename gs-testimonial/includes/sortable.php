<?php

namespace GSTM;

/**
 * Protect direct access
 */
defined('ABSPATH') || exit;

class Sortable {
	
	public function __construct() {

		$sort_args = [
			[
				'post_type'   => 'gs_testimonial',
				'title'       => 'Sort Groups',
				'menu_title'  => 'Sort Group Order',
				'menu_slug'   => 'sort_tax_group',
				'is_taxonomy' => false
			],
			[
				'post_type'    => 'gs_testimonial',
				'title'        => 'Sort Testimonials',
				'menu_title'   => 'Sort Testimonials',
				'menu_slug'    => 'sort_tax_item',
				'is_taxonomy'  => true
			],

		];

		new Term_Sort($sort_args);
	}
}
