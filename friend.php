<?php
/** 
 * Chaque requête sera identifier par TAG
 * Les réponses seront données en JSON
 */
 
// Attention utiliser GET pour verifier directement en HTTP et utiliser POST pour l'application
// Verification des requêtes sous la forme de GET 
if (isset($_POST['tag']) && $_POST['tag'] != '') {
    
	// RECUPERATION DU TAG
    $tag = $_POST['tag'];

	// IMPORTER LES FONCTIONS DE LA CLASSE DB_TripFunctions
	require_once 'include/DB_FriendFunctions.php';
	$friendFunc = new DB_FriendFunctions();

    // Tableau associatif qui sera envoyé au JSON
    $response = array("tag" => $tag, "success" => 0, "error" => 0);

    // Verification des TAGS
	switch ($tag){
		
		// AJOUTER UN AMI
		case 'addFriend':
		
			// les informations des champs "email" et "mot de passe" du formulaire de connexion
			$friend_id = $_POST['friend_id'];
			$user_id = $_POST['user_id'];

			// verifier si l'utilisateur existe
			$result = $friendFunc->addFriend($user_id, $friend_id);
			if ($result) {
				// L'utilisateur existe : echo json avec success = 1
				$response["success"] = 1;
				$response["message"] = "Ami enregistré avec succès";
				echo json_encode($response);
			}else{
				// l'utilisateur n'existe pas : echo json avec error = 1
				$response["error"] = 1;
				$response["message"] = "Ami non ajouté";
				echo json_encode($response);
			} 
			BREAK;
		
		// RECUPERER LES DEMANDES D'AMIS + LA LISTE D'AMIS
		case 'getFriendsList': 
        
			$user_id = $_POST['user_id'];
			$result = $friendFunc->getFriendsList($user_id);
			if($result){
				foreach($result as $row){
				 $response[] = array('friend_name' => $row['user_name'],
									 'friend_firstname' => $row['user_firstname'],
									 'friend_is_accepted' => $row['is_accepted']
								 );
			}
			}
			
			$response["success"] = 1;
			echo json_encode($response);
			BREAK;
			
		// ACCEPTER UN AMI 
		case 'acceptFriend': 
			$friend_id = $_POST['friend_id'];
			$user_id = $_POST['user_id'];
			
			$result = $friendFunc->acceptFriend($user_id, $friend_id);
			
			if ($result != false) {
	
				$response["success"] = 1;
				
				echo json_encode($response);
			}else{
				
				$response["error"] = 1;
				$response["message"] = "Erreur lors de l'acceptation de la demande";
				echo json_encode($response);
			} 
			BREAK;
		
		// SUPPRIMER UN AMI
		case 'removeFriend': 
			$friend_id = $_POST['friend_id'];
			$user_id = $_POST['user_id'];
			
			$result = $friendFunc->removeFriend($user_id, $friend_id);
			
			if ($result != false) {
	
				$response["success"] = 1;
				
				echo json_encode($response);
				
			}else{
				
				$response["error"] = 1;
				$response["message"] = "Erreur lors de la suppression";
				echo json_encode($response);
				
			} 
			BREAK;
			
		// RECUPERATION DES INFORMATIONS D'UN AMI
		case 'getFriend': 
		
			$friend_id = $_POST['friend_id'];
			
			$result = $friendFunc->getFriend($friend_id);
		
			BREAK;
			
		default : 
			// le tag n'existe pas 
			echo "Requête invalide";
    }
} else {
    echo "Accès refusé"; // le tag n'est pas spécifié 
}
?>