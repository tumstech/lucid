<?php 
	class User extends Base
	{
		var $username = array('type' => 'text');
		var $password = array('type' => 'text');
		var $logged = array('type' => 'integer', 'length' => 1, 'default' => 0);
		var $email = array('type' => 'text');
		var $level = array('type' => 'text');
		var $permissions = array('type' => 'array');
		
		function get_current()
		{
			if(isset($_SESSION['userid']))
			{
				return $this->get($_SESSION['userid']);
			}
			else
			{
				return FALSE;
			}
		}
		function authenticate($user, $pass)
		{
			$line = $this->filter("username", $user);
			if($line != FALSE)
			{
				$pass = crypt($pass, $GLOBALS['conf']['salt']);
				if($line[0]->password == $pass)
				{
					return $line[0];
				}
				else
				{
					return FALSE;
				}
			}
			else
			{
				return FALSE;
			}
		}
		function login()
		{
			$_SESSION['userid'] = $this->id;
			$_SESSION['username'] = $this->username;
			$_SESSION['userlevel'] = $this->level;
			$_SESSION['userloggedin'] = TRUE;
			$this->logged = 1;
			$this->make_userdir();
			$this->save();
		}
		function logout()
		{
			$_SESSION['userid'] = null;
			$_SESSION['username'] = null;
			$_SESSION['userlevel'] = null;
			$_SESSION['userloggedin'] = FALSE;
			$this->logged = 0;
			$this->save();
		}
		function make_userdir()
		{
			$blah = $this->username;
			if(!is_dir("../../files/".$blah)){
				//Create user environment for first time
				mkdir("../../files/".$blah);
				mkdir("../../files/".$blah."/Documents");
				mkdir("../../files/".$blah."/Desktop");
				$ourFileName = "../../files/".$blah."/Desktop/welcome.txt";
				$ourFileHandle = fopen($ourFileName, 'w') or die("Could not open file");
				fwrite($ourFileHandle, "Welcome to Psych Desktop, ".$this->username."\r\n Your new account is installed and ready.");
				fclose($ourFileHandle);
			}
		}
		function delete_userdir()
		{
			$user = $this->username;
			if(is_dir("../../files/".$user)) {
				$this->_deltree("../../files/".$user);
			}
		}
		function _deltree($f)
		{
			if( is_dir( $f ) ){
				foreach( scandir( $f ) as $item ){
					if( !strcmp( $item, '.' ) || !strcmp( $item, '..' ) )
						continue;       
					$this->_deltree( $f . "/" . $item );
				}   
				rmdir( $f );
			}
			else{
				unlink( $f );
			}
		}
		function set_password($pass)
		{
			$this->password = crypt($pass, $GLOBALS['conf']['salt']);
		}
		function crypt_password()
		{
			$this->password = crypt($this->password, $GLOBALS['conf']['salt']);
		}
		function generate_password()
		{
			$characters = 10;
			$possible = '23456789bcdfghjkmnpqrstvwxyz'; 
			$code = '';
			$i = 0;
			while ($i < $characters) { 
				$code .= substr($possible, mt_rand(0, strlen($possible)-1), 1);
				$i++;
			}
			$this->set_password($code);
			return $code;
		}
		function remove_permission($perm) {
			$this->permissions[$perm] = false;
		}
		function has_permission($perm) {
			if(!isset($this->permissions[$perm])) {
				return false;
			}
			return $this->permissions[$perm];
		}
		function add_permission($perm) {
			$this->permissions[$perm] = true;
		}
	}
	global $User;
	$User = new User();
?>
