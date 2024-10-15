<?php

use CustomTheme\Core\Helper;
use CustomTheme\Core\Site;

function ct(): Site {
	return Site::getInstance();
}

function cth(): Helper {
	return Helper::getInstance();
}
