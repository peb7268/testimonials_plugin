<?php require_once('functions.php');
/*
	Plugin Name: Infosurv-Testimonials
	Plugin URI:
	Description: 
	Version: 0.5
	Author: Paul Barrick	
*/

$_ENV['mode'] = 'dev';
define('STYLESHEET_DIR', get_template_directory_uri().'/stylesheets/');
define('JS_DIR', get_template_directory_uri().'/javascripts/');


class InfTestimonials 
{
	public $post_id;
	public $labels;
	public $testimonial_data;
	public $client_name;
	public $source;
	public $link;
	
	public function __construct()
	{
		$this->setUp();
		$this->attatchActionsAndFilters();
	}
	public function setUp()
	{
		//$this->enqueueScriptsAndStyles(4625); //debug the script enqueing here
		add_shortcode( 'testimonials', array($this, 'get_testimonials' ));
	}
	public function attatchActionsAndFilters()
	{
		// add_action( $tag, $function_to_add, $priority, $accepted_args );
		add_action( 'init', array($this, 'testimonials_init'));	
		add_action('save_post', array($this, 'testimonials_save_post'));

		add_action( 'manage_posts_custom_column', array($this, 'testimonials_columns'), 1, 2 ); 
		add_filter( 'manage_edit-testimonials_columns', array($this, 'testimonials_edit_columns' ));
	}
	
	public function enqueueScriptsAndStyles($page_id)
	{		
		if (is_page($page_id)) {
			wp_enqueue_script ( 'isotope' );
			wp_enqueue_script ( 'global' );
		
			wp_enqueue_style  ( 'styles2' );
			wp_enqueue_style  ( 'isotope');		
		}
	}
	
	public function testimonials_init() 
	{
		$this->labels = array(
			'name' => 'Testimonials',
			'singular_name' => 'Testimonial',
			'add_new' => 'Add New',
			'add_new_item' => 'Add New Testimonial',
			'edit_item' => 'Edit Testimonial',
			'new_item' => 'New Testimonial',
			'view_item' => 'View Testimonial',
			'search_items' => 'Search Testimonials',
			'not_found' =>  'No Testimonials found',
			'not_found_in_trash' => 'No Testimonials in the trash',
			'parent_item_colon' => '',
		);
		
		register_post_type( 'testimonials', array(
			'labels' => $this->labels, 	//Sidebar - add new, testimonials, ect..
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
			'exclude_from_search' => true,
			'query_var' => true,
			'rewrite' => true,
			'capability_type' => 'post',
			'has_archive' => true,
			'hierarchical' => true,
			'taxonomies'  => array('category'),
			'menu_position' => 10,
			'supports' => array( 'editor', 'title', 'thumbnail'),
			'register_meta_box_cb' => array($this, 'testimonials_meta_boxes'),
		));
	}
	
	public function testimonials_meta_boxes() 
	{
		//add_meta_box( $id, $title, $callback, $post_type, $context, $priority, $callback_args );
		add_meta_box( 'testimonials_form', 'Testimonial Details', array($this, 'testimonials_form'), 'testimonials', 'normal', 'high' );
	}
	
	//The callback to create the forms
	public function testimonials_form() 
	{
		$post_id = get_the_ID();
		$testimonial_data = get_post_meta( $post_id, '_testimonial', true );
		$client_name = ( empty( $testimonial_data['client_name'] ) ) ? '' : $testimonial_data['client_name'];
		$source = ( empty( $testimonial_data['source'] ) ) ? '' : $testimonial_data['source'];
		$link = ( empty( $testimonial_data['link'] ) ) ? '' : $testimonial_data['link'];
				
		$this->post_id = $post_id;
		$this->testimonial_data = $testimonial_data;
		$this->client_name = $client_name;
		$this->source = $source;
		$this->link = $link;
	
		//wp_nonce_field( 'testimonials', 'testimonials' );
		?>
		<p>
			<label>Client's Name (optional)</label><br />
			<input type="text" value="<?php echo $client_name; ?>" name="testimonial[client_name]" size="40" />
		</p>
		<p>
			<label>Business/Site Name (optional)</label><br />
			<input type="text" value="<?php echo $source; ?>" name="testimonial[source]" size="40" />
		</p>
		<p>
			<label>Link (optional)</label><br />
			<input type="text" value="<?php echo $link; ?>" name="testimonial[link]" size="40" />
		</p>
		<?php
	}
	
