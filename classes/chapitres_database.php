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

class chapitres_database 
{
	
	
				/**********      CONSTRUCTEUR / CONSTRUCTOR      **********/
	public function chapitres_database() 
	{
		global $wpdb;
		global $chapitres_db_version;
	}
	
				/********************************************/
				/**********  ACTIVATION / SUPPRESSION  ******/
				/**********  ACTIVATE / UNINSTALL  **********/
				/********************************************/
	
		/***** Création de la table de BDD 'chapitres' lors de l'activation de l'extension
			   Create a DB table named 'chapitres' when activating the plugin *****/
	public function chapitres_DB_install() 
	{
	   global $wpdb;
	   global $chapitres_db_version;
	   $table_chapitres = $wpdb->prefix."chapitres";	
	   $sql = "CREATE TABLE IF NOT EXISTS ".$table_chapitres." (
	  id_livre mediumint(9) NOT NULL AUTO_INCREMENT,
	  numero_livre tinyint NOT NULL,
	  nom_livre tinytext NOT NULL,
	  definition_livre text NULL,
	  afficher_infos_livre tinyint(1) NOT NULL,
	  UNIQUE KEY id_livre (id_livre)
		);";

	   require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	   dbDelta( $sql );
	 
	   add_option( "chapitres_db_version", $chapitres_db_version );
	}

		/***** Création d'un livre par défaut / Create a default book *****/
	public function chapitres_DB_install_data() 
	{
	   global $wpdb;
	   $id_l = "1";
	   $num_l = "1";
	   $nom_l = __( "BOOK", 'domaine-chapitres' )." 1";
	   $def_l = __( "Default Book", 'domaine-chapitres' );
	   $table_chapitres = $wpdb->prefix."chapitres";
	   $rows_affected = $wpdb->insert( $table_chapitres, array( 'id_livre' => $id_l, 'numero_livre' => $num_l, 'nom_livre' => $nom_l, 'definition_livre' => $def_l ) );
	}
	
		/***** Supprimer la table de BDD 'chapitres' lors de la suppression de l'extension: 
			   Delete DB table 'chapitres' when uninstalling the plugin *****/
	public function chapitres_DB_desinstall() 
	{
		global $wpdb;
		delete_option('chapitres_db_version');
			// Supprimer tous les champs personnalisés associés aux livres:
				// 1. Rechercher tous les articles et pages associés au livre: 
		$types = $this->chapitres_DB_renvoie_array_types();
		$args = array(
		'posts_per_page'   => -1,
		'post_type' => $types,
		'post_status' => 'publish',
		'order' => 'ASC' , 
		'orderby' => 'meta_value_num' ,
		'meta_key' => 'numero-livre',
		'fields' => 'ids'
		);
		$query = new WP_Query( $args );
		if ( $query->have_posts() ) : while ( $query->have_posts() ) : the_post();
		$id_contenu = get_the_ID();
				// 2. Supprimer tous les champs personnalisés numero-livre et chapitre pour ces articles 
		delete_post_meta($id_contenu, 'numero-livre');
		delete_post_meta($id_contenu, 'chapitre');
		endwhile; 
		endif;
		wp_reset_postdata();
			// Supprimer toute la table:
		$table_chapitres = $wpdb->prefix."chapitres";
		$wpdb->query("DROP TABLE IF EXISTS ".$table_chapitres);
		wp_reset_query();
	}

				/*******************************************/
				/********** FONDAMENTAUX / BASICS **********/
				/*******************************************/
	
	public function chapitres_DB_count_all()
	{
		global $wpdb;
		$table_chapitres = $wpdb->prefix."chapitres";
		$sql = "SELECT COUNT(*) FROM ".$table_chapitres;
		$total = $wpdb->get_var( $sql );
		wp_reset_postdata();
		return $total;
	}
	
	public function chapitres_DB_select_all()
	{
		global $wpdb;
		$table_chapitres = $wpdb->prefix."chapitres";
		$sql = "SELECT * FROM ".$table_chapitres;
		$livres = $wpdb->get_results( $sql	);
		wp_reset_postdata();
		return $livres;
	}
	
	public function chapitres_DB_select_num_sups($num)
	{
		global $wpdb;
		$table_chapitres = $wpdb->prefix."chapitres";
		$sql = "SELECT * FROM ".$table_chapitres;
		$sql .= " WHERE numero_livre > ".$num;
		$sql .= " ORDER BY numero_livre";
		$resultat = $wpdb->get_results( $sql );
		wp_reset_postdata();
		return $resultat;
	}
	
	public function chapitres_DB_select_num($num)
	{
		global $wpdb;
		$table_chapitres = $wpdb->prefix."chapitres";
		$sql = "SELECT * FROM ".$table_chapitres;
		$sql .= " WHERE numero_livre = ".$num;
		$resultat = $wpdb->get_row( $sql );
		wp_reset_postdata();
		return $resultat;
	}
	
	public function chapitres_DB_insert($nom_l,$def_l,$affiche_l)
	{
		global $wpdb;
		$table_chapitres = $wpdb->prefix."chapitres";
		$sql = "SELECT COUNT(*) FROM ".$table_chapitres;
		$num_l = $wpdb->get_var( $sql );
		$num_l ++;
		wp_reset_postdata();
		$wpdb->insert( $table_chapitres, array( 'id_livre' => '', 'numero_livre' => $num_l, 'nom_livre' => $nom_l, 'definition_livre' => $def_l, 'afficher_infos_livre' => $affiche_l ) );
		wp_reset_postdata();
	}
	
	public function chapitres_DB_update($fields,$clause_id)
	{
		global $wpdb;
		$table_chapitres = $wpdb->prefix."chapitres";
		$wpdb->update( $table_chapitres,$fields,$clause_id );
		wp_reset_postdata();
	}
	
	public function chapitres_DB_delete($id)
	{
		global $wpdb;
		$table_chapitres = $wpdb->prefix."chapitres";
		$wpdb->query("DELETE FROM ".$table_chapitres." WHERE id_livre = ".$id);//$wpdb->delete( $table_chapitres, array( 'id_livre' => $id ) );
		wp_reset_postdata();
	}
	
