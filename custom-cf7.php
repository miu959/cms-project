<?php
/*
Plugin Name:  Custom Contcat Form 7
Plugin URI:   https://developer.wordpress.org/plugins/custom-contact-form-7
Description:  Custom Field Plugin simply add meta field
Version:      1.0.0
Author:       WordPress.org
Author URI:   https://developer.wordpress.org/
*/


function create_table(){

    global $wpdb;
    $db       = apply_filters( 'cfdb7_database', $wpdb );
    $table_name = $db->prefix.'db7_data';

    if( $db->get_var("SHOW TABLES LIKE '$table_name'") != $table_name ) {

        $charset_collate = $db->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            form_id bigint(20) NOT NULL AUTO_INCREMENT,
            form_post_id bigint(20) NOT NULL,
            form_value longtext NOT NULL,
            form_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            PRIMARY KEY  (form_id)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }
	
}

function cfdb7_on_activate( ){

    create_table();
    
	// Add custom capability
	$role = get_role( 'administrator' );
	$role->add_cap( 'cfdb7_access' );
}

register_activation_hook( __FILE__, 'cfdb7_on_activate' );

function cfdb7_on_deactivate() {

	// Remove custom capability from all roles
	global $wp_roles;

	foreach( array_keys( $wp_roles->roles ) as $role ) {
		$wp_roles->remove_cap( $role, 'cfdb7_access' );
	}
}

register_deactivation_hook( __FILE__, 'cfdb7_on_deactivate' );

function contactform7_before_send_mail( $form_to_DB ) {
    //set your db details
    global $wpdb;
    $db       = apply_filters( 'cfdb7_database', $wpdb );
    $table_name = $db->prefix.'db7_data';

    $form_to_DB = WPCF7_Submission::get_instance();
    if ( $form_to_DB ) 
        $formData = $form_to_DB->get_posted_data();
    $title = $formData['your-name'];
    $email = $formData['your-email'];
    $subject = $formData['your-subject'];
    $message = $formData['your-message'];

    $mydb->insert( $table_name, array( 'title' =>$title, 'email' => $email, 'subject' => $subject, 'message' => $message ), array( '%s' ) );
}
remove_all_filters ('wpcf7_before_send_mail');
add_action( 'wpcf7_before_send_mail', 'contactform7_before_send_mail' );

?>