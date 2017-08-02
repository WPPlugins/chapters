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

include_once dirname( __FILE__ ) . '/chapitres_customize.php';
	
class chapitres_display 
{

	private $obj_chapitres_database;
	
			/***** CONSTRUCTEUR / CONSTRUCTOR *****/
		function chapitres_display($objDB) 
	{
		$this->obj_chapitres_database = $objDB;
	}
	
			/********************************************************/
			/************       ONGLETS / TABS      *****************/
			/********************************************************/
	
			/***** Créer les onglets pour les livres, les chapitres et les sommaires
				   Create the tabs for books, chapters and summaries *****/
	public function chapitres_display_cree_onglets($metabox)
	{
		switch($metabox)
		{
			case "livre":
				return $this->chapitres_display_metabox_livres();
				break;
			case "chapitre":
				return $this->chapitres_display_metabox_chapitres();
				break;
			case "sommaire":
				return $this->chapitres_display_metabox_sommaires();
				break;
		}
	}
	
		/***** Renvoyer les entêtes d'onglets pour les 3 metaboxs 
			   Return the tab titles for the 3 metaboxes *****/
	private function chapitres_display_entetes_onglets($livres,$metabox)
	{
		$tab_titres = "<ul class='metabox-tabs' id='tabs_infos_".$metabox."'>";
		$i = 1;
		foreach ( $livres as $livre ) 
		{
				// titres des onglets
			$tab_titres .= "<li class='tab'><a ";
			if ($i == 1) $tab_titres .= "class='active clic_onglet_".$metabox."' ";
			else $tab_titres .= "class='clic_onglet_".$metabox."' ";
			$tab_titres .= "id='clic_onglet_".$metabox."$i' ";
			$tab_titres .= "href='javascript:void(null);'>";
			$tab_titres .= __( 'Book', 'domaine-chapitres' )." ".$i."</a></li>";
			$i++;
		}
		$tab_titres .= "</ul>";
		return $tab_titres;
	}

			/*****************************************************/
			/************     METABOX / METABOXES    *************/
			/*****************************************************/
	
		/***** Renvoyer le contenu de la metabox 'livres': 
			   Return the content of the metabox 'books' *****/
	public function chapitres_display_metabox_livres()
	{
		$livres = $this->obj_chapitres_database->chapitres_DB_select_all();
		$debut_formulaire = '<form name="form_livres" id="form_livres" method="post" action="" >';
		$debut_formulaire .= '<input type="hidden" name="chapitres_submit_hidden" value="Y" >';
			// champs hidden pour la traduction du contenu d'un onglet de livre en javascript:
		$debut_formulaire .= '<input type="hidden" name="trad_titre_livre" id="trad_titre_livre" value="'.__( 'Book Title', 'domaine-chapitres' ).'" >';	
		$debut_formulaire .= '<input type="hidden" name="trad_resume_livre" id="trad_resume_livre" value="'.__( 'Book Abstract', 'domaine-chapitres' ).'" >';	
		$debut_formulaire .= '<input type="hidden" name="trad_affiche_livre" id="trad_affiche_livre" value="'.__( 'Display this content', 'domaine-chapitres' ).'" >';	
		$debut_formulaire .= '<input type="hidden" name="trad_pas_encore" id="trad_pas_encore" value="'.__( "This Book has not been saved yet.", 'domaine-chapitres' ).'" >';		
		$debut_formulaire .= '<input type="hidden" name="trad_supprimer" id="trad_supprimer" value="'.__( "Remove this tab", 'domaine-chapitres' ).'" >';		
		$debut_formulaire .= '<input type="hidden" name="trad_livre" id="trad_livre" value="'.__( "Book", 'domaine-chapitres' ).'" >';				
		// suite du formulaire
		$debut_formulaire .= "<fieldset class='bord_gris'>";
		$debut_formulaire .= "<span class='gauche'><img src ='". plugins_url('images/icone_livres_grand.png',dirname(__FILE__))."'></span>";
		$debut_formulaire .= "<h1 class='gauche gros_titres gros_titre_bleu'>".__( 'BOOKS', 'domaine-chapitres' )."</h1>";
		$debut_formulaire .= "<p class='submit'>";
		$debut_formulaire .= "<a id='ajouter_un_livre' class='button-secondary' href='javascript:void(null);' title='";
		$debut_formulaire .= __( 'Add a Book', 'domaine-chapitres' )."'>".__( 'Add a Book', 'domaine-chapitres' )."</a>";
		$debut_formulaire .= "</p></fieldset>";
		$i = 1;
		foreach ( $livres as $livre ) 
		{
			$id = $livre->id_livre;
			$num = $livre->numero_livre;
			$titre_livre = stripslashes($livre->nom_livre);
			$resume_livre = stripslashes($livre->definition_livre);
			$booleen_livre = $livre->afficher_infos_livre;
				// contenu des onglets
			$tab_contenus .= "<div class='onglet_livre onglet_livre_DB' id='onglet_livre$i'>"; 
			$tab_contenus .= "<div class='tab-content'>";
			$tab_contenus .= "<fieldset class='fond_gris'>";
				// champ caché contenant l'id du livre:
			$tab_contenus .= "<input type='hidden' name='hidden_livre_$num' id='hidden_livre_$num' value='$id'>";
			$tab_contenus .= "<table class='form-table'>";
				// titre du livre:
			$tab_contenus .= "<tr><th scope='row'><label for='titre_livre_$num'>".__( 'Book Title', 'domaine-chapitres' ).":</label></th>";
			$tab_contenus .= "<td><input type='text' class='large-text' name='titre_livre_$num' id='titre_livre_$num' value='$titre_livre'></td>";
			$tab_contenus .= "</tr>";
				// résumé du livre:
			$tab_contenus .= "<tr><th scope='row'><label for='resume_livre_$num'>".__( 'Book Abstract', 'domaine-chapitres' ).":</label></th>";
			$tab_contenus .= "<td><textarea class='large-text' name='resume_livre_$num' id='resume_livre_$num' >$resume_livre</textarea></td>";
			$tab_contenus .= "</tr>";
				// case à cocher: afficher ou non les infos:
			$tab_contenus .= "<tr><th scope='row'><label for='affiche_infos_livre_$num'>".__( 'Display this content', 'domaine-chapitres' ).":</label></th>";
			$tab_contenus .= "<td><input type='checkbox' class='affiche_sommaire' name='affiche_infos_livre_$num' id='affiche_infos_livre_$num' ";
			if($booleen_livre) $tab_contenus .= "checked='checked'";
			$tab_contenus .= "></td>";
			$tab_contenus .= "</tr>";
				// bouton
			$tab_contenus .= "<tr><td colspan='2'>";
			$tab_contenus .= '<p class="submit">';
			$tab_contenus .= '<span class="spinner" id ="spinner_supprime_livre'.$num.'" ></span>';
			$tab_contenus .= "<input type='submit' name='supprime_livre_$i' ";
			$tab_contenus .= "id='supprime_livre_$i' class='button-secondary supprime_livre' onClick='";
			$tab_contenus .= "javascript:void(null);";
			$tab_contenus .= "' value='".__( 'Delete Book', 'domaine-chapitres' )." $i' />";
			$tab_contenus .= "</p>";
			$tab_contenus .= "</td></tr>";
			$tab_contenus .= "</table></fieldset></div></div>";
			$i++;
		}
		$tab_titres = $this->chapitres_display_entetes_onglets($livres,'livre');
		$fin_formulaire .= '<fieldset class="bord_gris">';
		$fin_formulaire .= "<span class='gauche'><img src ='". plugins_url('images/icone_livres_grand.png',dirname(__FILE__)) ."'></span>";
		$fin_formulaire .= '<p class="submit"><input type="submit" name="sauvegarder_livres" id="sauvegarder_livres" class="button-primary" value="'.__( 'Save Books', 'domaine-chapitres' ).'" /></p></fieldset></form>';

		return $debut_formulaire.$tab_titres.$tab_contenus.$fin_formulaire;
	}
	
