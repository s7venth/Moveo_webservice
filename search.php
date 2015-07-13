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

    // Tableau associatif qui sera envoyé au JSON
    $response = array("tag" => $tag, "success" => 0, "error" => 0);

    // Verification des TAGS
	switch ($tag){
		
		case 'searchUser' : 
		  	require_once 'include/DB_UserFunctions.php';
	        $userFunc = new DB_UserFunctions();
        	
			$query = $_POST['query'];	
			$userId = $_POST['userId'];			
			
			$result = $userFunc->getUserByLastNameAndFirstName($query, $userId);
			
			if($result){
				foreach($result as $row){

						/*if($row["user_link_avatar"]){
							$data = @file_get_contents($row["user_link_avatar"]);
							if($data != false){
								$picture = base64_encode($data);
							}else{
								$picture = "null" ;
							}
						}else{
							$picture = "null" ;
						}
						*/
						$response['user'][] = array('user_id' => $row['user_id'],
													 'user_last_name' => $row['user_last_name'],
													 'user_first_name' => $row['user_first_name'],
													 'trip_count' => $row['trip_count'],
													 'user_avatar' => $row["user_link_avatar"]
													);			
				}
				$response["success"] = 1;
			}else{
				$response["error"] = 1;
				$response["message"] = "Pas de résultat";
			}

			echo json_encode($response);

			BREAK;		
		
        case 'searchTrip' : 
		  
            require_once 'include/DB_TripFunctions.php';
	        $tripFunc = new DB_TripFunctions();
        	
        	$userId = $_POST['userId'];	
			$query = $_POST['query'];			
			
			$result = $tripFunc->getTripListByQuery($userId,$query);
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
			}else{
				$response["error"] = 1;
				$response["message"] = "Pas de résultat";
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
