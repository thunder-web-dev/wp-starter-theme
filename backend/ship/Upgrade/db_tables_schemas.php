<?php

/**
 *
 * Single schema example:
 *
 *     'wp_plugmeta' => "CREATE TABLE wp_plugmeta (
 *         post_id  BIGINT  unsigned NOT NULL default 0    COMMENT 'ID поста, 123',
 *         slug     VARCHAR(191) NOT NULL default ''       COMMENT 'Ярлык плагина, user-role-editor',
 *         PRIMARY KEY  (post_id),
 *         KEY slug (slug)
 *     )";
 *
 * @return array
 */

global $wpdb;

$tables = [];

// $tables[ $wpdb->project_requests ] = \CustomTheme\CTP_Project_Examples\CTP_Project_Examples_DB_Tables::project_requests();

return $tables;