		/***** Renvoyer le contenu de la metabox 'chapitres'
			   Return the content of the metabox 'chapters' *****/
	public function chapitres_display_metabox_chapitres()
	{
		global $post;
		$livres = $this->obj_chapitres_database->chapitres_DB_select_all();
		$tab_entete = "<div class='bord_gris' style='text-align:center;clear:both;'>";
		$tab_entete .= "<span class='gauche'><img src ='". plugins_url('images/icone_chapitres_grand.png',dirname(__FILE__)) ."'></span>";
		$tab_entete .= "<h1 class='gros_titres gros_titre_vert'>".__( 'CHAPTERS', 'domaine-chapitres' )."</h1>";
		$tab_entete .= "</div>";
		$tab_titres = $this->chapitres_display_entetes_onglets($livres,'chapitre');
		$i = 1;
		$tab_contenus = "";
		foreach ( $livres as $livre ) 
		{
			$id = $livre->id_livre;
			$num = $livre->numero_livre;
				// contenu des onglets
			$tab_contenus .= "<div class='onglet_chapitre onglet_chapitre_DB' id='onglet_chapitre$i'>"; 
			$tab_contenus .= "<div class='tab-content fond_gris'>";
				// champ caché contenant l'id du livre:
			$tab_contenus .= "<input type='hidden' name='hidden_livre_chapitre_$num' id='hidden_livre_chapitre_$num' value='$id'>";
				// JQUERY SORTABLES:
			$tab_contenus .= '<ul id="sortable'.$i.'" class="ui-sortable triable">';
				// On va récupérer tous les chapitres associés à ce livre:
			$chapitres = $this->obj_chapitres_database->chapitres_DB_renvoie_chapitres_livre($num);
			$j = 1;
			if ( $chapitres->have_posts() ) 
			{
				while ( $chapitres->have_posts() ) 
				{
					$chapitres->the_post();
					$id_chapitre = get_the_ID();
					$tab_contenus .= $this->chapitres_display_renvoie_ligne_chapitre($id_chapitre,$num,$j);
					$j++;
				}
			}
			$tab_contenus .= '</ul>';			
			$tab_contenus .= "</div></div>";
			$i++;
		}
		return $tab_entete.$tab_titres.$tab_contenus;
	}
	
