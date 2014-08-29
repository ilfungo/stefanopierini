<?php get_header(); ?>

<div id="main-content">
	<div class="container">
		<div id="content-area" class="clearfix">
			<div id="left-area">
			<?php while ( have_posts() ) : the_post(); ?>
				<?php if (et_get_option('divi_integration_single_bottom') <> '' && et_get_option('divi_integrate_singlebottom_enable') == 'on') echo(et_get_option('divi_integration_single_bottom')); ?>

				<article id="post-<?php the_ID(); ?>" <?php post_class( 'et_pb_post' ); ?>>
					<h1><?php the_title(); ?></h1>

				<?php
					et_divi_post_meta();

					$thumb = '';

					$width = (int) apply_filters( 'et_pb_index_blog_image_width', 1080 );

					$height = (int) apply_filters( 'et_pb_index_blog_image_height', 675 );
					$classtext = 'et_featured_image';
					$titletext = get_the_title();
					$thumbnail = get_thumbnail( $width, $height, $classtext, $titletext, $titletext, false, 'Blogimage' );
					$thumb = $thumbnail["thumb"];

					if ( 'on' === et_get_option( 'divi_thumbnails', 'on' ) && '' !== $thumb )
						print_thumbnail( $thumb, $thumbnail["use_timthumb"], $titletext, $width, $height );
				?>
					<div class="entry-content">
					Anno: <?php the_field('anno'); ?>
					<br />
<?php
//print_r($post);
$postID=$post->ID;
$edizione = get_field('edizione');
if( $edizione ): 
	//override $post
	//print_r($edizione);
	//$post = $edizione;
	//setup_postdata( $post ); 
	?>
	<ul><li>Edizione: <a href="<?php echo $edizione->guid ?>"><?php echo $edizione->post_title; ?></a></li></ul>
	<?
endif
?>		
<br />
<?//progetto?>
<?php
 
/*
*  Loop through post objects (assuming this is a multi-select field) ( don't setup postdata )
*  Using this method, the $post object is never changed so all functions need a seccond parameter of the post ID in question.
*/
 
$post_objects = get_field('progetto');
 
if( $post_objects ): ?>
    <ul>
    <?php foreach( $post_objects as $post_object): ?>
        <li>
            <a href="<?php echo get_permalink($post_object->ID); ?>"><?php echo get_the_title($post_object->ID); ?></a>
        </li>
    <?php endforeach; ?>
    </ul>
<?php endif;
 
?>
<?//progetto?>
<?php
 
/*
*  Loop through post objects (assuming this is a multi-select field) ( setup postdata )
*  Using this method, you can use all the normal WP functions as the $post object is temporarily initialized within the loop
*  Read more: http://codex.wordpress.org/Template_Tags/get_posts#Reset_after_Postlists_with_offset
*/
 
$post_objects = get_field('esecuzione');
foreach( $post_objects as $post): 
	$my_order[]=array(get_field('data'),$post->ID);
endforeach;

sort($my_order);
//print_r($my_order);
//print_r($post_objects);
if( $post_objects ): ?>
    <ul>
    <?php foreach( $my_order as $key=>$value):?>
		<?php foreach( $post_objects as $post_object): 
		if($post_object->ID==$value[1]){
		?>
        <?php //echo "value".$post_object->ID;
		      setup_postdata($post_object); 
			  //print_r($post_object);?>
        <li>
			<?$date = DateTime::createFromFormat('Ymd', get_field('data',$post_object->ID));?>
            <span><?php echo $date->format('d/m/Y'); ?> - </span> <a href="<?php echo get_permalink( $post_object->ID ); ?>"><?php echo get_the_title( $post_object->ID ); ?></a>
        </li>
	<?php 
	      }
		  endforeach; ?>
    <?php endforeach; ?>
    </ul>
    <?php wp_reset_postdata(); // IMPORTANT - reset the $post object so the rest of the page works correctly ?>
<?php endif;?>

<?//esecuzione?>


					
					<?php
					    $this_post = get_post($postID);
						$post = $this_post;
						setup_postdata( $post ); 
						the_content();

						wp_link_pages( array( 'before' => '<div class="page-links">' . __( 'Pages:', 'Divi' ), 'after' => '</div>' ) );
					?>
					</div> <!-- .entry-content -->
					
					<?php 
					$terms = get_field('audio'); 
					//print_r($terms);
					if( $terms ): ?>
						<?php foreach( $terms as $term ): 
						//echo $term;
						$audio = get_term( $term, 'audio');
						//print_r($audio);   
						$term = get_term( $audio->term_taxonomy_id, "audio" );?>
						<iframe width="100%" height="450" scrolling="no" frameborder="no" src="https://w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/tracks/<?the_field('code', $term);?>&amp;auto_play=false&amp;hide_related=false&amp;show_comments=true&amp;show_user=true&amp;show_reposts=false&amp;visual=true"></iframe>
						<?php endforeach; ?>
					<?php endif; ?>					

					<?php
					if ( et_get_option('divi_468_enable') == 'on' ){
						echo '<div class="et-single-post-ad">';
						if ( et_get_option('divi_468_adsense') <> '' ) echo( et_get_option('divi_468_adsense') );
						else { ?>
							<a href="<?php echo esc_url(et_get_option('divi_468_url')); ?>"><img src="<?php echo esc_attr(et_get_option('divi_468_image')); ?>" alt="468 ad" class="foursixeight" /></a>
				<?php 	}
						echo '</div> <!-- .et-single-post-ad -->';
					}
				?>

					<?php
						if ( comments_open() && 'on' == et_get_option( 'divi_show_postcomments', 'on' ) )
							comments_template( '', true );
					?>
				</article> <!-- .et_pb_post -->

				<?php if (et_get_option('divi_integration_single_bottom') <> '' && et_get_option('divi_integrate_singlebottom_enable') == 'on') echo(et_get_option('divi_integration_single_bottom')); ?>
			<?php endwhile; ?>
			</div> <!-- #left-area -->

			<?php get_sidebar(); ?>
		</div> <!-- #content-area -->
	</div> <!-- .container -->
</div> <!-- #main-content -->

<?php get_footer(); ?>