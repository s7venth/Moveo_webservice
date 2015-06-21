<?php
/**
 * Chaque requête sera identifier par TAG
 * Les réponses seront données en JSON
 */
// Attention utiliser GET pour verifier directement en HTTP et utiliser POST pour l'appli
// Verification des requetes sous la forme de GET 
if (isset($_GET['tag']) && $_GET['tag'] != '') {
    // RECUPERER LE TAG
    $tag = $_GET['tag'];

    // IMPORTER LES FONCTIONS DE LA CLASSE DB_moderatorFunctions
    require_once 'include/DB_moderatorFunctions.php';
    $moderatorFunc = new DB_moderatorFunctions();

    // Tableau associatif qui sera envoyé au JSON
    $response = array("tag" => $tag, "success" => 0, "error" => 0);

    // Verification des TAGS
    switch ($tag) {

        case 'login': // Se connecter à l'application (Renvoie les informations de l'moderateur)

            // les informations des champs "email" et "mot de passe" du formulaire de connexion
            $email = $_GET['email'];
            $password = $_GET['password'];

            // verifier si le moderateur existe
            $moderator = $moderatorFunc->getmoderatorByEmailAndPassword($email, $password);

            if ($moderator) {

                if ($moderator["access_id"] == '1') {
                    $response["error"] = 3;
                    $response["message"] = "le compte n'est pas activé";
                } else if ($moderator["access_id"] == '3') {
                    $response["error"] = 4;
                    $response["message"] = "L'application est en maintenance";
                } else if ($moderator["access_id"] == '4') {
                    $response["error"] = 5;
                    $response["message"] = "Le compte est bloqué par l'administrateur";
                } else {

                    $moderatorId = $moderator["moderator_id"];

                    $successTrip = false;
                    $successFriend = false;
                    $successInbox = false;
                    $successSendbox = false;

                    $response["moderator"]["moderator_id"] = $moderatorId;
                    $response["moderator"]["moderator_name"] = $moderator["moderator_name"];
                    $response["moderator"]["moderator_email"] = $moderator["moderator_email"];
                    $response["moderator"]["access_id"] = $moderator["access_id"];
                }
                echo json_encode($response);
            } else {
                // le modérateur n'existe pas : envoyer un objet json avec un message d'erreur
                $response["error"] = 1;
                $response["message"] = "L'email ou le mot de passe est incorrect";
                echo json_encode($response);
            }
            BREAK;

        case 'register':

            $name = $_GET['name'];
            $email = $_GET['email'];
            $password = $_GET['password'];

            // Verifier si l'moderateur existe
            if ($moderatorFunc->isModeratorExisted($email)) {
                // Le moderateur existe donc envoyer un message d'erreur
                $response["error"] = 2;
                $response["error_msg"] = "L'moderateur existe deja";
                echo json_encode($response);
            } else {
                // L'moderateur n'existe pas donc l'enregistrer
                $moderator = $moderatorFunc->storeModerator($name, $email, $password);
                if ($moderator) {
                    // Si le moderateur a été enregistrer
                    $response["success"] = 1;
                    $response["error_msg"] = "Enregistrement réussi";
                    echo json_encode($response);
                } else {
                    // Si le moderateur n'a pas pu être enregistrer donc envoyer un message d'erreur
                    $response["error"] = 1;
                    $response["error_msg"] = "le moderateur n'a pas été enregistré";
                    echo json_encode($response);
                }
            }
            BREAK;

        case 'updateProfil':

            $moderator_id = $_GET['moderator_id'];
            $moderator_name = $_GET['moderator_name'];

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

            $otherModerator_id = $_GET['otherModerator_id'];

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

        case 'addDialog' : // ajouter un message avec l'expéditeur et le récepteur


            $moderator_id = $_GET['moderator_id'];
            $recipient_id = $_GET['recipient_id'];
            $message = $_GET['message'];

            $result = $moderatorFunc->addDialog($moderator_id, $recipient_id, $message);

            if ($result) {
                $response["success"] = 1;
                $response["message"] = "Le message a été ajouté avec succès";
                echo json_encode($response);
            } else {
                $response["error"] = 1;
                $response["message"] = "Erreur lors de l'ajout du dialogue";
                echo json_encode($response);
            }
            BREAK;

        case 'readMessage' : // ajouter un message avec l'expéditeur et le récepteur


            $moderator_id = $_GET['message_id'];
            $recipient_id = $_GET['recipient_id'];
            $message = $_GET['message'];

            $result = $moderatorFunc->addDialog($moderator_id, $recipient_id, $message);

            if ($result) {
                $response["success"] = 1;
                $response["message"] = "Le message a été ajouté avec succès";
                echo json_encode($response);
            } else {
                $response["error"] = 1;
                $response["message"] = "Erreur lors de l'ajout du dialogue";
                echo json_encode($response);
            }
            BREAK;

        case 'forgetPassword' :
            $moderator_email = $_GET['moderator_email'];

            // Si le moderateur existe il faut lui envoyer un mail avec le nouveau mot de passe
            if ($moderatorFunc->ismoderatorExisted($moderator_email)) {

                $result = $moderatorFunc->generateNewPassword($moderator_email);

                if ($result) {
                    $response["success"] = 1;
                    $response["message"] = "L'email à été envoyé";
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
            echo "Requête invalide";
    }
} else {
    echo "Accès refusé"; // le tag n'est pas spécifié 
}
?>
