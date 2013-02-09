<?php
function get_nav(){
	include('nav.php');
}?>
<?php
function custom_excerpt_length($length){
return 30;
}
add_filter('excerpt_length','custom_excerpt_length');?>

<?php
# pagination function
function pagination( $nav_id ) {
    global $wp_query;
        if ( $wp_query->max_num_pages > 1 ) : ?>
            <nav id="<?php echo $nav_id; ?>">
                <?php if (function_exists('wp_pagenavi')) {wp_pagenavi(); } else { ?>
                <div class="pagination">
                <div class="float_left"><?php next_posts_link('&laquo; Older Entries') ?></div>
                <div class="float_right"><?php previous_posts_link('Newer Entries &raquo;') ?></div>
                </div><?php } ?>
            </nav><!-- #nav-above -->
        <?php endif;
    }
?>