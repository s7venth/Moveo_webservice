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
	$userFunc = new DB_UserFunctions();

    // Tableau associatif qui sera envoyé au JSON
    $response = array("tag" => $tag, "success" => 0, "error" => 0);

    // Verification des TAGS
	switch ($tag){
		case 'login':
		
			// les informations des champs "email" et "mot de passe" du formulaire de connexion
			$email = $_POST['email'];
			$password = $_POST['password'];

			// verifier si l'utilisateur existe
			$user = $userFunc->getUserByEmailAndPassword($email, $password);
			if ($user) {
				$user_id = $userFunc->registerDateConnection($user['user_id']);
				if($user_id){
					// L'utilisateur existe : echo json avec success = 1
					$response["success"] = 1;
					$response["user"]["user_id"] = $user["user_id"];
					$response["user"]["user_last_name"] = $user["user_last_name"];
					$response["user"]["user_first_name"] = $user["user_first_name"];
					$response["user"]["avatar"] = $user["user_link_avatar"];
					$response["user"]["user_birthday"] = $user["user_birthday"];
					$response["user"]["user_email"] = $user["user_email"];
					$response["user"]["user_country"] = $user["user_country"];
					$response["user"]["user_city"] = $user["user_city"];
					$response["user"]["access_id"] = $user["access_id"];
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
			if ($userFunc->isUserExisted($email)) {
				// L'utilisateur existe donc envoyer un message d'erreur
				$response["error"] = 2;
				$response["error_msg"] = "L'utilisateur existe deja";
				echo json_encode($response);
			} else {
				// L'utilisateur n'existe pas donc l'enregistrer 
				$user = $userFunc->storeUser($name, $firstName, $email, $password);
				if ($user) {
					// Si l'utilisateur a été enregistrer 
					$response["success"] = 1;
					$response["error_msg"] = "Enregistrement réussi";
					echo json_encode($response);
				} else {
					// Si l'utilisateur n'a pas pu être enregistrer donc envoyer un message d'erreur
					$response["error"] = 1;
					$response["error_msg"] = "l'utilisateur n'a pas été enregistré";
					echo json_encode($response);
				}
			}
			BREAK;
			
		case 'getOtherUser': 
		
			$otherUser_id = $_POST['otherUser_id'];
				
			$result = $userFunc->getOtherUser($otherUser_id);
			if ($result != false) {
				$response["success"] = 1;
				$response["otherUser"]["user_name"] = $result["user_name"];
				$response["otherUser"]["user_firstname"] = $result["user_firstname"];
				$response["otherUser"]["user_link_avatar"] = $result["user_link_avatar"];
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
		
		case 'addDialog' :
			$message = $_POST['message'];
			$user_id = $_POST['user_id'];
			$other_user_id = $_POST['other_user_id'];
			
			$result = $userFunc->addDialog($user_id, $other_user_id, $message);
			
			if($result){
				$response["success"] = 1;
				$response["message"] = "Le message a été ajouté avec succès";
				echo json_encode($response);
			}else{
				$response["error"] = 1;
				$response["message"] = "Erreur lors de l'ajout du dialogue";
				echo json_encode($response);
			}
			BREAK;
			
		default : 
			echo "Requête invalide";
    }
} else {
    echo "Accès refusé";
}
?>
