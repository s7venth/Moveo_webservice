<?php
/** 
 * Chaque requête sera identifier par TAG
 * Les réponses seront données en JSON
 */

// Verification des requetes sous la forme de GET 
if (isset($_GET['tag']) && $_GET['tag'] != '') {
    // RECUPERER LE TAG
    $tag = $_GET['tag'];

	// IMPORTER LES FONCTIONS DE LA CLASSE DB_TripFunctions
	require_once 'include/DB_UserFunctions.php';
	$userfunc = new DB_UserFunctions();

    // Tableau associatif qui sera envoyé au JSON
    $response = array("tag" => $tag, "success" => 0, "error" => 0);

    // Verification des TAGS
	switch ($tag){
		case 'login':
		
			// les informations des champs "email" et "mot de passe" du formulaire de connexion
			$email = $_GET['email'];
			$password = $_GET['password'];

			// verifier si l'utilisateur existe
			$user = $userfunc->getUserByEmailAndPassword($email, $password);
			if ($user != false) {
				// L'utilisateur existe : echo json avec success = 1
				$response["success"] = 1;
				$response["user"]["name"] = $user["user_name"];
				$response["user"]["firstname"] = $user["user_firstname"];
				echo json_encode($response);
			}else{
				// l'utilisateur n'existe pas : echo json avec error = 1
				$response["error"] = 1;
				$response["error_msg"] = "L'email ou le mot de passe est incorrect";
				echo json_encode($response);
			} 
			BREAK;
			
		case 'register': 
        
			$name = $_GET['name'];
			$firstName = $_GET['firstName'];
			$email = $_GET['email'];
			$password = $_GET['password'];

			// Verifier si l'utilisateur existe
			if ($userfunc->isUserExisted($email)) {
				// L'utilisateur existe donc envoyer un message d'erreur
				$response["error"] = 2;
				$response["error_msg"] = "L'utilisateur existe deja";
				echo json_encode($response);
			} else {
				// L'utilisateur n'existe pas donc enregistrer l'utilisateur
				$user = $userfunc->storeUser($name, $firstName, $email, $password);
				if ($user) {
					// Si l'utilisateur a été enregister 
					$response["success"] = 1;
					$response["error_msg"] = "Enregistrement réussi";
					echo json_encode($response);
				} else {
					// Si l'utilisateur n'a pas pu être enregister donc envoyer un message d'erreur
					$response["error"] = 1;
					$response["error_msg"] = "l'utilisateur n'a pas été enregisté";
					echo json_encode($response);
				}
			}
			BREAK;
			
		default : 
			echo "Requête invalide";
    }
} else {
    echo "Accès refusé";
}
?>
