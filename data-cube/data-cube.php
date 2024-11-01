<?php
/*
Plugin Name: WP Data Cube 
Plugin URI: --
Description: Este plugin recebe como entrada um arquivo CSV e gera dados em Data Cube 
Version: 1.0
Author: Débora Nobre
Author URI: --
License: GPLv2
*/

/*
 *      Copyright 2014 Débora Nobre <deborarbnobre@gmail.com>
 *
 *      This program is free software; you can redistribute it and/or modify
 *      it under the terms of the GNU General Public License as published by
 *      the Free Software Foundation; either version 3 of the License, or
 *      (at your option) any later version.
 *
 *      This program is distributed in the hope that it will be useful,
 *      but WITHOUT ANY WARRANTY; without even the implied warranty of
 *      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *      GNU General Public License for more details.
 *
 *      You should have received a copy of the GNU General Public License
 *      along with this program; if not, write to the Free Software
 *      Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 *      MA 02110-1301, USA.
 */
 
ini_set('max_execution_time', 600);

require_once ( "includes/configDataCube.php" );
require_once ( "includes/classRender.php" );
require_once ( "includes/classController.php" );


$render		= new RenderDataCube;
$controller = new ControllerDataCube;
include	( 'includes/initDataCube.php' );

function wp_data_cube()
{
	global $render;
	global $controller;
	
	?>
	<h2 class="dc-logo">Data Cube</h2>
	<div id="dc-wrapper">
		<div id="message"></div>
	<?php
	$importdir = $controller->dc_get_upload_directory();
	
	/*
	 * Get POST data
	 */
	if (isset ($_POST ['dc-delim']) && in_array($_POST ['dc-delim'], $controller->delim_avail))
		$controller->delim = $_POST ['dc-delim'];
	
	if(isset($_POST['dc-import']))
	{
		$controller->dc_create_project();
		$render->dc_render_classification_form($controller->headers, $controller->id_project);
	}
	else if(isset($_POST['dc-classification-form']))
	{
		$controller->dc_create_model();
	}
	else
	{
		if (isset($_REQUEST['edit']))
		{
			$projectID = $_REQUEST['edit'];
			$classificationProject = $controller->dc_get_classification_project($projectID);
			$classificationColumns = $controller->dc_get_classification_columns($projectID);
			
			$render->dc_render_classification_edit_form($classificationProject, $classificationColumns, $projectID);
		}
		else if (isset($_REQUEST['project']))
		{
			$projectID = $_REQUEST['project'];
		
			?>
			<h3><?php $controller->dc_get_project_name($projectID) ?></h3>
			<a class="dc-delete-project" href="javascript:void(0)" data-id-project="<?php echo $project->id ?>"data_cube_submit_data>Delete project</a>
			<?php
			
			$sampleProject = $controller->dc_get_project_sample($projectID);
			$render->dc_render_sample_project_table($sampleProject);
		}
		else
		{
			// Render the homepage of plugin 
			
			$render->dc_render_upload_form();
			$render->dc_render_filter_form();
			
			if(isset($_POST['dc-filter'])){
				$projects = $controller->dc_get_projects($_POST); 
			}else{
				$projects = $controller->dc_get_projects(); 
			}
			
			if(count($projects) > 0)
			{
				$render->dc_render_projects_table($projects);
			}
			else
			{
				?><p>No existing projects.</p><?php
			}
		}
	}
	?></div><?php
}
 
?>