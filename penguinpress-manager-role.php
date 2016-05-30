<?php
/**
 * Plugin Name: PenguinPress Manager Role
 * Plugin URI:  https://github.com/nathansh/penguinpress-manager-role
 * Description: Defines a manager role, similar to admin but unable to change theme or plugins. Useful for the website owner.
 * Version:     1.0b
 * Author:      Nathan Shubert-Harbison
 * Author URI:  http://nathansh.com
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 *
 *
 * @package presspenguin-manager-role
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

			/* themes/plugins */
			'switch_themes' => false,
			'edit_themes' => false,
			'activate_plugins' => false,
			'edit_plugins' => false,
			'edit_users' => true,

			/* options, etc */
			'manage_options' => false,
			'moderate_comments' => true,
			'manage_categories' => true,
			'manage_links' => false,
			'upload_files' => true,
			'unfiltered_html' => true,
			'import' => false,
			'export' => false,

			/* content */
			'read' => true,
			'delete' => true,

			/* posts */
			'publish_posts' => true,
			'read_private_posts' => true,
			'edit_posts' => true,
			'edit_others_posts' => true,
			'edit_published_posts' => true,
			'edit_private_posts' => true,
			'delete_posts' => true,
			'delete_others_posts' => true,
			'delete_published_posts' => true,
			'delete_private_posts' => true,

			/* pages */
			'publish_pages' => true,
			'read_private_pages' => true,

			'edit_pages' => true,
			'edit_others_pages' => true,
			'edit_published_pages' => true,
			'edit_private_pages' => true,
			'delete_pages' => true,
			'delete_others_pages' => true,
			'delete_published_pages' => true,
			'delete_private_pages' => true,

			/* plugins/themes */
			'delete_plugins' => false,
			'delete_themes' => false,
			'update_core' => false,
			'update_plugins' => false,
			'update_themes' => false,
			'edit_files' => false,
			'install_plugins' => false,
			'install_themes' => false,

			/* users */
			'delete_users' => true,
			'promote_users' => false,
			'remove_users' => true,
			'create_users' => true,
			'list_users' => true,
			'edit_theme_options' => true,

			// Visual Form Builder Pro
			'vfb_read' => true,
			'vfb_create_forms' => true,
			'vfb_edit_forms' => true,
			'vfb_copy_forms' => true,
			'vfb_delete_forms' => true,
			'vfb_import_forms' => true,
			'vfb_export_forms' => true,
			'vfb_edit_email_design' => true,
			'vfb_view_entries' => true,
			'vfb_edit_entries' => true,
			'vfb_delete_entries' => true,
			'vfb_edit_settings' => true,
			'vfb_uninstall_plugin' => true

		) )
	);

}

add_action( 'init', 'pp_manager_role_add_role' );


/**
 * On plugin deactivation remove the role, usefull for debug
 *
 */
function pp_manager_role_deactivate() {

	remove_role( 'manager' );

}

register_deactivation_hook( __FILE__, 'pp_manager_role_deactivate' );


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


/*
 * Modifying TinyMCE editor to remove unused items

 */
function pp_manager_modify_tiny_mca( $init ) {

	// Only do this for non-admins

	if ( ! current_user_can( 'manage_options' ) ) {

		$init['block_formats'] = apply_filters( 'pp-utils/editor/formats', 'Paragraph=p;Header 2=h2;Header 3=h3;Header 4=h4' );
		$init['toolbar1'] = apply_filters( 'pp-utils/editor/toolbar1', 'formatselect,bold,italic,underline,strikethrough,bullist,numlist,blockquote,link,unlink,spellchecker' );
		$init['toolbar2'] = apply_filters( 'pp-utils/editor/toolbar2', '' );

	}


	return $init;

}

add_filter( 'tiny_mce_before_init', 'pp_manager_modify_tiny_mca', 1, 10 );



/**
 * Set tinymce as the default editor always for non-admins
 *
 */
function pp_manager_set_default_editor() {

	if ( ! current_user_can( 'manage_options' ) ) {

		return 'tinymce';

	}
}

add_filter( 'wp_default_editor', 'pp_manager_set_default_editor' );


/**
 * Hide the text editor link for non-admins
 *
 */
function pp_manager_hide_text_editor_link() {

	if ( ! current_user_can( 'manage_options' ) ) {

		echo '<style>.wp-editor-tabs{display: none;}</style>';

	}

}

add_action( 'admin_head', 'pp_manager_hide_text_editor_link' );
