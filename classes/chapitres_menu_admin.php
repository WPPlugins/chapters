<?php

/*
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

include_once dirname( __FILE__ ) . '/chapitres_display.php';include_once dirname( __FILE__ ) . '/chapitres_customize.php';

class chapitres_menu_admin
{

	private $pagehook;
	private $onglets_metabox_livres;
	private $onglets_metabox_chapitres;
	private $onglets_metabox_sommaire;
	private $obj_chapitres_database;
	private $obj_chapitres_display;private $style_a_la_volee="body{color:red;}";
	
	
			/********** CONSTRUCTEUR / CONSTRUCTOR **********/
			
		function chapitres_menu_admin($objDB) 
	{
		global $wpdb;
			// affichage du menu:
		add_action( 'admin_menu', array( &$this, 'actions_admin_chapitres' ) );
			// gestion de l'AJAX:
		add_action('wp_ajax_ajoute_chapitres_a_livre', array( &$this, 'admin_ajoute_chapitres_a_livre' ));
		add_action('wp_ajax_actualise_metabox_articles', array( &$this, 'admin_actualise_metabox_articles' ));
		add_action('wp_ajax_change_ordre_chapitres', array( &$this, 'admin_change_ordre_chapitres' ));
		add_action('wp_ajax_actualise_options_sommaire', array( &$this, 'admin_actualise_options_sommaire' ));
		add_action('wp_ajax_renvoie_json_styles', array( &$this, 'admin_renvoie_json_styles' )); add_action('wp_ajax_ecrit_styles', array( &$this, 'admin_ecrit_styles' )); 	
			// gestion des SHORTCODES:
		add_shortcode('sommaire-chapitres', array( &$this,'admin_sommaire_chapitres_shortcode')); // permet d'afficher le sommaire 
		add_shortcode('numero-livre', array( &$this,'admin_bidon_shortcode'));
		add_shortcode('chapitre', array( &$this,'admin_bidon_shortcode'));
		$this->obj_chapitres_database = $objDB;
		$this->obj_chapitres_display = new chapitres_display($objDB);
	}
	
			/********** IMPLEMENTATION INTERFACE ADMIN / ADMIN INTERFACE IMPLEMENTATION **********/
	
			/***** Insérer un sous-menu dans le panneau Réglages
				   Insert a sub-menu in Settings panel *****/
		function actions_admin_chapitres() 
	{  
		$this->pagehook = add_options_page(__("Chapters",'domaine-chapitres'), __("Chapters",'domaine-chapitres'), "manage_options", "chapitres", array( &$this,"admin_chapitres")); 
			// (ici $this->pagehook vaut settings_page_nom_du_plugin, donc settings_page_chapitres)
		$this->admin_enqueue();		
	}
	
		/***** Gérer les options dans Chapitres et afficher toute la page admin
			   Manage options in Chapters and display all the admin page *****/
	public function admin_chapitres() 
	{  
			if ( !current_user_can( 'manage_options' ) )   wp_die( __( 'You do not have sufficient permissions to access this page.' ) );	
		$message = '';	
			/* RECUPERER LES VALEURS POSTEES PAR LE FORMULAIRE SUR CREATION D'UN NOUVEAU LIVRE: */
		if( isset($_POST['sauvegarder_livres']) )
		{
				// Lire les valeurs postées par le formulaire: si l'utilisateur a déjà posté qqch, le champ caché contiendra 'Y'
			if( isset($_POST[ 'chapitres_submit_hidden' ]) && $_POST[ 'chapitres_submit_hidden' ] == 'Y' ) 
			{
				$i = 1;
					// mettre à jour ou créer les valeurs de chaque livre dans la BDD:
				while(isset($_POST['titre_livre_'.$i]))
				{
					$titre_livre = stripslashes(esc_sql( $_POST['titre_livre_'.$i] ));
					$resume_livre = stripslashes(esc_sql( $_POST['resume_livre_'.$i] ));
					$bool_livre = 0;
					if(isset($_POST['affiche_infos_livre_'.$i])) $bool_livre = 1;
						// deux cas de figure: 1. le livre n'existe pas encore, il faut le créer; 2. il faut l'updater
					if(isset($_POST['hidden_livre_'.$i])) // updater:
					{
						$clause_champs = array( 'nom_livre' => $titre_livre,'definition_livre' => $resume_livre,'afficher_infos_livre' => $bool_livre);
						$clause_id = array( 'id_livre' => $_POST['hidden_livre_'.$i] );
						$this->obj_chapitres_database->chapitres_DB_update($clause_champs,$clause_id);
					}
					else // créer un enregistrement dans la table:
					{
						$this->obj_chapitres_database->chapitres_DB_insert($titre_livre,$resume_livre,$bool_livre);
					}
					$i++;
				}
				$message = "<p><strong>".__('Books saved.', 'domaine-chapitres' )."</strong></p>";
			}
		}
		else
		{
				// suppression d'un livre par submit d'un onglet:
			$i = 1;
			if( isset($_POST[ 'chapitres_submit_hidden' ]) && $_POST[ 'chapitres_submit_hidden' ] == 'Y' ) 
			{
				while(!isset($_POST['supprime_livre_'.$i])) { $i++; }
				
				$id_livre = $_POST['hidden_livre_'.$i];
				$ordre_livre = $i;
				$message .= "<p><strong>".__('Book removed.', 'domaine-chapitres' )."</strong></p>";
				$this->obj_chapitres_database->chapitres_DB_supprime_livre($id_livre,$ordre_livre);
			}
			if( isset($_POST[ 'chapitres_conteneur_sommaire_margin_left' ])) $message = "<p><strong>".__('Stylesheet saved.', 'domaine-chapitres' )."</strong></p>";
		}
			/* AFFICHER TOUT LE CONTENU DE LA PAGE DE REGLAGES : */
		echo '<div class="wrap">';
			// entête
		screen_icon();	
		echo "<h2 >" . __( "Chapters Settings", 'domaine-chapitres' ) . "</h2>";
		echo "<div class='updated' id='message'>".$message."</div>"; 
		
			// Récupérer les données des livres enregistrés dans la BDD et créer les onglets:
		$this->onglets_metabox_livres = $this->obj_chapitres_display->chapitres_display_cree_onglets('livre');
		$this->onglets_metabox_chapitres = $this->obj_chapitres_display->chapitres_display_cree_onglets('chapitre');
		$this->onglets_metabox_sommaire = $this->obj_chapitres_display->chapitres_display_cree_onglets('sommaire');
		
			// METABOXES
		
		$titre_metabox_articles = __( 'Select Posts and Pages', 'domaine-chapitres' )."<span class='droite'><img src ='". plugins_url('images/icone_posts_and_pages.png',dirname(__FILE__)) ."'></span>";
		$titre_metabox_livres = __( 'Manage Books', 'domaine-chapitres' )."<span class='droite'><img src ='". plugins_url('images/icone_livres_petit.png',dirname(__FILE__)) ."'></span>";
		$titre_metabox_chapitres = __( 'Manage Chapters', 'domaine-chapitres' )."<span class='droite'><img src ='". plugins_url('images/icone_chapitres_petit.png',dirname(__FILE__)) ."'></span>";
		$titre_metabox_sommaire = __( 'Preview Summaries', 'domaine-chapitres' )."<span class='droite'><img src ='". plugins_url('images/icone_sommaires_petit.png',dirname(__FILE__)) ."'></span>";
					// droite:
		add_meta_box("chapitres_onglets_livres", $titre_metabox_livres, array( &$this,"admin_remplir_meta_box_livres"), "chapitres",'normal','high');
		add_meta_box("chapitres_onglets_sommaire", $titre_metabox_sommaire, array( &$this,"admin_remplir_meta_box_sommaire"), "chapitres",'normal','high');
					// gauche:
		add_meta_box("chapitres_choisir_articles", $titre_metabox_articles, array( &$this,"admin_affiche_articles"), "chapitres",'side','core');
		add_meta_box("chapitres_onglets_chapitres", $titre_metabox_chapitres, array( &$this,"admin_remplir_meta_box_chapitres"), "chapitres",'side','core');
					// champs cachés obligatoires:
		wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false );
		wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false );
		
		
		echo "<div id='nav-menus-frame' class='has-right-sidebar'>";
		echo '<div id="menu-settings-column" class="metabox-holder" style="padding-top:10px;">';
		do_meta_boxes('chapitres','side','core');
		echo "</div>";
			
		echo '<div id="menu-management-liquid" class="metabox-holder">';
		do_meta_boxes('chapitres', 'normal', 'high'); 
		echo '</div>';
		echo "</div>";
			
		echo "</div>"; // fin de wrap
		// fenêtre modale:
		echo '<div id="fenetre_modale" class="fenetre_modale">';
		echo '<h2>'.__( 'WARNING', 'domaine-chapitres' ).'</h2>';
		echo "<p>".__( "A new tab is already waiting to be saved. You must save the Books before adding another.", 'domaine-chapitres' )."</p>";
		echo "<span id='ok' name='ok' class='boutongris' onclick='javascript:ferme_fenetre_modale();'>OK</span>";
		echo '</div>';	
			// fenêtre de styles:
		echo '<div id="fenetre_styles" class="fenetre_styles">';
			// Créer tous les champs de saisie de styles:
		echo $this->obj_chapitres_display->chapitres_display_affiche_formulaire_styles();
		echo '<div id="customize-preview" class="wp-full-overlay-main"></div>';
		echo '</div>';
			 
	}
	
		/********** GESTIONNAIRES DE SHORTCODES / SHORTCODE HANDLERS **********/
	
		/***** Shortcode de sommaire, du type [sommaire-chapitres livre=1 affiche-infos-sommaire=true] 
			   Summary shortcode, sthg like [sommaire-chapitres livre=1 affiche-infos-sommaire=true] *****/
	public function admin_sommaire_chapitres_shortcode( $atts )
	{
		extract(shortcode_atts(array( 
		'livre' => '',
		'limit' => '30',
		'affiche_infos' => 'true',
		'titre' => 'false',
		'resume' => 'false',
		'numeros' => 'true'
		), $atts));
		$sommaire = $this->obj_chapitres_display->chapitres_display_sommaire($livre,$limit,$affiche_infos,$titre,$resume,$numeros);
		return $sommaire;
			
	}
		/***** rien / do nothing *****/
	public function admin_bidon_shortcode() {  }
	
		/********** GESTIONNAIRES DE CONTENU DES METABOX / MANAGE METABOXES CONTENT **********/
		
	public function admin_remplir_meta_box_livres(){ echo $this->onglets_metabox_livres; }
	public function admin_remplir_meta_box_chapitres(){ echo $this->onglets_metabox_chapitres; }
	public function admin_remplir_meta_box_sommaire(){ echo $this->onglets_metabox_sommaire; }
		
	public function admin_affiche_articles() { $this->obj_chapitres_display->chapitres_display_affiche_metabox_articles();}
	
		/********** GESTIONNAIRES .JS ET .CSS / MANAGE .JS AND .CSS **********/
	
	private function admin_enqueue()
	{
			// n'inclure les fichiers .js et .css QUE DANS LE PLUGIN */
		add_action('admin_print_scripts-' . $this->pagehook, array( &$this,'admin_scripts_pour_chapitres'));
		add_action('admin_print_styles-' . $this->pagehook, array( &$this,'admin_styles_pour_chapitres'));
	}
	public function admin_scripts_pour_chapitres()
	{
			// Pour les metabox:
		wp_enqueue_script('common');
		wp_enqueue_script('wp-lists');
		wp_enqueue_script('postbox');
			// Pour le drag-and-drop des chapitres:
		wp_enqueue_script('jquery-ui');
			// Pour la gestion de la page:
		wp_enqueue_script('jquery-chapitres',plugins_url( 'javascript/jquery-chapitres.js',dirname(__FILE__) ), array( 'jquery' ));
			// Pour l'ajax:
		wp_enqueue_script( 'ajax-script', plugins_url( 'javascript/chapitres_ajax.js', dirname(__FILE__)  ), array('jquery'));
		wp_localize_script( 'ajax-script', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' )) );
			// Pour les styles des sommaires:
		wp_enqueue_script('wp-color-picker');
		wp_enqueue_script('customize-controls');
		wp_enqueue_script('accordion');
		
	}	
	public function admin_styles_pour_chapitres()
	{
		wp_enqueue_style('chapitres-css',plugins_url( "styles/chapitres.css", dirname(__FILE__) ));wp_enqueue_style('nav-menu');
		wp_enqueue_style( 'chapitres-front-css', plugins_url( 'styles/chapitres-front.css',   dirname(__FILE__) ) );
			// Pour les onglets de metabox, merci à Pete Mall: http://developersmind.com/2011/04/05/wordpress-tabbed-metaboxes/
		$color = get_user_meta( get_current_user_id(), 'admin_color', true );
		wp_enqueue_style(  'jf-metabox-tabs',plugins_url( 'styles/metabox-tabs.css',   dirname(__FILE__) ));
		wp_enqueue_style(  'jf-$color',plugins_url( "styles/metabox-$color.css", dirname(__FILE__) ));
		wp_enqueue_style('buttons');
		wp_enqueue_style( 'wp-color-picker' );
	}
	
			/********** GESTIONNAIRES AJAX / AJAX HANDLERS **********/
	
		/***** Ajouter un/des chapitre(s) à un livre 
			   Add chapter(s) to a book *****/
	public function admin_ajoute_chapitres_a_livre($crochet) 
	{
		if( $crochet != $this->pagehook ) return;
			// récupérer les valeurs du POST:
		$num_livre = $_POST['num_livre'];
		$num_page = $_POST['num_page_articles'];
		$articles_par_page = $_POST['nb_articles_par_page'];
		$nb_chapitres = $_POST['nb_chapitres'];
		$order_by = $_POST['order_by'];
		$i = 0;
		$lignes = "";
		while(isset($_POST['id_article'.$i]))
		{
			$id_article = $_POST['id_article'.$i];
			$nb_chapitres++;
				// ajouter un champ personnalisé numero-livre, et un champ personnalisé chapitre à tous ces articles
			$this->obj_chapitres_database->chapitres_DB_ajoute_CP_a_article($num_livre,$nb_chapitres,$id_article);
				// récupération des chapitres à insérer:
			$lignes .= $this->obj_chapitres_display->chapitres_display_renvoie_ligne_chapitre($id_article,$num_livre,$nb_chapitres);
			$i++;
		}			
			// récupérer les articles en fonction de num_page_articles et nb_articles_par_page
		$contenu_metabox_articles = $this->obj_chapitres_display->chapitres_display_affiche_choix_articles($articles_par_page,$num_page,$order_by);
			// passer les onglets des deux metabox actualisées par effet de cascade:
		$onglets_sommaires = $this->obj_chapitres_display->chapitres_display_cree_onglets('sommaire');
		$separateur = "¤&¤&¤";
		echo "$contenu_metabox_articles$separateur$lignes$separateur$onglets_sommaires";
		die();
	}
		
		/***** Actualisation de la metabox articles selon interaction utilisateur 
			   Refresh 'posts' metabox depending on the user interaction *****/
	public function admin_actualise_metabox_articles($crochet) 
	{
		if( $crochet != $this->pagehook ) return;
		$num_page = $_POST['num_page'];
		$articles_par_page = $_POST['nb_articles_par_page'];
		$order_by = $_POST['order_by'];
		$contenu_metabox_articles = $this->obj_chapitres_display->chapitres_display_affiche_choix_articles($articles_par_page,$num_page,$order_by);
		echo $contenu_metabox_articles;
		die();
	}
		
		/***** Actualisation de l'ordre des chapitres 
			   Refresh chapters sort *****/
	public function admin_change_ordre_chapitres($crochet)
	{
		if( $crochet != $this->pagehook ) return;			
			// suppression éventuelle des champs personnalisés reliés à un chapitre:
		if(isset($_POST['id_article_a_supprimer']))
		{
			$this->obj_chapitres_database->chapitres_DB_supprime_CP_article($_POST['id_article_a_supprimer']);
			$num_page = $_POST['num_page_articles'];
			$articles_par_page = $_POST['nb_articles_par_page']; 
			$order_by = $_POST['order_by'];
				// actualiser les articles:
			$articles = $this->obj_chapitres_display->chapitres_display_affiche_choix_articles($articles_par_page,$num_page,$order_by);

			echo $articles.'¤&¤&¤';
		}
		$i = 0;
			// actualiser les articles:
		while(isset($_POST['id_article'.$i]))
		{
			$id_article = $_POST['id_article'.$i];
			$ordre = $i+1;
			$this->obj_chapitres_database->chapitres_DB_modifie_ordre_article($id_article,$ordre);			
			$i++;
		}
			// actualiser le sommaire:
		$num_livre = $_POST['num_livre'];
		$limit = $_POST['limit'];
		if($limit == "") $limit=1000;
		$affiche_infos = $_POST['affiche_infos'];
		$affiche_titre = $_POST['affiche_titre'];
		$affiche_resume = $_POST['affiche_resume'];
		$liste_numerotee = $_POST['liste_numerotee'];
		$sommaire = $this->obj_chapitres_display->chapitres_display_sommaire($num_livre,$limit,$affiche_infos,$affiche_titre,$affiche_resume,$liste_numerotee);
		echo $sommaire;
		die();
	}
		
		/***** Actualisation des options d'un sommaire 
			   Refresh summary options *****/
	public function admin_actualise_options_sommaire($crochet)
	{
		if( $crochet != $this->pagehook ) return;	
			// récupération des paramètres:
		$num_livre = $_POST['num_livre'];
		$limit = $_POST['limit'];
		if($limit == "") $limit=1000;
		$affiche_infos = $_POST['affiche_infos'];
		$affiche_titre = $_POST['affiche_titre'];
		$affiche_resume = $_POST['affiche_resume'];
		$liste_numerotee = $_POST['liste_numerotee'];
		$sommaire = $this->obj_chapitres_display->chapitres_display_sommaire($num_livre,$limit,$affiche_infos,$affiche_titre,$affiche_resume,$liste_numerotee);
		echo $sommaire;
		die();
	}
	
	/***** Renvoie un objet json contenant les styles de la feuille css
		   Returns json object containing all styles from css sheet *****/
	public function admin_renvoie_json_styles($crochet)
	{
		if( $crochet != $this->pagehook ) return;
		$num_livre = $_POST['num_livre'];
			// récupération des styles json:
		$json_styles = $this->obj_chapitres_display->chapitres_display_renvoie_json_styles($num_livre);		
		echo $json_styles;
		die();
	}
	/***** Ecrit les styles dans la feuille css
		   Writes all styles in css sheet *****/
	public function admin_ecrit_styles($crochet)
	{
		if( $crochet != $this->pagehook ) return;
		$styles = $_POST['styles'];
		$styles_identifiant = $_POST['styles_identifiant'];
		$autres_styles = $_POST['autres_styles'];
		$polices = $_POST['polices'];
		$id_choisis = $_POST['id_choisis'];
		$num_livre = $_POST['num_livre'];
		$obj_chapitres_customize = new chapitres_customize();
		$resultat = $obj_chapitres_customize->chapitres_customize_ecrit_styles($num_livre,$styles,$styles_identifiant,$autres_styles,$id_choisis,$polices);
		echo $resultat;
		die();
	}
}

?>