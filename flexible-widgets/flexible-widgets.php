<?php
/*
Plugin Name: Flexible Widgets
Plugin URI: http://www.netsans.dk/flexible-widgets
Description: Flexible Widgets lets you display a widget on any category or page you wish. When setting up the widget, you are able to select the categories and/or pages where you want to display the widget. If none are selected, the widget will be displayed globally on your site, exactly like a default WordPress widget.  
Version: 0.1
Author: Morten Brunbjerg Bech
Author URI: http://twitter.com/bech/
License: GPLv2 or later
*/

// Define current version constant
define( 'FLEXIBLE_WIDGETS_VERSION', '0.1' );


class Flexible_Widget_Pages extends WP_Widget {

	function Flexible_Widget_Pages() {
		$widget_ops = array('classname' => 'widget_pages', 'description' => __( 'Your site&#8217;s WordPress Pages') );
		$control_ops = array('width' => 450, 'height' => 350);
		$this->WP_Widget('pages', __('Pages'), $widget_ops, $control_ops);
	}

	function widget( $args, $instance ) {
		extract( $args );

		$title = apply_filters('widget_title', empty( $instance['title'] ) ? __( 'Pages' ) : $instance['title'], $instance, $this->id_base);
		$sortby = empty( $instance['sortby'] ) ? 'menu_order' : $instance['sortby'];
		$exclude = empty( $instance['exclude'] ) ? '' : $instance['exclude'];
		$checkCats = $instance['checkCats'];
		$checkPages = $instance['checkPages'];
		
		if (
		$checkCats !=0 && is_category($checkCats) // ...................... Selected categories only.
		|| ($checkCats !=0 && is_single() && in_category($checkCats)) // .. Single posts in selected categories only.
		|| ($checkPages !=0 && is_page($checkPages)) // ................... Selected pages only.
		|| ($checkCats == 0 && $checkPages == 0 && (is_single()
			|| is_category() 
			|| is_page())) // ............................................. All single posts, pages & categories.
		) {

			if ( $sortby == 'menu_order' )
				$sortby = 'menu_order, post_title';

			$out = wp_list_pages( apply_filters('widget_pages_args', array('title_li' => '', 'echo' => 0, 'sort_column' => $sortby, 'exclude' => $exclude) ) );

			if ( !empty( $out ) ) {
				echo $before_widget;
				if ( $title)
					echo $before_title . $title . $after_title;
			?>
			<ul>
				<?php echo $out; ?>
			</ul>
			<?php
				echo $after_widget;
			}
		}
	} 

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		if ( in_array( $new_instance['sortby'], array( 'post_title', 'menu_order', 'ID' ) ) ) {
			$instance['sortby'] = $new_instance['sortby'];
		} else {
			$instance['sortby'] = 'menu_order';
		}

		$instance['exclude'] = strip_tags( $new_instance['exclude'] );
		$instance['checkCats'] = $new_instance['checkCats'];
		$instance['checkPages'] = $new_instance['checkPages'];

		return $instance;
	}

	function form( $instance ) {
		//Defaults
		$instance = wp_parse_args( (array) $instance, array( 'sortby' => 'post_title', 'title' => '', 'exclude' => '') );
		$title = esc_attr( $instance['title'] );
		$exclude = esc_attr( $instance['exclude'] );
		$checkCats = $intance['checkCats'];
		$checkPages = $intance['checkPages'];
		
		$cats = get_categories('hide_empty=1&orderby=name&order=asc');
		$pages = get_pages('orderby=name&order=asc');	
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
		<p>
			<label for="<?php echo $this->get_field_id('sortby'); ?>"><?php _e( 'Sort by:' ); ?></label>
			<select name="<?php echo $this->get_field_name('sortby'); ?>" id="<?php echo $this->get_field_id('sortby'); ?>" class="widefat">
				<option value="post_title"<?php selected( $instance['sortby'], 'post_title' ); ?>><?php _e('Page title'); ?></option>
				<option value="menu_order"<?php selected( $instance['sortby'], 'menu_order' ); ?>><?php _e('Page order'); ?></option>
				<option value="ID"<?php selected( $instance['sortby'], 'ID' ); ?>><?php _e( 'Page ID' ); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('exclude'); ?>"><?php _e( 'Exclude:' ); ?></label> <input type="text" value="<?php echo $exclude; ?>" name="<?php echo $this->get_field_name('exclude'); ?>" id="<?php echo $this->get_field_id('exclude'); ?>" class="widefat" />
			<br />
			<small><?php _e( 'Page IDs, separated by commas.' ); ?></small>
		</p>
		
		<div id="<?php echo $this->get_field_id('title'); ?>-selector" class="selector-container">
			<p><?php __('Display on selected categories and pages only'); ?></p>
				
			<div id="<?php echo $this->get_field_id('title'); ?>-categories" class="select-categories select-content">
				<?php __('Categories'); ?>
				<ul class="selectlist list:category categorychecklist form-no-clear">
					<input type="hidden" name="" value="0" />						
					<?php 
					// Above - hidden input: Allows for an empty term set to be sent. 0 is an invalid Term ID and will be ignored by empty() checks.				
					// Solution for saving checkboxes: http://wordpress.org/support/topic/widget-checkbox-group-not-registering?replies=3
					foreach ($cats as $cat) {
					?>
						
					<li>						 
						<label for="<?php $this->get_field_id( 'checkCats' ) .'[]'; ?>"><?php 
						$option='<input type="checkbox" id="'. $this->get_field_id( 'checkCats' ) .'[]" name="'. $this->get_field_name( 'checkCats' ) .'[]"';
						if (is_array($instance['checkCats'])) {
							foreach ($instance['checkCats'] as $cats) {
								if($cats==$cat->term_id) {
									$option=$option.' checked="checked"';
								}
							}
						}
						$option .= ' value="'.$cat->term_id.'" /> ';
	       	            $option .= $cat->cat_name;
           	        	echo $option;
					  	?></label>
					</li>
				
					<?php
					}				
					?>
				</ul>
			</div>
				
			<div id="<?php echo $this->get_field_id('title'); ?>-pages" class="select-pages select-content">
				<?php __('Pages'); ?>
				<ul class="selectlist list:pages pageschecklist form-no-clear">
					<input type="hidden" name="" value="0" />						
					<?php 
					// Above - hidden input: Allows for an empty term set to be sent. 0 is an invalid Term ID and will be ignored by empty() checks.				
					// Solution for saving checkboxes: http://wordpress.org/support/topic/widget-checkbox-group-not-registering?replies=3
					foreach ($pages as $page) {
					?>
						
					<li>						 
						<label for="<?php $this->get_field_id( 'checkPages' ) .'[]'; ?>"><?php 
						$option='<input type="checkbox" id="'. $this->get_field_id( 'checkPages' ) .'[]" name="'. $this->get_field_name( 'checkPages' ) .'[]"';
						if (is_array($instance['checkPages'])) {
							foreach ($instance['checkPages'] as $pages) {
								if($pages==$page->ID) {
									$option=$option.' checked="checked"';
								}
							}
						}
						$option .= ' value="'.$page->ID.'" /> ';
	       	            $option .= $page->post_title;
           	        	echo $option;
					  	?></label>
					</li>
				
					<?php
					}				
					?>
				</ul>
			</div>
				
		</div>
<?php 
	}

}

/**
 * Links widget class
 *
 * @since 2.8.0
 */
class Flexible_Widget_Links extends WP_Widget {

	function Flexible_Widget_Links() {
		$widget_ops = array('description' => __( "Your blogroll" ) );
		$control_ops = array('width' => 450, 'height' => 350);
		$this->WP_Widget('links', __('Links'), $widget_ops, $control_ops);
	}

	function widget( $args, $instance ) {
		extract($args, EXTR_SKIP);

		$show_description = isset($instance['description']) ? $instance['description'] : false;
		$show_name = isset($instance['name']) ? $instance['name'] : false;
		$show_rating = isset($instance['rating']) ? $instance['rating'] : false;
		$show_images = isset($instance['images']) ? $instance['images'] : true;
		$category = isset($instance['category']) ? $instance['category'] : false;
		$checkCats = $instance['checkCats'];
		$checkPages = $instance['checkPages'];

		if (
		$checkCats !=0 && is_category($checkCats) // ...................... Selected categories only.
		|| ($checkCats !=0 && is_single() && in_category($checkCats)) // .. Single posts in selected categories only.
		|| ($checkPages !=0 && is_page($checkPages)) // ................... Selected pages only.
		|| ($checkCats == 0 && $checkPages == 0 && (is_single()
			|| is_category() 
			|| is_page())) // ............................................. All single posts, pages & categories.
		) {

			if ( is_admin() && !$category ) {
				// Display All Links widget as such in the widgets screen
				echo $before_widget . $before_title. __('All Links') . $after_title . $after_widget;
				return;
			}

			$before_widget = preg_replace('/id="[^"]*"/','id="%id"', $before_widget);
			wp_list_bookmarks(apply_filters('widget_links_args', array(
				'title_before' => $before_title, 'title_after' => $after_title,
				'category_before' => $before_widget, 'category_after' => $after_widget,
				'show_images' => $show_images, 'show_description' => $show_description,
				'show_name' => $show_name, 'show_rating' => $show_rating,
				'category' => $category, 'class' => 'linkcat widget'
			)));
		}
	}	

	function update( $new_instance, $old_instance ) {
		$new_instance = (array) $new_instance;
		$instance = array( 'images' => 0, 'name' => 0, 'description' => 0, 'rating' => 0);
		foreach ( $instance as $field => $val ) {
			if ( isset($new_instance[$field]) )
				$instance[$field] = 1;
		}
		$instance['category'] = intval($new_instance['category']);
		$instance['checkCats'] = $new_instance['checkCats'];
		$instance['checkPages'] = $new_instance['checkPages'];

		return $instance;
	}

