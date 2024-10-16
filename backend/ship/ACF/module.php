<?php

if ( function_exists( 'get_field' ) ) {
	include_dir_files( __DIR__ );

	$acf = new CustomTheme\ACF\ACF();
	$acf->hooks();
}

