<?php
require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
include_once( "arc2/ARC2.php" );
require_once( "classDBConfig.php" );
require_once( "functions.php" );

class ControllerDataCube extends DBConfigDataCube
{
	// @var string CSV upload directory name
    public $uploadDir = 'data-cube';

	// @var string delimiter
    public $delim = ";";

    // @var array delilimters supported by CSV importer
    public $delim_avail = array(
        ';',
        ','
    ); 
	
	function dc_init()
	{		
		ini_set('memory_limit', '512M'); 
		
		$this->dc_db_config_create_tables();
	}
	
	function dc_shortcode($atts, $content = null)
	{
		extract( shortcode_atts( array('id' => ''), $atts));
		//echo $id;
	
		?>
			<form method="post" action="">
				<textarea name="dc-sparql-query">
				</textarea>
				
				<input type="submit" value="Send" />
			</form>
		<?php
		
		if($_POST)
		{
			$query = $_POST['dc-sparql-query'];
			
			$config = $this->dc_get_arc2_config();
			$store = ARC2::getStoreEndpoint($config);
			
			/* list result */
			$r = '';
			if ($rows = $store->query($query, 'rows')) {
				$r .= '<tr>';
				foreach($rows[0] as $key => $column)
				{
					if (strpos($key,'type') === false) 
						$r .= '<th>'. $key .'</th>';
				}
				$r .= '</tr>';
			
				foreach ($rows as $row) 
				{
					$r .= '<tr>';
					foreach($row as $key => $column)
					{
						if (strpos($key,'type') === false) 
							$r .= '<td>'. $column .'</td>';
					}
					$r .= '</tr>';
				}
			}

			echo $count;	
			echo $r ? '<table>' . $r . '</table>' : 'no results found';
			
		}
	}
	
	function dc_get_projects($filter = null)
	{
		global $wpdb;
		
		$query = "SELECT * FROM wp_dc_project p ";
		
		if($filter != null){
			$query .= "INNER JOIN wp_dc_classification_project c ON c.wp_dc_project_id = p.id WHERE 1=1 ";
		
			if (trim($filter['dc-filter-ds-title']) != "") $query .= "AND c.dataset_title LIKE '%".$filter['dc-filter-ds-title']."%'";
			if (trim($filter['dc-filter-ds-desc']) != "") $query .= "AND c.dataset_description LIKE '%".$filter['dc-filter-ds-desc']."%'";
			if (trim($filter['dc-filter-ds-creator']) != "") $query .= "AND c.dataset_creator LIKE '%".$filter['dc-filter-ds-creator']."%'";
			if (trim($filter['dc-filter-ds-pub']) != "") $query .= "AND c.dataset_publisher LIKE '%".$filter['dc-filter-ds-pub']."%'";
			if (trim($filter['dc-filter-ds-contr']) != "") $query .= "AND c.dataset_contributor LIKE '%".$filter['dc-filter-ds-contr']."%'";
			if (trim($filter['dc-filter-ds-source']) != "") $query .= "AND c.dataset_source LIKE '%".$filter['dc-filter-ds-source']."%'";
		}
		
		$query .= "ORDER BY date DESC";
		
		$projects = $wpdb->get_results($query);
		
		return $projects;
	}
	
	
	function dc_get_arc2_config()
	{
		$config = array(
			/* db */
			'db_name' => DB_NAME,
			'db_user' => DB_USER,
			'db_pwd' => DB_PASSWORD,
			/* store */
			'store_name' => 'wp_dc_arc',
			/* stop after 100 errors */
			'max_errors' => 100,
			/* endpoint */
			'endpoint_features' => array(
				'select', 'construct', 'ask', 'describe', 
				'load', 'insert', 'delete', 
				'dump' /* dump is a special command for streaming SPOG export */
			),
			'endpoint_timeout' => 60, /* not implemented in ARC2 preview */
			'endpoint_read_key' => '', /* optional */
			'endpoint_write_key' => 'REPLACE_THIS_WITH_SOME_KEY', /* optional, but without one, everyone can write! */
			'endpoint_max_limit' => 250, /* optional */
		);
		
		return $config;
	}
	
