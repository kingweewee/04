<!DOCTYPE html>
<!--[if lt IE 7 ]><html class="ie ie6" lang="en"> <![endif]-->
<!--[if IE 7 ]><html class="ie ie7" lang="en"> <![endif]-->
<!--[if IE 8 ]><html class="ie ie8" lang="en"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--><html lang="en"> <!--<![endif]-->
<head>
	<meta charset="utf-8">
	<title><?php bloginfo('name');?></title>
	<meta name="description" content="<?php bloginfo('description');?>"/>
	<meta name='robots' content='noindex,nofollow' />
	<meta name="viewport" content="width=device-width">
	<link rel="stylesheet" type="text/css" href="<?bloginfo('stylesheet_url');?>">
	
	<script src="<?bloginfo('template_url');?>/js/modernizr-2.5.3.min.js"></script>
	


</head>
<body style="background-color:gray">
	<div class="container">

		<header class="sixteen columns ">
			<h1 class="screen_text">Responsive HOW Design</h1>
			
				<div class="four columns alpha">
					<a href="<?php echo home_url();?>"><img class="scale-with-grid" src="<?php bloginfo('template_url');?>/uploads/logo.gif" alt="logo"><span class="screen_text">homepage</span></a>
				</div><!-- four columns end, header left -->
				<div class="eleven columns">
					<?php	  
						if(function_exists(display_live_search)){
							display_live_search();
						};				
					?>
						
				</div><!-- eleven offset by one end, header right -->
		</header><!-- sixteen columns end, header container -->
		<?php include('nav.php');?>
		<section class="sixteen columns relative">
			<div id="main" class="clearfix">
			