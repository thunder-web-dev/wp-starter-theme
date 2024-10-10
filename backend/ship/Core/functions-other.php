<?php

// Задаёт основные настройки темы.
add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
} );

/**
 * Проверяет, разрабатывается ли сайт на локалке или нет.
 *
 * Сработает, если указан один из вариантов константы в файле wp-config.php:
 * define( 'WP_ENVIRONMENT_TYPE', 'local' );
 * define( 'WP_ENVIRONMENT_TYPE', 'development' );
 *
 * @return bool
 */
function is_dev_site(): bool {
	return in_array( wp_get_environment_type(), [ 'local', 'development' ], true );
}

// Добавляет ссылку текущей страницы на проде + GIT
add_action( 'admin_bar_menu', function ( \WP_Admin_Bar $wp_admin_bar ) {
	if ( ! is_dev_site() || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( defined( 'DEV_SITE_URL' ) && DEV_SITE_URL ) {
		$wp_admin_bar->add_node( [
			'id'    => 'current_page_link',
			'title' => '<span class="ab-icon dashicons-visibility" style="top:2px"></span>',
			'href'  => wp_normalize_path( DEV_SITE_URL . '/' . ( $_SERVER['REQUEST_URI'] ?? '' ) ),
			'meta'  => [
				'target' => '_blank',
				'title'  => 'Посмотреть текущую страницу на проде',
			],
		] );
	}

	if ( defined( 'GIT_REPO_URL' ) && GIT_REPO_URL ) {
		$wp_admin_bar->add_node( [
			'id'    => 'git_repo_link',
			'title' => '<span class="ab-icon dashicons-editor-code" style="top:2px"></span>',
			'href'  => GIT_REPO_URL,
			'meta'  => [
				'target' => '_blank',
				'title'  => 'Посмотреть удалённый GIT репозиторий',
			],
		] );
	}
}, 100 );