	function dc_create_project()
	{
		$name = $_POST['dc-project-name'];
		
		global $wpdb;

		if(!$this->dc_move_file())
		{
			$table_name = 'wp_dc_project';
			$query = "SELECT * FROM $table_name WHERE name = '$name'";
			
			$query_run = mysql_query($query);
			
			if(mysql_num_rows($query_run) == 0) 
			{
				$wpdb->insert( $table_name, array('name' => $name, 'csv_file_name' => $this->dc_csv_file_name, 'delim' => $this->delim ) );
				$this->id_project = $wpdb->insert_id;	
				
				//TODO - Retorno caso tenha gerado algum erro
				
				$this->dc_csv_header();
			}
			else
			{
				//TODO - validação quando existe um projeto com o mesmo nome ou mesmo arquivo
			}
		}
		else
		{
			//TODO - Retorno caso tenha gerado algum erro
		}
	}
	
	function dc_csv_header()
	{
		$this->dc_check_upload_dir_permission();
		ini_set("auto_detect_line_endings", true);
		
		$file = $this->dc_get_upload_directory() . "/$this->dc_csv_file_name";
		// Check whether file is present in the given file location
        $fileexists = file_exists($file);

        if ($fileexists) {
            $resource = fopen($file, 'r');

			$init = 0;
            while ($keys = fgetcsv($resource, '', $this->delim, '"')) {
                if ($init == 0) {
                    $this->headers = $keys;
					break;
				} 
                $init++;
            }
			
            fclose($resource);
            ini_set("auto_detect_line_endings", false);
        } 
	}
	
	function dc_csv_file_data_rows($file, $delim)
	{
		$this->dc_check_upload_dir_permission();
		ini_set("auto_detect_line_endings", true);
		
		$data_rows = array();
		
		# Check whether file is present in the given file location
        $fileexists = file_exists($file);

        if ($fileexists) {
            $resource = fopen($file, 'r');

            $init = 0;
            while ($keys = fgetcsv($resource, '', $this->delim, '"')) {
                if ($init != 0) {
					if (!(($keys[0] == null) && (count($keys) == 1)))
                        array_push($data_rows, $keys);
				} 
				
                $init++;
            }
            fclose($resource);
            ini_set("auto_detect_line_endings", false);
        } else {
			// TODO
		} 
		
		return $data_rows;
	}
	
	/**
     * Check upload directory permission
     */
    function dc_check_upload_dir_permission()
    {
        $this->dc_get_upload_directory();
        $upload_dir = wp_upload_dir();
        if (!is_dir($upload_dir ['basedir'])) {
            print " <div style='font-size:16px;margin-left:20px;margin-top:25px;'>UPLOAD PERMISSION ERROR 
			</div><br/>
			<div style='margin-left:20px;'>
			<form class='add:the-list: validate' method='post' action=''>
			<input type='submit' class='button-primary' name='Import Again' value='IMPORT AGAIN'/>
			</form>
			</div>";
            $this->freeze();
        } else {
            if (!is_dir($this->dc_get_upload_directory())) {
                wp_mkdir_p($this->dc_get_upload_directory());
            }
        }
    }
	
	/**
    * Get upload directory
    */
    function dc_get_upload_directory()
    {
        $upload_dir = wp_upload_dir();
        return $upload_dir ['basedir'] . "/" . $this->uploadDir;
    }
	
	/**
     * Move CSV to the upload directory
     */
    function dc_move_file()
    {
        $error = true;
		
		if ($_FILES ["dc-csv-file"] ["error"] == 0) {
            $tmp_name = $_FILES ["dc-csv-file"] ["tmp_name"];
			$this->dc_csv_file_name = $_FILES ["dc-csv-file"] ["name"];
			move_uploaded_file($tmp_name, $this->dc_get_upload_directory() . "/$this->dc_csv_file_name");
        
			$error = false;
		}
		
		return $error;
    }
	
