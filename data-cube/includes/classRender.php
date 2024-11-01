<?php
class RenderDataCube
{
	/**
    * Render upload csv form
    */
    function dc_render_upload_form()
    {
		?>
		<div class="form-rouded flat-form">
			<h3 class="flat-form-title close">New project</h3>
			<form id="dc-csv-form" class="pure-form pure-form-aligned flat-component" action="" method="post" enctype="multipart/form-data">
				<div class="pure-control-group">
					<label>Name <span class="required-field">*</span></label>
					<input id="dc-project-name" name="dc-project-name" type="text" value="" data-validate="validate(required)"/>
				</div>
				<div class="pure-control-group">
					<label>File <span class="required-field">*</span></label>
					<input id="dc-file" name="dc-csv-file" type="file" value="" data-validate="validate(required)" />
				</div>
				<div class="pure-control-group">
					<label>Delimiter </label>
					<select name="dc-delim" id="dc-delim">
							<option value=";">;</option>
							<option value=",">,</option>
					</select>
				</div>
				
				<input type="submit" class="pure-button pure-button-primary left-spacing" value="Send" name="dc-import"/>
					
			</form>
		</div><?php
	}
	
	function dc_render_filter_form(){
	?>
		<div class="form-rouded flat-form">
			<h3 class="flat-form-title close">Filter</h3>
			<form id="" class="pure-form pure-form-aligned flat-component" action="" method="post" enctype="multipart/form-data">
				<div class="pure-control-group">
					<label>Dataset title </label>
					<input name="dc-filter-ds-title" type="text" value="" />
					<label>Dataset description </label>
					<input name="dc-filter-ds-desc" type="text" value="" />
				</div>
				<div class="pure-control-group">
					<label>Creator</label>
					<input name="dc-filter-ds-creator" type="text" value="" />
					<label>Publisher</label>
					<input name="dc-filter-ds-pub" type="text" value="" />
				</div>
				<div class="pure-control-group">
					<label>Contributor</label>
					<input name="dc-filter-ds-contr" type="text" value="" />
					<label>Source</label>
					<input name="dc-filter-ds-source" type="text" value="" />
				</div>
				
				<input type="submit" class="pure-button pure-button-primary left-spacing" value="Send" name="dc-filter"/>
					
			</form>
		</div>
		<?php
	}
	
	function dc_render_classification_form($headers, $dc_id_project)
	{
		?>
		<form id="dc-header-options" class="pure-form pure-form-aligned" action="" method="post">
			<div class="form-rouded vertical-spacing">
				<div class="pure-control-group">
					<label>Base URI:<span class="required-field">*</span></label>
					<input name="dc-base-uri" type="text" data-validate="validate(required, url)"/>
					<label>URI Data Structure</label>
					<input name="dc-dataset-structure-uri" type="text"/>
				</div>
			</div>
			<div class="form-rouded">
				<h3 class="flat-form-title close">Dataset Description</h3>
					<div class="flat-component vertical-spacing">
						<?php $this->dc_render_dataset_form(); ?>
					</div>
			</div>
			<ul>
			<?php
				if(!empty($headers))
				{
					foreach ($headers as $key => $value) 
					{
						?>
						<li class="form-rouded flat-form">
							<h3 class="flat-form-title close"><?php echo utf8_encode($value) ?></h3>
							<div class="flat-component vertical-spacing">
								<a class="dc-delete-column" href="javascript:void(0)">Delete</a>
								<div class="pure-control-group">
									<label>Label <span class="required-field">*</span></label>
									<input name="dc-header[<?php echo $key ?>]" type="text" value="<?php echo utf8_encode($value) ?>" data-validate="validate(required)"/>						
								</div>
								<div class="pure-control-group">
									<label>URI <span class="required-field">*</span></label>
									<input name="dc-header-uri[<?php echo $key ?>]" type="text" data-validate="validate(required)" />
								</div>
								<div class="pure-control-group">
									<label class="pure-radio">
										<input name="dc-header-option[<?php echo $key ?>]" type="radio" value="dc-dimension" checked="checked"/>
										Dimension
									</label> 
									<label class="pure-radio">
										<input name="dc-header-option[<?php echo $key ?>]" type="radio" value="dc-measure" />		
										Measure
									</label>
								</div>
								<div class="pure-control-group">
									<label>Property</label>
									<?php echo $this->dc_render_property_options($key); ?>
									<a class="dc-bt-add-prefix" href="javascript:void(0)">Add Prefix</a>
								</div>
								<?php echo $this->dc_render_cells_content_options($key);?>
								<input name="dc-header-deleted[<?php echo $key ?>]" type="hidden" value="false" class="dc-df">
							</div>
						</li>	
						<?php
					}
				}
				?>
			</ul>
			<input name="dc-is-update" type="hidden" value="false" />
			<input name="dc-project-id" type="hidden" value="<?php echo $dc_id_project; ?>"/>
			<a id="dc-class-form-submit" href="javascript:void(0)" class="pure-button pure-button-primary">Send</a>
			<!-- <input name="dc-classification-form" type="submit" value="send"/> -->
		</form>
		<?php
		$this->dc_render_prefix_dialogs();
	}
	
