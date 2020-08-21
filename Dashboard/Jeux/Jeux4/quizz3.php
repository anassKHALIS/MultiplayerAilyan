<?php

// POUR QUE LES ACCENTS S AFFICHENT CORRECTEMENT
// ENCODER EN UTF8 ET ENREGISTRER EN ANSI


$nomPage = "Administration du Quizz";

//require_once ( "../conf/config.php" );

global $utilisateur;
global $base;

if ( $utilisateur == NULL )
{
//	rediriger( "index.html" );
}

require_once ( "page/debut.php" );

$gestionnaire = new Gestionnaire( "Etablissement" );

/* Début ajout d'une question */
if ( estAutorise( "etablissement", "quizz" ) && get( "action" ) == "ajout_question" && get( "id" ) + 0 > 0 )
{
	// Si un utilisateur non autorisé tente d'ajouter une question
	if ( !estAutorise( "etablissement", "quizz" ) && $utilisateur->getEtablissement()->getId() !=get( "id" ) + 0 )
	{
		$erreur = "L'utilisateur ";
		$erreur .= $utilisateur->getId();
		$erreur .= " a essayé d'ajouter une question à l'établissement ";
		$erreur .= get( "id" ) + 0;
		$erreur .= " !";
		throw new Erreur( "Accès non autorisé !", $erreur );
	}

	$etablissement = $base->recuperer( "Etablissement", get( "id" ) + 0 );

	// Si un utilisateur tente d'ajouter des questions à un établissement qui n'existe pas
	if ( $etablissement == NULL || !is_object( $etablissement ) )
	{
		$erreur = "L'utilisateur ";
	//	$erreur .= $utilisateur->getId();
		$erreur .= " a essayé d'ajouter une question à l'établissement ";
		$erreur .= get( "id" ) + 0;
		//throw new Erreur( "Cet établissement n'existe pas ou plus !", $erreur );
	}
	
	// Récupération des catégories de questions
	
	$categories = $base->recupererTableau( "CategorieQuestion" );
	$listeCategories = "";
	foreach( $categories as $categorie )
	{
		$listeCategories .= "<option value='".$categorie->getId()."'>".$categorie->getNom()."</option>";
		$categoriesQuestion[] = new ValeurListe( $categorie->getId(), $categorie->getNom(),$categorie);
        //$categoriesQuestion[] = new ValeurListe( $categorie->getId(), $categorie->getNom(),$categorie);
	}
	
	
	
	function fixGlobalFilesArray($files)
	{
		$ret = array();
		
		if(isset($files['tmp_name']))
		{
			if (is_array($files['tmp_name']))
			{
				foreach($files['name'] as $idx => $name)
				{
					$ret[$idx] = array(
						'name' => $name,
						'tmp_name' => $files['tmp_name'][$idx],
						'size' => $files['size'][$idx],
						'type' => $files['type'][$idx],
						'error' => $files['error'][$idx]
						);
				}
			}
			else
			{
				$ret = $files;
			}
		}
		else
		{
			foreach ($files as $key => $value)
			{
			//	$ret[$key] = self::fixGlobalFilesArray($value);
			}
		}
		
		return $ret;
	}
	
	if (isset($_POST['addQuestion']))
	{
		//$base->demarrerTransaction();
		$question = new Question();
		$question_ok = false;
		
		$bonne_reponse_ok = false;
		$reponses = array();
		$reponses_enonce = false;
		$reponse_image = true;
		
		if (!empty($_POST["enonce"]))
		{
			//echo "<p>enonce question".$_POST["enonce"]."</p>";
			$question->setEnonce( $_POST[ "enonce" ]);
				/*
				$categories = $base->recupererTableau( "CategorieQuestion" );
				foreach( $categories as $categorie )
				{
					$categoriesQuestion[] = new ValeurListe( $categorie->getId(), $categorie->getNom(),$categorie);
				}
				*/
				
				$valeurReelle = NULL;
				foreach ( $categoriesQuestion as $valeurListe )
				{
					if ( $_POST[ "categorieQuestion" ] == $valeurListe->getId() )
					{
						if ( $valeurListe->getObjet() != NULL )
						{
							$valeurReelle = $valeurListe->getObjet();
						}
						else
						{
							$valeurReelle = $valeurListe->getValeur();
						}
						break;
					}
				}
			$question->setCategorieQuestion( $valeurReelle );
			$question->setEtablissement( $etablissement );
			$question_ok = true;
		}
		if ($_FILES["image"]['error'] == 0)
		{
			//echo "<p>image question</p>";
			$question->setImage( deplacerImage2($_FILES[ "image" ]) );
			//$question->setImage( $_FILES[ "image" ]);
		}
		
		/*
		echo "<div class=\"message_succes\">La question a bien été ajoutée !</div>";*/
		
		
		if (!empty($_POST["bonne_reponse"]))
		{
			$reponse = new ReponseQuestion();
			$reponse->setEnonce($_POST["bonne_reponse"]);
			$reponse->setCorrecte(true);
			$reponses[] = $reponse;
			
			$bonne_reponse_ok = true;
			$reponse_image = false;
			//echo "<p>texte bonne reponse ok</p>";
		}
		elseif ($_FILES["bonne_reponse_image"]['error'] == 0)
		{
			$reponse = new ReponseQuestion();
			//$image = deplacerImage( $_FILES["bonne_reponse_image"] );
			
			//echo "</br></br>IMAGE bon LA : ";
			//print_r($_FILES["bonne_reponse_image"]);
			//echo "</br></br>IMAGE bon LA : ".deplacerImage2( $_FILES["bonne_reponse_image"] );
			$reponse->setImage(deplacerImage2( $_FILES["bonne_reponse_image"] ));
			$reponse->setCorrecte(true);
			$reponses[] = $reponse;
			
			$bonne_reponse_ok = true;
			//echo "<p>image bonne reponse ok</p>";
		}
		
		if (!$reponse_image)
		{
			foreach($_POST["mauvaise_reponse"] as $rep)
			{
				if (!empty($rep))
				{
					//echo "<p>mauvaise reponse : ".$rep."</p>";
					$reponse = new ReponseQuestion();
					$reponse->setEnonce($rep);
					$reponse->setCorrecte(false);
					//$reponse->setQuestion( $resultat[ "question" ] );
					$reponses[] = $reponse;
				}
			}
		}
		
		// SI LES ENONCE N ONT PAS ETE FAIT
		if ($reponse_image)
		{
			$badfiles = fixGlobalFilesArray($_FILES["mauvaise_reponse_image"]);
			foreach($badfiles as $image)
			{
				if ($image['error']==0)
				{
					$reponse = new ReponseQuestion();
					$reponse->setImage(deplacerImage2( $image));
					$reponse->setCorrecte(false);
					$reponses[] = $reponse;
				}
			}
		}
		
		if (!$question_ok)
		{
			echo "<div class='message_erreur'>Question incomplète.</div>";
		}
		
		if (!$bonne_reponse_ok)
		{
			echo "<div class='message_erreur'>Bonne réponse incomplète.</div>";
		}
		
		if (count($reponses)>=4)
		{
			echo "<div class='message_erreur'>Trop peu de réponses. Minimum : 4 (bonne réponse comprise).</div>";
		}
		
		if ($question_ok && $bonne_reponse_ok && (count($reponses)>=4))
		{
			$base->demarrerTransaction();
			$base->inserer($question);
			$base->terminerTransaction();
			
			foreach ($reponses as $rep_insert)
			{
				$rep_insert->setQuestion($question);
				
				$base->demarrerTransaction();
				$base->inserer($rep_insert);
				$base->terminerTransaction();
				$mettre = false;
			}
			echo "<div class='message_succes'>La question a bien été ajoutée !</div>";
		}
	}
	
	
	?>
	
	<script language="javascript">
		function addReponse()
		{
		    document.getElementById('reponses').innerHTML += '<tr><td>Texte : </td><td><input type="text" name="mauvaise_reponse[]"/></td></tr> <tr class="imagequiz"><td>Image : </td><td><input type="file" name="mauvaise_reponse_image[]"/></td></tr>';
		}
		
		function addInput()
		{
			var newInput = '<tr><td>Texte : </td><td><input type="text" name="mauvaise_reponse[]"/></td></tr> <tr class="imagequiz"><td>Image : </td><td><input type="file" name="mauvaise_reponse_image[]"/></td></tr>';
			document.body.insertBefore(newInput, document.getElementById('reponses_reference'));
		}
	</script>
	
	
	<form id="formquiz" action="" method="post" enctype="multipart/form-data">
	<table id="reponses">
		<tr><td>Enoncé de la question : </td><td><input type="text" name="enonce"/></td></tr>
		<tr><td>Chemin de l'image : </td><td><input type="file" name="image"/></td></tr>
		<tr><td>Catégorie de la question : </td><td><select name="categorieQuestion"><?php echo $listeCategories ?></select></td></tr>
		
		
		<tr><td class="titrequiz">Bonne réponse : </td></tr>
		<tr><td>Texte : </td><td><input type="text" name="bonne_reponse"/></td></tr>
		<tr class="imagequiz"><td>Image : </td><td><input type="file" name="bonne_reponse_image"/></td></tr>
		
		<tr><td class="titrequiz">Mauvaises réponses : </td></tr>
		<tr><td>Texte : </td><td><input type="text" name="mauvaise_reponse[]"/></td></tr>
		<tr class="imagequiz"><td>Image : </td><td><input type="file" name="mauvaise_reponse_image[]"/></td></tr>
		
		<tr><td>Texte : </td><td><input type="text" name="mauvaise_reponse[]"/></td></tr>
		<tr class="imagequiz"><td>Image : </td><td><input type="file" name="mauvaise_reponse_image[]"/></td></tr>
		
		<tr><td>Texte : </td><td><input type="text" name="mauvaise_reponse[]"/></td></tr>
		<tr class="imagequiz"><td>Image : </td><td><input type="file" name="mauvaise_reponse_image[]"/></td></tr>
		
		<tr id="reponses_reference"/>
	</table>
	<p><input id="plusquiz" type="button" value="+" onclick="addInput()" /></p>
	
	<p><input type="submit" value="Ajouter la question" name="addQuestion"/></p>
	
	</form>
	
	<?php
	
	/* Fin d'ajout d'une question */
	echo "<div class=\"etablissement_menu\">";
	echo "<a href=\"";
	echo html( "?action=menu&id=" . ( get( "id" ) + 0 ) );
	echo "\">Retour</a>";
	echo "</div>";
}