	/**
    * Exit operation
    *
    * @param $str string
    *            to display
    */
    function freeze($str = "")
    {
        die ($str);
    }
	
	function dc_create_model($form)
	{
		//$form = $_POST;
	
		global $wpdb;
		$table_name = 'wp_dc_project';
		$dc_project_id = $form['dc-project-id'];
		$query = "SELECT * FROM $table_name WHERE id = $dc_project_id ";
		$dc_project = $wpdb->get_row($query);
		
		$csv_file = $this->dc_get_upload_directory() . "/" . $dc_project->csv_file_name; 
		
		$data_rows = $this->dc_csv_file_data_rows($csv_file, $dc_project->delim);
	
		$baseURI = $form['dc-base-uri'];
		$model			= $this->dc_get_prefix();
		$dimensions		= array();
		$measures		= array();
		$uri			= array();
		$projectSample	= array(); 
		$samplePattern	= array(); 
		
		
		foreach($form['dc-header-option'] as $key => $value)
		{
			if($form['dc-header-deleted'][$key] == 'false')
			{
				(($form['dc-header-property'][$key] != '0') && ($form['dc-header-property'][$key] != '') && $form['dc-header-property'][$key]) ? $propItem = $form['dc-header-property'][$key] . ',' : $propItem = '';
			
				$item = array(	'key' => $key, 
								'uri' => $baseURI . $form['dc-header-uri'][$key], 
								'label' => utf8_strtr($form['dc-header'][$key]),
								'property' => $propItem,
								'content-cell' => $form['dc-cells-content'][$key], 
								'language-tagged' => $form['dc-language-tagged'][$key]);
				
				$samplePattern[$key] =  array(	'wp_dc_project_id' => $dc_project_id, 
											    'subject' => $form['dc-header'][$key],
												'type' => rtrim($propItem,','));
			
				if($value == "dc-dimension")
					$dimensions[] =  $item;
				else
					$measures[] =  $item;
			}			
		}
		
		foreach($dimensions as $dimension)
		{
			$model .= '<'.$dimension['uri'] .'>  a rdf:Property, '.$dimension['property'] .' qb:DimensionProperty;'. PHP_EOL
						.'rdfs:label "'. $dimension['label'] .'".'. PHP_EOL;
		}
		
		foreach($measures as $measure)
		{
			$model .= '<'. $measure['uri'] .'>  a rdf:Property, '.$measure['property'] .' qb:MeasureProperty;' . PHP_EOL
						.'rdfs:label "'. $measure['label'] .'".'. PHP_EOL;
		} 
	
		// Data Structure Definition
		if($form['dc-dataset-structure-uri'] != ''){		
			$datasetStructure = '<'. $baseURI . $form['dc-dataset-structure-uri'] .'>';
			$model.= $datasetStructure . ' a qb:DataStructureDefinition;' . PHP_EOL
						.'# The dimensions' . PHP_EOL;
			
			$order = 1;
			foreach($dimensions as $dimension)
			{
				$model .= 'qb:component [ qb:dimension <'. $dimension['uri'] .'>;	qb:order '. $order .' ];' . PHP_EOL;
				$order++;
			}
			
			$model .= '# The measure(s)'. PHP_EOL;
			foreach($measures as $measure)
			{
				$model .= 'qb:component [ qb:measure <'. $measure['uri'] .'> ];' . PHP_EOL;
			}
			
			$model = rtrim($model, ';'. PHP_EOL);
			$model .= '.' . PHP_EOL;
		}
		
		($form['dc-dataset-description'] != "") ? $datasetDescription = 'dc:description "'. $form['dc-dataset-description'] .'";' : $datasetDescription = '';
		($form['dc-dataset-creator'] != "") ? $datasetCreator = 'dc:creator "'. $form['dc-dataset-creator'] .'";' : $datasetCreator	= '';
		($form['dc-dataset-publisher'] != "") ? $datasetPublisher = 'dc:publisher "'. $form['dc-dataset-publisher'] .'";' : $datasetPublisher = '';
		($form['dc-dataset-contributor'] != "") ? $datasetContributor = 'dc:contributor "'. $form['dc-dataset-contributor'] .'";' : $datasetContributor	= '';
		($form['dc-dataset-source'] != "") ? $datasetSource	= 'dc:source "'. $form['dc-dataset-source'] .'";' : $datasetSource = '';
		
		// Dataset Definition
		$model .=	 '<'. $baseURI . $form['dc-dataset-uri'] .'> a qb:DataSet;' . PHP_EOL
					.'dc:title "'. $form['dc-dataset-title'] .'";' . PHP_EOL
					//.'foaf:homepage "'. $form['dc-dataset-homepage'] .'";' . PHP_EOL
					. $datasetDescription . PHP_EOL
					. $datasetCreator . PHP_EOL
					. $datasetPublisher . PHP_EOL
					. $datasetContributor . PHP_EOL
					. $datasetSource . PHP_EOL
					. 'qb:structure ' . $datasetStructure . '.' . PHP_EOL;
		
		$count = 1;
		foreach($data_rows as $row)
		{
			$model .= '<' .$baseURI .'#'. $count .'> a qb:Observation;' . PHP_EOL
					. 'qb:DataSet <'. $baseURI . $form['dc-dataset-uri'] .'>;' . PHP_EOL;
			
			foreach($row as $key => $column)
			{
				foreach($dimensions as $dimension)
				{
					if($dimension['key'] == $key){
						$model .= '<'.$dimension['uri'] .'>			'. $this->dc_format_content_cell($column, $dimension['content-cell'], $dimension['language-tagged']) .' ;' . PHP_EOL;
				
						if($count <= 5){
							$item = $samplePattern[$key];
							$item['object'] = $column;
							$item['row_index'] = $count;
							$projectSample[$count][] = $item; 
						}
					}	
				}
				foreach($measures as $measure)
				{
					if($measure['key'] == $key){
						$model .= '<'.$measure['uri'] .'>			'. $this->dc_format_content_cell($column, $measure['content-cell'], $dimension['language-tagged']) .' ;' . PHP_EOL;
					
						if($count <= 5){
							$item = $samplePattern[$key];
							$item['object'] = $column;
							$item['row_index'] = $count;
							$projectSample[$count][] = $item; 
						}
					}
				}
			}
			
			$model = rtrim($model, ';');
			$model .= '.' . PHP_EOL;
			$count++;
		}
		
		$fp = fopen($this->dc_get_upload_directory() . '/project'. $dc_project->id .'.ttl', 'w');
		fwrite($fp, $model);
		fclose($fp);
		
		if($form['dc-is-update'] == "false")
		{
			$this->dc_insert_classification_project($form);
			$this->dc_insert_sample_project($projectSample);
			$this->dc_arc_insert_triples($this->dc_get_upload_directory() . '/project'. $dc_project->id .'.ttl');
			
		}
		else
		{
			$this->dc_update_project($form, $projectSample);
		}
	}
	
