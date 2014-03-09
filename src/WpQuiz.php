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
	public function _construct( $type ){
		switch ( $type ) {
	        case 'admin' :
	            // Register the Post Type
	            $this->registerPostType(
	                $this->postTypeNameSingle,
	                $this->postTypeNamePlural
	            );

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
}	
	
?>