<?php

namespace GSTM;

?>
<div class="gs-containeer">

	<?php 
?>

	<?php 
if ( $gs_t_loop->have_posts() ) {
    ?>
		<div class="<?php 
    echo esc_attr( implode( ' ', $gs_row_classes ) );
    ?>">
			<?php 
    while ( $gs_t_loop->have_posts() ) {
        $gs_t_loop->the_post();
        $classes = array("gs_testimonial_single gs-col-lg-{$desktop_columns} gs-col-md-{$tablet_columns} gs-col-sm-{$columns_mobile} gs-col-xs-{$columns_small_mobile}");
        $image_id = get_post_meta( get_the_ID(), 'rudr_img', true );
        $rating = get_post_meta( get_the_ID(), 'gs_t_rating', true );
        $testi_title = get_the_title();
        $designatin = get_post_meta( get_the_ID(), 'gs_t_client_design', true );
        $client_name = get_post_meta( get_the_ID(), 'gs_t_client_name', true );
        $testi_date = get_the_date( get_option( 'date_format' ) );
        $company = get_post_meta( get_the_ID(), 'gs_t_client_company', true );
        $client_phone = get_post_meta( get_the_ID(), 'gs_t_client_phone', true );
        $client_email = get_post_meta( get_the_ID(), 'gs_t_client_email_address', true );
        $client_address = get_post_meta( get_the_ID(), 'gs_t_client_location', true );
        $client_website = get_post_meta( get_the_ID(), 'gs_t_website_url', true );
        if ( $view_type == 'filter' ) {
            $classes[] = get_terms_slugs();
        }
        ?>
				
				<div class="<?php 
        echo esc_attr( implode( ' ', $classes ) );
        ?>">

					<div class="testimonial-box has-shadow">
						
						<!-- Company Image -->
						<?php 
        ?>

						<!-- Testimonial Title -->
						<?php 
        if ( $testi_title && $shortcode_settings['show_title'] ) {
            ?>
							<h3 class="box-tm-title"><?php 
            echo esc_html( $testi_title );
            ?></h3>
						<?php 
        }
        ?>

						<!-- Testimonial Content -->
						<?php 
        if ( $allow_html ) {
            ?>
							<div class="box-content">
								<?php 
            echo wpautop( wp_kses_post( get_the_content() ) );
            ?>
								<?php 
            echo gstm_read_more( $is_popup_enabled, $shortcode_id );
            ?>
							</div>
							<?php 
        } else {
            ?>
							<div class="box-content"><?php 
            echo wpautop( get_description( $gs_tm_details_contl, $is_popup_enabled, $shortcode_id ) );
            ?></div>
							<?php 
        }
        ?>
						
						<!-- Rating -->
						<?php 
        if ( $shortcode_settings['ratings'] && !empty( $rating ) ) {
            $args = array(
                'rating' => $rating,
                'type'   => 'rating',
                'number' => '',
                'echo'   => true,
            );
            wp_star_rating( $args );
        }
        ?>

						<!-- Testimonial Author info -->
						
					</div> <!-- End of testimonial-box -->

					<div class="testimonial-author-info">

						<!-- Testimonial Image -->
						<?php 
        if ( has_post_thumbnail() && $shortcode_settings['image'] ) {
            ?>
							<div class="box-image"><?php 
            the_post_thumbnail( $shortcode_settings['imageSize'] );
            ?></div>
						<?php 
        }
        ?>

						<div class="gs-tai-client">

							<!-- Testimonial Name -->
							<h4 class="box-client-name"><?php 
        echo esc_html( $client_name );
        ?></h4>

							<!-- Client Designation -->
							<?php 
        if ( $designatin && $shortcode_settings['show_designation'] ) {
            ?>
								<div class="box-desiginfo">
									<span class="box-design-name"><?php 
            echo esc_html( $designatin );
            ?></span>
								</div>
							<?php 
        }
        ?>

							<!-- Client Company -->
							<?php 
        ?>

						</div>

					</div> <!-- End of testimonial-author-info -->

					<?php 
        ?>

				</div> <!-- End of gs_testimonial_single -->

			<?php 
    }
    ?>
		</div>
		<?php 
} else {
    echo esc_html( 'No Testimonial Added!', 'gs-testimonial' );
}
wp_reset_query();
?>
</div><?php 