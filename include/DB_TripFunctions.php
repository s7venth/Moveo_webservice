<?php

class DB_TripFunctions {

	protected $db;
    protected $pdo;
	
    // constructeur
    function __construct() {
        require_once('include/DB_Connect.php');
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
     * Enregistrer un nouveau voyage
     * @param country : le pays du voyage
	 * @param city : la ville du voyage
	 * @param description : résumé du voyage 
	 * @param user_id : l'identifiant de l'utilisateur
     * @return vrai si le voyage a été ajouté, faux s'il ne l'a pas été
     */
    public function addTrip($country, $name, $description, $user_id) {
        
        $result = $this->pdo->exec("INSERT INTO trip(trip_name, trip_country, trip_description, trip_created_at, user_id) 
									VALUES('$name', '$country', '$description', now(),'$user_id')");
		
        // verifier si l'ajout a été un succès 
        if ($result) {
			return true;
        } else {
			return false;
        }
    }
	
	 /**
     * Récupérer les voyages d'un utilisateur grâce à son identifiant 
	 * @param user_id : l'identifiant de l'utilisateur
     * @return la liste des voyages de l'utilisateur
     */
    public function getTripList($user_id) {
        
        $result = $this->pdo->query("SELECT t.trip_id, trip_name, trip_country, trip_description, trip_created_at, count(DISTINCT comment_id) as comment_count, count(DISTINCT photo_id) as photo_count 			 
                                     FROM (trip as t) 
										LEFT JOIN comment ON (t.trip_id = comment.trip_id)
										LEFT JOIN photo ON (t.trip_id = photo.trip_id) 
									 WHERE t.user_id = '$user_id'
									 GROUP BY t.trip_id");
		$result = $result->fetchAll();
        // verifier si la requête a réaliser la récuperation a été un succès 
        if ($result) {
			return $result;
        } else {
			return false;
        }
    }
	
	/**
     * Supprime le voyage grâce à l'identifiant de l'utilisateur et l'identifiant de l'utilisateur
	 * @param trip_id : l'identifiant de l'utilisateur
	 * @param user_id : l'identifiant du voyage
     * @return Vrai si la requête a réaliser la suppression, faux s'il la suppression a échoué
     */
    public function removeTrip($trip_id,$user_id) {
        
        $result = $this->pdo->query("DELETE from trip 
									 WHERE user_id = '$user_id' 
									 AND trip_id='$trip_id'");
		
        // verifier si l'ajout a été un succès 
        if ($result) {
			return true;
        } else {
			return false;
        }
    }
	
	 /**
     * Récupérer 10 voyages de façon aléatoires avec leurs informations (identifiant, nom, pays, description, date de création, l'auteur, nombre de commentaire et de photos)
     * @return une liste regroupant 10 voyages aléatoires, faux s'il la recuperation a échoué 
     */
    public function getTenTrips($user_id) {
        
        $result = $this->pdo->query("SELECT t.trip_id, trip_name, trip_country, trip_description, trip_created_at, user_last_name, user_first_name,count(DISTINCT comment_id) as comment_count, count(DISTINCT photo_id) as photo_count 
                            		 FROM (trip as t) 
                            			LEFT JOIN user ON (t.user_id = user.user_id) 
                            			LEFT JOIN comment ON (t.trip_id = comment.trip_id)
                            			LEFT JOIN photo ON (t.trip_id = photo.trip_id) 
									 WHERE t.user_id != '$user_id'
                            		 GROUP BY t.trip_id 
                            		 ORDER BY rand() 
                            		 LIMIT 10");
		$result = $result->fetchAll();
		
         // vérifier si la requête a réaliser la suppression
        if ($result) {
			return $result;
        } else {
			return false;
        }
    }

     /**
     * Récupérer un voyage grâce à son identifiant
     * @param $trip_id l'identifiant du voyage
     * @return les informations du voyages ainsi que ses lieux
     */
    public function getTrip($trip_id) {
        
        $result = $this->pdo->query("SELECT trip_id, trip_name, trip_country, trip_description, trip_created_at, user_last_name, user_first_name, trip.user_id 
                                     FROM trip, user 
                                     WHERE trip.user_id = user.user_id 
                                     AND trip_id = '$trip_id' ");
		$result = $result->fetch();
        // verifier si la requête a réaliser la recuperation 
        if ($result) {
			return $result;
        } else {
			return false;
        }
    }

    /**
     * Récupérer tous les lieux d'un voyage grâce à son identifiant de celui ci
     * @param $user_id l'identifiant de l'utilisateur
     * @param $trip_id l'identifiant du voyage
     * @return Les lieux d'un voyage
     */
    public function getPlaceList($trip_id) {
        
        $result = $this->pdo->query("SELECT place_id, place_name, place_address ,place_description, category_id
									 FROM place
									 WHERE  trip_id = '$trip_id'");
		$result = $result->fetchAll();

        // vérifier si la requête a réaliser la recuperation 
        if ($result) {
			return $result;
        } else {
			return false;
        }
    }

    /**
     * Ajouter un nouveau lieu dans une categories passé en paramètre 
     * @param $place_name le nom de ce lieu
     * @param $place_adresse l'adresse ou se trouve le lieu
     * @param $place_description la description de ce lieu
     * @param $trip_id l'identifiant du voyage
     * @param $category_id l'identifiant du voyage
     * @return vrai si l'ajout a réussi, faux l'ajout a échoué 
     */
    public function addPlace($place_name, $place_adresse, $place_description, $trip_id, $category_id) {
        
        $result = $this->pdo->query("INSERT INTO place (place_name, place_adresse, place_description, trip_id, category_id) 
        							 VALUES ('$place_name', '$place_adresse', '$place_description', '$trip_id', '$category_id')");
        
        // vérifier si la requête a réaliser l'ajout
        if ($result) {
			return true;
        } else {
			return false;
        }
    }
    

	
	/**
     * Verifier si l'utilisateur existe
	 * @param email
	 * retourne l'id s'il existe, faux s'il n'existe pas 
     */
    public function checkId($user_id) { // A MODIFIER 
        $result = $this->pdo->query("SELECT user_id 
									 FROM user 
									 WHERE user_mail = '$email'");
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
	
	
	/**
	 * Ajouter un commentaire
	 * @param $comment_message 
	 * @param $trip_id 
	 * @return vrai si l'ajout à réussi, faux s'il n'a pas réussi
	 */
	public function addComment($comment_message, $trip_id, $user_id){
		$result = $this->pdo->query("INSERT INTO comment (comment_message, comment_added_datetime, trip_id, user_id)
									 VALUES ('$comment_message', now(), '$trip_id', '$user_id')
									 ");
		if ($result) {
			return true;
        } else {
			return false;
        }
									 
	}

    /**
     * Ajouter un commentaire
     * @param $comment_message 
     * @param $trip_id 
     * @return vrai si l'ajout à réussi, faux s'il n'a pas réussi
     */
    public function getCommentList($trip_id){
        $result = $this->pdo->query("SELECT comment_id, comment_message, comment_added_datetime, trip_id, user_id
                                     FROM comment
                                     WHERE trip_id = '$trip_id'
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
