<?
/*
Template Name: home Template
*/
get_header();?>

<?php
	global $pods;
	$found_pod = false;
	$getPod = pods('packages');
	$params = array('orderby' => 'id ASC', 'where'=> 'feature = true','limit' => -1);
	$getPod->findRecords($params);
?>
	
<?php 
	if(!empty($getPod->data)){
	$found_pod = true;
?>
	<?php
		if($found_pod){ ?>
			
			 
			     <div class="containerMain"> <!-- CONTENT WRAPPER -->
                    <div class="containerFeatured clearfix"> <!-- FEATURE BLOCK -->
                    
				 
                        <div class="containerFeaturedImage">
                            <div class="containerFeaturedHeader">
                                <h3>Spaview Luxury Accommodation</h3>
                            </div>
                            <a href="<?php bloginfo('home_url');?>"><img class="featuredImage" src="<?php bloginfo('template_url');?>/img/feat/night.jpg" alt="noAlt"></a>
                        </div>
                        <div class="containerFeaturedLinks">
                            
                            <a href="#" class="standardLink"></a>
                            <a href="#" class="luxuryLink"></a>
                        </div>
                    </div>
                    <section class="containerPackages clearfix"> <!-- PACKAGE BLOCK -->
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
                    
                        <article class="containerPackage">
                            <a href="<?php bloginfo('url'); ?>/packages/<?php echo $getPod_permalink;?>">
                            <img class="pImage" src="<?php echo $getPod_thumb[0];?>" alt="<?php echo $getPod_name;?>"></a>
                            <a href="<?php bloginfo('url'); ?>/packages/<?php echo $getPod_permalink;?>">
                            <h3><?php echo $getPod_name;?></h3></a>
                            <p><?php echo $getPod_excerpt;?></p>
                           
                            <div class="containerIcons">
                                <?php if($getPod_theme){?>
					 	<p><?php 
				 	//loop all theme icons
				 	 $numTheme = count($getPod_theme);
				 	 for ($i=0; $i < $numTheme; $i++) { 
				 	 	// $comma = ($i<$numTheme) ? ", " : " ";
				 	 	$getTheme = $getPod_theme[$i];
				 	 	$getThemeId = ($getTheme['id']);
				 	 	$themePod = pods("themes",$getThemeId);
				 	 	echo $themePod->field('name')."&ensp;";
				 	}}?></p>
                            </div>
                            <button type="button">ADD CART</button>
                        </article>
                        <?php }?> <!--close while-->

                        <a class="leftArrow" href="#"></a>
                        <a class="rightArrow" href="#"></a>
                    </section>
                </div>
            
	        

         <?php } else{?>
			<h1>Oops!</h1>
			<h5>404 NOT FOUND</h5>
		<?php }?> <!--close if $found_pod-->

<?php }?><!--close if !empty-->
<?php get_footer();?>
