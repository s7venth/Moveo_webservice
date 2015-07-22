<?php

class DB_DialogFunctions {

    protected $db;
    protected $pdo;

    //constructeur
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
     * ajoute un message avec les identifiants de l'expéditeur et le récepteur
     * @param $user_id l'identifiant de l'utilisateur (L'expéditeur)
     * @param $other_user_id l'identifiant de la personne à qui l'utilisateur envoi un message (récepteur)
     * @param $message Le message que souhaite envoyer l'expéditeur 
     */ 
    public function addDialog($user_id, $recipient_id, $message, $date){
        $result = $this->pdo->exec("INSERT INTO dialog(user_id, recipient_id, message, sent_datetime, read_by_recipient, remove_by_user, remove_by_recipient) 
                                    VALUES('$user_id', '$recipient_id','$message', '$date', '0', '0', '0')");
                                    
        // verifier si la requête a réalisé l'ajout
        if ($result) {
            return true;
        } else {
            return false;
        }
    }
	
	/**
	 * 
	 *
	 */
	public function getUserNameAndUserFirstName($user_id){
        $result = $this->pdo->exec("SELECT user_first_name, user_last_name 
									FROM user
									WHERE user_id = '$user_id'");
                                    
		$result = $result->fetch();
	   
        if ($result) {
            return $result;
        } else {
            return false;
        }
    }
    
    /**
     * Recupére tous les messages qu'a reçu l'utilisateur
     * @param $user_id l'identifiant de l'utilisateur
     * 
     */
    public function getInbox($user_id){
        $result = $this->pdo->query("SELECT user.user_id as user_id, user_last_name as recipient_last_name, user_first_name as recipient_first_name, message, sent_datetime, read_by_recipient
                                     FROM dialog, user
                                     WHERE recipient_id = '$user_id'
                                     AND user.user_id = dialog.user_id
                                     AND remove_by_recipient = 0");

        $result = $result->fetchAll();
        
        if($result) {
            return $result;
        } else {
            return false;
        }
    }

    /**
     * Recupére tous les messages d'envoi de l'utilisateur
     * @param $user_id l'identifiant de l'utilisateur
     * 
     */
    public function getSendbox($user_id){ 
        $result = $this->pdo->query("SELECT recipient_id, user_last_name as recipient_last_name, user_first_name as recipient_first_name, message, sent_datetime
                                     FROM dialog d, user u
                                     WHERE u.user_id = d.recipient_id
                                     AND d.user_id = '$user_id'
                                     AND remove_by_user = 0");

        $result = $result->fetchAll();
        
        if($result) {
            return $result;
        } else {
            return false;
        }
    }

    public function readMessage($user_id, $recipient_id, $sent_datetime){ 
        $result = $this->pdo->query("UPDATE dialog
                                     SET read_by_recipient = 1
                                     WHERE user_id = '$user_id'
                                     AND recipient_id = '$recipient_id'
                                     AND sent_datetime = '$sent_datetime'");
        
        if($result) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Supprime les messages de la boite de reception
     *
     */
    public function removeMessageInbox($user_id, $recipient_id, $sent_datetime){ 
        $result = $this->pdo->exec("UPDATE dialog
                                    SET remove_by_recipient = 1
                                    WHERE user_id = '$user_id'
                                    AND recipient_id = '$recipient_id'
                                    AND sent_datetime = '$sent_datetime'");
        
        if($result) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Supprime les messages de la boite d'envoi
     * @param $message_id l'identifiant du message
     */
    public function removeMessageSendbox($user_id, $recipient_id, $sent_datetime){ 
        $result = $this->pdo->exec("UPDATE dialog
                                    SET remove_by_user = 1
                                    WHERE user_id = '$user_id'
                                    AND recipient_id = '$recipient_id'
                                    AND sent_datetime = '$sent_datetime'");
        
        if($result) {
            return true;
        } else {
            return false;
        }
    }
}