	function dc_render_classification_edit_form($classificationProject, $classificationColumns, $projectID)
	{
		$this->dc_render_help();
		
		?>
		
		<form id="dc-header-options" class="pure-form pure-form-aligned" action="" method="post">
			<div class="form-rouded vertical-spacing">
				<div class="pure-control-group">
					<label>Base URI:<span class="required-field">*</span></label>
					<input name="dc-base-uri" type="text" value="<?php echo $classificationProject->base_uri ?>" data-validate="validate(required)"/>
					<label>URI Data Structure</label>
					<input name="dc-dataset-structure-uri" type="text" value="<?php echo $classificationProject->data_structure_uri ?>"/>
				</div>
			</div>
			<div class="form-rouded">
				<h3 class="flat-form-title close">Dataset Description</h3>
					<div class="flat-component vertical-spacing">
						<div class="pure-control-group">
							<label>URI <span class="required-field">*</span></label>
							<input name="dc-dataset-uri" type="text" value="<?php echo $classificationProject->dataset_uri ?>" data-validate="validate(required)"/>
							<label>Title <span class="required-field">*</span></label>
							<input name="dc-dataset-title" type="text" value="<?php echo $classificationProject->dataset_title ?>" data-validate="validate(required)"/>
						</div>
						<div class="pure-control-group">
							<label>Description</label>
							<input name="dc-dataset-description" type="text" value="<?php echo $classificationProject->dataset_description ?>"/>
							<label>Creator</label>
							<input name="dc-dataset-creator" type="text" value="<?php echo $classificationProject->dataset_creator ?>"/>
						</div>
						<div class="pure-control-group">
							<label>Publisher</label>
							<input name="dc-dataset-publisher" type="text" value="<?php echo $classificationProject->dataset_publisher ?>"/>
							<label>Contributor</label>
							<input name="dc-dataset-contributor" type="text" value="<?php echo $classificationProject->dataset_contributor ?>"/>
						</div>
						<div class="pure-control-group">
							<label>Source</label>
							<input name="dc-dataset-source" type="text" value="<?php echo $classificationProject->dataset_source ?>"/>
						</div>
					</div>
			</div>
			<ul>
			<?php
				if(!empty($classificationColumns))
				{
					foreach ($classificationColumns as $key => $column) 
					{
						?>
						<li class="form-rouded flat-form">
							<h3 class="flat-form-title close"><?php echo $column->label ?></h3>
							<div class="flat-component vertical-spacing">
								<a class="dc-delete-column" href="javascript:void(0)">Delete</a>
								<div class="pure-control-group">
									<label>Label <span class="required-field">*</span></label>
									<input name="dc-header[<?php echo $key ?>]" type="text" value="<?php echo $column->label ?>" data-validate="validate(required)"/>						
								</div>
								<div class="pure-control-group">
									<label>URI <span class="required-field">*</span></label>
									<input name="dc-header-uri[<?php echo $key ?>]" type="text" value="<?php echo $column->uri ?>" data-validate="validate(required)"/>
								</div>
								<div class="pure-control-group">
									<label class="pure-radio">
										<input name="dc-header-option[<?php echo $key ?>]" type="radio" value="dc-dimension" <?php if($column->is_dimension) echo 'checked="checked"' ?> />
										Dimension
									</label> 
									<label class="pure-radio">
										<input name="dc-header-option[<?php echo $key ?>]" type="radio" value="dc-measure" <?php if($column->is_measure) echo 'checked="checked"' ?>/>		
										Measure
									</label>
								</div>
								<div class="pure-control-group">
									<label>Property</label>
									<?php echo $this->dc_render_property_options($key, $column->property); ?>
									<a class="dc-bt-add-prefix" href="javascript:void(0)">Add Prefix</a>
									<a class="dc-bt-manage-prefixes" href="javascript:void(0)">Manage Prefixes</a>
								</div>
								<?php echo $this->dc_render_cells_content_options($key, $column->property_cell_content, $column->language_tagged);?>
								
								<?php ($column->deleted) ? $column->deleted = "true" : $column->deleted = "false"?>
								
								<input name="dc-header-deleted[<?php echo $key ?>]" type="hidden" value="<?php echo $column->deleted?>" class="dc-df">
							</div>
						</li>	
						<?php
					}
				}
				?>
			</ul>
			<input name="dc-is-update" type="hidden" value="true" />
			<input name="dc-project-id" type="hidden" value="<?php echo $projectID; ?>"/>
			<a id="dc-class-form-submit" href="javascript:void(0)" class="pure-button pure-button-primary">Send</a>
			<!-- <input name="dc-classification-form" type="submit" value="send"/> -->
		</form>
		
		<?php
		$this->dc_render_prefix_dialogs();
	}
	
