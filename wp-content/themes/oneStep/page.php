<?
/*
Template Name: page Template
*/
get_header();?>

<?php
	global $pods;
	$found_pod = false;
	$podName = pods_var(-1, 'url');
	$getPod = pods($podName);
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
                <div class="breadcrumbs clearfix">
				<a href="<?php echo home_url();?>">Home</a>
				<a href="<?php echo $podName?>"><?php echo $podName?></a>
			    </div>
				<div class="containerHighlight clearfix">
	                <img class="" src="<?php bloginfo('template_url');?>/img/feat/nature.jpg" alt="highlight">
	            </div>
	            <div class="filters">
	                <table>
	                    <tr>
	                        <th>Destination</th>
	                        <th>Theme</th>
	                        <th>Price</th>
	                        
	                    </tr>
	                    <tr>
	                        <td><select class="filterOne">
	                            <option value="featured first">All regions<option>
	                        </select></td>
	                        <td><select class="filterTwo">
	                            <option value="All Themes">All Themes<option>
	                        </select></td>
	                        <td><select class="filterThree">
	                            <option value="All Prices">All Prices<option>
	                        </select></td>
	                        
	                    </tr>
	                </table>
	            </div>
            
			 
			
					<?php while($getPod->fetch()) {?>
				 <?php
					$getPod_name = $getPod->field('name');
					$getPod_price = $getPod->field('price');
					$getPod_excerpt = $getPod->field('excerpt');
					$getPod_thumb = $getPod->field('thumbnail');
					$getPod_thumb = wp_get_attachment_image_src($getPod_thumb['ID'],'full');
					$getPod_theme = $getPod->field('theme');
					$getPod_location = $getPod->field('location');
					$getPod_permalink = $getPod->field('permalink');
				?>
				<section class="containerActivities clearfix">
                 <article class="containerActivity clearfix">
				 	<a href="<?php bloginfo('url'); ?>/<?php echo $podName;?>/<?php echo $getPod_permalink;?>"><img class="aImage" src="<?php echo $getPod_thumb[0];?>" alt="<?php echo $podName;?>">
						 <div class="paraWrap">
							<h3><a href="<?php bloginfo('url'); ?>/<?php echo $podName;?>/<?php echo $getPod_permalink;?>"><?php echo $getPod_name;?></a></h3>
							<p><?php echo $getPod_excerpt;?></p>
							<p class="location">
								<?php if($getPod_theme){?>
							 	<?php 
						 	//loop all theme icons
						 	 $numTheme = count($getPod_theme);
						 	 for ($i=0; $i < $numTheme; $i++) { 
						 	 	// $comma = ($i<$numTheme) ? ", " : " ";
						 	 	$getTheme = $getPod_theme[$i];
						 	 	$getThemeId = ($getTheme['id']);
						 	 	$themePod = pods("themes",$getThemeId);
						 	 	echo $themePod->field('name')."&ensp;";
							 	}}?>
							 </p>
							 <p>Location: <?php 
						 	//loop all locations
						 	 $numlocation = count($getPod_location);
						 	 for ($i=0; $i < $numlocation; $i++) { 
						 	 	$getLocation = $getPod_location[$i];
						 	 	$getLocationId = ($getLocation['id']);
						 	 	$locationPod = pods("destination",$getLocationId);
						 	 	echo $locationPod->field('name')."&ensp;";
							}?></p>
						</div>

					<div class="containerPrice">
							<h3>Per Person</h3>
							<h2>$ <?php echo $getPod_price;?></h2>
							<select class="quantity">
                                <option value="1">1</option>
                                <option value="1">2</option>
                                <option value="1">3</option>
                                <option value="1">4</option>
                                <option value="1">5</option>
                            </select>
                            <button type="button">ADD CART</button>
					 </div>
				</article>
	     	 </section>
			 <?php }?> <!--close while-->
			 <?php include('sidebar.php');?>

	    </div>
  </div>
         <?php } else{?>
			<h1>Oops!</h1>
			<h5>404 NOT FOUND</h5>
		<?php }?> <!--close if $found_pod-->
<?php }?><!--close if !empty-->

<?php get_footer();?>
