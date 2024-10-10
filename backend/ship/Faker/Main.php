<?php

namespace CustomTheme\Faker;

use WP_CLI;
use WP_CLI\Utils;

/**
 * @links https://testfile.org
 * @links https://filesamples.com
 * @links https://samplelib.com/ru/
 */
class Main {

	protected $action;

	public function __construct() {
		WP_CLI::add_command( 'dev post delete', [ $this, 'delete_posts' ] );
	}

	protected function set_user( $user_id = 1 ) {
		wp_set_current_user( $user_id );
		define( 'ALLOW_UNFILTERED_UPLOADS', true );
	}

	/**
	 * Удаляет записи указанного типа.
	 *
	 * ## OPTIONS
	 *
	 * <ctp_name>
	 * : Название CTP, например post, page, product и так далее.
	 *
	 * [--yes]
	 * : Подтверждает удаление без запроса подтверждения.
	 *
	 * ## EXAMPLES
	 *     wp dev post delete {name-cpt} [--all]
	 *     wp dev post delete course
	 *     wp dev post delete course --yes
	 */
	public function delete_posts( $args, $assoc_args ) {
		$ctp = $args[0] ?? '';

		if ( ! $ctp ) {
			$this->error( 'CTP не указана!' );
		}

		$post_ids = get_posts( [
			'post_type'   => $ctp,
			'post_status' => 'any',
			'numberposts' => - 1,
			'fields'      => 'ids',
		] );
		$count    = count( $post_ids );

		if ( $post_ids ) {
			WP_CLI::confirm( "Вы уверены, что хотите удалить все записи с типом $ctp? Их $count шт.", $assoc_args );

			$progress = Utils\make_progress_bar( 'Удаление...', $count, 1 );
			foreach ( $post_ids as $post_id ) {
				wp_delete_post( $post_id, true );
				$progress->tick();
			}
			$progress->finish();
		} else {
			$this->error( 'Записей с типом $ctp нет. Удалять нечего!' );
		}
	}

	/**
	 * Возвращает рандомные ID записей указанного типа для ACF поля-relationship.
	 * Предусмотрено кеширование.
	 *
	 * @param int   $count Максимальное количество возвращаемых ID.
	 * @param array $args  Параметры запроса для get_posts().
	 *
	 * @return int[]|string
	 */
	function get_random_post_ids( $count, $args, $for_acf = true ) {
		$args = array_merge( [
			'numberposts' => - 1,
			'fields'      => 'ids',
		], $args );

		$cache_key   = md5( maybe_serialize( $args ) );
		$cache_group = 'random_item_ids_query';
		$ids         = wp_cache_get( $cache_key, $cache_group );

		if ( false === $ids ) {
			$ids = get_posts( $args );
			wp_cache_set( $cache_key, $ids, $cache_group );
		}

		if ( ! $ids && ! is_array( $ids ) ) {
			return '';
		}

		shuffle( $ids );

		if ( $for_acf ) {
			$ids = array_map( 'strval', $ids );
		}

		return array_slice( $ids, 0, $count );
	}

	/**
	 * Возвращает рандомное значение из списка переданных.
	 *
	 * @param array $items Список, например ['base', 'advanced', 'master'].
	 *
	 * @return mixed Рандомный значение из списка, например advanced.
	 */
	public function get_random_item( $items ) {
		shuffle( $items );

		return $items[0] ?? null;
	}

	/**
	 * Возвращает рандомные значения из списка переданных.
	 *
	 * @param int   $count Максимальное количество возвращаемых значений.
	 * @param array $items Список, например ['base', 'advanced', 'master'].
	 *
	 * @return array Рандомные значения списка, например ['base', 'master'].
	 */
	public function get_random_items( $count, $items ) {
		shuffle( $items );

		return array_slice( $items, 0, $count );
	}

	/**
	 * Возвращает все метаполя указаной записи.
	 *
	 * @param int $post_id
	 *
	 * @return \stdClass[]
	 */
	public function get_post_all_meta( $post_id ) {
		global $wpdb;

		$items = $wpdb->get_results( "SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id" );
		$metas = [];

		foreach ( $items as $item ) {
			$metas[ $item->meta_key ] = $item->meta_value;
		}

		return $metas;
	}

	/**
	 * Возвращает оригинальную запись для клонирования.
	 * Оригинальная запись - это самая первая создання запись указанного типа поста,
	 * поэтому чтобы уверенно её определить, указывайте ей при создании "маленькую" дату, к примеру, 1900 год.
	 *
	 * @param array $args
	 *
	 * @return \WP_Post|null
	 */
	public function get_post_for_clone( $args ) {
		$args = array_merge( [
			'numberposts' => 1,
			'order'       => 'ASC',
		], $args );

		return get_posts( $args )[0] ?? null;
	}

	/**
	 * Возвращает рандомную дату в формате "Y-m-d H:i:s" из промежутка дат.
	 *
	 * Указывать начало и конец надо в формате "Y-m-d",
	 * например "2009-01-31" (год-месяц-день, 31 января 2009 года).
	 *
	 * @param string $start
	 * @param string $end
	 * @param bool   $full
	 *
	 * @return array|string
	 * @throws \Exception
	 */
	public function get_random_date( string $start, string $end, bool $full = false ) {
		$timestamp = random_int( strtotime( $start ), strtotime( $end ) );

		if ( $full ) {
			return [
				'wp'   => date( "Y-m-d H:i:s", $timestamp ),
				'acf'  => date( "Ymd", $timestamp ),
				'unix' => $timestamp,
			];
		}

		return date( "Y-m-d H:i:s", $timestamp );
	}