	function dc_render_prefix_dialogs()
	{
		?>
		<div id="dc-add-prefixes">
			<form class="pure-form pure-form-aligned" method="post" action="">
				<div class="pure-control-group">
					<label>Prefix</label>
					<input id="dc-prefix-search" type="text"/>
					<input type="submit" value="Search" class="pure-button pure-button-primary"/>
				</div>
			</form>
			
			<div id="dc-prefix-results">
				<table class="dc-table widefat">
					<tr>
						<th>Prefix</th>
						<th>URI</th>
						<th></th>
					</tr>
				</table>
			</div>
		</div>
		
		<div id="dc-manage-prefixes">
			<div id="dc-list-prefix">
				<table class="dc-table widefat">
					<tr>
						<th>Prefix</th>
						<th>URI</th>
						<th></th>
					</tr>
				</table>
			</div>
		</div>
		<?php
	}
	
	/**
    * Render properties options for header classification form 
    */
	function dc_render_property_options($key, $propery = null)
	{
		$options = $this->dc_get_property_options($propery);
		
		$select =	'<select name="dc-header-property['. $key .']" class="dc-header-options">'
						. $options
					.'</select>';
		
		return $select;
	}
	
	function dc_get_property_options($propery = null)
	{
		global $wpdb;
		$properties = $wpdb->get_results( "SELECT * FROM wp_dc_vocabulary_term ORDER BY prefixedName" );
		
		$result = "<option value='0'>-- Select --</option>";
		foreach ( $properties as $property ) {
			
			$selected = '';
			if(($propery != null) && ($propery == $property->prefixedName)) $selected = 'selected="selected"';
			
			$result .= '<option '. $selected .' value="' . $property->prefixedName . '">' . $property->prefixedName . '</option>';
		}
		
		return $result;
	}
	