	function dc_format_content_cell($content, $option, $langtagged)
	{
		$result = "";
		$content = format_property($content);
		
		if($option == 'uri'){
			$result = "<".$content.">";
		}
		else if($option == 'text'){
			$result = '"'. $content .'"';
		}
		else if($option == 'lang-tagged'){
			$result = '"'. $content .'"@'.$langtagged;
		}
		else if($option == 'int'){
			$result = '"'. $content .'"^^xsd:int';
		}
		else if($option == 'non-int'){
			$result = '"'. $content .'"^^xsd:double';
		}
		else if($option == 'date'){
			$result = '"'. $content .'"^^xsd:date';
		}
		else if($option == 'dateTime'){
			$result = '"'. $content .'"^^xsd:dateTime';
		}
		else if($option == 'boolean'){
			$result = '"'. $content .'"^^xsd:boolean';
		}
		
		return $result;
	}
	
	function dc_get_prefix()
	{
		$prefix = '@prefix rdfs: <http://www.w3.org/2000/01/rdf-schema#> .' . PHP_EOL;
		$prefix .= '@prefix rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> .'. PHP_EOL;
		$prefix .= '@prefix qb: <http://purl.org/linked-data/cube#>.' . PHP_EOL;
		$prefix .= '@prefix dc: <http://purl.org/dc/elements/1.1>.' . PHP_EOL;
		$prefix .= '@prefix foaf: <http://xmlns.com/foaf/0.1>.' . PHP_EOL;
		
		global $wpdb;
		
		$properties = $wpdb->get_results("SELECT DISTINCT * FROM wp_dc_prefix;" );
		
		foreach($properties as $property){
			$prefix .= '@prefix ' . $property->prefix . ': <' . $property->uri . '>.'  . PHP_EOL;
		}
		
		return $prefix;
	}
	
