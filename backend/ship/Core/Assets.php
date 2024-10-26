<?php

namespace CustomTheme\Core;

new Assets();

class Assets {

	public function __construct() {
		add_action( 'wp_enqueue_scripts', [ $this, 'attach_assets' ], 11 );
	}

	public function attach_assets(): void {
		// $this->attach_script( '/assets/build/libraries/slick/slick.js' );
		// $this->attach_style( '/assets/build/libraries/slick/slick.css' );

		// $this->attach_script( '/assets/build/libraries/magnific-popup/jquery.magnific-popup.min.js', [ 'jquery' ] );
		// $this->attach_style( '/assets/build/libraries/magnific-popup/magnific-popup.css' );

		// $this->attach_script( '/assets/build/libraries/fancybox/fancybox.umd.js', [ 'jquery' ] );
		// $this->attach_style( '/assets/build/libraries/fancybox/fancybox.css' );

		$main_css = $this->attach_style( '/build/css/main.css' );
		$main_js  = $this->attach_script( '/build/js/main.js' );

		// @see assets/temp/README.md
		$this->attach_style( '/temp/temp.css', [ $main_css ] );
		$this->attach_script( '/temp/temp.js', [ $main_js ] );
	}

	private function attach_style( $path, $deps = [] ): ?string {
		if ( $this->is_empty_local_file( $path ) ) {
			return null;
		}

		$handle = $this->get_handle( $path );

		wp_enqueue_style( $handle, $this->get_url( $path ), $deps, $this->get_ver( $path ) );

		return $handle;
	}

	private function attach_script( $path, $deps = [] ): ?string {
		if ( $this->is_empty_local_file( $path ) ) {
			return null;
		}

		$handle = $this->get_handle( $path );

		wp_enqueue_script( $handle, $this->get_url( $path ), $deps, $this->get_ver( $path ), true );

		return $handle;
	}

	private function get_handle( string $path ): string {
		return sanitize_title( $path );
	}

	private function get_url( string $path ): string {
		return $this->is_local( $path ) ? wp_normalize_path( get_theme_file_uri( $this->get_rel_path( $path ) ) ) : $path;
	}

	private function get_full_path( string $path ): string {
		return get_theme_file_path( $this->get_rel_path( $path ) );
	}

	private function get_rel_path( string $path ): string {
		return DIR_PATH_ASSETS . $path;
	}

	private function get_ver( string $path ): string {
		return $this->is_local( $path ) ? filemtime( $this->get_full_path( $path ) ) : '';
	}

	private function is_local( string $path ): bool {
		return ! str_starts_with( $path, 'http' );
	}

	private function is_empty_local_file( string $path ): bool {
		if ( ! $this->is_local( $path ) ) {
			return false;
		}

		$filesize = @filesize( $this->get_full_path( $path ) );

		return empty( $filesize );
	}

}
