TsTree = function()
{
	this.requestNb = 0;

	this.init = function(){

		this.initTree();
		this.initPostList();
		this.initPostActions();		

	// lancement de la page active : 
		jQuery("#ts-tree .tree .label.current").parents().parents('li').addClass('deployed');
		jQuery(jQuery("#ts-tree .tree .label.current")[0]).trigger("click");

		this.initBehaviors();		
	}

	this.initTree = function()
	{
		jQuery('.sortable').nestedSortable({
			placeholder: "moveto",
            handle: 'div',
            items: 'li',
            toleranceElement: '> div',
            stop: TsTreeTools.onCategoryReorganize
        });
	
		// initialisatoin des éléments de l'arborescence
			jQuery("#ts-tree .tree li").each(function(i , n ){
				if( jQuery(this).children('ul').length < 1 ){
					jQuery(this).addClass('undeployable');
					jQuery(this).children('.element').find('.btn-deploy').remove();
				}
			});

		// gestion du déploiement
			jQuery( "#ts-tree .tree .btn-deploy" ).click(function(){
				if( jQuery( jQuery(this).parents('li')[0]).children('ul').length>0 ){
					jQuery( jQuery(this).parents('li')[0]).toggleClass('deployed');
				}
			});

		// gestion des actions
			jQuery("#ts-tree .tree .label").click(function(){
				jQuery("#ts-tree .tree .shown").removeClass('shown');
				jQuery(this).addClass('shown');

				// load page : 
				var data = {
					'action': 'load_categorie_content',
					'catId': jQuery(this).attr("id"),
					'catName': jQuery(this).children(".title").text()
				};
				
				TsTreeTools.startLoading();
				jQuery.get( ajaxurl , data , function (result){
					TsTreeTools.stopLoading();
					content = jQuery(result).children(".content")[0];
					jQuery( "#ts-tree #content-zone > .content" ).replaceWith(content);
					jQuery( "#ts-tree #content-zone" ).removeClass("loading");
				});
			});

		// ajout d'une sous catégorie
			jQuery(document).on('click' , "#ts-tree .element .add-cat,#ts-tree #add-top-cat" , function(evt){
				evt.stopPropagation();
				jQuery("#add-categorie-form").addClass( "shown" );
				jQuery("#add-categorie-form").addClass( "add" );
				jQuery("#add-categorie-form").removeClass( "edit" );
				jQuery("#add-categorie-form input,#add-categorie-form textarea").val( "" );
				jQuery("#add-categorie-form").attr( "data-cat" ,  jQuery(jQuery(this).parents('.label')[0]).attr("id") );
			});
			jQuery(document).on('click' , "#ts-tree #add-categorie-form .close" , function(evt){
				jQuery("#add-categorie-form").removeClass( "shown" );
			});

		//édition d'une catégorie 
		jQuery(document).on('click' , "#ts-tree .element .edit-cat" , function(evt){
				evt.stopPropagation();
				evt.preventDefault();
				
				jQuery("#add-categorie-form").attr( "data-cat" ,  jQuery(jQuery(this).parents('.label')[0]).attr("id") );
				var data = {
					'action' : 'get_category_data',
					'catId' : jQuery(this).parent().parent().attr("id")
				}
				jQuery.getJSON( ajaxurl , data , function(catInfo){
					jQuery("#add-categorie-form").addClass( "shown" );
					jQuery("#add-categorie-form").addClass( "edit" );
					jQuery("#add-categorie-form").removeClass( "add" );
					jQuery("#add-categorie-form [name=cat-name]").val( catInfo.name );
					jQuery("#add-categorie-form [name=cat-desc]").val( catInfo.description );
					jQuery("#add-categorie-form [name=cat-identifiant]").val( catInfo.slug );
				} );
				return null;
			});

			jQuery("#ts-tree .delete-cat").click(function(){
				if(confirm(jQuery("#deleteCatConfirmText").html())){
					var data = {
						'action': 'delete_categorie',
						'catId': jQuery(jQuery(this).parents('.label')[0]).attr("id")
					};
					
					TsTreeTools.startLoading();
					jQuery.get( ajaxurl , data , function (result){
						TsTreeTools.stopLoading();
						TsTreeTools.initTreeContent();
					});
				};
			});
	}

	jQuery(document).on('click' , '#ts-tree #add-categorie-form.add [type=button]' , function(){
		var data = {
			'action': 'add_categorie',
			'parentCatId': jQuery("#add-categorie-form").attr( "data-cat" ) ,
			'newCatName': jQuery("#add-categorie-form [name=cat-name]").val(),
			'newCatDesc': jQuery("#add-categorie-form [name=cat-desc]").val(),
			'newCatId': jQuery("#add-categorie-form [name=cat-identifiant]").val()
		};
		
		jQuery("#ts-tree #add-categorie-form").addClass("loading");
		jQuery.get( ajaxurl , data , function (result){
			jQuery("#ts-tree #add-categorie-form").removeClass("loading");
			jQuery("#ts-tree #add-categorie-form").removeClass("shown");
			TsTreeTools.initTreeContent();
		});
	});

	jQuery(document).on('click' , '#ts-tree #add-categorie-form.edit [type=button]' , function(){
		var data = {
			'action': 'edit_categorie',
			'catId': jQuery("#add-categorie-form").attr( "data-cat" ) ,
			'catName': jQuery("#add-categorie-form [name=cat-name]").val(),
			'catDesc': jQuery("#add-categorie-form [name=cat-desc]").val(),
			'catSlug': jQuery("#add-categorie-form [name=cat-identifiant]").val()
		};
		
		jQuery("#ts-tree #add-categorie-form").addClass("loading");
		jQuery.get( ajaxurl , data , function (result){
			jQuery("#ts-tree #add-categorie-form").removeClass("loading");
			jQuery("#ts-tree #add-categorie-form").removeClass("shown");
			TsTreeTools.initTreeContent();
		});
	});


	this.onCategoryReorganize = function(){
		var tree = getTree( jQuery( ".tree>.sortable" ) );
		// var t = new Array();
		// t["iyu"] = "on";
		var data = {
			'action' : 'update_categorie_tree' , 
			'tree' : tree
		}
		jQuery.get( ajaxurl , data , function (result){
			
		});


		function getTree( parent ){
			var tmp = {};
			tmp.id = parent.prev("div").children("div").attr("id");
			var childrenCat = jQuery(parent.children('li'));

			if( childrenCat.length > 0 ){
				tmp.children = {};
				childrenCat.each(function(i , n){
					n = jQuery(n);
					if( n.children('ol').length > 0 ){
						tmp.children['object'+i] = getTree( n.children('ol') );
					}
					else{
						tmp.children['object'+i] = {id : n.find(".label").attr("id") };
					}
				});
			}


			return tmp;
		}
	}

	this.initPostList = function()
	{
		// gestion de l'affichage de l'éditeur :
			jQuery(document).on("click" ,".list li" , function(){
				// on cache le menu et affiche le bouton back : 
					jQuery("#ts-tree").addClass("post-mode");
					jQuery("#ts-tree .list li").removeClass("selected");
					jQuery(this).addClass("selected");

					var postId = jQuery(this).attr("id").replace('post_' ,'');
					jQuery("#ts-tree #edit-content").addClass("loading");
					TsTreeTools.startLoading();
					jQuery("#ts-tree #edit-content").html("<iframe src='./post.php?post="+postId+"&action=edit'/>");
			});

		// gestion de l'affichage de l'éditeur :
			jQuery(document).on("click" ,"#add-post" , function(){
				// on cache le menu et affiche le bouton back : 
					jQuery("#ts-tree").addClass("post-mode");
					jQuery("#ts-tree .list li").removeClass("selected");
					jQuery(this).addClass("selected");

					
					jQuery("#ts-tree #edit-content").addClass("loading");
					TsTreeTools.startLoading();
					jQuery("#ts-tree #edit-content").html("<iframe src='./post-new.php'/>");
			});

		// gestion du retour au mode normal : 
			jQuery(document).on("click" ,".list .back" , function(){
				jQuery("#ts-tree").removeClass("post-mode");
			});

		// gestion des filtres : 
			jQuery(document).on("click" , ".list .selector" , function()
				{
					jQuery(this).toggleClass("selected");
					initSelectors();
				});
			function initSelectors()
			{
				jQuery(".selector").each(function(i , n ){
					n = jQuery(n);
					var c= n.attr("id").replace("selector_" , "");
					if( n.hasClass("selected") ){
						jQuery(".list .posts ."+c).show();
					}
					else{
						jQuery(".list .posts ."+c).hide();
					}
				})
			}
	}

	this.initPostActions = function()
	{
		// gestion des actions sur post : 
			jQuery(document).on("click" , ".delete-post" , function(evt){
				evt.preventDefault();
				evt.stopPropagation();
				if( confirm("Voulez vous vraiment placer cet élément dans la corbeille ?")){
					var data = {
						'action': 'delete_post',
						'postId': jQuery(jQuery(this).parents("li")[0]).attr("id").replace("post_" , "")
					};
					
					TsTreeTools.startLoading();
					jQuery.get( ajaxurl , data , function (result){
						TsTreeTools.stopLoading();
						window.TsTreeTools.refresh();
					});
				}
			});

		// gestion des actions sur post : 
			jQuery(document).on("click" , ".delete-post-from-cat" , function(evt){
				evt.preventDefault();
				evt.stopPropagation();
				if( confirm("Voulez vous vraiment supprimer l'article de la catégorie ?")){
					var data = {
						'action': 'delete_post_from_cat',
						'postId': jQuery(jQuery(this).parents("li")[0]).attr("id").replace("post_" , ""),
						'catId': jQuery(jQuery(this).parents(".list")[0]).attr("data-cat")
					};
					
					TsTreeTools.startLoading();
					jQuery.get( ajaxurl , data , function (result){
						TsTreeTools.stopLoading();
						window.TsTreeTools.refresh();
					});
				}
			});

		// gestion des actions sur post : 
			jQuery(document).on("click" , ".delete-post-permanently" , function(evt){
				if( confirm("Voulez vous vraiment supprimer le post définitivement ?")){
					evt.preventDefault();
					evt.stopPropagation();
					var data = {
						'action': 'delete_post_permanently',
						'postId': jQuery(jQuery(this).parents("li")[0]).attr("id").replace("post_" , "")
					};
					
					TsTreeTools.startLoading();
					jQuery.get( ajaxurl , data , function (result){
						TsTreeTools.stopLoading();
						window.TsTreeTools.refresh();
					});
				}
			});

		// gestion des actions sur post : 
			jQuery(document).on("click" , ".draft-post" , function(evt){
				evt.preventDefault();
				evt.stopPropagation();
				var data = {
					'action': 'draft_post',
					'postId': jQuery(jQuery(this).parents("li")[0]).attr("id").replace("post_" , "")
				};
				
				TsTreeTools.startLoading();
				jQuery.get( ajaxurl , data , function (result){
					TsTreeTools.stopLoading();
					window.TsTreeTools.refresh();
				});
			});

			jQuery(document).on("click" , ".publish-post" , function(evt){
				evt.preventDefault();
				evt.stopPropagation();
				var data = {
					'action': 'pubish_post',
					'postId': jQuery(jQuery(this).parents("li")[0]).attr("id").replace("post_" , "")
				};
				
				TsTreeTools.startLoading();
				jQuery.get( ajaxurl , data , function (result){
					TsTreeTools.stopLoading();
					window.TsTreeTools.refresh();
				});
			});
	}

	this.initBehaviors=function()
	{
		// behaviours
		jQuery(window).resize(onResize);
		onResize();

		function onResize(){
			jQuery("#ts-tree").width( jQuery(window).width() - jQuery("#ts-tree").position().left);
		}
	}

	this.setPostListOptions = function( toElement )
	{
		toElement = jQuery( toElement );
		jQuery("#content-zone .selector, #content-zone #add-post").each(function(i , n){
			n = jQuery(n);
			if( n.hasClass( "selected" ) ){
				toElement.find( "#" + n.attr("id") ).addClass("selected");
			}
			else{
				toElement.find( "#" + n.attr("id") ).removeClass("selected");
			}
		});

		toElement.find("#" + jQuery("#content-zone li.selected").attr("id")).addClass("selected");
	}

	this.initPostListContent = function(  ){
		var data = {
			'action': 'load_categorie_content',
			'catId': jQuery("#content-zone .list").attr("data-cat"),
			'catName': jQuery("#content-zone h3").html()
		};
		TsTreeTools.startLoading();
		jQuery.get( ajaxurl , data , function (result){
			TsTreeTools.stopLoading();
			content = jQuery(result).children(".content")[0];
			window.TsTreeTools.setPostListOptions( content );
			jQuery( "#ts-tree #content-zone > .content" ).replaceWith(content);
		});
	}

	this.initTreeContent = function(  ){
		var data = {
			'action': 'get_tree'
		};
		TsTreeTools.startLoading();
		jQuery.get( ajaxurl , data , function (result){
			TsTreeTools.stopLoading();
			jQuery( "#ts-tree .tree" ).replaceWith( jQuery(result).children(".tree")[0] );
			window.TsTreeTools.initTree();
			jQuery("#ts-tree .tree .label.current").parents().parents('li').addClass('deployed');
			jQuery("#ts-tree .tree .label.current").addClass('shown');
		});
	}

	this.refresh = function(){
		this.initPostListContent();
		this.initTreeContent();
	}

	this.showEditZone = function()
	{
		jQuery("#ts-tree #edit-content").removeClass("loading");
		this.stopLoading();
	}	

	this.closeEditZone = function()
	{
		jQuery("#ts-tree #edit-content").addClass("loading");
		jQuery("#ts-tree .list .back").trigger("click");
	}	

	this.isAddPostMode = function()
	{
		return jQuery("#add-post").hasClass("selected");
	}

	this.getCurrentCat = function()
	{
		return jQuery("#content-zone .list").attr("data-cat");
	}

	this.startLoading = function()
	{
		jQuery("#ts-tree").addClass("loading");
		this.requestNb++;
	}
	this.stopLoading = function()
	{
		 this.requestNb--;
		 if(  this.requestNb < 1){
		 	this.requestNb = 0;
			jQuery("#ts-tree").removeClass("loading");
		 }
	}
}


window.TsTreeTools = new TsTree();
jQuery( document ).ready( window.TsTreeTools.init() );