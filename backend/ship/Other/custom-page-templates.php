<?php

add_filter( 'theme_page_templates', 'add_templates_to_dropdown' );

/**
 * Добавляем шаблоны, которые лежат глубже, чем может прочесть движок.
 *
 * @param string[] $templates
 *
 * Данные также могут использоваться в:
 *
 * @see \CustomTheme\Upgrade\Functions_Before
 * @see \CustomTheme\Upgrade\Functions_After
 *
 * @return string[]
 */
function add_templates_to_dropdown( array $templates ): array {
	// Добавление автоматически найденных шаблонов нашей системой к тем шаблонам, что нашёл сам WordPress.
	$templates = array_merge( $templates, find_theme_page_templates() );

	// Выбор шаблона в атрибутах страницы (тут вручную можно указать особые страницы, которые не ищутся автоматически).
	// $templates[ DIR_PATH_TEMPLATES . '/page-example/page-example.php' ] = 'Шаблон страницы "Пример названия"';

	return $templates;
}

/**
 * Находит файлы шаблонов страниц в указанной директории.
 *
 * Чтобы функция корректно находила шаблоны:
 * 1. Файл должен начинаться с префикса 'page-' (или другого, заданного в $template_prefix)
 * 2. В начале файла должен быть PHP-комментарий с указанием шаблона в формате:
 *    `Page Template: Название Шаблона` (или другой header, заданный в $template_header)
 *
 * Пример корректного шаблона:
 * <?php
 * /**
 *  * Page Template: Шаблон страницы "Контакты"
 *  * /
 *
 * @return array Массив найденных файлов с информацией о шаблонах (относительный путь => название шаблона)
 */
function find_theme_page_templates(): array {
	$template_prefix = 'page-';
	$template_header = 'Page Template';

	// Кеш
	static $templates = null;
	if ( ! is_null( $templates ) ) {
		return $templates;
	}
	$templates = [];

	$theme_template_dir_path = wp_normalize_path( get_theme_file_path() . '/' . DIR_PATH_TEMPLATES );
	$theme_dir_path          = wp_normalize_path( get_theme_file_path() );

	// Собираем все файлы
	$items = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator( $theme_template_dir_path, FilesystemIterator::SKIP_DOTS ),
		RecursiveIteratorIterator::SELF_FIRST
	);

	foreach ( $items as $item ) {
		if ( ! $item->isFile() ) {
			continue;
		}

		$filename = $item->getFilename();

		// Только php файлы
		if ( ! str_ends_with( $filename, '.php' ) ) {
			continue;
		}

		// Только с указанным префиксом в имени файла
		if ( strpos( $filename, $template_prefix ) !== 0 ) {
			continue;
		}

		$abs_filepath = $item->getPathname();

		// Получаем заголовки файла с помощью WordPress функции
		$headers = get_file_data( $abs_filepath, [
			'title' => $template_header,
		] );

		// Если есть title, значит это шаблон страницы
		if ( ! empty( $headers['title'] ) ) {
			$abs_filepath = wp_normalize_path( $abs_filepath );
			$rel_filepath = str_replace( $theme_dir_path, '', $abs_filepath );

			$templates[ $rel_filepath ] = $headers['title'];
		}
	}

	return $templates;
}