////////////////////////////////////////////////////////////////////////////////////////////////////

				/***********************************************************/
				/********* REQUETES SPECIFIQUES / SPECIFIC QUERIES *********/
				/***********************************************************/
	
		/***** Supprimer un livre / Remove a book *****/
	public function chapitres_DB_supprime_livre($id_livre,$ordre_livre)
	{
	
		///// I) SUPPRIMER TOUS LES CHAMPS PERSONNALISES ASSOCIES AU LIVRE /////
		
				// 1. Rechercher tous les articles et pages associés au livre: 
			$types = $this->chapitres_DB_renvoie_array_types();
			$args = array(
			'posts_per_page'   => -1,
			'post_type' => $types,
			'post_status' => 'publish',
			'order' => 'ASC' , 
			'orderby' => 'meta_value_num' ,
			'meta_key' => 'numero-livre',
			'meta_value' => $ordre_livre,
			'fields' => 'ids'
			);
			$query = new WP_Query( $args );
			if ( $query->have_posts() ) : while ( $query->have_posts() ) : the_post();
			$id_article = get_the_ID();
				// 2. Supprimer tous les champs personnalisés numero-livre et chapitre pour ces articles 
			delete_post_meta($id_article, 'numero-livre');
			delete_post_meta($id_article, 'chapitre');
			endwhile; 
			endif;
			wp_reset_postdata();
			wp_reset_query();
				// 3. Rechercher tous les articles qui comportent le sommaire du livre, pour actualiser les jetons de sommaire
			$this->chapitres_DB_modifie_contenu_champ_sommaire($ordre_livre);
				// 4. Supprimer le livre 
			$this->chapitres_DB_delete($id_livre);
		
		///// II) CHANGER TOUS LES AUTRES LIVRES SUCCESSEURS: ORDRE ET CHAMPS PERSONNALISES /////
		
					// 1. Sélectionner le numéro d'ordre de tous les livres dont le numéro est supérieur
			$livres_successeurs = $this->chapitres_DB_select_num_sups($ordre_livre);
			$tab_articles_concernes = array();
			foreach ( $livres_successeurs as $livre ) 
			{
				$articles_concernes = array();
				$id_livre = $livre->id_livre;
				$numero_livre = $livre->numero_livre;
				$nouveau_numero_livre = $numero_livre-1;
					// 2. Rechercher tous les chapitres associés à chaque livre, on ne les modifie pas tt de suite pour éviter l'effet domino 
				$types = $this->chapitres_DB_renvoie_array_types();
				$args = array(
				'posts_per_page'   => -1,
				'post_type' => $types,
				'post_status' => 'publish',
				'order' => 'ASC' , 
				'orderby' => 'meta_value_num' ,
				'meta_key' => 'numero-livre',
				'meta_value' => $numero_livre,
				'fields' => 'ids'
				);
				$query = new WP_Query( $args );
				if ( $query->have_posts() ) : while ( $query->have_posts() ) : the_post();
				$id_article = get_the_ID();
				array_push($articles_concernes,$id_article);
				endwhile; 
				endif;
				array_push($tab_articles_concernes,$articles_concernes);
				wp_reset_postdata();
					// 3. Modifier la propriété numero_livre dans la table chapitres:
				$champs = array( 'numero_livre' => $nouveau_numero_livre );
				$clause_id = array( 'id_livre' => $id_livre );
				$this->chapitres_DB_update($champs,$clause_id);
			}
				// 4. Modifier la valeur numero-livre pour les chapitres concernés
			$lng= count($tab_articles_concernes);
			for($i=0;$i<$lng;$i++)
			{
				$nb_articles = count($tab_articles_concernes[$i]);
				for($j=0;$j<$nb_articles;$j++)
				{
					$id_article = $tab_articles_concernes[$i][$j];
					$numero = get_post_meta($id_article, 'numero-livre',true);
					$numero --;
					update_post_meta($id_article, 'numero-livre', $numero);
				}
			}
	}
	
		/***** Modifier tous les articles ou pages qui contiennent [sommaire-chapitres...] en fonction d'un numéro de livre supprimé
	   Update all posts or pages which include [sommaire-chapitres...], given the number of the deleted book *****/
	private function chapitres_DB_modifie_contenu_champ_sommaire($numero_livre)
	{
		global $wpdb;
		
			// 1. supprimer tous les champs sommaire pour ce livre:
					
		$articles_sommaire = $wpdb->get_results(
								"SELECT * FROM $wpdb->posts
								WHERE post_content LIKE '%[sommaire-chapitres livre=".$numero_livre." %'
								AND post_status = 'publish'
								;");
		if ( $articles_sommaire )
		{
			foreach ( $articles_sommaire as $article )
			{
				$id = $article->ID;
				$contenu = $article->post_content;
					// retrouver la chaîne complète:
				$masque_jeton = '|\[sommaire-chapitres livre='.$numero_livre.'(.*)\]|';
				preg_match($masque_jeton,$contenu,$resultat);
				$a_remplacer = $resultat[0];
				$contenu_modifie = str_replace($a_remplacer,"",$contenu);
				$wpdb->update( $wpdb->posts, array( 'post_content' => $contenu_modifie), array( 'ID' => $id ));
				wp_reset_postdata();
			}
		}
		
				// 2. décrémenter tous les champs sommaire pour les livres > à ce numéro:
				
			if($numero_livre != $nb_livres)
			{	
					// préparer les clés de remplacement pour strtr
				$nb_livres = $this->chapitres_DB_count_all();
				$cles_valeurs = "\$tab_cles_valeurs = array(";
				for($i=1;$i<$numero_livre;$i++)
				{
					$cles_valeurs .= "'[sommaire-chapitres livre=".$i."' => '[sommaire-chapitres livre=".$i."', ";
				}
				for($i=$numero_livre+1;$i<$nb_livres+1;$i++)
				{
					if($i<$nb_livres)
					$cles_valeurs .= "'[sommaire-chapitres livre=".$i."' => '[sommaire-chapitres livre=".($i-1)."', ";
					else
					$cles_valeurs .= "'[sommaire-chapitres livre=".$i."' => '[sommaire-chapitres livre=".($i-1)."'";
				}
				$cles_valeurs .= ");";
				eval($cles_valeurs); // on utilisera donc $tab_cles_valeurs
					
					// récupérer les ids de tous les articles ou pages qui possèdent au moins un champ sommaire-chapitres
					
				$articles_concernes = $wpdb->get_results(
								"SELECT * FROM ".$wpdb->posts."
								WHERE post_content LIKE '%[sommaire-chapitres livre=%'
								AND post_status = 'publish'
								;");
								
				if ( $articles_concernes )
				{
					foreach ( $articles_concernes as $article )
					{
						$id = $article->ID;
						$contenu = $article->post_content;
						$contenu = strtr($contenu,$tab_cles_valeurs);
						$wpdb->update( $wpdb->posts, array( 'post_content' => $contenu), array( 'ID' => $id ));
					}
				}
				wp_reset_postdata();
			}
	}
	
		/***** Renvoyer la liste des articles ou pages NON associés à un livre selon critères de navigation: 
			   Return the posts or pages NOT connected to a book, depending on the navigation inputs *****/
	public function chapitres_DB_articles_sans_livre($articles_par_page,$offset,$order_by="title")
	{
		global $post;
		if ( ! is_int(intval ( $articles_par_page , 10 ))) $articles_par_page = '';
		if ( $order_by == "title" ) $order = "ASC";
		else $order = "DESC";
			// 1. Articles associés à un livre
		$articles_a_exclure = $this->chapitres_DB_renvoie_articles_avec_livre();
			// 2. Rechercher tous les articles et pages NON associés à un livre, y compris les custom posts:
		$types =  $this->chapitres_DB_renvoie_array_types();
		$query = new WP_Query( array( 
		'post_type' => $types,
		'post_status' => 'publish',
		'order' => $order,
		'orderby' => $order_by,
		'posts_per_page' => $articles_par_page, 
		'offset' => $offset,
		'post__not_in' => $articles_a_exclure ) 
		);
		wp_reset_postdata();
		return $query;
	}
	
		/***** Compter TOUS les articles ou pages NON associés à un livre
			   Count ALL posts or pages NOT connected to a book *****/
	public function chapitres_DB_compte_articles_sans_livre()
	{
		$articles_a_exclure = $this->chapitres_DB_renvoie_articles_avec_livre();
			// récupérer les noms des types d'articles personnalisés:
		$types = $this->chapitres_DB_renvoie_array_types();
		$nb_total_articles = 0;
		foreach ( $types  as $type ) 
		{
			$nb_total_articles += (int)wp_count_posts($type)->publish;
		}
		$nb_total_articles -= count($articles_a_exclure);
		return $nb_total_articles;
	}
		
		/***** Retourner un tableau des ids d'articles ou de pages associés à un livre
			   Return an array of IDs of posts or pages connected to a book *****/
	private function chapitres_DB_renvoie_articles_avec_livre()
	{
		global $post;
		$types = $this->chapitres_DB_renvoie_array_types();
		$args = array(
		'posts_per_page'   => -1,
		'post_type' => $types,
		'post_status' => 'publish',
		'order' => 'ASC' , 
		'orderby' => 'meta_value_num' ,
		'meta_key' => 'numero-livre',
		'fields' => 'ids'
		);
		$articles_a_exclure = array();
		$query = new WP_Query( $args );
		if ( $query->have_posts() ) : while ( $query->have_posts() ) : $query->the_post();	
			$id_contenu = get_the_ID();
			array_push($articles_a_exclure,$id_contenu);
		endwhile;
		endif;
		wp_reset_postdata();
		return $articles_a_exclure;
	}
	
		/***** Renvoyer TOUS les articles ou pages associés à un certain livre
			   Return ALL posts or pages connected to a particular book *****/
	public function chapitres_DB_renvoie_chapitres_livre($num_livre,$limit=-1)
	{
		if ( ! is_int(intval ( $limit , 10 ))) $limit = -1;
			// Il faut les ranger par ordre de chapitre
		$types = $this->chapitres_DB_renvoie_array_types();
		$args = array(
		'posts_per_page'   => $limit,
		'post_type' => $types,
		'post_status' => 'publish',
		'order' => 'ASC' , 
		'orderby' => 'meta_value_num' ,
		'meta_key' => 'chapitre',
		'meta_query' => array( array( 'key' => 'numero-livre', 'value' => $num_livre ) )
		);
		$query = new WP_Query( $args );
		wp_reset_postdata();
		return $query;
	}
	
		/***** Ajouter les champs personnalisés 'numero-livre' et 'chapitre' et leurs valeurs à un article ou une page
			   Add custom fields 'numero-livre' and 'chapitre' and their values to a post or page *****/
	public function chapitres_DB_ajoute_CP_a_article($num_livre,$num_chapitre,$id_article)
	{
		add_post_meta($id_article, 'numero-livre', $num_livre, true);
		add_post_meta($id_article, 'chapitre', $num_chapitre, true);
	}
	
		/***** Supprimer les champs personnalisés 'numero-livre' et 'chapitre' associés à un article ou une page: 
			   Remove custom fields 'numero-livre' and 'chapitre' connected to a certain post or page *****/
	public function chapitres_DB_supprime_CP_article($id_article)
	{
		delete_post_meta($id_article, 'numero-livre');
		delete_post_meta($id_article, 'chapitre');
	}
	
		/***** Modifier un champ personnalisé 'chapitre' associé à un article ou une page: 
			   Update a custom field 'chapitre' connected to a post or page *****/
	public function chapitres_DB_modifie_ordre_article($id_article,$num_chapitre)
	{
		update_post_meta($id_article, 'chapitre', $num_chapitre);
	}
		
		/***** Renvoyer le permalien d'un article ou d'une page 
			   Return the permalink of a post or page *****/
	public function chapitres_DB_renvoie_permalien_article($id_article)
	{
		global $post;
		$permalien = "";
		$query = new WP_Query( 'p='.$id_article );
		if ( $query->have_posts() ) : while ( $query->have_posts() ) : $query->the_post();
		$permalien = get_permalink();
		endwhile;
		endif;
		wp_reset_postdata();
		if( $permalien == "" )
		{
			$query = new WP_Query( 'page_id='.$id_article );
			if ( $query->have_posts() ) : while ( $query->have_posts() ) : $query->the_post();
			$permalien = get_permalink();
			endwhile;
			endif;
			if( $permalien == "" )
			{
					// articles personnalisés:
				$types = $this->chapitres_DB_renvoie_array_types();
				$args = array(
				'post_type' => $types,
				'post_status' => 'publish',
				'p' => $id_article
				);
				$query = new WP_Query( $args );
				if ( $query->have_posts() ) : while ( $query->have_posts() ) : $query->the_post();
				$permalien = get_permalink();
				endwhile;
				endif;
			}
		}
		return $permalien;
	}
	
		/***** Renvoyer tous les types de contenu publiés
			   Return all types of published content *****/
		private function chapitres_DB_renvoie_array_types()
	{
		$types =  array( 'post', 'page' );
		$args = array( 'public'   => true, '_builtin' => false );
		$post_types = get_post_types( $args ); 
		foreach ( $post_types  as $post_type ){ array_push($types,$post_type); }
		return $types;
	}
	
}

?>