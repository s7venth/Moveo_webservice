<?php

class DB_TripFunctions {

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
     * retourne les informations
     */
    public function storeTrip($country, $city, $description, $user_id) {
        
        $result = $this->pdo->exec("INSERT INTO trip(trip_country, trip_city, trip_description, trip_created_at, user_id) VALUES('$country', '$city', '$description', now(),'1')");
		
        // verifier si l'ajout a été un succes 
        if ($result) {
			return true;
        } else {
			return false;
        }
    }
	
		/**
     * Verifie si l'utilisateur existe
	 * @param email
	 * retourne l'id s'il existe, faux s'il n'existe pas 
     */
    public function getUserIdByEmail($email) {
        $result = $this->pdo->query("SELECT user_id FROM user WHERE user_mail = '$email'");
		$resultEmail = $result->rowCount();
		
        if($resultEmail) {
            // l'utilisateur existe
            return $result;
        } else {
            // l'utilisateur n'existe pas
            return false;
        }
    }

}

?>
