<?php
namespace GSTM;

if ( ! defined( 'ABSPATH' ) ) exit;

require_once __DIR__ . '/gs-asset-generator-base.php';
require_once __DIR__ . '/gs-testimonial-asset-generator.php';

// Needed for pro compatibility
do_action( 'gs_testimonial_assets_generator_loaded' );