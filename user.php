<?php
/** 
 * Chaque requête sera identifier par TAG
 * Les réponses seront données en JSON
 */
// Attention utiliser GET pour verifier directement en HTTP et utiliser POST pour l'appli
// Verification des requetes sous la forme de GET 
if (isset($_GET['tag']) && $_GET['tag'] != '') {
    // RECUPERER LE TAG
    $tag = $_GET['tag'];

	// IMPORTER LES FONCTIONS DE LA CLASSE DB_UserFunctions
	require_once 'include/DB_UserFunctions.php';
	$userFunc = new DB_UserFunctions();

    // Tableau associatif qui sera envoyé au JSON
    $response = array("tag" => $tag, "success" => 0, "error" => 0);

    // Verification des TAGS
	switch ($tag){
		
		case 'login': // Se connecter à l'application (Renvoie les informations de l'utilisateur)
		
			// les informations des champs "email" et "mot de passe" du formulaire de connexion
			$email = $_GET['email'];
			$password = $_GET['password'];

			// verifier si l'utilisateur existe
			$user = $userFunc->getUserByEmailAndPassword($email, $password);
			
			if($user) {
				
				if($user["access_id"] == '1'){
					$response["error"] = 3;
					$response["message"] = "le compte n'est pas activé";
				}
				else if($user["access_id"] == '3'){
					$response["error"] = 4;
					$response["message"] = "L'application est en maintenance";
				}
				else if($user["access_id"] == '4'){
					$response["error"] = 5;
					$response["message"] = "Le compte est bloqué par un modérateur";
				}else{
			
					$userId = $user["user_id"];
					$result = $userFunc->registerLoginDate($userId);
					if($result){
						
						// IMPORTATION DES CLASSES FRIEND ET TRIP
						require_once 'include/DB_TripFunctions.php';
						require_once 'include/DB_FriendFunctions.php';
						$friendFunc = new DB_FriendFunctions();
						$tripFunc = new DB_TripFunctions();
						
						
						$successTrip = false;
						$successFriend = false;
						$successInbox = false;
						$successSendbox = false;
						
						$response["user"]["user_id"] = $userId;
						$response["user"]["user_last_name"] = $user["user_last_name"];
						$response["user"]["user_first_name"] = $user["user_first_name"];
						$response["user"]["avatar"] = $user["user_link_avatar"];
						$response["user"]["user_birthday"] = $user["user_birthday"];
						$response["user"]["user_email"] = $user["user_email"];
						$response["user"]["user_country"] = $user["user_country"];
						$response["user"]["user_city"] = $user["user_city"];
						$response["user"]["access_id"] = $user["access_id"];
						
								
						// Récuperation des voyages
						$tripsList = $tripFunc->getTripList($userId);
						if($tripsList) {
							$successTrip = true;
							foreach($tripsList as $trips) {
								$response["trip"][] = array(
									"trip_id" => $trips["trip_id"],
									"trip_name" => $trips["trip_name"],
									"trip_country" => $trips["trip_country"],
									"trip_description" => $trips["trip_description"],
									"trip_created_at" => $trips["trip_created_at"],
									"comment_count" => $trips["comment_count"],
									"photo_count" => $trips["photo_count"]
								);	
							}
						}else{
							$response["trip"] = 0;
						}
							
						// Récuperation des amis
						$friendList = $friendFunc->getFriendsList($userId);
						if($friendList){
							$successFriend = true;
							foreach($friendList as $friend) {
								$response["friend"][] = array(
										"friend_id" => $friend["id"],
										"friend_last_name" => $friend["user_last_name"],
										"friend_first_name" => $friend["user_first_name"],
										"is_accepted" => $friend["is_accepted"]
								);
							}
						}else{
							$response["friend"] = 0;
						}

						// Récuperation de la boite de reception
						$inbox = $userFunc->getInbox($userId);
						if($inbox){
							$successInbox = true;
							foreach($inbox as $message) {
								$response["inbox"][] = array(
										"recipient_id" => $message["recipient_id"],
										"recipient_last_name" => $message["recipient_last_name"],
										"recipient_first_name" => $message["recipient_first_name"],
										"message" => $message["message"],
										"sent_datetime" => $message["sent_datetime"],
										"read_by_recipient" => $message["read_by_recipient"]
								);
							}
						}else{
							$response["inbox"] = 0;
						}

						// Récuperation de la boite d'envoi
						$sendbox = $userFunc->getSendbox($userId);
						if($sendbox){ 
							$successSendbox = true;
							foreach($sendbox as $message) {
								$response["sendbox"][] = array(
										"recipient_id" => $message["recipient_id"],
										"recipient_last_name" => $message["recipient_last_name"],
										"recipient_first_name" => $message["recipient_first_name"],
										"message" => $message["message"],
										"sent_datetime" => $message["sent_datetime"]
								);
							}
						}else{
							$response["sendbox"] = 0;
						}
						
						// On vérifie si l'utilisateur a des amis et des voyages
						if($successTrip && $successFriend){ 
							$response["success"] = 1;
						}else if($successTrip){
							$response["success"] = 2;
							$response["message"] = "L'utilisateur n'a pas d'amis";
						}else if($successFriend){
							$response["success"] = 3;
							$response["message"] = "L'utilisateur n'a pas de voyages";
						}else{
							$response["success"] = 4;
							$response["message"] = "L'utilisateur n'a ni amis ni voyages";
						}
					
					} else {
						// La date de connexion n'a pas pu être enregistrer
						$response["error"] = 2;
						$response["message"] = "Erreur lors de l'enregistrement de la date de connexion ";
					}
				}
				echo json_encode($response);
			} else {
				// l'utilisateur n'existe pas : envoyer un objet json avec un message d'erreur
				$response["error"] = 1;
				$response["message"] = "L'email ou le mot de passe est incorrect";
				echo json_encode($response);
			}
			BREAK;
			
		case 'register': 
        
			$name = $_GET['name'];
			$firstName = $_GET['firstName'];
			$email = $_GET['email'];
			$password = $_GET['password'];

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
		
		case 'updateProfil': 

			$user_id= $_GET['user_id'];
			$user_last_name = $_GET['user_last_name'];
			$user_first_name = $_GET['user_first_name'];
			$birthday = $_GET['birthday'];
			$country = $_GET['country'];
			$city = $_GET['city'];

			$result = $userFunc->updateUser($user_id, $user_last_name, $user_first_name, $birthday, $country, $city);

			if ($result) {
				$response["success"] = 1;
				echo json_encode($response);
			} else {
				$response["error"] = 1;
				$response["message"] = "Erreur lors de l'enregistrement";
				echo json_encode($response);
			}
			BREAK;

		case 'getOtherUser': 
		
			$otherUser_id = $_GET['otherUser_id'];
				
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
		
		case 'addDialog' : // ajouter un message avec l'expéditeur et le récepteur 
		
			
			$user_id = $_GET['user_id'];
			$recipient_id = $_GET['recipient_id'];
			$message = $_GET['message'];

			$result = $userFunc->addDialog($user_id, $recipient_id, $message);
			
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
		
		case 'readMessage' : // ajouter un message avec l'expéditeur et le récepteur 
		
			
			$user_id = $_GET['message_id'];
			$recipient_id = $_GET['recipient_id'];
			$message = $_GET['message'];
			
			$result = $userFunc->addDialog($user_id, $recipient_id, $message);
			
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

		case 'forgetPassword' :
			$user_email = $_GET['user_email'];
			
			// Si l'utilisateur existe il faut lui envoyer un mail avec le nouveau mot de passe 
			if($userFunc->isUserExisted($user_email)){
				
				$result = $userFunc->generateNewPassword($user_email);
				
				if($result){
					$response["success"] = 1;
					$response["message"] = "L'email à été envoyer";
					echo json_encode($response);
				}else{
					$response["error"] = 1;
					$response["message"] = "Erreur lors de l'envoie du mail";
					echo json_encode($response);
				}
			}else{
				$response["error"] = 2;
				$response["message"] = "Cette adresse email n'existe pas";
				echo json_encode($response);
			}
			BREAK;
		
		default : 
			// le tag n'existe pas 
			echo "Requête invalide";
    }
} else {
    echo "Accès refusé"; // le tag n'est pas spécifié 
}
?>
