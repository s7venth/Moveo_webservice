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
    public function addTrip($country, $name, $description, $date, $cover, $user_id) {

        $result = $this->pdo->exec("INSERT INTO trip(trip_name, trip_country, trip_description, trip_created_at, link_cover, user_id) 
									VALUES('$name', '$country', '$description', '$date','$cover','$user_id')");
		
        // verifier si l'ajout a été un succès 
        if ($result) {  
			return $this->pdo->lastInsertId();
        } else {
			return false;
        }
    }

    public function modifyDescription($trip_id, $description){

        $result = $this->pdo->query("UPDATE trip
                                     SET trip_description = '$description'
                                     WHERE trip_id = '$trip_id'");

        if ($result) {
            return true;
        } else {
            return false;
        }

    }
    
    public function modifyCover($link_cover, $trip_id){
        $result = $this->pdo->query("UPDATE trip
                                     SET link_cover = '$link_cover'
                                     WHERE trip_id = '$trip_id'");

        if($result){
            return true;
        }else{
            return false;
        }
    }
    
         /**
     * Récupérer un voyage grâce à son identifiant
     * @param $trip_id l'identifiant du voyage
     * @return les informations du voyages ainsi que ses lieux
     */
    public function getTrip($trip_id) {
        
        $result = $this->pdo->query("SELECT trip_id, trip_name, trip_country, trip_description, trip_created_at, link_cover, user_last_name,                                          user_first_name, trip.user_id
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
     * Récupérer les voyages d'un utilisateur grâce à son identifiant 
	 * @param user_id : l'identifiant de l'utilisateur
     * @return la liste des voyages de l'utilisateur
     */
    public function getTripList($user_id) {
        
        $result = $this->pdo->query("SELECT t.trip_id, trip_name, trip_country, trip_description, link_cover, count(DISTINCT comment_id) as comment_count, count(DISTINCT photo_id) as photo_count 			 
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
    public function removeTrip($trip_id, $user_id) {
        
        $result = $this->pdo->query("DELETE from trip
									 WHERE trip_id = '$trip_id'");
		
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
        
        $result = $this->pdo->query("SELECT t.trip_id, trip_name, trip_country, link_cover, user_last_name, user_first_name,count(DISTINCT comment_id) as comment_count, count(DISTINCT photo_id) as photo_count 
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
     * Récupere une liste de voyage selon le mot recherché
     *
     */ 
    public function getTripListByQuery($userId, $query){
        
        $result = $this->pdo->query("SELECT t.trip_id, trip_name, trip_country, link_cover, user_last_name, user_first_name,
                                     count(DISTINCT comment_id) as comment_count, count(DISTINCT photo_id) as photo_count
                                     FROM (trip as t)
                            			LEFT JOIN user ON (t.user_id = user.user_id) 
										LEFT JOIN comment ON (t.trip_id = comment.trip_id)
										LEFT JOIN photo ON (t.trip_id = photo.trip_id) 
                                     WHERE t.user_id != '$user_id'
									 AND t.trip_name LIKE '%$query%'
                                     OR t.trip_country LIKE '%$query%'
									 GROUP BY t.trip_id
                                     LIMIT 10");
        
        $result = $result->fetchAll();
		
        if ($result) {
			return $result;
        } else {
			return false;
        }
    }

    public function getOtherUserTripList($userId){
        
        $result = $this->pdo->query("SELECT t.trip_id, trip_name, trip_country, trip_description, trip_created_at, link_cover, user_last_name, user_first_name,
                                     count(DISTINCT comment_id) as comment_count, count(DISTINCT photo_id) as photo_count
                                     FROM (trip as t)
                                        LEFT JOIN user ON (t.user_id = user.user_id) 
                                        LEFT JOIN comment ON (t.trip_id = comment.trip_id)
                                        LEFT JOIN photo ON (t.trip_id = photo.trip_id) 
                                     WHERE t.user_id = '$userId'
                                     GROUP BY t.trip_id");
        
        $result = $result->fetchAll();
        
        if ($result) {
            return $result;
        } else {
            return false;
        }
    }

    /**
     * Récupérer tous les lieux d'un voyage grâce à l'identifiant de celui ci
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
     * Récupérer tous les lieux d'une catégorie d'un voyage grâce à l'identifiant de celui ci
     * @param $trip_id l'identifiant du voyage
     * @return les lieux d'une catégorie 
     */
    public function getPlaceListByCategoryId($trip_id, $category_id) {
        
        $result = $this->pdo->query("SELECT place_id, place_name, place_address ,place_description, category_id
									 FROM place
									 WHERE  trip_id = '$trip_id'
									 AND category_id = '$category_id'");
		$result = $result->fetchAll();

        // vérifier si la requête a réaliser la recuperation 
        if ($result) {
			return $result;
        } else {
			return false;
        }
    }
	
	
	/**
     * Récupérer tous les lieux de l'utilisateur
     * @param $user_id l'identifiant de l'utilisateur
     * @return Les lieux d'un utilisateur
     */
    public function getAllPlaces($user_id) {
        
        $result = $this->pdo->query("SELECT DISTINCT place_id, place_name, place_address , place_description, category_id, trip.trip_id as trip_id
									 FROM place, trip
									 WHERE  user_id = '$user_id'
                                     AND place.trip_id = trip.trip_id");
									 
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
    public function addPlace($place_name, $place_address, $place_description, $trip_id, $category_id) {
        
        $result = $this->pdo->query("INSERT INTO place (place_name, place_address, place_description, trip_id, category_id) 
        							 VALUES ('$place_name', '$place_address', '$place_description', '$trip_id', '$category_id')");
        
        // vérifier si la requête a réaliser l'ajout
        if ($result) {
			return $this->pdo->lastInsertId();
        } else {
			return false;
        }
    }
	
    
    /**
     * Modifie un lieu
     * @param $place_name le nom de ce lieu
     * @param $place_adresse l'adresse ou se trouve le lieu
     * @param $place_description la description de ce lieu
     * 
     * 
     */
    public function modifyPlace($place_id, $place_name, $place_address, $place_description){
        
        $result = $this->pdo->query("UPDATE place
                                     SET place_name = '$place_name',
                                     place_address = '$place_address',
                                     place_description = '$place_description'
                                     WHERE place_id = '$place_id'");
        if($result){
            return true;
        }else{
            return false;
        }
        
    }
    
    public function removePlace($place_id){
        
        $result = $this->pdo->query("DELETE from place
									  WHERE place_id = '$place_id'");
		if ($result) {
			return true;
		}else{
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
	
    // --------------COMMENT -----------------
	
	/**
	 * Ajouter un commentaire
	 * @param $comment_message 
	 * @param $trip_id 
	 * @return vrai si l'ajout à réussi, faux s'il n'a pas réussi
	 */
	public function addComment($comment_message, $trip_id, $user_id){

        $date = new DateTime(null, new DateTimeZone('Europe/Paris'));
        //$date2 = new DateTime($date, $timezone);
        //$date->add(new DateInterval('PT5M20S'));
        $date =  $date->format('Y-m-d H:i:s');
        $result = $this->pdo->query("INSERT INTO comment (comment_message, comment_added_datetime, trip_id, user_id)
									 VALUES ('$comment_message', '$date', '$trip_id', '$user_id')
									 ");
		if ($result) {
			return true;
        } else {
			return false;
        }
									 
	}
    
    /**
	 * Modifier un commentaire
	 * @param $comment_message 
	 * @param $trip_id 
	 * @return vrai si la modification à réussi, faux si elle n'a pas réussi
	 */
	public function modifyComment($comment_message, $comment_id){
		$result = $this->pdo->query("UPDATE comment
                                     SET comment_message = '$comment_message'
                                     WHERE comment_id = '$comment_id'
									 ");
		if ($result) {
			return true;
        } else {
			return false;
        }
									 
	}
	
	/**
	 * Supprimer un commentaire
	 *
	 */
	public function removeComment($comment_id){
		
	    $result = $this->pdo->query("DELETE from comment
									  WHERE comment_id = '$comment_id'");
		if ($result) {
			return true;
		}else{
			return false;
		}
	}

    /**
     * Recuperation de la liste des commentaires
     * @param $comment_message 
     * @param $trip_id 
     * @return vrai si la requête renvoi un résultat, faux s'il en n'envoie pas 
     */
    public function getCommentList($trip_id){
        $result = $this->pdo->query("SELECT comment_id, comment_message, comment_added_datetime, trip_id, comment.user_id, user_last_name, user_first_name, user_link_avatar
                                     FROM comment,user
                                     WHERE trip_id = '$trip_id'
                                     AND comment.user_id = user.user_id
                                     ");
        $result = $result->fetchAll();
        if ($result) {
            return $result;
        } else {
            return false;
        }
                                     
    }

        /**
     * Recuperation de la liste des commentaires en fonction d'un utilisateur
     * @param $user_id
     * @return vrai si la requête renvoi un résultat, faux s'il en n'envoie pas
     */
    public function getCommentListByUser($user_id){
        $result = $this->pdo->query("SELECT comment_id, comment_message, comment_added_datetime, trip_id, comment.user_id, user_last_name, user_first_name, user_link_avatar
                                     FROM comment,user
                                     WHERE comment.user_id = '$user_id'
                                     ");
        $result = $result->fetchAll();
        if ($result) {
            return $result;
        } else {
            return false;
        }

    }

    // ------------------ PHOTO ----------------------

    /*
     * Ajouter une nouvelle photo dans la galerie photo d'un voyage
     * 
     */
    public function addPhoto($photo_link, $date, $trip_id){

        $result = $this->pdo->query("INSERT INTO photo (photo_link, photo_added_date, trip_id)
                                     VALUES ('$photo_link', '$date', '$trip_id')");

        if($result){
            return $this->pdo->lastInsertId();
        }else{
            return false;
        }
    }

    
    public function deletePhoto($photo_id){
    
       $result = $this->pdo->query("DELETE from photo
									  WHERE photo_id = '$photo_id'");
        if ($result) {
            return true;
        }else{
            return false;
        }
        
    }
    
     public function getPhotoLink($photo_id){
    
       $result = $this->pdo->query("SELECT photo_link
                                    FROM photo
								    WHERE photo_id = '$photo_id'");
        $result = $result->fetch();

        if ($result) {
            return $result;
        }else{
            return false;
        }
        
    }

    public function getCover($trip_id){
        $result = $this->pdo->query("SELECT link_cover
                                     FROM trip 
                                     WHERE trip_id = '$trip_id'");
        $result = $result->fetch();
        
        if($result) {
            return $result;
        } else {
            return false;
        }
    }


    /**
     * Récupérer la galerie photo d'un voyage
     * 
     *
     */
    public function getPhotoGallery($trip_id){
        $result = $this->pdo->query("SELECT * 
                                     FROM photo
                                     WHERE trip_id = '$trip_id'
                                     ");
        $result = $result->fetchAll();
        if($result){
            return $result;
        }else{
            return false;
        }
    }

    /**
     * Récuperation de la galerie photo de l'utilisateur
     *
     */
    public function getPhotoGalleryByUserId($user_id){
        $result = $this->pdo->query("SELECT photo_id, photo_link, photo_added_date, trip.trip_id 
                                     FROM photo, trip, user
                                     WHERE photo.trip_id = trip.trip_id
                                     AND trip.user_id = user.user_id
                                     AND trip.user_id = '$user_id'
                                     ");
        $result = $result->fetchAll();
        if($result){
            return $result;
        }else{
            return false;
        }
    }

    /**
     * Convertir une photo base64 en jpeg
     *
     */
    function base64_to_jpeg($base64_string, $output_file) {
        $ifp = fopen($output_file, "wb"); 

        $data = explode(',', $base64_string);

        fwrite($ifp, base64_decode($data[1])); 
        fclose($ifp); 

        return $output_file; 
    }
	
	/*
	 * Signaler une photo
	 *
	 */
    public function toReportPhoto($user_id, $photo_id){
        $result = $this->pdo->query("INSERT INTO to_report (user_id, photo_id)
                                     VALUES ('$user_id','$photo_id')");

        if($result){
            return true;
        }else{
            return false;
        }
    }
}

?>
