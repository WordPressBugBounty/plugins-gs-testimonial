<?php

namespace GSTM;

if ( $allow_html ) : ?>
    
    <div class="box-content">
        <div class="box-content--wrapper">
            <?php echo wpautop( wp_kses_post( get_the_content() ) ); ?>
        </div>
        <?php echo gstm_read_more( $is_popup_enabled, $shortcode_id ); ?>
    </div>

<?php else: ?>

    <div class="box-content"><?php echo wpautop( get_description( $gs_tm_details_contl, $is_popup_enabled, $shortcode_id ) ); ?></div>

<?php endif; ?>