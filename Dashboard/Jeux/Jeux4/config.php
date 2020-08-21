<?php

// On démarre le système de buffer
@ob_start();

// Génération du chemin relatif au fichier actuel
$cheminFichier = str_replace( "\\", "/", __FILE__ );
$cheminScript = $_SERVER[ "SCRIPT_FILENAME" ];
global $chemin;
$chemin = "";
$indice = -1;
for ( $i = 0; $i < strlen( $cheminFichier ) && $i < strlen( $cheminScript ); ++ $i )
{
	if ( $cheminFichier[ $i ] != $cheminScript[ $i ] )
	{
		$indice = $i;
		break;
	}
}
if ( $indice != -1 )
{
	$cheminFichier = substr( $cheminFichier, $indice );
	//$cheminScript = substr($cheminScript, $indice);
	$indice = 0;
	for ( $i = 0; $i < strlen( $cheminFichier ); ++ $i )
	{
		if ( $cheminFichier[ $i ] == '/' )
		{
			++ $indice;
		}
	}
	for ( $i = 0; $i < $indice; ++ $i )
	{
		$chemin .= "../";
	}
}
// Fin de la génération du chemin relatif au fichier actuel


// Les pages seront toutes en UTF-8
header( "Content-Type: text/html; charset=UTF-8" );
$chemin = "../";
require_once ( $chemin . "outils/outils.php" );

// On inclu les chemins des classes PHP
inclureChemin( $chemin . "classes/donnees/commun" );
inclureChemin( $chemin . "classes/donnees/base" );
inclureChemin( $chemin . "classes/donnees/reponse" );
inclureChemin( $chemin . "classes/utilitaires" );

// On se connecte à la base de données
$base = Base::getInstance();
if ( getIP() == "127.0.0.1" )
{
	$base->connecter( "localhost", "root", "", "autonomiu_sql" );
	$urlSite = "http://localhost/";
}
else
{
	$base->connecter( "mysql5-5.pro", "autonomiu_sql", "qv33ekb",
			"autonomiu_sql" );
	$urlSite = "http://ailyan.fr/";
}
$cheminMedia = "medias/";

// Pour l'upload
if ( getIP() == "127.0.0.1" )
{
	$repertoireUpload = "../upload/";
	$URLUpload = "http://localhost/upload/";
}
else
{
	$repertoireUpload = "../upload/";
	$URLUpload = "http://ailyan.fr/upload/";
}

// Vérification de l'utilisateur connecté
$gestionUtilisateur = new GestionUtilisateur( "UtilisateurSite" );
$utilisateur = $gestionUtilisateur->getUtilisateur();

?>