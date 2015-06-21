<?php
/** 
 * Chaque requête sera identifier par TAG
 * Les réponses seront données en JSON
 */
// Attention utiliser GET pour verifier directement en HTTP et utiliser POST pour l'appli
// Verification des requêtes sous la forme de GET 
if (isset($_GET['tag']) && $_GET['tag'] != '') {
    // RECUPERER LE TAG
    $tag = $_GET['tag'];

	// IMPORTER LES FONCTIONS DES CLASSES
	require_once 'include/DB_UserFunctions.php';
	$userFunc = new DB_UserFunctions();
	
	require_once 'include/DB_TripFunctions.php';
	$tripFunc = new DB_TripFunctions();

    // Tableau associatif qui sera envoyé au JSON
    $response = array("tag" => $tag, "success" => 0, "error" => 0);

    // Verification des TAGS
	switch ($tag){
		
		case 'searchUser' : 
		
			$query = $_GET['query'];			
			
			$result = $userFunc->getUserByLastNameAndFirstName($query);
			if($result){
				foreach($result as $row){

						if($row["user_link_avatar"]){
							$data = @file_get_contents($row["user_link_avatar"]);
							if($data != false){
								$picture = base64_encode($data);
							}else{
								$picture = "";
							}
						}else{
							$picture = "";
						}
						
						$response['user'][] = array('user_id' => $row['user_id'],
													 'user_last_name' => $row['user_last_name'],
													 'user_first_name' => $row['user_first_name'],
													 'user_birthday' => $row['user_birthday'],
													 'user_country' => $row['user_country'],
													 'user_city' => $row['user_city'],
													 'user_avatar' => $picture
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