	public function generate_random_content( $numParagraphs = 3 ) {
		$text = '';

		// Функция для генерации случайного слова
		$generateRandomWord = static function ( $length = null ) {
			$length     = $length ?? random_int( 3, 12 );
			$characters = 'abcdefghijklmnopqrstuvwxyz';
			$word       = '';
			for ( $i = 0; $i < $length; $i ++ ) {
				$word .= $characters[ random_int( 0, strlen( $characters ) - 1 ) ];
			}

			return $word;
		};

		// Функция для генерации случайного предложения
		$generateRandomSentence = function ( $numWords = null ) use ( $generateRandomWord ) {
			$numWords = $numWords ?? random_int( 5, 15 );
			$sentence = [];

			for ( $i = 0; $i < $numWords; $i ++ ) {
				$sentence[] = $generateRandomWord();
			}

			// Превращаем массив слов в строку и делаем первую букву заглавной
			$sentence[0] = ucfirst( $sentence[0] );

			return implode( ' ', $sentence ) . '.';
		};

		// Генерация абзацев
		for ( $i = 0; $i < $numParagraphs; $i ++ ) {
			$numSentences = random_int( 3, 7 );
			$paragraph    = '';

			for ( $j = 0; $j < $numSentences; $j ++ ) {
				$paragraph .= $generateRandomSentence() . ' ';
			}

			// Убираем лишний пробел в конце абзаца и добавляем перенос строки
			$text .= trim( $paragraph ) . "\n\n";
		}

		return trim( $text );
	}


	/**
	 * Загружает файл в медиабиблиотеку, если её там ещё нет.
	 *
	 * @param $file_url
	 *
	 * @return bool|int|string|\WP_Error
	 */
	protected function upload_file_to_media_library( string $file_url, $is_local = false ) {
		add_filter( 'http_request_args', [ $this, 'add_auth_header_to_request' ], 10, 2 );

		// Получение информации о файле
		$file_url  = $is_local ? $this->get_theme_file_uri( $file_url ) : $file_url;
		$file_info = pathinfo( $file_url );
		$file_name = $file_info['basename'];

		// Поиск файла в медиабиблиотеке
		$posts   = get_posts( [
			'post_type'  => 'attachment',
			'meta_query' => [
				[
					'key'     => '_wp_attached_file',
					'value'   => $file_name,
					'compare' => 'LIKE',
				],
			],
		] );
		$post_id = $posts[0]->ID ?? null;

		// Если файл уже загружен, возвращаем его ID
		if ( $post_id ) {
			return $post_id;
		}

		// Путь к файлу во временной папке WordPress
		$tmp_name = download_url( $file_url, false );

		if ( is_wp_error( $tmp_name ) ) {
			return $tmp_name;
		}

		// Массив данных для файла
		$file = [
			'name'     => $file_name,
			'type'     => mime_content_type( $tmp_name ),
			'tmp_name' => $tmp_name,
			'error'    => 0,
			'size'     => filesize( $tmp_name ),
		];

		// Проверка на допустимость типа файла
		$overrides = [
			'test_form' => false,
			'test_type' => false,
		];

		// Загружаем файл в медиабиблиотеку
		$attachment_id = @media_handle_sideload( $file, 0 );

		// Если произошла ошибка при загрузке, удаляем временный файл и возвращаем ошибку
		if ( is_wp_error( $attachment_id ) ) {
			@unlink( $tmp_name );

			return $attachment_id;
		}

		return $attachment_id;
	}

	protected function upload_files_to_media_library( array $files_url, bool $is_dev = false ): array {
		$ids = [];

		foreach ( $files_url as $file_url ) {
			$result = $this->upload_file_to_media_library( $file_url );

			if ( $result && is_int( $result ) ) {
				$ids[] = $result;
			}
		}

		return $ids;
	}

	protected function get_theme_file_uri( $path ): string {
		$url = get_theme_file_uri( $path );
		$url = is_dev_site() ? str_replace( 'https:', 'http:', $url ) : str_replace( 'http:', 'https:', $url );

		return apply_filters( 'ct_theme_file_uri', $url, is_dev_site() );
	}

	public function add_auth_header_to_request( $args, $url ) {
		if ( ! is_dev_site() && str_contains( $url, 'static-files' ) ) {
			$args['headers']['Authorization'] = 'Basic ' . base64_encode( 'campusboy:zFexn4~>Al' );
		}

		return $args;
	}

	protected function set_timer() {
		return microtime( true );
	}

	protected function get_timer( $start, $text = 'Время выполнения операции: ', $action = '' ) {
		$_start = microtime( true );

		$this->log( $text . number_format_i18n( $_start - $start, 2 ) . ' сек', $action );

		return $_start;
	}

	protected function show_main_timer( $start, $text = 'Общее время выполнения всех операций: ', $action = '' ) {
		return $this->get_timer( $start, $text, $action );
	}

	protected function log( $text = '', $action = '' ): void {
		$action = $action ?: $this->action;

		WP_CLI::log( $action ? "$action | $text" : $text );
	}

	protected function error( $text ): void {
		$this->log( $text, $this->action );
		exit();
	}

}
