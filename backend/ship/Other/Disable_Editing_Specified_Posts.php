<?php

Disable_Editing_Specified_Posts::init(
	[
		// 'remote-laboratories',
		// 'devboards',
		// 'history-risc-v',
		// 'about-risc-v',
	]
);

class Disable_Editing_Specified_Posts {

	private static array $disable_post_names = [];

	public static function init( array $disable_post_names ): void {
		self::$disable_post_names = $disable_post_names;

		add_action( 'current_screen', [ __CLASS__, 'disable_edit_static_pages' ] );
		add_filter( 'display_post_states', [ __CLASS__, 'set_state_for_static_pages' ], 9, 2 );
		add_filter( 'page_row_actions', [ __CLASS__, 'remove_page_row_actions' ], 10, 2 );
	}

	/**
	 * Мониторит открытие статичной страницы на редактирование и выводит предупреждение.
	 */
	public static function disable_edit_static_pages(): void {
		global $pagenow, $typenow;

		if ( 'post.php' === $pagenow && 'page' === $typenow && is_admin() ) {
			$post_id = $_GET['post'] ?? null;

			if ( $post_id && ( $post = get_post( $post_id ) ) ) {
				$text = [
					'<p>Эта страница статичная и не предполагает, чтобы в ней что-либо редактировали.</p>',
					'<p>Если Вам нужно что-то изменить, обратитесь к разработчикам сайта.</p>',
					sprintf( '<p><a href="%s">Вернуться обратно к списку страниц</a></p>', admin_url( 'edit.php?post_type=page' ) ),
				];

				if ( in_array( $post->post_name, self::$disable_post_names, true ) ) {
					wp_die( implode( '', $text ) );
				}
			}
		}
	}

	/**
	 * Добавляет статичным страницам лейбл об этом в таблице странице в админке.
	 */
	public static function set_state_for_static_pages( array $post_states, WP_Post $post ): array {
		if ( in_array( $post->post_name, self::$disable_post_names, true ) ) {
			$post_states[] = 'Редактируется через разработчиков';
		}

		return $post_states;
	}

	/**
	 * Удаляет действие "Изменить" со страницей в списке страниц.
	 */
	public static function remove_page_row_actions( array $actions, WP_Post $post ): array {
		if ( in_array( $post->post_name, self::$disable_post_names, true ) ) {
			unset( $actions['edit'] );
		}

		return $actions;
	}

}
