<?php
/**
 * @since 1.1 Проверка наличия модуля CSV и его запуск.
 * @since 1.2 Добавлены хуки site_form_before_content и site_form_after_content.
 * @since 1.3 Добавлены хуки site_form_before_styles и site_form_after_styles.
 * @since 1.4 Добавлен хук site_form_admin_table_new_columns.
 * @since 1.5 Добавлен хук site_form_admin_table_meta_query.
 */

namespace CustomTheme\Form;

class Admin_Side {
	public string $post_type = 'site-form';

	public function hooks() {
		add_action( 'init', [ $this, 'register_post_type' ], 11 );

		add_filter( 'manage_' . $this->post_type . '_posts_columns', [ $this, 'register_columns' ], 11 );
		add_action( 'manage_' . $this->post_type . '_posts_custom_column', [ $this, 'fill_columns' ], 5, 2 );
		add_filter( 'post_date_column_status', [ $this, 'remove_date_column_post_status' ], 10, 2 );
		add_action( 'admin_print_footer_scripts', [ $this, 'table_styles' ] );

		add_action( 'restrict_manage_posts', [ $this, 'add_table_dropdown_forms' ] );
		add_action( 'manage_posts_extra_tablenav', [ $this, 'add_btn_download_csv' ] );
		add_action( 'pre_get_posts', [ $this, 'add_table_filter_forms' ] );

		add_action( 'admin_menu', function () {
			remove_submenu_page(
				"edit.php?post_type={$this->post_type}",
				"post-new.php?post_type={$this->post_type}"
			);
		}, 11 );

		add_filter( 'bulk_actions-edit-' . $this->post_type, [ $this, 'custom_bulk_actions' ] );
		add_filter( 'post_row_actions', [ $this, 'custom_post_row_actions' ], 10, 2 );
	}

	public function register_post_type() {
		register_post_type( $this->post_type, [
			'labels'            => [
				'name'      => 'Формы',
				'not_found' => 'Форм не найдено',
			],
			'public'            => false,
			'hierarchical'      => false,
			'show_ui'           => true,
			'show_in_admin_bar' => false,
			'menu_position'     => 20,
			'supports'          => [ 'title' ],
			'menu_icon'         => 'dashicons-forms',
			'posts_per_page'    => 100,
		] );
	}

	/**
	 * Регистрирует колонки для таблицы с формами в админке.
	 *
	 * @param string[] $columns
	 *
	 * @return string[]
	 */
	public function register_columns( $columns ) {
		unset( $columns['title'] );
		$num = count( $columns ) - 1;

		$new_columns = [
			'form_id'      => 'ID письма',
			'form_name'    => 'Название формы',
			'form_content' => 'Сохраненные данные',
		];

		$new_columns = apply_filters( 'site_form_admin_table_new_columns', $new_columns );

		return array_slice( $columns, 0, $num ) + $new_columns + array_slice( $columns, $num );
	}

	/**
	 * Заполняет контентом колонки таблицы с формами в админке.
	 *
	 * @param string $colname
	 * @param int    $post_id
	 */
	public function fill_columns( $colname, $post_id ) {
		if ( $colname === 'form_id' ) {
			echo (int) $post_id;
		}

		if ( $colname === 'form_name' ) {
			do_action( 'site_form_name', $post_id );
		}

		do_action( 'site_form_before_content', $colname, $post_id );

		if ( $colname === 'form_content' ) {
			do_action( 'site_form_content', $post_id );
		}

		do_action( 'site_form_after_content', $colname, $post_id );
	}

	/**
	 * Добавляет выпадающие списки-фильтры для таблицы с формами.
	 *
	 * @param string $post_type
	 */
	public function add_table_dropdown_forms( $post_type ) {
		if ( $post_type !== $this->post_type ) {
			return;
		}

		$key_form   = filter_input( INPUT_GET, 'site_form_name' );
		$form_names = [ '- Все формы -' ] + $this->get_site_forms();
		?>

		<label>
			<select name="site_form_name">
				<?php foreach ( $form_names as $key => $name ): ?>
					<?php printf( '<option value="%s"%s>%s</option>', $key, selected( $key, $key_form, false ), $name ); ?>
				<?php endforeach; ?>
			</select>
		</label>

		<?php
	}

	/**
	 * Добавляет кнопку "Скачать CSV" в таблицу с формами.
	 */
	public function add_btn_download_csv() {
		if ( $this->is_csv_module_enable() && have_posts() && $this->is_page_admin_table() ) {
			echo '<input type="submit" name="download-csv" class="button" value="Скачать CSV">';
		}
	}

	/**
	 * Проверяет, является ли текущая страница со списком сохранённых форм.
	 *
	 * @return bool
	 */
	private function is_page_admin_table() {
		global $current_screen;;

		if ( ! is_admin() ) {
			return false;
		}

		$_post_type = $current_screen->post_type ?? null;
		$_id        = $current_screen->id ?? null;

		return $_post_type === $this->post_type || $_id === "edit-$this->post_type";
	}

	/**
	 * Изменяет запрос для таблицы с формами.
	 *
	 * @param \WP_Query $query
	 */
	public function add_table_filter_forms( $query ) {
		if ( ! $this->is_page_admin_table() ) {
			return;
		}

		$meta_query = [];

		if ( $key_form = filter_input( INPUT_GET, 'site_form_name' ) ) {
			$meta_query[] = [
				'key'   => 'site_form__tech_data__form_key',
				'value' => $key_form,
			];
		}

		$meta_query = apply_filters( 'site_form_admin_table_meta_query', $meta_query, $query );

		if ( $meta_query ) {
			$query->set( 'meta_query', $meta_query );
		}

		if ( $this->is_csv_module_enable() && filter_input( INPUT_GET, 'download-csv' ) ) {
			$this->download_form_csv( $query, $key_form );
		}
	}

