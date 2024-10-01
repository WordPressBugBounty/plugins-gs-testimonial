<?php

namespace GSTM;

// if direct access than exit the file.
defined( 'ABSPATH' ) || exit;
/**
 * Registers meta boxes for the testimonial
 * 
 * @since 1.0.0
 */
class Meta_Fields {
    /**
     * Class constructor.
     * 
     * @since 1.0.0
     */
    public function __construct() {
        add_action( 'add_meta_boxes', [$this, 'meta_boxes'] );
        add_action( 'save_post', [$this, 'save_meta_box_data'] );
        add_action( 'add_meta_boxes', [$this, 'upgrade_to_pro'] );
    }

    /**
     * Adds a box to the main column on the Post and Page edit screens.
     * 
     * @since 1.0.0
     */
    public function meta_boxes() {
        add_meta_box(
            'gs_testimonial_sectionid',
            __( "Reviewer Information", 'gs-testimonial' ),
            [$this, 'reviewer_information'],
            'gs_testimonial',
            'normal',
            'high'
        );
    }

    public function pro_meta_boxes_premium_only() {
        add_meta_box(
            'gs_testimonial_sectionid_pro',
            __( 'Reviewer Extra Information', 'gs-testimonial' ),
            array($this, 'reviewerExtraInformation__premium_only'),
            'gs_testimonial',
            'normal',
            'high'
        );
        add_meta_box(
            'company_logo',
            __( 'Company Logo', 'gs-testimonial' ),
            [$this, 'add_media_uploader__premium_only'],
            'gs_testimonial',
            'normal',
            'high'
        );
        add_meta_box(
            'gs_testimonial_social_profiles',
            __( 'Social Profiles', 'gs-testimonial' ),
            array($this, 'social_profiles__premium_only'),
            'gs_testimonial',
            'normal',
            'high'
        );
    }

    /**
     * Registers upgrade to pro fields.
     *
     * @since 1.0.0
     */
    public function upgrade_to_pro() {
        add_meta_box(
            'gs_testimonial_sectionid_pro',
            __( "Reviewer Extra Information", 'gs-testimonial' ),
            [$this, 'get_reviewer_extra_information'],
            'gs_testimonial',
            'normal',
            'high'
        );
        add_meta_box(
            'gs_testimonial_social_profiles',
            __( "Social Profiles", 'gs-testimonial' ),
            [$this, 'get_social_profiles'],
            'gs_testimonial',
            'normal',
            'high'
        );
    }

    /**
     * Displays upgrade to pro to get reviewer extra information.
     *
     * @since  1.0.0
     * 
     * @return void
     */
    public function get_reviewer_extra_information() {
        ?>
        <p>
            <a target="_blank" href="https://www.gsplugins.com/product/gs-testimonial-slider/#pricing">
                <b><?php 
        _e( 'Upgrade to PRO', 'gs-testimonial' );
        ?></b>
            </a><?php 
        _e( ' to get these advanced features.', 'gs-testimonial' );
        ?> 
        </p>
        <div class="pro-only">

            <p><label for="email"><b><?php 
        _e( 'Email:', 'gs-testimonial' );
        ?></b></label></p>
            <p>
                <input
                    class="large-text"
                    type="text"
                    name="email"
                    id="email"
                    value=""
                />
            </p>

            <p><label for="address"><b><?php 
        _e( 'Address:', 'gs-testimonial' );
        ?></b></label></p>
            <p>
                <input
                    class="large-text"
                    type="text"
                    name="address"
                    name="address"
                    id="address"
                    value=""
                />
            </p>

            <p><label for="phone"><b><?php 
        _e( 'Phone/Mobile:', 'gs-testimonial' );
        ?></b></label></p>
            <p>
                <input
                    class="large-text"
                    type="text"
                    name="phone"
                    id="phone"
                    value=""
                />
            </p>

            <p><label for="website"><b><?php 
        _e( 'Website URL:', 'gs-testimonial' );
        ?></b></label></p>
            <p>
                <input
                    class="large-text"
                    type="text"
                    name="website"
                    id="website"
                    value=""
                />
            </p>

			<p><label for="gs_t_client_company"><b><?php 
        _e( 'Organization Name:', 'gs-testimonial' );
        ?></b></label></p>
			<p>
				<input
					class="large-text"
					type="text"
					name="gs_t_client_company"
					id="gs_t_client_company"
					value=""
				/>
			</p>

            <p><label for="video"><b><?php 
        _e( 'Video URL:', 'gs-testimonial' );
        ?></b></label></p>
            <p>
                <input
                    class="large-text"
                    type="text"
                    name="video"
                    id="video"
                    value=""
                />
            </p>
        </div>
        <?php 
    }

    /**
     * Displays upgrade to pro to get social profiles.
     *
     * @since  1.0.0
     *
     * @param  WP_Post $post The object for the current post/page.
     * @return void
     */
    public function get_social_profiles( $post ) {
        ?>
        <p>
            <a target="_blank" href="https://www.gsplugins.com/product/gs-testimonial-slider/#pricing">
                <b><?php 
        _e( 'Upgrade to PRO', 'gs-testimonial' );
        ?></b>
            </a><?php 
        _e( ' to get these advanced features.', 'gs-testimonial' );
        ?></p>
        <div class="pro-only">
            <ul id="gstm_social_profiles">
                <li class="gstm-repeater-item">
                    <div class="gstm-repeater-content">
                        <div class="gstm-field">
                            <select name="" id="">
                                <option value=""><?php 
        _e( 'Select icon', 'gs-testimonial' );
        ?></option>
                            </select>
                            <div class="clear"></div>
                        </div>
                        <div class="gstm-field gstm-field-text social-url">
                            <input type="text" name="gs_t_social_profiles[url][]" id="" value="" />
                            <div class="clear"></div>
                        </div>
                    </div>
                    <div class="gstm-repeater-helper">
                        <div class="gstm-repeater-helper-inner">
                            <i class="gstm-repeater-sort fas fa-arrows-alt"></i>
                            <i class="gstm-repeater-remove gstm-confirm fas fa-times" data-confirm="Are you sure to delete this item?"></i>
                        </div>
                    </div>
                </li>
            </ul>
            <a href="#" class="button button-primary gstm-repeater-add">Add Row</a>
        </div>
	    <?php 
    }

