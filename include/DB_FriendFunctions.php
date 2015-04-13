<?php

class DB_FriendFunctions {

	protected $db;
    protected $pdo;
	
    //constucteur
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
     * Enregister un ami 
     * return vrai si l'ajout a reussi ou faux s'il a echoué
     */
    public function addFriend($user_id, $friend_id) {
        $result = $this->pdo->exec("INSERT INTO is_friend(user_id,friend_id,is_accepted) VALUES('$user_id', '$friend_id','0')");
		
        // verifier si l'ajout a été un succes 
        if ($result) {
			return true;
        } else {
			return false;
        }
    }
	
	 /**
     * Enregister un ami 
     * return vrai si l'ajout a reussi ou faux s'il a echoué
     */
    public function acceptFriend($user_id, $friend_id) {
        $result = $this->pdo->exec("UPDATE is_friend SET is_accepted = '1' where user_id='$user_id' AND friend_id='$friend_id'");
		
        // verifier si l'ajout a été un succes 
        if ($result) {
			return true;
        } else {
			return false;
        }
    }

    /**
     * Recupere tous les amis de l'utilisateur
	 * @param $user_id l'identifiant de l'utilisateur
	 * return la liste d'amis de l'utilisateur si vrai sinon retourner faux
     */
    public function getFriendsList($user_id) {
        $result = $this->pdo->query("
		SELECT user_name, user_firstname, is_accepted FROM user, is_friend WHERE user.user_id=friend_id AND is_friend.user_id ='$user_id'
		UNION
		SELECT user_name, user_firstname,is_accepted FROM user, is_friend WHERE user.user_id=is_friend.user_id AND is_friend.friend_id ='$user_id'
		");
		
        $result = $result->fetchAll();

        if ($result) {
			return $result;
        } else {
			return false;
        }
    }
	
}

?>
