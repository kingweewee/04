<?
/*
Template Name: single Template
*/
get_header();?>

<?php
	global $pods;
	$found_pod = false;
	$podName = pods_url_variable(1);
	$prodUrl = pods_var(-1, 'url');
	$getPod = pods($podName, $prodUrl);
	$params = array('orderby' => 'id ASC','limit' => -1);
	$getPod->findRecords($params);

?>
	
<?php 
	if(!empty($getPod->data)){
	$found_pod = true;
?>
	<?php
		if($found_pod){ ?>
			<div class="containerMain clearfix">
				<ul class="breadcrumb">
					
<?php
$count = 0; $url = ("/" . pods_url_variable($count));
while(pods_url_variable($count) != pods_url_variable('last')) { $title = (pods_url_variable($count));
echo "<li><a href='" . $url . "'>" . $title . "</a></li>";
$count ++; $url .= ("/" . pods_url_variable($count)); }
echo "<li class='current'>" . pods_url_variable('last') . "</li>"; ?> </ul>
</div>
				 
			 <?php
				$getPod_name = $getPod->field('name');
				$getPod_price = $getPod->field('price');
				$getPod_spotlight = $getPod->field('spotlight');
				$getPod_description = $getPod->field('description');
				$getPod_image = $getPod->field('image');
				$getPod_image = wp_get_attachment_image_src($getPod_image['ID'],'full');
				$getPod_theme = $getPod->field('theme');
				$getPod_location = $getPod->field('location');
				$getPod_permalink = $getPod->field('permalink');
			?>
            <section class="containerProduct clearfix">
					<img class="productImage" src="<?php echo $getPod_image[0];?>" alt="<?php $getPod_name;?>">
					<div class="productTextWrap">
					<article class="productTitle">
				 	<h2><?php echo $getPod_spotlight;?> </h2><br />
				 	<p class="location">

				 	<?php 
				 	//loop all theme icons
				 	 $numTheme = count($getPod_theme);
				 	 for ($i=0; $i < $numTheme; $i++) { 
				 	 	// $comma = ($i<$numTheme) ? ", " : " ";
				 	 	$getTheme = $getPod_theme[$i];
				 	 	$getThemeId = ($getTheme['id']);
				 	 	$themePod = pods("themes",$getThemeId);
				 	 	echo $themePod->field('name')."&ensp;";
				 	}?>
				 	
				 	Location: <?php 
				 	//loop all locations
				 	 $numlocation = count($getPod_location);
				 	 for ($i=0; $i < $numlocation; $i++) { 
				 	 	$getLocation = $getPod_location[$i];
				 	 	$getLocationId = ($getLocation['id']);
				 	 	$locationPod = pods("destination",$getLocationId);
				 	 	echo $locationPod->field('name')."&ensp;";
					}?></p>
					 </article>
					 <article class="productBlurb">
					<p><?php echo $getPod_description;?></p> 
					</article>
					<div class="productPrice">
						<h3>Per Person</h3>
						<h2><?php $getPod_price;?></h2>
						<select class="quantity">
	                        <option value="1">1</option>
	                        <option value="1">2</option>
	                        <option value="1">3</option>
	                        <option value="1">4</option>
	                        <option value="1">5</option>
                        </select>
                        <button type="button">ADD CART</button>
			 	</div>
			 </section>
			 <?php include('sidebar.php');?>
			  </div>
                
            </div>
        <?php } else{?>
			<h1>Oops!</h1>
			<h5>404 NOT FOUND</h5>
		<?php }?> <!--close if $found_pod-->

<?php }?><!--close if !empty-->
<?php get_footer();?>
