<?php

require_once __DIR__ . '/Helper.php';

/// Upgrade
( new \CustomTheme\Upgrade\Upgrade_Core( CUSTOM_THEME_VER ) )->init();