/* Début ajout d'une réponse */
else if ( estAutorise( "etablissement", "quizz" ) && get( "action" ) == "ajout_reponse" && get( "id" ) + 0 > 0 )
{
	// Vérification des droits d'accès
	if ( !estAutorise( "etablissement", "quizz" ) && $utilisateur->getEtablissement()->getId() != get( "id" ) + 0 )
	{
		$erreur = "L'utilisateur ";
		$erreur .= $utilisateur->getId();
		$erreur .= " a essayé d'ajouter une question à l'établissement ";
		$erreur .= get( "id" ) + 0;
		$erreur .= " !";
		throw new Erreur( "Accès non autorisé !", $erreur );
	}

	$etablissement = $base->recuperer( "Etablissement", get( "id" ) + 0 );

	if ( $etablissement == NULL || !is_object( $etablissement ) )
	{
		$erreur = "L'utilisateur ";
		$erreur .= $utilisateur->getId();
		$erreur .= " a essayé d'ajouter une question à l'établissement ";
		$erreur .= get( "id" ) + 0;
		throw new Erreur( "Cet établissement n'existe pas ou plus !", $erreur );
	}

	// Récupération des catégories de questions
	$tab = $base->recupererTableau( "Question" );
	foreach( $tab as $question )
	{
		$questions[] = new ValeurListe( $question->getId(), $question->getEnonce(),$question);
	}
        
    // Ajout de la question
	$formulaire = new Formulaire();

	$formulaire->ajouterChamp(array( "nom" => "enonce", "titre" => "Enoncé de la réponse", "facultatif" => true) );
	$formulaire->ajouterChamp(array( "nom" => "image", "titre" => "Chemin de l'image", "type" => "fichier", "facultatif" => true, "regex" => "image/jpeg", "format" => "Image JPEG" ));
    $formulaire->ajouterChamp(array( "nom" => "correcte",  "titre" => "Est elle correcte (oui ou non)"));
	$formulaire->ajouterChamp(array( "nom" => "question", "titre" => "Question associée", "type" => "liste", "valeurs" => $questions ) );
	$formulaire->ajouterBoutonEnvoyer( array( "valeur" => "Ajouter la réponse" ) );

	$resultat = $formulaire->afficher(array( "titre" => "Ajout d'une réponse", "legende" => true,"classeCSS" => "formulaire_ajout_réponse" ) );


	if ( $resultat != NULL )
	{
		$base->demarrerTransaction();
		$reponse = new ReponseQuestion();
		$reponse->setEnonce( $resultat[ "enonce" ]);
		$reponse->setImage( $resultat[ "image" ]);
		$reponse->setQuestion( $resultat[ "question" ] );
                
		if( strtolower($resultat["correcte"]) == "non")
		{
			$reponse->setCorrecte(false);
		}
		else if( strtolower($resultat["correcte"]) == "oui" )
		{
			$reponse->setCorrecte(true);
		}

		print_r ($reponse);
		//$base->inserer( $reponse );
		$base->terminerTransaction();
		echo "<div class=\"message_succes\">La réponse a bien été ajoutée !</div>";
	}

	/* Fin d'ajout d'une réponse */
	echo "<div class=\"etablissement_menu\">";
	echo "<a href=\"";
	echo html( "?action=menu&id=" . ( get( "id" ) + 0 ) );
	echo "\">Retour</a>";
	echo "</div>";
}


