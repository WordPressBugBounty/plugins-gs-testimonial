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
        $classes = [
            'gs_testimonial_single',
            "gs-col-lg-{$desktop_columns}",
            "gs-col-md-{$tablet_columns}",
            "gs-col-sm-{$columns_mobile}",
            "gs-col-xs-{$columns_small_mobile}"
        ];
        if ( $view_type === 'filter' ) {
            $classes[] = get_terms_slugs();
        }
        $image_id = get_post_meta( get_the_ID(), 'rudr_img', true );
        $rating = get_post_meta( get_the_ID(), 'gs_t_rating', true );
        $testi_title = get_the_title();
        $designatin = get_post_meta( get_the_ID(), 'gs_t_client_design', true );
        $client_name = get_post_meta( get_the_ID(), 'gs_t_client_name', true );
        $company = get_post_meta( get_the_ID(), 'gs_t_client_company', true );
        $client_phone = get_post_meta( get_the_ID(), 'gs_t_client_phone', true );
        $client_email = get_post_meta( get_the_ID(), 'gs_t_client_email_address', true );
        $client_address = get_post_meta( get_the_ID(), 'gs_t_client_location', true );
        $client_website = get_post_meta( get_the_ID(), 'gs_t_website_url', true );
        ?>

			<div class="<?php 
        echo esc_attr( implode( ' ', $classes ) );
        ?>">

				<div class="testimonial-box has-shadow">

					<!-- Company Image -->
					<?php 
        ?>

					<!-- Testimonial Title -->
						<h3 class="box-tm-title <?php 
        echo esc_attr( gstm_get_visibility_class( 'gstm_card_title', $card_visibility_settings ) );
        ?>">
							<?php 
        echo esc_html( $testi_title );
        ?>
						</h3>

					<!-- Content -->
					<div class="<?php 
        echo esc_attr( gstm_get_visibility_class( 'gstm_card_description', $card_visibility_settings ) );
        ?>">
						<?php 
        include Template_Loader::locate_template( 'partials/gs-layout-content.php' );
        ?>
					</div>

					<!-- Rating -->
					<?php 
        if ( !empty( $rating ) ) {
            ?>
						<div class="<?php 
            echo esc_attr( gstm_get_visibility_class( 'gstm_card_ratings', $card_visibility_settings ) );
            ?>">
							<?php 
            wp_star_rating( [
                'rating' => $rating,
                'type'   => 'rating',
                'echo'   => true,
            ] );
            ?>
						</div>
					<?php 
        }
        ?>

				</div>

				<div class="testimonial-author-info">

					<!-- Reviewer Image -->
					<?php 
        if ( has_post_thumbnail() ) {
            ?>
						<div class="box-image <?php 
            echo esc_attr( gstm_get_visibility_class( 'gstm_card_reviewer_image', $card_visibility_settings ) );
            ?>">
							<?php 
            the_post_thumbnail( $shortcode_settings['imageSize'] );
            ?>
						</div>
					<?php 
        }
        ?>

					<div class="gs-tai-client">

						<h4 class="box-client-name <?php 
        echo esc_attr( gstm_get_visibility_class( 'gstm_card_reviewer_name', $card_visibility_settings ) );
        ?>">
							<?php 
        echo esc_html( $client_name );
        ?>
						</h4>

						<?php 
        if ( $designatin ) {
            ?>
							<div class="box-desiginfo <?php 
            echo esc_attr( gstm_get_visibility_class( 'gstm_card_reviewer_designation', $card_visibility_settings ) );
            ?>">
								<span class="box-design-name"><?php 
            echo esc_html( $designatin );
            ?></span>
							</div>
						<?php 
        }
        ?>

						<?php 
        if ( gstm_fs()->can_use_premium_code__premium_only() && $company ) {
            ?>
							<div class="box-companyinfo <?php 
            echo esc_attr( gstm_get_visibility_class( 'gstm_card_company_name', $card_visibility_settings ) );
            ?>">
								<span class="box-company-name"><?php 
            echo esc_html( $company );
            ?></span>
							</div>
						<?php 
        }
        ?>

					</div>
				</div>

				<?php 
        include Template_Loader::locate_template( 'partials/gs-popup-content.php' );
        ?>

			</div>

			<?php 
    }
    wp_reset_postdata();
    ?>

		</div>
	<?php 
} else {
    ?>
		<?php 
    echo esc_html__( 'No Testimonial Added!', 'gs-testimonial' );
    ?>
	<?php 
}
?>

</div>
<?php 