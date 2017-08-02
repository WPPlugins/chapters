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
	
class chapitres_customize
{
	
	private $licence = "/*
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
 
*/";
	
			/***** CONSTRUCTEUR / CONSTRUCTOR *****/
		function chapitres_customize() 
	{
	}
		/***** Renvoyer un objet json contenant tous les styles
			   Return json object containing all styles *****/
	public function chapitres_customize_renvoie_styles($num_livre)
	{
		$fichier_css = PLUGIN_DIR."/styles/chapitres-front.css";//plugins_url( "styles/chapitres-front.css", dirname(__FILE__) );
		$contenu = file_get_contents($fichier_css);
			// enlever les sauts de ligne et les tabulations
		$contenu = str_replace(chr(13),"",$contenu);  // retour à la ligne
		$contenu = str_replace(chr(10),"",$contenu);  // backspace
		$contenu = str_replace(chr(9),"",$contenu);  // tabulation
			// enlever le commentaire de licence:
		$debut_licence = strripos($contenu, "/*");
		$fin_licence = strripos($contenu, "*/");
		$contenu = substr($contenu,$fin_licence+2);
			// récupérer les éventuelles polices google
		$noms_polices = "";
		$tab_polices = array();
		preg_match_all("/^(@import).*';/",$contenu,$polices);
		$appels_polices = $polices[0][0];
		$indice_depart = strripos($contenu, $appels_polices);
		$indice_fin = $indice_depart + strlen($appels_polices);
		$debut_contenu = substr($contenu,0,$indice_depart);
		$fin_contenu = substr($contenu,$indice_fin);
		$contenu = $debut_contenu.$fin_contenu;
		$polices = explode(";",$polices[0][0]);
		$lng = sizeof($polices)-1;
		for($i=0;$i<$lng;$i++)
		{
			$appel_police = $polices[$i];
			$police = substr($appel_police,43);
			$lng_police = strlen($police)-1;
			$police = substr($police,0,$lng_police); 
			$morceaux = explode(":",$police);
			if(isset($morceaux[0]))
			{
				$police_courte = $morceaux[0];
			}
			else
			{
				$police_courte = $police;
			}
			$police_courte = str_replace("+"," ",$police_courte);
				// vérifier que cette police est bel et bien utilisée dans les styles:
			if(strpos($contenu,$police_courte))
			{
				$police_courte = str_replace(" ","_",$police_courte);
				$police = str_replace("+"," ",$police);
				$tab_polices["$police_courte"] = $police;
				$noms_polices .= "&".$police;
			}
		}
			//obtenir le contenu des accolades:
		$debuts_accolades = $this->renvoyer_indices($contenu, "{");
		$fins_accolades = $this->renvoyer_indices($contenu, "}");
		$lng = sizeof($debuts_accolades);
		$entetes_css = array();
		$chaine_styles_css = "\$styles_css = array(";
		$copie_chaine_styles_css = "\$styles_css_identifies = array(";
		for($i=0;$i<$lng;$i++)
		{
				// chercher les entêtes des règles CSS:
			if($i==0) $entete_accolade = trim(substr($contenu,0,$debuts_accolades[$i]));
			else 
			{
					$intervalle = $debuts_accolades[$i] - $fins_accolades[$i-1]-1;
					$entete_accolade = trim(substr($contenu,$fins_accolades[$i-1]+1,$intervalle));
			}
			$entete_accolade = substr($entete_accolade,1);
			$remplacement = array(" " => "_",":" => "_");
			$entete_accolade = strtr($entete_accolade,$remplacement);
			array_push($entetes_css,$entete_accolade);
			$chaine_styles_css .= " '$entete_accolade' => array(";
			$remplacement = array("livre" => "livre".$num_livre);
			$entete_accolade = strtr($entete_accolade,$remplacement);
			$remplacement = array("chapitres_conteneur_sommaire" => "chapitres_conteneur_sommaire".$num_livre);
			$entete_accolade = strtr($entete_accolade,$remplacement);
			$copie_chaine_styles_css .= " '$entete_accolade' => array(";
				// chercher les contenus des règles CSS:
			$diff = $fins_accolades[$i]-$debuts_accolades[$i]-1;
			$contenu_accolade = trim(substr($contenu,$debuts_accolades[$i]+1,$diff));
				// décomposer le contenu en sous-contenus:
			$contenu_accolade = explode(";",$contenu_accolade);
			array_pop($contenu_accolade);
			$nb_styles = sizeof($contenu_accolade)-1;
			$j = 0;
			foreach($contenu_accolade as &$valeur)
			{
				$valeur = trim($valeur);
				$valeur = explode(":",$valeur);
				foreach($valeur as &$val)
				{
					$val = trim($val);
				}
					// remplacer les tirets par des underscore:
				$remplacement = array("-" => "_");
				$valeur[0] = strtr($valeur[0],$remplacement);
					// enlever le "px" à la fin des 0px etc. :
				$motif = "/[0-9]+px/";
				if(preg_match($motif,$valeur[1]))
				{
					$lng_valeur = sizeof($valeur[1])-3;
					$valeur[1] = substr($valeur[1],0,$lng_valeur); 
				}
				if($i<$lng-1)
				{
					if($j == $nb_styles) 
					{
						$chaine_styles_css .= " '".$valeur[0]."' => '".$valeur[1]."'), ";
						$copie_chaine_styles_css .= " '".$valeur[0]."' => '".$valeur[1]."'), ";
					}
					else 
					{
						$chaine_styles_css .= " '".$valeur[0]."' => '".$valeur[1]."', ";
						$copie_chaine_styles_css .= " '".$valeur[0]."' => '".$valeur[1]."', ";
					}
				}
				else
				{
					if($j == $nb_styles) 
					{
						$chaine_styles_css .= " '".$valeur[0]."' => '".$valeur[1]."')); ";
						$copie_chaine_styles_css .= " '".$valeur[0]."' => '".$valeur[1]."')); ";
					}
					else 
					{
						$chaine_styles_css .= " '".$valeur[0]."' => '".$valeur[1]."', ";
						$copie_chaine_styles_css .= " '".$valeur[0]."' => '".$valeur[1]."', ";
					}
				}
				$j++;
			}
					
		}
		eval($chaine_styles_css);
		
			// trier les clés qui contiennent des entiers:
		$styles_css_identifies = array();
		$styles_css_autres_identifies = array();
		$cles = array_keys($styles_css);
		$lng = sizeof($cles);
		for($i=0;$i<$lng;$i++)
		{
			if(preg_match("/[0-9]/",$cles[$i]))
			{
					// trier les clés qui contiennent le numéro du livre:
				if(preg_match("/[a-z]".$num_livre."/",$cles[$i]))
				{
					$styles_css_identifies[$cles[$i]] = $styles_css[$cles[$i]];
				}
				else
				{
					$styles_css_autres_identifies[$cles[$i]] = $styles_css[$cles[$i]];
				}
				unset($styles_css[$cles[$i]]);
			}
		}
		if(empty($styles_css)) $styles_css = "{}"; else $styles_css = json_encode($styles_css); 
		if(empty($styles_css_identifies))  eval($copie_chaine_styles_css);
		$styles_css_identifies = json_encode($styles_css_identifies); 
		if(empty($styles_css_autres_identifies)) $styles_css_autres_identifies = "{}"; else $styles_css_autres_identifies = json_encode($styles_css_autres_identifies); 
		if(empty($tab_polices)) $tab_polices = "{}"; else $tab_polices = json_encode($tab_polices); 
		return $styles_css."separateur".$styles_css_identifies."separateur".$styles_css_autres_identifies."separateur".$tab_polices;
	}
	
