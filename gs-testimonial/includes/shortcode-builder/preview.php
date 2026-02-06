<html class="no-js" <?php language_attributes(); ?>>

    <head>

        <meta charset="<?php bloginfo( 'charset' ); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1.0" >

        <?php wp_head(); ?>

    </head>

    <body class="gstm-shortcode-preview--page">

        <div class="gs-shortcode-preview--container">

            <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

                <div class="gs-shortcode-preview--wrapper shortcode-found">

                    <?php echo do_shortcode( get_the_content() ); ?>
                
                </div>

            <?php endwhile;
            wp_reset_postdata();
        else: ?>

                <div class="gs-shortcode-preview--wrapper something-wrong">

                    <h2><?php _e( 'Something went wrong!', 'gs-testimonial' ); ?></h2>
                    <p><?php _e( 'Data not found for preview, probably it\'s a bug, contact with plugin author', 'gs-testimonial' ); ?></p>

                </div>

            <?php endif; ?>

        </div>

        <?php wp_footer(); ?>

    </body>
    
</html>