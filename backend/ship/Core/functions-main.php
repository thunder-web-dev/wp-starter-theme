<?php

use CustomTheme\Helper;
use CustomTheme\Site;

function ct(): Site {
	return Site::getInstance();
}

function cth(): Helper {
	return Helper::getInstance();
}
