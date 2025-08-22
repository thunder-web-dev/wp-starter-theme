<?php
/**
 * @since 1.1 Поддержка указания почты (1/нескольких) в дочернем классе.
 * @since 1.2 Поддержка получения html формы по AJAX через REST.
 */

namespace CustomTheme\Form;

use WP_REST_Request;

abstract class Form_Base {

	protected string $route_namespace = 'api/site-form';

	protected string $form_key = '';
	protected string $form_name = '';
	protected array $form_fields = [];
	protected array $emails = [];
	protected bool $skip_send_email = false;

	/**
	 * Регистрирует действия и фильтры для формы.
	 *
	 * @return void
	 */
	public function init(): void {
		add_action( 'init', function () {
			$this->set_fields();
			$this->set_emails();
		} );

		add_action( 'rest_api_init', [ $this, 'register_rest_route_form' ] );
		add_filter( 'site_form_names', [ $this, 'set_form_info' ] );
		add_action( 'site_form_name', [ $this, 'saved_site_form_name' ] );
		add_action( 'site_form_content', [ $this, 'saved_site_form_content' ] );
	}

	/**
	 * Регистрирует маршрут для приёма данных от пользователя.
	 *
	 * @return void
	 */
	public function register_rest_route_form(): void {
		register_rest_route(
			$this->route_namespace,
			$this->form_key,
			[
				'methods'             => 'POST',
				'permission_callback' => '__return_true',
				'show_in_index'       => false,
				'callback'            => function ( $request ) {
					return $this->rest_callback( $request );
				},
			]
		);

		register_rest_route(
			$this->route_namespace,
			$this->get_rest_route_html(),
			[
				'methods'             => 'GET',
				'permission_callback' => '__return_true',
				'show_in_index'       => false,
				'callback'            => function ( $request ) {
					return $this->rest_callback_html( $request );
				},
			]
		);
	}

	/**
	 * Обрабатывает rest запрос формы (проверяет, сохраняет).
	 *
	 * Здесь описана простая, стандартная процедура для приёма данных от пользователя.
	 * Если нужно что-то более сложное, то опишите этот метод в классе нужной формы.
	 *
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return array|true
	 */
	public function rest_callback( $request ) {
		$data = $this->prepare_request_form_data( $request );

		if ( $data['errors'] ) {
			return $data['errors'];
		}

		$this->send_email( $data['fields'] );
		$this->call_other_providers( $data['fields'] );

		return true;
	}

	/**
	 * Обрабатывает rest запрос для получения html формы.
	 *
	 * В классе формы опишите какой шаблон должен возвращаться.
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return string
	 */
	public function rest_callback_html( $request ): string {
		$slug = $this->get_html_path();

		if ( ! $slug ) {
			return '';
		}

		ob_start();
		ct()->template( $slug, [ 'request' => $request, ] );

		return ob_get_clean();
	}

	private function get_html_path(): string {
		return $this->set_html_path();
	}

	protected function set_html_path(): string {
		return '';
	}

	protected function get_rest_route_html() {
		return $this->form_key . '/html';
	}

	/**
	 * Заполяет основную информацию о формах.
	 *
	 * @param array $forms_info
	 *
	 * @return array
	 */
	public function set_form_info( $forms_info ): array {
		$forms_info[ $this->form_key ] = $this->form_name;

		return $forms_info;
	}

	/**
	 * Получает ссылку на rest маршрут для приёма данных от пользователя.
	 *
	 * @return string
	 */
	public function get_url(): string {
		return rest_url( $this->route_namespace . '/' . $this->form_key );
	}

	/**
	 * Получает ссылку на rest маршрут для получения html формы.
	 *
	 * @return string
	 */
	public function get_url_html(): string {
		return rest_url( $this->route_namespace . '/' . $this->get_rest_route_html() );
	}

	/**
	 * Формирует сообщение об ошибке всей формы.
	 *
	 * @param string $message
	 *
	 * @return array
	 */
	protected function fail_form( $message ): array {
		return [
			'error_form' => $message,
		];
	}

	/**
	 * Формирует сообщение об ошибке в одном или более полях формы.
	 *
	 * @param array $message
	 *
	 * @return array
	 */
	protected function fail_fields( $errors_fields ) {
		return [
			'errors_fields' => $errors_fields,
		];
	}

	/**
	 * Сохраняет форму в базу данных.
	 *
	 * @param array $keys_values_from_user
	 */
	protected function save_form( $keys_values_from_user ) {
		wp_insert_post( [
			'post_type'    => ( new Admin_Side() )->post_type,
			'post_title'   => wp_generate_uuid4(),
			'post_content' => '',
			'post_status'  => 'publish',
			'meta_input'   => $this->get_prepare_meta_all_data( $keys_values_from_user ),
		] );
	}

