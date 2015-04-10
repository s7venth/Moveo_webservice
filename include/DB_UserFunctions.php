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
        $result = $this->pdo->exec("INSERT INTO user(user_name, user_firstname, user_mail, user_password, user_security_key,access_id) VALUES('$name', '$firstName', '$email', '$encrypted_password', '$salt','1')");
		
        // verifier si l'ajout a été un succes 
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
     * Verifie si l'utilisateur existe
	 * @param email
	 * return vrai s'il existe, faux s'il n'existe pas 
     */
    public function isUserExisted($email) {
        $result = $this->pdo->query("SELECT user_mail FROM user WHERE user_mail = '$email'");
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
     * Recuperer l'id de l'utilisateur grace à son email
	 * @param email
	 * return l'id s'il l'utilisateur existe, faux s'il n'existe pas 
     *
    public function getUserIdByEmail($email) {
        $result = $this->pdo->query("SELECT user_id FROM user WHERE user_mail = '$email'");
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
