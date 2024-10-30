=== ID Coppermine ===
Contributors: m0gb0y74
Donate link: http://www.infinitedreamers.co.uk/2009/09/15/wordpress-coppermine-plugin/
Tags: coppermine, plugin, tags
Requires at least: 2.8.0
Tested up to: 3.0.1
Stable tag: 1.1.1

ID Coppermine allows you to display images from your Coppermine 1.5.x gallery in WordPress

== Description ==

This WordPress plugin supplies a set of template tags to allow you display images from a Coppermine 1.5.x Gallery. 

*NOTE:* Version 1.1.0 onwards does not support Coppermine 1.4.x

Tags are provided to display the following:

1. A configurable list of albums
2. Grid of images from a selected album
3. A latest images block to display a configurable number of images that were last uploaded.

This plugin leverages Lightbox2 v2.04 to display the full size version of the images. As this software was not created by me it has not been included within this package. It can be downloaded from http://www.huddletogether.com/projects/lightbox2/#download

The plug-in supplies a settings page which allows the user to enter the Coppermine database details and the display settings.  

== Installation ==

1. Unzip `id-coppermine-plugin.zip` in to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. In the 'Settings' menu select 'ID Coppermine'.
4. Under the 'General Settings' section enter the full url to the root of your Coppermine installation i.e. http://www.yoursite.com/copperminedirectory and the Coppermine username of the user for whom to display the images for. NOTE: there should not be a trailing slash in the url
5. Under the 'Coppermine Database Settings' section enter the Coppermine web server and database details. These details will not be validated so you will only find out if they are incorrect when you try to view a page using one of the template tags. This allows you to be able to change the details at any point.
6. In the 'Display Settings' section you can set the number of column and rows to display when rendering the gallery, and wether to display the image title and to list the albums.
7. In the 'Latest Images Settings' section you can specify the number of images to display when renderingthe latest images section.
8. Download Lightbox2 from http://www.huddletogether.com/projects/lightbox2/#download and extract to the same directory as your blog homepage.

== Frequently Asked Questions ==

= How do I create a gallery page using this plugin? =

This is a simple process.

1. Create a template file within your current theme directory (I used gallery.php)

To create the layout as shown in screenshot 1 the template that was used is as follows:

`<?php
/*
Template Name: Gallery
*/

/**
 * @package WordPress
 * @subpackage Default_Theme
 */

get_header(); ?>

<div id="gallery" role="main"> 

<?php 
	id_coppermine_render_album_list();
	id_coppermine_render_gallery(); 
	id_coppermine_render_page_nav();
?>
</div>

<?php get_footer(); ?>`

2. Create a new page using the WordPress Pages option.

I created a page called Gallery with no content and selected 'Gallery' as the template option. When published, that's it. A fully working gallery.


= How can I control the appearance of the plugin CSS? =

The included file id-coppermine.css contains the styles that are used by the plugin. You have the option of modifying them here or making this file empty and adding the required information to your themes CSS file.
All the selectors are prefixed 'id_' so the chances of conflict with selectors used in your selected theme are low.


= Why do you not include the Lightbox2 files with your plugin? =

Lightbox2 was not created by me and is unsupported by me. Although this plugin was built using v2.04 of Lightbox2 it is very likely that newer versions of Lightbox2 will be backwards compatible and so this will make it easier for you to maintain Lightbox2 seperately.

== Screenshots ==

1. An example of the plugin in use on my blog highlighting the output from two of the template tags.


== Template Tags ==

The template tags that are available to you after activating the plugin are as follows:

	id_coppermine_render_album_list();

	id_coppermine_render_gallery(); 

	id_coppermine_render_page_nav();

	id_coppermine_render_latest();

They basically do what they say they do so not much more explanation is needed.


== Changelog ==

= 1.1.0 =
Released Oct 29, 2010
Minor bug-fix to remove error on configuration page.

= 1.1.0 =
Released Oct 29, 2010
Minor update to ease the configuration of the plugin and to make fully compatible with Coppermine 1.5.x.

= 1.0.1 =
Minor fix to change plugin directory name in id-coppermine-plugin.php to solve 'access denied' errors when accessing the settings page.

= 1.0 =
Initial Release Sept 25, 2009


== Upgrade Notice ==

= 1.1.1 =
Makes plugin compatible with Coppermine 1.5.x. 
Easier configuration.

