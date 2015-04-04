<?php

class DB_Connect {

	private $bdd;

    // Constructeur
    function __construct() {
        
    }

    // Fermer fonction
    function __destruct() {
        // $this->close();
    }

    // Se connecter à la database
    public function connect() {
		// Importer le fichier config
        require_once 'include/config.php';
		
        // créer un objet de connexion
		try {
			$bdd = new PDO('mysql:host=localhost;dbname=moveotest', 'root', '');
			return $bdd;
		}
		catch(Exception $e) {
			die('Erreur : ' . $e->getMessage());
		}
		
    }

    // Fermer la connexion vers la base de données
    public function close() {
        mysql_close();
    }

}

?>