		/***** Renvoyer une ligne de chapitre: 
			   Return a chapter line *****/
	public function chapitres_display_renvoie_ligne_chapitre($id_chapitre,$num_livre,$num_chapitre)
	{
		$chapitre = get_post($id_chapitre);
		$titre_chapitre = $chapitre->post_title;
			// raccourcir l'extrait
		$extrait_complet = $chapitre->post_excerpt;
		$extrait = __( 'None', 'domaine-chapitres' );
		$charlength = 150;
		if($extrait_complet != '')
		{
			if ( mb_strlen( $extrait_complet ) > $charlength ) 
			{
				$subex = mb_substr( $extrait_complet, 0, $charlength - 5 );
				$exwords = explode( ' ', $subex );
				$excut = - ( mb_strlen( $exwords[ count( $exwords ) - 1 ] ) );
				if ( $excut < 0 ) $extrait = mb_substr( $subex, 0, $excut );
				else $extrait = $subex;
				$extrait .= "[...]";
			}
			else $extrait = $extrait_complet;					
		}
			// obtenir le permalien
		$permalien = $this->obj_chapitres_database->chapitres_DB_renvoie_permalien_article($id_chapitre);
		$permalien = basename($permalien);
		$ligne = "";
		$ligne .= '<li class="ui-state-default" style="margin-bottom:0px;">';
		$ligne .= '<dl class="menu-item-bar" style="margin:0px;">';
			// partie visible
		$ligne .= '<dt class="menu-item-handle" style="width:90%;margin:auto;">';
		$ligne .= '<span class="item-title" style="margin:0px;">';
		$ligne .= '<span class="item-type" id="numero_chapitre_'.$id_chapitre.'">'.$num_chapitre.'. </span>';
		$ligne .= $titre_chapitre;
		$ligne .= '<div class="handlediv" id="poignee_livre_'.$num_livre.'_chapitre_'.$num_chapitre.'"';
		$ligne .= ' title="'.__( 'Click to Toggle', 'domaine-chapitres' ).'"></div>';
		$ligne .= '</span>';
		$ligne .= '</dt>';
		$ligne .= '</dl>';
			// partie cachée
		$ligne .= '<div class="menu-item-settings" id="contenu_livre_'.$num_livre.'_chapitre_'.$num_chapitre.'"';
		$ligne .= 'style="margin:auto;width:90%;padding:10px;display:none;">';
		$ligne .= '<table class="widefat" align="center">';
		$ligne .= '<tr><th class="row-title">'.__( 'SLUG', 'domaine-chapitres' ).'</th><th><code>'.$permalien.'</code></th></tr>';
		$ligne .= '<tr><td >'.__( 'EXCERPT', 'domaine-chapitres' ).'</td><td>'.$extrait.'</td></tr>';
		$ligne .= '<tr class="alternate"><td class="row-title" colspan="2">';
		$ligne .= '<a id="supprime_chapitre_livre_'.$id_chapitre.'"';
		$ligne .= ' class="item-delete submitdelete deletion" href="javascript:void(null);">'.__( 'Remove from this Book', 'domaine-chapitres' ).'</a>';
		$ligne .= "<input class='hidden_id_chapitre' name='hidden_id_chapitre$id_chapitre' type='hidden' value='$id_chapitre'>";
		$ligne .= '</td></tr>';
		$ligne .= '</table></div></li>';
		return $ligne;
	}
		
		/***** Renvoyer le contenu de la metabox sommaire
			   Return the content of the metabox 'summary' *****/
	private function chapitres_display_metabox_sommaires()
	{
		$livres = $this->obj_chapitres_database->chapitres_DB_select_all();
		$tab_entete = "<div class='bord_gris' style='text-align:center;clear:both;'>";
		$tab_entete .= "<span class='gauche'><img src ='".plugins_url('images/icone_sommaires_grand.png',dirname(__FILE__)) ."'></span>";
		$tab_entete .= "<h1 class='gros_titres gros_titre_rouge'>".__( 'SUMMARIES', 'domaine-chapitres' )."</h1>";
		$tab_entete .= "</div>";
		$tab_titres = $this->chapitres_display_entetes_onglets($livres,'sommaire');
		$i = 1;
		$tab_contenus = "";
		foreach ( $livres as $livre ) 
		{
			$id = $livre->id_livre;
			$num = $livre->numero_livre;
			$infos = $livre->afficher_infos_livre;
			if($infos == '1') $infos = "true";
			else $infos = "false";
				// contenu des onglets
			$tab_contenus .= "<div class='onglet_sommaire onglet_sommaire_DB' id='onglet_sommaire$i'>"; 
			$tab_contenus .= "<div class='tab-content fond_gris'>";
				// champ caché contenant l'id du livre:
			$tab_contenus .= "<input type='hidden' name='hidden_livre_sommaire_$num' id='hidden_livre_sommaire_$num' value='$id'>";
						// tableau de champs permettant de choisir les options:
			$tab_contenus .= '<table class="widefat" align="center" id="options_sommaire_'.$num.'"><thead>';
				// titres colonnes:
			$tab_contenus .= "<tr><th colspan='2' class='row-title'>".__( "DISPLAY OPTIONS", 'domaine-chapitres' )."</th><th><code>code</code></th></tr></thead>";
				// option afficher le titre
			if($infos=="true")
			$tab_contenus .= '<tbody><tr><td>';
			else
			$tab_contenus .= '<tbody><tr style="display:none;"><td>';
			$tab_contenus .= '<input class="option_affiche_titre" name="option_affiche_titre_'.$num.'" type="checkbox" id="option_affiche_titre_'.$num.'" />';
			$tab_contenus .= '</td>';
			$tab_contenus .= '<td >'.__( "Display title", 'domaine-chapitres' ).'</td><td><code>titre=false</code>';
			$tab_contenus .= '<span class="commentaire"> '.__( "(default)", 'domaine-chapitres' ).'</span></td></tr>';
				// option afficher le résumé
			if($infos=="true")
			$tab_contenus .= '<tr><td>';
			else
			$tab_contenus .= '<tr style="display:none;"><td>';
			$tab_contenus .= '<input class="option_affiche_resume" name="option_affiche_resume_'.$num.'" type="checkbox" id="option_affiche_resume_'.$num.'" />';
			$tab_contenus .= '</td>';
			$tab_contenus .= '<td >'.__( "Display abstract", 'domaine-chapitres' ).'</td><td><code>resume=false</code>';
			$tab_contenus .= '<span class="commentaire"> '.__( "(default)", 'domaine-chapitres' ).'</span></td></tr>';
				// option liste numérotée
			$tab_contenus .= '<tr><td>';
			$tab_contenus .= '<input class="option_liste_numerotee" name="option_liste_numerotee_'.$num.'" type="checkbox" id="option_liste_numerotee_'.$num.'" checked="checked"/>';
			$tab_contenus .= '</td>';
			$tab_contenus .= '<td >'.__( "Ordered list", 'domaine-chapitres' ).'</td><td ><code>numeros=true</code>';
			$tab_contenus .= '<span class="commentaire"> '.__( "(default)", 'domaine-chapitres' ).'</span></td></tr>';
				// option limite
			$tab_contenus .= '<tr><td>';
			$tab_contenus .= '</td>';
			$tab_contenus .= '<td >'.__( "Display chapters limit", 'domaine-chapitres' ).'</td>';
			$tab_contenus .= '<td><code>limit=</code>';
			$tab_contenus .= '<input class="option_limite" name="option_limite_'.$num.'" type="text" size="1" id="option_limite_'.$num.'" />';
			$tab_contenus .= '<span class="commentaire"> '.__( "(all if empty)", 'domaine-chapitres' ).'</span></td></tr></tbody>';
			$tab_contenus .= "<tfoot><tr><th colspan='2' class='row-title'>";
			$tab_contenus .= __( "COPY AND PASTE THIS", 'domaine-chapitres' );
			$tab_contenus .= "</th>";
			if($infos == "true")
			$tab_contenus .= "<th id='jeton_sommaire_$num' style='color:#FF5F5F;'>[sommaire-chapitres livre=$num affiche_infos=true]";
			else
			$tab_contenus .= "<th id='jeton_sommaire_$num' style='color:#FF5F5F;'>[sommaire-chapitres livre=$num]";
			$tab_contenus .= "</th></tr></tfoot>";
			$tab_contenus .= '</table>';
			$tab_contenus .= '<p class="apercu rouge">'.__( "Book index", 'domaine-chapitres' ).' '.$num.' :<span class="gerer_styles">'.__( "Manage Styles", 'domaine-chapitres' ).'</span><img id="styles_'.$num.'" src="'. plugins_url('images/icone_style.png',dirname(__FILE__)).'" class="image_styles"></p>';
			$tab_contenus .= "<div id='apercu_sommaire_$num'>";
			$tab_contenus .= $this->chapitres_display_sommaire($num,30,$infos,"false","false","true"); // par défaut aucune option n'est cochée.
			$tab_contenus .= "</div></div></div>";
			$i++;
		}
		return $tab_entete.$tab_titres.$tab_contenus;
	}
	
