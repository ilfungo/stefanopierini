<?php
/*
Plugin Name: Simple Taxonomies
Version: 1.0
Plugin URI: http://yoast.com/wordpress/simple-taxonomies/
Description: WordPress 2.8 and up allow for reasonably simple custom taxonomies, this plugin makes it even simpler, removing the need for you to write <em>any</em> code.
Author: Joost de Valk
Author URI: http://yoast.com/

Copyright 2009 Joost de Valk (email: joost@yoast.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

if ( ! class_exists( 'YoastSimpleTaxonomies_Admin' ) ) {

	class YoastSimpleTaxonomies_Admin {

		function add_config_page() {
			add_posts_page('Yoast Simple Taxonomies Configuration', 'Simple Taxonomies', 10, basename(__FILE__), array( 'YoastSimpleTaxonomies_Admin', 'config_page' ) );
			add_filter( 'plugin_action_links', array( 'YoastSimpleTaxonomies_Admin', 'filter_plugin_actions' ), 10, 2 );
		}

		function filter_plugin_actions( $links, $file ){
			static $this_plugin;
			if ( ! $this_plugin ) $this_plugin = plugin_basename( __FILE__ );
			
			if ( $file == $this_plugin ){
				$settings_link = '<a href="edit.php?page='.basename( __FILE__ ).'">' . __('Settings') . '</a>';
				array_unshift( $links, $settings_link ); 
			}
			return $links;
		}

		function config_page() {
			if ( isset($_POST['submit']) ) {
				if (!current_user_can( 'manage_options' )) die(__( 'You cannot edit the Yoast Simple Taxonomies options.' ));
				check_admin_referer( 'yoast-simpletax-updatesettings' );
				
				foreach ( array('disablenormaltags') as $val ) {
					if ( isset( $_POST[$val] ) )
						$opt[$val] = true;
					else
						$opt[$val] = false;
				}
				
				foreach ( $_POST['taxonomies'] as $taxonomy ) {
					if ( $taxonomy['name'] != "" ) {
						$tax = array();

						// Lowercase the taxonomy name / slug string
						$taxonomy['name'] = strtolower( $taxonomy['name'] );
						
						foreach ( array('name', 'label') as $val ) {
							$tax[$val] = $taxonomy[$val];
						}

						foreach ( array('filter', 'filterexcerpt') as $val ) {
							if ( isset( $taxonomy[$val] ) ) {
								$tax[$val] = true;
							} else {
								$tax[$val] = false;
							}
						}

						$opt['taxonomies'][] = $tax;
					}
				}
				
				update_option( 'yoast_simpletax', $opt );
				
				echo '<div id="message" class="updated"><p>'.__('Yoast Simple Taxonomies settings updated.').'</p></div>';
			}
			$opt  = get_option( 'yoast_simpletax' );
			?>
			<div class="wrap">
				<?php screen_icon(); ?>
				<h2><?php _e("Yoast Simple Taxonomies Configuration"); ?></h2>
				<form action="" method="post" id="yoastsimpletax-conf">
					<h3>Taxonomy Settings</h3>
					<table class="form-table">
						<?php if (function_exists('wp_nonce_field')) { wp_nonce_field('yoast-simpletax-updatesettings'); } ?>
						<?php 
						$i = 1;
						$taxonomies = $opt['taxonomies'];
						$taxonomies[] = array();
						foreach($taxonomies as $taxonomy) { ?>
						<tr>
							<th scope="row" style="vertical-align:top;width:15%;"><?php if (isset($taxonomy['label'])) { echo '<a id="'.$taxonomy['name'].'" href="'.admin_url("edit-tags.php?taxonomy=".$taxonomy['name']).'">'.$taxonomy['label'].'</a>'; } else { _e("New Taxonomy"); } ?>:</th>
							<td style="vertical-align:top;width:85%;">
								<table class="form-table" width="100%">
									<tr>
										<th style="vertical-align:top;width:35%;">
											<label for="name<?php echo $i; ?>"><?php _e("Name / Slug"); ?>:</label><br/>
											<small>(<?php _e("All lowercase and no weird characters"); ?>)</small>
										</th>
										<td style="vertical-align:top;"><input type="text" name="taxonomies[<?php echo $i ?>][name]" id="name<?php echo $i; ?>" value="<?php echo $taxonomy['name']; ?>"/></td>
									</tr>
									<tr>
										<th style="vertical-align:top;">
											<label for="label<?php echo $i; ?>"><?php _e("Label"); ?>:</label><br/>
											<small>(<?php _e("The label used in the backend, on widgets etc."); ?>)</small>
										</th>
										<td style="vertical-align:top;"><input type="text" name="taxonomies[<?php echo $i ?>][label]" id="label<?php echo $i; ?>" value="<?php echo $taxonomy['label']; ?>"/></td>
									</tr>
									<tr>
										<th style="vertical-align:top;"><label for="filter<?php echo $i; ?>"><?php _e("Add terms to the end of posts"); ?>:</label></th>
										<td><input type="checkbox" name="taxonomies[<?php echo $i ?>][filter]" id="filter<?php echo $i; ?>" <?php if ($taxonomy['filter']) { echo 'checked="checked"'; } ?>/></td>
									</tr>
									<tr>
										<th style="vertical-align:top;"><label for="filterexcerpt<?php echo $i; ?>"><?php _e("Add terms to the end of excerpts"); ?>:</label></th>
										<td><input type="checkbox" name="taxonomies[<?php echo $i ?>][filterexcerpt]" id="filter<?php echo $i; ?>" <?php if ($taxonomy['filterexcerpt']) { echo 'checked="checked"'; } ?>/></td>
									</tr>
								</table>
							</td>
						</tr>
						<?php
							$i++;
						} ?>
					</table>
					<h3>General Settings</h3>
					<table class="form-table">
						<tr>
							<th style="width:60%;">
								<?php _e('Disable the "post_tag" taxonomy?'); ?><br/>
								<small><?php _e('If you have defined other taxonomies, you might think the "normal" tag taxonomy is not needed, this removes it.'); ?></small>
							</th>
							<td>
								<input type="checkbox" name="disablenormaltags" <?php if ($opt['disablenormaltags']) { echo 'checked="checked"'; } ?>/>
							</td>
						</tr>
					</table>
					<br/>
					<span class="submit" style="border: 0;"><input type="submit" name="submit" value="<?php _e("Save Settings"); ?>" /></span>
				</form>
			</div>
<?php		}
	}
}

function yoast_simple_taxonomies() {
	$opt  = get_option( 'yoast_simpletax' );
	if ( is_array( $opt['taxonomies'] ) ) {
		foreach( $opt['taxonomies'] as $taxonomy ) {
			register_taxonomy( $taxonomy['name'], 'post', array( 'hierarchical' => false, 'label' => $taxonomy['label'], 'query_var' => true, 'rewrite' => true ) );
		}		
	}
}

class Yoast_Taxonomy_Widget extends WP_Widget {

	function Yoast_Taxonomy_Widget() {
		$widget_ops = array( 'classname' => 'taxonomy', 'description' => 'A tag cloud for your taxonomy!' );
		$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'taxonomy-widget' );
		$this->WP_Widget( 'taxonomy-widget', 'Yoast Taxonomy Widget', $widget_ops, $control_ops );
	}
	
	function widget( $args, $instance ) {
		extract( $args );
		echo $before_widget;
		$title = apply_filters('widget_title', $instance['title'] );
		if ( $title ) {
			echo $before_title . $title . $after_title;
		}
		if ( $instance['type'] == "cloud" ) {
			wp_tag_cloud( array( 'taxonomy' => $instance['taxonomy'], 'number' => $instance['number'], 'order' => $instance['cloudorder'] ) );
		} else {
			$terms = get_terms( $instance['taxonomy'], 'number='.$instance['number'].'&order='.$instance['listorder'] );
			echo '<ul class="taxonomylist">';
			foreach ($terms as $term) {
				$link = get_term_link( $term, $instance['taxonomy'] );
				echo '<li><a href="'.$link.'">'.$term->name.'</a>';
				if ( $instance['showcount'] ) {
					echo ' ('.$term->count.')';
				}
				echo '</li>';
			}
			echo '</ul>';
		}
		echo $after_widget;
	}
	
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		foreach ( array('title', 'taxonomy', 'number', 'type', 'showcount', 'cloudorder', 'listorder') as $val ) {
			$instance[$val] = strip_tags( $new_instance[$val] );
		}
		return $instance;
	}
	
	function form( $instance ) {
		global $wp_taxonomies;
		
		$opt  = get_option('yoast_simpletax');
		$defaults = array( 
			'title' 		=> 'Taxonomy Tag Cloud', 
			'taxonomy' 		=> $opt[0], 
			'type' 			=> 'cloud', 
			'cloudorder' 	=> 'RAND', 
			'listorder' 	=> 'ASC', 
			'number' 		=> 45,
		);
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e("Title"); ?>:</label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'taxonomy' ); ?>"><?php _e("What to show"); ?>:</label>
			<select id="<?php echo $this->get_field_id( 'taxonomy' ); ?>" name="<?php echo $this->get_field_name( 'taxonomy' ); ?>" style="width:100%;">
				<?php foreach ($wp_taxonomies as $taxonomy) {
					if ( in_array($taxonomy->name, array('category', 'link_category') ) == false ) {
						$sel = '';
						if ($instance['taxonomy'] == $taxonomy->name)
							$sel = 'selected="selected"';
						echo '<option '.$sel.' value="'.$taxonomy->name.'">'.$taxonomy->label.'</option>';						
					}
				} ?>
			</select>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'type' ); ?>"><?php _e("How to show it"); ?>:</label>
			<select id="<?php echo $this->get_field_id( 'type' ); ?>" name="<?php echo $this->get_field_name( 'type' ); ?>" style="width:100%;">
				<?php foreach( array('cloud', 'list') as $option) {
					$sel = '';
					if ($instance['type'] == $option)
						$sel = 'selected="selected"';					
					echo '<option '.$sel.' value="'.$option.'">'.ucfirst($option).'</option>';
				} ?>
			</select>
		</p>

		<?php if ( $instance['type'] == 'cloud' ) { ?>
		<p>
			<label for="<?php echo $this->get_field_id( 'cloudorder' ); ?>"><?php _e("Order within cloud"); ?>:</label>
			<select id="<?php echo $this->get_field_id( 'cloudorder' ); ?>" name="<?php echo $this->get_field_name( 'cloudorder' ); ?>" style="width:100%;">
				<?php foreach( array('RAND' => 'Random', 'ASC' => 'Ascending', 'DESC' => 'Descending') as $optval => $option) {
					$sel = '';
					if ($instance['cloudorder'] == $optval)
						$sel = 'selected="selected"';					
					echo '<option '.$sel.' value="'.$optval.'">'.$option.'</option>';
				} ?>
			</select>
		</p>
		<?php } ?>
		
		<?php if ( $instance['type'] == 'list' ) { ?>
		<p>
			<label for="<?php echo $this->get_field_id( 'listorder' ); ?>"><?php _e("Order within list"); ?>:</label>
			<select id="<?php echo $this->get_field_id( 'listorder' ); ?>" name="<?php echo $this->get_field_name( 'listorder' ); ?>" style="width:100%;">
				<?php foreach( array('ASC' => 'Ascending', 'DESC' => 'Descending') as $optval => $option) {
					$sel = '';
					if ($instance['listorder'] == $optval)
						$sel = 'selected="selected"';					
					echo '<option '.$sel.' value="'.$optval.'">'.$option.'</option>';
				} ?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'showcount' ); ?>"><?php _e("Show post count in list"); ?>:</label>
			<input type="checkbox" id="<?php echo $this->get_field_id( 'showcount' ); ?>" name="<?php echo $this->get_field_name( 'showcount' ); ?>" <?php if ( $instance['showcount'] ) { echo 'checked="checked"'; } ?> style="width:100%;"/>
		</p>
		<?php } ?>

		<p>
			<label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e("Number of tags to show"); ?>:</label>
			<input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" value="<?php echo $instance['number']; ?>" style="width:100%;" />
		</p>
	<?php 
	}
}

function yoast_simple_taxonomies_widget() {
	register_widget( 'Yoast_Taxonomy_Widget' );
}

function yoast_simple_taxonomies_filter( $content, $type ) {
	$opt  = get_option('yoast_simpletax');
	$output = '';
	foreach ( $opt['taxonomies'] as $taxonomy ) {
		$filter = false;
		if ( $type == 'content' && $taxonomy['filter'] ) {
			$filter = true;
		} else if ( $type == 'excerpt' && $taxonomy['filterexcerpt'] ) {
			$filter = true;
		}
		if ( $filter ) {
			$terms = get_the_term_list( $post->ID, $taxonomy['name'], $taxonomy['label'].': ', ', ', '' );
			if ($terms)
				$output .= "\t".'<span class="taxonomy-'.$taxonomy['name'].'">'.$terms."</span><br/>\n";			
		}
	}
	if ($output != '') {
		$content .= '<div id="yoast-taxonomy">'."\n".$output."\n".'</div>'."\n";
	}
	return $content;
}

function yoast_simple_taxonomies_content_filter( $content ) {
	return yoast_simple_taxonomies_filter( $content, 'content' );
}

function yoast_simple_taxonomies_excerpt_filter( $content ) {
	return yoast_simple_taxonomies_filter( $content, 'excerpt' );
}

function get_yoast_term_title( $prefix = false ) {
	if ( !is_tax() )
		return;
	$taxonomy 	= get_taxonomy ( get_query_var('taxonomy') );
	$term 		= get_query_var('term');
	if ( $prefix )
		return $taxonomy->label .': '.ucwords($term);
	else
		return ucwords($term);
}

function yoast_term_title() {
	echo get_yoast_term_title();
}

function get_yoast_term_description() {
	if ( !is_tax() )
		return;
	$term = get_term_by( 'slug', get_query_var('term'), get_query_var('taxonomy') );
	return $term->description;	
}

function yoast_term_description() {
	echo '<p>'.get_yoast_term_description().'</p>';
}

function yoast_taxonomy_edit_message() {
	$opt  = get_option('yoast_simpletax');
	$output = '';
	foreach ( $opt['taxonomies'] as $tax ) {
		if ( $tax['name'] == $_GET['taxonomy'] ) {
			echo '<div id="message" class="updated">';
			echo '<p><strong>'.__('Just a reminder').'</strong>: '.__('this taxonomy was created with the Yoast Simple Taxonomies plugin, if you want, you can go').' <a href="'.admin_url('edit.php?page='.basename(__FILE__)).'#'.$tax['name'].'">'.__('edit its settings').'</a>.</p>';
			echo '</div><br/>';
		}
	}
}

function yoast_remove_tags() {
	$opt  = get_option('yoast_simpletax');
	if ( $opt['disablenormaltags'] )
		register_taxonomy( 'post_tag', 'page', array( 'hierarchical' => false, 'label' => false, 'query_var' => false, 'rewrite' => false ) );
}

add_action( 'init', 'yoast_remove_tags', 0 );
add_action( 'init', 'yoast_simple_taxonomies', 0 );
add_action( 'widgets_init', 'yoast_simple_taxonomies_widget' );

add_filter(	'the_excerpt', 'yoast_simple_taxonomies_excerpt_filter', 10, 1 );
add_filter(	'the_content', 'yoast_simple_taxonomies_content_filter', 10, 1 );

add_action( 'admin_menu', array( 'YoastSimpleTaxonomies_Admin', 'add_config_page' ) );
add_action(	'add_tag_form_pre','yoast_taxonomy_edit_message', 10, 1 );
?>