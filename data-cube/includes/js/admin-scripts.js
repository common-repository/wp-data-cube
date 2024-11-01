
var WPDataCube = {
	insertPrefixes: function(prefix, prefixURI, vocabTerms) {
		var data = {
			action: 'data_cube_add_prefix',
			prefix: prefix,
			prefixURI: prefixURI,
			vocabTerms: vocabTerms
		};

		jQuery.post(ajaxurl, data, function(response) {
			jQuery('select.dc-header-options option').remove();
			jQuery('select.dc-header-options').each(function(){
				jQuery(this).append(response);
			});
			
			jQuery("select.dc-header-options").val('').trigger("chosen:updated");
		
			toastr.success('Prefix successfully added');
		});
	}
};


jQuery(document).ready(function($){
	
	// Validation 
	$('#dc-csv-form').ketchup();
	$('#dc-header-options').ketchup();
	
	$( "#dc-csv-form" ).submit(function( event ) {
		if( !$('#dc-csv-form').ketchup("isValid") ) {
			event.preventDefault();
		}
	});

	
	$( "a.dc-bt-add-prefix" ).click(function( event ) {
		$('#dc-add-prefixes').dialog( "open" );
		event.preventDefault();
	});
	
	$( "#dc-add-prefixes" ).dialog({
		autoOpen: false,
		width: 800,
		buttons: [
			{
				text: "Ok",
				click: function() {
					$( this ).dialog( "close" );
				}
			},
			{
				text: "Cancel",
				click: function() {
					$( this ).dialog( "close" );
				}
			}
		]
	});
	
	$( "#dc-manage-prefixes" ).dialog({
		autoOpen: false,
		width: 800,
		buttons: [
			{
				text: "Ok",
				click: function() {
					$( this ).dialog( "close" );
				}
			},
			{
				text: "Cancel",
				click: function() {
					$( this ).dialog( "close" );
				}
			}
		]
	});
	
	// List prefixes
	$( "a.dc-bt-manage-prefixes" ).click(function( event ) {
		var data = {
			action: 'data_cube_get_prefixes'
		};

		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		$.post(ajaxurl, data, function(response) {
			$('div#dc-manage-prefixes table tr:not(:first)').remove(); 
			$('div#dc-manage-prefixes table').append(response); 
		});
	
		$('#dc-manage-prefixes').dialog( "open" );
		event.preventDefault();
	});

	var prefixesResult = [];
	$('div#dc-add-prefixes form').submit(function( event ) {
		event.preventDefault();
		var query = $('input#dc-prefix-search').val();
		prefixesResult = [];
		$('div#dc-add-prefixes table tr:not(:first)').remove(); 
		$('div#dc-prefix-results').hide();
		
		// Check if search is not empty
		if (query.trim()) {
			var totalPages = 1;
			var totalResults = 0;
			
			$.getJSON('http://lov.okfn.org/dataset/lov/api/v2/autocomplete/vocabularies?q='+query,function(prefix){	
				
				totalResults = prefix.total_results;
				totalPages = Math.ceil(prefix.total_results/prefix.page_size);
				
				if(prefix.total_results > 0 ){
					$.each(prefix.results, function( index, value ) {
						delete value.score;
						prefixesResult.push(value);
					});
					
					var teste = [];
					if(totalPages > 1){
						for (var i = 2; i <= totalPages; i++) {
							(function(i) { // protects i in an immediately called function
								$.getJSON('http://lov.okfn.org/dataset/lov/api/v2/autocomplete/vocabularies?q='+query+'&page='+i,function(prefixPerPage){	
									$.each(prefixPerPage.results, function( index, value ) {
										delete value.score;
										prefixesResult.push(value);
									});
									
									// Check if finished
									if(prefixesResult.length == totalResults){
										
										$.each(prefixesResult, function( index, value ) {
											((index%2) == 0) ? classProp = 'class="alternate"' : classProp = ""; 
											
											$('div#dc-add-prefixes table').append('<tr '+ classProp +'>'+
																							'<td>'+ value.prefix +'</td>'+
																							'<td>'+ value.uri +'</td>'+
																							'<td><a data-prefix="'+ value.prefix +';;'+ value.uri +'" class="dc-bt-add-prefix" href="javascript:void(0)" >Add</a></td>'+
																						'</tr>');
										});
										
										$('div#dc-prefix-results').show();
									}
								});
							})(i);
						}
					}else{
						if(prefixesResult.length == totalResults){
							
							$.each(prefixesResult, function( index, value ) {
								((index%2) == 0) ? classProp = 'class="alternate"' : classProp = ""; 
								
								$('div#dc-add-prefixes table').append('<tr '+ classProp +'>'+
																				'<td>'+ value.prefix +'</td>'+
																				'<td>'+ value.uri +'</td>'+
																				'<td><a data-prefix="'+ value.prefix +';;'+ value.uri +'" class="dc-bt-add-prefix" href="javascript:void(0)" >Add</a></td>'+
																			'</tr>');
							});
							
							$('div#dc-prefix-results').show();
						}
					}
					
				}
				
			});
		}
		
	});	
	
	// Add prefix terms to database
	var vocabResult = [];
	$(document).on("click","div#dc-prefix-results a.dc-bt-add-prefix",function(e){
		var prefixData = $(this).data('prefix').split(';;');
		var prefix = prefixData[0]; 
		var prefixURI = prefixData[1]; 
		
		query = prefix+':';
		vocabResult = [];
		
		// Check if search is not empty
		if (query.trim()) {
			var totalPages = 1;
			var totalResults = 0;
			
			$.getJSON('http://lov.okfn.org/dataset/lov/api/v2/autocomplete/terms?q='+query,function(vocab){	
				
				totalResults = vocab.total_results;
				totalPages = Math.ceil(vocab.total_results/vocab.page_size);
				
				if(vocab.total_results > 0 ){
					$.each(vocab.results, function( index, value ) {
						delete value.score;
						vocabResult.push(value);
					});
					
					var teste = [];
					if(totalPages > 1){
						for (var i = 2; i <= totalPages; i++) {
							(function(i) { // protects i in an immediately called function
								$.getJSON('http://lov.okfn.org/dataset/lov/api/v2/autocomplete/terms?q='+query+'&page='+i,function(vocabPerPage){	
									$.each(vocabPerPage.results, function( index, value ) {
										delete value.score;
										vocabResult.push(value);
									});
									
									// Check if finished
									if(vocabResult.length == totalResults){
										WPDataCube.insertPrefixes(prefix, prefixURI, vocabResult);
									}
								});
							})(i);
						}
					}else{
						if(vocabResult.length == totalResults){
							WPDataCube.insertPrefixes(prefix, prefixURI, vocabResult);
						}
					}
					
				}
				
			});
		}
	});
	
	$(document).on("click","div#dc-manage-prefixes a.dc-bt-delete-prefix",function(e){
		var prefixID = $(this).data('predixid');
		
		var r = confirm("Are you sure you want to delete this item?");
		if (r == true)
		{
			var data = {
				action: 'data_cube_delete_prefix',
				prefixID: prefixID
			};

			// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
			$.post(ajaxurl, data, function(response) {
				$('select.dc-header-options option').remove();
				$('select.dc-header-options').each(function(){
					$(this).append(response);
				});
				
				$("select.dc-header-options").val('').trigger("chosen:updated");
			
				toastr.success('Prefix successfully deleted');
			});
		}
	});
	
	
	$('select.dc-header-options').chosen({search_contains : true});
	
	// Downaload model file
	$('a.dc-download-model').click(function() {
		var projectID = $(this).data('id-project');
		
		var data = {
			action: 'data_cube_download_model',
			projectID: projectID
		};

		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		$.post(ajaxurl, data, function(response) {
			 window.location.replace('http://localhost/pf/wp-content/uploads/data-cube/project'+projectID+'.zip');
		});
	});
	
	//Delete project
	$('a.dc-delete-project').click(function() {
		var projectID = $(this).data('id-project');
		
		var data = {
			action: 'data_cube_delete_project',
			projectID: projectID
		};

		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		$.post(ajaxurl, data, function(response) {
			toastr.success('Project successfully deleted');
		});
	});
	
	// SlideDown/SlideUp event
	$('h3.flat-form-title').click(function() {
		if($(this).hasClass('close')){
			$(this).removeClass('close');
			$(this).addClass('open');
			$(this).siblings('.flat-component').slideDown();
		}
		else{
			$(this).removeClass('open');
			$(this).addClass('close');
			$(this).siblings('.flat-component').slideUp();
		}
	});
	
	// Delete column  
	$('a.dc-delete-column').click(function() {
		var r = confirm("Are you sure you want to delete this item?");
		if (r == true)
		{
			$(this).siblings('input.dc-df').val(true);
			$(this).parents('li.form-rouded.flat-form').hide();
		}
		
	});
	
	// Submit classification data form
	$('a#dc-class-form-submit').click(function() {
		
		if( $('#dc-header-options').ketchup("isValid") ) {
			console.log('ok');
			var formData = $('form#dc-header-options').serialize();
			var projectID = $('input[name="dc-project-id"]').val();
			
			var data = {
				action: 'data_cube_submit_data',
				formData: formData
			};
			
			$('div#dc-wrapper').html('<img src="'+contenturl+'/plugins/data-cube/images/ajax-loader.GIF" height=64 width=64 alt="ajax loader" title="ajax loader" />');

			$.post(ajaxurl, data, function(response) {
				window.location.href = document.URL + '&project=' + projectID;
			});
		}else{
			console.log('nao');
		}
	});
	

});


