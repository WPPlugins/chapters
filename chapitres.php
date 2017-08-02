<?php   
    /* 
    Plugin Name: Chapters 
    Plugin URI: http://www.3m3.fr/chapitres
    Description: This plugin builds 4 boxes by which you can easily manage <strong>'Books'</strong>. Books are containers for <strong>'Chapters'</strong> which are ordinary <strong>Posts</strong> that you add to a book. You can also remove and/or sort them (straightforward by Ajax). Finally in the box <strong>'Summaries'</strong> the plugin generates a custom shortcode ( e.g. <code>[sommaire-chapitres livre=3]</code>) to display a list of links to your chapters anywhere you like.
    Author: Fabrice SEVERIN 
    Version: 2.1
    Author URI: http://www.3m3.fr
	Text Domain: domaine-chapitres
	
	
	License:
 ==============================================================================
 Copyright 2013 Fabrice SEVERIN  (email : 3m3@3m3.fr) Please find help at http://www.3m3.fr/plugins_wordpress/chapters

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
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
    */ 
	
		/****** SECURITE / SECURITY ******/
	if (!function_exists ('add_action')) 
	{
			header('Status: 403 Forbidden');
			header('HTTP/1.1 403 Forbidden');
			exit();
	}
	
	
		/***** CREER/SUPPRIMER TABLE BDD
			   CREATING/DELETING DB TABLE *****/
	global $chapitres_db_version;
	$chapitres_db_version = "2.0";
	define( 'PLUGIN_DIR', dirname(__FILE__) ); 
	include_once PLUGIN_DIR. '/classes/chapitres_database.php';
	$obj_chapitres_database = new chapitres_database();
		// enregistrement des crochets d'activation/désactivation permettant d'installer/désinstaller la table de BDD:
	register_activation_hook( __FILE__, array( 'chapitres_database', 'chapitres_DB_install' ) );
	register_activation_hook( __FILE__, array( 'chapitres_database', 'chapitres_DB_install_data' ) );
	register_uninstall_hook( __FILE__, array( 'chapitres_database', 'chapitres_DB_desinstall' ) );
		
		
		/***** CREER OPTIONS DE MENU ET INTERFACE DANS L'ADMIN 
			   CREATING MENU OPTIONS AND INTERFACE IN ADMIN *****/
	include_once PLUGIN_DIR. '/classes/chapitres_menu_admin.php';
	$obj_chapitres_menu_admin = new chapitres_menu_admin($obj_chapitres_database);	
	
	
		/***** INCLURE LE STYLE DES SOMMAIRES DANS TOUT WORDPRESS 
			   INCLUDING SUMMARY STYLE IN ALL WORDPRESS *****/
	add_action( 'wp_enqueue_scripts', 'admin_styles_sommaire');
	function admin_styles_sommaire() { wp_enqueue_style(  'chapitres-front-css',plugins_url( 'styles/chapitres-front.css',   __FILE__ ));}
	
	
		/***** AUTORISER LES SHORTCODES DANS LES WIDGETS
			   ALLOWING SHORTCODES IN WIDGETS *****/
	add_filter('widget_text', 'do_shortcode');
	
	
		/***** CHARGER FICHIERS DE TRADUCTION
			   LOADING LANGUAGE FILES *****/
	load_plugin_textdomain('domaine-chapitres', false, basename( dirname( __FILE__ ) ) . '/languages' );
	
		/***** MESSAGE DE MISE A JOUR
			   UPDATE MESSAGE *****/
	add_action( 'in_plugin_update_message-' . plugin_basename(__FILE__), 'message_de_mise_a_jour' );
	
	function message_de_mise_a_jour() 
	{
		$info = __( "ATTENTION! If you modified your Chapters' styles, please backup styles/chapitres-front.css file before updating.", 'domaine-chapitres' );
		echo '<span class="spam">' . strip_tags( $info, '<br><a><b><i><span>' ) . '</span>';
	}
	
	
?>