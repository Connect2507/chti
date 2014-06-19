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
class itg_temoignage {

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
    public function get_allTemoignages() {
        global $db;
        $result = $db->get_results("SELECT * FROM `temoignage` ORDER BY date");
        return $result;
       
    }
    
    
    // TODO : ajouter supprimer modifier valider/invalider
    
     /**
     * Ajoute témoignage. 
     * Requiert un nom, prénom et un message 
     * 
     */
    
    
    public function add_Temoignage($nom, $prenom, $message) {
        global $db;
        $result = $db->query("INSERT INTO temoignage (nom, prenom, message, date, valide) VALUES ('".$nom."', '".$prenom."', '".$message."', now(), 0);");
        return $result;
        
    }
    
    /**
     * supprime un témoignage. 
     * Requiert un id
     * 
     */
    
    
    public function del_Temoignage($id) {
        global $db;
        $result = $db->query("DELETE from temoignage WHERE id=".$id.";");
        return $result;
        
    }
    
    /**
     * modifie un témoignage. 
     * Requiert l'id du temoignage a modifier, et nouveaux nom prénom message 
     * 
     */
    
    
    public function chg_Temoignage($id, $nom, $prenom, $message) {
        global $db;
        $result = $db->query("UPDATE temoignage SET nom = '$nom', prenom = '$prenom', message = '$message' WHERE id = '$id' ;");
        return $result;
        
    }
    
    /**
     * valide un témoignage. 
     * Requiert l'id du temoignage a valider. 
     * 
     */
    
    
    public function val_Temoignage($id, $val) {
        global $db;
        $result = $db->query("UPDATE temoignage SET valide = '$val' WHERE id = '$id' ;");
        return $result;
        
    }
    
    

    
    
    
    
    /**
     * Sample function to return the email of currently logged in admin user
     * @global ezSQL_mysql $db
     * @return string The email of the user
     */
    public function get_email() {
        $username = $_SESSION['admin_login'];
        global $db;
        $info = $db->get_row("SELECT `email` FROM `user` WHERE `username` = '" . $db->escape($username) . "'");
        if(is_object($info)){
        return $info->email;}
        else{
        return '';}
    }

    /**
     * Checks whether the user is authenticated
     * to access the admin page or not.
     *
     * Redirects to the login.php page, if not authenticates
     * otherwise continues to the page
     *
     * @access public
     * @return void
     */
    public function _authenticate() {
        //first check whether session is set or not
        if(!isset($_SESSION['admin_login'])) {
            //check the cookie
            if(isset($_COOKIE['username']) && isset($_COOKIE['password'])) {
                //cookie found, is it really someone from the
                if($this->_check_db($_COOKIE['username'], $_COOKIE['password'])) {
                    $_SESSION['admin_login'] = $_COOKIE['username'];
                    header("location: index.php");
                    die();
                }
                else {
                    header("location: login.php");
                    die();
                }
            }
            else {
                header("location: login.php");
                die();
            }
        }
    }


    /**
     * Check for login in the action file
     */
    public function _login_action() {

        //insufficient data provided
        if(!isset($this->post['username']) || $this->post['username'] == '' || !isset($this->post['password']) || $this->post['password'] == '') {
            header ("location: login.php");
        }

        //get the username and password
        $username = $this->post['username'];
        $password = md5(sha1($this->post['password']));

        //check the database for username
        if($this->_check_db($username, $password)) {
            //ready to login
            $_SESSION['admin_login'] = $username;

            //check to see if remember, ie if cookie
            if(isset($this->post['remember'])) {
                //set the cookies for 1 day, ie, 1*24*60*60 secs
                //change it to something like 30*24*60*60 to remember user for 30 days
                setcookie('username', $username, time() + 1*24*60*60);
                setcookie('password', $password, time() + 1*24*60*60);
            } else {
                //destroy any previously set cookie
                setcookie('username', '', time() - 1*24*60*60);
                setcookie('password', '', time() - 1*24*60*60);
            }

            header("location: index.php");
        }
        else {
            header ("location: login.php");
        }

        die();
    }



    /**
     * Check the database for login user
     * Get the password for the user
     * compare md5 hash over sha1
     * @param string $username Raw username
     * @param string $password expected to be md5 over sha1
     * @return bool TRUE on success FALSE otherwise
     */
    private function _check_db($username, $password) {
        global $db;
        $user_row = $db->get_row("SELECT * FROM `user` WHERE `username`='" . $db->escape($username) . "'");

        //general return
        if(is_object($user_row) && md5($user_row->password) == $password)
            return true;
        else
            return false;
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
