<?php

namespace CustomTheme\Core;

class Site {

	private static $instance;

	private $static_url;

	private function __construct() {
		$this->static_url = esc_url( get_template_directory_uri() . '/' . DIR_PATH_ASSETS . '/build' );
	}

	public static function getInstance(): Site {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Возвращает путь к папке со статическими файлами (css/js/fonts/images).
	 *
	 * @return string
	 */
	public function get_assets_url(): string {
		return $this->static_url;
	}

	/**
	 * Подключает шаблон хедера сайта.
	 *
	 * @param array|string $args
	 */
	public function header( $args = [] ): void {
		$args = $this->normalize_template_args( $args );

		$name = $args['tpl'] ?: '/commons/header/header.php';

		do_action( 'get_header', $name, $args );

		$this->template( $name, $args );
	}

	/**
	 * Подключает шаблон футера сайта.
	 *
	 * @param array|string $args
	 */
	public function footer( $args = [] ): void {
		$args = $this->normalize_template_args( $args );

		$name = $args['tpl'] ?: '/commons/footer/footer.php';

		do_action( 'get_footer', $name, $args );

		$this->template( $name, $args );
	}

	/**
	 * Приводит параметры шаблона к единому виду.
	 *
	 * @param array|string $args
	 *
	 * @return string[]
	 */
	private function normalize_template_args( $args = [] ): array {
		if ( is_string( $args ) ) {
			$args = [ 'tpl' => $args ];
		}

		return array_merge(
			[ 'class' => null, 'tpl' => null ],
			$args
		);
	}

	public function template( $slug, $args = [] ): void {
		$slug = wp_normalize_path(
			trim(
				str_replace( '.php', '', "/" . DIR_PATH_TEMPLATES . "/$slug" ),
				'/'
			)
		);

		get_template_part( $slug, null, $args );
	}

	public function abs_template( $dir, $slug, $args = [] ): void {
		$dir = str_replace(
			wp_normalize_path( get_theme_file_path() . '/' . DIR_PATH_TEMPLATES ),
			'',
			wp_normalize_path( $dir )
		);

		$slug = wp_normalize_path(
			trim(
				str_replace( '.php', '', "/" . DIR_PATH_TEMPLATES . "/$dir/$slug" ),
				'/'
			)
		);

		get_template_part( $slug, null, $args );
	}

	/**
	 * Выводит на экран содержимое SVG-файла.
	 *
	 * @param $slug
	 *
	 * @return void
	 */
	public function show_svg( $slug ): void {
		$path = wp_normalize_path( get_template_directory() . '/' . DIR_PATH_ASSETS . "/build/img/$slug" );

		include $path;
	}

}