			/**************************************/
			/*******    ARTICLES / POSTS     ******/
			/**************************************/
		
		/***** Afficher les articles qui ne sont associés à aucun livre
			   Display posts not connected to any book *****/
	public function chapitres_display_affiche_metabox_articles()
	{
		echo $this->chapitres_display_affiche_choix_articles(10,1);
	}
	
		/***** Renvoyer le contenu de la metabox 'articles'
			   Return the content of the 'articles' metabox *****/
	public function chapitres_display_affiche_choix_articles($articles_par_page,$num_page,$order_by="title")
	{
			// Obtenir tous les articles qui ne sont PAS associés à un livre:
		global $post;	
		$offset = $articles_par_page*($num_page-1);
		$myposts = $this->obj_chapitres_database->chapitres_DB_articles_sans_livre($articles_par_page,$offset,$order_by);
		
		$nb_articles = $this->obj_chapitres_database->chapitres_DB_compte_articles_sans_livre();
		
			// CONTENU:

		$contenu_metabox = '<div class="tablenav"><div class="tablenav-pages"><span class="displaying-num">(';
		$contenu_metabox .= __( "available posts and pages", 'domaine-chapitres' ).": ".$nb_articles.")</span></div></div>";
		if($order_by == "title") $contenu_metabox .= '<a id="par_nom" href="javascript:void(null);" class="nav-tab nav-tab-active">Par nom</a><a id="par_date" href="javascript:void(null);" class="nav-tab">Par date</a>';
		else $contenu_metabox .= '<a id="par_nom" href="javascript:void(null);" class="nav-tab">Par nom</a><a id="par_date" href="javascript:void(null);" class="nav-tab nav-tab-active">Par date</a>';
		$contenu_metabox .= "<div id='conteneur_articles' class='posttypediv'>";
		$contenu_metabox .=  '<table class="widefat"><thead><tr><th>';
			// case à cocher sélectionnant/déselectionnant tout
		$contenu_metabox .=  '<input name="selectionne_tous_articles_haut" type="checkbox" id="selectionne_tous_articles_haut" />'; 
		$contenu_metabox .=  '</th><th class="row-title">'.__( "NAME", 'domaine-chapitres' ).'</th><th class="row-title"><i>'.__( "SLUG", 'domaine-chapitres' ).'</i></th></tr></thead>';
		$contenu_metabox .=  "<tbody>";
			// boucle
		if ( $myposts->have_posts() ) 
		{
			while ( $myposts->have_posts() ) 
				{
				$myposts->the_post();
				$id_article = get_the_ID();
				$le_titre = get_the_title($id_article);
				$contenu_metabox .=  "<tr><td>";
				$contenu_metabox .=  '<input class="selectionne_article" name="selectionne_article_';
				$contenu_metabox .=  $id_article;
				$contenu_metabox .=  '" type="checkbox" id="selectionne_article_'.$id_article.'" name="selectionne_article_'.$id_article.'" />'; 
				$contenu_metabox .=  "</td><td><label for='selectionne_article_".$id_article."'><strong>";
				$contenu_metabox .=  $le_titre;
				$contenu_metabox .=  "</strong></label></td><td><i>";
				$contenu_metabox .=  basename(get_permalink());
				$contenu_metabox .=  "</i></td></tr>";
				}
		}		
		$contenu_metabox .=  '</tbody><tfoot><tr><th>';
			// case à cocher sélectionnant/déselectionnant tout
		$contenu_metabox .=  '<input name="selectionne_tous_articles_bas" type="checkbox" id="selectionne_tous_articles_bas" />'; 
		$contenu_metabox .=  '</th><th class="row-title">'.__( "NAME", 'domaine-chapitres' ).'</th>';
		$contenu_metabox .=  '<th class="row-title"><i>'.__( "SLUG", 'domaine-chapitres' ).'</i></th></tr>';
		$contenu_metabox .=  "</tfoot></table>";
		$contenu_metabox .=  "</div>";
		
			// BOUTON AJOUTER:
		$ajout = '<div class="tablenav">';		
		$ajout .= "<p class='button-controls'>";
		$ajout .= "<span class='add-to-menu' style='margin-bottom:10px;'>";
		$ajout .= "<input id='btn_ajoute_article_a_livre' class='bouton_vert' type='submit' name='btn_ajoute_article_a_livre' ";
		$ajout .= "value='".__( "Add Chapter(s) to Book", 'domaine-chapitres' )." 1'>";
		$ajout .= "<input id='msg_ajoute_article_a_livre' type='hidden' value='".__( "Add Chapter(s) to Book", 'domaine-chapitres' )."' />";
		$ajout .= "<span class='spinner' id='spinner_ajout_article'></span>";
		$ajout .= "</span></p></div>";
			// PAGINATION: on doit tester s'il faut ou non activer les boutons
		$nb_pages = ceil($nb_articles/$articles_par_page);
		$class_premiers = "";$class_derniers = "";$class_precedents = "";$class_suivants = "";
		if($nb_pages == $num_page) {$class_derniers = " disabled";$class_suivants = " disabled";}
		if($num_page == 1) {$class_premiers = " disabled";$class_precedents = " disabled";}
		$pagination = "";
		$pagination .= '<div class="tablenav"><div class="tablenav-pages">';
		$pagination .= '<a class="first-page'.$class_premiers.'" href="javascript:void(null);" id="premiers_articles" title="'.__( "First", 'domaine-chapitres' ).'">«</a>';
		$pagination .= '<a class="prev-page'.$class_precedents.'" href="javascript:void(null);" id="articles_precedents" title="'.__( "Previous", 'domaine-chapitres' ).'">‹</a>';
		$pagination .= '<span class="paging-input">';
		$pagination .= '<input class="current-page" type="text" size="1" id="num_page_articles" name="num_page_articles" value="'.$num_page.'" title="'.__( "Current", 'domaine-chapitres' ).'"></span>';
		$pagination .= '<span class="displaying-num" id="nb_pages_articles">'.__( "on", 'domaine-chapitres' ).' '.$nb_pages.'</span><input id="nb_pages_max" type="hidden" value="'.$nb_pages.'"/>';
		$pagination .= '<a class="next-page'.$class_suivants.'" href="javascript:void(null);" id="articles_suivants" title="'.__( "Next", 'domaine-chapitres' ).'">›</a>';
		$pagination .= '<a class="last-page'.$class_derniers.'" href="javascript:void(null);" id="derniers_articles" title="'.__( "Last", 'domaine-chapitres' ).'">»</a>';
		$pagination .= '<span class="displaying-num">'.__( "by", 'domaine-chapitres' ).'</span>';
		$pagination .= '<span class="paging-input"><input class="current-page" type="text" size="1" id="nb_articles_par_page" name="nb_articles_par_page" value="'.$articles_par_page.'" >';
		$pagination .= '</span>';
		$pagination .= '</div></div><br/>';
		
		return $contenu_metabox.$pagination.$ajout;
	}
	
