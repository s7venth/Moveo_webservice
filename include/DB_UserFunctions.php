<?php

class DB_UserFunctions {

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
     * Enregister un nouveau utilisateur 
     * return vrai si l'ajout a reussi ou faux s'il a echoué
     */
    public function storeUser($name, $firstName, $email, $password) {
        $hash = $this->hashSSHA($password);
        $encrypted_password = $hash["encrypted"]; // mot de passe crypté
        $salt = $hash["salt"]; // clé pour la securité du mot de passe
        $result = $this->pdo->exec("INSERT INTO user(user_name, user_firstname, user_mail, user_password, user_security_key, user_subscribe_date, access_id) 
									VALUES('$name', '$firstName', '$email', '$encrypted_password', '$salt', now()', '1')");
		
        // verifier si l'ajout a été un succes 
        if ($result) {
			return true;
        } else {
			return false;
        }
    }
	
	/**
     * Mettre à jour les informations de l'utilisateur 
     * return vrai si la mise à jour a réussi ou faux si elle a échoué
     */
    public function updateUser($user_id, $birthday, $country, $city, $password) {
        $result = $this->pdo->exec("UPDATE user
									SET user_birthday = '$birthday'
									AND user_country = '$country'
									WHERE user_id='$user_id'");
		
        // verifier si la mise à jour a été un succes 
        if ($result) {
			return true;
        } else {
			return false;
        }
    }

    /**
     * Recupere tous les informations de l'utilisateur
	 * @param email et password
	 * return les informations de l'utilisateur
     */
    public function getUserByEmailAndPassword($email, $password) {
        $result = $this->pdo->query("SELECT * FROM user WHERE user_mail = '$email'");
        // compter le nombre de reponses (lignes) 
        $resultUser = $result->rowCount();
        if ($resultUser > 0) {
            $result = $result->fetch();
            $key = $result['user_security_key'];
            $encrypted_password = $result['user_password'];
            $hash = $this->checkhashSSHA($key, $password);
			
            // verifier si les mots sont identiques 
            if ($encrypted_password == $hash) {
                // si les mots de passes sont identiques envoyer les informations 
                return $result;
            }
        } else {
            // L'utilisateur n'existe pas
            return false;
        }
    }
	
	/**
	 * Met à jour la date et l'heure de la connexion lorsque l'utilisateur se connecte
	 * @param $user_id l'identifiant de l'utilisateur
	 * return vrai si l'update a été un succès sinon faux
	 */
	public function registerDateConnection($user_id){
		$result = $this->pdo->query("UPDATE user
									 SET user_last_connection_datetime=now()
									 WHERE user_id='$user_id'");
		if($result){
			return true;
		}else{
			return false;
		}
	}

    /**
     * Verifier si l'utilisateur existe
	 * @param email
	 * return vrai s'il existe, faux s'il n'existe pas 
     */
    public function isUserExisted($email) {
        $result = $this->pdo->query("SELECT user_mail 
									 FROM user 
									 WHERE user_mail = '$email'");
		$resultEmail = $result->rowCount();
		
        if($resultEmail) {
            // l'utilisateur existe
            return true;
        } else {
            // l'utilisateur n'existe pas
            return false;
        }
    }
	
	/**
     * Récupérer l'identifiant de l'utilisateur grace à son email
	 * @param email
	 * return l'id s'il l'utilisateur existe, faux s'il n'existe pas 
     *
    public function getUserIdByEmail($email) {
        $result = $this->pdo->query("SELECT user_id 
									 FROM user 
									 WHERE user_mail = '$email'");
		$resultEmail = $result->rowCount();
		
        if($resultEmail) {
            // l'utilisateur existe
            return $result['user_id'];
        } else {
            // l'utilisateur n'existe pas
            return false;
        }
    }
	*/
	
	/**
     * Recuperer les informations d'un autre utilisateur grace à son identifiant
	 * @param $user_id
	 * return Les informations d'un autre utilisateur
     */
    public function getOtherUser($otherUser_id) {
        $result = $this->pdo->query("SELECT user_name, user_firstname, user_birthday, user_link_avatar, user_country, user_city
									 FROM user WHERE user_id = '$otherUser_id'");
		$result = $result->fetch();
		
        if($result) {
            // l'utilisateur existe
            return $result;
        } else {
            // l'utilisateur n'existe pas
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
     * Decrypter le mot de passe 
     * @param salt, password
     * returns hash string
     */
    public function checkhashSSHA($salt, $password) {

        $hash = base64_encode(sha1($password . $salt, true) . $salt);

        return $hash;
    }

}

?>
