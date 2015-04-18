<?php

class DB_ValidationFunctions {

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

    public function validateUserAccompt($key, $id){
		echo "KEY avant : ".$key;
		$key = sha1($key);
		echo "KEY après : ".$key;
		$result = $this->pdo->query("UPDATE user
									 SET access_id = '2',user_password_temp = ''
									 WHERE user_id = '$id'
									 AND user_password_temp = '$key'
									");
		$this->closeDataBase();
		if($result){
			return true;
		}else{
			return false;
		}
	}

}

?>
