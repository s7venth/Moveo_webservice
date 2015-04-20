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
	$tripfunc = new DB_TripFunctions();

    // réponse en Array
    $response = array("tag" => $tag, "success" => 0, "error" => 0);
	
	switch ($tag){

		case "addTrip" :
		
			if(isset($_POST['country'])&&isset($_POST['city'])&&isset($_POST['email'])){
				
				// Les champs obligatoires
				$country = $_POST['country'];
				$city = $_POST['city'];
				$email = $_POST['email'];
				
				// Les champs optionnels	
				$description = isset($_POST['description'])?$_POST['description']:"";
				
				// Recuperer l'id de l'utilisateur grace à son adresse mail
				$user_id = $tripfunc->getUserIdByEmail($email);
				if ($user_id) {
					$trip = $tripfunc->addTrip($country,$city,$description,$user_id);
						if ($trip) {
							// Si le voyage a été enregistrer 
							$response["success"] = 1;
							$response["message"] = "Enregistrement du voyage réussi";
							echo json_encode($response);
						} else {
							// Si le voyage n'a pas pu être enregistrer donc envoyer un message d'erreur
							$response["error"] = 1;
							$response["error_msg"] = "le voyage n'a pas été enregistré";
							echo json_encode($response);
						}
				} else {
					$response["error"] = 2;
					$response["error_msg"] = "L'email de l'utilisateur n'existe pas ou est incorrect.";
					echo json_encode($response);	
				}
			}else{
				$response["error"] = 3;
				$response["error_msg"] = "Les informations sur le pays et la ville du voyage ainsi que l'email de l'utilisateur sont obligatoires.";
				echo json_encode($response);
			}
			BREAK;
			
		case "getTenTrips" :
		
			$result = $tripfunc->getTenTrips();
			foreach($result as $row){
				 $response['trip'][] = array(  'trip_id' => $row['trip_id'],
										   'trip_name' => $row['trip_name'],
										'trip_country' => $row['trip_country'],
								    'trip_description' => $row['trip_description'],
								     'trip_created_at' => $row['trip_created_at'],
							        'user_last_name' => $row['user_last_name'],
								         'user_first_name' => $row['user_first_name']
								 );
			}
			$response["success"] = 1;
			echo json_encode($response);
			BREAK;
			
		case "deleteTrip" :
		
			if( (isset($_POST['email'])) && (isset($_POST['trip_id'])) ) {
				$trip_id = $_POST['trip_id'];
				$user_id = $tripfunc->getUserIdByEmail($_POST['email']);
				if($user_id){
					$result = $tripfunc->removeTrip($trip_id,$user_id);
					$response["success"] = 5;
					$response["error_msg"] = "le voyage a été supprimé";
					echo json_encode($response);
				}else{
					$response["error"] = 4;
					$response["error_msg"] = "Erreur lors de la suppression du voyage";
					echo json_encode($response);
				}	
			}else{
				$response["error"] = 5;
				$response["error_msg"] = "Paramètre manquant";
				echo json_encode($response);
			}
			BREAK;

		case "addComment" :
			
			$comment_message = $_POST['comment_message'];
			$trip_id = $_POST['trip_id'];
			$user_id = $_POST['user_id'];
			
			$result = $tripfunc->addComment($comment_message, $trip_id, $user_id);
			if($result){
				$response["success"] = 6;
				$response["error_msg"] = "Le commentaire a bien été ajouté";
				echo json_encode($response);
			}else{
				$response["error"] = 5;
				$response["error_msg"] = "Erreur lors de l'ajout du commentaire";
				echo json_encode($response);	
			}
			BREAK;
			
		default : 
			echo "Requête invalide";
	}
}else{
	echo "Accès refusé";
}
?>