	function form( $instance ) {

		//Defaults
		$instance = wp_parse_args( (array) $instance, array( 'images' => true, 'name' => true, 'description' => false, 'rating' => false, 'category' => false ) );
		$link_cats = get_terms( 'link_category');
		$checkCats = $intance['checkCats'];
		$checkPages = $intance['checkPages'];
		
		$cats = get_categories('hide_empty=1&orderby=name&order=asc');
		$pages = get_pages('orderby=name&order=asc');
?>
		<p>
		<label for="<?php echo $this->get_field_id('category'); ?>" class="screen-reader-text"><?php _e('Select Link Category'); ?></label>
		<select class="widefat" id="<?php echo $this->get_field_id('category'); ?>" name="<?php echo $this->get_field_name('category'); ?>">
		<option value=""><?php _e('All Links'); ?></option>
		<?php
		foreach ( $link_cats as $link_cat ) {
			echo '<option value="' . intval($link_cat->term_id) . '"'
				. ( $link_cat->term_id == $instance['category'] ? ' selected="selected"' : '' )
				. '>' . $link_cat->name . "</option>\n";
		}
		?>
		</select></p>
		<p>
		<input class="checkbox" type="checkbox" <?php checked($instance['images'], true) ?> id="<?php echo $this->get_field_id('images'); ?>" name="<?php echo $this->get_field_name('images'); ?>" />
		<label for="<?php echo $this->get_field_id('images'); ?>"><?php _e('Show Link Image'); ?></label><br />
		<input class="checkbox" type="checkbox" <?php checked($instance['name'], true) ?> id="<?php echo $this->get_field_id('name'); ?>" name="<?php echo $this->get_field_name('name'); ?>" />
		<label for="<?php echo $this->get_field_id('name'); ?>"><?php _e('Show Link Name'); ?></label><br />
		<input class="checkbox" type="checkbox" <?php checked($instance['description'], true) ?> id="<?php echo $this->get_field_id('description'); ?>" name="<?php echo $this->get_field_name('description'); ?>" />
		<label for="<?php echo $this->get_field_id('description'); ?>"><?php _e('Show Link Description'); ?></label><br />
		<input class="checkbox" type="checkbox" <?php checked($instance['rating'], true) ?> id="<?php echo $this->get_field_id('rating'); ?>" name="<?php echo $this->get_field_name('rating'); ?>" />
		<label for="<?php echo $this->get_field_id('rating'); ?>"><?php _e('Show Link Rating'); ?></label>
		</p>
		
		<div id="<?php echo $this->get_field_id('title'); ?>-selector" class="selector-container">
			<p><?php __('Display on selected categories and pages only'); ?></p>
				
			<div id="<?php echo $this->get_field_id('title'); ?>-categories" class="select-categories select-content">
				<?php __('Categories'); ?>
				<ul class="selectlist list:category categorychecklist form-no-clear">
					<input type="hidden" name="" value="0" />						
					<?php 
					// Above - hidden input: Allows for an empty term set to be sent. 0 is an invalid Term ID and will be ignored by empty() checks.				
					// Solution for saving checkboxes: http://wordpress.org/support/topic/widget-checkbox-group-not-registering?replies=3
					foreach ($cats as $cat) {
					?>
						
					<li>						 
						<label for="<?php $this->get_field_id( 'checkCats' ) .'[]'; ?>"><?php 
						$option='<input type="checkbox" id="'. $this->get_field_id( 'checkCats' ) .'[]" name="'. $this->get_field_name( 'checkCats' ) .'[]"';
						if (is_array($instance['checkCats'])) {
							foreach ($instance['checkCats'] as $cats) {
								if($cats==$cat->term_id) {
									$option=$option.' checked="checked"';
								}
							}
						}
						$option .= ' value="'.$cat->term_id.'" /> ';
	       	            $option .= $cat->cat_name;
           	        	echo $option;
					  	?></label>
					</li>
				
					<?php
					}				
					?>
				</ul>
			</div>
				
			<div id="<?php echo $this->get_field_id('title'); ?>-pages" class="select-pages select-content">
				<?php __('Pages'); ?>
				<ul class="selectlist list:pages pageschecklist form-no-clear">
					<input type="hidden" name="" value="0" />						
					<?php 
					// Above - hidden input: Allows for an empty term set to be sent. 0 is an invalid Term ID and will be ignored by empty() checks.				
					// Solution for saving checkboxes: http://wordpress.org/support/topic/widget-checkbox-group-not-registering?replies=3
					foreach ($pages as $page) {
					?>
						
					<li>						 
						<label for="<?php $this->get_field_id( 'checkPages' ) .'[]'; ?>"><?php 
						$option='<input type="checkbox" id="'. $this->get_field_id( 'checkPages' ) .'[]" name="'. $this->get_field_name( 'checkPages' ) .'[]"';
						if (is_array($instance['checkPages'])) {
							foreach ($instance['checkPages'] as $pages) {
								if($pages==$page->ID) {
									$option=$option.' checked="checked"';
								}
							}
						}
						$option .= ' value="'.$page->ID.'" /> ';
	       	            $option .= $page->post_title;
           	        	echo $option;
					  	?></label>
					</li>
				
					<?php
					}				
					?>
				</ul>
			</div>
				
		</div>
<?php
	}
}

/**
 * Search widget class
 *
 * @since 2.8.0
 */
class Flexible_Widget_Search extends WP_Widget {

	function Flexible_Widget_Search() {
		$widget_ops = array('classname' => 'widget_search', 'description' => __( "A search form for your site") );
		$control_ops = array('width' => 450, 'height' => 350);
		$this->WP_Widget('search', __('Search'), $widget_ops, $control_ops);
	}

	function widget( $args, $instance ) {
		extract($args);
		$title = apply_filters('widget_title', $instance['title'], $instance, $this->id_base);
		$checkCats = $instance['checkCats'];
		$checkPages = $instance['checkPages'];		
		
		if (
		$checkCats !=0 && is_category($checkCats) // ...................... Selected categories only.
		|| ($checkCats !=0 && is_single() && in_category($checkCats)) // .. Single posts in selected categories only.
		|| ($checkPages !=0 && is_page($checkPages)) // ................... Selected pages only.
		|| ($checkCats == 0 && $checkPages == 0 && (is_single()
			|| is_category() 
			|| is_page())) // ............................................. All single posts, pages & categories.
		) {

			echo $before_widget;
			if ( $title )
				echo $before_title . $title . $after_title;

			// Use current theme search form if it exists
			get_search_form();

			echo $after_widget;
		}
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '') );
		$title = $instance['title'];
		$checkCats = $intance['checkCats'];
		$checkPages = $intance['checkPages'];
		
		$cats = get_categories('hide_empty=1&orderby=name&order=asc');
		$pages = get_pages('orderby=name&order=asc');
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
		
		<div id="<?php echo $this->get_field_id('title'); ?>-selector" class="selector-container">
			<p><?php __('Display on selected categories and pages only'); ?></p>
				
			<div id="<?php echo $this->get_field_id('title'); ?>-categories" class="select-categories select-content">
				<?php __('Categories'); ?>
				<ul class="selectlist list:category categorychecklist form-no-clear">
					<input type="hidden" name="" value="0" />						
					<?php 
					// Above - hidden input: Allows for an empty term set to be sent. 0 is an invalid Term ID and will be ignored by empty() checks.				
					// Solution for saving checkboxes: http://wordpress.org/support/topic/widget-checkbox-group-not-registering?replies=3
					foreach ($cats as $cat) {
					?>
						
					<li>						 
						<label for="<?php $this->get_field_id( 'checkCats' ) .'[]'; ?>"><?php 
						$option='<input type="checkbox" id="'. $this->get_field_id( 'checkCats' ) .'[]" name="'. $this->get_field_name( 'checkCats' ) .'[]"';
						if (is_array($instance['checkCats'])) {
							foreach ($instance['checkCats'] as $cats) {
								if($cats==$cat->term_id) {
									$option=$option.' checked="checked"';
								}
							}
						}
						$option .= ' value="'.$cat->term_id.'" /> ';
	       	            $option .= $cat->cat_name;
           	        	echo $option;
					  	?></label>
					</li>
				
					<?php
					}				
					?>
				</ul>
			</div>
				
			<div id="<?php echo $this->get_field_id('title'); ?>-pages" class="select-pages select-content">
				<?php __('Pages'); ?>
				<ul class="selectlist list:pages pageschecklist form-no-clear">
					<input type="hidden" name="" value="0" />						
					<?php 
					// Above - hidden input: Allows for an empty term set to be sent. 0 is an invalid Term ID and will be ignored by empty() checks.				
					// Solution for saving checkboxes: http://wordpress.org/support/topic/widget-checkbox-group-not-registering?replies=3
					foreach ($pages as $page) {
					?>
						
					<li>						 
						<label for="<?php $this->get_field_id( 'checkPages' ) .'[]'; ?>"><?php 
						$option='<input type="checkbox" id="'. $this->get_field_id( 'checkPages' ) .'[]" name="'. $this->get_field_name( 'checkPages' ) .'[]"';
						if (is_array($instance['checkPages'])) {
							foreach ($instance['checkPages'] as $pages) {
								if($pages==$page->ID) {
									$option=$option.' checked="checked"';
								}
							}
						}
						$option .= ' value="'.$page->ID.'" /> ';
	       	            $option .= $page->post_title;
           	        	echo $option;
					  	?></label>
					</li>
				
					<?php
					}				
					?>
				</ul>
			</div>
				
		</div>
<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$new_instance = wp_parse_args((array) $new_instance, array( 'title' => ''));
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['checkCats'] = $new_instance['checkCats'];
		$instance['checkPages'] = $new_instance['checkPages'];
		return $instance;
	}

}

/**
 * Archives widget class
 *
 * @since 2.8.0
 */
class Flexible_Widget_Archives extends WP_Widget {

	function Flexible_Widget_Archives() {
		$widget_ops = array('classname' => 'widget_archive', 'description' => __( 'A monthly archive of your site&#8217;s posts') );
		$control_ops = array('width' => 450, 'height' => 350);
		$this->WP_Widget('archives', __('Archives'), $widget_ops, $control_ops);
	}

