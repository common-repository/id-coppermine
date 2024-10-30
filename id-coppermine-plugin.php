<?php
	/*
	Plugin Name: Infinite Dreamers Coppermine Plugin
	Plugin URI: http://www.infinitedreamers.co.uk
	Description: A plugin to render specified Coppermine Gallery images within a static page.
	Version: 1.1.1
	Author: James Culshaw
	Author URI: http://www.infinitedreamers.co.uk
	*/

	/*  Copyright 2009/2010  James Culshaw  (email : james.culshaw@infinitedreamers.co.uk)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
	*/

	/**
	 * This file holds the code to access the coppermine database
	 * for displaying the gallery
	 */
	require_once('id-coppermine-context.php');
	/**
	 * This file holds the code/template tag
	 * for displaying the gallery
	 */
	require_once('id-coppermine-template.php');
	
	
	/**
	 * Writes the required stylesheet and javascript directives
	 */
	function id_write_stylesheet()
	{
		echo('<link rel="stylesheet" href="'.get_bloginfo('wpurl').'/wp-content/plugins/id-coppermine-plugin/id-coppermine.css" type="text/css" media="screen" />');
		echo('<!-- Lightbox 2 Required files -->');
		echo('<script type="text/javascript" src="'.get_bloginfo('url').'/js/prototype.js"></script>');
		echo('<script type="text/javascript" src="'.get_bloginfo('url').'/js/scriptaculous.js?load=effects,builder"></script>');
		echo('<script type="text/javascript" src="'.get_bloginfo('url').'/js/lightbox.js"></script>');
		echo('<link rel="stylesheet" href="'.get_bloginfo('url').'/css/lightbox.css" type="text/css" media="screen" />');
		
		return;
	}

	/**
	 * Initialises the plugin settings
	 */
	function id_coppermine_init()
	{
		$newoptions['server'] = '';
		$newoptions['database'] = '';
		$newoptions['db_username'] = '';
		$newoptions['db_password'] = '';
		$newoptions['table_prefix'] = '';

		$newoptions['coppermine_db_connection_successful'] = false;
		
		$newoptions['coppermine_url'] = '';
		$newoptions['coppermine_username'] = '';
		$newoptions['coppermine_albums'] = '';

		$newoptions['rows'] = '4';
		$newoptions['columns'] = '5';
		$newoptions['show_title'] = true;
		$newoptions['show_albums'] = false;

		$newoptions['number_latest'] = '3';
		$newoptions['direction_latest'] = 'Vertical';

		$newoptions['id_coppermine_configured'] = false;
		
		add_option('id_coppermine_key', $newoptions);

		return;
	}

	/**
	 * Updates the Plugin Settings
	 */
	function id_coppermine_update_settings()
	{
		$newoptions['server'] = esc_attr($_POST['server_name']);
		$newoptions['database'] = esc_attr($_POST['database_name']);
		$newoptions['db_username'] = esc_attr($_POST['database_username']);
		$newoptions['db_password'] = esc_attr($_POST['database_password']);
		
		if($_POST['coppermine_db_connection_successful'] == 'true')
		{
			$newoptions['coppermine_db_connection_successful'] = true;
		}
		else
		{
			$newoptions['coppermine_db_connection_successful'] = false;
		}
		
		$newoptions['table_prefix'] = esc_attr($_POST['table_prefix']);
		
		$newoptions['coppermine_url'] = esc_attr($_POST['coppermine_url']);
		$newoptions['coppermine_username'] = esc_attr($_POST['coppermine_username']);
		
		$number_of_albums = absint($_POST['coppermine_album_count']);
		$selected_albums = '';
		if(count($_POST['coppermine_albums']) != $number_of_albums)
		{
			$selected_albums = implode(",", $_POST['coppermine_albums']);
		}
		
		$newoptions['coppermine_albums'] = $selected_albums;
		
		$newoptions['rows'] = absint($_POST['rows']);
		$newoptions['columns'] = absint($_POST['columns']);
		if($_POST['show_title'] == 'show_title')
		{
			$newoptions['show_title'] = true;
		}
		else
		{
			$newoptions['show_title'] = false;
		}
		if($_POST['show_albums'] == 'show_albums')
		{
			$newoptions['show_albums'] = true;
		}
		else
		{
			$newoptions['show_albums'] = false;
		}

		$newoptions['number_latest'] = absint($_POST['number_latest']);
		$newoptions['direction_latest'] = esc_attr($_POST['direction_latest']);

		$newoptions['id_coppermine_configured'] = $_POST['id_coppermine_configured'];
		
		update_option('id_coppermine_key', $newoptions);
		
		return;
	}

		
	/**
	 * Tests the connection to the coppermine database
	 */
	function id_coppermine_test_connection(&$err)
	{
		$dataContext = new id_coppermine_context(esc_attr($_POST['server_name']),
						   						 esc_attr($_POST['database_name']),
						   						 esc_attr($_POST['database_username']),
						   				         esc_attr($_POST['database_password']),
						   				         esc_attr($_POST['table_prefix']));
						   			
		if(!$dataContext->connected)
		{
			$err = $dataContext->lastError;
			return false;
		}
		else 
		{
			return true;
		}
	}
	
	function id_coppermine_render_latest_image_settings($id_options)
	{
		echo('<br/>');
		echo('<h3>Latest Images Settings</h3>');
		echo('<table class="form-table">');
		echo('<tr valign="top">');
		echo('<th scope="row">Number:</th>');
		echo('<td>');
		echo('<input type="select" name="number_latest" value="');
		echo($id_options['number_latest']);
		echo('"/>');
		echo('<br/>The number of images to display.</td>');
		echo('</tr>');
		echo('<tr valign="top">');
		echo('<th scope="row">Direction:</th>');
		echo('<td>');
		echo('<input type="radio" name="direction_latest" value="Vertical"');
		if($id_options['direction_latest'] == 'Vertical')
		{
			echo(' checked="true"');
		}
		echo('/>Vertical<br/>');
		echo('<input type="radio" name="direction_latest" value="Horizontal"');
		if($id_options['direction_latest'] != 'Vertical')
		{
			echo(' checked="true"');
		}
		echo('/>Horizontal');
		echo('<br/>The direction to display the images.</td>');
		echo('</tr>');
		echo('</table>');
		
		return;
	}
	
	function id_coppermine_render_display_settings($id_options)
	{
		echo('<br/>');
		echo('<h3>Display Settings</h3>');
		echo('<table class="form-table">');
		echo('<tr valign="top">');
		echo('<th scope="row">Columns:</th>');
		echo('<td>');
		echo('<input type="text" name="columns" value="');
		echo($id_options['columns']);
		echo('"/>');
		echo('<br/>The number of columns to display.</td>');
		echo('</tr>');
		echo('<tr valign="top">');
		echo('<th scope="row">Rows:</th>');
		echo('<td>');
		echo('<input type="text" name="rows" value="');
		echo($id_options['rows']);
		echo('"/>');
		echo('<br/>The number of rows to display.</td>');
		echo('</tr>');
		echo('<tr valign="top">');
		echo('<th scope="row">Show Title:</th>');
		echo('<td>');
		echo('<input type="checkbox" name="show_title" value="show_title"');
		if($id_options['show_title'])
		{
			echo(' checked="true"');
		}
		echo('"/>');
		echo('</td>');
		echo('</tr>');
		echo('<tr valign="top">');
		echo('<th scope="row">List Albums:</th>');
		echo('<td>');
		echo('<input type="checkbox" name="show_albums" value="show_albums"');
		if($id_options['show_albums'])
		{
			echo(' checked="true"');
		}
		echo('"/>');
		echo('</td>');
		echo('</tr>');
		echo('</table>');
		
		return;
	}
	
	function id_coppermine_render_user_selector($id_options, 
												$data_context)
	{
		$users = $data_context->GetUserList();
		
		echo('<select name="coppermine_username">');
		
		foreach($users as $user)
		{
			echo('<option value="');
			echo($user['user_id']);
			echo('"');
			if(absint($id_options['coppermine_username']) == $user['user_id'])
			{
				echo(' selected="true"');
			}
			echo('>');
			echo($user['user_name']);
			echo('</option>');
		}
		echo('</select>');
		
		return;
	}
	

	function id_coppermine_render_album_selector($id_options, 
											 	 $data_context)
	{
											 	
		$albums = $data_context->GetUserAlbumData($id_options['coppermine_username']);
		$album_list = explode(",", 
							  $id_options['coppermine_albums']);

		foreach($albums as $album)
		{
			echo('<input type="checkbox" name="coppermine_albums[]" value="');
			echo($album['aid']);
			if($id_options['coppermine_albums'] == '' || in_array($album['aid'], $album_list))
			{
				echo('" checked="true');
			}
			echo('">');
			echo($album['title']);
			echo('</input><br/>');
		}
		echo('<input type="hidden" name="coppermine_album_count" value="');
		echo(count($albums));
		echo('"/>');
			
		return;
	}
	
	
	function id_coppermine_render_general_settings($id_options)
	{
		$dataContext = new id_coppermine_context($id_options['server'],
						  					   	 $id_options['database'],
						  					     $id_options['db_username'],
						  					     $id_options['db_password'],
						  					     $id_options['table_prefix']);
		echo('<br/>');
		echo('<h3>General Settings</h3>');
		echo('<table class="form-table">');
		echo('<tr valign="top">');
		echo('<th scope="row">Coppermine address (URL):</th>');
		echo('<td>');
		echo('<input type="text" name="coppermine_url" value="');
		echo($id_options['coppermine_url']);
		echo('"/>');
		echo('<br/>The url for the Coppermine gallery.</td>');
		echo('</tr>');
		echo('<tr valign="top">');
		echo('<th scope="row">Coppermine username:</th>');
		echo('<td>');
		id_coppermine_render_user_selector($id_options, 
									       $dataContext);
		echo('<br/>The Coppermine username of the user for whom to display the images.</td>');
		echo('</tr>');
		echo('<tr valign="top">');
		echo('<th scope="row">Coppermine Albums:</th>');
		echo('<td>');
		id_coppermine_render_album_selector($id_options, 
									        $dataContext);
		echo('<br/>The Coppermine albums to display. <i>Check the albums you wish to display pictures from.</i></td>');
		echo('</tr>');
		echo('</table>');
	}
	
	function id_coppermine_render_database_settings_block($id_options,
														  $message)
	{
		echo('<h3>Coppermine Database Settings</h3>');
		echo('<table class="form-table">');
		echo('<tr valign="top">');
		echo('<th scope="row">Server Name:</th>');
		echo('<td>');
		echo('<input type="text" name="server_name" value="');
		echo($id_options['server']);
		echo('"/>');
		echo('</td>');
		echo('</tr>');
		echo('<tr valign="top">');
		echo('<th scope="row">Database:</th>');
		echo('<td>');
		echo('<input type="text" name="database_name" value="');
		echo($id_options['database']);
		echo('"/>');
		echo('</td>');
		echo('</tr>');
		echo('<tr valign="top">');
		echo('<th scope="row">Username:</th>');
		echo('<td>');
		echo('<input type="text" name="database_username" value="');
		echo($id_options['db_username']);
		echo('"/>');
		echo('</td>');
		echo('</tr>');
		echo('<tr valign="top">');
		echo('<th scope="row">Password:</th>');
		echo('<td>');
		echo('<input type="text" name="database_password" value="');
		echo($id_options['db_password']);
		echo('"/>');
		echo('</td>');
		echo('</tr>');
		echo('<tr valign="top">');
		echo('<th scope="row">Table Prefix:</th>');
		echo('<td>');
		echo('<input type="text" name="table_prefix" value="');
		echo($id_options['table_prefix']);
		echo('"/>');
		echo('</td>');
		echo('</tr>');
		echo('<tr valign="top">');
		echo('<th scope="row"></th>');
		echo('<td>');
		echo('<input type="submit" name="TestConnection" class="button-primary" value="Test Connection"/>');
		echo('</td>');
		echo('</tr>');
		echo('</table>');
		
		return;
	}
	
	/**
	 * Creates the plugin administration page
	 */
	function id_coppermine_admin_page()
	{
		
		$successfullConnection = false;
		$errMessage = '';
		
		//must check that the user has the required capability 
	    if (!current_user_can('manage_options'))
	    {
	      wp_die( __('You do not have sufficient permissions to access this page.') );
	    }
		
		id_coppermine_init();
		
		if($_POST['TestConnection'])
		{
			$successfullConnection = id_coppermine_test_connection($errMessage);
			$id_options = get_option('id_coppermine_key');
			$id_options['server'] = esc_attr($_POST['server_name']);
			$id_options['database'] = esc_attr($_POST['database_name']);
			$id_options['db_username'] = esc_attr($_POST['database_username']);
			$id_options['db_password'] = esc_attr($_POST['database_password']);
			$id_options['table_prefix'] = esc_attr($_POST['table_prefix']);
		}
		elseif($_POST['id_coppermine_config_save'])
		{
			id_coppermine_update_settings();
			$id_options = get_option('id_coppermine_key');
			$successfullConnection = true;
		}
		else
		{ 
			$id_options = get_option('id_coppermine_key');
			if($id_options['id_coppermine_configured'] == 'true')
			{
				$successfullConnection = true;
			} 
		}
		
		echo('<div class="wrap">');
		echo('<h2>ID Coppermine Plugin Options</h2>');
		echo('<form method="post" action="');
		echo(get_bloginfo('wpurl'));
		echo('/wp-admin/options-general.php?page=id-coppermine/id-coppermine-plugin.php">');

		wp_nonce_field('update-options');

		id_coppermine_render_database_settings_block($id_options, $errMessage);
		
		echo('<input type="hidden" name="id_coppermine_configured" value="');
		if($successfullConnection)
		{
			echo('true');
		}
		else
		{
			echo('false');
		}
		echo('"/>');
		
		if($successfullConnection)
		{
			id_coppermine_render_general_settings($id_options);
			id_coppermine_render_display_settings($id_options);
			id_coppermine_render_latest_image_settings($id_options);
			echo('<p class="submit">');
			echo('<input type="submit" name="id_coppermine_config_save" class="button-primary" value="Save" />');
			echo('</p>');
		}
		
		echo('</div>');
		
	}

	/**
	 * required function to add the plugin admin page
	 * to WordPress
	 */
	function id_coppermine_add_pages()
	{
		add_options_page('ID Coppermine',
						 'ID Coppermine',
						 8,
						 __FILE__,
						 'id_coppermine_admin_page');
	}

	/**
	 * Registers the required actions with WordPress
	 */
	add_action('admin_menu', 'id_coppermine_add_pages');
	add_action('wp_head','id_write_stylesheet')

?>
