<?php
/**
 * Пример формы.
 *
 * Получить url для REST запроса (отправки форма):
 * <?= ( new CustomTheme\Form\Form_Example() )->get_url() ?>
 *
 * Получить url для REST запроса html формы (при указании пути к шаблону в методе set_html_path()):
 * <?= ( new CustomTheme\Form\Form_Example() )->get_url_html() ?>
 */

namespace CustomTheme\Form;

( new Form_Example() )->init();

class Form_Example extends Form_Base {

	protected string $form_key = 'form-example';
	protected string $form_name = 'Тестовая форма';

	/**
	 * Устанавливает данные для полей формы.
	 *
	 * Обязательный метод, так как без указания полей форма не имеет смысла.
	 *
	 * @return void
	 */
	public function set_fields(): void {
		$this->form_fields = [
			[
				'key'      => 'user_name',
				'name'     => 'Имя пользователя',
				'required' => true,
			],
			[
				'key'         => 'user_email',
				'name'        => 'E-mail',
				'required'    => true,
				'validate_as' => 'email',
			],
			[
				'key'      => 'user_phone',
				'name'     => 'Номер телефона',
				'required' => true,
			],
			[
				'key'  => 'company_name',
				'name' => 'Наименование компании',
			],
		];
	}

	/**
	 * Указываем, что делать при отправке формы.
	 *
	 * Необязательный метод.
	 * Если не определить, то форма отправит письмо на почту, и скрипт завершит работу.
	 *
	 * Метод save() встроенный, остальные по потребности дописываются тут (в классе формы).
	 *
	 * @param array $fields
	 */
	protected function call_other_providers( array $fields ): void {
		$this->save_form( $fields );
	}

	/**
	 * Указываем на какую почту (или несколько) отправлять письмо.
	 *
	 * Необязательный метод.
	 * Если не определить, то письмо будет отправлено админ-почту сайта -> get_option('admin_email').
	 *
	 * @param void
	 */
	protected function set_emails(): void {
		$this->emails = [ 'example@site.test' ];
	}

	/**
	 * Указываем путь к шаблону формы относительно папки templates.
	 *
	 * Необязательный метод.
	 * Это нужно, если затем по REST требуется получить форму и вставить на страницу (механизм борьбы со спамом).
	 * Вывести url в шаблоне можно так: <?= ( new CustomTheme\Form\Form_Example() )->get_url_html() ?>
	 *
	 * @return string
	 */
	protected function set_html_path(): string {
		return '/folder/feedback.php';
	}

}