	function widget( $args, $instance ) {
		extract($args);
		$c = $instance['count'] ? '1' : '0';
		$d = $instance['dropdown'] ? '1' : '0';
		$title = apply_filters('widget_title', empty($instance['title']) ? __('Archives') : $instance['title'], $instance, $this->id_base);
		$checkCats = $instance['checkCats'];
		$checkPages = $instance['checkPages'];
		
		if (
		$checkCats !=0 && is_category($checkCats) // ...................... Selected categories only.
		|| ($checkCats !=0 && is_single() && in_category($checkCats)) // .. Single posts in selected categories only.
		|| ($checkPages !=0 && is_page($checkPages)) // ................... Selected pages only.
		|| ($checkCats == 0 && $checkPages == 0 && (is_single()
			|| is_category() 
			|| is_page())) // ............................................. All single posts, pages & categories.
		) {

			echo $before_widget;
			if ( $title )
				echo $before_title . $title . $after_title;

			if ( $d ) {
?>
			<select name="archive-dropdown" onchange='document.location.href=this.options[this.selectedIndex].value;'> <option value=""><?php echo esc_attr(__('Select Month')); ?></option> <?php wp_get_archives(apply_filters('widget_archives_dropdown_args', array('type' => 'monthly', 'format' => 'option', 'show_post_count' => $c))); ?> </select>
<?php
			} else {
?>
			<ul>
			<?php wp_get_archives(apply_filters('widget_archives_args', array('type' => 'monthly', 'show_post_count' => $c))); ?>
			</ul>
<?php
			}

			echo $after_widget;
		}
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$new_instance = wp_parse_args( (array) $new_instance, array( 'title' => '', 'count' => 0, 'dropdown' => '') );
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['count'] = $new_instance['count'] ? 1 : 0;
		$instance['dropdown'] = $new_instance['dropdown'] ? 1 : 0;
		$instance['checkCats'] = $new_instance['checkCats'];
		$instance['checkPages'] = $new_instance['checkPages'];

		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'count' => 0, 'dropdown' => '') );
		$title = strip_tags($instance['title']);
		$count = $instance['count'] ? 'checked="checked"' : '';
		$dropdown = $instance['dropdown'] ? 'checked="checked"' : '';
		$checkCats = $intance['checkCats'];
		$checkPages = $intance['checkPages'];
		
		$cats = get_categories('hide_empty=1&orderby=name&order=asc');
		$pages = get_pages('orderby=name&order=asc');
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>
		<p>
			<input class="checkbox" type="checkbox" <?php echo $dropdown; ?> id="<?php echo $this->get_field_id('dropdown'); ?>" name="<?php echo $this->get_field_name('dropdown'); ?>" /> <label for="<?php echo $this->get_field_id('dropdown'); ?>"><?php _e('Display as dropdown'); ?></label>
			<br/>
			<input class="checkbox" type="checkbox" <?php echo $count; ?> id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>" /> <label for="<?php echo $this->get_field_id('count'); ?>"><?php _e('Show post counts'); ?></label>
		</p>
		
		<div id="<?php echo $this->get_field_id('title'); ?>-selector" class="selector-container">
			<p><?php __('Display on selected categories and pages only'); ?></p>
				
			<div id="<?php echo $this->get_field_id('title'); ?>-categories" class="select-categories select-content">
				<?php __('Categories'); ?>
				<ul class="selectlist list:category categorychecklist form-no-clear">
					<input type="hidden" name="" value="0" />						
					<?php 
					// Above - hidden input: Allows for an empty term set to be sent. 0 is an invalid Term ID and will be ignored by empty() checks.				
					// Solution for saving checkboxes: http://wordpress.org/support/topic/widget-checkbox-group-not-registering?replies=3
					foreach ($cats as $cat) {
					?>
						
					<li>						 
						<label for="<?php $this->get_field_id( 'checkCats' ) .'[]'; ?>"><?php 
						$option='<input type="checkbox" id="'. $this->get_field_id( 'checkCats' ) .'[]" name="'. $this->get_field_name( 'checkCats' ) .'[]"';
						if (is_array($instance['checkCats'])) {
							foreach ($instance['checkCats'] as $cats) {
								if($cats==$cat->term_id) {
									$option=$option.' checked="checked"';
								}
							}
						}
						$option .= ' value="'.$cat->term_id.'" /> ';
	       	            $option .= $cat->cat_name;
           	        	echo $option;
					  	?></label>
					</li>
				
					<?php
					}				
					?>
				</ul>
			</div>
				
			<div id="<?php echo $this->get_field_id('title'); ?>-pages" class="select-pages select-content">
				<?php __('Pages'); ?>
				<ul class="selectlist list:pages pageschecklist form-no-clear">
					<input type="hidden" name="" value="0" />						
					<?php 
					// Above - hidden input: Allows for an empty term set to be sent. 0 is an invalid Term ID and will be ignored by empty() checks.				
					// Solution for saving checkboxes: http://wordpress.org/support/topic/widget-checkbox-group-not-registering?replies=3
					foreach ($pages as $page) {
					?>
						
					<li>						 
						<label for="<?php $this->get_field_id( 'checkPages' ) .'[]'; ?>"><?php 
						$option='<input type="checkbox" id="'. $this->get_field_id( 'checkPages' ) .'[]" name="'. $this->get_field_name( 'checkPages' ) .'[]"';
						if (is_array($instance['checkPages'])) {
							foreach ($instance['checkPages'] as $pages) {
								if($pages==$page->ID) {
									$option=$option.' checked="checked"';
								}
							}
						}
						$option .= ' value="'.$page->ID.'" /> ';
	       	            $option .= $page->post_title;
           	        	echo $option;
					  	?></label>
					</li>
				
					<?php
					}				
					?>
				</ul>
			</div>
				
		</div>
<?php
	}
}

/**
 * Meta widget class
 *
 * Displays log in/out, RSS feed links, etc.
 *
 * @since 2.8.0
 */
class Flexible_Widget_Meta extends WP_Widget {

	function Flexible_Widget_Meta() {
		$widget_ops = array('classname' => 'widget_meta', 'description' => __( "Log in/out, admin, feed and WordPress links") );
		$control_ops = array('width' => 450, 'height' => 350);
		$this->WP_Widget('meta', __('Meta'), $widget_ops, $control_ops);
	}

	function widget( $args, $instance ) {
		extract($args);
		$title = apply_filters('widget_title', empty($instance['title']) ? __('Meta') : $instance['title'], $instance, $this->id_base);
		$checkCats = $instance['checkCats'];
		$checkPages = $instance['checkPages'];
		
		if (
		$checkCats !=0 && is_category($checkCats) // ...................... Selected categories only.
		|| ($checkCats !=0 && is_single() && in_category($checkCats)) // .. Single posts in selected categories only.
		|| ($checkPages !=0 && is_page($checkPages)) // ................... Selected pages only.
		|| ($checkCats == 0 && $checkPages == 0 && (is_single()
			|| is_category() 
			|| is_page())) // ............................................. All single posts, pages & categories.
		) {

			echo $before_widget;
			if ( $title )
				echo $before_title . $title . $after_title;
?>
				<ul>
				<?php wp_register(); ?>
				<li><?php wp_loginout(); ?></li>
				<li><a href="<?php bloginfo('rss2_url'); ?>" title="<?php echo esc_attr(__('Syndicate this site using RSS 2.0')); ?>"><?php _e('Entries <abbr title="Really Simple Syndication">RSS</abbr>'); ?></a></li>
				<li><a href="<?php bloginfo('comments_rss2_url'); ?>" title="<?php echo esc_attr(__('The latest comments to all posts in RSS')); ?>"><?php _e('Comments <abbr title="Really Simple Syndication">RSS</abbr>'); ?></a></li>
				<li><a href="http://wordpress.org/" title="<?php echo esc_attr(__('Powered by WordPress, state-of-the-art semantic personal publishing platform.')); ?>">WordPress.org</a></li>
				<?php wp_meta(); ?>
				</ul>
<?php
			echo $after_widget;
		}
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['checkCats'] = $new_instance['checkCats'];
		$instance['checkPages'] = $new_instance['checkPages'];

		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
		$title = strip_tags($instance['title']);
		$checkCats = $intance['checkCats'];
		$checkPages = $intance['checkPages'];
		
		$cats = get_categories('hide_empty=1&orderby=name&order=asc');
		$pages = get_pages('orderby=name&order=asc');
?>
			<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>
		
		<div id="<?php echo $this->get_field_id('title'); ?>-selector" class="selector-container">
			<p><?php __('Display on selected categories and pages only'); ?></p>
				
			<div id="<?php echo $this->get_field_id('title'); ?>-categories" class="select-categories select-content">
				<?php __('Categories'); ?>
				<ul class="selectlist list:category categorychecklist form-no-clear">
					<input type="hidden" name="" value="0" />						
					<?php 
					// Above - hidden input: Allows for an empty term set to be sent. 0 is an invalid Term ID and will be ignored by empty() checks.				
					// Solution for saving checkboxes: http://wordpress.org/support/topic/widget-checkbox-group-not-registering?replies=3
					foreach ($cats as $cat) {
					?>
						
					<li>						 
						<label for="<?php $this->get_field_id( 'checkCats' ) .'[]'; ?>"><?php 
						$option='<input type="checkbox" id="'. $this->get_field_id( 'checkCats' ) .'[]" name="'. $this->get_field_name( 'checkCats' ) .'[]"';
						if (is_array($instance['checkCats'])) {
							foreach ($instance['checkCats'] as $cats) {
								if($cats==$cat->term_id) {
									$option=$option.' checked="checked"';
								}
							}
						}
						$option .= ' value="'.$cat->term_id.'" /> ';
	       	            $option .= $cat->cat_name;
           	        	echo $option;
					  	?></label>
					</li>
				
					<?php
					}				
					?>
				</ul>
			</div>
				
			<div id="<?php echo $this->get_field_id('title'); ?>-pages" class="select-pages select-content">
				<?php __('Pages'); ?>
				<ul class="selectlist list:pages pageschecklist form-no-clear">
					<input type="hidden" name="" value="0" />						
					<?php 
					// Above - hidden input: Allows for an empty term set to be sent. 0 is an invalid Term ID and will be ignored by empty() checks.				
					// Solution for saving checkboxes: http://wordpress.org/support/topic/widget-checkbox-group-not-registering?replies=3
					foreach ($pages as $page) {
					?>
						
					<li>						 
						<label for="<?php $this->get_field_id( 'checkPages' ) .'[]'; ?>"><?php 
						$option='<input type="checkbox" id="'. $this->get_field_id( 'checkPages' ) .'[]" name="'. $this->get_field_name( 'checkPages' ) .'[]"';
						if (is_array($instance['checkPages'])) {
							foreach ($instance['checkPages'] as $pages) {
								if($pages==$page->ID) {
									$option=$option.' checked="checked"';
								}
							}
						}
						$option .= ' value="'.$page->ID.'" /> ';
	       	            $option .= $page->post_title;
           	        	echo $option;
					  	?></label>
					</li>
				
					<?php
					}				
					?>
				</ul>
			</div>
				
		</div>
<?php
	}
}

/**
 * Calendar widget class
 *
 * @since 2.8.0
 */
class Flexible_Widget_Calendar extends WP_Widget {

	function Flexible_Widget_Calendar() {
		$widget_ops = array('classname' => 'widget_calendar', 'description' => __( 'A calendar of your site&#8217;s posts') );
		$control_ops = array('width' => 450, 'height' => 350);
		$this->WP_Widget('calendar', __('Calendar'), $widget_ops, $control_ops);
	}

