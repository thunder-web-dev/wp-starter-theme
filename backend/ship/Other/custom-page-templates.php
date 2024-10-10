<?php
/**
 * Добавляем шаблоны, которые лежат глубже, чем может прочесть движок.
 *
 * @param string[] $templates
 *
 * Данные также используются в:
 *
 * @return string[]
 * @see \CustomTheme\Upgrade\Functions_Before
 *
 */
function add_templates_to_dropdown( $templates ) {
	// Выбор шаблона в атрибутах
	// $templates['templates/page-history-risc-v/page-history-risc-v.php']             = 'Шаблон страницы "История RISC-V"';

	return $templates;
}

add_filter( 'theme_page_templates', 'add_templates_to_dropdown' );
