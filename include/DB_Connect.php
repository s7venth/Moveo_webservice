<?php

class DB_Connect {

	private $pdo;
	
    // Constructeur
    function __construct() {
        try {
			$conn = new PDO('mysql:host=mysql.hostinger.fr;dbname=u843730206_moveo', 'u843730206_vince', 'ameliebarre');
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		catch(Exception $e) {
			echo 'Erreur lors de la création de la base de données: ' . $e->getMessage();
		}
		$this->pdo = $conn;
    }

    // Fermer fonction
    function __destruct() {}

    // Se connecter à la database
    public function getPdo() {
		return $this->pdo;		
    }

    // Fermer la connexion vers la base de données
    public function close() {
        $this->pdo = NULL;
    }

}

?>
