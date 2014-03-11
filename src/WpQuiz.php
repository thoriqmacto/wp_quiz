<?php
	
class WpQuiz{
	//********* PROPERTIES
	// Names of Custom Post Type
    public $postTypeNameSingle = 'Question';
    public $postTypeNamePlural = 'Questions';

    // Meta Box Stuff
    public $metaBoxTitle = 'Answers';
    public $metaBoxTempl = 'templates/metabox.templ.php';

    // Question Id's
    public $answerIds = array( 'quiz-a-1', 'quiz-a-2', 'quiz-a-3', 'quiz-a-4' );

    // Javascript
    public $jsAdmin = '/js/admin.js';
	
	//********* CONSTRUCTOR
	public function __construct( $type ){
		switch ( $type ) {
	        case 'admin' :
	            // Register the Post Type
	            $this->registerPostType( $this->postTypeNameSingle, $this->postTypeNamePlural );

	            // Add the Meta Box
	            add_action( 'add_meta_boxes', array( $this, 'addMetaBox' ) );

	            // Accept an Ajax Request
	            add_action( 'wp_ajax_save_answer', array( $this, 'saveAnswers' ) );

	            // Watch for Post being saved
	            add_action( 'save_post', array( $this, 'savePost' ) );
	    }
	}
	
	//********* METHOD
	// Add meta box
	public function addMetaBox(){
		// Load the Javascript needed on this admin page.
	    $this->addScripts();

	    // Create an id based on Post-type name
	    $id = $this->postTypeNameSingle . '_metabox';

	    // Add the meta box
	    add_meta_box(
	        $id,
	        $this->metaBoxTitle,
	        array( $this, 'getMetaBox' ), // Get the markup needed
	        $this->postTypeNameSingle
	    );
	}
	
	// Get meta box
	public function getMetaBox( $post ){
		// Get the current values for the questions
	    $json = array();
	    foreach ( $this->answerIds as $id ) {
	        $json[] = $this->getOneAnswer( $post->ID, $id );
	    }

	    // Set data needed in the template
	    $viewData = array(
	        'post' => $post,
	        'answers' => json_encode( $json ),
	        'correct' => json_encode( get_post_meta( $post->ID, 'correct_answer' ) )
	    );

	    echo $this->getTemplatePart( $this->metaBoxTempl, $viewData );
	}
	
	// Get one answer
	public function getOneAnswer( $post_id, $answer_id ){
		return array( 
			'answer_id'	=> $answer_id,
			'answer'	=> get_post_meta( $post_id, $answer_id, true)
		);
	}
	
	// Save post
	public function savePost( $post_id ){
		// Check that we are saving our Custom Post type
	    if ( $_POST['post_type'] !== strtolower( $this->postTypeNameSingle ) ) {
	        return;
	    }

	    // Check that the user has correct permissions
	    if ( ! $this->canSaveData( $post_id ) ) {
	        return;
	    }

	    // Access the data from the $_POST global and create a new array containing
	    // the info needed to make the save
	    $fields = array();
	    foreach ( $this->answerIds as $id ) {
	        $fields[$id] = $_POST[$id];
	    }

	    // Loop through the new array and save/update each one
	    foreach ( $fields as $id => $field ) {
	        add_post_meta( $post_id, $id, $field, true );
	        // or
	        update_post_meta( $post_id, $id, $field );
	    }

	    // Save/update the correct answer
	    add_post_meta( $post_id, 'correct_answer', $_POST['correct_answer'], true );
	    // or
	    update_post_meta( $post_id, 'correct_answer', $_POST['correct_answer'] );
	}
	
	// Save Answers
	public function saveAnswers(){
		// Get PUT data and decode it
	    $model = json_decode( file_get_contents( "php://input" ) );

	    // Ensure that this user has the correct permissions
	    if ( ! $this->canSaveData( $model->post_id ) ) {
	        return;
	    }

	    // Attempt an insert/update
	    $update = add_post_meta( $model->post_id, $model->answer_id, $model->answer, true );
	    // or
	    $update = update_post_meta( $model->post_id, $model->answer_id, $model->answer );

	    // If a save or update was successful, return the model in JSON format
	    if ( $update ) {
	        echo json_encode( $this->getOneAnswer( $model->post_id, $model->answer_id ) );
	    } else {
	        echo 0;
	    }
		
	    die();
	}
	
	//********* METHOD-HELPERS
	/**
	* Determine if the current user has the relevant permissions
	*
	* @param $post_id
	* @return bool
	*/
	private function canSaveData( $post_id ) {
	    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
	        return false;
	    if ( 'page' == $_POST['post_type'] ) {
	        if ( ! current_user_can( 'edit_page', $post_id ) )
	            return false;
	    } else {
	        if ( ! current_user_can( 'edit_post', $post_id ) )
	            return false;
	    }
	    return true;
	}
 
	private function addScripts() {
	    wp_register_script( 'wp_quiz_main_js', pp() . $this->jsAdmin , array( 'backbone' ), null, true );
	    wp_enqueue_script( 'wp_quiz_main_js' );
	}
 
	/**
	* Register a Custom Post Type
	*
	* @param $single
	* @param $plural
	* @param null $supports
	*/
	private function registerPostType( $single, $plural, $supports = null ) {
 
	    $labels = array(
	        'name' => _x( $plural, 'post type general name' ),
	        'singular_name' => _x( "$single", 'post type singular name' ),
	        'add_new' => _x( "Add New $single", "$single" ),
	        'add_new_item' => __( "Add New $single" ),
	        'edit_item' => __( "Edit $single" ),
	        'new_item' => __( "New $single" ),
	        'all_items' => __( "All $plural" ),
	        'view_item' => __( "View $single" ),
	        'search_items' => __( "Search $plural" ),
	        'not_found' => __( "No $plural found" ),
	        'not_found_in_trash' => __( "No $single found in Trash" ),
	        'parent_item_colon' => '',
	        'menu_name' => $plural
	    );
	    $args = array(
	        'labels' => $labels,
	        'public' => true,
	        'publicly_queryable' => true,
	        'show_ui' => true,
	        'show_in_menu' => true,
	        'query_var' => true,
	        'rewrite' => true,
	        'capability_type' => 'post',
	        'has_archive' => true,
	        'hierarchical' => false,
	        'menu_position' => null,
	        'supports' => ( $supports ) ? $supports : array( 'title', 'editor', 'page-attributes' )
	    );
	    register_post_type( $single, $args );
	}
 
	/**
	* Render a Template File
	*
	* @param $filePath
	* @param null $viewData
	* @return string
	*/
	public function getTemplatePart( $filePath, $viewData = null ) {
 
	    ( $viewData ) ? extract( $viewData ) : null;
 
	    ob_start();
	    include ( "$filePath" );
	    $template = ob_get_contents();
	    ob_end_clean();
 
	    return $template;
	}
}	