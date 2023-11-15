$(window).load(function(){
	$(".thumbSources input[type='radio']").click(function(){
	    $("input[class='"+this.className+"']").not($(this)).each(function(){
	        this.checked = false;
	    });
	    
	    var firstUnfilledColumn = getFirstUnfilledColumn();
	    for (i=1; i<=4; i++){
	    	$("input[class='options[th_thumbSources_thumbnails]["+i+"]']").each(function(){
	    		if (i>firstUnfilledColumn && firstUnfilledColumn != 0){
	    			$(this).parent().addClass("disabled");
	    			$(this).parent().removeClass("selected");
	    			this.disabled = true;
	    		}else if (i==firstUnfilledColumn){
	    			$(this).parent().removeClass("selected disabled");
	    			this.disabled = false;
	    		}else{
	    			$(this).parent().addClass("selected");
	    			$(this).parent().removeClass("disabled");
	    			this.disabled = false;
	    		}
	    	});
	    }
	    
		function getFirstUnfilledColumn(){
		    for (i=1; i<=4; i++){
		    	if($("input[class='options[th_thumbSources_thumbnails]["+i+"]']:checked").length < 1){
		    		return i;
		    	}
		    }
	    	return 0;
		}
	});
});


