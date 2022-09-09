jQuery(document).ready(function(){

    try {
        if( window.self !== window.top ){
            jQuery("html").addClass("hide-menus");

            if( "undefined" != typeof window.top.TsTreeTools ){
                if( ( window.location.href.indexOf("post.php?") > -1
                        && window.location.href.indexOf("action=edit") > -1)
                        || window.location.href.indexOf("post-new.php") > -1 ){
                    window.top.TsTreeTools.showEditZone();
                    window.top.TsTreeTools.refresh();

                    if( window.top.TsTreeTools.isAddPostMode() ){
                        jQuery("#category-"+window.top.TsTreeTools.getCurrentCat() + ">label").click();
                    }
        		}
        		else{
	        		window.top.TsTreeTools.refresh();
	        		window.top.TsTreeTools.closeEditZone();
        		}
        	}
        }
    } catch (e) {
    }
});

