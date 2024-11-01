=== Plugin Name ===
Contributors: debsnobre
Donate link: http://example.com/
Tags: datacube, csv, import, spreadsheet, plugin, importer, rdf, linkeddata
Requires at least: 3.4.0
Tested up to: 3.8
Stable tag: 4.3
Version: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A plugin that extract statistical data available in spreadsheets in CSV format and perform the transformation to the format of RDF triples.

== Description ==

The plugin WP Data Cube has the functionality to extract statistical data available in spreadsheets in CSV format and perform the transformation to RDF triples format, based on Data Cube Vocabulary, publish the data from these models generated content in addition to offer a web interface SPARQL queries.


== Installation ==

WP Data Cube Importer is very easy to install like any other wordpress plugin. No need to edit or modify anything here.

1.    Unzip the file 'wp-data-cube.zip'.
2.    Upload the ' data-cube' directory to '/wp-content/plugins/' directory using ftp client or upload and install wp-data-cube.zip through plugin install wizard in wp admin panel .
3.    Activate the plugin through the 'Plugins' menu in WordPress.
4.    After activating, you will see an option for 'WP Data Cube' in the admin menu (left navigation) and you will import the csv files to import the data's.


== Frequently Asked Questions ==

1. How to install the plugin?
Like other plugins wp-data-cube is easy to install. Upload the wp-data-cube.zip file through plugin install page through wp admin. Everything will work fine with it.

2. How to use the plugin?
After plugin activation you can see the ' WP Data Cube ' menu in admin backend.
1)Browse csv file to import the data's.
2)You can mapping the headers to import the data's in dimensions or measures.
3)You can optionally assign a type to each dimension and / or measured using ontologies.
4)You can register ontologies in database.
5)The data's are processed based on the rating you did.

3. What to do when an import broke in the middle of import?
Check your CSV format. It should be UTF-8. If you get memory related issue, change or create	a custom php.ini with increased value for max_execution_time and memory limt
    

== Screenshots ==

1. Browse and Import CSV with delimiter
2. Explained -How to CSV Mapping Headers 
3. Simple click mapping option to relate csv field headers with theirs properties 

== Changelog ==

= 1.0.0 =	
* Initial release version. Tested and found works well without any issues.
