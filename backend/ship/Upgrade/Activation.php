<?php

namespace CustomTheme\Upgrade;

class Activation {

	/**
	 * Runs on plugin activation.
	 *
	 * @return array
	 */
	public static function init(): array {
		$res = [];

		$res[] = Upgrade_DB::update_create_tables( Upgrade_DB::get_db_tables_schemas() );

		return $res;
	}

}
