<?php
/***************************************
 * Функции для использования в шаблоне *
 **************************************/

/**
 * Получает заголовок проекта.
 *
 * @param int|WP_Post|null $post
 *
 * @return string
 */
function get_project_title( $post = null ): string {
	return get_the_title( $post );
}

/**
 * Получает ссылку на страницу проекта.
 *
 * @param int|WP_Post|null $post
 *
 * @return string
 */
function get_project_url( $post = null ): string {
	return esc_url( apply_filters( 'the_permalink', get_permalink( $post ), $post ) );
}

/**
 * Получает URL на обложку проекта.
 *
 * @param array            $args
 * @param int|WP_Post|null $post
 *
 * @return string
 */
function get_project_cover_url( array $args, $post = null ): string {
	$image_id = get_field( 'project_cover_image_id', $post ) ?: 0;

	return kama_thumb_src(
		array_merge( [ 'no_stub' => false, ], $args ),
		$image_id ?: 'no_photo'
	);
}

/**
 * Получает URL на миниатюру проекта.
 * Используется как правило в карточке проекта в архиве.
 *
 * @param array            $args
 * @param int|WP_Post|null $post
 *
 * @return string
 */
function get_project_image_url_for_archive( array $args, $post = null ): string {
	$image_id = absint( get_field( 'project_thumb_id', $post ) ?: 0 );

	if ( $image_id ) {
		return kama_thumb_src( array_merge( [ 'no_stub' => false, ], $args ), $image_id );
	}

	return get_project_cover_url( $args, $post );
}

/**
 * Получает короткое описание проекта.
 *
 * @param int|WP_Post|null $post
 *
 * @return string
 */
function get_project_desc( $post = null ): string {
	return get_field( 'project_desc', $post ) ?: '';
}

/**
 * Получает текст о задаче проекта.
 *
 * @param int|WP_Post|null $post
 *
 * @return string
 */
function get_project_task( $post = null ): string {
	return get_field( 'project_task', $post ) ?: '';
}

/**
 * Получает решение задачи проекта.
 *
 * @param int|WP_Post|null $post
 *
 * @return string
 */
function get_project_solution( $post = null ): string {
	return get_field( 'project_solution', $post ) ?: '';
}

/**
 * Получает ссылки на социальные сети (или другое), где рассказывли о проекте.
 *
 * @param int|WP_Post|null $post
 *
 * @return array<int, array{
 *     text: string,
 *     url: string
 * }>
 */
function get_project_social_network_links( $post = null ): array {
	return get_field( 'project_social_network', $post ) ?: [];
}

/**
 * Получает URL на изображение, как выглядел объект проекта ДО (визуализация).
 *
 * @param array            $args
 * @param int|WP_Post|null $post
 *
 * @return string
 */
function get_project_visualization_image_url( array $args, $post = null ): string {
	$image_id = get_field( 'project_visualization_image_id', $post ) ?: 0;

	return kama_thumb_src(
		array_merge( [ 'no_stub' => false, ], $args ),
		$image_id ?: 'no_photo'
	);
}

/**
 * Получает URL на изображение, как выглядит объект проекта ПОСЛЕ (реализация).
 *
 * @param array            $args
 * @param int|WP_Post|null $post
 *
 * @return string
 */
function get_project_implementation_image_url( array $args, $post = null ): string {
	$image_id = get_field( 'project_implementation_image_id', $post ) ?: 0;

	return kama_thumb_src(
		array_merge( [ 'no_stub' => false, ], $args ),
		$image_id ?: 'no_photo'
	);
}

/**
 * Получает URL на фото планировки проекта.
 *
 * @param array            $args
 * @param int|WP_Post|null $post
 *
 * @return string
 */
function get_project_layout_image_url( array $args, $post = null ): string {
	$image_id = get_field( 'project_layout_image_id', $post ) ?: 0;

	return kama_thumb_src(
		array_merge( [ 'no_stub' => false, ], $args ),
		$image_id ?: 'no_photo'
	);
}

/**
 * Получает описание планировки проекта.
 *
 * @param int|WP_Post|null $post
 *
 * @return string
 */
function get_project_layout_desc( $post = null ): string {
	return get_field( 'project_layout_desc', $post ) ?: '';
}

/**
 * Получает слайдер проекта.
 *
 * @param int|WP_Post|null $post
 *
 * @return string[]
 */
function get_project_gallery( $args, $post = null ): array {
	$image_ids = get_field( 'project_gallery', $post ) ?: [];

	$links = array_map( static function ( $image_id ) use ( $args ) {
		return kama_thumb_src(
			array_merge( [ 'no_stub' => true, ], $args ),
			$image_id
		);
	}, $image_ids );

	return array_filter( $links );
}

/**
 * Получает отзыв заказчика о проекте.
 *
 * @param int|WP_Post|null $post
 *
 * @return string
 */
function get_project_feedback( $post = null ): string {
	return get_field( 'project_feedback', $post ) ?: '';
}

/**
 * Получает название (описание) проекта для архива.
 *
 * @param int|WP_Post|null $post
 *
 * @return string
 */
function get_project_desc_for_archive( $post = null ): string {
	return get_field( 'project_desc_for_archive', $post ) ?: '';
}

/**
 * Получает данные для блока "Этапы работы", отображаемый в архиве проектов.
 *
 * @return array<int, array{
 *     title: string,
 *     desc: string
 * }>
 */
function get_project_archive_stages_work(): array {
	return get_field( 'project_archive_stages_work', 'option' ) ?: [];
}
