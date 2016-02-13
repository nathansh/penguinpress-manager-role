<?php
/**
 * Plugin Name: PenguinPress Manager Role
 * Plugin URI:  https://github.com/nathansh/pp-manager-role
 * Description: Defines a manager role, similar to admin but unable to change theme or plugins. Useful for the website owner.
 * Version:     1.0b
 * Author:      Nathan Shubert-Harbison
 * Author URI:  http://nathansh.com
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 *
 *
 * @package pp-manager-role
 * @author  Nathan Shubert-Harbison <hello@nathansh.com>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */


/**
 * Defines the manager user
 */
function pp_manager_role_add_role() {

	remove_role( 'manager' );

	add_role(
		'manager',
		__( 'Manager' ),
		apply_filters( 'pp-manager-role/capabilities', array (
			'read' => true,
			'edit_pages' => true,
			'publish_pages' => true,
			'edit_others_pages' => true,
			'edit_published_pages' => true,
			'delete_pages' => true,
			'delete_others_pages' => true,
			'delete_published_pages' => true,
			'delete_others_posts' => true,
			'delete_private_posts' => true,
			'edit_private_posts' => true,
			'read_private_posts' => true,
			'delete_private_pages' => true,
			'edit_private_pages' => true,
			'read_private_pages' => true,
			'delete_published_posts	' => true,
			'delete_posts' => true,
			'publish_posts' => true,
			'edit_published_posts' => true,
			'edit_others_posts' => true,
			'edit_posts' => true,
			'delete_plugins' => false,
			'delete_themes' => false,
			'delete_users' => true,
			'unfiltered_html' => true,
			'import' => false,
			'export' => false,
			'upload_files' => true,
			'manage_links' => false,
			'manage_categories' => true,
			'moderate_comments' => true,
			'manage_options' => false,
			'promote_users' => false,
			'remove_users' => true,
			'update_core' => false,
			'update_plugins' => false,
			'update_themes' => false,
			'edit_files' => false,
			'create_users' => true,
			'edit_users' => true,
			'install_plugins' => false,
			'edit_plugins' => false,
			'activate_plugins' => false,
			'install_themes' => false,
			'list_users' => true,
			'edit_theme_options' => true,
			'edit_themes' => false,
			'switch_themes' => false
		) )
	);

}

add_action( 'init', 'pp_manager_role_add_role' );


/**
 * Remove administrator from editable roles
 */
function pp_manager_role_editable_roles( $roles ) {

	// If the current user is a manager...
	if ( current_user_can( 'manager' ) ) {

		// Don't include the administrator
		unset( $roles['administrator'] );

	}

	return $roles;

}

add_filter( 'editable_roles', 'pp_manager_role_editable_roles', 10 );


/**
 * Make managers unable to edit administrators
 */
function pp_manager_role_cant_edit_administrator( $actions, $user ) {

	// If the current user is a manager...
	if ( current_user_can( 'manager' ) ) {

		// If the current item is an administrator...
		if ( in_array('administrator', $user->roles) ) {

			// Return an empty array. Manager can't edit administrator.
			return array();

		} // showing is admin

	} // current is manager

	return $actions;

}

add_filter( 'user_row_actions', 'pp_manager_role_cant_edit_administrator', 10, 20);


/**
 * Exclude admins when querying users to display in users.php
 */
function pp_manager_filter_user_table_query( $args ) {

	// Remove administrators from managers
	if ( current_user_can('manager') ) {	
		$args['exclude'] = array(1);
	}

	return $args;

	
}

add_filter( 'users_list_table_query_args', 'pp_manager_filter_user_table_query', 1, 10);


/**
 * Exclude admins from table views to managers, disables editing
 */
function pp_manager_filter_views_users( $views ) {

	// Remove administrators from managers
	if ( current_user_can('manager') ) {	
		if ( array_key_exists('administrator', $views) ) {
			unset( $views['administrator'] );
		}
	}

	return $views;

}

add_filter( 'views_users', 'pp_manager_filter_views_users', 1, 10);


/**
 * Create an options page to list admins, but only for managers
 */
function pp_manager_create_options_page_for_listing_admins() {

	// Only for managers
	if ( current_user_can('manager') ) {

		// Add admins listing page
		add_submenu_page(
			'users.php',
			'Administrators',
			'Admins',
			'edit_users',
			'admins_for_managers',
			'pp_manager_options_page_listing_admins'
		);

	}

}

add_action( 'admin_menu', 'pp_manager_create_options_page_for_listing_admins' );


/**
 * Create the options page content for listing admins
 */
function pp_manager_options_page_listing_admins() {

	echo '<div class="wrap">';

		echo '<h1>' . __( 'Administrators' ) . '</h1>';

		echo '<p class="description">';
			$description = __( 'The following administrators are able to manage every technical aspect of this website.' );
			echo apply_filters( 'pp-manager-role/admin-page-description', $description );
		echo '<p>';

		$user_query = new WP_User_Query(array(
			'role' => 'administrator'
		));

		$users = $user_query->get_results();

		if ( count($users) > 0 ) {

			// Loop 'em up
			foreach ( $users as $user ) {

				echo '<li>';
					echo $user->data->display_name;
					echo ' (';
						echo '<a href="mailto:' . $user->data->user_email . '">' . $user->data->user_email . '</a>';
					echo ')';
				echo '</li>';

			}
			
		}

	echo '</div>';

}