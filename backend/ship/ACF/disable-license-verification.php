<?php

add_filter( 'pre_option_acf_pro_license_status', function () {
	return [
		'status'                  => 'active',
		'created'                 => 0,
		'expiry'                  => 0,
		'name'                    => 'Developer',
		'lifetime'                => true,
		'refunded'                => false,
		'view_licenses_url'       => 'https://www.advancedcustomfields.com/my-account/view-licenses/',
		'manage_subscription_url' => '',
		'error_msg'               => '',
		'next_check'              => 2359764516, // 13 октября 2044 года, 08:08:36 по всемирному координированному времени (UTC)
		'legacy_multisite'        => true,
	];
} );

add_filter( 'pre_option_acf_pro_license', static function () {
	static $data = '';

	if ( ! $data ) {
		$data = base64_encode( serialize( [
			'key' => base64_encode( 'order_id=166472|type=developer|date=2018-10-19 10:42:21' ), // Генерация фейкового ключа
			'url' => home_url(),
		] ) );

		add_action( 'admin_print_footer_scripts-acf_page_acf-settings-updates', function () {
			$message = '<p>Проверка лицензии запрещена, чтобы можно было использовать PRO возможности ACF.</p>';
			$message .= '<p>Отключить запрет можно, удалив файл <code>' . addslashes( __FILE__ ) . '</code></p>';
			$html    = '<div class="acf-license-status-table">' . $message . '</div>';
			?>
			<script>
				let targetElement = document.querySelector('.acf-license-status-table');
				if (targetElement) {
					targetElement.insertAdjacentHTML('afterend', '<?= $html ?>');
				}
			</script>
			<?php
		} );
	}

	return $data;
} );
