<?php

/**
 *如果需要复写CSS
 *
 */
add_action(
	'wp_enqueue_scripts',
	function () {
		wp_enqueue_style('wndt-child-style', get_stylesheet_uri());
	},
	29
);
