<?php

class DB_FriendFunctions {

	protected $db;
    protected $pdo;
	
	
	
    // Constructeur
    function __construct() {
        require 'include/DB_Connect.php';
        // se connecter à la base de données
		$this->db= new DB_Connect();
		$this->pdo = $this->db->getPdo();
    }

    // fermer la base de données
    function __destruct() {
        $db = null;
    }
	
	public function closeDataBase(){
		$db = null;
	}

    /**
     * Enregistrer un ami 
     * return vrai si l'ajout a réussi ou faux s'il a échoué
     */
    public function addFriend($user_id, $friend_id) {
        $result = $this->pdo->exec("INSERT INTO is_friend(user_id, friend_id, is_accepted) 
									VALUES('$user_id', '$friend_id','0')");
		
        // verifier si la requête a réalisé l'ajout
        if ($result) {
			return true;
        } else {
			return false;
        }
    }
	
	 /**
     * Accepter un ami 
     * return vrai si l'acceptation a réussi ou faux si elle a échoué
     */
    public function acceptFriend($user_id, $friend_id) {
        $result = $this->pdo->exec("UPDATE is_friend 
									SET is_accepted = '1' 
									WHERE user_id='$user_id' 
									AND friend_id='$friend_id'");
		
        // vérifier si la requête a mis à jour l'acceptation
        if ($result) {
			return true;
        } else {
			return false;
        }
    }
	
	 /**
     * Supprimer un ami 
     * return vrai si la suppression a réussi ou faux s'il a échoué
     */
    public function removeFriend($user_id, $friend_id) {
        $result = $this->pdo->exec("DELETE FROM is_friend 
									WHERE user_id='$user_id' 
									AND friend_id='$friend_id'");
		
        // verifier si la requête a réaliser la suppression 
        if ($result) {
			return true;
        } else {
			return false;
        }
    }

    /**
     * Récupère tous les amis de l'utilisateur
	 * @param $user_id l'identifiant de l'utilisateur
	 * return la liste d'amis de l'utilisateur si vrai sinon retourner faux
     */
    public function getFriendsList($user_id) {
        $result = $this->pdo->query("
		SELECT user_last_name, user_first_name, is_accepted FROM user, is_friend WHERE user.user_id=friend_id AND is_friend.user_id ='$user_id'
		UNION
		SELECT user_last_name, user_first_name,is_accepted FROM user, is_friend WHERE user.user_id=is_friend.user_id AND is_friend.friend_id ='$user_id'
		");
		
        $result = $result->fetchAll();

        if ($result) {
			return $result;
        } else {
			return false;
        }
    }
	
	/**
     * Récupérer les informations d'un autre utilisateur grace à son identifiant
	 * @param $friend_id
	 * return Les informations d'un autre utilisateur
     */
    public function getFriend($friend_id){
        $result = $this->pdo->query("SELECT user_last_name, user_first_name, user_birthday, user_link_avatar, user_country, user_city, user_favorite_country, user_favorite_city 
									 FROM user 
									 WHERE user_id = '$friend_id'
									 ");
		$result = $result->fetch();
		
        if($result) {
            // l'utilisateur existe
            return $result;
        } else {
            // l'utilisateur n'existe pas
            return false;
        }
    }
	
}

?>
