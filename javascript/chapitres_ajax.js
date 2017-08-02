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

var obj_styles = new Object(); 
var obj_styles_origine = new Object(); 
var obj_styles_identifiant = new Object(); 
var obj_styles_identifiant_origine = new Object(); 
var obj_styles_autres_identifiants = new Object(); 
var obj_polices = new Object();
var livre_actif;

jQuery(document).ready(function($) 
{
			/******************************************************/
			/***** Gérer la navigation des articles par ajax: *****/
			/******************************************************/
			
		/***** Gérer le changement d'onglet: par nom ou par date *****/
	$('#par_nom,#par_date').live('click',function(event)
	{
		var mon_id = $(this).attr('id');
		var num_page_articles = $('#num_page_articles').val();
		var nb_articles_par_page = $('#nb_articles_par_page').val();
		var order_by;
		$(this).addClass('nav-tab-active');
		if(mon_id == "par_date")
		{
			$('#par_nom').removeClass('nav-tab-active');
			order_by = "date";
		}
		else
		{
			$('#par_date').removeClass('nav-tab-active');
			order_by = "title";
		}
		$('#conteneur_articles td').html('<span class="spinner provisoire"></span>');
		$('.provisoire').show();
		var param = "action=actualise_metabox_articles&num_page="+num_page_articles+"&order_by="+order_by;
		param = param+"&nb_articles_par_page="+nb_articles_par_page;
		$.post(ajax_object.ajax_url, param, function(retour) 
		{
			$('#chapitres_choisir_articles .inside').html(retour);
		});
	});
	
		/***** Gérer le changement de l'input num_page_articles, par saisie ou par navigation: *****/
	$('#num_page_articles').live('keyup',function(event)
	{
		if ($(this).val() != "")
		{
			var num_page_articles = $(this).val();
			var order_by;
			if ( $('#par_nom').hasClass('nav-tab-active') ) order_by = "title";
			else order_by = "date";
			var nb_articles_par_page = $('#nb_articles_par_page').val();
				// tester que la valeur est bien un numéro à l'aide d'une expression régulière:
			var expr_reg = new RegExp("^[0-9]+$");
			var bool = expr_reg.test(num_page_articles);
			if( bool )
			{
					// on peut lancer le post
				var param = "action=actualise_metabox_articles&num_page="+num_page_articles+"&order_by="+order_by;
				param = param+"&nb_articles_par_page="+nb_articles_par_page;
					// remplacer tous les articles existants par un spinner:
				$('#conteneur_articles td').html('<span class="spinner provisoire"></span>');
				$('.provisoire').show();
				$.post(ajax_object.ajax_url, param, function(retour) 
				{
					$('#chapitres_choisir_articles .inside').html(retour);
				});
			}
			else
			{
					// on supprime le dernier caractère saisi:
				num_page_articles = num_page_articles.substring(0,num_page_articles.length-1);
				$('#num_page_articles').val(num_page_articles);
			}
		}
		else
		{
			$('#num_page_articles').val(1);
		}
	});
	
		/***** Gérer le changement de l'input nb_articles_par_page, redirigé vers le gestionnaire de num_page_articles *****/
	$('#nb_articles_par_page').live('keyup',function(event)
	{
		if($('#nb_articles_par_page').val() != "" && $('#nb_articles_par_page').val() != 0)
		{
				// tester que la saisie est bien un nombre entier >= 1:
			var saisie = $(this).val();
			var filtre = /^[0-9]+$/;
			if(filtre.test(saisie))
			{
				// on actualise:
				$('#num_page_articles').val(1).trigger("keyup");
			}
			else
			{
					// on supprime le dernier caractère saisi:
				saisie = saisie.substring(0,saisie.length-1);
				$('#nb_articles_par_page').val(saisie);
			}
		}
		else
		{
			$('#nb_articles_par_page').val(10);
		}
	});
	
		/***** Gérer le clic sur les boutons de navigation des articles *****/
	$('#premiers_articles,#articles_precedents,#articles_suivants,#derniers_articles').live('click',function(event)
	{
		if(!$(this).hasClass('disabled'))
		{
			var num_page_articles = $('#num_page_articles').val();
			var max_de_pages = $('#nb_pages_max').val();
		
			if($(this).attr('id') == 'premiers_articles')
			{
				if(max_de_pages>1)
				{
					$('#num_page_articles').val(1).trigger("keyup");
						// désactiver < et <<
					$(this).addClass('disabled');
					$('#articles_precedents').addClass('disabled');
						// réactiver > et >>
					$('#articles_suivants').removeClass('disabled');
					$('#derniers_articles').removeClass('disabled');
				}
			}
			if($(this).attr('id') == 'articles_precedents')
			{
				num_page_articles--;
				if(num_page_articles > 0)
				$('#num_page_articles').val(num_page_articles).trigger("keyup");
				else 
				{
					$(this).addClass('disabled');
					$('#premiers_articles').addClass('disabled');
				}
			}
			if($(this).attr('id') == 'articles_suivants')
			{
				num_page_articles++;
				if( num_page_articles <=  max_de_pages) 
				{
						// réactiver < et <<:
					$('#premiers_articles').removeClass('disabled');
					$('#articles_precedents').removeClass('disabled');
					$('#num_page_articles').val(num_page_articles).trigger("keyup");
				}
				if( num_page_articles == max_de_pages)
				{
						// désactiver > et >>:
					$(this).addClass('disabled');
					$('#derniers_articles').addClass('disabled');
				}
			}
			if($(this).attr('id') == 'derniers_articles')
			{
				if( num_page_articles != max_de_pages)
				$('#num_page_articles').val(max_de_pages).trigger("keyup");
					// désactiver > et >>
				$('#articles_suivants').addClass('disabled');
				$(this).addClass('disabled');
				if(max_de_pages>1)
				{
						// réactiver < et <<
					$('#premiers_articles').removeClass('disabled');
					$('#articles_precedents').removeClass('disabled');
						
				}
			}
		}
		return false;
	});
	
			/************************************************/
			/***** Gérer l'ajout de chapitres par ajax: *****/
			/************************************************/
		
	$('#btn_ajoute_article_a_livre').live('click',function(event)
	{
			// Quel livre est actif?
		var id = $('#tabs_infos_livre li a.active').attr('id');
		var num_livre = id.substring(17,id.length);
		var nb_chapitres;
		
		if(id != "undefined")
		{
			var ids_articles = new Array();
				// Quels articles sont sélectionnés?
			$('.selectionne_article').each(function(i) 
			{
				if($(this).attr('checked') == 'checked' || $(this).attr('checked') == true)
                {
						// récupérer les ids des articles
					var ident = $(this).attr('id');
					var id_article = ident.substring(20,ident.length);
					ids_articles.push(id_article);
				}
            });
			
			if(ids_articles != '')
			{
					var btn_ajout = $(this);
					$(this).attr("disabled",true);
					$('#spinner_ajout_article').show();
	
						// Préparer le post:
					var num_page_articles = $('#num_page_articles').val();
					var nb_articles_par_page = $('#nb_articles_par_page').val();
					var nb_chapitres_existants = $('#sortable'+num_livre+' li').length;
					var order_by;
					if ( $('#par_nom').hasClass('nav-tab-active') ) order_by = "title";
					else order_by = "date";
					$('#conteneur_articles td').html('<span class="spinner provisoire"></span>');
					$('.provisoire').show();
					var param = "action=ajoute_chapitres_a_livre&num_livre="+num_livre+"&num_page_articles=";
					param = param+num_page_articles+"&nb_articles_par_page="+nb_articles_par_page+"&nb_chapitres="+nb_chapitres_existants;
					param = param + "&order_by="+order_by;
						// tous les nouveaux articles:
					var lng = ids_articles.length;
					for(var i=0;i<lng;i++)
					{
						param = param+"&id_article"+i+"="+ids_articles[i];
					}
					$('#chapitres_onglets_chapitres .inside').css('background-color','#85F186').fadeTo(500,.1);
					$.post(ajax_object.ajax_url, param, function(retour) 
					{
							// récupérer le contenu des metaboxs:
						var metaboxes = retour.split('¤&¤&¤');
						var metabox_articles = metaboxes[0];
						var lignes_chapitres = metaboxes[1];
						var onglets_sommaires = metaboxes[2];
						$('#chapitres_choisir_articles .inside').html(metabox_articles);
						$('#chapitres_onglets_sommaire .inside').html(onglets_sommaires);
							// ajout des nouvelles lignes par javascript, sinon on casse le jQuery UI sortable
						$('#sortable'+num_livre).append(lignes_chapitres);
						$('#spinner_ajout_article').hide();
						$(btn_ajout).attr("disabled",false);
							// cliquer sur le bon onglet
						$('#clic_onglet_livre'+num_livre).click();
						$('#chapitres_onglets_chapitres .inside').css('background-color','#FFFFFF').fadeTo(500,1);
					});
			}		
		}
		return false;
	});
	
	
			/**********************************************************************/
			/***** Gérer la réorganisation de l'ordre des chapitres par ajax: *****/
			/**********************************************************************/
		
		/***** tri des chapitres *****/
	$('.ui-sortable').sortable(
	{
       update: function(event, ui) 
		{
			var ids_articles = new Array();
				// parcourir tous les articles pour récupérer leurs ids:
            $(this).children().each(function(i) 
			{
				var id_article = $(this).find('.hidden_id_chapitre').val();
				ids_articles.push(id_article);
            });			
				// préparer le post
			var param = "action=change_ordre_chapitres";
			var order_by;
			if ( $('#par_nom').hasClass('nav-tab-active') ) order_by = "title";
			else order_by = "date";
			param = param + "&order_by="+order_by;
				//quel livre est actif?
			var id = $('#tabs_infos_livre li a.active').attr('id');
			var num_livre = id.substring(17,id.length);
			param = param + "&num_livre="+num_livre;
			var affiche_infos = $('#affiche_infos_livre_'+num_livre).is(':checked');
			var affiche_titre = false;
			var affiche_resume = false;
			var liste_numerotee = true;
			if($('#option_affiche_titre_'+num_livre).attr('checked') == 'checked' || $('#option_affiche_titre_'+num_livre).attr('checked') == true) affiche_titre = true;
			if($('#option_affiche_resume_'+num_livre).attr('checked') == 'checked' || $('#option_affiche_resume_'+num_livre).attr('checked') == true) affiche_resume = true;
			if($('#option_liste_numerotee_'+num_livre).attr('checked') != 'checked' || $('#option_liste_numerotee_'+num_livre).attr('checked') == true) liste_numerotee = false;
			var limit = $('#option_limite_'+num_livre).val();
			param = param + "&limit="+limit;
			param = param + "&affiche_infos="+affiche_infos;
			param = param + "&affiche_titre="+affiche_titre;
			param = param + "&affiche_resume="+affiche_resume;
			param = param + "&liste_numerotee="+liste_numerotee;
			var lng = ids_articles.length;
			for(var i=0;i<lng;i++) { param = param+'&id_article'+i+'='+ids_articles[i]; }
			$('#chapitres_onglets_chapitres .inside').css('background-color','#85F186').fadeTo(100,.1);
			var lignes_articles = $(this).children();
			$('#apercu_sommaire_'+num_livre).html("<span class='spinner' style='display:block;margin:auto;float:none;width:16px;'></span><br/>");
			$.post(ajax_object.ajax_url, param, function(retour) 
			{
					// actualiser les bons numéros via javascript!
				$(lignes_articles).each(function(i) 
				{
						// juste renommer les titres des articles: 
					id_article = ids_articles[i];
					$('#numero_chapitre_'+id_article).html((i+1)+". ");
				});
					// actualiser l' onglet sommaire correspondant:
				$('#apercu_sommaire_'+num_livre).html(retour);
				$('#chapitres_onglets_chapitres .inside').css('background-color','#FFFFFF').fadeTo(500,1);
			});
        }
    }); 
	
		/***** Supprimer un chapitre *****/
	$('.item-delete').live('click',function(event)
	{
		var id = $(this).attr('id');
		var id_article = id.substring(24,id.length);
			// quel livre est actif?
		var id = $('#tabs_infos_livre li a.active').attr('id');
		var num_livre = id.substring(17,id.length);
			// quelle ligne faut-il enlever?
		var lignes_existantes = $('#sortable'+num_livre+' li');
		var indice = $(this).parent().parent().parent().parent().parent().parent().index();
		$('#sortable'+num_livre).children().eq(indice).remove();
			// préparer le post:
		var ids_articles = new Array();
			// parcourir tous les articles pour récupérer leurs ids:
		$('#sortable'+num_livre).children().each(function(i) 
		{
			var id_article = $(this).find('.hidden_id_chapitre').val();
			ids_articles.push(id_article);
		});
		var num_page_articles = $('#num_page_articles').val();
		var nb_articles_par_page = $('#nb_articles_par_page').val();
		$('#conteneur_articles td').html('<span class="spinner provisoire"></span>');
		$('.provisoire').show();
		var param = "action=change_ordre_chapitres&num_page_articles="+num_page_articles+"&nb_articles_par_page="+nb_articles_par_page;
		param = param+"&id_article_a_supprimer="+id_article;
		param = param+"&num_livre="+num_livre;
		var order_by;
		if ( $('#par_nom').hasClass('nav-tab-active') ) order_by = "title";
		else order_by = "date";
		param = param + "&order_by="+order_by;
		var lng = ids_articles.length;
		for(var i=0;i<lng;i++) { param = param+'&id_article'+i+'='+ids_articles[i]; }
			// paramètres des options:
		var affiche_infos = $('#affiche_infos_livre_'+num_livre).is(':checked');
		var affiche_titre = false;
		var affiche_resume = false;
		var liste_numerotee = true;
		if($('#option_affiche_titre_'+num_livre).attr('checked') == 'checked' || $('#option_affiche_titre_'+num_livre).attr('checked') == true) affiche_titre = true;
		if($('#option_affiche_resume_'+num_livre).attr('checked') == 'checked' || $('#option_affiche_resume_'+num_livre).attr('checked') == true) affiche_resume = true;
		if($('#option_liste_numerotee_'+num_livre).attr('checked') != 'checked' || $('#option_liste_numerotee_'+num_livre).attr('checked') == true) liste_numerotee = false;
		var limit = $('#option_limite_'+num_livre).val();
		param = param + "&limit="+limit;
		param = param + "&affiche_infos="+affiche_infos;
		param = param + "&affiche_titre="+affiche_titre;
		param = param + "&affiche_resume="+affiche_resume;
		param = param + "&liste_numerotee="+liste_numerotee;
			// animation visuelle:
		$('#chapitres_onglets_chapitres .inside').css('background-color','#85F186').fadeTo(100,.1);
		$('#apercu_sommaire_'+num_livre).html("<span class='spinner' style='display:block;margin:auto;float:none;width:16px;'></span><br/>");
		$.post(ajax_object.ajax_url, param, function(retour) 
		{
				// actualiser les bons numéros via javascript
			$('#sortable'+num_livre).children().each(function(i) 
			{
					// juste renommer les titres: 
				id_article = ids_articles[i];
				$('#numero_chapitre_'+id_article).html((i+1)+". ");
			});
			$('#chapitres_onglets_chapitres .inside').css('background-color','#FFFFFF').fadeTo(500,1);
				// réactualiser la liste des articles:
			var onglets = retour.split('¤&¤&¤');
			var articles = onglets[0];
			var sommaire = onglets[1];
			$('#chapitres_choisir_articles .inside').html(articles);
				// changer l'intitulé du bouton:
			var lib_btn = $('#msg_ajoute_article_a_livre').val();
			$('#btn_ajoute_article_a_livre').val(lib_btn+" "+num_livre);
				// réactualiser le sommaire:
			$('#apercu_sommaire_'+num_livre).html(sommaire);
			
		});
		return false;
	});
		
			/*******************************************************************/
			/***** Gérer l'édition du sommaire selon les options par ajax: *****/
			/*******************************************************************/
		
		/***** Remettre les 3 cases à cocher d'options à leurs valeurs par défaut en cas de rafraîchissement de la page: *****/
	$('.option_affiche_titre,.option_affiche_resume,.option_liste_numerotee').each(function(i) 
	{
		if($(this).hasClass('option_affiche_titre') || $(this).hasClass('option_affiche_resume')) 
		{
				// les décocher par défaut:
			if($(this).attr('checked') == 'checked' || $(this).attr('checked') == true) $(this).attr('checked','');
		}
		else
		{
			if($(this).attr('checked') != 'checked' || $(this).attr('checked') == true) $(this).attr('checked','checked');
		}
	});
		
		/***** Rattacher la saisie d'une limite aux autres options *****/
	$('.option_limite').live('keyup',function(event)
	{
		var id = $(this).attr('id');
		var num_livre = id.substring(14,id.length);
			// changer l'état d'une case avant de cliquer dessus:
		case_affiche_titre = $('#option_affiche_titre_'+num_livre);
		if($(case_affiche_titre).attr('checked') == 'checked' || $(case_affiche_titre).attr('checked') == true)
		$(case_affiche_titre).attr('checked','');
		else
		$(case_affiche_titre).attr('checked','checked');
		$(case_affiche_titre).trigger("click");
	});
	
		/***** Rattacher l'option du livre aux autres options *****/
	$('.affiche_sommaire').live('click',function(event)
	{
		var id = $(this).attr('id');
			// récupérer le numéro du livre:
		var num = id.substring(20,id.length);
		case_affiche_titre = $('#option_affiche_titre_'+num);
		case_resume_titre = $('#option_affiche_resume_'+num);
		if($(case_affiche_titre).attr('checked') == 'checked' || $(case_affiche_titre).attr('checked') == true)
		$(case_affiche_titre).attr('checked','');
		else
		$(case_affiche_titre).attr('checked','checked');
		if($(this).is(':checked')) 
		{ $(case_affiche_titre).closest('tr').show(); $(case_resume_titre).closest('tr').show(); }
		else { $(case_affiche_titre).closest('tr').hide(); $(case_resume_titre).closest('tr').hide(); }
		$(case_affiche_titre).trigger('click');
	});
	
		/***** Actualiser les options d'un chapitre: *****/
	$('.option_affiche_titre,.option_affiche_resume,.option_liste_numerotee').live('click',function(event)
	{
			// récupérer l'id par le tableau ancêtre
		var id = $(this).closest('table').attr('id');
		var est_coche = ($(this).attr('checked') == 'checked' || $(this).attr('checked') == true);
		var morceau = '';
		if($(this).hasClass('option_affiche_titre')) morceau = 'titre';
		if($(this).hasClass('option_affiche_resume')) morceau = 'resume';
		if($(this).hasClass('option_liste_numerotee')) morceau = 'numeros';
		$(this).parent().next('td').next('td').html('<code>'+morceau+'='+est_coche+'</code>');
		var num_livre = id.substring(17,id.length);
		var affiche_infos = $('#affiche_infos_livre_'+num_livre).is(':checked');
		var affiche_titre = false;
		var affiche_resume = false;
		var liste_numerotee = true;
		if($('#option_affiche_titre_'+num_livre).attr('checked') == 'checked' || $('#option_affiche_titre_'+num_livre).attr('checked') == true) affiche_titre = true;
		if($('#option_affiche_resume_'+num_livre).attr('checked') == 'checked' || $('#option_affiche_resume_'+num_livre).attr('checked') == true) affiche_resume = true;
		if($('#option_liste_numerotee_'+num_livre).attr('checked') != 'checked' || $('#option_liste_numerotee_'+num_livre).attr('checked') == true) liste_numerotee = false;
		var limit = $('#option_limite_'+num_livre).val();
		
			// reconstituer le jeton complet
		var jeton = "[sommaire-chapitres livre="+num_livre;
		if(limit != "") jeton = jeton+" limit="+limit;
		if(affiche_infos) 
		{
			jeton = jeton+" affiche_infos="+affiche_infos;
			if(affiche_titre) jeton = jeton+" titre="+affiche_titre;
			if(affiche_resume) jeton = jeton+" resume="+affiche_resume;
		}
		if(liste_numerotee) jeton = jeton+" numeros="+liste_numerotee;
		jeton = jeton+"]";
		$('#jeton_sommaire_'+num_livre).html(jeton);
		
		var param = "action=actualise_options_sommaire";
		param = param + "&num_livre="+num_livre;
		param = param + "&limit="+limit;
		param = param + "&affiche_infos="+affiche_infos;
		param = param + "&affiche_titre="+affiche_titre;
		param = param + "&affiche_resume="+affiche_resume;
		param = param + "&liste_numerotee="+liste_numerotee;
		$('#apercu_sommaire_'+num_livre).html("<span class='spinner' style='display:block;margin:auto;float:none;width:16px;'></span><br/>");
		$.post(ajax_object.ajax_url, param, function(retour) 
		{
				// remplir l'aperçu du chapitre
			$('#apercu_sommaire_'+num_livre).html(retour);
		});
		
	});
	
			/*******************************************************************/
			/***** Gérer l'affichage du sommaire pour les styles par ajax: *****/
			/*******************************************************************/
		
	/***** Ouvrir la fenêtre de styles, actualiser tout et afficher le bon sommaire *****/
	$('.image_styles').live('click',function(event)
	{
		var id = $(this).attr('id'); 
		var num_livre = id.substring(7,id.length);
		
		livre_actif = num_livre;
		var affiche_infos = $('#affiche_infos_livre_'+num_livre).is(':checked');
		var affiche_titre = false;
		var affiche_resume = false;
		var liste_numerotee = true;
		if($('#option_affiche_titre_'+num_livre).attr('checked') == 'checked' || $('#option_affiche_titre_'+num_livre).attr('checked') == true) affiche_titre = true;
		if($('#option_affiche_resume_'+num_livre).attr('checked') == 'checked' || $('#option_affiche_resume_'+num_livre).attr('checked') == true) affiche_resume = true;
		if($('#option_liste_numerotee_'+num_livre).attr('checked') != 'checked' || $('#option_liste_numerotee_'+num_livre).attr('checked') == true) liste_numerotee = false;	
		if(liste_numerotee) 
		{
			$('#entete_chapitres_sommaire_livre_ul,#entete_chapitres_sommaire_livre_ul_li').hide();
			$('#entete_chapitres_sommaire_livre_ol,#entete_chapitres_sommaire_livre_ol_li').show();
		}
		else 
		{
			$('#entete_chapitres_sommaire_livre_ul,#entete_chapitres_sommaire_livre_ul_li').show();
			$('#entete_chapitres_sommaire_livre_ol,#entete_chapitres_sommaire_livre_ol_li').hide();
		}
		if( !affiche_infos )
		{
			$('#entete_chapitres_infos_livre,#entete_chapitres_titre_livre,#entete_chapitres_resume_livre').hide();
		}
		else
		{
			$('#entete_chapitres_infos_livre').show();
			if(affiche_titre) $('#entete_chapitres_titre_livre').show();
			if(affiche_resume) $('#entete_chapitres_resume_livre').show();
		}
		$('#customize-preview').html("<div class='chargement'></div>");
		var param = "action=renvoie_json_styles&num_livre="+num_livre;
		$.post(ajax_object.ajax_url, param, function(retour) 
		{
			//$('#customize-preview').html(retour);
			var objets = retour.split('separateur');
				// Récupérer l'objet json:
			var obj = eval('(' + objets[0] + ')');
			obj_styles_origine = eval('(' + objets[0] + ')');
			obj_styles = obj;
			obj_styles_identifiant = eval('(' + objets[1] + ')');
			obj_styles_identifiant_origine = eval('(' + objets[1] + ')');
			obj_styles_autres_identifiants = eval('(' + objets[2] + ')');
			obj_polices = eval('(' + objets[3] + ')');
				
				// Affecter les styles selon la saisie et actualiser l'objet obj_styles
			for(var nom_ancetre in obj) 
			{
				var obj_selecteur_css = eval("obj."+nom_ancetre);
				for(var propcss in obj_selecteur_css) 
				{
						// Actualiser les valeurs des champs de saisie du formulaire de styles en fonction de l'objet json des classes:
					var idchamp = '#'+nom_ancetre+'_'+propcss;
					eval("var valeur_css = obj."+nom_ancetre+"[propcss]");
					$(idchamp).val(valeur_css);
					
						// Activer les sélecteurs de couleur:
					if(propcss.indexOf("color") != -1)
					{
						var options_couleur =  
						{ 
							defaultColor: false, 
							change: function(event, ui)
							{ 
								var couleur = $(this).val(); 
								var id = $(this).attr("id"); // chapitres_sommaire_livre_ul_li_border_left_color
								var reg = new RegExp("_", "g");
								var propriete_css = $(this).attr("propcss");
								var prop_css = propriete_css;
								var a_enlever = id.length - propriete_css.length - 1;
								var propriete_css = propriete_css.replace(reg,"-");
								var champ_a_modifier = id.substring(0,a_enlever);
								var selecteur_css = $('#styles_'+champ_a_modifier).attr("selecteur");
								$('#customize-preview '+selecteur_css).css(propriete_css,couleur);
								selecteur_css = selecteur_css.substring(1);
								var indice = selecteur_css.indexOf(" ");
								var debut = selecteur_css.substring(0,indice); 
								var fin = selecteur_css.substring(indice); 
								var selecteur_css = debut+num_livre+" "+fin;
								$('#customize-preview #'+selecteur_css).css(propriete_css,couleur);
								eval ("obj_styles."+champ_a_modifier+"."+prop_css+" = '"+couleur+"'");
								var reg=new RegExp("livre", "g");
								champ_a_modifier = champ_a_modifier.replace(reg,"livre"+num_livre);
								eval ("obj_styles_identifiant."+champ_a_modifier+"."+prop_css+" = '"+couleur+"'");
							}, 
							clear: function() {}, 
							hide: true, palettes: true 
						};
						var id_color_picker = "#"+nom_ancetre+"_"+propcss;
						$(id_color_picker).wpColorPicker(options_couleur);
					}
							
				}
			}
			
			var nb_chiffres = num_livre.length;
				// Mettre à jour si on possède des infos spécifiques:
			for(var nom_ancetre in obj_styles_identifiant) 
			{
				var obj_selecteur_css = eval("obj_styles_identifiant."+nom_ancetre);
				for(var propcss in obj_selecteur_css) 
				{
						// Actualiser les valeurs des champs de saisie du formulaire de styles en fonction de l'objet json des identifiants:
					var reg = new RegExp("[0-9]", "g");
					var nom_correspondant = nom_ancetre.replace(reg,"");
					var idchamp = '#'+nom_correspondant+'_'+propcss;
					eval("var valeur_css = obj_styles_identifiant."+nom_ancetre+"."+propcss+";");
					$(idchamp).val(valeur_css);
				}
			}

				// remplir l'aperçu du chapitre dans la fenêtre styles
			$('#customize-preview').html(affiche_sommaire_guide(affiche_infos,affiche_titre,affiche_resume,liste_numerotee)+$('#apercu_sommaire_'+num_livre).html());		
			
		});
				// ouvrir la fenêtre de styles:
			$('#fenetre_styles').fadeIn().css({ 'width': 900 , 'height':500});
			var margehaute = ($('#fenetre_styles').height() + 80) / 2;
			var margegauche = ($('#fenetre_styles').width() + 80) / 2;
			$('#fenetre_styles').css({ 'margin-top' : -margehaute, 'margin-left' : -margegauche});
			$('body').append('<div id="fond_opaque_styles" class="fond_opaque"></div>'); 
			$('#fond_opaque_styles').css({'filter' : 'alpha(opacity=80)'}).fadeIn();
	});
		
		/***** Fermer la fenêtre de styles sans enregistrer de modification *****/	
	$('#fermer_styles').live('click',function(event)
	{
		ferme_fenetre_styles();
	});
	
		/***** Activer les champs de saisie de styles de type texte *****/	
	$('.styles-font-size').live('keyup',function(event)
	{
		var valeur = parseInt($(this).val());
		active_champ($(this),valeur);
	});
	
		/***** Activer les champs de saisie de styles de type select: puces de listes, bordures, polices *****/	
	$('.selectionner_puces_listes,.selectionner_styles_bordures,.styles-font-family').live('change',function(event)
	{
		var valeur = $(this).val();
		active_champ($(this),valeur);
	});
	
	function active_champ(champ,valeur)
	{
		if(valeur != "")
		{
			var num_livre = livre_actif;
			var id_ancetre = $(champ).parent().parent().parent().parent().parent();
			var selecteur_css = $(id_ancetre).attr('selecteur');
			var prop_css = $(champ).attr('propcss');
			var reg = new RegExp("_", "g");
			var cle_css = prop_css.replace(reg,"-");
			id_ancetre = $(id_ancetre).attr('id');
			id_ancetre = id_ancetre.substring(7); 
			$('#customize-preview '+selecteur_css).css(cle_css,valeur);
			selecteur_css = selecteur_css.substring(1);
			var indice = selecteur_css.indexOf(" ");
			var debut = selecteur_css.substring(0,indice); 
			var fin = selecteur_css.substring(indice); 
			var selecteur_css = debut+num_livre+" "+fin;
			$('#customize-preview #'+selecteur_css).css(cle_css,valeur);
			if (typeof valeur == "string") 
			{
				eval ("obj_styles."+id_ancetre+"."+prop_css+" = '"+valeur+"';");
				reg=new RegExp("livre", "g");
				id_ancetre = id_ancetre.replace(reg,"livre"+num_livre);
				reg=new RegExp("chapitres_conteneur_sommaire", "g");
				id_ancetre = id_ancetre.replace(reg,"chapitres_conteneur_sommaire"+num_livre);
				eval ("obj_styles_identifiant."+id_ancetre+"."+prop_css+" = '"+valeur+"';");
			}
			else 
			{
				eval ("obj_styles."+id_ancetre+"."+prop_css+" = "+valeur+";");
				reg=new RegExp("livre", "g");
				id_ancetre = id_ancetre.replace(reg,"livre"+num_livre);
				reg=new RegExp("chapitres_conteneur_sommaire", "g");
				id_ancetre = id_ancetre.replace(reg,"chapitres_conteneur_sommaire"+num_livre);
				eval ("obj_styles_identifiant."+id_ancetre+"."+prop_css+" = "+valeur+";");
			}
		}
	}
	
	function affiche_sommaire_guide(affiche_infos,affiche_titre,affiche_resume,liste_numerotee)
	{
		var nom_conteneur_global = $('#entete_chapitres_conteneur_sommaire strong').html();
		var nom_entete = $('#entete_chapitres_infos_livre strong').html();
		var nom_titre = $('#entete_chapitres_titre_livre strong').html();
		var nom_resume = $('#entete_chapitres_resume_livre strong').html();
		var nom_conteneur = $('#entete_chapitres_sommaire_livre strong').html();
		var nom_liste_numerotee = $('#entete_chapitres_sommaire_livre_ol strong').html();
		var nom_liste = $('#entete_chapitres_sommaire_livre_ul strong').html();
		var nom_ligne = $('#entete_chapitres_sommaire_livre_ol_li strong').html();
		var nom_lien = $('#entete_chapitres_sommaire_livre_a strong').html();
		
		var contenu = "<div id='conteneur_schema'>";
		contenu = contenu +"<h1 id='titre_schema'>css</h1>";
		contenu = contenu +"<div id='chapitres_conteneur_sommaire' class='maquette'>"+nom_conteneur_global;
		
		if(affiche_infos) contenu = contenu + "<div id='chapitres_infos_livre' class='maquette'>"+nom_entete;
		if(affiche_titre) contenu = contenu + "<div id='chapitres_titre_livre' class='maquette'>"+nom_titre+"</div>";
		if(affiche_resume) contenu = contenu + "<div id='chapitres_resume_livre' class='maquette'>"+nom_resume+"</div>";
		if(affiche_infos) contenu = contenu + "</div>";
		contenu = contenu + "<div id='chapitres_sommaire_livre' class='maquette'>"+nom_conteneur;
		if(liste_numerotee) 
		{ 	
			contenu = contenu + "<div id='chapitres_sommaire_livre_ol' class='maquette'>"+nom_liste_numerotee;
			contenu = contenu + "<div id='chapitres_sommaire_livre_ol_li' class='maquette'>"+nom_ligne;
		}
		else 
		{
			contenu = contenu + "<div id='chapitres_sommaire_livre_ul' class='maquette'>"+nom_liste;
			contenu = contenu + "<div id='chapitres_sommaire_livre_ul_li' class='maquette'>"+nom_ligne;
		}
		contenu = contenu + "<div id='chapitres_sommaire_livre_a' class='maquette'>"+nom_lien;
		contenu = contenu + "</div></div></div>";
		contenu = contenu + "</div></div></div>";
		return contenu;
	}
	
		/***** Activer l'enregistrement des styles saisis *****/	
	$('#sauver_styles').live('click',function(event)
	{
			// actualiser l'objet polices:
		$('.styles-font-family').each(function(i) 
		{
			var police = $(this).val();
			reg=new RegExp(" ", "g");
			police = police.replace(reg,"_");
			var google_police = $(this).children('option:selected').attr('police');
			if( typeof(google_police) != 'undefined')
			{		
				var reg = new RegExp("[\+]", "g");
				google_police = google_police.replace(reg," ");
				eval("obj_polices."+police+"='"+google_police+"'");
			}
		});
	
			// passer les paramètres
		var id_choisis = $('#id_ou_classe').is(':checked');
		var param = "action=ecrit_styles"+"&id_choisis="+id_choisis+"&num_livre="+livre_actif;
		if(!id_choisis)
		{ 
			param = param +"&styles="+JSON.stringify(obj_styles_origine)+"&styles_identifiant="+JSON.stringify(obj_styles_identifiant); 
		}
		else
		{ param = param +"&styles="+JSON.stringify(obj_styles)+"&styles_identifiant="+JSON.stringify(obj_styles_identifiant_origine);}
		param = param +"&autres_styles="+JSON.stringify(obj_styles_autres_identifiants)+"&polices="+JSON.stringify(obj_polices);
		$('#customize-preview').html("<div class='chargement'></div>");
		$.post(ajax_object.ajax_url, param, function(retour) 
		{
			$('#customize-controls').submit();		
		});
	});
	
		/***** Activer les entêtes qui plient/déplient les options de styles *****/	
	$('.plus_moins').live('click',function(event)
	{
		if($(this).html() == "+") $(this).html("-"); else $(this).html("+");
		var id = $(this).parent().parent().attr("id");
		var nom_selecteur = id.substring(7);
		$('#styles_'+nom_selecteur).toggle();
	});
	
		/***** Changer le style de la maquette selon le survol des entêtes *****/	
	$( ".plus_moins" ).hover(
		function() 
		{
			var id = $(this).parent().parent().attr("id");
			var nom_selecteur = id.substring(7);
			$('#customize-preview #'+nom_selecteur).css({"border-color":"red", "background-color":"#808080", "color":"white", "border-style":"solid"});
		}, 
		function() 
		{
			var id = $(this).parent().parent().attr("id");
			var nom_selecteur = id.substring(7);
			$('#customize-preview #'+nom_selecteur).css({"border-color":"#000000", "background-color":"white", "color":"black", "border-style":"dotted"});
		}
		);
	
});

function ferme_fenetre_styles()
{
	jQuery(document).ready( function($) 
	{
		$("#fenetre_styles").fadeOut();
		$("#fond_opaque_styles").remove();
	});
}
