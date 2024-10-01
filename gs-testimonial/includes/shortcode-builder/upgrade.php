<?php

namespace GSTM;

/**
 * Protect direct access
 */
if ( ! defined( 'ABSPATH' ) ) exit;

trait Upgrade {

    public function maybe_upgrade_data( $old_version ) {
        if ( version_compare( $old_version, '3.2.6' ) < 0 ) $this->upgrade_to_3_2_6();
        if ( version_compare( $old_version, '3.2.7' ) < 0 ) $this->upgrade_to_3_2_7();
    }

    public function upgrade_to_3_2_6__taxonomies() {

        $wpdb = self::get_wpdb();

        $term_taxonomy_ids = $wpdb->get_results( "SELECT term_taxonomy_id FROM $wpdb->term_taxonomy WHERE taxonomy='testimonial_category'", ARRAY_A );

        if ( $this->gstm_check_db_error() ) {
            die( sprintf( __( 'GS Testimonial Upgrade failed. Database Error: %s' ), $wpdb->last_error ) );
        }

        if ( empty($term_taxonomy_ids) ) return;

        $term_taxonomy_ids = wp_list_pluck( $term_taxonomy_ids, 'term_taxonomy_id' );

        foreach ( $term_taxonomy_ids as $term_taxonomy_id ) {
            $wpdb->update( $wpdb->term_taxonomy, array( 'taxonomy' => 'gs_testimonial_category' ), array( 'term_taxonomy_id' => $term_taxonomy_id ) );
        }

    }

    public function upgrade_to_3_2_6__font_awesome() {

        $social_icons_map = [
            "envelope"                => "fas fa-envelope",
            "link"                    => "fas fa-link",
            "google-plus"             => "fab fa-google-plus-g",
            "facebook"                => "fab fa-facebook-f",
            "instagram"               => "fab fa-instagram",
            "whatsapp"                => "fab fa-whatsapp",
            "twitter"                 => "fab fa-x-twitter",
            "youtube"                 => "fab fa-youtube",
            "vimeo-square"            => "fab fa-vimeo-square",
            "flickr"                  => "fab fa-flickr",
            "dribbble"                => "fab fa-dribbble",
            "behance"                 => "fab fa-behance",
            "dropbox"                 => "fab fa-dropbox",
            "wordpress"               => "fab fa-wordpress",
            "tumblr"                  => "fab fa-tumblr",
            "skype"                   => "fab fa-skype",
            "linkedin"                => "fab fa-linkedin-in",
            "stack-overflow"          => "fab fa-stack-overflow",
            "pinterest"               => "fab fa-pinterest",
            "foursquare"              => "fab fa-foursquare",
            "github"                  => "fab fa-github",
            "xing"                    => "fab fa-xing",
            "stumbleupon"             => "fab fa-stumbleupon",
            "delicious"               => "fab fa-delicious",
            "lastfm"                  => "fab fa-lastfm",
            "hacker-news"             => "fab fa-hacker-news",
            "reddit"                  => "fab fa-reddit",
            "soundcloud"              => "fab fa-soundcloud",
            "yahoo"                   => "fab fa-yahoo",
            "trello"                  => "fab fa-trello",
            "steam"                   => "fab fa-steam-symbol",
            "deviantart"              => "fab fa-deviantart",
            "twitch"                  => "fab fa-twitch",
            "feed"                    => "fas fa-rss",
            "renren"                  => "fab fa-renren",
            "vk"                      => "fab fa-vk",
            "vine"                    => "fab fa-vine",
            "spotify"                 => "fab fa-spotify",
            "digg"                    => "fab fa-digg",
            "slideshare"              => "fab fa-slideshare",
            "bandcamp"                => "fab fa-bandcamp",
            "map-pin"                 => "fas fa-map-pin",
            "map-marker"              => "fas fa-map-marker-alt"
        ];

        $testimonials = get_posts([
            'numberposts' => -1,
            'post_type' => 'gs_testimonial',
            'fields' => 'ids'
        ]);

        foreach ( $testimonials as $tm_id ) {

            $social_data = get_post_meta( $tm_id, 'gs_t_social_profiles', true );

            $social_data_new = [];

            foreach ( $social_data as $social_icon => $social_link ) {

                if ( array_key_exists( $social_icon, $social_icons_map ) ) {
                    $social_data_new[ $social_icons_map[ $social_icon ] ] = $social_link;
                } else {
                    $social_data_new[ $social_icon ] = $social_link;
                }

            }

            update_post_meta( $tm_id, 'gs_t_social_profiles', $social_data_new );

        }

    }

    public function upgrade_to_3_2_6() {

        // Update Taxonomies
        $this->upgrade_to_3_2_6__taxonomies();

        // Update Font Awesome
        $this->upgrade_to_3_2_6__font_awesome();

        // Flush rewrite rules
        $this->upgrade_to_3_2_6__font_awesome();

    }

    public function upgrade_to_3_2_7() {

        // Flush rewrite rules
        delete_option('gs_testimonial_plugin_permalinks_flushed');

    }

}