	/**
	 * Отправляет форму по почте.
	 *
	 * @param array $keys_values_from_user
	 */
	protected function send_email( $keys_values_from_user ) {
		if ( $this->skip_send_email ) {
			return;
		}
		$domain  = parse_url( home_url() )['host'];
		$emails  = $this->get_emails();
		$subject = "Form: $this->form_name";
		$headers = [
			"From: E-mail logger <no-replay@$domain>",
			'content-type: text/html',
		];
		$message = '';
		$metas   = $this->get_prepare_meta_all_data( $keys_values_from_user );

		// Удалим фильтры, которые могут изменять заголовок $headers
		remove_all_filters( 'wp_mail_from' );
		remove_all_filters( 'wp_mail_from_name' );

		$bg_user_data = 'rgba(82,149,42,0.2)';
		$bg_tech_data = 'rgb(124,148,241,0.2)';

		// Шаблон контента письма
		$message .= '<style type="text/css">table, th, td {border:1px solid #a9a9a9;border-collapse:collapse;padding:5px}</style>';
		foreach ( $metas as $key => $value ) {
			$message .= sprintf(
				'<tr style="background-color: %s;"><td>%s</td><td>%s</td></tr>',
				str_contains( $key, $this->get_meta_prefix_user_data() ) ? $bg_user_data : $bg_tech_data,
				$this->get_field_name( $key ),
				print_r( $value, true )
			);
		}
		$message = sprintf( '<table>%s</table>', $message );
		$message .= sprintf( '
			<table style="margin-top: 20px;">
				<tr>
					<td colspan="2" style="text-align: center;">Легенда</td>
				</tr>
				<tr>
					<td style="background-color: %s;">Пользовательские данные</td>
					<td style="background-color: %s;">Технические данные</td>
				</tr>
			</table>
			',
			$bg_user_data,
			$bg_tech_data
		);

		foreach ( $emails as $email ) {
			wp_mail( $email, $subject, $message, $headers );
		}
	}

	protected function call_other_providers( array $fields ): void {
	}

	protected function get_emails(): array {
		if ( $this->emails ) {
			return $this->emails;
		}

		return [ get_option( 'admin_email' ) ];
	}

	protected function get_prepare_meta_all_data( $keys_values_from_user ) {
		return array_merge(
			$this->get_prepare_meta_user_data( $keys_values_from_user ),
			$this->get_prepare_meta_tech_data()
		);
	}

	protected function get_prepare_meta_tech_data() {
		$metas = [
			'form_name'    => $this->form_name,
			'form_key'     => $this->form_key,
			'page_referer' => $this->get_http_referer(),
			'user_ip'      => $_SERVER['REMOTE_ADDR'] ?? '',
		];

		return $this->get_prepare_meta_key(
			$metas,
			$this->get_meta_prefix_tech_data()
		);
	}

	protected function get_prepare_meta_user_data( $keys_values ) {
		return $this->get_prepare_meta_key(
			$keys_values,
			$this->get_meta_prefix_user_data()
		);
	}

	protected function get_prepare_meta_key( $keys_values, $prefix ) {
		$items = [];

		foreach ( $keys_values as $key => $item ) {
			$items[ $prefix . $key ] = $item;
		}

		return $items;
	}

	protected function get_meta_prefix_user_data() {
		return 'site_form__user_data__';
	}

	protected function get_meta_prefix_tech_data() {
		return 'site_form__tech_data__';
	}

