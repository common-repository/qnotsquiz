<?php


// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}
//delete options
delete_option( 'qnotsquiz_start_text' );
//delete posts with types questions and quiz upon uninstallation of qnotsquiz
$args = array (
    'post_type' =>  array('questions','quiz','qnots_attempts'),
    'nopaging' => true
  );
  $query = new WP_Query ($args);
  while ($query->have_posts ()) {
    $query->the_post ();
    $id = get_the_ID ();
    wp_delete_post ($id, true);
  }
  wp_reset_postdata ();