	function dc_render_cells_content_options($key, $propertyCellContent = null, $languageTagged = null)
	{
		?>
			<div class="dc-cells-content-options">
				<h4>The cell's content is used...</h4>
				<div class="pure-control-group">
					<label class="pure-radio">
						<input name="dc-cells-content[<?php echo $key?>]" type="radio" value="uri" <?php if($propertyCellContent == "uri") echo 'checked="checked"' ?>/> as URI
					</label>
				</div>
				<div class="pure-control-group">
					<label class="pure-radio">
						<input name="dc-cells-content[<?php echo $key?>]" type="radio" value="text" <?php if($propertyCellContent == "text") echo 'checked="checked"' ?>/> as text 
					</label>
				</div>
				<div class="pure-control-group">
					<label class="pure-radio">
						<input name="dc-cells-content[<?php echo $key?>]" type="radio" value="lang-tagged" <?php if($propertyCellContent == "lang-tagged") echo 'checked="checked"' ?>/> as language-tagged text <input name="dc-language-tagged[<?php echo $key?>]" type="text" value="<?php if($propertyCellContent == "lang-tagged") echo $languageTagged ?>" />
					</label>
				</div>
				<div class="pure-control-group">
					<label class="pure-radio">
						<input name="dc-cells-content[<?php echo $key?>]" type="radio" value="int" <?php if($propertyCellContent == "int") echo 'checked="checked"' ?>/> as integer number 
					</label>
				</div>
				<div class="pure-control-group">
					<label class="pure-radio">
						<input name="dc-cells-content[<?php echo $key?>]" type="radio" value="non-int" <?php if($propertyCellContent == "non-int") echo 'checked="checked"' ?>/> as non-integer number 
					</label>
				</div>
				<div class="pure-control-group">
					<label class="pure-radio">
						<input name="dc-cells-content[<?php echo $key?>]" type="radio" value="date" <?php if($propertyCellContent == "date") echo 'checked="checked"' ?>/> as date (YYYY-MM-DD) 
					</label>
				</div>
				<div class="pure-control-group">
					<label class="pure-radio">
						<input name="dc-cells-content[<?php echo $key?>]" type="radio" value="dateTime" <?php if($propertyCellContent == "dateTime") echo 'checked="checked"' ?>/> as dateTime (YYYY-MM-DD HH:MM:SS) 
					</label>
				</div>
				<div class="pure-control-group">
					<label class="pure-radio">
						<input name="dc-cells-content[<?php echo $key?>]" type="radio" value="boolean" <?php if($propertyCellContent == "boolean") echo 'checked="checked"' ?>/> as boolean 
					</label>
				</div>
			</div>
		<?php
	}
	
	function dc_render_dataset_form(){
		?>
			<div class="pure-control-group">
				<label>URI <span class="required-field">*</span></label>
				<input name="dc-dataset-uri" type="text" data-validate="validate(required)"/>
				<label>Title <span class="required-field">*</span></label>
				<input name="dc-dataset-title" type="text" data-validate="validate(required)"/>
			</div>
			<div class="pure-control-group">
				<!-- <label>Homepage</label>
				<input name="dc-dataset-homepage" type="text"/> -->
				<label>Description</label>
				<input name="dc-dataset-description" type="text"/>
				<label>Creator</label>
				<input name="dc-dataset-creator" type="text"/>
			</div>
			<div class="pure-control-group">
				<label>Publisher</label>
				<input name="dc-dataset-publisher" type="text"/>
				<label>Contributor</label>
				<input name="dc-dataset-contributor" type="text"/>
			</div>
			<div class="pure-control-group">
				<label>Source</label>
				<input name="dc-dataset-source" type="text"/>
			</div>
		<?php
	}
	
