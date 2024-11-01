<?php
require_once( "classSettings.php" );

class DBConfigDataCube extends SettingsDataCube
{
	function dc_db_config_create_tables()
	{
		global $wpdb;
		$table_name = "wp_dc_vocabulary_term";
		if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
			$sql = "CREATE TABLE $table_name (
						id mediumint(9) NOT NULL AUTO_INCREMENT,
						prefixedName VARCHAR(55) NOT NULL,
						uri VARCHAR(55) NOT NULL,
						localName VARCHAR(55) NOT NULL,
						type VARCHAR(55),
						UNIQUE KEY id (id)
					);";

			
			dbDelta( $sql );
			
			foreach($this->properties as $property)
			{
				$wpdb->insert( $table_name, $property );
			}			
		}
		
		$table_name = "wp_dc_prefix";
		if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
			$sql = "CREATE TABLE $table_name (
						id mediumint(9) NOT NULL AUTO_INCREMENT,
						prefix VARCHAR(55) NOT NULL,
						uri VARCHAR(100) NOT NULL,
						UNIQUE KEY id (id)
					);";

			
			dbDelta( $sql );
			
			/*foreach($this->properties as $property)
			{
				$wpdb->insert( $table_name, $property );
			}*/			
		}
		
		$table_name = "wp_dc_project";
		if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
			$sql = "CREATE TABLE $table_name (
						id mediumint(9) NOT NULL AUTO_INCREMENT,
						name VARCHAR(45) NOT NULL,
						csv_file_name VARCHAR(100) NOT NULL,
						delim VARCHAR(1) NOT NULL,
						date DATETIME DEFAULT CURRENT_TIMESTAMP,
						UNIQUE KEY id (id)
					);";
					
			dbDelta( $sql );
		}
		
		$table_name = "wp_dc_sample_project";
		if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
			$sql = "CREATE TABLE $table_name (
						id mediumint(9) NOT NULL AUTO_INCREMENT,
						wp_dc_project_id mediumint(9) NOT NULL,
						subject VARCHAR(45) NOT NULL,
						object VARCHAR(45) NOT NULL,
						type VARCHAR(45) NOT NULL,
						row_index mediumint(9) NOT NULL,
						UNIQUE KEY id (id)
					);";
					
			dbDelta( $sql );
		}
		
		$table_name = "wp_dc_classification_project";
		if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
			$sql = "CREATE TABLE $table_name (
						id mediumint(9) NOT NULL AUTO_INCREMENT,
						wp_dc_project_id mediumint(9) NOT NULL,
						base_uri VARCHAR(100) NOT NULL,
						data_structure_uri VARCHAR(45) NOT NULL,
						dataset_uri VARCHAR(45) NOT NULL,
						dataset_title VARCHAR(45) NOT NULL,
						dataset_description VARCHAR(500),
						dataset_creator VARCHAR(45),
						dataset_publisher VARCHAR(45),
						dataset_contributor VARCHAR(45),
						dataset_source VARCHAR(100),
						UNIQUE KEY id (id)
					);";
					
			dbDelta( $sql );
			
		}
		
		$table_name = "wp_dc_column_classification_project";
		if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
			$sql = "CREATE TABLE $table_name (
						id mediumint(9) NOT NULL AUTO_INCREMENT,
						wp_dc_project_id mediumint(9) NOT NULL,
						index_column mediumint(3) NOT NULL,
						label VARCHAR(45) NOT NULL,
						uri VARCHAR(45) NOT NULL,
						is_dimension BOOLEAN NOT NULL default 0,
						is_measure BOOLEAN NOT NULL default 0,
						property VARCHAR(45),
						property_cell_content VARCHAR(45),
						language_tagged VARCHAR(45),
						deleted BOOLEAN NOT NULL default 0,
						UNIQUE KEY id (id)
					);";
					
			dbDelta( $sql );
			
		}
		
		$config = $this->dc_get_arc2_config();
		$store = ARC2::getStoreEndpoint($config);
		if (!$store->isSetUp()) {
			$store->setUp();
		}
	}
}
 
?>