	function widget( $args, $instance ) {
		extract($args);
		$title = apply_filters('widget_title', empty($instance['title']) ? '&nbsp;' : $instance['title'], $instance, $this->id_base);
		$checkCats = $instance['checkCats'];
		$checkPages = $instance['checkPages'];
		
		if (
		$checkCats !=0 && is_category($checkCats) // ...................... Selected categories only.
		|| ($checkCats !=0 && is_single() && in_category($checkCats)) // .. Single posts in selected categories only.
		|| ($checkPages !=0 && is_page($checkPages)) // ................... Selected pages only.
		|| ($checkCats == 0 && $checkPages == 0 && (is_single()
			|| is_category() 
			|| is_page())) // ............................................. All single posts, pages & categories.
		) {
		
			echo $before_widget;
			if ( $title )
				echo $before_title . $title . $after_title;
			echo '<div id="calendar_wrap">';
			get_calendar();
			echo '</div>';
			echo $after_widget;
		}
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['checkCats'] = $new_instance['checkCats'];
		$instance['checkPages'] = $new_instance['checkPages'];

		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
		$title = strip_tags($instance['title']);
		$checkCats = $intance['checkCats'];
		$checkPages = $intance['checkPages'];
		
		$cats = get_categories('hide_empty=1&orderby=name&order=asc');
		$pages = get_pages('orderby=name&order=asc');
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>
		
		<div id="<?php echo $this->get_field_id('title'); ?>-selector" class="selector-container">
			<p><?php __('Display on selected categories and pages only'); ?></p>
				
			<div id="<?php echo $this->get_field_id('title'); ?>-categories" class="select-categories select-content">
				<?php __('Categories'); ?>
				<ul class="selectlist list:category categorychecklist form-no-clear">
					<input type="hidden" name="" value="0" />						
					<?php 
					// Above - hidden input: Allows for an empty term set to be sent. 0 is an invalid Term ID and will be ignored by empty() checks.				
					// Solution for saving checkboxes: http://wordpress.org/support/topic/widget-checkbox-group-not-registering?replies=3
					foreach ($cats as $cat) {
					?>
						
					<li>						 
						<label for="<?php $this->get_field_id( 'checkCats' ) .'[]'; ?>"><?php 
						$option='<input type="checkbox" id="'. $this->get_field_id( 'checkCats' ) .'[]" name="'. $this->get_field_name( 'checkCats' ) .'[]"';
						if (is_array($instance['checkCats'])) {
							foreach ($instance['checkCats'] as $cats) {
								if($cats==$cat->term_id) {
									$option=$option.' checked="checked"';
								}
							}
						}
						$option .= ' value="'.$cat->term_id.'" /> ';
	       	            $option .= $cat->cat_name;
           	        	echo $option;
					  	?></label>
					</li>
				
					<?php
					}				
					?>
				</ul>
			</div>
				
			<div id="<?php echo $this->get_field_id('title'); ?>-pages" class="select-pages select-content">
				<?php __('Pages'); ?>
				<ul class="selectlist list:pages pageschecklist form-no-clear">
					<input type="hidden" name="" value="0" />						
					<?php 
					// Above - hidden input: Allows for an empty term set to be sent. 0 is an invalid Term ID and will be ignored by empty() checks.				
					// Solution for saving checkboxes: http://wordpress.org/support/topic/widget-checkbox-group-not-registering?replies=3
					foreach ($pages as $page) {
					?>
						
					<li>						 
						<label for="<?php $this->get_field_id( 'checkPages' ) .'[]'; ?>"><?php 
						$option='<input type="checkbox" id="'. $this->get_field_id( 'checkPages' ) .'[]" name="'. $this->get_field_name( 'checkPages' ) .'[]"';
						if (is_array($instance['checkPages'])) {
							foreach ($instance['checkPages'] as $pages) {
								if($pages==$page->ID) {
									$option=$option.' checked="checked"';
								}
							}
						}
						$option .= ' value="'.$page->ID.'" /> ';
	       	            $option .= $page->post_title;
           	        	echo $option;
					  	?></label>
					</li>
				
					<?php
					}				
					?>
				</ul>
			</div>
				
		</div>
<?php
	}
}

/**
 * Text widget class
 *
 * @since 2.8.0
 */
class Flexible_Widget_Text extends WP_Widget {

	function Flexible_Widget_Text() {
		$widget_ops = array('classname' => 'widget_text', 'description' => __('Arbitrary text or HTML'));
		$control_ops = array('width' => 400, 'height' => 350);
		$this->WP_Widget('text', __('Text'), $widget_ops, $control_ops);
	}

	function widget( $args, $instance ) {
		extract($args);
		$title = apply_filters( 'widget_title', empty($instance['title']) ? '' : $instance['title'], $instance, $this->id_base);
		$text = apply_filters( 'widget_text', $instance['text'], $instance );
		$checkCats = $instance['checkCats'];
		$checkPages = $instance['checkPages'];
		
		if (
		$checkCats !=0 && is_category($checkCats) // ...................... Selected categories only.
		|| ($checkCats !=0 && is_single() && in_category($checkCats)) // .. Single posts in selected categories only.
		|| ($checkPages !=0 && is_page($checkPages)) // ................... Selected pages only.
		|| ($checkCats == 0 && $checkPages == 0 && (is_single()
			|| is_category() 
			|| is_page())) // ............................................. All single posts, pages & categories.
		) {
		
			echo $before_widget;
			if ( !empty( $title ) ) { echo $before_title . $title . $after_title; } ?>
				<div class="textwidget"><?php echo $instance['filter'] ? wpautop($text) : $text; ?></div>
			<?php
			echo $after_widget;
		}
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		if ( current_user_can('unfiltered_html') )
			$instance['text'] =  $new_instance['text'];
		else
			$instance['text'] = stripslashes( wp_filter_post_kses( addslashes($new_instance['text']) ) ); // wp_filter_post_kses() expects slashed
		$instance['filter'] = isset($new_instance['filter']);
		$instance['checkCats'] = $new_instance['checkCats'];
		$instance['checkPages'] = $new_instance['checkPages'];
		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'text' => '' ) );
		$title = strip_tags($instance['title']);
		$text = esc_textarea($instance['text']);
		$checkCats = $intance['checkCats'];
		$checkPages = $intance['checkPages'];
		
		$cats = get_categories('hide_empty=1&orderby=name&order=asc');
		$pages = get_pages('orderby=name&order=asc');
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>

		<textarea class="widefat" rows="16" cols="20" id="<?php echo $this->get_field_id('text'); ?>" name="<?php echo $this->get_field_name('text'); ?>"><?php echo $text; ?></textarea>

		<p><input id="<?php echo $this->get_field_id('filter'); ?>" name="<?php echo $this->get_field_name('filter'); ?>" type="checkbox" <?php checked(isset($instance['filter']) ? $instance['filter'] : 0); ?> />&nbsp;<label for="<?php echo $this->get_field_id('filter'); ?>"><?php _e('Automatically add paragraphs'); ?></label></p>
		
		<div id="<?php echo $this->get_field_id('title'); ?>-selector" class="selector-container">
			<p><?php __('Display on selected categories and pages only'); ?></p>
				
			<div id="<?php echo $this->get_field_id('title'); ?>-categories" class="select-categories select-content">
				<?php __('Categories'); ?>
				<ul class="selectlist list:category categorychecklist form-no-clear">
					<input type="hidden" name="" value="0" />						
					<?php 
					// Above - hidden input: Allows for an empty term set to be sent. 0 is an invalid Term ID and will be ignored by empty() checks.				
					// Solution for saving checkboxes: http://wordpress.org/support/topic/widget-checkbox-group-not-registering?replies=3
					foreach ($cats as $cat) {
					?>
						
					<li>						 
						<label for="<?php $this->get_field_id( 'checkCats' ) .'[]'; ?>"><?php 
						$option='<input type="checkbox" id="'. $this->get_field_id( 'checkCats' ) .'[]" name="'. $this->get_field_name( 'checkCats' ) .'[]"';
						if (is_array($instance['checkCats'])) {
							foreach ($instance['checkCats'] as $cats) {
								if($cats==$cat->term_id) {
									$option=$option.' checked="checked"';
								}
							}
						}
						$option .= ' value="'.$cat->term_id.'" /> ';
	       	            $option .= $cat->cat_name;
           	        	echo $option;
					  	?></label>
					</li>
				
					<?php
					}				
					?>
				</ul>
			</div>
				
			<div id="<?php echo $this->get_field_id('title'); ?>-pages" class="select-pages select-content">
				<?php __('Pages'); ?>
				<ul class="selectlist list:pages pageschecklist form-no-clear">
					<input type="hidden" name="" value="0" />						
					<?php 
					// Above - hidden input: Allows for an empty term set to be sent. 0 is an invalid Term ID and will be ignored by empty() checks.				
					// Solution for saving checkboxes: http://wordpress.org/support/topic/widget-checkbox-group-not-registering?replies=3
					foreach ($pages as $page) {
					?>
						
					<li>						 
						<label for="<?php $this->get_field_id( 'checkPages' ) .'[]'; ?>"><?php 
						$option='<input type="checkbox" id="'. $this->get_field_id( 'checkPages' ) .'[]" name="'. $this->get_field_name( 'checkPages' ) .'[]"';
						if (is_array($instance['checkPages'])) {
							foreach ($instance['checkPages'] as $pages) {
								if($pages==$page->ID) {
									$option=$option.' checked="checked"';
								}
							}
						}
						$option .= ' value="'.$page->ID.'" /> ';
	       	            $option .= $page->post_title;
           	        	echo $option;
					  	?></label>
					</li>
				
					<?php
					}				
					?>
				</ul>
			</div>
				
		</div>
<?php
	}
}

/**
 * Categories widget class
 *
 * @since 2.8.0
 */
class Flexible_Widget_Categories extends WP_Widget {

	function Flexible_Widget_Categories() {
		$widget_ops = array( 'classname' => 'widget_categories', 'description' => __( "A list or dropdown of categories" ) );
		$control_ops = array('width' => 450, 'height' => 350);
		$this->WP_Widget('categories', __('Categories'), $widget_ops, $control_ops);
	}

	function widget( $args, $instance ) {
		extract( $args );

		$title = apply_filters('widget_title', empty( $instance['title'] ) ? __( 'Categories' ) : $instance['title'], $instance, $this->id_base);
		$c = $instance['count'] ? '1' : '0';
		$h = $instance['hierarchical'] ? '1' : '0';
		$d = $instance['dropdown'] ? '1' : '0';
		$checkCats = $instance['checkCats'];
		$checkPages = $instance['checkPages'];
		
		if (
		$checkCats !=0 && is_category($checkCats) // ...................... Selected categories only.
		|| ($checkCats !=0 && is_single() && in_category($checkCats)) // .. Single posts in selected categories only.
		|| ($checkPages !=0 && is_page($checkPages)) // ................... Selected pages only.
		|| ($checkCats == 0 && $checkPages == 0 && (is_single()
			|| is_category() 
			|| is_page())) // ............................................. All single posts, pages & categories.
		) {

			echo $before_widget;
			if ( $title )
		 		echo $before_title . $title . $after_title;

			$cat_args = array('orderby' => 'name', 'show_count' => $c, 'hierarchical' => $h);

			if ( $d ) {
				$cat_args['show_option_none'] = __('Select Category');
				wp_dropdown_categories(apply_filters('widget_categories_dropdown_args', $cat_args));
?>

<script type='text/javascript'>
/* <![CDATA[ */
	var dropdown = document.getElementById("cat");
	function onCatChange() {
		if ( dropdown.options[dropdown.selectedIndex].value > 0 ) {
			location.href = "<?php echo home_url(); ?>/?cat="+dropdown.options[dropdown.selectedIndex].value;
		}
	}
	dropdown.onchange = onCatChange;
/* ]]> */
</script>

<?php
			} else {
?>
			<ul>
<?php
			$cat_args['title_li'] = '';
			wp_list_categories(apply_filters('widget_categories_args', $cat_args));
?>
			</ul>
<?php
			}

			echo $after_widget;
		}
	}	

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['count'] = !empty($new_instance['count']) ? 1 : 0;
		$instance['hierarchical'] = !empty($new_instance['hierarchical']) ? 1 : 0;
		$instance['dropdown'] = !empty($new_instance['dropdown']) ? 1 : 0;
		$instance['checkCats'] = $new_instance['checkCats'];
		$instance['checkPages'] = $new_instance['checkPages'];

