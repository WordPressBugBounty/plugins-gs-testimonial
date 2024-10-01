<?php

namespace GSTM;

// if direct access than exit the file.
defined('ABSPATH') || exit;

$classmaps = [
	'Plugin'             => 'includes/plugin.php',
	'Columns' 		     => 'includes/columns.php',
	'Cpt'       	     => 'includes/cpt.php',
	'Hooks'              => 'includes/hooks.php',
	'Meta_Fields'        => 'includes/meta-fields.php',
	'Notices'            => 'includes/notices.php',
	'Template_Loader'    => 'includes/template-loader.php',
	'Scripts'            => 'includes/scripts.php',
	'Term_Sort'          => 'includes/term-sort.php',
	'Sortable'           => 'includes/sortable.php',
	'Dummy_Data'         => 'includes/demo-data/dummy-data.php',
	'Builder'            => 'includes/shortcode-builder/builder.php',
	'Upgrade'            => 'includes/shortcode-builder/upgrade.php',
	'Integrations'       => 'includes/integrations/integrations.php',
	'Shortcode'          => 'includes/shortcode.php',
	'Hooks'              => 'includes/hooks.php'
];

return $classmaps;
