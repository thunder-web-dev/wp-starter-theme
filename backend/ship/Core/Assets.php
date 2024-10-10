<?php

namespace CustomTheme;

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

		$this->attach_style( '/assets/build/css/main.css' );
		$this->attach_script( '/assets/build/js/main.js', [ 'jquery' ] );

		// @see assets/temp/README.md
		$this->attach_style( '/assets/temp/temp.css' );
		$this->attach_script( '/assets/temp/temp.js', [ 'jquery' ] );
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

	private function get_handle( $path ) {
		return sanitize_title( $path );
	}

	private function get_url( $path ) {
		return wp_normalize_path( get_theme_file_uri( $path ) );
	}

	private function get_ver( $path ) {
		return filemtime( get_theme_file_path( $path ) );
	}

}