	/**
	 * Получает ссылку на страницу, с которой была отправлена форма.
	 *
	 * @return string
	 */
	protected function get_http_referer() {
		if ( ! empty( $_SERVER['HTTP_REFERER'] ) ) {
			return wp_unslash( $_SERVER['HTTP_REFERER'] );
		}

		return '';
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return array
	 */
	protected function prepare_request_form_data( WP_REST_Request $request ): array {
		$data = [
			'fields' => [],
			'errors' => [],
		];

		foreach ( $this->get_user_fields() as $field ) {
			$key = $field['key'];

			if ( isset( $field['callback_prepare'] ) && is_callable( $field['callback_prepare'] ) ) {
				$value = call_user_func( $field['callback_prepare'], $request );
			} else {
				$value = trim( $request->get_param( $key ) );
			}

			$data['fields'][ $key ] = $value;

			if ( ! $value && $field['required'] ) {
				$data['errors'][] = [
					'name'  => $key,
					'error' => 'Field required',
				];

				continue;
			}

			if ( $value && $field['validate_as'] === 'email' ) {
				if ( ! is_email( $value ) ) {
					$data['errors'][] = [
						'name'  => $key,
						'error' => 'Wrong email',
					];

					continue;
				}
			}

			if ( $value && $field['validate_as'] === 'url' ) {
				if ( empty( parse_url( $value )['host'] ) ) {
					$data['errors'][] = [
						'name'  => $key,
						'error' => 'Wrong url',
					];

					continue;
				}
			}
		}

		if ( ! array_filter( $data['fields'] ) ) {
			return array_merge( $data, [ 'errors' => $this->fail_form( 'Заполните форму!' ) ] );
		}

		if ( $data['errors'] ) {
			return array_merge( $data, [ 'errors' => $this->fail_fields( $data['errors'] ) ] );
		}

		return $data;
	}

	/**
	 * Получает список полей формы.
	 *
	 * @return array
	 */
	protected function get_user_fields(): array {
		$default_values = [
			'key'              => '',
			'name'             => '',
			'required'         => false,
			'validate_as'      => '',
			'callback_prepare' => '',
		];

		foreach ( $this->form_fields as & $form_field ) {
			$form_field = wp_parse_args( $form_field, $default_values );
		}

		return $this->form_fields;
	}

	protected function get_tech_fields(): array {
		return [
			[
				'key'  => 'form_name',
				'name' => 'Имя форма',
			],
			[
				'key'  => 'form_key',
				'name' => 'Ключ форма',
			],
			[
				'key'  => 'page_referer',
				'name' => 'Страница-источник',
			],
			[
				'key'  => 'user_ip',
				'name' => 'IP пользователя',
			],
		];
	}

	protected function get_form_meta( $key, $post_id ) {
		return get_post_meta( $post_id, "{$this->get_meta_prefix_user_data()}_$key", true );
	}

	protected function is_saved_form_by_id( $post_id ) {
		$meta_key = "{$this->get_meta_prefix_tech_data()}form_key";
		$form_key = get_post_meta( $post_id, $meta_key, true );

		return $form_key === $this->form_key;
	}

	/**
	 * Получает название формы.
	 *
	 * @param int $post_id
	 *
	 * @return void
	 */
	public function saved_site_form_name( $post_id ): void {
		echo $this->is_saved_form_by_id( $post_id ) ? $this->form_name : '';
	}

	/**
	 * Выводит на экран контент сохранённой формы.
	 *
	 * @param int $post_id
	 */
	public function saved_site_form_content( $post_id ) {
		if ( ! $this->is_saved_form_by_id( $post_id ) ) {
			return;
		}

		$metas = get_post_meta( $post_id, null, true );

		$metas = array_map( static function ( $meta ) {
			return $meta[0];
		}, $metas );

		if ( ! $metas ) {
			return;
		}

		// Данные от пользователя
		$metas_user = array_filter( $metas, function ( $meta ) {
			return str_contains( $meta, $this->get_meta_prefix_user_data() );
		}, ARRAY_FILTER_USE_KEY );

		// Технические данные
		$metas_tech = array_filter( $metas, function ( $meta ) {
			return str_contains( $meta, $this->get_meta_prefix_tech_data() );
		}, ARRAY_FILTER_USE_KEY );
		?>

		<table class="site_form_content">
			<tr class="site_form_content_line">
				<td class="site_form_content_user"><b>От пользователя</b></td>
				<td class="site_form_content_tech"><b>Технические</b></td>
			</tr>
			<tr class="site_form_content_line">
				<td class="site_form_content_user">
					<table>
						<?php foreach ( $metas_user as $key => $meta ): ?>
							<tr>
								<td><?= $this->get_field_name( $key, 'user' ) ?></td>
								<td><?= esc_html( $meta ) ?></td>
							</tr>
						<?php endforeach; ?>
					</table>
				</td>

				<td class="site_form_content_tech">
					<table>
						<?php foreach ( $metas_tech as $key => $meta ): ?>
							<tr>
								<td><?= $this->get_field_name( $key, 'tech' ) ?></td>
								<td><?= esc_html( $meta ) ?></td>
							</tr>
						<?php endforeach; ?>
					</table>
				</td>
			</tr>

		</table>

		<?php
	}

	public function get_field_name( string $full_key, string $type = '' ) {
		$key    = '';
		$fields = [];

		if ( '' === $type ) {
			$key    = str_replace( [ $this->get_meta_prefix_user_data(), $this->get_meta_prefix_tech_data() ], '', $full_key );
			$fields = array_merge( $this->get_tech_fields(), $this->get_user_fields() );
		}

		if ( 'user' === $type ) {
			$key    = str_replace( $this->get_meta_prefix_user_data(), '', $full_key );
			$fields = $this->get_user_fields();
		}

		if ( 'tech' === $type ) {
			$key    = str_replace( $this->get_meta_prefix_tech_data(), '', $full_key );
			$fields = $this->get_tech_fields();
		}

		foreach ( $fields as $field ) {
			if ( $key === $field['key'] ) {
				return $field['name'];
			}
		}

		return $full_key;
	}

	protected function set_emails(): void {
	}

	/**
	 * @return void
	 */
	abstract public function set_fields(): void;

}
