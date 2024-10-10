<?php

/* @see Upgrade_CLI_Commands */

namespace CustomTheme\Upgrade;

class Upgrade_DB {

	/**
	 * @param array $db_tables_schemas
	 *
	 * @return array Result data.
	 */
	public static function update_create_tables( array $db_tables_schemas ): array {

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$res = [];

		foreach( $db_tables_schemas as $table_name => $table_query ){

			$res['dbDelta'][ $table_name ] = dbDelta( $table_query );

			// почистим значения которые ничего не означают
			foreach( $res['dbDelta'][ $table_name ] as & $val ){
				if( preg_match( '/ to\s*$/', $val ) ){
					$val = '';
				}
			}
		}

		return $res;
	}

	/**
	 * Gets DB tables schemas.
	 *
	 * @return array
	 */
	public static function get_db_tables_schemas(): array{

		// Plugin's all database tables schemas.
		$db_tables_schemas = require __DIR__ . '/db_tables_schemas.php';

		// prepare for dbDelta()
		foreach( $db_tables_schemas as & $schema ){
			$schema = self::prepare_for_dbDelta( $schema );
		}
		unset( $schema );

		return array_filter( $db_tables_schemas );
	}

	/**
	 * prepare table schema for wp dbDelta().
	 *
	 * @param string $table_schema
	 *
	 * @return string
	 */
	private static function prepare_for_dbDelta( string $table_schema ): string{
		global $wpdb;

		$collate_charset_engine = $wpdb->get_charset_collate() . ' ENGINE InnoDB';

		$table_schema = trim( $table_schema );
		$table_schema = preg_replace( '/^\s+|\s+$/m', '', $table_schema ); // delete \s\s...
		$table_schema = preg_replace( '/^--.+$/m',    '', $table_schema ); // delete sql comment
		$table_schema = preg_replace( "/\r/",         '', $table_schema ); // delete caret
		$table_schema = preg_replace( "/\n\n+/",    "\n", $table_schema ); // delete double line break
		$table_schema = strtr( $table_schema, [
			' TINYINT('   => ' tinyint(',
			' INT('       => ' int(',
			' BIGINT('    => ' bigint(',
			' VARCHAR('   => ' varchar(',
			' CHAR('      => ' char(',
			' YEAR('      => ' year(',
			' DOUBLE('    => ' double(',
			' FLOAT('     => ' float(',
			' TEXT '      => ' text ',
			' TIMESTAMP ' => ' timestamp ',
			' DATETIME '  => ' datetime ',
		] );

		$table_schema = rtrim( $table_schema, ';' );

		return "$table_schema $collate_charset_engine;";
	}

}