		/***** Retourner le sommaire d'un livre selon les options
			   Return the summary of a book according to the options *****/
	public function chapitres_display_sommaire($num_livre,$limit='1000',$affiche_infos="true",$affiche_titre="false",$affiche_resume="false",$liste_numerotee="true")
	{
		global $post;
		$sommaire = "";
		// Requête pour les articles
		$chapitres = $this->obj_chapitres_database->chapitres_DB_renvoie_chapitres_livre($num_livre,$limit);
		if ( $chapitres->have_posts() ) 
		{
				// conteneur
			$sommaire .= "<div class='chapitres_conteneur_sommaire' id='chapitres_conteneur_sommaire$num_livre'>";
			$afficher_infos_livre = $affiche_infos;
			if($afficher_infos_livre == 'true')
			{
					// Requête pour le livre:
				$livre = $this->obj_chapitres_database->chapitres_DB_select_num($num_livre);
				$titre_livre = stripslashes( $livre->nom_livre);
				$resume_livre = stripslashes($livre->definition_livre);
					// les infos sur le livre:
				$sommaire .= "<div class='chapitres_infos_livre' id='chapitres_infos_livre$num_livre'>";
				if($affiche_titre == "true") $sommaire .= "<h2 class='chapitres_titre_livre' id='chapitres_titre_livre$num_livre'>".$titre_livre."</h2>";
				if($affiche_resume == "true") $sommaire .= "<h4 class='chapitres_resume_livre' id='chapitres_resume_livre$num_livre'>".$resume_livre."</h4>";
				if($affiche_titre == "false" && $affiche_resume == "false") $sommaire .= "<h2 class='chapitres_titre_livre' id='chapitres_titre_livre$num_livre'>".__( "INDEX", 'domaine-chapitres' )."</h2>";
				$sommaire .= "</div>"; 
			}
				// sommaire du livre:
			$sommaire .= "<div class='chapitres_sommaire_livre' id='chapitres_sommaire_livre$num_livre'>";
				
			if($liste_numerotee == "true") $sommaire .= "<ol>";
			else $sommaire .= "<ul>";
				// Boucle
				while ( $chapitres->have_posts() ) 
				{
					$chapitres->the_post();		
					$sommaire .= "<li><a href='";
					$sommaire .= get_permalink();
					$sommaire .= "'>";
					$id_article = get_the_ID();
					$sommaire .= get_the_title($id_article);
					$sommaire .= "</a></li>";
				}
			if($liste_numerotee =="true") $sommaire .= "</ol>";
			else $sommaire .= "</ul>";
			$sommaire .= "</div></div>";

				//Réinitialiser la requête:
			wp_reset_query();
		}
		return $sommaire;
	}

