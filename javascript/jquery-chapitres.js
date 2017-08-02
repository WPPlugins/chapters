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

window.console;
console.warn;
	// lorsque tout est chargé:
jQuery(document).ready( function($) 
{
	if($('#message').html() != "") $('#message').fadeIn(2000).fadeOut(2000);	
	
		/****************************************/
		/*             ACTIVATION               */
		/****************************************/	
	
		/***** gestion globale des metaboxes *****/
	$('.if-js-closed').removeClass('if-js-closed').addClass('closed');			
	postboxes.add_postbox_toggles('settings_page_chapitres');
	
		/***** activation globale de tous les onglets: *****/
	$('.clic_onglet_livre').each(function(i) 
	{
		var id = $(this).attr('id');
		var num_onglet = id.substring(17,id.length);
		if ( !$(this).hasClass('active')) 
		{ 
			$('#onglet_livre'+num_onglet).hide(); 
		}
	});
	
	$('.clic_onglet_chapitre').each(function(i) 
	{
		var id = $(this).attr('id');
		var num_onglet = id.substring(20,id.length);
		if ( !$(this).hasClass('active')) 
		{ 
			$('#onglet_chapitre'+num_onglet).hide(); 
		}
	});
	
	$('.clic_onglet_sommaire').each(function(i) 
	{
		var id = $(this).attr('id');
		var num_onglet = id.substring(20,id.length);
		if ( !$(this).hasClass('active')) 
		{ 
			$('#onglet_sommaire'+num_onglet).hide(); 
		}
	});
	
		/****************************************/
		/*               ONGLETS                */
		/****************************************/
		
		/***** Ajouter un nouvel onglet de livre, et en même temps dans les metabox chapitre et sommaire *****/
	$('#ajouter_un_livre').live('click',function(event)
	{
			// récupérer le dernier numéro d'onglet affiché:
		var dernier_onglet = $('.onglet_livre').last();
		var nb_onglets = $('.onglet_livre').length;
		
			// si cet onglet n'est pas enregistré, afficher un message
		if($(dernier_onglet).hasClass('onglet_livre_javascript'))
		{
				// ouvrir la fenêtre modale d'avertissement:
			$('#fenetre_modale').fadeIn().css({ 'width': 500 });
			var margehaute = ($('#fenetre_modale').height() + 80) / 2;
			var margegauche = ($('#fenetre_modale').width() + 80) / 2;
			$('#fenetre_modale').css({ 'margin-top' : -margehaute, 'margin-left' : -margegauche});
			$('body').append('<div id="fond_opaque_modale" class="fond_opaque"></div>'); 
			$('#fond_opaque_modale').css({'filter' : 'alpha(opacity=80)'}).fadeIn();
		}
		else
		{
			if(nb_onglets == 0) 
			{num = 1;}
			else
			{
				var id = $('.affiche_sommaire').last().attr('id');
				var num = id.substring(20,id.length);
				num ++;
			}
			ajoute_onglet(num);
			$('.clic_onglet_livre').last().click();
		}
		return false;
	});
				
	
		/***** clic sur un onglet de la metabox livre: *****/
	$('.clic_onglet_livre').live('click',function(event)
		{
			var id = $(this).attr('id');
			var num_onglet;
				// récupérer l'id, selon si c'est un onglet javascript ou non:
			if((id.indexOf("javascript") == -1)) num_onglet = id.substring(17,id.length);
			else num_onglet = id.substring(28,id.length);
				// cacher tous les contenus de tous les onglets de la page
			$('.onglet_livre,.onglet_chapitre,.onglet_sommaire').hide();
				// rendre tous les onglets de la page inactifs
			$('#tabs_infos_livre,#tabs_infos_chapitre,#tabs_infos_sommaire').find('li a.active').removeClass('active');
				// afficher le contenu sélectionné
			$('#onglet_livre'+num_onglet+',#onglet_chapitre'+num_onglet+',#onglet_sommaire'+num_onglet).show();
			$('#clic_onglet_livre'+num_onglet+',#clic_onglet_chapitre'+num_onglet+',#clic_onglet_sommaire'+num_onglet).addClass('active');
				// s'adapter à la traduction de l'intitulé du bouton:
			var libelle = $('#msg_ajoute_article_a_livre').val();
			$('#btn_ajoute_article_a_livre').val(libelle+" "+num_onglet);
		});
		
		/***** Clic sur un onglet des metabox 'Chapitres' ou 'Sommaires'
			   Click on a tab in 'Chapters' or 'Summaries' metaboxes *****/
	$('.clic_onglet_chapitre,.clic_onglet_sommaire').live('click',function(event)
		{
			var id = $(this).attr('id');
			var num_onglet = id.substring(20,id.length);
				// reconduire vers clic sur onglet de la metabox livre
			$('#clic_onglet_livre_javascript'+num_onglet).click();
			$('#clic_onglet_livre'+num_onglet).click();
		});
	
		/***** Ajout d'un onglet dans la metabox 'Livres' 
			   Add tab in 'Books' metabox *****/
	function ajoute_onglet(num)
	{
			// récupération des messages hidden traduits:
		var lib_pas_encore = $('#trad_pas_encore').val();	
		var lib_titre = $('#trad_titre_livre').val();
		var lib_resume = $('#trad_resume_livre').val();	
		var lib_affiche_livre = $('#trad_affiche_livre').val();	
		var lib_supprimer = $('#trad_supprimer').val();
		var lib_livre = $('#trad_livre').val();
			// préparation du contenu HTML:
		var contenu_onglet = "";
		contenu_onglet = contenu_onglet+ "<div class='onglet_livre onglet_livre_javascript' id='onglet_livre"+num+"'>"; 
		contenu_onglet = contenu_onglet+ "<div class='tab-content'>";
		contenu_onglet = contenu_onglet+ "<div class='form-invalid' style='text-align:center;'><i>"+lib_pas_encore+"</i></div>";
		contenu_onglet = contenu_onglet+ "<fieldset style='border:2px solid #E1E1E1;margin:5px;'><table class='form-table'>";
			// titre du livre:
		contenu_onglet = contenu_onglet+ "<tr><th scope='row'><label for='titre_livre_"+num+"'>"+lib_titre+":</label></th>";
		contenu_onglet = contenu_onglet+ "<td><input type='text' class='large-text' name='titre_livre_"+num+"'  id='titre_livre_"+num+"' value='Livre "+num+"'></td>";
		contenu_onglet = contenu_onglet+ "</tr>";
			// résumé du livre:
		contenu_onglet = contenu_onglet+ "<tr><th scope='row'><label for='resume_livre_$num'>"+lib_resume+":</label></th>";
		contenu_onglet = contenu_onglet+ "<td><textarea class='large-text' name='resume_livre_"+num+"'  id='resume_livre_"+num+"' >"+lib_resume+" "+num+"</textarea></td>";
		contenu_onglet = contenu_onglet+ "</tr>";
			// case à cocher: afficher ou non les infos:
		contenu_onglet = contenu_onglet+ "<tr><th scope='row'><label for='affiche_infos_livre_"+num+"'>"+lib_affiche_livre+"</label></th>";
		contenu_onglet = contenu_onglet+ "<td><input type='checkbox' class='affiche_sommaire' name='affiche_infos_livre_"+num+"'  id='affiche_infos_livre_"+num+"' checked='checked'></td>";
		contenu_onglet = contenu_onglet+ "</tr>";
			// suppression:
		contenu_onglet = contenu_onglet+ "<tr><td colspan='2'>";
		var message_suppression = $('#trad_supprimer').val();
		contenu_onglet = contenu_onglet+ "<a name='supprimer_onglet' class='supprime_onglet' id='supprime_onglet"+num+"'>"+message_suppression+"</a>";
		contenu_onglet = contenu_onglet+ "</td></tr>";
		contenu_onglet = contenu_onglet+ "</table></fieldset>";
		contenu_onglet = contenu_onglet+ "</div></div>";
			// ajouter l'onglet:
		if(num != 1)
		$('.onglet_livre').last().after(contenu_onglet);
		else
		$('#tabs_infos_livre').after(contenu_onglet);
		
		var titre_onglet = "";
		titre_onglet = titre_onglet + "<li class='tab'>";
		titre_onglet = titre_onglet + "<a class='clic_onglet_livre' id='clic_onglet_livre_javascript"+num+"'";
		titre_onglet = titre_onglet + "href='javascript:void(null);'>";
		titre_onglet = titre_onglet + lib_livre + " "+num+"</a></li>";
		$('#tabs_infos_livre').append(titre_onglet);
	}
	
	/******* SELECTION / DESELECTION DES ARTICLES *******/
	
		/***** Sélectionner/déselectionner tous les articles *****/
	$('#selectionne_tous_articles_haut,#selectionne_tous_articles_bas,#select_deselect_tous_articles').live('click',function(event)
	{
		if(this.checked) $('#selectionne_tous_articles_haut,#selectionne_tous_articles_bas,.selectionne_article').attr('checked','checked');
		else $('#selectionne_tous_articles_haut,#selectionne_tous_articles_bas,.selectionne_article').removeAttr('checked');	
	});
	
	
		/****************************************/
		/*               LIVRES                 */
		/****************************************/
		
		/***** Supprimer un onglet créé par javascript *****/
	$('.supprime_onglet').live('click',function(event)
		{
			var id = $(this).attr('id');
			var num = id.substring(15,id.length);
			$('#onglet_livre'+num+',#onglet_chapitre'+num+',#onglet_sommaire'+num).remove();
			$('#clic_onglet_livre_javascript'+num+',#clic_onglet_chapitre'+num+',#clic_onglet_sommaire'+num).remove();
			$('.clic_onglet_livre,.clic_onglet_chapitre,.clic_onglet_sommaire').last().click();
		});	
		
		/****************************************/
		/*               CHAPITRES              */
		/****************************************/	
	
		/***** Animer la bascule d'un chapitre *****/
	$('.triable .handlediv').live('click',function(event)
	{
		var id = $(this).attr('id'); // poignee_livre_1_chapitre_1
		var ref_chapitre = id.substring(7,id.length);
		var id_cible = "contenu"+ref_chapitre;
		if( $('#'+id_cible).css('display') == 'none' )
		$('#'+id_cible).show();
		else $('#'+id_cible).hide();
	});
	
		/***** Marqueur pour signaler que le formulaire a été soumis pour suppression *****/
	$('.supprime_livre').live('click',function(event)
	{
		$('#supprime_livre_1').attr('suppression','yes');
	});

		/*************************************/
		/*               STYLES              */
		/*************************************/	
	
			/***** S'occuper des champs select *****/	
		add_control_label_spans();
		function add_control_label_spans() 
		{
				// Long dash, not hyphen
			var delimeter = '::';

			$( 'span.customize-control-title:contains(' + delimeter + ')' ).each( function(){
				var html, parts;

				html = $(this).html();
				parts = html.split( delimeter );

				if ( 2 == parts.length ) {
					html = parts[0] + '<span class="styles-type">' + parts[1] + '</span>';
					$(this).html( html );
				}

			});
		}
		populate_google_fonts();
		function populate_google_fonts() 
		{
			var google_families = { 'Abel': 'Abel', 'Aclonica': 'Aclonica', 'Actor': 'Actor', 'Allan': 'Allan:bold', 'Allerta': 'Allerta', 'Allerta Stencil': 'Allerta+Stencil', 'Amaranth': 'Amaranth:700,400,italic700,italic400', 'Andika': 'Andika', 'Angkor': 'Angkor', 'Annie Use Your Telescope': 'Annie+Use+Your+Telescope', 'Anonymous Pro': 'Anonymous+Pro:bold,italicbold,normal,italic', 'Anton': 'Anton', 'Architects Daughter': 'Architects+Daughter', 'Arimo': 'Arimo:italicbold,bold,normal,italic', 'Artifika': 'Artifika', 'Arvo': 'Arvo:italic,bold,italicbold,normal', 'Asset': 'Asset', 'Astloch': 'Astloch:normal,bold', 'Aubrey': 'Aubrey', 'Bangers': 'Bangers', 'Battambang': 'Battambang:bold,normal', 'Bayon': 'Bayon', 'Bentham': 'Bentham', 'Bevan': 'Bevan', 'Bigshot One': 'Bigshot+One', 'Black Ops One': 'Black+Ops+One', 'Bokor': 'Bokor', 'Bowlby One': 'Bowlby+One', 'Bowlby One SC': 'Bowlby+One+SC', 'Brawler': 'Brawler', 'Buda': 'Buda:300', 'Cabin': 'Cabin:italic600,500,italicbold,italic500,italic400,400,600,bold', 'Cabin Sketch': 'Cabin+Sketch:bold', 'Calligraffitti': 'Calligraffitti', 'Candal': 'Candal', 'Cantarell': 'Cantarell:italic,bold,italicbold,normal', 'Cardo': 'Cardo', 'Carme': 'Carme', 'Carter One': 'Carter+One', 'Caudex': 'Caudex:italic,italic700,400,700', 'Cedarville Cursive': 'Cedarville+Cursive', 'Chenla': 'Chenla', 'Cherry Cream Soda': 'Cherry+Cream+Soda', 'Chewy': 'Chewy', 'Coda': 'Coda:800', 'Coda Caption': 'Coda+Caption:800', 'Coming Soon': 'Coming+Soon', 'Content': 'Content:bold,normal', 'Copse': 'Copse', 'Corben': 'Corben:700', 'Comfortaa': 'Comfortaa', 'Cousine': 'Cousine:italic,normal,italicbold,bold', 'Covered By Your Grace': 'Covered+By+Your+Grace', 'Crafty Girls': 'Crafty+Girls', 'Crimson Text': 'Crimson+Text:700,italic400,400,italic600,italic700,600', 'Crushed': 'Crushed', 'Cuprum': 'Cuprum', 'Damion': 'Damion', 'Dancing Script': 'Dancing+Script:bold,normal', 'Dangrek': 'Dangrek', 'Dawning of a New Day': 'Dawning+of+a+New+Day', 'Delius': 'Delius:400', 'Delius Swash Caps': 'Delius+Swash+Caps:400', 'Delius Unicase': 'Delius+Unicase:400', 'Didact Gothic': 'Didact+Gothic', 'Droid Arabic Kufi': 'Droid+Arabic+Kufi:bold,normal', 'Droid Arabic Naskh': 'Droid+Arabic+Naskh:normal,bold', 'Droid Sans': 'Droid+Sans:bold,normal', 'Droid Sans Mono': 'Droid+Sans+Mono', 'Droid Sans Thai': 'Droid+Sans+Thai:bold,normal', 'Droid Serif': 'Droid+Serif:bold,normal,italicbold,italic', 'Droid Serif Thai': 'Droid+Serif+Thai:bold,normal', 'EB Garamond': 'EB+Garamond', 'Expletus Sans': 'Expletus+Sans:500,italic600,600,italic400,italic700,700,400,italic500', 'Federo': 'Federo', 'Fontdiner Swanky': 'Fontdiner+Swanky', 'Forum': 'Forum', 'Francois One': 'Francois+One', 'Freehand': 'Freehand', 'GFS Didot': 'GFS+Didot', 'GFS Neohellenic': 'GFS+Neohellenic:italic,italicbold,normal,bold', 'Gentium Basic': 'Gentium+Basic:italicbold,bold,normal,italic', 'Geo': 'Geo:normal,oblique', 'Geostar': 'Geostar', 'Geostar Fill': 'Geostar+Fill', 'Give You Glory': 'Give+You+Glory', 'Gloria Hallelujah': 'Gloria+Hallelujah', 'Goblin One': 'Goblin+One', 'Goudy Bookletter 1911': 'Goudy+Bookletter+1911', 'Gravitas One': 'Gravitas+One', 'Gruppo': 'Gruppo', 'Hammersmith One': 'Hammersmith+One', 'Hanuman': 'Hanuman:normal,bold', 'Holtwood One SC': 'Holtwood+One+SC', 'Homemade Apple': 'Homemade+Apple', 'IM Fell DW Pica': 'IM+Fell+DW+Pica:italic,normal', 'IM Fell DW Pica SC': 'IM+Fell+DW+Pica+SC', 'IM Fell Double Pica': 'IM+Fell+Double+Pica:normal,italic', 'IM Fell Double Pica SC': 'IM+Fell+Double+Pica+SC', 'IM Fell English': 'IM+Fell+English:italic,normal', 'IM Fell English SC': 'IM+Fell+English+SC', 'IM Fell French Canon': 'IM+Fell+French+Canon:italic,normal', 'IM Fell French Canon SC': 'IM+Fell+French+Canon+SC', 'IM Fell Great Primer': 'IM+Fell+Great+Primer:italic,normal', 'IM Fell Great Primer SC': 'IM+Fell+Great+Primer+SC', 'Inconsolata': 'Inconsolata', 'Indie Flower': 'Indie+Flower', 'Irish Grover': 'Irish+Grover', 'Irish Growler': 'Irish+Growler', 'Istok Web': 'Istok+Web:italic700,400,700,italic400', 'Josefin Sans': 'Josefin+Sans:italic600,italic100,600,italic400,700,italic700,100,italic300,400,300', 'Josefin Sans Std Light': 'Josefin+Sans+Std+Light', 'Josefin Slab': 'Josefin+Slab:100,italic600,700,italic400,600,italic100,italic300,300,400,italic700', 'Judson': 'Judson:700,italic400,400', 'Jura': 'Jura:400,500,600,300', 'Just Another Hand': 'Just+Another+Hand', 'Just Me Again Down Here': 'Just+Me+Again+Down+Here', 'Kameron': 'Kameron:400,700', 'Kelly Slab': 'Kelly+Slab', 'Kenia': 'Kenia', 'Khmer': 'Khmer', 'Koulen': 'Koulen', 'Kranky': 'Kranky', 'Kreon': 'Kreon:700,400,300', 'Kristi': 'Kristi', 'La Belle Aurore': 'La+Belle+Aurore', 'Lato': 'Lato:italic300,300,900,700,italic100,100,italic700,400,italic900,italic400', 'League Script': 'League+Script:400', 'Leckerli One': 'Leckerli+One', 'Lekton': 'Lekton:italic,400,700', 'Limelight': 'Limelight', 'Lobster': 'Lobster', 'Lobster Two': 'Lobster+Two:italic400,700,400,italic700', 'Lora': 'Lora:italic,normal,bold,italicbold', 'Love Ya Like A Sister': 'Love+Ya+Like+A+Sister', 'Loved by the King': 'Loved+by+the+King', 'Luckiest Guy': 'Luckiest+Guy', 'Maiden Orange': 'Maiden+Orange', 'Mako': 'Mako', 'Marvel': 'Marvel:400,700,italic700,italic400', 'Maven Pro': 'Maven+Pro:700,900,500,400', 'Meddon': 'Meddon', 'MedievalSharp': 'MedievalSharp', 'Megrim': 'Megrim', 'Merriweather': 'Merriweather:700,900,400,300', 'Metal': 'Metal', 'Metrophobic': 'Metrophobic', 'Miama': 'Miama', 'Michroma': 'Michroma', 'Miltonian': 'Miltonian', 'Miltonian Tattoo': 'Miltonian+Tattoo', 'Modern Antiqua': 'Modern+Antiqua', 'Molengo': 'Molengo', 'Monofett': 'Monofett', 'Moul': 'Moul', 'Moulpali': 'Moulpali', 'Mountains of Christmas': 'Mountains+of+Christmas', 'Muli': 'Muli:italic400,400,italic300,300', 'Nanum Brush Script': 'Nanum+Brush+Script', 'Nanum Gothic': 'Nanum+Gothic:800,700,normal', 'Nanum Gothic Coding': 'Nanum+Gothic+Coding:normal,700', 'Nanum Myeongjo': 'Nanum+Myeongjo:700,normal,800', 'Nanum Pen Script': 'Nanum+Pen+Script', 'Neucha': 'Neucha', 'Neuton': 'Neuton:italic,normal', 'Neuton Cursive': 'Neuton+Cursive', 'News Cycle': 'News+Cycle', 'Nixie One': 'Nixie+One', 'Nobile': 'Nobile:700,italic500,400,italic700,500,italic400', 'Nothing You Could Do': 'Nothing+You+Could+Do', 'Nova Cut': 'Nova+Cut', 'Nova Flat': 'Nova+Flat', 'Nova Mono': 'Nova+Mono', 'Nova Oval': 'Nova+Oval', 'Nova Round': 'Nova+Round', 'Nova Script': 'Nova+Script', 'Nova Slim': 'Nova+Slim', 'Nova Square': 'Nova+Square', 'Nunito': 'Nunito:700,300,400', 'OFL Sorts Mill Goudy TT': 'OFL+Sorts+Mill+Goudy+TT:italic,normal', 'OFL Sorts Mill Goudy TT': 'OFL+Sorts+Mill+Goudy+TT:italic,normal', 'Odor Mean Chey': 'Odor+Mean+Chey', 'Old Standard TT': 'Old+Standard+TT:italic,bold,normal', 'Open Sans': 'Open+Sans:italic300,italic800,600,300,italic400,italic600,italic700,700,800,400', 'Open Sans Condensed': 'Open+Sans+Condensed:italic300,300', 'Orbitron': 'Orbitron:500,900,400,700', 'Oswald': 'Oswald', 'Over the Rainbow': 'Over+the+Rainbow', 'Ovo': 'Ovo', 'PT Sans': 'PT+Sans:italic,bold,normal,italicbold', 'PT Sans Caption': 'PT+Sans+Caption:normal,bold', 'PT Sans Narrow': 'PT+Sans+Narrow:normal,bold', 'PT Serif': 'PT+Serif:italic,normal,bold,italicbold', 'PT Serif Caption': 'PT+Serif+Caption:normal,italic', 'Pacifico': 'Pacifico', 'Patrick Hand': 'Patrick+Hand', 'Paytone One': 'Paytone+One', 'Pecita': 'Pecita', 'Permanent Marker': 'Permanent+Marker', 'Philosopher': 'Philosopher:bold,normal,italic,italicbold', 'Play': 'Play:bold,normal', 'Playfair Display': 'Playfair+Display', 'Podkova': 'Podkova', 'Pompiere': 'Pompiere', 'Preahvihear': 'Preahvihear', 'Puritan': 'Puritan:bold,italic,italicbold,normal', 'Quattrocento': 'Quattrocento', 'Quattrocento Sans': 'Quattrocento+Sans', 'Radley': 'Radley', 'Raleway': 'Raleway:100', 'Rationale': 'Rationale', 'Redressed': 'Redressed', 'Reenie Beanie': 'Reenie+Beanie', 'Rochester': 'Rochester', 'Rock Salt': 'Rock+Salt', 'Rokkitt': 'Rokkitt:700,400', 'Rosario': 'Rosario', 'Ruslan Display': 'Ruslan+Display', 'Schoolbell': 'Schoolbell', 'Shadows Into Light': 'Shadows+Into+Light', 'Shanti': 'Shanti', 'Siamreap': 'Siamreap', 'Siemreap': 'Siemreap', 'Sigmar One': 'Sigmar+One', 'Six Caps': 'Six+Caps', 'Slackey': 'Slackey', 'Smokum': 'Smokum', 'Smythe': 'Smythe', 'Sniglet': 'Sniglet:800', 'Snippet': 'Snippet', 'Special Elite': 'Special+Elite', 'Stardos Stencil': 'Stardos+Stencil:normal,bold', 'Sue Ellen Francisco': 'Sue+Ellen+Francisco', 'Sunshiney': 'Sunshiney', 'Suwannaphum': 'Suwannaphum', 'Swanky and Moo Moo': 'Swanky+and+Moo+Moo', 'Syncopate': 'Syncopate:normal,bold', 'Tangerine': 'Tangerine:normal,bold', 'Taprom': 'Taprom', 'Tenor Sans': 'Tenor+Sans', 'Terminal Dosis Light': 'Terminal+Dosis+Light', 'Thabit': 'Thabit:italic,italicbold,normal,bold', 'The Girl Next Door': 'The+Girl+Next+Door', 'Tienne': 'Tienne:400,900,700', 'Tinos': 'Tinos:italicbold,normal,italic,bold', 'Tulpen One': 'Tulpen+One', 'Ubuntu': 'Ubuntu:bold,300,normal,italicbold,italic,italic500,500,italic300', 'Ultra': 'Ultra', 'UnifrakturCook': 'UnifrakturCook:bold', 'UnifrakturMaguntia': 'UnifrakturMaguntia', 'Unkempt': 'Unkempt', 'Unna': 'Unna', 'VT323': 'VT323', 'Varela': 'Varela', 'Varela Round': 'Varela+Round', 'Vibur': 'Vibur', 'Vollkorn': 'Vollkorn:bold,italic,italicbold,normal', 'Waiting for the Sunrise': 'Waiting+for+the+Sunrise', 'Wallpoet': 'Wallpoet', 'Walter Turncoat': 'Walter+Turncoat', 'Wire One': 'Wire+One', 'Yanone Kaffeesatz': 'Yanone+Kaffeesatz:700,200,400,300', 'Yellowtail': 'Yellowtail', 'Yeseva One': 'Yeseva+One', 'Zeyada': 'Zeyada', /*'jsMath cmbx10': 'jsMath+cmbx10', 'jsMath cmex10': 'jsMath+cmex10', 'jsMath cmmi10': 'jsMath+cmmi10', 'jsMath cmr10': 'jsMath+cmr10', 'jsMath cmsy10': 'jsMath+cmsy10', 'jsMath cmti10': 'jsMath+cmti10',*/ };
			var google_options;

			$.each( google_families, function( name, value )
			{
				google_options += "<option value='" + name + "' police='"+value+"'>" + name + "</option>";
			});

			$( 'select.styles-font-family' ).append( google_options ).each( function(){
				var selected = $(this).data('selected');
				$(this).find( 'option[value="' + selected + '"]' ).attr('selected', 'selected');
			} );
		}
	
});

function ferme_fenetre_modale()
{
	jQuery(document).ready( function($) 
	{
		$("#fenetre_modale").fadeOut();
		$("#fond_opaque_modale").remove();
	});
}
	