	// Data validation and saving
	public function testimonials_save_post( $post_id )
	{	
		
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		{
			return;
		}
	
		if ( ! empty( $_POST['testimonials'] ) && ! wp_verify_nonce( $_POST['testimonials'], 'testimonials' ) )
		{
			return;
		}

	
		if ( ! empty( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) 
		{
			if ( ! current_user_can( 'edit_page', $post_id ) )
			{
				return;
			}
		} else {
			if ( ! current_user_can( 'edit_post', $post_id ) )
			{
				return;
			}
		}
		if ( ! wp_is_post_revision( $post_id ) && 'testimonials' == get_post_type( $post_id ) ) {
			remove_action( 'save_post', array($this, 'testimonials_save_post') );
			wp_update_post(array(
				'ID' => $post_id
				//'post_title' => 'Testimonial - ' . $post_id
			) );
			
			add_action('save_post', array($this, 'testimonials_save_post' ));
		} 
	
		if ( ! empty( $_POST['testimonial'] ) ) {
			$testimonial_data['client_name'] = ( empty( $_POST['testimonial']['client_name'] ) ) ? '' : sanitize_text_field( $_POST['testimonial']['client_name'] );
			$testimonial_data['source'] = ( empty( $_POST['testimonial']['source'] ) ) ? '' : sanitize_text_field( $_POST['testimonial']['source'] );
			$testimonial_data['link'] = ( empty( $_POST['testimonial']['link'] ) ) ? '' : esc_url( $_POST['testimonial']['link'] );

			update_post_meta( $post_id, '_testimonial', $testimonial_data );
			
		} else {
			delete_post_meta( $post_id, '_testimonial' );
		}
	}
	
	/**
	 * Display a testimonial
	 *
	 * @param	int $post_per_page  The number of testimonials you want to display
	 * @param	string $orderby  The order by setting  https://codex.wordpress.org/Class_Reference/WP_Query#Order_.26_Orderby_Parameters
	 * @param	array $testimonial_id  The ID or IDs of the testimonial(s), comma separated
	 *
	 * @return	string  Formatted HTML
	 */
	public function get_testimonial( $posts_per_page = 1, $orderby = 'none', $testimonial_id = null ) 
	{
			$args = array(
				'posts_per_page' => (int) ($posts_per_page) ? $posts_per_page : 100,
				'post_type' => 'testimonials',
				'orderby' => $orderby,
				'no_found_rows' => true,
			);
			if ( $testimonial_id )
				$args['post__in'] = array( $testimonial_id );
		
			$query = new WP_Query( $args  );
			
			//Build the collection containers
			$testimonials = array();
			$testimonials['start'] 			= array();
			$testimonials['categories']	= array();
			$testimonials['titles'] 		= array();
			$testimonials['body'] 			= array();				
			$testimonials['end'] 			= array();
			$wrapper								= array('<div id="isotope_wrapper" class="clearfix">', '<div id="isotope_container" class="isotope_container clearfix">');
			$inc = 0;
			
			if ( $query->have_posts() ): while ( $query->have_posts() ) : $query->the_post();
					$post_id = get_the_ID();
					$testimonials['categories'][] = get_the_category( $post_id );
					$testimonial_data = get_post_meta( $post_id, '_testimonial', true );
					$client_name = ( empty( $testimonial_data['client_name'] ) ) ? '' : $testimonial_data['client_name'];
					$source = ( empty( $testimonial_data['source'] ) ) ? '' : ' - ' . $testimonial_data['source'];
					$link = ( empty( $testimonial_data['link'] ) ) ? '' : $testimonial_data['link'];
					$cite = ( $link ) ? '<a href="' . esc_url( $link ) . '" target="_blank">' . $client_name . $source . '</a>' : $client_name . $source;
					$thumbnail = $this->grabThumbnail( $post_id );
										
					require "views/thumbnail-format.php";					
					$inc++;
				endwhile; 
				wp_reset_postdata();
			endif;
			
			$categories 			= $this->extractCategories($testimonials['categories']);
			$navigation 			= $this->getNavigation( $categories );
			$layout 					= array($navigation, $wrapper);
			$testimonials_return = $this->processTestimonials( $testimonials, $layout);
			//__e($testimonials, '$testimonials');
			
		return $testimonials_return;
	}
	
	public function extractCategories($categories)
	{
		$categories_to_return = array();
		
		foreach( $categories as $i => $category){
			if(isArray($category)){
				foreach($category as $key => $val){
				
					if(isUnique($val->name, $categories_to_return))
					{
						$categories_to_return[] = $val->name;
					} 
				}
			} 		
		}
		return $categories_to_return;
	}
	
	public function getNavigation( $categories = null)
	{
		//require "views/element-navigation.php";
		$start = '<div id="filters_wrapper" class="clearfix"><ul id="filters" class="clearfix"><li><a href="#" data-filter="*">show all</a></li>';
		$body = '';
		
		foreach($categories as $i => $value)
		{
			$body .= "<li><a href='#' data-filter='.type".$i."'> $value </a></li>";
		}
	  
	  $end = '</ul></div><!-- #filters_wrapper -->';
	
	  return $start.$body.$end;
	}
	public function grabThumbnail( $post_id )
	{
		if(has_post_thumbnail( $post_id ))
		{
			$thumb 	= get_the_post_thumbnail( $post_id );
			return $thumb;
		} 
	}
	
	public function processTestimonials( $testimonials, $layout = null )
	{
				$testimonials_return  = $layout[0];																			//Navigation
				$testimonials_return .= (!empty($layout[1])) ? $layout[1][0].$layout[1][1] : '';				//Isotope_wrapper & Isotope_container
				$max = count($testimonials);
				
				for($i = 0; $i < $max; $i++)
				{
					$testimonials_return .= $testimonials['start'][$i];	
					$testimonials_return .= $testimonials['titles'][$i];
					$testimonials_return .= $testimonials['body'][$i];
					$testimonials_return .= $tesimonials['end'][$i];	
				}
				$testimonials_return .= '</div><!-- #isotope_container -->
												 </div><!-- #isotope_wrapper -->';
												 
				return $testimonials_return;
	}
	
	//Add a testimonial via shortcode - [testimonial posts_per_page="1" orderby="none" testimonial_id=""]
	//shortcode: [testimonials] 
	public function get_testimonials( $atts ) 
	{
		extract( shortcode_atts( array(
			'orderby' => 'none',
			'testimonial_id' => '',
		), $atts ) );
	
		return $this->get_testimonial( $posts_per_page, $orderby, $testimonial_id );
	}
	
	// Modifying the list view columns
	public function testimonials_edit_columns( $columns ) 
	{	
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'title' => 'Title',
			'testimonial' => 'Testimonial',
			'testimonial-client-name' => 'Client\'s Name',
			'testimonial-source' => 'Business/Site',
			'testimonial-link' => 'Link',
			'author' => 'Posted by',
			'date' => 'Date'
		);
		return $columns;
	}
	
	//Customizing the list view columns	 
	public function testimonials_columns( $column, $post_id) 
	{
		$testimonial_data = get_post_meta( $post_id, '_testimonial', true );
		switch ( $column ) {
			case 'testimonial':
				the_excerpt();
				break;
			case 'testimonial-client-name':
				if ( ! empty( $testimonial_data['client_name'] ) )
					echo $testimonial_data['client_name'];
				break;
			case 'testimonial-source':
				if ( ! empty( $testimonial_data['source'] ) )
					echo $testimonial_data['source'];
				break;
			case 'testimonial-link':
				if ( ! empty( $testimonial_data['link'] ) )
					echo $testimonial_data['link'];
				break;
		}
	}
}

$testimonials = new InfTestimonials(); 