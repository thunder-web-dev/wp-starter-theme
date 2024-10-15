<?php

namespace CustomTheme\Upgrade;

use Plugin_Upgrader;
use WP_Ajax_Upgrader_Skin;

trait Helper {

	// Удаление дефолтных WP сущностей
	public static function remove_default_wp_entities() {
		wp_delete_post( 1, true );
		wp_delete_post( 2, true );
		wp_delete_comment( 1, true );
	}
	
	/**
	 * По указанной ссылке устанавливает и активирует плагин.
	 *
	 * @param string $url         Ссылка на архив с плагином
	 * @param string $plugin_name Имя плагина в формате "query-monitor/query-monitor.php"
	 */
	public static function install_plugin_from_url( string $url, string $plugin_name = '' ): void {
		include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		include_once ABSPATH . 'wp-admin/includes/file.php';
		include_once ABSPATH . 'wp-admin/includes/misc.php';
		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		if ( $plugin_name && is_plugin_active( $plugin_name ) ) {
			error_log( print_r( "Плагин $plugin_name уже установлен и активен", true ) );

			return;
		}

		if ( $plugin_name && file_exists( wp_normalize_path( WP_PLUGIN_DIR . '/' . $plugin_name ) ) ) {
			error_log( print_r( "Плагин $plugin_name отключён и будет включён", true ) );
			$result = activate_plugin( $plugin_name );
			if ( is_wp_error( $result ) ) {
				error_log( print_r( $result->get_error_message(), true ) );
			}

			return;
		}

		// Создаем объект апгрейдера плагинов
		$upgrader = new Plugin_Upgrader( new WP_Ajax_Upgrader_Skin() );

		// Загружаем плагин из указанного URL
		$download = download_url( $url );

		if ( is_wp_error( $download ) ) {
			error_log( print_r( "Скачивание $url", true ) );
			error_log( print_r( $download, true ) );

			return;
		}

		// Устанавливаем плагин
		$install_result = $upgrader->install( $download );

		if ( is_wp_error( $install_result ) ) {
			error_log( print_r( "Установка: $url", true ) );
			error_log( print_r( $install_result, true ) );

			return;
		}

		// Активируем плагин, если установка прошла успешно
		$plugin_basename = $upgrader->plugin_info();
		if ( $plugin_basename ) {
			activate_plugin( $plugin_basename );
		}

		// Удаляем временный файл загрузки
		@unlink( $download );

		// Выводим сообщение об успешной установке и активации
		error_log( print_r( 'Плагин успешно установлен и активирован!', true ) );
	}

	public static function insert_page_check_slug( $postarr, $wp_error = true, $fire_after_hooks = true ) {
		$slug = $postarr['post_name'] ?? '';

		if ( ! $slug ) {
			return null;
		}

		if ( get_page_by_path( $slug ) ) {
			return null;
		}

		$default = [
			'post_title'   => '',
			'post_name'    => '',
			'post_content' => '',
			'post_status'  => 'publish',
			'post_type'    => 'page',
		];

		if ( isset( $postarr['template'] ) ) {
			$default['meta_input'] = [
				/* @see add_templates_to_dropdown() */
				'_wp_page_template' => $postarr['template'],
			];
		}

		$post_id = wp_insert_post( array_merge( $default, $postarr ), $wp_error, $fire_after_hooks );

		if ( is_wp_error( $post_id ) ) {
			error_log( print_r( $post_id, true ) );
		}

		return $post_id;
	}

}

