<?php
/*
Plugin Name: Custom Pods Live Ajax Search
Version: 2.0
Author: Mason Herber, updated to Pods 2 by Matt Stevens Nov 2012
Description: A plugin to demonstrate a simple ajax search using Pods
*/

define("SEARCH_PLUGIN_URL", plugin_dir_url( __FILE__ ));
define("SEARCH_TRIGGER", SEARCH_PLUGIN_URL."js/triggers.js");

// --------------- ACTIONS ---------------------
add_action( 'wp_ajax_liveSearch', 'liveSearch' );
add_action( 'wp_ajax_nopriv_liveSearch', 'liveSearch' );

// --------------- FUNCTIONS ---------------------

//displays the serach form and accepts the pod to be searched as an argument
function display_live_search(){
?>
<div class="live-search">
  <form action="<?php echo admin_url('admin-ajax.php');?>" method="post" class="live-search-form" data-podname="<?php echo $podName; ?>">
      <label for="search">Search <?php echo ucwords($podName)?></label>
      <input type="text" name="search"  id="search" class="search"/>
      <input type="submit" value="Search" class="submit" />
      <input type="hidden" value="liveSearch" id="action" />
      <div class="resultBody"></div>
  </form>
  </div>
<?php	
}

// --------------- SCRIPTS ---------------------
//create custom hook (to use instead of wp_footer())
function ls_footer(){
    do_action('ls_footer');
}

//add function addSearchScripts to custom hook
add_action('ls_footer', 'addSearchScripts');

function addSearchScripts(){ ?>
	<script src="<?php echo SEARCH_TRIGGER ?>"></script>
<?php 
}

//live search function is called by Ajax, it queries the specified pod fields for 'LIKE' string and returns html results
function liveSearch(){
	$searchTerm = $_POST['searchTerm'];
	// $podName = 'packages';
	$podNames = array('packages','accommodation','things to do');
	foreach($podNames as $podName){

	$thePod = pods($podName);

	//This is searching one Pod at a time searching the bothe the description and name fields
	$params = array( 'orderby' => 't.name ASC', 'limit' => -1, 'where' => "t.name LIKE '%$searchTerm%' || t.description LIKE '%$searchTerm%'");
	$thePod->find($params);
	$numRows = $thePod->total_found();
	echo "<h3>"."Results for ".$searchTerm." (".$numRows." total results from ".$podName.")"."</h3>" ;
	if($numRows > 0){
		while($thePod->fetch() ){//while there are items to display:
			$id = $thePod->field("id");
			$name = $thePod->field("name");
			$excerpt = $thePod->field("excerpt");
			$price = $thePod->field("price");
			$permalink = $thePod->field("permalink");
			$thumb = $thePod->field('thumbnail');
		    $thumb = wp_get_attachment_image_src($thumb['ID'], array(32,32));
			?>
           
            <a href="<?php bloginfo('url'); ?>/<?php echo $podName;?>/<?php echo $permalink;?>">
            	<p><?php echo $name;?></p></a>
            	<p><?php echo $excerpt;?></p>

            <?php //echo $description;?>
            
            
            <?php if(function_exists(displayAddToCart)):?>
             
              <div class="addCartLink">
                <?php displayAddToCart($id, $podName);?>
              </div>
              
            <?php endif; ?>
          </div> <!-- end .post -->
				
			<?php
		} //endwhile

	} else {
		echo "<p>There are no matching results for \"$searchTerm\"</p>";
	}//endelse
} //end foreach
	exit;
}
?>