<?php
/**
 * PSR-4 compatable autoload
 */

/**
 * PSR-4 compatable autoloader.
 * Site classes may be placed in `ship` OR `containers` folders.
 */
spl_autoload_register( static function ( $class ) {
	if ( strpos( $class, MAIN_NAMESPACE . '\\' ) !== 0 ) {
		return;
	}

	$class = str_replace( '\\', '/', $class );

	$check_files = [
		str_replace( MAIN_NAMESPACE . '/', '/' . DIR_NAME_BACKEND . '/ship/', $class ),
		str_replace( MAIN_NAMESPACE . '/', '/' . DIR_NAME_BACKEND . '/containers/', $class ),
	];

	foreach ( $check_files as $rel_path ) {
		if ( file_exists( $file = __DIR__ . "/$rel_path.php" ) ) {
			require_once $file;

			return; // stop
		}
	}
} );

/**
 * Авто включение плагинов и просто файлов по маске glob().
 *
 * Для модуля в папке должна лежать папка модуля, а в ней основной файл с названием:
 * 'название_папки.php' или 'zero.php' или 'main.php' или 'module.php'.
 *
 * Если название папки / файла начинается с '__', то они пропускаются.
 *
 * Плагины должны подключаться раньше авто-подключаемых файлов.
 *
 * @param string[] $glog_paths
 */
function include_modules_files( array $glog_paths ) {
	$glog_paths = array_map( static function ( $path ) {
		return wp_normalize_path( __DIR__ . '/' . DIR_NAME_BACKEND . '/' . $path );
	}, $glog_paths );
	$paths      = array_merge( ...array_map( 'glob', $glog_paths ) );
	$files      = [];

	foreach ( $paths as $filedir ) {
		$basename = basename( $filedir );

		if ( '__' === $basename[0] . $basename[1] ) {
			continue;
		}

		if ( is_dir( $filedir ) ) {
			if ( file_exists( $file = "$filedir/zero.php" ) ) {
				$files[] = $file;
			} elseif ( file_exists( $file = "$filedir/module.php" ) ) {
				$files[] = $file;
			}
		}
	}

	foreach ( $files as $file ) {
		require_once $file;
	}
}

/**
 * Подключает файлы из указанной директории.
 *
 * Не подключает файлы, начинающиеся на `__`.
 *
 * @param $dir_path
 */
function include_dir_files( $dir_path ) {
	foreach ( glob( $dir_path . '/*.php' ) as $file ) {
		if ( str_starts_with( basename( $file ), '__' ) ) {
			continue;
		}

		require_once $file;
	}
}
