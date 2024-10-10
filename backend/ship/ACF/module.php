<?php

if ( function_exists( 'get_field' ) ) {
	$acf = new CustomTheme\ACF\ACF();
	$acf->hooks();
}