    /**
     * Displays the reviewer information meta boxes.
     * 
     * @since 1.0.0
     * @param WP_Post $post The object for the current post/page.
     *
     * @return void
     */
    public function reviewer_information( $post ) {
        wp_nonce_field( 'gs_testimonial_meta_box', 'gs_testimonial_meta_box_nonce' );
        $rating = get_post_meta( $post->ID, 'gs_t_rating', true );
        $rating = ( !empty( $rating ) ? $rating : 2 );
        ?>

		<p><label for="gs_t_client_name"><b><?php 
        _e( 'Reviewer Name:', 'gs-testimonial' );
        ?></b></label></p>
        <p>
			<input
				class="large-text"
				type="text"
				name="gs_t_client_name"
				id="gs_t_client_name"
				value="<?php 
        echo esc_attr( get_post_meta( $post->ID, 'gs_t_client_name', true ) );
        ?>"
			/>
		</p>

		<p><label for="gs_t_client_design"><b><?php 
        _e( 'Designation:', 'gs-testimonial' );
        ?></b></label></p>
        <p>
			<input
				class="large-text"
				type="text"
				name="gs_t_client_design"
				id="gs_t_client_design"
				value="<?php 
        echo esc_attr( get_post_meta( $post->ID, 'gs_t_client_design', true ) );
        ?>"
			/>
		</p>

        <p><label for="gs_t_rating"><b><?php 
        _e( 'Rating:', 'gs-testimonial' );
        ?></b></label></p>
        <p>
            <input
                name="gs_t_rating"
                type="range"
                value="<?php 
        echo esc_attr( $rating );
        ?>"
                step="0.25"
                id="gs_t_rating"
                style="display:none"
            />
            <div
                class="rateit bigstars"
                data-rateit-starwidth="32"
                data-rateit-starheight="32"
                data-rateit-backingfld="#gs_t_rating"
                data-rateit-resetable="false"
                data-rateit-ispreset="true"
                data-rateit-min="0" data-rateit-max="5"
            >
            </div>
        </p>
		<?php 
        do_action( 'add_gstm_metabox', $post );
    }

    /**
     * When the post is saved, saves our custom data.
     *
     * @since 1.0.0
     * @param int $post_id The ID of the post being saved.
     */
    public function save_meta_box_data( $post_id ) {
        // Check if our nonce is set.
        if ( !isset( $_POST['gs_testimonial_meta_box_nonce'] ) ) {
            return;
        }
        // Verify that the nonce is valid.
        if ( !wp_verify_nonce( $_POST['gs_testimonial_meta_box_nonce'], 'gs_testimonial_meta_box' ) ) {
            return;
        }
        // If this is an autosave, our form has not been submitted, so we don't want to do anything.
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        // Check the user's permissions.
        if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {
            if ( !current_user_can( 'edit_page', $post_id ) ) {
                return;
            }
        } else {
            if ( !current_user_can( 'edit_post', $post_id ) ) {
                return;
            }
        }
        if ( isset( $_POST['gs_t_client_name'] ) ) {
            update_post_meta( $post_id, 'gs_t_client_name', sanitize_text_field( $_POST['gs_t_client_name'] ) );
        }
        if ( isset( $_POST['gs_t_client_design'] ) ) {
            update_post_meta( $post_id, 'gs_t_client_design', sanitize_text_field( $_POST['gs_t_client_design'] ) );
        }
        if ( isset( $_POST['gs_t_rating'] ) ) {
            update_post_meta( $post_id, 'gs_t_rating', filter_var( $_POST['gs_t_rating'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION ) );
        }
    }

    /**
     * Build the select control with social icons.
     *
     * @param           $name
     * @param   string $selected
     * @param   string $selecttext
     * @param   string $class
     * @param   string $optionvalue
     */
    function build_select(
        $name,
        $options,
        $selected = '',
        $selecttext = '',
        $class = '',
        $optionvalue = 'value'
    ) {
        if ( is_array( $options ) ) {
            $select_html = sprintf( '<select name="%1$s" id="%1$s" class="%2$s">', esc_attr( $name ), esc_attr( $class ) );
            if ( $selecttext ) {
                $select_html .= sprintf( '<option value="">%s</option>', esc_html( $selecttext ) );
            }
            foreach ( $options as $key => $option ) {
                $value = ( $optionvalue == 'value' ? $option : $key );
                $is_selected = ( $value == $selected ? 'selected="selected"' : '' );
                $select_html .= sprintf(
                    '<option value="%s" %s>%s</option>',
                    esc_attr( $value ),
                    $is_selected,
                    esc_html( $option )
                );
            }
            $select_html .= '</select>';
            echo gs_wp_kses( $select_html );
        }
    }

}
