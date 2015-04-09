<?php

class DB_Connect {

	var $pdo;
	
    // Constructeur
    function __construct() {
        
    }

    // Fermer fonction
    function __destruct() {
		
    }

    // Se connecter à la database
    public function connect() {
		
        // créer un objet de connexion
		
		
    }

    // Fermer la connexion vers la base de données
    public function close() {
        mysql_close();
    }

}

?>
