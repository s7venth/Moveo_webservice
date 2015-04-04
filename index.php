<?php
/**
 * 
 * chaque requete sera identifier par TAG
 * Les reponses seront données en JSON
*/
  /**
 * Verification des requetes sous la forme de GET 
 */
if (isset($_POST['tag']) && $_POST['tag'] != '') {
    // Recuperer le TAG
    $tag = $_POST['tag'];

    // include db handler
    require_once 'include/DB_Functions.php';
    $db = new DB_Functions();

    // response Array
    $response = array("tag" => $tag, "success" => 0, "error" => 0);

    // Verification des TAGS
    if ($tag == 'login') {/*
        // Request type is check Login
        $email = $_POST['email'];
        $password = $_POST['password'];

        // check for user
        $user = $db->getUserByEmailAndPassword($email, $password);
        if ($user != false) {
            // user found
            // echo json with success = 1
            $response["success"] = 1;
            $response["uid"] = $user["unique_id"];
            $response["user"]["name"] = $user["name"];
            $response["user"]["email"] = $user["email"];
            $response["user"]["created_at"] = $user["created_at"];
            $response["user"]["updated_at"] = $user["updated_at"];
            echo json_encode($response);
        } else {
            // user not found
            // echo json with error = 1
            $response["error"] = 1;
            $response["error_msg"] = "Incorrect email or password!";
            echo json_encode($response);
        } */
    } else if ($tag == 'register') { // si le TAG est register(Inscription)
        
        $name = $_POST['name'];
		$firstName = $_POST['firstName'];
        $email = $_POST['email'];
        $password = $_POST['password'];

        // Verifier si l'utilisateur existe
        if ($db->isUserExisted($email)) {
            // L'utilisateur existe donc envoyer un message d'erreur
            $response["error"] = 2;
            $response["error_msg"] = "L'utilisateur existe deja";
            echo json_encode($response);
        } else {
            // L'utilisateur n'existe pas donc enregistrer l'utilisateur
            $user = $db->storeUser($name, $firstName, $email, $password);
            if ($user) {
                // Si l'utilisateur a été enregister 
                echo "Votre compte ";
				$response["success"] = 1;
                $response["error_msg"] = "Enregistrement réussi";
                echo json_encode($response);
            } else {
                // Si l'utilisateur n'a pas pu être enregister donc envoyer un message d'erreur
                $response["error"] = 1;
                $response["error_msg"] = "nom : ".$name." prenom : ".$firstName." mail : ".$email." mot de passe : ".$password;
                echo json_encode($response);
            }
        }
    } else {
        echo "Requête invalide";
    }
} else {
    echo "Accès refusé";
}
?>
