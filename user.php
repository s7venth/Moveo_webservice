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

	// IMPORTER LES FONCTIONS DE LA CLASSE DB_UserFunctions
	require_once 'include/DB_UserFunctions.php';
	$userFunc = new DB_UserFunctions();

    // Tableau associatif qui sera envoyé au JSON
    $response = array("tag" => $tag, "success" => 0, "error" => 0);

    // Verification des TAGS
	switch ($tag){
		
		case 'login': // Se connecter à l'application (Renvoie les informations de l'utilisateur)
		
			// les informations des champs "email" et "mot de passe" du formulaire de connexion
			$email = $_POST['email'];
			$password = $_POST['password'];

			// verifier si l'utilisateur existe
			$user = $userFunc->getUserByEmailAndPassword($email, $password);
			
			if($user) {
				
				if($user["access_id"] == '1'){
					$response["error"] = 3;
					$response["message"] = "le compte n'est pas activé";
				}
				else if($user["access_id"] == '4'){
					$response["error"] = 4;
					$response["message"] = "L'application est en maintenance";
				}
				else if($user["access_id"] == '5'){
					$response["error"] = 5;
					$response["message"] = "Le compte est bloqué par un modérateur";
				}else{
			
					$userId = $user["user_id"];
					$result = $userFunc->registerLoginDate($userId);
					if($result){
						
						// IMPORTATION DES CLASSES FRIEND, TRIP ET DIALOG
						require_once 'include/DB_TripFunctions.php';
						require_once 'include/DB_FriendFunctions.php';
						require_once 'include/DB_DialogFunctions.php';							
						
						
						$response["user"]["user_id"] = $userId;
						$response["user"]["user_last_name"] = $user["user_last_name"];
						$response["user"]["user_first_name"] = $user["user_first_name"];
						$response["user"]["user_birthday"] = $user["user_birthday"];
						$response["user"]["user_email"] = $user["user_email"];
						$response["user"]["user_country"] = $user["user_country"];
						$response["user"]["user_city"] = $user["user_city"];
						$response["user"]["access_id"] = $user["access_id"];
						// Recuperation de la photo de l'utilisateur en base 64
						if($user["user_link_avatar"]){
							$data = @file_get_contents($user["user_link_avatar"]);
							if($data != false){
								$picture = base64_encode($data);
							}else{
								$picture = "";
							}
						}else{
							$picture = "";
						}
						$response["user"]["avatar"] = $picture;
						

						// -----------RECUPERATION DES VOYAGES--------------					
						//instantiation de la classe voyage 
						$tripFunc = new DB_TripFunctions();		
						// Recuperation des voyages
						$tripsList = $tripFunc->getTripList($userId);
						
						if($tripsList) {
							$successTrip = true;
							foreach($tripsList as $trip) {

								/*if($trip["link_cover"]){
									$data = @file_get_contents($trip["link_cover"]);
									if($data != false){
										$picture = base64_encode($data);
									}else{
										$picture = "null";
									}
								}else{
									$picture = "null";
								}*/
								$response["trip"][] = array(
									"trip_id" => $trip["trip_id"],
									"trip_name" => $trip["trip_name"],
									"trip_country" => $trip["trip_country"],
									"trip_description" => $trip["trip_description"],
									"trip_created_at" => $trip["trip_created_at"],
									"comment_count" => $trip["comment_count"],
									"photo_count" => $trip["photo_count"],
									"trip_cover" => $trip["link_cover"]
								);	
							}
						}else{
							$response["trip"] = 0;
						}
						
						// -------------RECUPERATION DES LIEUX-----------------
						
						$placesList = $tripFunc->getAllPlaces($userId);
						
						$tripFunc = null;
						
						if($placesList) {
							
							foreach($placesList as $place) {

								$response['place'][] = array('place_id' => $place['place_id'] ,
										 'place_name' => $place['place_name'] ,
										 'place_address' => $place['place_address'] ,
										 'place_description' => $place['place_description'] ,
										 'category_id' => $place['category_id'],
                                         'trip_id' => $place['trip_id']
                                                            );
							}
						}else{
							// Si la requête ne renvoie pas de lieu alors on initialise à 0
							$response["place"] = 0;
						}
						
						// -------------FRIEND------------
						$friendFunc = new DB_FriendFunctions();

						// Récupération des amis
						$friendList = $friendFunc->getFriendsList($userId);

						// on ferme la base de données 
						$friendFunc = null;
						
						if($friendList){
						
							foreach($friendList as $friend) {

								/*if($friend["user_link_avatar"]){
									$data = @file_get_contents($friend["user_link_avatar"]);
									if($data != false){
										$picture = base64_encode($data);
									}else{
										$picture = "";
									}
								}else{
									$picture = "";
								}*/

								$response["friend"][] = array(
									"friend_id" => $friend["id"],
									"friend_last_name" => $friend["user_last_name"],
									"friend_first_name" => $friend["user_first_name"],
									"friend_birthday" => $friend["user_birthday"],
									"friend_country" => $friend["user_country"],
									"friend_city" => $friend["user_city"],
									"is_accepted" => $friend["is_accepted"],
									"friend_avatar" =>  $friend["user_link_avatar"]
								);
							}
						}else{
							$response["friend"] = 0;
						}
						

						// ----------INBOX-------------
						$dialogFunc = new DB_DialogFunctions();
						// Récuperation de la boite de reception
						$inbox = $dialogFunc->getInbox($userId);


						if($inbox){
							
							foreach($inbox as $message) {
								$response["inbox"][] = array(
										"user_id" => $message["user_id"],
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

						// ----------SENDBOX-------------

						// Récuperation de la boite d'envoi
						$sendbox = $dialogFunc->getSendbox($userId);
						if($sendbox){ 
							
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
						
						$dialogFunc = null;
						$response["success"] = 1;
					
					
					} else {
						// La date de connexion n'a pas pu être enregistrer
						$response["error"] = 2;
						$response["message"] = "Erreur lors de l'enregistrement de la date de connexion ";
					}
				}

				echo json_encode($response);
				//$userFunc->closeDataBase();

			} else {
				// l'utilisateur n'existe pas : envoyer un objet json avec un message d'erreur
				$response["error"] = 1;
				$response["message"] = "L'email ou le mot de passe est incorrect";
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
		
		case 'updateProfil': 

			$user_id = $_POST['userId'];
			$user_last_name = $_POST['firstName'];
			$user_first_name = $_POST['lastName'];
			$country = $_POST['country'];
			$city = $_POST['city'];
		

			if(isset($_POST['birthday'])) $birthday = $_POST['birthday']; else $birthday='';
			if($_POST['avatar'] != "null" && $_POST['avatar'] != ""){
				$link_avatar = $userFunc->getLinkAvatar($user_id);
				if($link_avatar['user_link_avatar'] == "" || $link_avatar['user_link_avatar'] == null || $link_avatar['user_link_avatar'] == "null" || $link_avatar == false){
					// Si l'utilisateur n'as pas de chemin vers une photo on crée un nouveau chemin 
					$user_link_avatar = "img/".substr(sha1(rand()),10,10).".jpg";
					$avatar =  $userFunc->base64_to_jpeg("data:image/jpg;base64,".$_POST['avatar'], $user_link_avatar);
					if(!$avatar){
						$user_link_avatar = ""; 
						$response["avatar"] = 0;
					}
				}else{
					
					$user_link_avatar =  $link_avatar['user_link_avatar'];
					$avatar =  $userFunc->base64_to_jpeg("data:image/jpg;base64,".$_POST['avatar'], $user_link_avatar);
					// Si la création de la photo à echoué, le chemin est initialisé à vide
					if(!$avatar){
						$user_link_avatar = ""; 
						$response["avatar"] = 0;
					}

				}
			
			}else{
				//$link_avatar = $userFunc->getLinkAvatar($user_id);
				$user_link_avatar = null;
			}
			

			$result = $userFunc->updateUser($user_id, $user_last_name, $user_first_name, $birthday, $user_link_avatar, $country, $city);
			if ($result) {
				$response["success"] = 1;
			} else {
				$response["error"] = 1;
				$response["message"] = "Erreur lors de l'enregistrement ou aucun changement sauf probablement la photo";
			}

			echo json_encode($response);

			BREAK;

		case 'getOtherUser': 
			
			require_once 'include/DB_TripFunctions.php';
			require_once 'include/DB_FriendFunctions.php';

			$otherUserId = $_POST['otherUserId'];
			$userId = $_POST['userId'];

			$result = $userFunc->getOtherUser($otherUserId);

			if ($result) {
				$response["success"] = 1;
				$response["otherUser"]["last_name"] = $result["user_last_name"];
				$response["otherUser"]["first_name"] = $result["user_first_name"];
				$response["otherUser"]["birthday"] = $result["user_birthday"];
				$response["otherUser"]["country"] = $result["user_country"];
				$response["otherUser"]["city"] = $result["user_city"];
				$response["otherUser"]["access_id"] = $result["access_id"];
				$response["otherUser"]["trip_count"] = $result["trip_count"];
				if($result["user_link_avatar"]){
					$data = @file_get_contents($result["user_link_avatar"]);
					if($data != false){
						$picture = base64_encode($data);
					}else{
						$picture = "";
					}
				}else{
					$picture = "";
				}
				$response["otherUser"]["link_avatar"] = $picture;

				$userFunc = null;

				$tripFunc = new DB_TripFunctions();		
						// Récuperation des voyages
						$tripsList = $tripFunc->getOtherUserTripList($otherUserId);
						$tripFunc = null;
						if($tripsList) {

							foreach($tripsList as $trip) {

							if($row["link_cover"]){
								$data = @file_get_contents($row["link_cover"]);
								if($data != false){
									$picture = base64_encode($data);
								}else{
									$picture = "null";
								}
							}else{
								$picture = "null";
							}

						$response['trip'][] = array('trip_id' => $row['trip_id'],
													'trip_name' => $row['trip_name'],
													'trip_country' => $row['trip_country'],
													'trip_description' => $row['trip_description'],
													'trip_created_at' => $row['trip_created_at'],
													'trip_cover' => $picture,
													'user_last_name' => $row['user_last_name'],
													'user_first_name' => $row['user_first_name'],
													'comment_count' => $row['comment_count'],
													'photo_count' => $row['photo_count']
										 			);
							}

						}else{
							$response["trip"] = 0;
						}

					$friendFunc = new DB_FriendFunctions();	
					$friendList = $friendFunc->checkFriend($userId, $otherUserId);	
					$friendFunc = null;
					if($friendList){
						$response['invitation'] = 1;
					}else{
						$response['invitation'] = 0;
					}


			}else{
				$response["error"] = 1;
				$response["error_msg"] = "Erreur lors de la recuperation de l'utilisateur";

			} 

			
			echo json_encode($response);

			BREAK;
		
		case 'addDialog' : // ajouter un message avec l'expéditeur et le récepteur 
		
			$user_id = $_POST['userId'];
			$recipient_id = $_POST['recipientId'];
			$message = $_POST['message'];
			require_once 'include/DB_DialogFunctions.php';
			$dialogFunc = new DB_DialogFunctions();
			$result = $dialogFunc->addDialog($user_id, $recipient_id, $message);
			$dialogFunc = null;
			
			if($result){

				$response["success"] = 1;

			}else{

				$response["error"] = 1;
				$response["message"] = "Erreur lors de l'ajout du dialogue";

			}

			echo json_encode($response);

			BREAK;
		
		case 'readDialog' : // changer l'état "read" de 0 à 1
			
			$user_id = $_POST['userId'];
			$recipient_id = $_POST['recipientId'];
			$sent_datetime = $_POST['sentDatetime'];
			
			require_once 'include/DB_DialogFunctions.php';
			$dialogFunc = new DB_DialogFunctions();
			$result = $userFunc->readMessage($user_id, $recipient_id, $sent_datetime);
			$dialogFunc = null;
			
			if($result){

				$response["success"] = 1;

			}else{

				$response["error"] = 1;
				$response["message"] = "Erreur lors du changement d'etat du message";

			}

			echo json_encode($response);

			BREAK;

		case 'removeDialogInbox' : // supprimer un message de la boite de réception
		
			$user_id = $_POST['userId'];
			$recipient_id = $_POST['recipientId'];
			$sent_datetime = $_POST['sentDatetime'];
			
			require_once 'include/DB_DialogFunctions.php';
			$dialogFunc = new DB_DialogFunctions();
			$result = $userFunc->removeMessageInbox($user_id, $recipient_id, $sent_datetime);
			$dialogFunc = null;
			
			if($result){
				$response["success"] = 1;
			}else{
				$response["error"] = 1;
				$response["message"] = "Erreur lors de la suppression du message de reception";
			}

			echo json_encode($response);

			BREAK;
		
		case 'removeDialogOutbox' : // supprimer un message de la boite d'envoi
		
			$user_id = $_POST['userId'];
			$recipient_id = $_POST['recipientId'];
			$sent_datetime = $_POST['sentDatetime'];
			
			require_once 'include/DB_DialogFunctions.php';
			$dialogFunc = new DB_DialogFunctions();
			$result = $userFunc->removeMessageSendbox($user_id, $recipient_id, $sent_datetime);
			$dialogFunc = null;
			
			if($result){
				$response["success"] = 1;
			}else{
				$response["error"] = 1;
				$response["message"] = "Erreur lors de la suppression du message d'envoi";
			}

			echo json_encode($response);

			BREAK;
		
		case 'forgetPassword' : // OK

			$user_email = $_POST['user_email'];
			
			// Si l'utilisateur existe il faut lui envoyer un mail avec le nouveau mot de passe 
			if($userFunc->isUserExisted($user_email)){
				
				$result = $userFunc->generateNewPassword($user_email);
				
				if($result){
					$response["success"] = 1;
				}else{
					$response["error"] = 1;
					$response["message"] = "Erreur lors de l'envoie du mail";
				}

			}else{
				$response["error"] = 2;
				$response["message"] = "Cette adresse email n'existe pas";
			}

			echo json_encode($response);


			BREAK;
		
		case 'changePassword':

			$user_id = $_POST['user_id'];
			$password = $_POST['password'];
			$new_password = $_POST['new_password'];

			$result = $userFunc->checkPassword($user_id, $password);

			if($result){
				$result = $userFunc->changePassword($user_id, $new_password);
				if($result){
					$response["success"] = 1;
				}else{
					$response["error"] = 2;
					$response["message"] = "Une erreur s'est produite lors de l'enregistrement du mot de passe";
				}
			}else{
				$response["error"] = 1;
				$response["message"] = "Mot de passe incorrect";
			}

			echo json_encode($response);

			break;

		case 'changeAccess':

			$user_id = $_POST['userId'];
			$access = $_POST['access'];
			$password = $_POST['password'];
			$result = $userFunc->checkPassword($user_id, $password);
			if($result){
				$accessResult = $userFunc->changeAccess($user_id, $access);

				if($accessResult){
					$response["success"] = 1;
				}else{
					$response["error"] = 2;
					$response["message"] = "Une erreur s'est produite lors du changement d'état de l'access";
				}
			}else{
				$response["error"] = 1;
				$response["message"] = "Mot de passe incorrect";
			}
			echo json_encode($response);
			BREAK;
        
        case "deleteAccount":
            
            $user_id = $_POST['userId'];
            $password = $_POST['password'];
        
            $result = $userFunc->deleteAccount($user_id);
        
            if($result){
					$response["success"] = 1;
            }else{
					$response["error"] = 1;
					$response["message"] = "Une erreur s'est produite lors de la suppression du compte";
            }
        
            echo json_encode($response);
			BREAK;
        
		default : 
			// le tag n'existe pas 
			echo "Requête invalide";
    }
} else {
    echo "Accès refusé"; // le tag n'est pas spécifié 
}
?>
