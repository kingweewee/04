<div class="dropShadow">
            </div>
<footer class="footer clearfix">
                <div class="containerFooterContent clearfix"> <!-- FOOTER CONTENT WRAPPER -->
                    <div class="conatinerMascot clearfix"> <!-- MASCOT BLOCK -->
                        <img id="mascot" src="<?bloginfo('template_url');?>/img/mascot.png" alt="mascot">
                        
                    </div>
                    <nav class="footerNav clearfix"> <!-- FOOTER NAVIGATION -->
                        <ul>
                            <li><a href="index.html">Home</a></li>
                            <li><a href="destination.html">Destination</a></li>
                            <li><a href="things to do.html">Things To Do</a></li>
                            <li><a href="package.html">Packages</a></li>
                            <li><a href="accomodation.html">Accomadation</a></li>
                            <li><a href="destination.html">Map</a></li>
                            <li><a href="#">Contact Us</a></li>
                        </ul>
                        <ul>
                            <li><a href="#">Login/Register</a></li>
                            <li><a href="info.html">Transport</a></li>
                            <li><a href="info.html">About New Zealand</a></li>
                            <li><a href="info.html">Exchange Rate</a></li>
                            <li><a href="info.html">Info Center</a></li>
                            <li><a href="info.html">Terms and Condition</a></li>
                            <li><a href="info.html">Payment Method</a></li>
                        </ul>
                    </nav>
                    <div class="containerSSL"> <!-- SOCIAL SEARCH LOGO COPYRIGHT BLOCK -->
                        <div class="containerSocial clearfix">
                            <a id="facebook" href="www.facebook.com"></a>
                            <a id="twit" href="www.twitter.com"></a>
                            <a id="goo" href="www.google.com"></a>
                        </div>
                                               
                            <?php     
                        if(function_exists(display_live_search)){
                            display_live_search();
                        };              
                    ?>

                       
                        <img class="footerLogo" src="<?bloginfo('template_url');?>/img/fLogo.png" alt="footerLogo">
                        <p class="copyright">&#169; 2013  ONE-STEP</p>
                    </div>
                </div>
            </footer>
</div>

        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
        <script>window.jQuery || document.write('<script src="<?php bloginfo('template_url');?>/js/vendor/jquery-1.9.0.min.js"><\/script>')</script>

        <script src="<?php bloginfo('template_url');?>/js/plugins.js"></script>
        <script src="<?php bloginfo('template_url');?>/js/main.js"></script>
    <?php 
	ls_footer();
	sc_footer();
	?>
<!-- End Document
================================================== -->
    </body>
</html>