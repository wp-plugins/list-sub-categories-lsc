<?php
/*
Plugin Name: List Sub Categories (LSC)
Plugin URI: http://mrmagne.com/
Version: 1.0
Description: This plugin enables a widget that lists only sub categories for the current category page.
Author: Magne Nygaard
Author URI: http://mrmagne.com/
*/

class LSC_Widget extends WP_Widget{

	function LSC_Widget(){
		$widget_ops = array('classname' => 'LSC_Widget', 'description' => 'This plugin enables a widget that lists only sub categories for the current category page.');
		$this->WP_Widget('LSC_Widget', 'List Sub Categories (LSC)', $widget_ops);
	}// end function LSC_Widget.

	function form($instance){
		$instance = wp_parse_args((array) $instance, array( 'title' => '', 'categories' => '' ));
		$title = $instance['title'];
		$categories = $instance['categories'];
		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id('categories'); ?>">Main category id's to use on: <input class="widefat" id="<?php echo $this->get_field_id('categories'); ?>" name="<?php echo $this->get_field_name('categories'); ?>" type="text" value="<?php echo attribute_escape($categories); ?>" /></label>
        <br /><span style="color: #999999; font-size: 11px;">List only your main category id's here, the sub categories will automatically be included. Use comma-separated values (example: 4,9,10).
        <br />If empty, it will display on all category pages.</span></p>
		<?php
	}// end function form.

	function update($new_instance, $old_instance){
		$instance = $old_instance;
		$instance['title'] = $new_instance['title'];
		$instance['categories'] = $new_instance['categories'];
		return $instance;
	}// end function update.

	function widget($args, $instance){
		
		// Get the main categories, remove white spaces and put in array $main_cats.
		$main_cats = str_replace(" ", "", explode(",", $instance['categories']));
				
		// Loop through all main cats.
		foreach($main_cats as $main_cat_id){
			
			// Get all (sub) categories where $main_cat_id is the parent (children only, no grandchildren!).
			$temp = get_categories("parent=$main_cat_id");
			
			// Loop through (sub) categories...
			foreach($temp as $child_cat_id){
				
				// ... And push them into the array $main_cats.
				array_push($main_cats, $child_cat_id->term_id);

			}// end second foreach.
			
		}// end first foreach.
		
		//var_dump($main_cats);
		
		// Only display the widget if category id is in the $main_cats array.		
		if((is_category($main_cats))){
					
			echo '<div class="lsc"><!-- start lsc -->';
			
			extract($args, EXTR_SKIP);
			
			echo $before_widget;
			
			$title = empty($instance['title']) ? '' : apply_filters('widget_title', $instance['title']);		
		
			if (!empty($title)){
				echo $before_title . $title . $after_title;
			}

			global $cat;
			
			// Get ancestors/parents (if there is any).
			$ancestors = get_ancestors( $cat, 'category' );
			
			// Get the first ancestor/parent (if there is one).
			$ancestor = $ancestors[0];
			
			// If we found an ancestor/parent (then we must be in a sub category).
			if($ancestor){
				
				// Get "children" to "ancestor/parent" (here we use $ancestor, since we are in a sub category).
				// Note: $ancestor is the main category id of the sub category id.
				foreach(get_categories("parent=$ancestor") as $category){
					// echo categories.
					echo '<a href="'. get_category_link( $category->term_id ) .'">'.$category->cat_name.'</a><br>';
				}
				
			}

			// If we did NOT find an ancestor (then we must be in a main category).
			if(!$ancestor){
				
				// Get "children" to "ancestor/parent" (here we use $cat, since we are in a main category).		
				foreach(get_categories("parent=$cat") as $category){
					// echo cateogries.
					echo '<a href="'. get_category_link( $category->term_id ) .'">'.$category->cat_name.'</a><br>';
				}
				
			}
		
			echo $after_widget;
			
			echo '</div><!-- end lsc -->';
			
		}// end is_category($main_cats).
	}// end function widget.
}// end class LSC_Widget.
add_action( 'widgets_init', create_function('', 'return register_widget("LSC_Widget");') );
?>