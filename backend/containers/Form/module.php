<?php

require_once __DIR__ . '/Admin_Side.php';
require_once __DIR__ . '/Form_Base.php';

// 1 вариант - Автоматическое подключение всех классов с формами
include_dir_files( __DIR__ . '/forms' );

// 2 вариант - Ручное подключение, когда порядок подключения важен из-за наследования классов
// require_once __DIR__ . '/forms/Form_1.php';
// require_once __DIR__ . '/forms/Form_2_Extends_Form_1.php';
// require_once __DIR__ . '/forms/Form_3_Extends_Form_1.php';

// 3 вариант - Совмещённый (сначала указываются основные классы, потом автоматом наследуемые)
// require_once __DIR__ . '/forms/Form_1.php';
// include_dir_files( __DIR__ . '/forms' );
