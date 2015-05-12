<?php
/**
 * chaque requête sera identifier par TAG
 * Les réponses seront données en JSON
 */
 
// Verification des requêtes sous la forme de GET 
if (isset($_POST['tag']) && $_POST['tag'] != '') {

    // RECUPERER LE TAG
    $tag = $_POST['tag'];

	// IMPORTER LES FONCTIONS DE LA CLASSE DB_TripFunctions
	require_once 'include/DB_TripFunctions.php';
	$tripFunc = new DB_TripFunctions();

    // réponse en Array
    $response = array("tag" => $tag, "success" => 0, "error" => 0);
	
	switch ($tag){

		case "addTrip" :
		
			if(isset($_POST['user_id']) && isset($_POST['trip_name']) && isset($_POST['trip_country'])){
				
				// Les champs obligatoires
				$user_id = $_POST['user_id'];
				$trip_country = $_POST['trip_country'];
				$trip_name = $_POST['trip_name'];
				
				// Les champs optionnels	
				$description = isset($_POST['description'])?$_POST['description']:"";
				
				// Récupérer l'identifiant de l'utilisateur grâce à son adresse mail
				if ($user_id) {
					$trip = $tripFunc->addTrip($trip_country, $trip_name, $description, $user_id);
						if ($trip) {
							// Si le voyage a été enregistrer 
							$response["success"] = 1;
							$response["message"] = "Enregistrement du voyage réussi";
							echo json_encode($response);
						} else {
							// Si le voyage n'a pas pu être enregistrer donc envoyer un message d'erreur
							$response["error"] = 1;
							$response["error_msg"] = "Le voyage n'a pas été enregistré";
							echo json_encode($response);
						}
				} else {
					$response["error"] = 2;
					$response["error_msg"] = "L'email de l'utilisateur n'existe pas ou est incorrect.";
					echo json_encode($response);	
				}
			}else{
				$response["error"] = 3;
				$response["error_msg"] = "Paramètre(s) manquant(s) ou erroné(s)";
				echo json_encode($response);
			}
			BREAK;
			
		case "getMyTripsList" :
			$user_id = $_POST['user_id'];
			$result = $tripFunc->getTripList($user_id);
			
			foreach($result as $row){
				 $response['trip'][] = array('trip_id' => $row['trip_id'],
											 'trip_name' => $row['trip_name'],
											 'trip_country' => $row['trip_country'],
											 'trip_description' => $row['trip_description'],
											 'trip_created_at' => $row['trip_created_at'],
											 'comment_count' => $row['comment_count'],
											 'photo_count' => $row['photo_count']
								 );
			}
			$response["success"] = 1;
			echo json_encode($response);
			BREAK;
		
		case "getTenTrips" :
			
			$userId = $_POST['user_id'];
			$result = $tripFunc->getTenTrips($userId);
			 
			
			if($result){
				foreach($result as $row){
					 $response['trip'][] = array('trip_id' => $row['trip_id'],
												 'trip_name' => $row['trip_name'],
												 'trip_country' => $row['trip_country'],
												 'trip_description' => $row['trip_description'],
												 'trip_created_at' => $row['trip_created_at'],
												 'user_last_name' => $row['user_last_name'],
												 'user_first_name' => $row['user_first_name'],
												 'comment_count' => $row['comment_count'],
												 'photo_count' => $row['photo_count']
									 );
				}
				$response["success"] = 1;
				$response["message"] = " Récupération des 10 voyages [OK]";
				echo json_encode($response);
			}else{
				$response["error"] = 1;
				$response["message"] = " Erreur lors de la récupération de la liste de voyages aléatoires";
				echo json_encode($response);
			}
			BREAK;

		case "getTrip" :

			$trip_id = $_POST['trip_id'];

			$result = $tripFunc->getTrip($trip_id);

			if($result){

				$successPlace = false;
				$successComment = false;

				$response["success"] = 1;
				$response['trip']['trip_id'] = $result['trip_id'];
				$response['trip']['trip_name'] = $result['trip_name'];
				$response['trip']['trip_country'] = $result['trip_country'];
				$response['trip']['trip_description'] = $result['trip_description'];
				$response['trip']['trip_created_at'] = $result['trip_created_at'];
				$response['trip']['user_last_name'] = $result['user_last_name'];
				$response['trip']['user_first_name'] = $result['user_first_name'];
				$response['trip']['user_id'] = $result['user_id'];
				
				$resultPlace = $tripFunc->getPlaceList($trip_id);

				if($resultPlace){
					$successPlace = true;
					foreach ($resultPlace as $place) {

						$response['place'][] = array('place_id' => $place['place_id'] ,
													 'place_name' => $place['place_name'] ,
													 'place_address' => $place['place_address'] ,
													 'place_description' => $place['place_description'] ,
													 'category_id' => $place['category_id'] );
					}									
				}

				$resultComment = $tripFunc->getCommentList($trip_id);

				if($resultComment){
					$successComment = true;
					foreach ($resultComment as $comment) {

						$response['comment'][] = array('comment_id' => $comment['comment_id'] ,
													   'comment_message' => $comment['comment_message'] ,
													   'comment_added_datetime' => $comment['comment_added_datetime'] ,
													   'trip_id' => $comment['trip_id'],
													   'user_id' => $comment['user_id'] );
					}									
				}

				if($successPlace && $successComment){
					$response["success"] = 1;
				}else if($successPlace){
					$response["success"] = 2;
					$response["message"] = "L'utilisateur n'a pas de commentaire";
				}else if($successComment){
					$response["success"] = 3;
					$response["message"] = "L'utilisateur n'a pas de lieu";
				}else{
					$response["success"] = 4;
					$response["message"] = "L'utilisateur n'a ni lieu ni commentaire";
				}
				
				echo json_encode($response);

			}else{
				$response['error'] = 1;
				$response['message'] = "erreur lors de la recuperation du voyage";
				echo json_encode($response);
			}
			BREAK;
		case "deleteTrip" :
		
			if( (isset($_POST['email'])) && (isset($_POST['trip_id'])) ) {
				$trip_id = $_POST['trip_id'];
				$user_id = $tripFunc->getUserIdByEmail($_POST['email']);
				if($user_id){
					$result = $tripFunc->removeTrip($trip_id,$user_id);
					$response["success"] = 1;
					$response["error_msg"] = "le voyage a été supprimé";
					echo json_encode($response);
				}else{
					$response["error"] = 1;
					$response["error_msg"] = "Erreur lors de la suppression du voyage";
					echo json_encode($response);
				}	
			}else{
				$response["error"] = 2;
				$response["error_msg"] = "Paramètre(s) manquant(s) ou erroné(s)";
				echo json_encode($response);
			}
			BREAK;

		case "addComment" :
			
			if(isset($_POST['comment_message']) && isset($_POST['trip_id']) && isset($_POST['user_id'])){
				
				$comment_message = $_POST['comment_message'];
				$trip_id = $_POST['trip_id'];
				$user_id = $_POST['user_id'];
				
				$result = $tripFunc->addComment($comment_message, $trip_id, $user_id);
				if($result){
					$response["success"] = 1;
					$response["error_msg"] = "Le commentaire a bien été ajouté";
					echo json_encode($response);
				}else{
					$response["error"] = 1;
					$response["error_msg"] = "Erreur lors de l'ajout du commentaire";
					echo json_encode($response);	
				}
				
			}else{
				$response["error"] = 2;
				$response["error_msg"] = "Paramètre(s) manquant(s) ou erroné(s)";
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