<?php

namespace CustomTheme\CTP_Project_Example;

class Admin_Columns {

	public function init(): void {
		//add_filter( 'manage_project_posts_columns', [ $this, 'edit_columns' ], 4 );
		//add_action( 'manage_project_posts_custom_column', [ $this, 'fill_columns' ], 5, 2 );
	}

	public function edit_columns( array $columns ): array {
		$num = 2;

		$new_columns = [
			// 'project_developer' => __( 'Developer', 'ardang' ),
		];

		return array_slice( $columns, 0, $num ) + $new_columns + array_slice( $columns, $num );
	}

	public function fill_columns( $colname, $post_id ): void {
		if ( $colname === 'project_developer' ) {

		}
	}

}
