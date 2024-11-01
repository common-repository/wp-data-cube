<?php
add_action( 'init', 'dc_control_init', 11 );
function dc_control_init() {
	global $controller;
	$controller->dc_init();
}

// Ajax calls

add_action( 'wp_ajax_data_cube_submit_data', 'data_cube_submit_data_callback' );
function data_cube_submit_data_callback() {
	parse_str($_POST['formData'], $form);
	
	global $controller;
	$controller->dc_create_model($form);
	
	die();
}

add_action( 'wp_ajax_data_cube_download_model', 'data_cube_download_model_callback' );
function data_cube_download_model_callback() {
	global $controller;
	$projectID = $_POST['projectID'];
	
	$zip = new ZipArchive;
	$fileZip = $controller->dc_get_upload_directory() . '/project'. $projectID .'.zip';
	
	if(!file_exists($fileZip)){
		if ($zip->open($fileZip, ZipArchive::CREATE)) {
			$zip->addFile($controller->dc_get_upload_directory() . '/project'. $projectID .'.ttl', 'project'. $projectID .'.ttl');
			$zip->close();
			
			$file_name = basename($fileZip);

			header("Content-Type: application/zip");
			header("Content-Disposition: attachment; filename=$file_name");
			header("Content-Length: " . filesize($fileZip));

			readfile($fileZip);
			echo 'Archive created!';
		} else {
			echo 'Failed!';
		}
	}else{
		header("Content-Type: application/zip");
		header("Content-Disposition: attachment; filename=$file_name");
		header("Content-Length: " . filesize($fileZip));

		readfile($fileZip);
	}
	
	die();
}

add_action( 'wp_ajax_data_cube_delete_project', 'data_cube_delete_project_callback' );
function data_cube_delete_project_callback(){
	global $controller;
	$controller->dc_delete_project($_POST['projectID']);
	
	die();
}
/* 
add_action( 'wp_ajax_data_cube_add_prefix_terms', 'data_cube_add_prefix_terms' );
function data_cube_add_prefix_terms(){
	
	if(isset($_POST['terms'])) {
		global $render;
		global $wpdb;
		
		$table_name = "wp_dc_vocabulary_term";
		$terms = $_POST['terms'];
		
		foreach($terms as $term){
			$wpdb->insert( $table_name, $term);
		}
		
		echo $render->dc_get_property_options();
	} 
	
	die();
}
 */
add_action( 'wp_ajax_data_cube_add_prefix', 'data_cube_add_prefix' );
function data_cube_add_prefix(){
	global $wpdb;
	global $render;
	
	if(isset($_POST['prefix']) && isset($_POST['prefixURI'])) {
		$prefix = array('prefix' => $_POST['prefix'], 'uri' => $_POST['prefixURI']); 
		$wpdb->insert( 'wp_dc_prefix', $prefix);
		
		if(isset($_POST['vocabTerms'])) {
			
			$table_name = "wp_dc_vocabulary_term";
			$terms = $_POST['vocabTerms'];
			
			foreach($terms as $term){
				$wpdb->insert( $table_name, $term);
			}
			
			echo $render->dc_get_property_options();
		}
	}
	
	die();
}

add_action( 'wp_ajax_data_cube_get_prefixes', 'data_cube_get_prefixes' );
function data_cube_get_prefixes(){
	global $wpdb;
	global $render;
	
	$prefixes = $wpdb->get_results("SELECT * FROM wp_dc_prefix 
									ORDER BY prefix, prefix;" );
		
	foreach($prefixes as $prefix){
		echo 	'<tr>
					<td>'. $prefix->prefix .'</td>
					<td>'. $prefix->uri .'</td>
					<td><a class="dc-bt-delete-prefix" data-predixid="'. $prefix->id .'" href="javascript: void(0);">Delete prefix</a></td>
				</tr>';
	}
	
	die();
}

add_action( 'wp_ajax_data_cube_delete_prefix', 'data_cube_delete_prefix' );
function data_cube_delete_prefix(){
	global $wpdb;
	global $render;
	
	if(isset($_POST['prefixID'])) {
		$prefixID = $_POST['prefixID'];
	
		$prefix = $wpdb->get_row( "SELECT * FROM wp_dc_prefix WHERE id = $prefixID" );
		
		$wpdb->delete( 'wp_dc_prefix', array( 'id' => $_POST['prefixID'] ));
		
		$wpdb->query("DELETE FROM wp_dc_vocabulary_term
						WHERE prefixedName LIKE '%". $prefix->prefix .":%';");
	}
	
	echo $render->dc_get_property_options();
	
	die();
}

?>