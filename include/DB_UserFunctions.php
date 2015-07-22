<?php

class DB_UserFunctions {

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
     * Enregistrer un nouveau utilisateur 
     * return vrai si l'ajout a réussi ou faux s'il a échoué
     */
    public function storeUser($name, $firstName, $email, $password) {
        $hash = $this->hashSSHA($password);
        $encrypted_password = $hash["encrypted"]; // mot de passe crypté
        $salt = $hash["salt"]; // clé pour la sécurité du mot de passe
		$validation_key_to_send = substr(sha1(rand()),10,20);
		$validation_key = sha1($validation_key_to_send);
        $result = $this->pdo->exec("INSERT INTO user(user_last_name, user_first_name, user_email, user_password, user_security_key, user_password_temp, user_subscribe_date, access_id) 
									VALUES('$name', '$firstName', '$email', '$encrypted_password', '$salt', '$validation_key', now(), '1')");
		
        // Vérifie si l'ajout a été un succès 
        if ($result) {
			$user_id = $this->pdo->query("SELECT user_id FROM user WHERE user_email = '$email'");
			$user_id = $user_id->fetch();
			$a = mail($email, 'Activation de votre compte Moveo', 'Pour activer votre compte Moveo, cliquez sur le lien suivant : http://moveo.besaba.com/validation.php?key='.$validation_key_to_send.'&id='.$user_id['user_id']);
			return true;
        } else {
			return false;
        }
    }
	
	/**
     * Mettre à jour les informations de l'utilisateur 
     * return vrai si la mise à jour a réussi ou faux si elle a échoué
     */
    public function updateUser($user_id, $user_last_name, $user_first_name, $birthday, $user_link_avatar, $country, $city) {

        if($user_link_avatar == null){
            $query = "UPDATE user
                      SET user_last_name = '$user_last_name',
                      user_first_name = '$user_first_name',
                      user_birthday = '$birthday',
                      user_country = '$country',
                      user_city = '$city'
                      WHERE user_id = '$user_id'";
        }else{
            $query = "UPDATE user
                      SET user_last_name = '$user_last_name',
                      user_first_name = '$user_first_name',
                      user_birthday = '$birthday',
                      user_link_avatar = '$user_link_avatar',
                      user_country = '$country',
                      user_city = '$city'
                      WHERE user_id = '$user_id'";
        }
        


        $result = $this->pdo->exec($query);
		
        // verifier si la mise à jour a été un succes 
        if ($result) {
			return true;
        } else {
			return false;
        }
    }

  /**
   * Recuperation du nom et prenom d'un utilisateur
   * @param user_id
   */
  public function getUserNameAndUserFirstName($user_id){
        $result = $this->pdo->query("SELECT user_first_name, user_last_name 
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
     * Recupere tous les informations de l'utilisateur
	 * @param email et password
	 * return les informations de l'utilisateur
     */
    public function getUserByEmailAndPassword($email, $password) {
        $result = $this->pdo->query("SELECT * FROM user WHERE user_email = '$email'");
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
	
	public function getUserByLastNameAndFirstName($query, $user_id){
		$result = $this->pdo->query("SELECT u.user_id, user_last_name, user_first_name, user_link_avatar, COUNT( trip_id ) AS trip_count
                                  FROM (user as u)
                                    LEFT JOIN trip ON (u.user_id = trip.user_id)
                                  WHERE (
                                  user_last_name LIKE '%$query%'
                                  OR user_first_name LIKE '%$query%'
                                  )
                                  AND u.user_id != '$user_id'
                                  GROUP BY u.user_id
                                  LIMIT 0 , 30
									              ");
									 
		$result = $result->fetchAll();
	
		if($result){
			return $result;
		}else{
			return false;
		}
	}
	
	/**
	 * Mettre à jour la date et l'heure de la connexion lorsque l'utilisateur se connecte
	 * @param $user_id l'identifiant de l'utilisateur
	 * return vrai si l'update a été un succès sinon faux
	 */
	public function registerLoginDate($user_id){
		$result = $this->pdo->query("UPDATE user
									 SET user_last_login_datetime=now()
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
        $result = $this->pdo->query("SELECT user_email 
									 FROM user 
									 WHERE user_email = '$email'");
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
									 WHERE user_email = '$email'");
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
     * Récupérer les informations d'un autre utilisateur grace à son identifiant
	 * @param $user_id
	 * return Les informations d'un autre utilisateur
     */
    public function getOtherUser($otherUserId) {
        $result = $this->pdo->query("SELECT user_last_name, user_first_name, user_birthday, user_link_avatar, user_country, user_city, access_id, COUNT(trip_id) as trip_count
                                     FROM user, trip
                                     WHERE user.user_id = '$otherUserId'
                                     AND user.user_id = trip.user_id");
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
	* génère un nouveau mot de passe puis l'envoi à l'utilisateur
	* @param $user_email l'adresse email de l'utilisateur
	* @return vrai si le mot de passe a été ajouté et envoyer sinon faux
	*/
	public function generateNewPassword($user_email){
		
		$password = substr(sha1(rand()),10,10);
		$hash = $this->hashSSHA($password);
        $encrypted_password = $hash["encrypted"]; // mot de passe crypté
        $salt = $hash["salt"]; // clé pour la sécurité du mot de passe
		
		$result = $this->pdo->exec("UPDATE user
									SET user_password = '$encrypted_password',user_security_key = '$salt'
									WHERE user_email = '$user_email' 
                                   ");

        if ($result) {
			$a = mail($user_email,"Votre nouveau mot de passe pour votre compte MOVEO","Voici votre nouveau mot de passe : ".$password);
			if($a)return true;
			else return false;
        } else {
			return false;
		}	
		
	}

    /**
     *
     *
     *
     */
    public function changePassword($user_id, $new_password){

        $hash = $this->hashSSHA($new_password);
        $encrypted_password = $hash["encrypted"]; // mot de passe crypté
        $salt = $hash["salt"]; // clé pour la sécurité du mot de passe

        $result = $this->pdo->exec("UPDATE user
                                    SET user_password = '$encrypted_password',
                                        user_security_key = '$salt'
                                    WHERE user_id = '$user_id' 
                               ");
                
        if($result) {
            // Mot de passe enregisté
            return true;
            
        } else {
            // Mot de passe non enregisté
            return false;
        }

    }

    public function changeAccess($user_id, $access){
        $result = $this->pdo->exec("UPDATE user
                                    SET access_id = '$access'
                                    WHERE user_id = '$user_id' 
                                  ");
        if($result) {
            return true;
        } else {
            return false;
        }
    }
	

    public function checkPassword($user_id, $password){

        $checked = false;
        $result = $this->pdo->query("SELECT user_password, user_security_key 
                                     FROM user 
                                     WHERE user_id = '$user_id'");
        $result = $result->fetch();
        if ($result > 0) {
            $key = $result['user_security_key'];
            $encrypted_password = $result['user_password'];
            $hash = $this->checkhashSSHA($key, $password);
            
            // verifier si les mots sont identiques 
            if ($encrypted_password == $hash) {
                $checked = true; 
            }
        }
        
        return $checked;
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
     * @param $salt clé de sécurité de l'utilisateur
	 * @param $password mot de passe crypté de l'utilisateur
     * returns le hash 
     */
    public function checkhashSSHA($salt, $password) {

        $hash = base64_encode(sha1($password . $salt, true) . $salt);

        return $hash;
    }

    public function base64_to_jpeg($base64_string, $output_file) {
        $ifp = fopen($output_file, "wb"); 

        $data = explode(',', $base64_string);

        fwrite($ifp, base64_decode($data[1])); 
        fclose($ifp); 

        return $output_file; 
    }

    public function getLinkAvatar($user_id){
        $result = $this->pdo->query("SELECT user_link_avatar
                                     FROM user 
                                     WHERE user_id = '$user_id'");
        $result = $result->fetch();
        
        if($result) {
            return $result;
        } else {
            return false;
        }
    }
    
    public function deleteAccount($user_id){
        $result = $this->pdo->query("DELETE 
                                     FROM user 
                                     WHERE user_id = '$user_id'");
        
        if($result) {
            return true;
        } else {
            return false;
        }
    }

}

?>
