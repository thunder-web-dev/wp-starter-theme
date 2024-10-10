<?php
/**
 * Runs before activation. Before DB tables auto-update.
 *
 * Do here any ALTER TABLE work.
 *
 * Create method with name `ver_{version}()` and it will automatically run on upgrade.
 * Method with suffix `_once` - `ver_{version}_once()` will run only once (not run on force upgrade).
 */

namespace CustomTheme\Upgrade;

final class Functions_Before {

	use Helper;

	public static function ver_1_0_1_once( &$res ) {

	}

}