		return $instance;
	}

	function form( $instance ) {
		//Defaults
		$instance = wp_parse_args( (array) $instance, array( 'title' => '') );
		$title = esc_attr( $instance['title'] );
		$count = isset($instance['count']) ? (bool) $instance['count'] :false;
		$hierarchical = isset( $instance['hierarchical'] ) ? (bool) $instance['hierarchical'] : false;
		$dropdown = isset( $instance['dropdown'] ) ? (bool) $instance['dropdown'] : false;
		$checkCats = $intance['checkCats'];
		$checkPages = $intance['checkPages'];
		
		$cats = get_categories('hide_empty=1&orderby=name&order=asc');
		$pages = get_pages('orderby=name&order=asc');
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>

		<p><input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('dropdown'); ?>" name="<?php echo $this->get_field_name('dropdown'); ?>"<?php checked( $dropdown ); ?> />
		<label for="<?php echo $this->get_field_id('dropdown'); ?>"><?php _e( 'Display as dropdown' ); ?></label><br />

		<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>"<?php checked( $count ); ?> />
		<label for="<?php echo $this->get_field_id('count'); ?>"><?php _e( 'Show post counts' ); ?></label><br />

		<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('hierarchical'); ?>" name="<?php echo $this->get_field_name('hierarchical'); ?>"<?php checked( $hierarchical ); ?> />
		<label for="<?php echo $this->get_field_id('hierarchical'); ?>"><?php _e( 'Show hierarchy' ); ?></label></p>
		
		<div id="<?php echo $this->get_field_id('title'); ?>-selector" class="selector-container">
			<p><?php __('Display on selected categories and pages only'); ?></p>
				
			<div id="<?php echo $this->get_field_id('title'); ?>-categories" class="select-categories select-content">
				<?php __('Categories'); ?>
				<ul class="selectlist list:category categorychecklist form-no-clear">
					<input type="hidden" name="" value="0" />						
					<?php 
					// Above - hidden input: Allows for an empty term set to be sent. 0 is an invalid Term ID and will be ignored by empty() checks.				
					// Solution for saving checkboxes: http://wordpress.org/support/topic/widget-checkbox-group-not-registering?replies=3
					foreach ($cats as $cat) {
					?>
						
					<li>						 
						<label for="<?php $this->get_field_id( 'checkCats' ) .'[]'; ?>"><?php 
						$option='<input type="checkbox" id="'. $this->get_field_id( 'checkCats' ) .'[]" name="'. $this->get_field_name( 'checkCats' ) .'[]"';
						if (is_array($instance['checkCats'])) {
							foreach ($instance['checkCats'] as $cats) {
								if($cats==$cat->term_id) {
									$option=$option.' checked="checked"';
								}
							}
						}
						$option .= ' value="'.$cat->term_id.'" /> ';
	       	            $option .= $cat->cat_name;
           	        	echo $option;
					  	?></label>
					</li>
				
					<?php
					}				
					?>
				</ul>
			</div>
				
			<div id="<?php echo $this->get_field_id('title'); ?>-pages" class="select-pages select-content">
				<?php __('Pages'); ?>
				<ul class="selectlist list:pages pageschecklist form-no-clear">
					<input type="hidden" name="" value="0" />						
					<?php 
					// Above - hidden input: Allows for an empty term set to be sent. 0 is an invalid Term ID and will be ignored by empty() checks.				
					// Solution for saving checkboxes: http://wordpress.org/support/topic/widget-checkbox-group-not-registering?replies=3
					foreach ($pages as $page) {
					?>
						
					<li>						 
						<label for="<?php $this->get_field_id( 'checkPages' ) .'[]'; ?>"><?php 
						$option='<input type="checkbox" id="'. $this->get_field_id( 'checkPages' ) .'[]" name="'. $this->get_field_name( 'checkPages' ) .'[]"';
						if (is_array($instance['checkPages'])) {
							foreach ($instance['checkPages'] as $pages) {
								if($pages==$page->ID) {
									$option=$option.' checked="checked"';
								}
							}
						}
						$option .= ' value="'.$page->ID.'" /> ';
	       	            $option .= $page->post_title;
           	        	echo $option;
					  	?></label>
					</li>
				
					<?php
					}				
					?>
				</ul>
			</div>
				
		</div>
<?php
	}

}

/**
 * Recent_Posts widget class
 *
 * @since 2.8.0
 */
class Flexible_Widget_Recent_Posts extends WP_Widget {

	function Flexible_Widget_Recent_Posts() {
		$widget_ops = array('classname' => 'widget_recent_entries', 'description' => __( "The most recent posts on your site") );
		$control_ops = array('width' => 450, 'height' => 350);
		$this->WP_Widget('recent-posts', __('Recent Posts'), $widget_ops, $control_ops);
		$this->alt_option_name = 'widget_recent_entries';

		add_action( 'save_post', array(&$this, 'flush_widget_cache') );
		add_action( 'deleted_post', array(&$this, 'flush_widget_cache') );
		add_action( 'switch_theme', array(&$this, 'flush_widget_cache') );
	}

	function widget($args, $instance) {
		$cache = wp_cache_get('widget_recent_posts', 'widget');
		$checkCats = $instance['checkCats'];
		$checkPages = $instance['checkPages'];

		if ( !is_array($cache) )
			$cache = array();

		if ( isset($cache[$args['widget_id']]) ) {
			echo $cache[$args['widget_id']];
			return;
		}

		ob_start();
		extract($args);

		$title = apply_filters('widget_title', empty($instance['title']) ? __('Recent Posts') : $instance['title'], $instance, $this->id_base);
		if ( ! $number = absint( $instance['number'] ) )
 			$number = 10;
		
		if (
		$checkCats !=0 && is_category($checkCats) // ...................... Selected categories only.
		|| ($checkCats !=0 && is_single() && in_category($checkCats)) // .. Single posts in selected categories only.
		|| ($checkPages !=0 && is_page($checkPages)) // ................... Selected pages only.
		|| ($checkCats == 0 && $checkPages == 0 && (is_single()
			|| is_category() 
			|| is_page())) // ............................................. All single posts, pages & categories.
		) {

			$r = new WP_Query(array('posts_per_page' => $number, 'nopaging' => 0, 'post_status' => 'publish', 'ignore_sticky_posts' => true));
			if ($r->have_posts()) :
?>
			<?php echo $before_widget; ?>
			<?php if ( $title ) echo $before_title . $title . $after_title; ?>
			<ul>
			<?php  while ($r->have_posts()) : $r->the_post(); ?>
			<li><a href="<?php the_permalink() ?>" title="<?php echo esc_attr(get_the_title() ? get_the_title() : get_the_ID()); ?>"><?php if ( get_the_title() ) the_title(); else the_ID(); ?></a></li>
			<?php endwhile; ?>
			</ul>
			<?php echo $after_widget; ?>
<?php
			// Reset the global $the_post as this query will have stomped on it
			wp_reset_postdata();

			endif;

			$cache[$args['widget_id']] = ob_get_flush();
			wp_cache_set('widget_recent_posts', $cache, 'widget');
		}
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['number'] = (int) $new_instance['number'];
		$instance['checkCats'] = $new_instance['checkCats'];
		$instance['checkPages'] = $new_instance['checkPages'];
		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset($alloptions['widget_recent_entries']) )
			delete_option('widget_recent_entries');

		return $instance;
	}

	function flush_widget_cache() {
		wp_cache_delete('widget_recent_posts', 'widget');
	}

	function form( $instance ) {
		$title = isset($instance['title']) ? esc_attr($instance['title']) : '';
		$number = isset($instance['number']) ? absint($instance['number']) : 5;
		$checkCats = $intance['checkCats'];
		$checkPages = $intance['checkPages'];
		
		$cats = get_categories('hide_empty=1&orderby=name&order=asc');
		$pages = get_pages('orderby=name&order=asc');
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>

		<p><label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of posts to show:'); ?></label>
		<input id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" size="3" /></p>
		
		<div id="<?php echo $this->get_field_id('title'); ?>-selector" class="selector-container">
			<p><?php __('Display on selected categories and pages only'); ?></p>
				
			<div id="<?php echo $this->get_field_id('title'); ?>-categories" class="select-categories select-content">
				<?php __('Categories'); ?>
				<ul class="selectlist list:category categorychecklist form-no-clear">
					<input type="hidden" name="" value="0" />						
					<?php 
					// Above - hidden input: Allows for an empty term set to be sent. 0 is an invalid Term ID and will be ignored by empty() checks.				
					// Solution for saving checkboxes: http://wordpress.org/support/topic/widget-checkbox-group-not-registering?replies=3
					foreach ($cats as $cat) {
					?>
						
					<li>						 
						<label for="<?php $this->get_field_id( 'checkCats' ) .'[]'; ?>"><?php 
						$option='<input type="checkbox" id="'. $this->get_field_id( 'checkCats' ) .'[]" name="'. $this->get_field_name( 'checkCats' ) .'[]"';
						if (is_array($instance['checkCats'])) {
							foreach ($instance['checkCats'] as $cats) {
								if($cats==$cat->term_id) {
									$option=$option.' checked="checked"';
								}
							}
						}
						$option .= ' value="'.$cat->term_id.'" /> ';
	       	            $option .= $cat->cat_name;
           	        	echo $option;
					  	?></label>
					</li>
				
					<?php
					}				
					?>
				</ul>
			</div>
				
			<div id="<?php echo $this->get_field_id('title'); ?>-pages" class="select-pages select-content">
				<?php __('Pages'); ?>
				<ul class="selectlist list:pages pageschecklist form-no-clear">
					<input type="hidden" name="" value="0" />						
					<?php 
					// Above - hidden input: Allows for an empty term set to be sent. 0 is an invalid Term ID and will be ignored by empty() checks.				
					// Solution for saving checkboxes: http://wordpress.org/support/topic/widget-checkbox-group-not-registering?replies=3
					foreach ($pages as $page) {
					?>
						
					<li>						 
						<label for="<?php $this->get_field_id( 'checkPages' ) .'[]'; ?>"><?php 
						$option='<input type="checkbox" id="'. $this->get_field_id( 'checkPages' ) .'[]" name="'. $this->get_field_name( 'checkPages' ) .'[]"';
						if (is_array($instance['checkPages'])) {
							foreach ($instance['checkPages'] as $pages) {
								if($pages==$page->ID) {
									$option=$option.' checked="checked"';
								}
							}
						}
						$option .= ' value="'.$page->ID.'" /> ';
	       	            $option .= $page->post_title;
           	        	echo $option;
					  	?></label>
					</li>
				
					<?php
					}				
					?>
				</ul>
			</div>
				
		</div>
<?php
	}
}