	/**
	 * Добавляет на страницу с таблицей стили.
	 *
	 * @return void
	 */
	public function table_styles() {
		if ( ! $this->is_page_admin_table() ) {
			return;
		}

		do_action( 'site_form_before_styles', $this->post_type );
		?>

		<style>
			.post-type-<?= $this->post_type ?> .site_form_content {
				width: 100%;
			}

			.post-type-<?= $this->post_type ?> .site_form_content_line {
				display: flex;
				justify-content: space-between;
			}

			.post-type-<?= $this->post_type ?> .site_form_content_tech {
				opacity: 0.3;
			}

			.post-type-<?= $this->post_type ?> .site_form_content td,
			.post-type-<?= $this->post_type ?> .site_form_content th {
				padding: 0 5px !important;
			}

			.post-type-<?= $this->post_type ?> .page-title-action {
				display: none;
			}

			.post-type-<?= $this->post_type ?> .column-form_id {
				width: 70px;
			}

			.post-type-<?= $this->post_type ?> .column-form_name {
				width: 150px;
			}
		</style>

		<?php
		do_action( 'site_form_after_styles', $this->post_type );
	}

	/**
	 * Получает название формы по её ключу.
	 *
	 * @param string $form_key
	 *
	 * @return string
	 */
	private function get_name_form( $form_key ) {
		return $this->get_site_forms()[ $form_key ] ?? 'Форма не определена';
	}

	/**
	 * Убирает статус записи из колонки "Дата".
	 *
	 * @param string   $status
	 * @param \WP_Post $post
	 *
	 * @return string
	 */
	public function remove_date_column_post_status( $status, $post ) {
		return $this->post_type == $post->post_type ? '' : $status;
	}

	/**
	 * Получает список форм в формате ключ-название.
	 *
	 * @return string[]
	 */
	public function get_site_forms() {
		return apply_filters( 'site_form_names', [] );
	}

	public function get_site_form_content() {
		return apply_filters( 'site_form_content', [] );
	}

	/**
	 * Перехватывает запрос на сохранение CSV файла и делает это.
	 *
	 * @param \WP_Query $query
	 */
	public function download_form_csv( $query, $key_form ) {
		$query->set( 'posts_per_page', - 1 );

		add_action( 'wp', function () use ( $query, $key_form ) {
			$meta_keys = function () use ( $key_form ) {
				global $wpdb;

				$where = $key_form ? "pm.meta_key = '$key_form'" : '1=1';

				$meta_keys = $wpdb->get_col( "
					SELECT DISTINCT pm.meta_key
					FROM {$wpdb->postmeta} pm
					LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
					WHERE $where
					AND p.post_type = '$this->post_type'
					AND p.post_status = 'publish'
				" );

				if ( $meta_keys ) {
					return array_map( static function ( $key ) {
						$parts = explode( '__', $key );

						return [
							'key'   => $key,
							'title' => end( $parts ),
						];
					}, $meta_keys );
				}

				return $meta_keys;
			};

			$hash = substr( md5( serialize( $query->query_vars ) ), 0, 10 );
			$file = sprintf( 'form__%s__%s_%s.csv', $key_form ?: 'all', $hash, current_time( 'd-m-Y' ) );

			if ( ! have_posts() ) {
				$text = [
					'<p>Форм для экспорта не найдено.</p>',
					sprintf(
						'<p><a href="%s">Вернуться обратно к списку форм</a></p>',
						admin_url( "edit.php?post_type=$this->post_type" )
					),
				];

				wp_die( implode( '', $text ) );
			}

			$meta_keys = $meta_keys();

			// Первым элементом идёт массив с заголовками будущей таблицы
			$list[] = array_merge(
				[ 'form_id', 'date' ],
				wp_list_pluck( $meta_keys, 'title' )
			);

			// Заполняем таблицу данными
			while ( have_posts() ) {
				the_post();

				$data = [ get_the_ID(), get_the_date( 'd-m-Y H:i:s' ) ];

				foreach ( wp_list_pluck( $meta_keys, 'key' ) as $key ) {
					$data[] = get_post_meta( get_the_ID(), $key, true );
				}

				$list[] = $data;
			}

			header( 'Content-Type: text/csv; charset=utf-8' );
			header( sprintf( 'Content-Disposition: attachment; filename="%s"', $file ) );

			echo \kama_create_csv_file( $list );

			die();
		} );
	}

	protected function is_csv_module_enable() {
		return function_exists( 'kama_create_csv_file' );
	}

	/**
	 * Фильтрует групповые действия в таблице записей.
	 *
	 * @param array $actions Массив групповых действий.
	 *
	 * @return array Отфильтрованный массив групповых действий.
	 */
	public function custom_bulk_actions( array $actions ): array {
		unset( $actions['edit'] );

		return $actions;
	}

	/**
	 * Фильтрует действия в строке таблицы записей.
	 *
	 * @param array    $actions Массив действий.
	 * @param \WP_Post $post    Объект записи.
	 *
	 * @return array Отфильтрованный массив действий.
	 */
	public function custom_post_row_actions( array $actions, \WP_Post $post ): array {
		if ( $post->post_type === $this->post_type && isset( $actions['trash'] ) ) {
			return [ 'trash' => $actions['trash'] ];
		}

		return $actions;
	}

}

( new Admin_Side() )->hooks();
