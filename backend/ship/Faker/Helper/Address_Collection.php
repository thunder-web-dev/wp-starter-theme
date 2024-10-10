<?php
namespace CustomTheme\Faker\Helper;

use CustomTheme\Faker\Main;

class Address_Collection extends Main {

	public function get_random_company_full_address() {
		$data = $this->get_random_company_address();

		return $data['legal_address'];
	}

	// Новый СУКА-метод для получения ОТДЕЛЬНОГО городов
	public function get_random_company_city() {
		$data = $this->get_random_company_address();

		return $data['city'];
	}

	public function get_random_company_address(): array {
		$contry  = 'Россия';
		$regions = [ 'Центральный', 'Северо-Западный', 'Южный', 'Сибирский', 'Дальневосточный' ];
		$cities  = [ 'Москва', 'Санкт-Петербург', 'Новосибирск', 'Екатеринбург', 'Нижний Новгород' ];
		$streets = [ 'ул. Ленина', 'ул. Гагарина', 'пр. Мира', 'ул. Пушкина', 'ул. Суворова' ];

		$region   = $regions[ array_rand( $regions ) ];
		$city     = $cities[ array_rand( $cities ) ];
		$postcode = random_int( 100000, 999999 );
		$address  = $streets[ array_rand( $streets ) ] . ', д. ' . random_int( 1, 200 );
		$address  = "$contry, $region, $city, $address";

		return [
			'postcode'      => $postcode,
			'contry'        => $contry,
			'region'        => $region,
			'city'          => $city,
			'legal_address' => $address,
		];
	}

}