			/******************************************************/
			/*******    PERSONNALISATION / CUSTOMIZATION     ******/
			/******************************************************/
		
		/***** Renvoyer un objet json contenant tous les styles
			   Returning json object containing all styles *****/
	public function chapitres_display_renvoie_json_styles($num_livre)
	{
		$obj_customize = new chapitres_customize();
		$styles = $obj_customize->chapitres_customize_renvoie_styles($num_livre);
		return $styles;
	}
	
		/***** Afficher tout le formulaire des styles
			   Display the whole styles form *****/
	public function chapitres_display_affiche_formulaire_styles()
	{
		$liste_groupes = array("chapitres_conteneur_sommaire","chapitres_infos_livre","chapitres_titre_livre","chapitres_resume_livre","chapitres_sommaire_livre",
		"chapitres_sommaire_livre_ul","chapitres_sommaire_livre_ol","chapitres_sommaire_livre_ul_li","chapitres_sommaire_livre_ol_li","chapitres_sommaire_livre_ul_li_before",
		"chapitres_sommaire_livre_a","chapitres_sommaire_livre_a_hover","chapitres_sommaire_livre_a_visited");
		$liste_selecteurs = array(".chapitres_conteneur_sommaire",".chapitres_infos_livre",".chapitres_titre_livre",".chapitres_resume_livre",".chapitres_sommaire_livre",
		".chapitres_sommaire_livre ul",".chapitres_sommaire_livre ol",".chapitres_sommaire_livre ul li",".chapitres_sommaire_livre ol li",".chapitres_sommaire_livre ul li:before",
		".chapitres_sommaire_livre a",".chapitres_sommaire_livre a:hover",".chapitres_sommaire_livre a:visited");
		$intitules_groupes = array(__( "GLOBAL CONTAINER", 'domaine-chapitres' ),__( "HEADER", 'domaine-chapitres' ),__( "TITLE", 'domaine-chapitres' ),__( "ABSTRACT", 'domaine-chapitres' ),
		__( "CONTAINER", 'domaine-chapitres' ),__( "LIST", 'domaine-chapitres' ),__( "ORDERED LIST", 'domaine-chapitres' ),__( "LINE", 'domaine-chapitres' ),__( "LINE", 'domaine-chapitres' ),
		__( "BEFORE LINE", 'domaine-chapitres' ),__( "LINK", 'domaine-chapitres' ),__( "HOVER LINK", 'domaine-chapitres' ),__( "VISITED LINK", 'domaine-chapitres' ));
		$formulaire = '<form id="customize-controls" class="wp-full-overlay-sidebar" style="position:absolute;" action="" method="post">';
		$formulaire .= '<div id="conteneur_id_ou_classe"><label>'.__( "Apply to all", 'domaine-chapitres' ).'   </label><input type="checkbox" id="id_ou_classe" ></div>';
		$formulaire .= '<div id="customize-header-actions" class="wp-full-overlay-header"><input type="button" name="sauver_styles" id="sauver_styles" class="button button-primary save" value="'.__( "Save", 'domaine-chapitres' ).'"  />';
		$formulaire .= '<span class="spinner"></span><a id="fermer_styles" class="back button" href="javascript:void(null);">'.__( "Cancel", 'domaine-chapitres' ).'</a></div>';
		$formulaire .= '<div class="wp-full-overlay-sidebar-content accordion-container"><div id="customize-theme-controls">';
		for($i=0;$i<13;$i++)
		{
			$formulaire .= '<div class="accordion-section entete_section" id="entete_'.$liste_groupes[$i].'" selecteur_css="'.$liste_selecteurs[$i].'" >';
			$formulaire .= '<div><span class="preview-notice"><strong>'.$intitules_groupes[$i].'</strong></span>';
			$formulaire .= '<span class="plus_moins">+</span></div></div>';
			$formulaire .= '<ul id="styles_'.$liste_groupes[$i].'" selecteur="'.$liste_selecteurs[$i].'" style="display:none;" >';
			switch($liste_groupes[$i])
			{
					// styler le texte
				case "chapitres_titre_livre": case "chapitres_resume_livre": case "chapitres_sommaire_livre_a": case "chapitres_sommaire_livre_a_hover": case "chapitres_sommaire_livre_a_visited":
					$formulaire .= $this->chapitres_display_item_texte($liste_groupes[$i]);
					break;
				
					// styler les puces des listes
				case "chapitres_sommaire_livre_ol_li": case "chapitres_sommaire_livre_ul_li":
					$formulaire .= $this->chapitres_display_select_list_style_type($liste_groupes[$i]);
					break;
	
				default: 
					break; 
			}
			$formulaire .= $this->chapitres_display_margin_paddings_border_background($liste_groupes[$i]);
			$formulaire .= '</ul>';
		}
		$formulaire .= wp_nonce_field('changer_css','champ_changement');
		$formulaire .= '</div></div></form>';
		return $formulaire;
	}
	
