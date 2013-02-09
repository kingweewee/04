jQuery(document).ready(function($) {
	$searchForm = $(".live-search-form");
	$searchInput = $searchForm.find("#search");
	$target = $('#main');
	  
	$searchInput.keyup(function(){
		searchPod();
	});
	
	$searchForm.submit(function(e){
		e.preventDefault();
		searchPod();
	});
	
	function searchPod(){
		//alert("search pod");
		var searchTerm = $searchInput.val(),
		podName = $searchForm.data('podname'),
		ajaxURL = $searchForm.attr('action'),
		action = $searchForm.find('#action').val();

		//post searchTerm and podName to php action live-search
		$.post(ajaxURL, {'action': action, 'searchTerm' : searchTerm, 'podName' :  podName },function(data){
			$target.html(data);
	   });
		
	}
});
