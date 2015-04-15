<?php
/** 
 * Chaque requête sera identifier par TAG
 * Les réponses seront données en JSON
 */
// Attention utiliser GET pour verifier directement en HTTP et utiliser POST pour l'appli
// Verification des requetes sous la forme de GET 
if (isset($_POST['tag']) && $_POST['tag'] != '') {
    // RECUPERER LE TAG
    $tag = $_POST['tag'];

	// IMPORTER LES FONCTIONS DE LA CLASSE DB_TripFunctions
	require_once 'include/DB_UserFunctions.php';
	$userfunc = new DB_UserFunctions();

    // Tableau associatif qui sera envoyé au JSON
    $response = array("tag" => $tag, "success" => 0, "error" => 0);

    // Verification des TAGS
	switch ($tag){
		case 'login':
		
			// les informations des champs "email" et "mot de passe" du formulaire de connexion
			$email = $_POST['email'];
			$password = $_POST['password'];

			// verifier si l'utilisateur existe
			$user = $userfunc->getUserByEmailAndPassword($email, $password);
			if ($user) {
				$user_id = $userfunc->registerDateConnection($user['user_id']);
				if($user_id){
					// L'utilisateur existe : echo json avec success = 1
					$response["success"] = 1;
					$response["user"]["id"] = $user["user_id"];
					$response["user"]["name"] = $user["user_name"];
					$response["user"]["firstname"] = $user["user_firstname"];
					$response["user"]["birthday"] = $user["user_firstname"];
					$response["user"]["email"] = $user["user_mail"];
					$response["user"]["country"] = $user["user_country"];
					$response["user"]["city"] = $user["user_city"];
					$response["user"]["access"] = $user["access_id"];
					echo json_encode($response);
				}else{
					$response["error"] = 2;
					$response["message"] = "Erreur lors de l'enregistrement de la date de connexion ";
					echo json_encode($response);
				}
			}else{
				// l'utilisateur n'existe pas : echo json avec error = 1
				$response["error"] = 1;
				$response["error_msg"] = "L'email ou le mot de passe est incorrect";
				echo json_encode($response);
			} 
			BREAK;
			
		case 'register': 
        
			$name = $_POST['name'];
			$firstName = $_POST['firstName'];
			$email = $_POST['email'];
			$password = $_POST['password'];

			// Verifier si l'utilisateur existe
			if ($userfunc->isUserExisted($email)) {
				// L'utilisateur existe donc envoyer un message d'erreur
				$response["error"] = 2;
				$response["error_msg"] = "L'utilisateur existe deja";
				echo json_encode($response);
			} else {
				// L'utilisateur n'existe pas donc l'enregistrer 
				$user = $userfunc->storeUser($name, $firstName, $email, $password);
				if ($user) {
					// Si l'utilisateur a été enregister 
					$response["success"] = 1;
					$response["error_msg"] = "Enregistrement réussi";
					echo json_encode($response);
				} else {
					// Si l'utilisateur n'a pas pu être enregister donc envoyer un message d'erreur
					$response["error"] = 1;
					$response["error_msg"] = "l'utilisateur n'a pas été enregistré";
					echo json_encode($response);
				}
			}
			BREAK;
			
		case 'getOtherUser': 
		
			$otherUser_id = $_POST['otherUser_id'];
				
			$result = $userfunc->getOtherUser($otherUser_id);
			if ($result != false) {
				$response["success"] = 1;
				$response["otherUser"]["name"] = $result["user_name"];
				$response["otherUser"]["firstname"] = $result["user_firstname"];
				$response["otherUser"]["avatar"] = $result["user_link_avatar"];
				echo json_encode($response);
			}else{
				$response["error"] = 1;
				$response["error_msg"] = "Erreur lors de la recuperation de l'utilisateur";
				echo json_encode($response);
			} 
			BREAK;
			
		case 'validate': 
			
			$id_user= $_POST['id_user'];
			$id_key= $_POST['key'];
			// A SUIVRE !!!!
			BREAK;
			
			
		default : 
			echo "Requête invalide";
    }
} else {
    echo "Accès refusé";
}
?>