		/***** Renvoyer les champs de saisie contenant margins,paddings,bordures,fond
			   Returning margin,paddings,border,background fields *****/
	private function chapitres_display_margin_paddings_border_background($id_champ)
	{
		$noms_margins = array("margin_left","margin_right","margin_top","margin_bottom");
		$intitules_margins = array(__( "Margin Left", 'domaine-chapitres' ),__( "Margin Right", 'domaine-chapitres' ),__( "Margin Top", 'domaine-chapitres' ),__( "Margin Bottom", 'domaine-chapitres' ));
		$noms_paddings = array("padding_left","padding_right","padding_top","padding_bottom");
		$intitules_paddings = array(__( "Padding Left", 'domaine-chapitres' ),__( "Padding Right", 'domaine-chapitres' ),__( "Padding Top", 'domaine-chapitres' ),__( "Padding Bottom", 'domaine-chapitres' ));
		$noms_tailles_bordures = array("border_left_width","border_right_width","border_top_width","border_bottom_width");
		$intitules_tailles_bordures = array(__( "Border Left Width", 'domaine-chapitres' ),__( "Border Right Width", 'domaine-chapitres' ),__( "Border Top Width", 'domaine-chapitres' ),__( "Border Bottom Width", 'domaine-chapitres' ));
		$noms_couleurs_bordures = array("border_left_color","border_right_color","border_top_color","border_bottom_color");
		$intitules_couleurs_bordures = array(__( "Border Left Color", 'domaine-chapitres' ),__( "Border Right Color", 'domaine-chapitres' ),__( "Border Top Color", 'domaine-chapitres' ),__( "Border Bottom Color", 'domaine-chapitres' ));
		$noms_styles_bordures = array("border_left_style","border_right_style","border_top_style","border_bottom_style");
		$intitules_styles_bordures = array(__( "Border Left Style", 'domaine-chapitres' ),__( "Border Right Style", 'domaine-chapitres' ),__( "Border Top Style", 'domaine-chapitres' ),__( "Border Bottom Style", 'domaine-chapitres' ));
		$options_styles_bordures = array("none","dotted","dashed","solid","double","groove","ridge","inset","outset","inherit");
			// margins:
		$contenu = '<li class="control-section accordion-section">';
		$contenu .= '<h3 class="accordion-section-title">'.__( "MARGINS", 'domaine-chapitres' ).'</h3><ul class="accordion-section-content">';
		for($i=0;$i<4;$i++)
		{
			$contenu .= '<li class="customize-control customize-control-text_formatting"><span class="customize-control-title">'.$intitules_margins[$i].'</span><label>';
			$contenu .= '<input id="'.$id_champ.'_'.$noms_margins[$i].'" name="'.$id_champ.'_'.$noms_margins[$i].'" propcss="'.$noms_margins[$i].'" type="text" placeholder="..." class="styles-font-size"/> px</label></li>';
		}
		$contenu .= '</ul></li>';
			// paddings:
		$contenu .= '<li class="control-section accordion-section">';
		$contenu .= '<h3 class="accordion-section-title">'.__( "PADDINGS", 'domaine-chapitres' ).'</h3><ul class="accordion-section-content">';
		for($i=0;$i<4;$i++)
		{
			$contenu .= '<li class="customize-control customize-control-text_formatting"><span class="customize-control-title">'.$intitules_paddings[$i].'</span><label>';
			$contenu .= '<input id="'.$id_champ.'_'.$noms_paddings[$i].'" name="'.$id_champ.'_'.$noms_paddings[$i].'" propcss="'.$noms_paddings[$i].'" type="text" placeholder="..." class="styles-font-size"/> px</label></li>';
		}
		$contenu .= '</ul></li>';
			// borders:
		$contenu .= '<li id="accordion-section-title_tagline" class="control-section accordion-section">';
		$contenu .= '<h3 class="accordion-section-title">'.__( "BORDERS", 'domaine-chapitres' ).'</h3><ul class="accordion-section-content">';
		for($i=0;$i<4;$i++)
		{
			$contenu .= '<li class="customize-control customize-control-text_formatting"><span class="customize-control-title">'.$intitules_tailles_bordures[$i].'</span><label>';
			$contenu .= '<input id="'.$id_champ.'_'.$noms_tailles_bordures[$i].'" name="'.$id_champ.'_'.$noms_tailles_bordures[$i].'" propcss="'.$noms_tailles_bordures[$i].'" type="text" placeholder="..." class="styles-font-size"/> px</label></li>';
			$contenu .= '<li class="customize-control customize-control-color"><label><span class="customize-control-title">'.$intitules_couleurs_bordures[$i].'</span><div class="customize-control-content">';
			$contenu .= '<input id="'.$id_champ.'_'.$noms_couleurs_bordures[$i].'" name="'.$id_champ.'_'.$noms_couleurs_bordures[$i].'" propcss="'.$noms_couleurs_bordures[$i].'" class="color-picker-hex" type="text" maxlength="7" placeholder="'.__( "Hexa Value", 'domaine-chapitres' ).'" /></div></label></li>';
			$contenu .= '<li class="customize-control customize-control-text_formatting"><span class="customize-control-title">'.$intitules_styles_bordures[$i].'</span><label>';
			$contenu .= '<select id="'.$id_champ.'_'.$noms_styles_bordures[$i].'" name="'.$id_champ.'_'.$noms_styles_bordures[$i].'" propcss="'.$noms_styles_bordures[$i].'" class="selectionner_styles_bordures">';
				for($j=0;$j<10;$j++) $contenu .= '<option value="'.$options_styles_bordures[$j].'">'.$options_styles_bordures[$j].'</option>';
			$contenu .= '</select></label></li>';
		}
		$contenu .= '</ul></li>';
			// background:
		$contenu .= '<li id="accordion-section-colors" class="control-section accordion-section">';
		$contenu .= '<h3 class="accordion-section-title">'.__( "BACKGROUND", 'domaine-chapitres' ).'</h3><ul class="accordion-section-content">';
		$contenu .=  '<li class="customize-control customize-control-color"><label><span class="customize-control-title">'.__( "Background Color", 'domaine-chapitres' ).'</span><div class="customize-control-content">';
		$contenu .=  '<input id="'.$id_champ.'_background_color" name="'.$id_champ.'_background_color" propcss="background_color" class="color-picker-hex" type="text" maxlength="7" placeholder="'.__( "Hexa Value", 'domaine-chapitres' ).'" /></div></label></li>';
		$contenu .= '</ul></li>';
					
		return $contenu;
	}
	
