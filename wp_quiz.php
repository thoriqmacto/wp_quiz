<?php 
/*
Plugin Name: WP Quiz
Plugin URI: http://wp.tutsplus.com/author/shaneosbourne/
Description: An example of using Backbone within a plugin.
Author: Shane Osbourne
Version: 0.1
Author URI: http://wp.tutsplus.com/author/shaneosbourne/
*/

/** wp_quiz.php **/
include 'src/WpQuiz.php'; // Class File
 
// Create an instance of the Plugin Class
function call_wp_quiz() {
    return new WpQuiz( 'admin' );
}
 
// Only when the current user is an Admin
if ( is_admin )
    add_action( 'init', 'call_wp_quiz' );
 
// Helper function
if ( ! function_exists( 'pp' ) ) {
    function pp() {
        return plugin_dir_url( __FILE__ );
    }
}

?>