/* Début ajout d'une catégorie de questions */
else if ( estAutorise( "etablissement", "quizz" ) && get( "action" ) == "ajout_categorie_question" && get( "id" ) + 0 > 0 )
{
	// Vérification des droits d'accès
	if ( $utilisateur->getEtablissement()->getId() != get( "id" ) + 0 )
	{
		$erreur = "L'utilisateur ";
		$erreur .= $utilisateur->getId();
		$erreur .= " a essayé d'ajouter une catégorie de questions à l'établissement ";
		$erreur .= get( "id" ) + 0;
		$erreur .= " !";
		throw new Erreur( "Accès non autorisé !", $erreur );
	}

	$etablissement = $base->recuperer( "Etablissement", get( "id" ) + 0 );

	if ( $etablissement == NULL || !is_object( $etablissement ) )
	{
		$erreur = "L'utilisateur ";
		$erreur .= $utilisateur->getId();
		$erreur .= " a essayé d'ajouter une catégorie de questions à l'établissement ";
		$erreur .= get( "id" ) + 0;
		$erreur .= " !";
		throw new Erreur( "Cet établissement n'existe pas ou plus !", $erreur );
	}

	$formulaire = new Formulaire();
	$formulaire->ajouterChamp(array( "nom" => "nom", "titre" => "Nom de la catégorie" ) );
     
	$formulaire->ajouterBoutonEnvoyer( array( "valeur" => "Ajouter la categorie" ) );

	$resultat = $formulaire->afficher(array( "titre" => "Ajout d'une catégorie de questions", "legende" => true, "classeCSS" => "formulaire_ajout_categorie_question" ) );

	
	if ( $resultat != NULL )
	{
		$categorie = new CategorieQuestion();
		$categorie->setNom( $resultat[ "nom" ] );
		$categorie->setEtablissement( $etablissement);
		$base->inserer( $categorie );
		echo "<div class=\"message_succes\">La catégorie de questions a bien été ajoutée !</div>";
	}
	
    /* Fin ajout d'une catégorie de questions */
	echo "<div class=\"etablissement_menu\">";
	echo "<a href=\"";
	echo html( "?action=menu&id=" . ( get( "id" ) + 0 ) );
	echo "\">Retour</a>";
	echo "</div>";
}