	function dc_render_projects_table($projects)
	{
		?>
			<table id="dc-table-projects" class="widefat dc-table">
				<tr>
					<th>Name</th>
					<th>Date</th>
					<th>CSV</th>
					<th>Model</th>
					<th>Edit</th>
					<th>Delete</th>
				</tr>
				<?php
				$i = 0;
				foreach($projects as $project)
				{
					?>
					<tr <?php if(($i%2) == 0) echo 'class="alternate"' ?>>
						<td><a href="?page=wp-data-cube&project=<?php echo $project->id ?>"><?php echo $project->name ?></a></td>
						<td><?php echo $project->date ?></td>
						<td><?php echo $project->csv_file_name ?></td>
						<td><a class="dc-download-model" href="javascript:void(0)" data-id-project="<?php echo $project->id ?>"data_cube_submit_data><span>Download</span></a></td>
						<td><a href="?page=wp-data-cube&edit=<?php echo $project->id ?>" class="dc-edit-project"><span>Edit</span></a></td>
						<td><a class="dc-delete-project" href="javascript:void(0)" data-id-project="<?php echo $project->id ?>"data_cube_submit_data><span>Delete</span></a></td>
					</tr>
					<?php
					$i++;
				}
				?>
			</table>
			
		<?php
	}
	
	function dc_render_sample_project_table($sampleProject)
	{
		$currentRow = 0;
			
		?>
		<table class="widefat dc-table" style="margin-top: 15px;">
			<tr>
			<?php
			$i = 0;
			foreach($sampleProject as $sample)
			{
				if($currentRow != $sample->row_index){
					$currentRow = $sample->row_index;
					
					?>
					</tr>
					<tr <?php if(($i%2) == 0) echo 'class="alternate"' ?>>
						<td>
							<ul>
								<li>Subject: <?php echo $sample->subject ?></li>
								<li>Object: <?php echo $sample->object  ?></li>
								<li>Type: <?php echo $sample->type ?></li>
							</ul>
						</td>
					<?php
					$i++;
				}else{
					?>
					<td>
						<ul>
							<li>Subject: <?php echo $sample->subject ?></li>
							<li>Object: <?php echo $sample->object  ?></li>
							<li>Type: <?php echo $sample->type ?></li>
						</ul>
					</td>
					<?php
				}
				$i++;
			} 
			?>
			</tr>
		</table>
		<?php
	}
	
	function dc_render_help()
	{
		?>
			<div id="dc-help">
				<ul>
					<li><strong>Base URI: </strong> Fill with the URI root.</li>
					<li><strong>URI Data Structure: </strong> Fill with the URI of the defines the structure of dataset that is relative to base URI.</li>
					<li class="dc-title-help"><h3>Dataset Description</h3></li>
					<li><strong>URI: </strong> Fill with the URI of the resource that is relative to base URI.</li>
					<li><strong>Title: </strong> Fill with the name given to the resource.</li>
					<li><strong>Publisher: </strong> Fill with the entity responsible for making the resource available.</li>
					<li><strong>Description: </strong> Fill with the account of the resource.</li>
					<li><strong>Creator: </strong> Fill with the entity primarily responsible for making the resource.</li>
					<li><strong>Contributor: </strong> Fill with the entity responsible for making contributions to the resource.</li>
					<li><strong>Source: </strong> Fill with the related resource from which the described resource is derived.</li>
					<li class="dc-title-help"><h3>For each data header item imported from CSV file:</h3></li>
					<li><strong>Label: </strong> Fill with the human-readable version of a resource's (dimension/measure) name.</li>
					<li><strong>URI: </strong> Fill with the URI of the resource (dimension/measure) that is relative to base URI.</li>
					<li><strong>Dimension/Measure: </strong> Select between dimension or measure</li>
					<li><strong>Property: </strong> You can assign a type to each dimension or measured using ontologies.</li>
					<li><strong>The cell's content is used... : </strong>Fill with the type of its domain values​​;</li>
				</ul>
			</div>
		<?php
	}
}
?>