		/***** Ecrire tous les styles
			   Writing all styles *****/
	public function chapitres_customize_ecrit_styles($num_livre,$styles,$styles_identifiant,$autres_styles,$id_choisis,$polices)
	{
		$contenu_fichier_css = $this->licence.chr(13);
		
		$fichier_css = plugins_url( "styles/chapitres-front.css", dirname(__FILE__) );
				
			// Traitement des polices
		$polices = json_decode(stripslashes($polices), true);
		$cles = array_keys($polices);
		$lng = sizeof($cles);
		for($i=0;$i<$lng;$i++)
		{
			$remplacer = array(" " => "+");
			$polices[$cles[$i]] = strtr($polices[$cles[$i]],$remplacer);
			$contenu_fichier_css .= "@import '//fonts.googleapis.com/css?family=".$polices[$cles[$i]]."';".chr(13); 
		}
				
			// Traitement des styles principaux, qui sont des sélecteurs de classes
		$styles = json_decode(stripslashes($styles), true);
		$cles = array_keys($styles);
		$lng = sizeof($cles);
		for($i=0;$i<$lng;$i++)
		{
			$sous_tab = $styles[$cles[$i]];
			$remplace = array("livre_ul" => "livre ul","livre_ol" => "livre ol","livre_ul_li"=>"livre ul li","livre_ol_li"=>"livre ol li","_a" => " a","_hover" => ":hover","_visited" => ":visited");
			$cles[$i] = strtr($cles[$i],$remplace);
			$contenu_fichier_css .= ".".$cles[$i].chr(13)."{".chr(13);
			$sous_cles = array_keys($sous_tab);
			$sous_lng = sizeof($sous_cles);
			for($j=0;$j<$sous_lng;$j++)
			{
				$propcss = $sous_tab[$sous_cles[$j]];
				$remplacement = array("_" => "-");
				$sous_cle = strtr($sous_cles[$j],$remplacement);
				if(preg_match("/^[0-9]+$/",$propcss)) $propcss .= "px";
				$contenu_fichier_css .= chr(9).$sous_cle.":".$propcss.";".chr(13);
			}
			$contenu_fichier_css .= "}".chr(13);
		}
			// Traitement des styles du livre, qui sont des sélecteurs d'identifiant
		$styles_identifiant = json_decode(stripslashes($styles_identifiant), true);
		$cles = array_keys($styles_identifiant);
		$lng = sizeof($cles);
		$n = $num_livre;
		for($i=0;$i<$lng;$i++)
		{
			$sous_tab = $styles_identifiant[$cles[$i]];
			$remplace = array("livre".$n."_ul" => "livre".$n." ul","livre".$n."_ol" => "livre".$n." ol",
			"livre".$n."_ul_li"=>"livre".$n." ul li","livre".$n."_ol_li"=>"livre".$n." ol li",
			"_a" => " a","_hover" => ":hover","_visited" => ":visited");
			$cles[$i] = strtr($cles[$i],$remplace);
			$contenu_fichier_css .= "#".$cles[$i].chr(13)."{".chr(13);
			$sous_cles = array_keys($sous_tab);
			$sous_lng = sizeof($sous_cles);
			for($j=0;$j<$sous_lng;$j++)
			{
				$propcss = $sous_tab[$sous_cles[$j]];
				$remplacement = array("_" => "-");
				$sous_cle = strtr($sous_cles[$j],$remplacement);
				if(preg_match("/^[0-9]+$/",$propcss)) $propcss .= "px";
				$contenu_fichier_css .= chr(9).$sous_cle.":".$propcss.";".chr(13);
			}
			$contenu_fichier_css .= "}".chr(13);
		}
		
			// Traitement des autres styles (autres numéros de livres)
		$autres_styles = json_decode(stripslashes($autres_styles), true);
		$cles = array_keys($autres_styles);
		$lng = sizeof($cles);
		for($i=0;$i<$lng;$i++)
		{
			$sous_tab = $autres_styles[$cles[$i]];
				// détecter le numéro de livre:
			preg_match("/[0-9]+/",$cles[$i],$resultats);
			$n = $resultats[0];
			$remplace = array("livre".$n."_ul" => "livre".$n." ul","livre".$n."_ol" => "livre".$n." ol",
			"livre".$n."_ul_li"=>"livre".$n." ul li","livre".$n."_ol_li"=>"livre".$n." ol li",
			"_a" => " a","_hover" => ":hover","_visited" => ":visited");
			$cles[$i] = strtr($cles[$i],$remplace);
			$contenu_fichier_css .= "#".$cles[$i].chr(13)."{".chr(13);
			$sous_cles = array_keys($sous_tab);
			$sous_lng = sizeof($sous_cles);
			for($j=0;$j<$sous_lng;$j++)
			{
				$propcss = $sous_tab[$sous_cles[$j]];
				$remplacement = array("_" => "-");
				$sous_cle = strtr($sous_cles[$j],$remplacement);
				if(preg_match("/^[0-9]+$/",$propcss)) $propcss .= "px";
				$contenu_fichier_css .= chr(9).$sous_cle.":".$propcss.";".chr(13);
			}
			$contenu_fichier_css .= "}".chr(13);
			
		}
		
		$adresse = PLUGIN_DIR."/styles/chapitres-front.css";
		file_put_contents($adresse,$contenu_fichier_css);
		return "ok";
	}
	
	/***** Renvoyer les indices de présence d'une chaîne 
		   Returning indices of a string *****/
	private function renvoyer_indices($meule_de_foin, $aiguille)
	{
		$indices = array();
		while($indice = strripos($meule_de_foin, $aiguille))
		{
			array_push($indices, $indice);
			$meule_de_foin = substr($meule_de_foin, 0, $indice);
		}

		return array_reverse($indices);
	}
}

?>