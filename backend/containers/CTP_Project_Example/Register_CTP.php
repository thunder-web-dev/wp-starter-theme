<?php

namespace CustomTheme\CTP_Project_Example;

class Register_CTP {

	private $post_type = 'project';

	public function init(): void {
		add_action( 'init', [ $this, 'register' ], 11 );
	}

	public function register(): void {
		register_post_type( $this->post_type, [
			'labels'             => [
				'name'                  => 'Проекты',
				'singular_name'         => 'Проект',
				'menu_name'             => 'Проекты',
				'name_admin_bar'        => 'Проект',
				'archives'              => 'Архивы проектов',
				'attributes'            => 'Атрибуты проекта',
				'parent_item_colon'     => 'Родительский проект:',
				'all_items'             => 'Все проекты',
				'add_new_item'          => 'Добавить новый проект',
				'add_new'               => 'Добавить проект',
				'new_item'              => 'Новый проект',
				'edit_item'             => 'Редактировать проект',
				'update_item'           => 'Обновить проект',
				'view_item'             => 'Просмотр проекта',
				'view_items'            => 'Просмотр проектов',
				'search_items'          => 'Поиск проектов',
				'not_found'             => 'Не найдено',
				'not_found_in_trash'    => 'Не найдено в корзине',
				'featured_image'        => 'Изображение проекта',
				'set_featured_image'    => 'Установить изображение проекта',
				'remove_featured_image' => 'Удалить изображение проекта',
				'use_featured_image'    => 'Использовать как изображение проекта',
				'insert_into_item'      => 'Вставить в проект',
				'uploaded_to_this_item' => 'Загружено в этот проект',
				'items_list'            => 'Список проектов',
				'items_list_navigation' => 'Навигация по списку проектов',
				'filter_items_list'     => 'Фильтр списка проектов',
				'title_placeholder'     => 'Введите название проекта здесь',
			],
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => true,
			'capability_type'    => 'post',
			'map_meta_cap'       => true,
			'has_archive'        => trim( $this->post_type, 's' ) . 's',
			'hierarchical'       => false,
			'menu_position'      => null,
			'show_in_rest'       => false,
			'supports'           => [ 'title', 'editor' ],
			'menu_icon'          => 'dashicons-awards',
			'template_item'      => "/templates/ctp-project/single/project-single.php",
			'template_archive'   => '/templates/ctp-project/archive/project-archive.php',
			'posts_per_page'     => - 1,
		] );
	}

}
