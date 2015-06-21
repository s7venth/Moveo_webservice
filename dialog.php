<?php
/** 
 * Chaque requête sera identifier par TAG
 * Les réponses seront données en JSON
 */
// Attention utiliser GET pour verifier directement en HTTP et utiliser POST pour l'appli
// Verification des requêtes sous la forme de GET 
if (isset($_POST['tag']) && $_POST['tag'] != '') {
    // RECUPERER LE TAG
    $tag = $_POST['tag'];

	// IMPORTER LES FONCTIONS DE LA CLASSE DB_UserFunctions
	require_once 'include/DB_DialogFunctions.php';
	$dialogFunc = new DB_DialogFunctions();

    // Tableau associatif qui sera envoyé au JSON
    $response = array("tag" => $tag, "success" => 0, "error" => 0);

    // Verification des TAGS
	switch ($tag){
		
		case 'addDialog' : // ajouter un message avec l'expéditeur et le récepteur 
		
			$user_id = $_POST['userId'];
			$recipient_id = $_POST['recipientId'];
			$message = $_POST['message'];
			
			$result = $dialogFunc->addDialog($user_id, $recipient_id, $message);
			
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
			
			$result = $dialogFunc->readMessage($user_id, $recipient_id, $sent_datetime);
			
			if($result){
				$response["success"] = 1;
			}else{
				$response["error"] = 1;
				$response["message"] = "Erreur lors du changement d'etat du message";
			}

			echo json_encode($response);

			BREAK;
		
		case 'getInbox' :
		
			// Recuperation de la boite de reception
			$inbox = $dialogFunc->getInbox($userId);

			if($inbox){
				
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
				$response["success"] = 1;
			}else{
				$response["error"] = 1;
				$response["message"] = "Erreur lors de la recuperation du inbox";
			}	
			
			echo json_encode($response);
			
			BREAK;
		
		case 'getOutbox':

			$sendbox = $dialogFunc->getSendbox($userId);
			if($sendbox){ 
				
				foreach($sendbox as $message) {
					$response["outbox"][] = array(
													"recipient_id" => $message["recipient_id"],
													"recipient_last_name" => $message["recipient_last_name"],
													"recipient_first_name" => $message["recipient_first_name"],
													"message" => $message["message"],
													"sent_datetime" => $message["sent_datetime"]
												 );
				}
				$response["success"] = 1;
			}else{
				$response["error"] = 1;
				$response["message"] = "Erreur lors de la recuperation du outbox";
			}
		
			echo json_encode($response);
			
			BREAK;
		
		case 'removeDialogInbox' : // supprimer un message de la boite de réception
		
			$user_id = $_POST['userId'];
			$recipient_id = $_POST['recipientId'];
			$sent_datetime = $_POST['sentDatetime'];

			$result = $dialogFunc->removeMessageInbox($user_id, $recipient_id, $sent_datetime);
			
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
			
			$result = $dialogFunc->removeMessageSendbox($user_id, $recipient_id, $sent_datetime);
			
			if($result){
				$response["success"] = 1;
			}else{
				$response["error"] = 1;
				$response["message"] = "Erreur lors de la suppression du message d'envoi";
			}

			echo json_encode($response);

			BREAK;
	
		default : 
			// le tag n'existe pas 
			echo "Requête invalide";
    }
	$dialogFunc = null;
} else {
    echo "Accès refusé"; // le tag n'est pas spécifié 
}
?>
