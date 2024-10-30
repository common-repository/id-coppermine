<?php

/*
		id-coppermine-context.php

		Class for accessing the Coppermine database
	*/

	/*  Copyright 2010  James Culshaw  (email : james.culshaw@infinitedreamers.co.uk)

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
	
	class id_coppermine_context
	{
		private $server = '';
		private $database = '';
		private $username = '';
		private $password = '';
		private $tablePrefix = '';
		
		public $connected = false;

		private $connection;

		public $hasError = false;
		public $lastError = '';

		/**
		 * PHP4 Constructor
		 */
		function id_coppermine_context($server,
						  			   $database,
						  			   $username,
						  			   $password,
						  			   $tablePrefix)
		{
			$this->__construct($server,
						  	   $database,
						  	   $username,
						 	   $password,
						 	   $tablePrefix);
		}

		/**
		 * PHP5 Constructor
		 */
		function __construct($server,
						  	 $database,
						  	 $username,
						  	 $password,
						  	 $tablePrefix)
		{
			$this->server = $server;
			$this->database = $database;
			$this->username = $username;
			$this->password = $password;
			$this->tablePrefix = $tablePrefix;
			
			$this->connection = mysql_connect($server, $username, $password);

			if (!$this->connection)
			{
				$this->setError(mysql_error());
				return;
			}
			else
			{
				if (!mysql_select_db($database, $this->connection)) {
					$this->setError(mysql_error());
				} else {
					$this->connected = true;
				}
			}
		}

		private function setError($msg)
		{
			$this->hasError  = true;
			$this->lastError = $msg;
		}

		private function GetData($sql)
		{
			if (!$this->connected)
			{
				return false;
			}

			$result = mysql_query($sql, $this->connection);
			if (!$result)
			{
				$this->setError(mysql_error());
				return false;
			}

			$rows = array();

			while ($row = mysql_fetch_assoc($result)) {
				array_push($rows, $row);
			}

			mysql_free_result($result);

			return $rows;
		}
		
		public function GetThumbWidth()
		{
			$sql = "SELECT value FROM ";
			$sql .= $this->tablePrefix;
			$sql .= "config WHERE name = 'thumb_width'";	
			
			$rows = $this->GetData($sql);

			return $rows[0]['value'];
		}
		
		/**
		 *  Retrieves the list of usernames that have albums from the Coppermine database
		 */
		public function GetUserList()
		{
			$sql = 'SELECT DISTINCT u.user_id, u.user_name FROM ';
			$sql .= $this->tablePrefix;
			$sql .= 'users u INNER JOIN ';
			$sql .= $this->tablePrefix;
			$sql .= 'albums a ON u.user_id = a.owner ORDER BY u.user_name ASC';	
	
			$rows = $this->GetData($sql);
	
			return $rows;
		}
		
		private function GetUserImageCountSql($coppemine_username)
		{
			$sql = "SELECT COUNT(*)  AS number_pictures FROM ";
			$sql .= $this->tablePrefix;
			$sql .= "pictures WHERE owner_id = '";
			$sql .= $coppemine_username;
			$sql .= "'";
			
			return $sql;
		}
		
		/**
		 * Gets the current number of images for the specified user
		 */
		private function GetUserImageCount($coppemine_username)
		{			
			$sql = GetUserImageCountSql($coppemine_username);
			
			$rows = $this->GetData($sql);

			return $rows[0]['number_pictures'];
		}
		
		/**
		 * Gets the current number of images for the specified user and albums
		 */
		public function GetUserAlbumImageCount($coppemine_username,
										   	   $albums)
		{
			$sql = GetUserImageCountSql($coppemine_username);
			$sql .= " AND aid IN (";
			$sql .= $albums;
			$sql .= ")";

			$rows = $this->GetData($sql);

			return $rows[0]['number_pictures'];
		}
		
		/**
		 * Gets the current number of images in the specified album
		 */
		public function GetAlbumImageCount($album_id)
		{
			$sql = "SELECT COUNT(*)  AS number_pictures FROM ";
			$sql .= $this->tablePrefix;
			$sql .= "pictures WHERE aid = ";
			$sql .= $album_id;

			$rows = $this->GetData($sql);

			return $rows[0]['number_pictures'];
		}
		
		/**
		 * Retrieves the album data from the Coppermine database for the specified albums
		 */
		public function GetSpecificAlbumData($albums)
		{
			$sql = "SELECT aid, title FROM ";
			$sql .= $this->tablePrefix;
			$sql .= "albums WHERE aid IN (";
			$sql .= $albums;
			$sql .= ") ORDER BY pos ASC";
			
			$rows = $this->GetData($sql);

			return $rows;
		}

		/**
		 * Retrieves the album data from the Coppermine database for the specified user
		 */
		public function GetUserAlbumData($coppermine_username)
		{
			$sql = "SELECT a.aid, a.title FROM ";
			$sql .= $this->tablePrefix;
			$sql .= "pictures p INNER JOIN ";
			$sql .= $this->tablePrefix;
			$sql .= "albums a ON p.aid = a.aid WHERE p.owner_id = '";
			$sql .= $coppermine_username;
			$sql .= "' GROUP BY a.aid, a.title ORDER BY a.pos ASC";

			$rows = $this->GetData($sql);

			return $rows;
		}

		private function GetUserImageDataSql($coppermine_username)
		{
			$sql = "SELECT pid, filepath, filename, url_prefix, filesize, pwidth, pheight, ctime, title, caption, owner_id FROM ";
			$sql .= $this->tablePrefix;
			$sql .= "pictures WHERE owner_id = '";
			$sql .= $coppermine_username;
			$sql .= "'";
			
			return $sql;
		}
		
		private function GetUserImageDataLimitSql($start,
												  $number_to_display)
		{
			$sql .= " ORDER BY pid DESC LIMIT ";
			$sql .= $start;
			$sql .= ", ";
			$sql .= $number_to_display;
			
			return $sql;
		}
		
		/**
		 * Retrieves the image data from the Coppermine database for the specified user
		 */
		public function GetUserImageData($coppermine_username, 
		 								 $start, 
		 								 $number_to_display)
		{

			$sql = $this->GetUserImageDataSql($coppermine_username);
			$sql .= $this->GetUserImageDataLimitSql($start,
											 		$number_to_display);
											 
			$rows = $this->GetData($sql);

			return $rows;
		}
		
		/**
		 * Retrieves the image data from the Coppermine database for the specified user and album
		 */
		public function GetUserAlbumImageData($coppermine_username,
		 									  $album_id, 
		 									  $start, 
		 									  $number_to_display)
		{
			$sql = $this->GetUserImageDataSql($coppermine_username);
			
			$sql .= " AND aid = ";
			$sql .= $album_id;
			$sql .= $this->GetUserImageDataLimitSql($start,
											 		$number_to_display);

			$rows = $this->GetData($sql);
			
			return $rows;
		}
		
		/**
		 * Retrieves the image data from the Coppermine database for the specified user and albums
		 */
		public function GetUserAlbumsImageData($coppermine_username,
		 									   $album_ids, 
		 									   $start, 
		 									   $number_to_display)
		{
			$sql = $this->GetUserImageDataSql($coppermine_username);
			$sql .= " AND aid IN (";
			$sql .= $album_ids;
			$sql .= ")";
			$sql .= GetUserImageDataLimitSql($start,
											 $number_to_display);

			$rows = $this->GetData($sql);

			return $rows;
		}
		
		private function GetLatestImageDataSql($coppermine_username)
		{
			$sql = "SELECT pid, filepath, filename, url_prefix, filesize, pwidth, pheight, ctime, title, caption, owner_id FROM ";
			$sql .= $this->tablePrefix;
			$sql .= "pictures WHERE owner_id = '";
			$sql .= $coppermine_username;
			$sql .= "'";
			
			return $sql;
		}
		
		/**
		 * Retrieves the latest image data from the Coppermine database for the specified user
		 */
		public function GetUserLatestImageData($coppermine_username, 
											   $number_latest)
		{
			$sql = $this->GetLatestImageDataSql($coppermine_username);
			$sql .= " ORDER BY pid DESC LIMIT 0, ";
			$sql .= $number_latest;
			
			$rows = $this->GetData($sql);

			return $rows;
		}
		
		/**
		 * Retrieves the latest image data from the Coppermine database for the specified user and albums
		 */
		public function GetUserLatestSpecificAlbumsImageData($coppermine_username,
															 $albums, 
															 $number_latest)
		{
			$sql = $this->GetLatestImageDataSql($coppermine_username);
			$sql .= " AND aid IN (";
			$sql .= $$albums;
			$sql .= ")";
			$sql .= " ORDER BY pid DESC LIMIT 0, ";
			$sql .= $number_latest;

			$rows = $this->GetData($sql);

			return $rows;
		}
		
	}
?>