/**
 * Recent_Comments widget class
 *
 * @since 2.8.0
 */
class Flexible_Widget_Recent_Comments extends WP_Widget {

	function Flexible_Widget_Recent_Comments() {
		$widget_ops = array('classname' => 'widget_recent_comments', 'description' => __( 'The most recent comments' ) );
		$control_ops = array('width' => 450, 'height' => 350);
		$this->WP_Widget('recent-comments', __('Recent Comments'), $widget_ops, $control_ops);
		$this->alt_option_name = 'widget_recent_comments';

		if ( is_active_widget(false, false, $this->id_base) )
			add_action( 'wp_head', array(&$this, 'recent_comments_style') );

		add_action( 'comment_post', array(&$this, 'flush_widget_cache') );
		add_action( 'transition_comment_status', array(&$this, 'flush_widget_cache') );
	}

	function recent_comments_style() {
		if ( ! current_theme_supports( 'widgets' ) // Temp hack #14876
			|| ! apply_filters( 'show_recent_comments_widget_style', true, $this->id_base ) )
			return;
		?>
	<style type="text/css">.recentcomments a{display:inline !important;padding:0 !important;margin:0 !important;}</style>
<?php
	}

	function flush_widget_cache() {
		wp_cache_delete('widget_recent_comments', 'widget');
	}

	function widget( $args, $instance ) {
		global $comments, $comment;

		$cache = wp_cache_get('widget_recent_comments', 'widget');
		$checkCats = $instance['checkCats'];
		$checkPages = $instance['checkPages'];

		if ( ! is_array( $cache ) )
			$cache = array();

		if ( isset( $cache[$args['widget_id']] ) ) {
			echo $cache[$args['widget_id']];
			return;
		}

 		extract($args, EXTR_SKIP);
 		$output = '';
 		$title = apply_filters('widget_title', empty($instance['title']) ? __('Recent Comments') : $instance['title']);

		if ( ! $number = absint( $instance['number'] ) )
 			$number = 5;
		
		if (
		$checkCats !=0 && is_category($checkCats) // ...................... Selected categories only.
		|| ($checkCats !=0 && is_single() && in_category($checkCats)) // .. Single posts in selected categories only.
		|| ($checkPages !=0 && is_page($checkPages)) // ................... Selected pages only.
		|| ($checkCats == 0 && $checkPages == 0 && (is_single()
			|| is_category() 
			|| is_page())) // ............................................. All single posts, pages & categories.
		) {

			$comments = get_comments( array( 'number' => $number, 'status' => 'approve' ) );
			$output .= $before_widget;
			if ( $title )
				$output .= $before_title . $title . $after_title;

			$output .= '<ul id="recentcomments">';
			if ( $comments ) {
				foreach ( (array) $comments as $comment) {
					$output .=  '<li class="recentcomments">' . /* translators: comments widget: 1: comment author, 2: post link */ sprintf(_x('%1$s on %2$s', 'widgets'), get_comment_author_link(), '<a href="' . esc_url( get_comment_link($comment->comment_ID) ) . '">' . get_the_title($comment->comment_post_ID) . '</a>') . '</li>';
				}
 			}
			$output .= '</ul>';
			$output .= $after_widget;

			echo $output;
			$cache[$args['widget_id']] = $output;
			wp_cache_set('widget_recent_comments', $cache, 'widget');
		}
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['number'] = absint( $new_instance['number'] );
		$instance['checkCats'] = $new_instance['checkCats'];
		$instance['checkPages'] = $new_instance['checkPages'];
		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset($alloptions['widget_recent_comments']) )
			delete_option('widget_recent_comments');

		return $instance;
	}

	function form( $instance ) {
		$title = isset($instance['title']) ? esc_attr($instance['title']) : '';
		$number = isset($instance['number']) ? absint($instance['number']) : 5;
		$checkCats = $intance['checkCats'];
		$checkPages = $intance['checkPages'];
		
		$cats = get_categories('hide_empty=1&orderby=name&order=asc');
		$pages = get_pages('orderby=name&order=asc');
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>

		<p><label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of comments to show:'); ?></label>
		<input id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" size="3" /></p>
		
		<div id="<?php echo $this->get_field_id('title'); ?>-selector" class="selector-container">
			<p><?php __('Display on selected categories and pages only'); ?></p>
				
			<div id="<?php echo $this->get_field_id('title'); ?>-categories" class="select-categories select-content">
				<?php __('Categories'); ?>
				<ul class="selectlist list:category categorychecklist form-no-clear">
					<input type="hidden" name="" value="0" />						
					<?php 
					// Above - hidden input: Allows for an empty term set to be sent. 0 is an invalid Term ID and will be ignored by empty() checks.				
					// Solution for saving checkboxes: http://wordpress.org/support/topic/widget-checkbox-group-not-registering?replies=3
					foreach ($cats as $cat) {
					?>
						
					<li>						 
						<label for="<?php $this->get_field_id( 'checkCats' ) .'[]'; ?>"><?php 
						$option='<input type="checkbox" id="'. $this->get_field_id( 'checkCats' ) .'[]" name="'. $this->get_field_name( 'checkCats' ) .'[]"';
						if (is_array($instance['checkCats'])) {
							foreach ($instance['checkCats'] as $cats) {
								if($cats==$cat->term_id) {
									$option=$option.' checked="checked"';
								}
							}
						}
						$option .= ' value="'.$cat->term_id.'" /> ';
	       	            $option .= $cat->cat_name;
           	        	echo $option;
					  	?></label>
					</li>
				
					<?php
					}				
					?>
				</ul>
			</div>
				
			<div id="<?php echo $this->get_field_id('title'); ?>-pages" class="select-pages select-content">
				<?php __('Pages'); ?>
				<ul class="selectlist list:pages pageschecklist form-no-clear">
					<input type="hidden" name="" value="0" />						
					<?php 
					// Above - hidden input: Allows for an empty term set to be sent. 0 is an invalid Term ID and will be ignored by empty() checks.				
					// Solution for saving checkboxes: http://wordpress.org/support/topic/widget-checkbox-group-not-registering?replies=3
					foreach ($pages as $page) {
					?>
						
					<li>						 
						<label for="<?php $this->get_field_id( 'checkPages' ) .'[]'; ?>"><?php 
						$option='<input type="checkbox" id="'. $this->get_field_id( 'checkPages' ) .'[]" name="'. $this->get_field_name( 'checkPages' ) .'[]"';
						if (is_array($instance['checkPages'])) {
							foreach ($instance['checkPages'] as $pages) {
								if($pages==$page->ID) {
									$option=$option.' checked="checked"';
								}
							}
						}
						$option .= ' value="'.$page->ID.'" /> ';
	       	            $option .= $page->post_title;
           	        	echo $option;
					  	?></label>
					</li>
				
					<?php
					}				
					?>
				</ul>
			</div>
				
		</div>
<?php
	}
}

class Flexible_Widget_RSS extends WP_Widget {

	function Flexible_Widget_RSS() {
		$widget_ops = array('classname' => 'flexible_widget_rss', 'description' => __('Posts from an RSS or ATOM feed'));
		$control_ops = array('width' => 450, 'height' => 350);
		$this->WP_Widget('flexible_rss', __('RSS feed'), $widget_ops, $control_ops);
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['feed_url'] = $new_instance['feed_url'];
		$instance['number'] = (int) $new_instance['number'];
		$instance['checkExcerpt'] = $new_instance['checkExcerpt'];
		$instance['checkAuthor'] = $new_instance['checkAuthor'];	
		$instance['checkDate'] = $new_instance['checkDate'];		
		$instance['checkCats'] = $new_instance['checkCats'];
		$instance['checkPages'] = $new_instance['checkPages'];
		
		return $instance;
	}

