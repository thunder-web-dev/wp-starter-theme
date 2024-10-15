<?php

namespace CustomTheme\Core;

new Assets();

class Assets {

	public function __construct() {
		add_action( 'wp_enqueue_scripts', [ $this, 'attach_assets' ], 11 );
	}

	public function attach_assets() {
		// $this->attach_script( '/assets/build/libraries/slick/slick.js' );
		// $this->attach_style( '/assets/build/libraries/slick/slick.css' );

		// $this->attach_script( '/assets/build/libraries/magnific-popup/jquery.magnific-popup.min.js', [ 'jquery' ] );
		// $this->attach_style( '/assets/build/libraries/magnific-popup/magnific-popup.css' );

		// $this->attach_script( '/assets/build/libraries/fancybox/fancybox.umd.js', [ 'jquery' ] );
		// $this->attach_style( '/assets/build/libraries/fancybox/fancybox.css' );

		$this->attach_style( '/build/css/main.css' );
		$this->attach_script( '/build/js/main.js' );

		// @see assets/temp/README.md
		$this->attach_style( '/temp/temp.css' );
		$this->attach_script( '/temp/temp.js' );
	}

	private function attach_style( $path, $deps = [] ) {
		$handle = $this->get_handle( $path );

		wp_enqueue_style( $handle, $this->get_url( $path ), $deps, $this->get_ver( $path ) );

		return $handle;
	}

	private function attach_script( $path, $deps = [] ) {
		$handle = $this->get_handle( $path );

		wp_enqueue_script( $this->get_handle( $path ), $this->get_url( $path ), $deps, $this->get_ver( $path ), true );

		return $handle;
	}

	private function get_handle( string $path ) {
		return sanitize_title( $path );
	}

	private function get_url( $path ) {
		return $this->is_local( $path ) ? wp_normalize_path( get_theme_file_uri( DIR_PATH_ASSETS . $path ) ) : $path;
	}

	private function get_ver( $path ) {
		return $this->is_local( $path ) ? filemtime( get_theme_file_path( DIR_PATH_ASSETS . $path ) ) : '';
	}

	private function is_local( string $path ): bool {
		return ! str_starts_with( $path, 'http' );
	}

}
