<?php
/** 
 * Chaque requête sera identifier par TAG
 * Les réponses seront données en JSON
 */
// Attention utiliser GET pour verifier directement en HTTP et utiliser POST pour l'application
// Verification des requêtes sous la forme de GET 
if (isset($_GET['key']) && $_GET['key'] != '' && isset($_GET['id']) && $_GET['id']) {
	
    // RECUPERER LE TAG
    $key = $_GET['key'];
	$id = $_GET['id'];
	
	// IMPORTER LES FONCTIONS DE LA CLASSE DB_ValidationFunctions
	require_once 'include/DB_ValidationFunctions.php';
	$validationFunc = new DB_ValidationFunctions();
	
	$accessId = $validationFunc->checkAccessId($id);
	
	// Si le compte n'est pas encore activé 
	if($accessId){
		
		$result = $validationFunc->validateUserAccompt($key, $id);
    
		if($result){
			echo "Félicitation votre compte est maintenant activé.";
		}else{
			echo "Une erreur s'est produite lors de l'activation de votre compte, veuillez cliquer de nouveau sur le lien d'activation.";
		}
		
	}else {
		echo "Votre compte est déjà activé.";
	}
	
	
} else {
    echo "Accès refusé";
}
?>
