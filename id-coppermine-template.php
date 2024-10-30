<?php

	/*
		id-coppermine-template.php

		The gallery rendering template tags
	*/

	/*  Copyright 2009  James Culshaw  (email : james.culshaw@infinitedreamers.co.uk)

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

	class id_coppermine
	{

		private $dataContext;

		private $id_options;
		private $album_id = -1;
		private $max_width = -1;
		private $number_to_display = -1;
		private $current_index = 0;
		private $start = 0;

		private $specific_albums = false;

		/**
		 * PHP4 Constructor
		 */
		function id_coppermine()
		{
			$this->__construct();
		}

		/**
		 * PHP5 Constructor
		 */
		function __construct()
		{
			$this->id_options = get_option('id_coppermine_key');

			$this->dataContext = new id_coppermine_context($this->id_options['server'],
									  					   $this->id_options['database'],
									  					   $this->id_options['db_username'],
									  					   $this->id_options['db_password'],
									  					   $this->id_options['table_prefix']);

			if($this->id_options['coppermine_albums'] != '')
			{
				$this->specific_albums = true;
			}

			$this->number_to_display = $this->id_options['rows'] * $this->id_options['columns'];

			$this->set_thumb_max_dimension();

			if(array_key_exists('idaid', $_GET))
			{
				$this->album_id = intval($_GET['idaid']);
				$this->current_index = absint($_GET['idpn']) -1;
			}

			if($this->id_options['show_albums'] && $this->album_id == -1)
			{
				$albums = $this->get_album_data();

				$this->album_id = $albums[0]['aid'];
			}

			$this->start = $this->current_index * $this->number_to_display;
		}


		/**
		 * Sets the maximum thumbnail width from the Coppermine Database
		 */
		private function set_thumb_max_dimension()
		{
			$this->max_width = $this->dataContext->GetThumbWidth();
			return;
		}

		/**
		 * Determines the size of the thumbnail
		 * based on the original image size and
		 * the maximum thumb size value
		 */
		private function determine_thumbsize($height,
											 $width)
		{
			$ratio = $height / $this->max_width;

			$ratio = max($ratio, 1.0);
		    $thumbsize['width'] = ceil($width / $ratio);
		    $thumbsize['height'] = ceil($height / $ratio);

			return $thumbsize;
		}

		/**
		 * Gets the current number of images for the specified user
		 */
		private function get_user_image_count()
		{
			$imageCount = 0;
			if($this->specific_albums)
			{
				$imageCount = $this->dataContext->GetUserAlbumImageCount($this->id_options['coppermine_username'], 
																		 $this->id_options['coppermine_albums']);
			}
			else 
			{
				$imageCount = $this->dataContext->GetUserImageCount($this->id_options['coppermine_username']);
			}
			
			return $imageCount;
		}


		/**
		 * Gets the current number of images in the specified album
		 */
		private function get_album_image_count()
		{
			return $this->dataContext->GetAlbumImageCount($this->album_id);
		}

		/**
		 * Retrieves the album data from the Coppermine database
		 */
		private function get_album_data()
		{
			$rows;
			
			if($this->specific_albums)
			{
				$rows = $this->dataContext->GetSpecificAlbumData($this->id_options['coppermine_albums']);
			}
			else
			{
				$rows = $this->dataContext->GetUserAlbumData($this->id_options['coppermine_username']);
			}

			return $rows;
		}

		/**
		 * Retrieves the image data from the Coppermine database
		 */
		private function get_image_data()
		{
			$rows;
			
			if($this->id_options['show_albums'])
			{
				$rows = $this->dataContext->GetUserAlbumImageData($this->id_options['coppermine_username'], 
															 	  $this->album_id,
															 	  $this->start,
															 	  $this->number_to_display);
			}
			elseif($this->specific_albums)
			{
				$rows = $this->dataContext->GetUserAlbumsImageData($this->id_options['coppermine_username'], 
															 	   $this->id_options['coppermine_albums'],
															 	   $this->start,
															 	   $this->number_to_display);
			}
			else
			{
				$rows = $this->dataContext->GetUserImageData($this->id_options['coppermine_username'], 
															 $this->start,
															 $this->number_to_display);
			}

			return $rows;
		}

		/**
		 * Retrives the latest image data from the Coppermine database
		 */
		private function get_latest_image_data()
		{
			$rows;
			
			if($this->specific_albums)
			{
				$rows = $this->dataContext->GetUserLatestSpecificAlbumsImageData($this->id_options['coppermine_username'],
																				 $this->id_options['coppermine_albums'], 
																				 $this->id_options['number_latest']);
			}
			else 
			{
				$rows = $this->dataContext->GetUserLatestImageData($this->id_options['coppermine_username'], 
																   $this->id_options['number_latest']);
			}

			return $rows;
		}


		private function get_image_count()
		{
			if($this->id_options['show_albums'])
			{
				return $this->get_album_image_count();
			}
			else
			{
				return $this->get_user_image_count();
			}
		}
		
		/**
		 * Renders the gallery album list
		 */
		function render_album_list()
		{
			if($this->id_options['show_albums'])
			{
				$albums = $this->get_album_data();

				echo('<div id="id_album_names">');
				echo('<ul id="id_album_names_list">');

				foreach($albums as $album)
				{
					echo('<li>');
					echo('<a href="');
					$params = array('idaid' => $album['aid'],
									'idpn' => 1);
					echo(add_query_arg($params));
					echo('">');
					echo($album['title']);
					echo('</a>');
					echo('</li>');
				}

				echo('</ul>');
				echo('</div>');
			}
		}

		/**
		 * Renders the gallery page navigation
		 */
		function render_page_nav()
		{

			$album_image_count = $this->get_image_count();

			$number_pages = ceil($album_image_count/$this->number_to_display);
			$current_page = $this->current_index + 1;

			$index = 0;

			echo('<div id="id_page_navigation">');
			echo('<ul id="id_page_navigation_list">');
			for ($page_number = 1; $page_number <= $number_pages; $page_number++)
			{
				echo('<li ');
				if($current_page == $page_number)
				{
					echo('class="id_current_page"');
				}
				echo('><a href="');
				$params = array('idaid' => $this->album_id,
								'idpn' => $page_number);
				echo(add_query_arg($params));
				echo('">');
				echo($page_number);
				echo('</a></li>');

				$index += $this->number_to_display;
			}
			echo('</ul>');
			echo('</div>');

		}

		/**
		 * Renders the gallery block
		 */
		function render_gallery()
		{

			$images = $this->get_image_data();

			$col = 0;
			$percentage = ceil(100/$this->id_options['columns']);

			echo('<table id="id_gallery">');

			foreach($images as $image)
			{

				$col += 1;
				$height = 0;
				$width = 0;

				$thumbsize = $this->determine_thumbsize($image['pheight'],
													$image['pwidth']);

				$baseurl = $this->id_options['coppermine_url'].'/albums/'.$image['filepath'];
				$src = $baseurl.'thumb_'.$image['filename'];
				$href = $baseurl.$image['filename'];

				if($col == 1)
				{
					echo('<tr>');
				}

				echo('<td width="');
				echo($percentage);
				echo('%"><a href="');
				echo($href);
				echo('" rel="lightbox[gallery]" title="');
				echo($image['title']);
				echo('"><img src="');
				echo($src);
				echo('" height="');
				echo($thumbsize['height']);
				echo('" width="');
				echo($thumbsize['width']);
				echo('" alt="');
				echo($image['title']);
				echo('"/></a>');
				if($this->id_options['show_title'])
				{
					echo('<p class="id_title">');
					echo($image['title']);
					echo('</p>');
					echo('<p class="id_caption">');
					echo($image['caption']);
					echo('</p>');
				}
				echo('</td>');

				if($col == $this->id_options['columns'])
				{
					echo('</tr>');
					$col = 0;
				}
			}

			if ($col > 0)
			{
				while($col < $this->id_options['columns'])
				{
					echo('<td>&nbsp;</td>');
					$col += 1;
				} // while
				if($col == $this->id_options['columns']){
					echo('</tr>');
				}
			} //if

			echo('</table>');

		}

		function render_latest_images()
		{
			$images = $this->get_latest_image_data();

			$percentage = ceil(100/$this->id_options['number_latest']);

			echo('<table id="id_gallery_latest">');
			echo('<tr>');

			foreach($images as $image)
			{

				$height = 0;
				$width = 0;

				$thumbsize = $this->determine_thumbsize($image['pheight'],
														$image['pwidth']);

				$baseurl = $this->id_options['coppermine_url'].'/albums/'.$image['filepath'];
				$src = $baseurl.'thumb_'.$image['filename'];

				echo('<td width="');
				echo($percentage);
				echo('%"><img src="');
				echo($src);
				echo('" height="');
				echo($thumbsize['height']);
				echo('" width="');
				echo($thumbsize['width']);
				echo('" alt="');
				echo($image['title']);
				echo('"/>');
				if($this->id_options['show_title'])
				{
					echo('<p class="id_title">');
					echo($image['title']);
					echo('</p>');
				}
				echo('</td>');
			}

			echo('</tr>');
			echo('</table>');

		}

	}


	/**
	 * Renders the gallery album list
	 */
	function id_coppermine_render_album_list()
	{
		$gallery = new id_coppermine();
		$gallery->render_album_list();
	}

	/**
	 * Renders the gallery page navigation
	 */
	function id_coppermine_render_page_nav()
	{
		$gallery = new id_coppermine();
		$gallery->render_page_nav();
	}

	/**
	 * Renders the gallery block
	 */
	function id_coppermine_render_gallery()
	{
		$gallery = new id_coppermine();
		$gallery->render_gallery();
	}

	/**
	 * Renders the latest images block
	 */
	function id_coppermine_render_latest()
	{
		$gallery = new id_coppermine();
		$gallery->render_latest_images();
	}
?>