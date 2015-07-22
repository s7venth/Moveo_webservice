<?php
/**
 * Chaque requ�te sera identifier par TAG
 * Les r�ponses seront donn�es en JSON
 */
// Attention utiliser GET pour verifier directement en HTTP et utiliser POST pour l'appli
// Verification des requetes sous la forme de GET 
if (isset($_POST['tag']) && $_POST['tag'] != '') {
    // RECUPERER LE TAG
    $tag = $_POST['tag'];

    // IMPORTER LES FONCTIONS DE LA CLASSE DB_moderatorFunctions
    require_once 'include/DB_ModeratorFunctions.php';
    $moderatorFunc = new DB_moderatorFunctions();

    // Tableau associatif qui sera envoy� au JSON
    $response = array("tag" => $tag, "success" => 0, "error" => 0);

    // Verification des TAGS
    switch ($tag) {

        case 'login': // Se connecter � l'application (Renvoie les informations du moderateur)

            // les informations des champs "email" et "mot de passe" du formulaire de connexion
            $email = $_POST['email'];
            $password = $_POST['password'];

            // verifier si le moderateur existe
            $moderator = $moderatorFunc->getmoderatorByEmailAndPassword($email, $password);

            if ($moderator) {

                $moderatorId = $moderator["moderator_id"];

                $response["moderator"]["moderator_id"] = $moderatorId;
                $response["moderator"]["moderator_name"] = $moderator["moderator_name"];
                $response["moderator"]["moderator_email"] = $moderator["moderator_email"];
                $response["moderator"]["is_admin"] = $moderator["is_admin"];

                echo json_encode($response);
            } else {
                // le mod�rateur n'existe pas : envoyer un objet json avec un message d'erreur
                $response["error"] = 1;
                $response["message"] = "L'email ou le mot de passe est incorrect"+$email+ " et "+$password;
                echo json_encode($response);
            }
            BREAK;

        case 'updateProfil':

            $moderator_id = $_POST['moderator_id'];
            $moderator_name = $_POST['moderator_name'];

            $result = $moderatorFunc->updatemoderator($moderator_id, $moderator_name);

            if ($result) {
                $response["success"] = 1;
                echo json_encode($response);
            } else {
                $response["error"] = 1;
                $response["message"] = "Erreur lors de l'enregistrement";
                echo json_encode($response);
            }
            BREAK;

        case 'getOthermoderator':

            $otherModerator_id = $_POST['otherModerator_id'];

            $result = $moderatorFunc->getOtherModerator($otherModerator_id);
            if ($result != false) {
                $response["success"] = 1;
                $response["otherModerator"]["moderator_name"] = $result["moderator_name"];
                echo json_encode($response);
            } else {
                $response["error"] = 1;
                $response["error_msg"] = "Erreur lors de la recuperation du moderateur";
                echo json_encode($response);
            }
            BREAK;

        case 'getUsers':

            $UsersList = $moderatorFunc->getUsers();
            if($UsersList) {
                $response["success"] = 1;
                foreach($UsersList as $user) {
                    $response["user"][] = array(
                        "user_id" => $user["user_id"],
                        "user_last_name" => $user["user_last_name"],
                        "user_first_name" => $user["user_first_name"],
                        "user_birthday" => $user["user_birthday"],
                        "user_email" => $user["user_email"],
                        "user_country" => $user["user_country"],
                        "user_city" => $user["user_city"]
                    );
                };
                echo json_encode($response);
            } else {
                $response["error"] = 1;
                $response["error_msg"] = "Erreur lors de la recuperation des utilisateurs";
                echo json_encode($response);
            }
            BREAK;

        case 'getModerators':

            $UsersList = $moderatorFunc->getModerators();
            if($UsersList) {
                $response["success"] = 1;
                foreach($UsersList as $user) {
                    $response["moderator"][] = array(
                        "moderator_id" => $user["moderator_id"],
                        "moderator_name" => $user["moderator_name"],
                        "moderator_email" => $user["moderator_email"],
                        "is_admin" => $user["is_admin"]
                    );
                };
                echo json_encode($response);
            } else {
                $response["error"] = 1;
                $response["error_msg"] = "Erreur lors de la recuperation des moderateurs";
                echo json_encode($response);
            }
            BREAK;

        case 'addDialog' : // ajouter un message avec l'exp�diteur et le r�cepteur


            $moderator_id = $_POST['moderator_id'];
            $recipient_id = $_POST['recipient_id'];
            $message = $_POST['message'];

            $result = $moderatorFunc->addDialog($moderator_id, $recipient_id, $message);

            if ($result) {
                $response["success"] = 1;
                $response["message"] = "Le message a ete ajoute avec succes";
                echo json_encode($response);
            } else {
                $response["error"] = 1;
                $response["message"] = "Erreur lors de l'ajout du dialogue";
                echo json_encode($response);
            }
            BREAK;

        case 'readMessage' : // lire un message avec l'exp�diteur et le r�cepteur


            $moderator_id = $_POST['message_id'];
            $recipient_id = $_POST['recipient_id'];
            $message = $_POST['message'];

            $result = $moderatorFunc->addDialog($moderator_id, $recipient_id, $message);

            if ($result) {
                $response["success"] = 1;
                $response["message"] = "Le message a �t� ajout� avec succ�s";
                echo json_encode($response);
            } else {
                $response["error"] = 1;
                $response["message"] = "Erreur lors de l'ajout du dialogue";
                echo json_encode($response);
            }
            BREAK;

        case 'forgetPassword' :
            $moderator_email = $_POST['moderator_email'];

            // Si le moderateur existe il faut lui envoyer un mail avec le nouveau mot de passe
            if ($moderatorFunc->ismoderatorExisted($moderator_email)) {

                $result = $moderatorFunc->generateNewPassword($moderator_email);

                if ($result) {
                    $response["success"] = 1;
                    $response["message"] = "L'email � �t� envoy�";
                    echo json_encode($response);
                } else {
                    $response["error"] = 1;
                    $response["message"] = "Erreur lors de l'envoie du mail";
                    echo json_encode($response);
                }
            } else {
                $response["error"] = 2;
                $response["message"] = "Cette adresse email n'existe pas";
                echo json_encode($response);
            }
            BREAK;

        default :
            // le tag n'existe pas
            echo "Requete invalide";
    }
} else {
    echo "Acces refuse"; // le tag n'est pas sp�cifi�
}
?>
