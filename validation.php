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

    // Tableau associatif qui sera envoyé au JSON
    $response = array("key" => $key, "success" => 0, "error" => 0);
	
	$result = $validationFunc->validateUserAccompt($key, $id);
    
	if($result){
		$response["success"] = 1;
		$response["message"] = "ok";
		echo json_encode($response);
	}else{
		$response["error"] = 1;
		$response["message"] = "non ok";
		echo json_encode($response);
	}
} else {
    echo "Accès refusé";
}
?>
