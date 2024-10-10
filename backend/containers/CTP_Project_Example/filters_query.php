<?php

// add_action( 'pre_get_posts', 'modify_query_project_by_filter_params' );

/**
 * @param WP_Query $query
 */
function modify_query_project_by_filter_params( WP_Query $query ) {
	if ( ! is_admin() && $query->is_main_query() && $query->is_post_type_archive( 'project' ) ) {

	}
}
