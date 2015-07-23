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
        //Se connecter � la base de donn�es
        $this->db = new DB_Connect();
        $this->pdo = $this->db->getPdo();
    }

    // fermer la base de donn�es
    function __destruct() {
        $db = NULL;
    }

    public function closeDataBase(){
        $this->db = $this->db->close();
        $this->db = NULL;
    }

    public function addModerator($name, $email, $password,$isAdmin){
        $result = $this->pdo->query("INSERT INTO moderator (moderator_name, moderator_email, moderator_password,is_admin) VALUES ('$name','$email','$password','$isAdmin')");
        if($result){
            $user_id = $this->pdo->query("SELECT user_id FROM moderator WHERE moderator_email = '$email'");
            return true;
        }
        else return false;
    }

    /**
     * Mettre � jour les informations du moderateur
     * return vrai si la mise � jour a r�ussi ou faux si elle a �chou�
     */
    public function updateModerator($moderator_id, $moderator_name) {
        $result = $this->pdo->query("UPDATE moderator
									SET moderator_name = '$moderator_name'
									WHERE moderator_id='$moderator_id'");

        // verifier si la mise � jour a �t� un succes
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
            // verifier si les mots sont identiques
            if ($password == $result['moderator_password']) {
                // si les mots de passes sont identiques envoyer les informations
                return $result;
            }
        } else {
            // Le moderateur n'existe pas
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
     * R�cup�rer les informations d'un autre moderateur grace � son identifiant
     * @param $moderator_id
     * return Les informations d'un autre moderateur
     */
    public function getOtherModerator($otherModerator_id) {
        $result = $this->pdo->query("SELECT moderator_name
									 FROM moderator
									 WHERE moderator_id = '$otherModerator_id'");
        $result = $result->fetch();

        if($result) {
            // le moderateur existe
            return $result;
        } else {
            // l'moderateur n'existe pas
            return false;
        }
    }

    /**
     * ajoute un message avec les identifiants de l'exp�diteur et le r�cepteur
     * attention, user_id est utilis� ici pour concordance avec la base de donn�es
     * @param $moderator_id l'identifiant du moderateur (L'exp�diteur)
     * @param $other_moderator_id l'identifiant de la personne � qui du moderateur envoi un message (r�cepteur)
     * @param $message Le message que souhaite envoyer l'exp�diteur
     */
    public function addDialog($moderator_id, $recipient_id, $message){
        $result = $this->pdo->exec("INSERT INTO dialog(user_id, recipient_id, message, sent_datetime, read_by_recipient)
									VALUES('$moderator_id', '$recipient_id','$message', now(), '0')");

        // verifier si la requ�te a r�alis� l'ajout
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Recup�re tous les messages qu'a re�u l'moderateur
     * @param $moderator_id le identifiant de l'moderateur
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
     * Recup�re tous les messages d'envoi du moderateur
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
     * g�n�re un nouveau mot de passe puis l'envoi du moderateur
     * @param $moderator_email l'adresse email du moderateur
     * @return vrai si le mot de passe a �t� ajout� et envoyer sinon faux
     */
    public function generateNewPassword($moderator_email){

        $password = substr(sha1(rand()),10,10);

        $result = $this->pdo->exec("UPDATE moderator
									SET moderator_password = '$password'
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
     * Function that return an array of users.
     * It is a function especialy used by the moderator
     * @return array|bool|PDOStatement the array of users or false if there is a problem.
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
     * Function that return an array of moderators.
     * It is a function especialy used by the admin
     * @return array|bool|PDOStatement the array of moderators or false if there is a problem.
     */
    public function getModerators(){
        $result = $this->pdo->query("SELECT * FROM moderator");

        $result = $result->fetchAll();

        if($result) {
            return $result;
        } else {
            return false;
        }
    }
}