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
	// fermer la base de données
	public function closeDataBase(){
		$db = null;
	}

    /**
     * Enregister un nouveau voyage
     * @param country : le pays du voyage
	 * @param city : la ville du voyage
	 * @param description : resumé du voyage 
	 * @param user_id : l'identifiant de l'utilisateur
     * return vrai si le voyage a été ajouté, faux s'il ne l'a pas été
     */
    public function storeTrip($country, $city, $description, $user_id) {
        
        $result = $this->pdo->exec("INSERT INTO trip(trip_country, trip_city, trip_description, trip_created_at, user_id) VALUES('$country', '$city', '$description', now(),'$user_id')");
		
        // verifier si l'ajout a été un succes 
        if ($result) {
			return true;
        } else {
			return false;
        }
    }
	
	 /**
     * Recuperer les voyages grâce à l'ID de l'utilisateur
	 * @param user_id : l'identifiant de l'utilisateur
     * return la liste des voyages de l'utilisateur
     */
    public function getTripByIdUser($user_id) {
        
        $result = $this->pdo->query("SELECT trip_country,trip_city,trip_description,trip_created_at FROM trip WHERE user_id = '$user_id'");
		
        // verifier si l'ajout a été un succes 
        if ($result) {
			return true;
        } else {
			return false;
        }
    }
	
	/**
     * Recuperer les voyages grâce à l'ID de l'utilisateur
	 * @param user_id : l'identifiant de l'utilisateur
     * return la liste des voyages de l'utilisateur
     */
    public function removeTripByIdTripAndIdUser($trip_id,$user_id) {
        
        $result = $this->pdo->query("DELETE from trip WHERE user_id = '$user_id' AND trip_id='$trip_id'");
		
        // verifier si l'ajout a été un succes 
        if ($result) {
			return true;
        } else {
			return false;
        }
    }
	
	 /**
     * Recuperer 10 voyages de façon aléatoires
     * return une liste regroupant 10 voyages aléatoires
     */
    public function getTenTrip() {
        
        $result = $this->pdo->query("SELECT trip_country,trip_city,trip_description,trip_created_at,user_name,user_firstname FROM trip,user where  trip.user_id = user.user_id order by rand() LIMIT 10");
		$result = $result->fetchAll();
        // verifier si l'ajout a été un succes 
        if ($result) {
			return $result;
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
		$result = $result->fetch();
        if($resultEmail) {
            // l'utilisateur existe
            return $result['user_id'];
        } else {
            // l'utilisateur n'existe pas
            return false;
        }
    }

}

?>