	function form( $instance ) {
		// $instance = wp_parse_args( (array) $instance, array( 'title' => '', 'text' => '' ) );
		$title = esc_attr($instance['title']);
		$feed_url = esc_attr($instance['feed_url']);
		$checkExcerpt = $instance['checkExcerpt'];
		$checkAuthor = $instance['checkAuthor'];
		$checkDate = $instance['checkDate'];
		$checkCats = $instance['checkCats'];
		$checkPages = $instance['checkPages'];
		if ( !$number = (int) $instance['number'] )
			$number = 4;
		
		$cats = get_categories('hide_empty=1&orderby=name&order=asc');
		$pages = get_pages('orderby=name&order=asc');
?>
		
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Overskrift'); ?>
			<input class="widefat"
			id="<?php echo $this->get_field_id('title'); ?>"
			name="<?php echo $this->get_field_name('title'); ?>"
			type="text"
			value="<?php echo attribute_escape($title); ?>" />
			</label>
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id('feed_url'); ?>"><?php _e('Feed URL'); ?>
			<input class="widefat"
			id="<?php echo $this->get_field_id('feed_url'); ?>"
			name="<?php echo $this->get_field_name('feed_url'); ?>"
			type="text"
			value="<?php echo $feed_url; ?>" />
			</label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Vis'); ?>
				<input
				id="<?php echo $this->get_field_id('number'); ?>"
				name="<?php echo $this->get_field_name('number'); ?>"
				type="text"
				value="<?php echo $number; ?>"
				style="width:30px;" />	
				<?php _e('artikler'); ?>.			
			</label>
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id('checkExcerpt'); ?>">
				<input class="checkbox"
				id="<?php echo $this->get_field_id('checkExcerpt'); ?>"
				name="<?php echo $this->get_field_name('checkExcerpt'); ?>"
				type="checkbox"
				value="excerpt"
				<?php if ( 'excerpt' == $instance['checkExcerpt'] ) echo 'checked="checked"'; ?>
				 />
				<?php _e('Vis uddrag'); ?>
			</label>
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id('checkAuthor'); ?>">
				<input class="checkbox"
				id="<?php echo $this->get_field_id('checkAuthor'); ?>"
				name="<?php echo $this->get_field_name('checkAuthor'); ?>"
				type="checkbox"
				value="author"
				<?php if ( 'author' == $instance['checkAuthor'] ) echo 'checked="checked"'; ?>
				 />
				<?php _e('Vis forfatter'); ?>
			</label>
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id('checkDate'); ?>">
				<input class="checkbox"
				id="<?php echo $this->get_field_id('checkDate'); ?>"
				name="<?php echo $this->get_field_name('checkDate'); ?>"
				type="checkbox"
				value="date"
				<?php if ( 'date' == $instance['checkDate'] ) echo 'checked="checked"'; ?>
				 />
				<?php _e('Vis dato'); ?>
			</label>
		</p>
		
		
		
			<div id="<?php echo $this->get_field_id('title'); ?>-selector" class="selector-container">
				<p><?php __('Display on selected categories and pages only'); ?></p>
				
				<div id="<?php echo $this->get_field_id('title'); ?>-categories" class="select-categories select-content">
					<?php __('Categories'); ?>
					<ul class="selectlist list:category categorychecklist form-no-clear">
						<input type="hidden" name="" value="0" />						
						<?php 
						// Above - hidden input: Allows for an empty term set to be sent. 0 is an invalid Term ID and will be ignored by empty() checks.				
						// Solution for saving checkboxes: http://wordpress.org/support/topic/widget-checkbox-group-not-registering?replies=3
						foreach ($cats as $cat) {
						?>
						
						<li>						 
							<label for="<?php $this->get_field_id( 'checkCats' ) .'[]'; ?>"><?php 
							$option='<input type="checkbox" id="'. $this->get_field_id( 'checkCats' ) .'[]" name="'. $this->get_field_name( 'checkCats' ) .'[]"';
							if (is_array($instance['checkCats'])) {
								foreach ($instance['checkCats'] as $cats) {
									if($cats==$cat->term_id) {
										$option=$option.' checked="checked"';
									}
								}
							}
							$option .= ' value="'.$cat->term_id.'" /> ';
	        	            $option .= $cat->cat_name;
            	        	echo $option;
						  	?></label>
						</li>
				
						<?php
						}				
						?>
					</ul>
				</div>
				
				<div id="<?php echo $this->get_field_id('title'); ?>-pages" class="select-pages select-content">
					<?php __('Pages'); ?>
					<ul class="selectlist list:pages pageschecklist form-no-clear">
						<input type="hidden" name="" value="0" />						
						<?php 
						// Above - hidden input: Allows for an empty term set to be sent. 0 is an invalid Term ID and will be ignored by empty() checks.				
						// Solution for saving checkboxes: http://wordpress.org/support/topic/widget-checkbox-group-not-registering?replies=3
						foreach ($pages as $page) {
						?>
						
						<li>						 
							<label for="<?php $this->get_field_id( 'checkPages' ) .'[]'; ?>"><?php 
							$option='<input type="checkbox" id="'. $this->get_field_id( 'checkPages' ) .'[]" name="'. $this->get_field_name( 'checkPages' ) .'[]"';
							if (is_array($instance['checkPages'])) {
								foreach ($instance['checkPages'] as $pages) {
									if($pages==$page->ID) {
										$option=$option.' checked="checked"';
									}
								}
							}
							$option .= ' value="'.$page->ID.'" /> ';
	        	            $option .= $page->post_title;
            	        	echo $option;
						  	?></label>
						</li>
				
						<?php
						}				
						?>
					</ul>
				</div>
				
			</div>

<?php
	}	

	function widget( $args, $instance ) {
		extract($args, EXTR_SKIP);
		
		include_once(ABSPATH . WPINC . '/rss.php');
		// setlocale(LC_TIME, "danish", "da_DK", "da_DK.iso-8859-1", "da_DK.utf-8");

		// Widget instance variables
		$feed_url = $instance['feed_url'];
		$feed = fetch_rss($feed_url);
		
		$title = empty($instance['title']) ? __('RSS news', 'flexible_rss_widget') : apply_filters('widget_title', $instance['title']);
			
		$checkExcerpt = $instance['checkExcerpt'];
		$checkAuthor = $instance['checkAuthor'];
		$checkDate = $instance['checkDate'];
		$checkCats = $instance['checkCats'];
		$checkPages = $instance['checkPages'];
		$selectCat = (int) $instance['selectCat'];
		if ( !$number = (int) $instance['number'] )
			$number = 4;
		else if ( $number < 1 )
			$number = 1;
		
		$items = array_slice($feed->items, 0, $number);
		
		if (
		$checkCats !=0 && is_category($checkCats) // ............................................... Selected categories only.
		|| ($checkCats !=0 && is_single() && in_category($checkCats)) // ........................... Single posts in selected categories only.
		|| ($checkPages !=0 && is_page($checkPages)) // ............................................ Selected pages only.
		|| ($checkCats == 0 && $checkPages == 0 && (is_single()
			|| is_singular('anbefalinger') 
			|| is_category() 
			|| is_tax('materialetyper') 
			|| is_page())) // ...................................................................... All single posts, pages & categories.
		) {
			echo $before_widget;
			if ( !empty( $title ) ) { echo $before_title . $title . $after_title; } 
			?>
			<div class="rss-widget">
				<ul>				
				<?php if (empty($items)) {
					echo '<li>' . _e('There are no posts in this feed') . '</li>';
				} else {
					foreach ( $items as $item ) { ?>
					<li>
						<h3><a class="title" href='<?php echo $item['link']; ?>'><?php if (empty($item['title'])) {
							__('Untitled');
						} else {
							echo $item['title']; 
						}?></a></h3>	
					<?php
					if ($checkDate == 'date') {
						echo '<span class="rss-date">' . strftime('%e. %b %Y', strtotime($item['pubdate'])) . '</span> ';
					}
					if ($checkAuthor == 'author') {
						?><span class="meta-prep meta-prep-author"><?php _e('Af ') ?></span><?php
						echo '<cite>' . $item['dc']['creator'] . '</cite>';
					}
					if ($checkExcerpt == 'excerpt') {
						echo '<div class="rssSummary">' . string_limit_words($item['description'],20) . '</div>'; // Set # of words to display in excerpt.
					}
					?>
					</li>
					<?php } 
				} ?>
					<li><a class="widget-rss-link" href="<?php echo $feed_url; ?>"><?php echo __('RSS feed') ?></a></li>
				</ul>
			</div>
			<?php
			echo $after_widget;
		
		} // end if
	}
}


/**
 * Tag cloud widget class
 *
 * @since 2.8.0
 */
class Flexible_Widget_Tag_Cloud extends WP_Widget {

	function Flexible_Widget_Tag_Cloud() {
		$widget_ops = array( 'description' => __( "Your most used tags in cloud format") );
		$control_ops = array('width' => 450, 'height' => 350);
		$this->WP_Widget('tag_cloud', __('Tag Cloud'), $widget_ops, $control_ops);
	}

	function widget( $args, $instance ) {
		extract($args);
		$current_taxonomy = $this->_get_current_taxonomy($instance);
		if ( !empty($instance['title']) ) {
			$title = $instance['title'];
		} else {
			if ( 'post_tag' == $current_taxonomy ) {
				$title = __('Tags');
			} else {
				$tax = get_taxonomy($current_taxonomy);
				$title = $tax->labels->name;
			}
		}
		$title = apply_filters('widget_title', $title, $instance, $this->id_base);
		$checkCats = $instance['checkCats'];
		$checkPages = $instance['checkPages'];
		
		if (
		$checkCats !=0 && is_category($checkCats) // ...................... Selected categories only.
		|| ($checkCats !=0 && is_single() && in_category($checkCats)) // .. Single posts in selected categories only.
		|| ($checkPages !=0 && is_page($checkPages)) // ................... Selected pages only.
		|| ($checkCats == 0 && $checkPages == 0 && (is_single()
			|| is_category() 
			|| is_page())) // ............................................. All single posts, pages & categories.
		) {

			echo $before_widget;
			if ( $title )
				echo $before_title . $title . $after_title;
			echo '<div class="tagcloud">';
			wp_tag_cloud( apply_filters('widget_tag_cloud_args', array('taxonomy' => $current_taxonomy) ) );
			echo "</div>\n";
			echo $after_widget;
		}
	}

	function update( $new_instance, $old_instance ) {
		$instance['title'] = strip_tags(stripslashes($new_instance['title']));
		$instance['taxonomy'] = stripslashes($new_instance['taxonomy']);
		$instance['checkCats'] = $new_instance['checkCats'];
		$instance['checkPages'] = $new_instance['checkPages'];
		return $instance;
	}

	function form( $instance ) {
		$current_taxonomy = $this->_get_current_taxonomy($instance);
		$checkCats = $intance['checkCats'];
		$checkPages = $intance['checkPages'];
		
		$cats = get_categories('hide_empty=1&orderby=name&order=asc');
		$pages = get_pages('orderby=name&order=asc');
?>
	<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:') ?></label>
	<input type="text" class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php if (isset ( $instance['title'])) {echo esc_attr( $instance['title'] );} ?>" /></p>
	<p><label for="<?php echo $this->get_field_id('taxonomy'); ?>"><?php _e('Taxonomy:') ?></label>
	<select class="widefat" id="<?php echo $this->get_field_id('taxonomy'); ?>" name="<?php echo $this->get_field_name('taxonomy'); ?>">
	<?php foreach ( get_object_taxonomies('post') as $taxonomy ) :
				$tax = get_taxonomy($taxonomy);
				if ( !$tax->show_tagcloud || empty($tax->labels->name) )
					continue;
	?>
		<option value="<?php echo esc_attr($taxonomy) ?>" <?php selected($taxonomy, $current_taxonomy) ?>><?php echo $tax->labels->name; ?></option>
	<?php endforeach; ?>
	</select></p>
	
		
		<div id="<?php echo $this->get_field_id('title'); ?>-selector" class="selector-container">
			<p><?php __('Display on selected categories and pages only'); ?></p>
				
			<div id="<?php echo $this->get_field_id('title'); ?>-categories" class="select-categories select-content">
				<?php __('Categories'); ?>
				<ul class="selectlist list:category categorychecklist form-no-clear">
					<input type="hidden" name="" value="0" />						
					<?php 
					// Above - hidden input: Allows for an empty term set to be sent. 0 is an invalid Term ID and will be ignored by empty() checks.				
					// Solution for saving checkboxes: http://wordpress.org/support/topic/widget-checkbox-group-not-registering?replies=3
					foreach ($cats as $cat) {
					?>
						
					<li>						 
						<label for="<?php $this->get_field_id( 'checkCats' ) .'[]'; ?>"><?php 
						$option='<input type="checkbox" id="'. $this->get_field_id( 'checkCats' ) .'[]" name="'. $this->get_field_name( 'checkCats' ) .'[]"';
						if (is_array($instance['checkCats'])) {
							foreach ($instance['checkCats'] as $cats) {
								if($cats==$cat->term_id) {
									$option=$option.' checked="checked"';
								}
							}
						}
						$option .= ' value="'.$cat->term_id.'" /> ';
	       	            $option .= $cat->cat_name;
           	        	echo $option;
					  	?></label>
					</li>
				
					<?php
					}				
					?>
				</ul>
			</div>
				
			<div id="<?php echo $this->get_field_id('title'); ?>-pages" class="select-pages select-content">
				<?php __('Pages'); ?>
				<ul class="selectlist list:pages pageschecklist form-no-clear">
					<input type="hidden" name="" value="0" />						
					<?php 
					// Above - hidden input: Allows for an empty term set to be sent. 0 is an invalid Term ID and will be ignored by empty() checks.				
					// Solution for saving checkboxes: http://wordpress.org/support/topic/widget-checkbox-group-not-registering?replies=3
					foreach ($pages as $page) {
					?>
						
					<li>						 
						<label for="<?php $this->get_field_id( 'checkPages' ) .'[]'; ?>"><?php 
						$option='<input type="checkbox" id="'. $this->get_field_id( 'checkPages' ) .'[]" name="'. $this->get_field_name( 'checkPages' ) .'[]"';
						if (is_array($instance['checkPages'])) {
							foreach ($instance['checkPages'] as $pages) {
								if($pages==$page->ID) {
									$option=$option.' checked="checked"';
								}
							}
						}
						$option .= ' value="'.$page->ID.'" /> ';
	       	            $option .= $page->post_title;
           	        	echo $option;
					  	?></label>
					</li>
				
					<?php
					}				
					?>
				</ul>
			</div>
				
		</div>
		
	<?php
	}

