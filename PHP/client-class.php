<?php
header("Content-Type: charset=utf-8");
/** Include the database file */
include_once '../db/db.php';
/**
 * The main class of login
 * All the necesary system functions are prefixed with _
 * examples, _login_action - to be used in the login-action.php file
 * _authenticate - to be used in every file where admin restriction is to be inherited etc...
 * @author Swashata <swashata@intechgrity.com>
 */
class itg_client {

    /**
     * Holds the script directory absolute path
     * @staticvar
     */
    static $abs_path;

    /**
     * Store the sanitized and slash escaped value of post variables
     * @var array
     */
    var $post = array();

    /**
     * Stores the sanitized and decoded value of get variables
     * @var array
     */
    var $get = array();

    /**
     * The constructor function of admin class
     * We do just the session start
     * It is necessary to start the session before actually storing any value
     * to the super global $_SESSION variable
     */
    public function __construct() {
        session_start();

        //store the absolute script directory
        //note that this is not the admin directory
        self::$abs_path = dirname(dirname(__FILE__));

        //initialize the post variable
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->post = $_POST;
            if(get_magic_quotes_gpc ()) {
                //get rid of magic quotes and slashes if present
                array_walk_recursive($this->post, array($this, 'stripslash_gpc'));
            }
        }

        //initialize the get variable
        $this->get = $_GET;
        //decode the url
        array_walk_recursive($this->get, array($this, 'urldecode'));
    }

    /**
     * Retourne tous les témoignages.
     * @global ezSQL_mysql $db
     * @return tab
     */
    public function get_allClients() {
        global $db;
        $result = $db->get_results("SELECT * FROM `client`");
        return $result;
       
    }
    
    
    // TODO : ajouter supprimer modifier valider/invalider
    
     /**
     * Ajoute témoignage. 
     * Requiert un nom, prénom et un message 
     * 
     */
    
    
    public function add_Client($nom, $prenom, $telephone, $adresse, $ville, $cp) {
        global $db;
        $result = $db->query("INSERT INTO client (nom, prenom, telephone, adresse, ville, cp, updated_at, created_at) VALUES ('".$nom."', '".$prenom."', '".$telephone."', '".$adresse."','".$ville."','".$cp."', now(), now());");
        return $result;
        
    }
    
    /**
     * supprime un témoignage. 
     * Requiert un id
     * 
     */
    
    
    public function del_Client($id) {
        global $db;
        $result = $db->query("DELETE from client WHERE id=".$id.";");
        return $result;
        
    }
    
    /**
     * modifie un témoignage. 
     * Requiert l'id du temoignage a modifier, et nouveaux nom prénom message 
     * 
     */
    
    
    public function chg_Client($id, $nom, $prenom, $telephone, $adresse, $ville, $cp) {
        global $db;
        $result = $db->query("UPDATE temoignage SET nom = '".$nom."', prenom = '".$prenom."', telephone = '".$telephone."', adresse = '".$adresse."', ville = '".$ville."',cp = '".$cp."',cp = 'now()'   WHERE id = '$id' ;");
        return $result;
        
    }
   

    /**
     * stripslash gpc
     * Strip the slashes from a string added by the magic quote gpc thingy
     * @access protected
     * @param string $value
     */
    private function stripslash_gpc(&$value) {
        $value = stripslashes($value);
    }

    /**
     * htmlspecialcarfy
     * Encodes string's special html characters
     * @access protected
     * @param string $value
     */
    private function htmlspecialcarfy(&$value) {
        $value = htmlspecialchars($value);
    }

    /**
     * URL Decode
     * Decodes a URL Encoded string
     * @access protected
     * @param string $value
     */
    protected function urldecode(&$value) {
        $value = urldecode($value);
    }
}
