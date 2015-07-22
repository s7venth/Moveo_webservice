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
				$country = addslashes($_POST['trip_country']);
				$name = addslashes($_POST['trip_name']);
				$cover = $_POST['cover'];

				$date = new DateTime(null, new DateTimeZone('Europe/Paris'));
        		$date->add(new DateInterval('PT5M20S'));
        		$date =  $date->format('Y-m-d H:i:s');
				

				// Les champs optionnels	
				$description = isset($_POST['description'])?addslashes($_POST['description']):"";

				$photo_link = "img/".substr(sha1(rand()),10,10).".jpg";
			
				$photo =  $tripFunc->base64_to_jpeg("data:image/jpg;base64,".$cover, $photo_link);
			
				$trip = $tripFunc->addTrip($country, $name, $description, $date, $photo_link, $user_id);

				if ($trip) {
					// Si le voyage a été enregistrer 
					$response["success"] = 1;
					$response["trip_id"] = $trip;
					$response["date"] = "".$date;
                    $response["photo_link"] = $photo_link;
				} else {
					// Si le voyage n'a pas pu être enregistrer donc envoyer un message d'erreur
					$response["error"] = 1;
					$response["error_msg"] = "Le voyage n'a pas été enregistré";
				}
			
			}else{
				$response["error"] = 3;
				$response["error_msg"] = "Paramètre(s) manquant(s) ou erroné(s)";
			}

			echo json_encode($response);

			BREAK;
			
		case "modifyDescription" :
			$trip_id = $_POST['trip_id'];
			$description = addslashes($_POST['description']);

			$result = $tripFunc->modifyDescription($trip_id, $description);

			if($result){
				$response["success"] = 1;
			}else{
				$response["error"] = 1;
				$response["message"] = "Erreur lors de la modification de la description";
			}

			echo json_encode($response);
			BREAK;
		
		// RECUPERER LES VOYAGES DE L'UTILISATEUR 
		case "getTripList" :
			$user_id = $_POST['user_id'];
			$result = $tripFunc->getTripList($user_id);
			
			if($result){
				foreach($result as $row){

					/*if($row["link_cover"]){
						$data = @file_get_contents($row["link_cover"]);
						if($data != false){
							$picture = base64_encode($data);
						}else{
							$picture = "";
						}
					}else{
						$picture = "";
					}*/

					 $response['trip'][] = array('trip_id' => $row['trip_id'],
												 'trip_name' => $row['trip_name'],
												 'trip_country' => $row['trip_country'],
												 'trip_cover' => $row["link_cover"],
												 'comment_count' => $row['comment_count'],
												 'photo_count' => $row['photo_count']
									   			);
				    
				}
				$response["success"] = 1;
				echo json_encode($response);
			}else{
				$response["error"] = 1;
				$response["message"] = "Erreur lors de la recuperation du voyage";
				echo json_encode($response);

			}
			BREAK;
		
		// RECUPERER 10 VOYAGES ALEATOIRE
		case "getTenTrips" :
			
			$userId = $_POST['user_id'];
			$result = $tripFunc->getTenTrips($userId);
			 
			
			if($result){
				foreach($result as $row){

					/*if($row["link_cover"]){
						$data = @file_get_contents($row["link_cover"]);
						if($data != false){
							$picture = base64_encode($data);
						}else{
							$picture = "null";
						}

					}else{
						$picture = "null";
					}*/

					$response['trip'][] = array('trip_id' => $row['trip_id'],
												'trip_name' => $row['trip_name'],
												'trip_country' => $row['trip_country'],
												'trip_cover' => $row["link_cover"],
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

				$response["success"] = 1;
				$response['trip']['trip_id'] = $result['trip_id'];
				$response['trip']['trip_name'] = $result['trip_name'];
				$response['trip']['trip_country'] = $result['trip_country'];
				$response['trip']['trip_description'] = $result['trip_description'];
				$response['trip']['trip_created_at'] = $result['trip_created_at'];
				$response['trip']['user_last_name'] = $result['user_last_name'];
				$response['trip']['user_first_name'] = $result['user_first_name'];

				$response['trip']['user_id'] = $result['user_id'];
				if($result["link_cover"]){
					$data = @file_get_contents($result["link_cover"]);
					if($data != false){
						$picture = base64_encode($data);
					}else{
						$picture = "null";
					}
				}else{
					$picture = "null";
				}
				$response['trip']['trip_cover'] = $picture;
				
				
				// LIEU DE RESTAURATION 
				$resultFooding = $tripFunc->getPlaceListByCategoryId($trip_id, 1);
				
					if($resultFooding){
				
					foreach ($resultFooding as $fooding) {

						$response['fooding'][] = array('place_id' => $fooding['place_id'] ,
													 'place_name' => $fooding['place_name'] ,
													 'place_address' => $fooding['place_address'] ,
													 'place_description' => $fooding['place_description'] ,
													 'category_id' => $fooding['category_id']);
					}									
				}else{
					$response['fooding'] = 0;
				}
				
				// LIEU DE SHOPPING 
				$resultShopping = $tripFunc->getPlaceListByCategoryId($trip_id, 2);
				
				if($resultShopping){
					
					foreach ($resultShopping as $shopping) {

						$response['shopping'][] = array('place_id' => $shopping['place_id'] ,
													 'place_name' => $shopping['place_name'] ,
													 'place_address' => $shopping['place_address'] ,
													 'place_description' => $shopping['place_description'] ,
													 'category_id' => $shopping['category_id']);
					}
					
				}else{
					$response['shopping'] = 0;
				}
				
				// LIEU DE LOISIRS 
				$resultLeisure= $tripFunc->getPlaceListByCategoryId($trip_id, 3);
				
				if($resultLeisure){
			
					foreach ($resultLeisure as $leisure) {

						$response['leisure'][] = array('place_id' => $leisure['place_id'] ,
													 'place_name' => $leisure['place_name'] ,
													 'place_address' => $leisure['place_address'] ,
													 'place_description' => $leisure['place_description'] ,
													 'category_id' => $leisure['category_id']);
					}	
					
				}else{
					$response['leisure'] = 0;
				}
				
				echo json_encode($response);

			}else{
				$response['error'] = 1;
				$response['message'] = "erreur lors de la recuperation du voyage";
				echo json_encode($response);
			}
			BREAK;
			
		case "deleteTrip" :
		

			$trip_id = $_POST['trip_id'];
			$user_id = $_POST['user_id'];
			$result = $tripFunc->removeTrip($trip_id, $user_id);

			if($result){
				$response["success"] = 1;
			}else{
				$response["error"] = 1;
				$response["error_msg"] = "Erreur lors de la suppression du voyage";
			}	

			echo json_encode($response);

			BREAK;

		case "addComment" :
			
			if(isset($_POST['comment_message']) && isset($_POST['trip_id']) && isset($_POST['user_id'])){
				
				$comment_message = addslashes($_POST['comment_message']);
				$trip_id = $_POST['trip_id'];
				$user_id = $_POST['user_id'];
				
				$result = $tripFunc->addComment($comment_message, $trip_id, $user_id);
				if($result){
					$response["success"] = 1;
				}else{
					$response["error"] = 1;
					$response["message"] = "Erreur lors de l'ajout du commentaire";
				}
				
			}else{
				$response["error"] = 2;
				$response["message"] = "Paramètre(s) manquant(s) ou erroné(s)";
			}
			
			echo json_encode($response);

			BREAK;
        
		case "modifyComment" :
        
            $comment_id = $_POST['comment_id'];
            $comment_message = addslashes($_POST['message']);
        
            $result = $tripFunc->modifyComment($comment_message, $comment_id);
            
            if($result){
                $response["success"] = 1;
            }else{
                $response["error"] = 1;
                $response["message"] = "Erreur lors de la modification du message";
            }

            echo json_encode($response);

            BREAK;
        
		case "deleteComment" :
			
			$comment_id = $_POST['comment_id'];
			$result = $tripFunc->removeComment($comment_id);
			
			if($result){
				$response["success"] = 1;
			}else{
				$response["error"] = 1;
				$response["message"] = "Erreur lors de la suppression du commentaire";
			}
			
			echo json_encode($response);
			
			BREAK;

		case "getCommentList" :

			$trip_id = $_POST['trip_id'];

			$result = $tripFunc->getCommentList($trip_id);

			if($result){

				foreach ($result as $comment) {
				
					/*if($comment["user_link_avatar"]){
						$data = @file_get_contents($comment["user_link_avatar"]);
						if($data != false){
							$picture = base64_encode($data);
						}else{
							$picture = "";
						}
					}else{
						$picture = "";
					}*/

					$response['comment'][] = array('comment_id' => $comment['comment_id'] ,
													   'comment_message' => $comment['comment_message'] ,
													   'comment_added_datetime' => $comment['comment_added_datetime'] ,
													   'trip_id' => $comment['trip_id'],
													   'user_id' => $comment['user_id'],
													   'user_last_name' => $comment['user_last_name'],
													   'user_first_name' => $comment['user_first_name'],
													   'user_link_avatar' => $comment["user_link_avatar"] );

				}

				$response['success'] = 1;

			}else{
				$response["error"] = 1;
				$response["error_msg"] = "Erreur lors de la récupération des photos";
			}

			echo json_encode($response);

			BREAK;

		case "getCommentListByUser" :

			$user_id = $_POST['user_id'];

			$result = $tripFunc->getCommentListByUser($user_id);

			if($result){

				foreach ($result as $comment) {

					$response['comment'][] = array('comment_id' => $comment['comment_id'] ,
													   'comment_message' => $comment['comment_message'] ,
													   'comment_added_datetime' => $comment['comment_added_datetime'] ,
													   'trip_id' => $comment['trip_id'],
													   'user_id' => $comment['user_id'],
													   'user_last_name' => $comment['user_last_name'],
													   'user_first_name' => $comment['user_first_name'],
													   'user_link_avatar' => $comment["user_link_avatar"] );
				}

				$response['success'] = 1;

			}else{
				$response["error"] = 1;
				$response["error_msg"] = "Erreur lors de la récupération des commentaires";
			}

			echo json_encode($response);

			BREAK;


		case "getPhotoGallery":

			$trip_id = $_POST['trip_id'];

			$result = $tripFunc->getPhotoGallery($trip_id);

			if($result){

				foreach ($result as $photo) {
				
					/*if($photo["photo_link"]){
						$data = @file_get_contents($photo["photo_link"]);
						if($data != false){
							$picture = base64_encode($data);
						}else{
							$picture = "";
						}
					}else{
						$picture = "";
					}*/

					$response['photo'][] = array('photo_id' => $photo['photo_id'] ,
												 'photo_added_date' => $photo['photo_added_date'] ,
												 'photo_link' => $photo["photo_link"] );
				}

				$response["success"] = 1;
				echo json_encode($response);

			}else{
				$response["error"] = 1;
				$response["message"] = "Erreur lors de la récupération des photos du voyages";
				echo json_encode($response);
			}								

			BREAK;

		case "addPlace":
			
			$place_name = $_POST['place_name'];
			$place_address = $_POST['place_address'];
			$place_description = $_POST['place_description'];
			$trip_id = $_POST['trip_id'];
			$category_id = $_POST['category_id'];

			$result = $tripFunc->addPlace($place_name, $place_address, $place_description, $trip_id, $category_id);

			if($result){
				$response["success"] = 1;
				$response["place_id"] = $result;
			}else{
				$response["error"] = 1;
				$response["message"] = "Erreur lors de l'enregistrement des lieux'";
			}

			echo json_encode($response);

			break;
        
        case "modifyPlace":
        
            $place_name = $_POST['place_name'];
            $place_address = $_POST['place_address'];
			$place_description = $_POST['place_description'];
            $place_id = $_POST['place_id'];
        
            $result = $tripFunc->modifyPlace($place_id, $place_name, $place_address, $place_description);
        
            if ($result) {
				$response["success"] = 1;
			} else {
				$response["error"] = 1;
				$response["message"] = "Erreur lors de la modification du lieu";
			}
        
            echo json_encode($response);

			break;
        
        case "deletePlace":
            
            $place_id = $_POST['place_id'];
        
            $result = $tripFunc->removePlace($place_id);
            
            if ($result) {
				$response["success"] = 1;
			} else {
				$response["error"] = 1;
				$response["message"] = "Erreur lors de la suppression du lieu";
			}
        
            echo json_encode($response);

			break;
            
		case "addPhoto":
			
			$photo_base_64 = $_POST['photo'];
			$trip_id = $_POST['trip_id'];
			$date = new DateTime(null, new DateTimeZone('Europe/Paris'));
        	$date->add(new DateInterval('PT5M20S'));
        	$date =  $date->format('Y-m-d H:i:s');
			$photo_link = "img/".substr(sha1(rand()),10,10).".jpg";
			
			$photo =  $tripFunc->base64_to_jpeg("data:image/jpg;base64,".$photo_base_64, $photo_link);
			
			if($photo){
				$result = $tripFunc->addPhoto($photo_link, $date, $trip_id);
			}else{
				$result = false;
			}

			if($result){
				$response["success"] = 1;
				$response["date"] = $date;
				$response["id"] = $result;
				$response["image"] = $photo_link;
			}else{
				$response["error"] = 1;
				$response["message"] = "Erreur lors de l'enregistrement de la photo";
			}

			echo json_encode($response);

			break;

		case "modifyCover" :

			$trip_id = $_POST['trip_id'];

			if($_POST['cover'] != "null" && $_POST['cover'] != ""){
				$link_avatar = $tripFunc->getCover($trip_id);
				if($link_avatar['link_cover'] == "" || $link_avatar['link_cover'] == null || $link_avatar['link_cover'] == "null" || $link_avatar == false){
					// Si l'utilisateur n'as pas de chemin vers une photo on crée un nouveau chemin 
					$link_cover = "img/".substr(sha1(rand()),10,10).".jpg";
					$avatar =  $tripFunc->base64_to_jpeg("data:image/jpg;base64,".$_POST['cover'], $link_cover);
					if(!$avatar){
						$link_cover = "null"; 
						$response["avatar"] = 0;
					}
				}else{
					
					$link_cover =  $link_avatar['link_cover'];
					$avatar =  $tripFunc->base64_to_jpeg("data:image/jpg;base64,".$_POST['cover'], $link_cover);
					// Si la création de la photo à echoué, le chemin est initialisé à vide
					if(!$avatar){
						$link_cover = "null"; 
						$response["avatar"] = 0;
					}

				}
			
			}else{
				$link_cover = null;
			}

			$result = $tripFunc->modifyCover($link_cover, $trip_id)	;
            
            if ($result) {
				$response["success"] = 1;
				$response["link_cover"] = $link_cover;
			} else {
				$response["error"] = 1;
				$response["message"] = "Erreur lors de la modification de la photo";
			}
            
            echo json_encode($response);
        
			break;
        
        case "deletePhoto":
            
            $photo_id = $_POST['photo_id'];
            
            $link_photo = $tripFunc->getPhotoLink($photo_id);
            
            if($link_photo){
                $delete_photo = unlink($link_photo['photo_link']);
            }
        
            if($delete_photo){
                $result = $tripFunc->deletePhoto($photo_id)	;
                if($result){
				    $response["success"] = 1;
                }else{
                    $response["error"] = 2;
                    $response["message"] = "Erreur lors de la suppression de l'image de la base";
                }
            }else{
                $response["error"] = 1;
                $response["message"] = "Erreur lors de la suppression de l'image dans le repertoire";
            }
            
            echo json_encode($response);
        
			break;
        
		case "reportPhoto":

			$photo_id = $_POST['photoId'];
			$user_id = $_POST['userId'];

			$result = $tripFunc->toReportPhoto($user_id, $photo_id);

			if($result){
				$response["success"] = 1;
			}else{
				$response["error"] = 1;
				$response["message"] = "Erreur lors de l'enregistrement du signalement";
			}

			echo json_encode($response);

			break;

		default : 
			// le tag n'existe pas 
			echo "Requête invalide";
    }
} else {
    echo "Accès refusé"; // le tag n'est pas spécifié 
}
?>