		/***** Renvoyer un item de texte contenant la taille, la couleur et le menu select des polices
			   Returning font-size, color and font select fields *****/
	private function chapitres_display_item_texte($id_champ)
	{
		$polices_classiques = array('Arial','Bookman','Century Gothic','Comic Sans MS','Courier','Garamond','Georgia','Helvetica','Lucida Grande','Palatino','Tahoma','Times','Trebuchet MS','Verdana');
		$contenu = '<li id="accordion-section-title_tagline" class="control-section accordion-section"><h3 class="accordion-section-title">'.__( "TEXT", 'domaine-chapitres' ).'</h3>';
		$contenu .= '<ul class="accordion-section-content"><li class="customize-control customize-control-text_formatting">';
		$contenu .= '<span class="customize-control-title">'.__( "Size", 'domaine-chapitres' ).'</span><label><input id="'.$id_champ.'_font_size" propcss="font_size" type="text" placeholder="..." value="" class="styles-font-size"/> px</label>';
		$contenu .= '<label><select id="'.$id_champ.'_font_family" propcss="font_family" name="'.$id_champ.'_font_family" propcss="font_family" class="styles-font-family">';
		$contenu .= '<option class="label first" value="" selected="selected">'.__( "FONT", 'domaine-chapitres' ).'</option><option class="label" value="">'.__( "Standard Fonts", 'domaine-chapitres' ).'</option>';
		for($i=0;$i<14;$i++)
		{
			$contenu .= "<option value='".$polices_classiques[$i]."'>".$polices_classiques[$i]."</option>";
		}
		$contenu .= '<option class="label" value="">Polices Google</option></select></label></li>';
		$contenu .= '<li id="customize-control-header_textcolor" class="customize-control customize-control-color">';
		$contenu .= '<label><span class="customize-control-title">Couleur du Texte</span><div class="customize-control-content">';
		$contenu .= '<input id="'.$id_champ.'_color" name="'.$id_champ.'_color" propcss="color" class="color-picker-hex" type="text" maxlength="7" placeholder="'.__( "Hexa Value", 'domaine-chapitres' ).'" /></div>';
		$contenu .= '</label></li></ul></li>';
		return $contenu;
	}
	
		/***** Renvoyer un menu select de styles de liste
			   Returning list-style-type select field *****/
	private function chapitres_display_select_list_style_type($id_champ)
	{
		$options_de_puces = array("armenian","circle","cjk-ideographic","decimal","decimal-leading-zero","disc","georgian","hebrew","hiragana","hiragana-iroha","inherit",
		"katakana","katakana-iroha","lower-alpha","lower-greek","lower-latin","lower-roman","none","square","upper-alpha","upper-latin","upper-roman");
		$contenu = '<li id="accordion-section-title_tagline" class="control-section accordion-section"><h3 class="accordion-section-title">'.__( "LIST STYLES", 'domaine-chapitres' ).'</h3>';
		$contenu .= '<ul class="accordion-section-content"><li class="customize-control customize-control-text_formatting">';
		$contenu .= '<label><select id="'.$id_champ.'_list_style_type" name="'.$id_champ.'_list_style_type" propcss="list_style_type" class="selectionner_puces_listes">';
		for($j=0;$j<22;$j++) 
		{ 
			if($options_de_puces[$j] != "none" && $options_de_puces[$j] != "inherit")
			{
				$contenu .= '<option style="background:url('.plugins_url('/images/liststyletypes/',dirname(__FILE__)).$options_de_puces[$j].'.png)no-repeat ;background-position:right;height:40px;" ';
				$contenu .= 'value="'.$options_de_puces[$j].'">'.$options_de_puces[$j].'</option>';}
			else
			{
				$contenu .= '<option value="'.$options_de_puces[$j].'">'.$options_de_puces[$j];
				$contenu .= '</option>';
			}
		}
		$contenu .= '</select></label></li></ul></li>';
		return $contenu;
	}
}

?>