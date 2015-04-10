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
    // Recuperer le TAG
    $tag = $_GET['tag'];

    // inclure la classe db_function 
	if (($tag == 'login') || ($tag == 'register')){
		require_once 'include/DB_UserFunctions.php';
		$userfunc = new DB_UserFunctions();
	}else if ($tag == 'trip'){
		require_once 'include/DB_TripFunctions.php';
		$tripfunc = new DB_TripFunctions();
	}
    // response Array
    $response = array("tag" => $tag, "success" => 0, "error" => 0);

    // Verification des TAGS
    if ($tag == 'login') {
        // les informations du formulaire de connexion
        $email = $_GET['email'];
        $password = $_GET['password'];

        // verifier si l'utilisateur existe
        $user = $tripfunc->getUserByEmailAndPassword($email, $password);
        if ($user != false) {
            // user found
            // echo json with success = 1
            $response["success"] = 1;
            $response["user"]["name"] = $user["user_name"];
            $response["user"]["firstname"] = $user["user_firstname"];
            echo json_encode($response);
        } else {
            // user not found
            // echo json with error = 1
            $response["error"] = 1;
            $response["error_msg"] = "L'email ou le mot de passe est incorrect";
            echo json_encode($response);
        } 
    } else if ($tag == 'register') { // si le TAG est register(Inscription)
        
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
    } else if ($tag == 'trip'){ 
		if(isset($_GET['option'])){
			$option = $_GET['option'];
			if($option == 'add'){
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
			}else if($option == 'get'){
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
			}else if($option == "delete"){
				if( (isset($_GET['email'])) && (isset($_GET['trip_id'])) ) {
					$trip_id = $_GET['trip_id'];
					$user_id = $tripfunc->getUserIdByEmail($_GET['email']);
					echo " id : ".$user_id;
					if($user_id){
						$result = $tripfunc->removeTripByIdTripAndIdUser($trip_id,$user_id);
						$response["success"] = 5;
						$response["error_msg"] = "le voyage a été supprimé";
						echo json_encode($response);
					}
				}
			}else{
				$response["error"] = 5 ;
				$response["error_msg"] = "Cette option n'existe pas";
				echo json_encode($response);
			}
		}else{
			$response["error"] = 4 ;
			$response["error_msg"] = "Option indisponible";
			echo json_encode($response);
		}
		
	}else {
        echo "Requête invalide";
    }
} else {
    echo "Accès refusé";
}
?>
