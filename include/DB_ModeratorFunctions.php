<?php

/**
 * Created by IntelliJ IDEA.
 * User: Alexandre
 * Date: 21/06/2015
 * Time: 16:46
 */
class DB_ModeratorFunctions
{

    protected $db;
    protected $pdo;

    //constucteur
    function __construct() {
        require_once('include/DB_Connect.php');
        //Se connecter à la base de données
        $this->db = new DB_Connect();
        $this->pdo = $this->db->getPdo();
    }

    // fermer la base de données
    function __destruct() {
        $db = NULL;
    }

    public function closeDataBase(){
        $this->db = $this->db->close();
        $this->db = NULL;
    }


    /**
     * Enregistrer un nouveau moderateur
     * return vrai si l'ajout a réussi ou faux s'il a échoué
     */
    public function storeModerator($name,$email, $password) {
        $hash = $this->hashSSHA($password);
        $encrypted_password = $hash["encrypted"]; // mot de passe crypté
        $salt = $hash["salt"]; // clé pour la sécurité du mot de passe
        $validation_key_to_send = substr(sha1(rand()),10,20);
        $validation_key = sha1($validation_key_to_send);
        $result = $this->pdo->exec("INSERT INTO moderator(moderator_name, moderator_email, moderator_password, moderator_security_key, moderator_password, access_id)
									VALUES('$name', '$email', '$encrypted_password', '$salt', '$validation_key', '1')");

        // Vérifie si l'ajout a été un succès
        if ($result) {
            $moderator_id = $this->pdo->query("SELECT moderator_id FROM moderator WHERE moderator_email = '$email'");
            $moderator_id = $moderator_id->fetch();
            $a = mail($email, 'Activation de votre compte Moveo', 'Pour activer votre compte Moveo cliquer sur le lien suivant : http://127.0.0.1/Moveo_webservice/validation.php?key='.$validation_key_to_send.'&id='.$moderator_id['moderator_id']);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Mettre à jour les informations du moderateur
     * return vrai si la mise à jour a réussi ou faux si elle a échoué
     */
    public function updateModerator($moderator_id, $moderator_name) {
        $result = $this->pdo->query("UPDATE moderator
									SET moderator_name = '$moderator_name'
									WHERE moderator_id='$moderator_id'");

        // verifier si la mise à jour a été un succes
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Recupere tous les informations du moderateur
     * @param email et password
     * return les informations du moderateur ou false si c'est pas bon
     */
    public function getModeratorByEmailAndPassword($email, $password) {
        $result = $this->pdo->query("SELECT * FROM moderator WHERE moderator_email = '$email'");
        // compter le nombre de reponses (lignes)
        $resultModerator = $result->rowCount();
        if ($resultModerator > 0) {
            $result = $result->fetch();
            $key = $result['moderator_security_key'];
            $encrypted_password = $result['moderator_password'];
            $hash = $this->checkhashSSHA($key, $password);

            // verifier si les mots sont identiques
            if ($encrypted_password == $hash) {
                // si les mots de passes sont identiques envoyer les informations
                return $result;
            }
        } else {
            // L'moderateur n'existe pas
            return false;
        }
    }

    /**
     * Verifier si le moderateur existe
     * @param email
     * return vrai s'il existe, faux s'il n'existe pas
     */
    public function isModeratorExisted($email) {
        $result = $this->pdo->query("SELECT moderator_email
									 FROM moderator
									 WHERE moderator_email = '$email'");
        $resultEmail = $result->rowCount();

        if($resultEmail) {
            // le moderateur existe
            return true;
        } else {
            // le moderateur n'existe pas
            return false;
        }
    }

    /**
     * Récupérer les informations d'un autre moderateur grace à son identifiant
     * @param $moderator_id
     * return Les informations d'un autre moderateur
     */
    public function getOtherModerator($otherModerator_id) {
        $result = $this->pdo->query("SELECT moderator_name
									 FROM moderator
									 WHERE moderator_id = '$otherModerator_id'");
        $result = $result->fetch();

        if($result) {
            // l'moderateur existe
            return $result;
        } else {
            // l'moderateur n'existe pas
            return false;
        }
    }

    /**
     * ajoute un message avec les identifiants de l'expéditeur et le récepteur
     * attention, user_id est utilisé ici pour concordance avec la base de données
     * @param $moderator_id l'identifiant du moderateur (L'expéditeur)
     * @param $other_moderator_id l'identifiant de la personne à qui du moderateur envoi un message (récepteur)
     * @param $message Le message que souhaite envoyer l'expéditeur
     */
    public function addDialog($moderator_id, $recipient_id, $message){
        $result = $this->pdo->exec("INSERT INTO dialog(user_id, recipient_id, message, sent_datetime, read_by_recipient)
									VALUES('$moderator_id', '$recipient_id','$message', now(), '0')");

        // verifier si la requête a réalisé l'ajout
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Recupére tous les messages qu'a reçu l'moderateur
     * @param $moderator_id l'identifiant de l'moderateur
     *
     */
    public function getInbox($moderator_id){
        $result = $this->pdo->query("SELECT recipient_id, moderator_name as recipient_name, message, sent_datetime, read_by_recipient
                                     FROM dialog, moderator
                                     WHERE recipient_id = '$moderator_id'
                                     AND moderator.moderator_id = dialog.moderator_id
                                     AND remove_by_recipient = 0");

        $result = $result->fetchAll();

        if($result) {
            return $result;
        } else {
            return false;
        }
    }

    /**
     * Recupére tous les messages d'envoi du moderateur
     * @param $moderator_id l'identifiant du moderateur
     *
     */
    public function getSendbox($moderator_id){
        $result = $this->pdo->query("SELECT recipient_id, moderator_name as recipient_last_name, message, sent_datetime
                                     FROM dialog d, moderator m
                                     WHERE m.moderator_id = d.recipient_id
                                     AND d.moderator_id = '$moderator_id'
                                     AND remove_by_moderator = 0");

        $result = $result->fetchAll();

        if($result) {
            return $result;
        } else {
            return false;
        }
    }

    public function readMessage($message_id){
        $result = $this->pdo->query("UPDATE dialog
                                     SET read_by_recipient = 1
                                     WHERE dialog_id = '$message_id'");

        if($result) {
            return true;
        } else {
            return false;
        }
    }

    public function removeMessageInbox($message_id){
        $result = $this->pdo->query("UPDATE dialog
                                     SET read_by_recipient = 1
                                     WHERE dialog_id = '$message_id'");

        if($result) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * génère un nouveau mot de passe puis l'envoi du moderateur
     * @param $moderator_email l'adresse email du moderateur
     * @return vrai si le mot de passe a été ajouté et envoyer sinon faux
     */
    public function generateNewPassword($moderator_email){

        $password = substr(sha1(rand()),10,10);
        $hash = $this->hashSSHA($password);
        $encrypted_password = $hash["encrypted"]; // mot de passe crypté
        $salt = $hash["salt"]; // clé pour la sécurité du mot de passe

        $result = $this->pdo->exec("UPDATE moderator
									SET moderator_password = '$encrypted_password',moderator_security_key = '$salt'
									WHERE moderator_email = '$moderator_email'");

        if ($result) {
            $a = mail($moderator_email,"Votre nouveau mot de passe pour votre compte MOVEO","Voici votre nouveau mot de passe : ".$password);
            if($a)return true;
            else return false;
        } else {
            return false;
        }


    }

    /**
     * Function that return an array of moderators.
     * It is a function especialy used by the admin
     * @return array|bool|PDOStatement the array of moderators or false if there is a problem.
     */
    public function getUsers(){
        $result = $this->pdo->query("SELECT * FROM user");

        $result = $result->fetchAll();

        if($result) {
            return $result;
        } else {
            return false;
        }
    }

    /**
     * Crypter le mot de passe
     * @param password
     * returns le salt et le mot de passe crypté
     */
    public function hashSSHA($password) {

        $salt = sha1(rand());
        $salt = substr($salt, 0, 10);
        $encrypted = base64_encode(sha1($password . $salt, true) . $salt);
        $hash = array("salt" => $salt, "encrypted" => $encrypted);
        return $hash;
    }

    /**
     * Décrypter le mot de passe
     * @param $salt clé de sécurité du moderateur
     * @param $password mot de passe crypté du moderateur
     * returns le hash
     */
    public function checkhashSSHA($salt, $password) {

        $hash = base64_encode(sha1($password . $salt, true) . $salt);

        return $hash;
    }
}