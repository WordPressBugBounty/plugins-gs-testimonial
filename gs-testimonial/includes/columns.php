<?php

namespace GSTM;

/**
 * Protect direct access
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class Columns {

    /**
	 * Class constructor.
	 * 
	 * @since 1.0.0
	 */
    public function __construct() {
        
        add_filter( 'manage_gs_testimonial_posts_columns', [ $this, 'gs_testimonial_screen_columns' ] );
        add_action( 'manage_gs_testimonial_posts_custom_column', [ $this, 'gs_t_populate_columns' ], 10, 2 );        
    }

    /**
     * Customize testimonial screen columns.
     * 
     * @since 1.0.0
     * 
     * @param  array $columns Screen columns.
     * @return array          Modified screen columns.
     */
    public function gs_testimonial_screen_columns( $columns ) {

        $new_columns = [
            'cb'                               => $columns['cb'],
            'title'                            => $columns['title'],
            'featured_image'                   => __( 'Reviewer Image', 'gs-testimonial' ),
            'gs_t_client_name'                 => __( 'Reviewer Name', 'gs-testimonial' ),
            'gs_t_client_company'              => __( 'Company', 'gs-testimonial' ),
            'gs_t_client_design'               => __( 'Designation', 'gs-testimonial' ),
            'gs_t_client_rating'               => __( 'Rating', 'gs-testimonial' ),
            'taxonomy-gs_testimonial_category' => $columns['taxonomy-gs_testimonial_category'],
            'date'                             =>  $columns['date']
        ];

        return $new_columns;
    }

    /**
     * Populating the columns.
     * 
     * @since 1.0.0
     * 
     * @param  array $columns Screen column.
     * @return void
     */
    public function gs_t_populate_columns( $column, $postId ) {

        if ( 'featured_image' === $column ) {
            $image = get_featured_image( $postId );

            if ( $image ) {
                echo '<img src="' . $image . '" width="34"/>';
            }
        }

        if ( 'gs_t_client_name' === $column ) {
            $client_company = get_post_meta( $postId, 'gs_t_client_name', true );
            echo wp_kses_post( $client_company );
        }

        if ( 'gs_t_client_company' === $column ) {
            $client_company = get_post_meta( $postId, 'gs_t_client_company', true );
            echo wp_kses_post( $client_company );
        }
    
        if ( 'gs_t_client_design' === $column ) {
            $client_desig = get_post_meta( $postId, 'gs_t_client_design', true );
            echo wp_kses_post( $client_desig );
        }
    
        if ( 'gs_t_client_rating' === $column ) {
            $args = array(
                'rating' => get_post_meta( $postId, 'gs_t_rating', true ),
                'type'   => 'rating',
                'number' => 0
            );
           gs_wp_star_rating( $args );
        }
    }
}
