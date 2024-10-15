<?php

namespace CustomTheme\ACF;

class ACF {
	private string $json_dir_path;

	/**
	 * @throws \Exception
	 */
	public function __construct() {
		$this->json_dir_path = __DIR__ . '/groups-and-fields';

		if ( class_exists( 'WP_CLI' ) ) {
			\WP_CLI::add_command( 'dev acf make json', [ $this, 'make_json' ] );
		}
	}

	public function hooks(): void {
		// Сохранение и чтение json-конфигураций групп полей
		add_filter( 'acf/settings/load_json', [ $this, 'set_dir_json_for_load' ] );
		add_filter( 'acf/settings/save_json', [ $this, 'set_dir_json_for_save' ] );
		add_filter( 'acf/location/rule_types', [ $this, 'location_rules' ] );

		// Скрываем админку ACF на продакшене.
		add_filter( 'acf/settings/show_admin', [ $this, 'show_admin' ] );
	}

	public function show_admin() {
		return \is_dev_site();
	}

	/**
	 * Изменяет путь к папке с json-конфигурациями групп полей при чтении.
	 *
	 * @return array
	 */
	public function set_dir_json_for_load(): array {
		return (array) $this->json_dir_path;
	}

	/**
	 * Изменяет путь к папке с json-конфигурациями групп полей при загрузке.
	 *
	 * @return string
	 */
	public function set_dir_json_for_save(): string {
		$this->maybe_mkdir_for_json_files();

		return $this->json_dir_path;
	}

	/**
	 * Создаёт папку с json файлами, если её ещё нет.
	 *
	 * @return bool
	 */
	private function maybe_mkdir_for_json_files(): bool {
		return wp_mkdir_p( $this->json_dir_path );
	}

	/**
	 * Создаёт json-болванку для группы полей ACF с нужным ключом и названием.
	 *
	 * ## OPTIONS
	 *
	 * <title>
	 * : Название группы полей.
	 *
	 * <key>
	 * : Ключ группы полей.
	 *
	 * [--yes]
	 * : Подтверждает удаление без запроса подтверждения.
	 *
	 * ## EXAMPLES
	 *
	 * wp dev acf make json  "Настройки одиночной новости" news_single_settings
	 * @throws \WP_CLI\ExitException
	 */
	public function make_json( $args, $assoc_args ): void {
		$group_name = $args[0] ?? '';
		$file_name  = $args[1] ?? '';

		if ( ! $group_name || ! $file_name ) {
			\WP_CLI::error( 'Вы не ввели название группы полей или файла' );
		}

		if ( ! $this->maybe_mkdir_for_json_files() ) {
			\WP_CLI::error( 'Папка для json файлов не создана.' );
		}

		$firstChar    = mb_substr( $group_name, 0, 1, 'UTF-8' );
		$restOfString = mb_substr( $group_name, 1, null, 'UTF-8' );
		$group_name   = mb_strtoupper( $firstChar, 'UTF-8' ) . $restOfString;

		$file_name = "group_$file_name";
		$file_name = preg_replace( '/^(group_)+/', 'group_', $file_name );

		$json = file_get_contents( wp_normalize_path( __DIR__ . '/group_sample.json' ) );
		$json = str_replace( [ 'group_key', 'group_title' ], [ $file_name, $group_name ], $json );

		$path = wp_normalize_path( "$this->json_dir_path/$file_name.json" );

		if ( file_exists( $path ) ) {
			\WP_CLI::confirm( 'Вы уверены, что хотите перезаписать файл?', $assoc_args );
		}

		file_put_contents( $path, $json );

		\WP_CLI::success( "Файл записан: $path" );
	}

	public function location_rules( array $choices ): array {
		$choices['Особое']['never_show'] = 'Никогда не показывать';

		return $choices;
	}

}
