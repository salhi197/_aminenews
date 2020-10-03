<?php 

function voidgrid_get_flat_icons() {
		return [
			'fi flaticon-contact'=>'<span class="fi flaticon-contact"></span>Contact',
			'fi flaticon-double-angle-pointing-to-right'=>'Double Angle Pointing To Right',
			'fi flaticon-e-mail-envelope'=>'E Mail Envelope',
			'fi flaticon-envelope'=>'Envelope',
			'fi flaticon-fast-forward-double-right-arrows'=>'Fast Forward Double Right Arrows',
			'fi flaticon-fence'=>'Fence',
			'fi flaticon-info'=>'Info',
			'fi flaticon-lawn-mower'=>'Lawn Mower',
			'fi flaticon-location'=>'Location',
			'fi flaticon-log'=>'Log',
			'fi flaticon-mail'=>'Mail',
			'fi flaticon-people'=>'People',
			'fi flaticon-people-1'=>'People 1',
			'fi flaticon-portfolio-black-symbol'=>'Portfolio Black Symbol',
			'fi flaticon-question'=>'Question',
			'fi flaticon-right-arrows-couple'=>'Right Arrows Couple',
			'fi flaticon-sprout'=>'Sprout',
			'fi flaticon-watering-can'=>'Watering Can'
		];
	}

function voidgrid_post_orderby_options(){
    $orderby = array(
        'ID' => 'Post Id',
        'author' => 'Post Author',
        'title' => 'Title',
        'date' => 'Date',
        'modified' => 'Last Modified Date',
        'parent' => 'Parent Id',
        'rand' => 'Random',
        'comment_count' => 'Comment Count',
        'menu_order' => 'Menu Order',
    );

    return $orderby;
}

	
function void_grid_post_type(){
	$args= array(
			'public'	=> 'true',
			'_builtin'	=> false
		);
	$post_types = get_post_types( $args, 'names', 'and' );
	$post_types = array( 'post'	=> 'post' ) + $post_types;
	return $post_types;
}

/**
 * return all taxonomy by post type function
 *
 * @since 2.1
 */
function void_grid_ajax_process_tax_request() {
	// first check if data is being sent and that it is the data we want   
   
	if( isset( $_POST['postTypeNonce'] ) ){     
		$nonce = $_POST['postTypeNonce'];
		if ( ! wp_verify_nonce( $nonce, 'void_grid-post-type-nonce' ) ){
			wp_die( 'You are not allowed!');
		}
		$post_type = $_POST['post_type'];
		$taxonomoies = get_object_taxonomies( $post_type, 'names' );
		$taxonomy_name = array();    
		foreach( $taxonomoies as $taxonomy ){            
			$taxonomy_name[] = array( 'name'    => $taxonomy ) ;            
					
		}
		echo json_encode($taxonomy_name);
		wp_die(); 
	} 
}
add_action('wp_ajax_void_grid_ajax_tax', 'void_grid_ajax_process_tax_request');

/**
 * return all terms by taxonomy function
 *
 * @since 2.1
 */
function void_grid_ajax_process_terms_request() {
	// first check if data is being sent and that it is the data we want
	if( isset( $_POST['postTypeNonce'] ) ){     
		$nonce = $_POST['postTypeNonce'];
		if ( ! wp_verify_nonce( $nonce, 'void_grid-post-type-nonce' ) ){
			wp_die( 'You are not allowed!');
		}
		$taxonomy_type = $_POST['taxonomy_type'];           
		$term_slug = array();
		$terms =  get_terms( array( 'taxonomy' => $taxonomy_type) );
		foreach ( $terms as $term ){
			$id = $term->term_id;
			$name = $term->name;
			$term_slug[] = array(
					'id'    => $id,
					'name'  => $name
				);              
		}
		//to process the current post terms           
		$term_slug[] = array( 'id' => 'current', 'name' => 'Current Post' );
		echo json_encode($term_slug);
		wp_die(); 
	} 
}
add_action('wp_ajax_void_grid_ajax_terms', 'void_grid_ajax_process_terms_request');