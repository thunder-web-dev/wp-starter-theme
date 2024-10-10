<?php
/* * * * * * * * * * * * * * * * * * * * * * * *
 *   Подключение и Запуск функционала сайта    *
 * * * * * * * * * * * * * * * * * * * * * * * */

const CUSTOM_THEME_VER = '1.0.0';
const DIR_NAME_BACKEND = 'backend';
const MAIN_NAMESPACE   = 'CustomTheme';
const DEV_SITE_URL     = ''; // Например, https://penskaya.dev.thunder-web.ru
const GIT_REPO_URL     = ''; // Например, https://github.com/ThunderDrake/penskaya-theme

require_once __DIR__ . '/autoload-theme.php';

include_modules_files( [
	'/ship/*',
	'/containers/*',
] );
