<?php
/**
 * 
 * chaque requete sera identifier par TAG
 * Les reponses seront données en JSON
*/
  /**
 * Verification des requetes sous la forme de GET 
 */
if (isset($_GET['tag']) && $_GET['tag'] != '') {
    // RECUPERER LE TAG
    $tag = $_GET['tag'];

	// IMPORTER LES FONCTIONS DE LA CLASSE DB_TripFunctions
	require_once 'include/DB_TripFunctions.php';
	$tripfunc = new DB_TripFunctions();

    // response Array
    $response = array("tag" => $tag, "success" => 0, "error" => 0);
	
	switch ($tag){
		case "addTrip" :
		
			if(isset($_GET['country'])&&isset($_GET['city'])&&isset($_GET['email'])){
				
				// Les champs obligatoires
				$country = $_GET['country'];
				$city = $_GET['city'];
				$email = $_GET['email'];
				
				// Les champs optionnels	
				$description = isset($_GET['description'])?$_GET['description']:"";
				
				// Recuperer l'id de l'utilisateur grace à son adresse mail
				$user_id = $tripfunc->getUserIdByEmail($email);
				if ($user_id) {
					$trip = $tripfunc->storeTrip($country,$city,$description,$user_id);
						if ($trip) {
							// Si le voyage a été enregister 
							$response["success"] = 1;
							$response["message"] = "Enregistrement du voyage réussi";
							echo json_encode($response);
						} else {
							// Si le voyage n'a pas pu être enregister donc envoyer un message d'erreur
							$response["error"] = 1;
							$response["error_msg"] = "le voyage n'a pas été enregisté";
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
			
		case "get" :
		
			$result = $tripfunc->getTenTrip();
			foreach($result as $row){
				 $response[] = array('country' => $row['trip_country'],
										'city' => $row['trip_city'],
								 'description' => $row['trip_description'],
								 'created_at' => $row['trip_created_at'],
								 'author_firstname' => $row['user_name'],
								 'author_name' => $row['user_firstname']
								 );
			}
			$response["success"] = 2;
			echo json_encode($response);
			BREAK;
				
		case "delete" :
		
			if( (isset($_GET['email'])) && (isset($_GET['trip_id'])) ) {
				$trip_id = $_GET['trip_id'];
				$user_id = $tripfunc->getUserIdByEmail($_GET['email']);
				if($user_id){
					$result = $tripfunc->removeTripByIdTripAndIdUser($trip_id,$user_id);
					$response["success"] = 5;
					$response["error_msg"] = "le voyage a été supprimé";
					echo json_encode($response);
				}
			}
			BREAK;
				
		default : 
			echo "Requête invalide";
	}
}else{
	echo "Accès refusé";
}
?>