else if ( estAutorise( "etablissement", "quizz" ) && get( "action" ) == "menu" && get("id" ) + 0 > 0 )
{
	echo "<div class=\"etablissement_menu\">";
	
	if ( estAutorise( "etablissement", "quizz" ) )
	{

		echo "<a href=\"";

		echo html( "?action=ajout_categorie_question&id=" . ( get( "id" ) + 0 ) );

		echo "\">Ajouter une catégorie de questions</a> - ";

	}

	if ( estAutorise( "etablissement", "quizz" ) )

	{

		echo "<a href=\"";

		echo html( "?action=ajout_question&id=" . ( get( "id" ) + 0 ) );

		echo "\">Ajouter une question</a> - ";

	}
        
        if ( estAutorise( "etablissement", "quizz" ) )

	{

		echo "<a href=\"";

		echo html( "?action=ajout_reponse&id=" . ( get( "id" ) + 0 ) );

		echo "\">Ajouter une réponse</a>";

	}

	echo "</div>";

}

else // a tej ... je crois
{
	if ( estAutorise( "etablissement", "admin" ) )

	{

		$options = array( "liens" => array() );

		if ( estAutorise( "etablissement", "ajout" ) )

		{

			$options[ "ajouter" ] = "?action=ajouter";

		}

		if ( estAutorise( "etablissement", "modif" ) )

		{

			$options[ "modifier" ] = "?action=modifier&id=";

		}

		if ( estAutorise( "etablissement", "suppr" ) )

		{

			$options[ "supprimer" ] = "Êtes-vous sûr de vouloir supprimer cet établissement ?";

		}

		if ( estAutorise( "etablissement", "membre" ) )

		{

			$options[ "liens" ][ "Voir la liste des membres" ] = '?action=voir&id=$id$';

		}

		if ( estAutorise( "etablissement", "menu" ) )

		{

			$options[ "liens" ][ "Gérer les menus" ] = '?action=menu&id=$id$';

		}

		if ( estAutorise( "etablissement", "annonce" ) )

		{

			$options[ "liens" ][ "Annonces" ] = 'annonce.html?ide=$id$';

		}

		$gestionnaire->afficherTableau( $options );

	}

	else

	{

		if ( estAutorise( "etablissement", "membre" ) )

		{

			$lien = "?action=voir&id=";

			$lien .= ( $utilisateur->getEtablissement()->getId() + 0 );

			rediriger( $lien );

		}

		else if ( estAutorise( "etablissement", "menu" ) )

		{

			$lien = "?action=menu&id=";

			$lien .= ( $utilisateur->getEtablissement()->getId() + 0 );

			rediriger( $lien );

		}

		else

		{

			$erreur = "L'utilisateur ";

			$erreur .= $utilisateur->getId();

			$erreur .= " a essayé d'accéder à l'administration des établissements !";

			throw new Erreur( "Accès interdit !", $erreur );

		}

	}

}


require_once ( "page/fin.php" );

?>