	function dc_insert_classification_project($form)
	{
		global $wpdb;
		
		$table_name = "wp_dc_classification_project";
		$classificationProject = array(	"wp_dc_project_id" => $form['dc-project-id'],
										"base_uri" => $form['dc-base-uri'],
										"data_structure_uri" => $form['dc-dataset-structure-uri'],
										"dataset_uri" => $form['dc-dataset-uri'],
										"dataset_title" => $form['dc-dataset-title'],
										"dataset_description" => $form['dc-dataset-description'],
										"dataset_creator" => $form['dc-dataset-creator'],
										"dataset_publisher" => $form['dc-dataset-publisher'],
										"dataset_contributor" => $form['dc-dataset-contributor'],
										"dataset_source" => $form['dc-dataset-source']);
		
		$wpdb->insert( $table_name, $classificationProject);
		
		$table_name = "wp_dc_column_classification_project";
		foreach($form['dc-header-option'] as $key => $value)
		{
			if($value == "dc-dimension"){
				$isDimension =  1;
				$isMeasure =  0;
			}
			else{
				$isDimension =  0;
				$isMeasure =  1;
			}
		
			$deleted = 0;
			if($form['dc-header-deleted'][$key] != 'false') $deleted = 1;
		
			$column = array("wp_dc_project_id" => $form['dc-project-id'],
							"index_column" => $key,
							"label" => $form['dc-header'][$key],
							"uri" => $form['dc-header-uri'][$key],
							"is_dimension" => $isDimension,
							"is_measure" => $isMeasure,
							"property" => $form['dc-header-property'][$key],
							"property_cell_content" => $form['dc-cells-content'][$key],
							"language_tagged" => $form['dc-language-tagged'][$key],
							"deleted" => $deleted,
			);

			$wpdb->insert( $table_name, $column);
		}
		
	}
	
	function dc_update_project($form, $projectSample){
	
		$projectID = $form['dc-project-id'];
		$this->dc_delete_project($projectID, true);
		
		$this->dc_update_classification_project($form);
		$this->dc_insert_sample_project($projectSample);
		$this->dc_arc_insert_triples($this->dc_get_upload_directory() . '/project'. $projectID .'.ttl');
	}
	