	function _get_current_taxonomy($instance) {
		if ( !empty($instance['taxonomy']) && taxonomy_exists($instance['taxonomy']) )
			return $instance['taxonomy'];

		return 'post_tag';
	}
		
}

/**
 * Navigation Menu widget class
 *
 * @since 3.0.0
 */
 class Flexible_Nav_Menu_Widget extends WP_Widget {

	function Flexible_Nav_Menu_Widget() {
		$widget_ops = array( 'description' => __('Use this widget to add one of your custom menus as a widget.') );
		$control_ops = array('width' => 450, 'height' => 350);
		parent::WP_Widget( 'nav_menu', __('Custom Menu'), $widget_ops, $control_ops);
	}

	function widget($args, $instance) {
		// Get menu
		$nav_menu = wp_get_nav_menu_object( $instance['nav_menu'] );
		$checkCats = $instance['checkCats'];
		$checkPages = $instance['checkPages'];

		if ( !$nav_menu )
			return;

		$instance['title'] = apply_filters('widget_title', $instance['title'], $instance, $this->id_base);
		
		if (
		$checkCats !=0 && is_category($checkCats) // ...................... Selected categories only.
		|| ($checkCats !=0 && is_single() && in_category($checkCats)) // .. Single posts in selected categories only.
		|| ($checkPages !=0 && is_page($checkPages)) // ................... Selected pages only.
		|| ($checkCats == 0 && $checkPages == 0 && (is_single()
			|| is_category() 
			|| is_page())) // ............................................. All single posts, pages & categories.
		) {

			echo $args['before_widget'];
	
			if ( !empty($instance['title']) )
				echo $args['before_title'] . $instance['title'] . $args['after_title'];

			wp_nav_menu( array( 'fallback_cb' => '', 'menu' => $nav_menu ) );

			echo $args['after_widget'];
		}
	}

	function update( $new_instance, $old_instance ) {
		$instance['title'] = strip_tags( stripslashes($new_instance['title']) );
		$instance['nav_menu'] = (int) $new_instance['nav_menu'];
		$instance['checkCats'] = $new_instance['checkCats'];
		$instance['checkPages'] = $new_instance['checkPages'];
		return $instance;
	}

	function form( $instance ) {
		$title = isset( $instance['title'] ) ? $instance['title'] : '';
		$nav_menu = isset( $instance['nav_menu'] ) ? $instance['nav_menu'] : '';
		$checkCats = $intance['checkCats'];
		$checkPages = $intance['checkPages'];
		
		$cats = get_categories('hide_empty=1&orderby=name&order=asc');
		$pages = get_pages('orderby=name&order=asc');

		// Get menus
		$menus = get_terms( 'nav_menu', array( 'hide_empty' => false ) );

		// If no menus exists, direct the user to go and create some.
		if ( !$menus ) {
			echo '<p>'. sprintf( __('No menus have been created yet. <a href="%s">Create some</a>.'), admin_url('nav-menus.php') ) .'</p>';
			return;
		}
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:') ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $title; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('nav_menu'); ?>"><?php _e('Select Menu:'); ?></label>
			<select id="<?php echo $this->get_field_id('nav_menu'); ?>" name="<?php echo $this->get_field_name('nav_menu'); ?>">
		<?php
			foreach ( $menus as $menu ) {
				$selected = $nav_menu == $menu->term_id ? ' selected="selected"' : '';
				echo '<option'. $selected .' value="'. $menu->term_id .'">'. $menu->name .'</option>';
			}
		?>
			</select>
		</p>
		
		<div id="<?php echo $this->get_field_id('title'); ?>-selector" class="selector-container">
			<p><?php __('Display on selected categories and pages only'); ?></p>
				
			<div id="<?php echo $this->get_field_id('title'); ?>-categories" class="select-categories select-content">
				<?php __('Categories'); ?>
				<ul class="selectlist list:category categorychecklist form-no-clear">
					<input type="hidden" name="" value="0" />						
					<?php 
					// Above - hidden input: Allows for an empty term set to be sent. 0 is an invalid Term ID and will be ignored by empty() checks.				
					// Solution for saving checkboxes: http://wordpress.org/support/topic/widget-checkbox-group-not-registering?replies=3
					foreach ($cats as $cat) {
					?>
						
					<li>						 
						<label for="<?php $this->get_field_id( 'checkCats' ) .'[]'; ?>"><?php 
						$option='<input type="checkbox" id="'. $this->get_field_id( 'checkCats' ) .'[]" name="'. $this->get_field_name( 'checkCats' ) .'[]"';
						if (is_array($instance['checkCats'])) {
							foreach ($instance['checkCats'] as $cats) {
								if($cats==$cat->term_id) {
									$option=$option.' checked="checked"';
								}
							}
						}
						$option .= ' value="'.$cat->term_id.'" /> ';
	       	            $option .= $cat->cat_name;
           	        	echo $option;
					  	?></label>
					</li>
				
					<?php
					}				
					?>
				</ul>
			</div>
				
			<div id="<?php echo $this->get_field_id('title'); ?>-pages" class="select-pages select-content">
				<?php __('Pages'); ?>
				<ul class="selectlist list:pages pageschecklist form-no-clear">
					<input type="hidden" name="" value="0" />						
					<?php 
					// Above - hidden input: Allows for an empty term set to be sent. 0 is an invalid Term ID and will be ignored by empty() checks.				
					// Solution for saving checkboxes: http://wordpress.org/support/topic/widget-checkbox-group-not-registering?replies=3
					foreach ($pages as $page) {
					?>
						
					<li>						 
						<label for="<?php $this->get_field_id( 'checkPages' ) .'[]'; ?>"><?php 
						$option='<input type="checkbox" id="'. $this->get_field_id( 'checkPages' ) .'[]" name="'. $this->get_field_name( 'checkPages' ) .'[]"';
						if (is_array($instance['checkPages'])) {
							foreach ($instance['checkPages'] as $pages) {
								if($pages==$page->ID) {
									$option=$option.' checked="checked"';
								}
							}
						}
						$option .= ' value="'.$page->ID.'" /> ';
	       	            $option .= $page->post_title;
           	        	echo $option;
					  	?></label>
					</li>
				
					<?php
					}				
					?>
				</ul>
			</div>
				
		</div>
		<?php
	}
}


/**
 * Unregister all of the default WordPress widgets on startup.
 *
 * Calls 'widgets_init' action after all of the WordPress widgets have been
 * registered.
 *
 * @since 2.2.0
 */
function unregister_default_wp_widgets() {
	if ( !is_blog_installed() )
		return;

	unregister_widget('WP_Widget_Pages');

	unregister_widget('WP_Widget_Calendar');

	unregister_widget('WP_Widget_Archives');

	unregister_widget('WP_Widget_Links');

	unregister_widget('WP_Widget_Meta');

	unregister_widget('WP_Widget_Search');

	unregister_widget('WP_Widget_Text');

	unregister_widget('WP_Widget_Categories');

	unregister_widget('WP_Widget_Recent_Posts');

	unregister_widget('WP_Widget_Recent_Comments');

	unregister_widget('WP_Widget_RSS');

	unregister_widget('WP_Widget_Tag_Cloud');

	unregister_widget('WP_Nav_Menu_Widget');

	do_action('widgets_init');
}
add_action( 'init', 'unregister_default_wp_widgets', 1);

/**
 * Register all of the flexible widgets on startup.
 *
 * Calls 'widgets_init' action after all of the WordPress widgets have been
 * registered.
 *
 * @since 2.2.0
 */
function flexible_widgets_init() {
	if ( !is_blog_installed() )
		return;

	register_widget('Flexible_Widget_Pages');

	register_widget('Flexible_Widget_Calendar');

	register_widget('Flexible_Widget_Archives');

	register_widget('Flexible_Widget_Links');

	register_widget('Flexible_Widget_Meta');

	register_widget('Flexible_Widget_Search');

	register_widget('Flexible_Widget_Text');

	register_widget('Flexible_Widget_Categories');

	register_widget('Flexible_Widget_Recent_Posts');

	register_widget('Flexible_Widget_Recent_Comments');

	register_widget('Flexible_Widget_RSS');

	register_widget('Flexible_Widget_Tag_Cloud');

	register_widget('Flexible_Nav_Menu_Widget');

	do_action('widgets_init');
}

add_action('init', 'flexible_widgets_init', 1);

function flexible_widget_admin_style() {

	if ( ! function_exists( 'is_ssl' ) ) {
		function is_ssl() {
			if ( isset($_SERVER['HTTPS']) ) {
				if ( 'on' == strtolower($_SERVER['HTTPS']) )
				return true;
				if ( '1' == $_SERVER['HTTPS'] )
				return true;
			} elseif ( isset($_SERVER['SERVER_PORT']) && ( '443' == $_SERVER['SERVER_PORT'] ) ) {
				return true;
			}
		return false;
		}
	}

	if ( version_compare( get_bloginfo( 'version' ) , '3.0' , '<' ) && is_ssl() ) {
		$wp_content_url = str_replace( 'http://' , 'https://' , get_option( 'siteurl' ) );
	} else {
		$wp_content_url = get_option( 'siteurl' );
	}
	$wp_content_url .= '/wp-content';
	$wp_content_dir = ABSPATH . 'wp-content';
	$wp_plugin_url = $wp_content_url . '/plugins';
	$wp_plugin_dir = $wp_content_dir . '/plugins';
	$wpmu_plugin_url = $wp_content_url . '/mu-plugins';
	$wpmu_plugin_dir = $wp_content_dir . '/mu-plugins';

	wp_register_style('flexible_widget_admin_style',  $wp_plugin_url . '/flexible-widgets/flexible-widgets-admin-styles.css');
	wp_enqueue_style('flexible_widget_admin_style');
}
if (is_admin()) {
	add_action('init', 'flexible_widget_admin_style');
}