	function dc_update_classification_project($form)
	{
		global $wpdb;
		$projectID = $form['dc-project-id'];
		
		$table_name = "wp_dc_classification_project";
		$where = array( 'wp_dc_project_id' => $projectID );
		
		$classificationProject = array(	"wp_dc_project_id" => $form['dc-project-id'],
										"base_uri" => $form['dc-base-uri'],
										"data_structure_uri" => $form['dc-dataset-structure-uri'],
										"dataset_uri" => $form['dc-dataset-uri'],
										"dataset_title" => $form['dc-dataset-title'],
										"dataset_description" => $form['dc-dataset-description'],
										"dataset_creator" => $form['dc-dataset-creator'],
										"dataset_publisher" => $form['dc-dataset-publisher'],
										"dataset_contributor" => $form['dc-dataset-contributor'],
										"dataset_source" => $form['dc-dataset-source']);
		
		$wpdb->update( $table_name, $classificationProject, $where);
		
		$table_name = "wp_dc_column_classification_project";
		foreach($form['dc-header-option'] as $key => $value)
		{
			if($value == "dc-dimension"){
				$isDimension =  1;
				$isMeasure =  0;
			}
			else{
				$isDimension =  0;
				$isMeasure =  1;
			}
		
			$deleted = 0;
			if($form['dc-header-deleted'][$key] != 'false') $deleted = 1;
		
			$column = array("wp_dc_project_id" => $form['dc-project-id'],
							"index_column" => $key,
							"label" => $form['dc-header'][$key],
							"uri" => $form['dc-header-uri'][$key],
							"is_dimension" => $isDimension,
							"is_measure" => $isMeasure,
							"property" => $form['dc-header-property'][$key],
							"property_cell_content" => $form['dc-cells-content'][$key],
							"language_tagged" => $form['dc-language-tagged'][$key],
							"deleted" => $deleted,
			);
			$where = array( 'wp_dc_project_id' => $projectID , "index_column" => $key);	
			$wpdb->update( $table_name, $column, $where);
		}
		
	}
	
	function dc_delete_project($projectID, $update = false){
		global $wpdb;
	
		$file = $this->dc_get_upload_directory() . '/project'. $projectID .'.ttl';
		$doc ='file:'.$file;
		
		$parser = ARC2::getTurtleParser();
		$parser->parse($doc);
		
		$config = $this->dc_get_arc2_config();
		$store = ARC2::getStoreEndpoint($config);
		$triples = $parser->getTriples();
		$store->delete(null, $doc);
		
		if(!$update) $wpdb->delete( 'wp_dc_project', array( 'id' => $projectID ));
		$wpdb->delete( 'wp_dc_sample_project', array( 'wp_dc_project_id' => $projectID ));
		
		if(file_exists($this->dc_get_upload_directory() . '/project'. $projectID .'.ttl') && !$update) 
			unlink($this->dc_get_upload_directory() . '/project'. $projectID .'.ttl');
		
	}
	
	function dc_insert_sample_project($projectSample){
		global $wpdb;
		$table_name = "wp_dc_sample_project";
	
		foreach($projectSample as $sampleRow){
			foreach($sampleRow as $sample){
					$wpdb->insert( $table_name, $sample);
			}
		}
	}
	
	function dc_arc_insert_triples($file)
	{
		$parser = ARC2::getTurtleParser();
		
		$doc ='file:'.$file;
		$parser->parse($doc);
		
		$config = $this->dc_get_arc2_config();
		$store = ARC2::getStoreEndpoint($config);
		$triples = $parser->getTriples();
		$store->insert($triples, $doc);
		
		//$errs = $parser->getErrors();
		//echo print_r($errs);
	}
	
	function dc_get_classification_project($projectID){
		global $wpdb;
		
		$classificationProject = $wpdb->get_row( "SELECT * FROM wp_dc_classification_project
												 WHERE wp_dc_project_id = $projectID;" );
		return $classificationProject;
	}
	
	function dc_get_classification_columns($projectID){
		global $wpdb;
		
		$classificationColumns = $wpdb->get_results( "SELECT * FROM wp_dc_column_classification_project
													 WHERE wp_dc_project_id = $projectID;" );
		return $classificationColumns;
	}
	
	function dc_get_project_sample($projectID){
		global $wpdb;
		
		$sampleProject = $wpdb->get_results("SELECT * FROM wp_dc_sample_project 
											WHERE wp_dc_project_id = $projectID
											ORDER BY row_index, subject;" );
		
		return $sampleProject;
	}
	
	function dc_get_project_name($projectID){
		global $wpdb;
		
		$project = $wpdb->get_row( "SELECT * FROM wp_dc_project WHERE id = $projectID" );
		
		echo $project->name;
	}
}
?>