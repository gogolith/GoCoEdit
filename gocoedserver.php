<?php  


header('Access-Control-Allow-Origin: *');

date_default_timezone_set("Europe/Berlin");
 
if(isset($_REQUEST['install']))
if($_REQUEST['install']!=""){

	if(file_exists("./tmp/config_admin.php")){

		print("
					<html>
						<head>
							<style>

								body{
									font-family:monospace;
									font-size:14px;
									color:#aaa;
									text-align:center;
									background:#222;
								}

								textarea{
									padding:10px;
									font-size:14px;
									height: 150px;
									width: 300px;
									border:0px;
									background:#555;
									color:#fff;
								}

								h1{
									color:#ccc;

								}

								.sub{

									font-size:11px;
								}


							</style>	
						</head>
						<h1>GOCOED allready installed... now use it!</h1>
						<p class='sub'>
							Your the id is stored in tmp/config_admin.php.
						</p>
					</html>

				");

		die();
	}

	if(!@mkdir('./tmp')){

		file_put_contents("./tmp/gocoed-tmp","gocoed needs this");
		

	}
	$configfile = @file_get_contents("./tmp/config_admin.php");
	$configfile = str_replace('<?php', '', $configfile);
	$config = json_decode($configfile,true);

	if(!$config){
		$config = array();
	}




	echo ('<br><br>');	
	$connector_url = str_replace('?'.$_SERVER['QUERY_STRING'],'',(strpos($_SERVER['SERVER_PROTOCOL'],"HTTP")!==false?"http://":"http://").$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
	$salt = hash("sha256",$connector_url.time(true));

	$config['salt'] = $salt;
	$config['connectorid'] = base64_encode('{"connectorurl":"'.$connector_url.'","s":"'.$salt.'"}');
	
	if(!file_put_contents("./tmp/config_admin.php","<?php".json_encode($config))){
		echo "<html>
						<head>
							<style>

								body{
									font-family:monospace;
									font-size:14px;
									color:#aaa;
									text-align:center;
									background:#222;
								}

								a{
									color:#fff !important;
								}

								textarea{
									padding:10px;
									font-size:14px;
									height: 150px;
									width: 300px;
									border:0px;
									background:#555;
									color:#fff;
								}

								h1{
									color:#ccc;
								}

								.sub{

									font-size:11px;
								}


							</style>	
						</head>OH! Please give me write (chmod 755) rights in: ".str_replace('','',__dir__);
		echo ("<br><br> <a href='?install=true'>RETRY?</a></html>");
		exit(0);
	}


	
		print("
					<html>
						<head>
							<style>

								body{
									font-family:monospace;
									font-size:14px;
									color:#aaa;
									text-align:center;
									background:#222;
								}

								textarea{
									padding:10px;
									font-size:14px;
									height: 150px;
									width: 300px;
									border:0px;
									background:#555;
									color:#fff;
								}

								h1{
									color:#ccc;
								}

								.sub{

									font-size:11px;
								}


							</style>	
						</head>
						<h1>YEH! Installation done!</h1>
						<p>
							Copy and Paste this Connector ID to your GoCoEd-Client!
						</p>
						<p class='sub'>
							Your ID is stored in tmp/config_admin.php.
						</p>


						<textarea>".$config['connectorid']."</textarea>

					</html>

				");

	 

	echo ('<br><br>');
	die();
}



class ConnectorFTP {


	private $crypto;
	private $config;
	private $con;
	private $ackid;
	private $startdir = "/";
	private $tokenfile = null;

	public function __construct(){

		$this->crypto = new ConnectorCrypt();

		file_put_contents("./tmp/gocoed-tmp","gocoed needs this");
		$myuser = fileowner("./tmp/gocoed-tmp");

		$configfile = @file_get_contents("./tmp/config_admin.php");
		$configfile = str_replace('<?php', '', $configfile);
		$this->config = json_decode($configfile);

	}

	public function ACKID($ackid){

		$this->ackid = (int)$ackid;

	}

	private function checkPath($cpath, $vroot="../../../"){
		return true;
		$path = $cpath;
	    
	    // Validate that the $path is a subfolder of $vroot
	    $vroot = realpath($vroot);
	    if(substr(realpath($path), 0, strlen($vroot)) != $vroot or !is_dir($path)) {
	        return false;
	    } else {
	       return true;
	    }

	}
	
	public function getTokenFile(){
		


		if(!$this->tokenfile = $this->crypto->validateToken($_REQUEST['token'])){

			$return = array(
				"error" => true,
				"errorcode" => 503,
				"ackid" => $this->ackid
			);
			die($this->returnData($return));

		}

	}
 

	

	public function checkCrypto(){
		 
		$configfile = @file_get_contents("./tmp/config_admin.php");
		$configfile = str_replace('<?php', '', $configfile);
		$config = json_decode($configfile);
		 

		if(!$config){
			
			print("
					<html>
						<head>
							<style>

								body{
									font-family:monospace;
									font-size:14px;
									color:#aaa;
									text-align:center;
									background:#222;
								}

								textarea{
									padding:10px;
									font-size:14px;
									height: 150px;
									width: 300px;
									border:0px;
									background:#555;
									color:#fff;
								}

								a{
									color:#fff;
								}

								h1{
									color:#ccc;
								}

								.sub{

									font-size:11px;
								}


							</style>
						</head>
							<h1>no config file found...</h1>
						<p>
							<a href='?install=true'>Install GoCoEd-Server NOW?</a>
						</p>
					</html>

				");

			die();


		}

		if(!isset($_REQUEST['data']))
			die();
		
		if(!$this->crypto->decodeData($_REQUEST['data'],$config->salt,$_REQUEST['hash'])){
			die('{error:"security error"}');
		}

	}

	public function fileUpload($topath){

		$this->getTokenFile();
		$this->ftplogin();

		foreach ($_FILES as $key => $value) {

			 $value['name'] = $this->checkfileExsits($topath,$value['name']);
			 ftp_put($this->con, $this->startdir.$topath.'/'.$value['name'], $value['tmp_name'], FTP_BINARY);
			 //move_uploaded_file($value['tmp_name'],$config->docroot.'/'.$_REQUEST['topath'].'/'.$value['name']);
		}

		$return = array();
		$return['ackid'] = $this->ackid;

	 	 
		$return['success'] = true;


		ftp_close($this->con);
	    return $this->returnData($return);

	}


	public function readConnections(){

		 
		 
		$return = array();
		$return['hosts'] = null;
		if($hosts = file_get_contents('./tmp/config_hosts.php')){
		 	$hosts = str_replace('<?php', '', $hosts);
			$hostsjson = json_decode($hosts,true);
			$return['hosts'] = $hostsjson;
		}

		
		$return['ackid'] = $this->ackid; 
		$return['success'] = true;


	    echo json_encode($return);

	}


	public function fileUploadDropbox($files,$topath){

		$this->getTokenFile();
		$this->ftplogin();

		foreach ($files as $key => $value) {


			 $value['name'] = $this->checkfileExsits($topath,$value['name']);

			 $this->downloadFile($value['link'],$this->startdir.$topath.'/'.$value['name']);
		}

		$return = array();
		$return['ackid'] = $this->ackid;

	 	 
		$return['success'] = true;


		ftp_close($this->con);
	    return $this->returnData($return);
	}


	public function downloadFile($url, $desc){


		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		$st = curl_exec($ch);

		$uid = substr($this->tokenfile->user,0,10).time();
	  
		mkdir('./tmp/__gocoed_tmp_'.$uid);
	 	chmod('./tmp/__gocoed_tmp_'.$uid, 0777);

		$fd = fopen('./tmp/__gocoed_tmp_'.$uid.'/tmp_db_download', 'w');
		fwrite($fd, $st);
		fclose($fd);

		curl_close($ch);
	 
		ftp_put($this->con, ($desc), './tmp/__gocoed_tmp_'.$uid.'/tmp_db_download', FTP_BINARY);

		@unlink('./tmp/__gocoed_tmp_'.$uid);


	}

	private function checkfileExsits($path,$fname,$add=null,$orgfname=null){
  
  			
 		 

        $check_file_exist = $this->startdir.$path.'/'.$fname;  

        ftp_chdir($this->con, $this->startdir);
        $contents_on_server = ftp_nlist($this->con, $path);  



       
        
        foreach ($contents_on_server as $key => $value) {
        	

        	 	if(substr($value, 0,1) == '.')
        	 		$contents_on_server[$key] = $this->startdir.$value;
        	 	else
        			$contents_on_server[$key] = $this->startdir.'./'.$value;
        	 
        }

 		 

         if (in_array($check_file_exist, $contents_on_server)) 
        {
           		$file = $fname;
	        		
	        		if($orgfname)
	           	 	$file = $orgfname;
	        		else 
	              $orgfname = $fname;
	          	
		        if (!@ftp_chdir($this->con, $check_file_exist)) {
		            ftp_chdir($this->con, $this->startdir);
	          		$fileName = substr($file, 0, strrpos($file,'.'));
	          	 	$dot = ".";
	  				$ext =  substr($file, strrpos($file,'.') + 1); 
	  			}	else {
	  				$fileName = $fname;
	          	 	$dot = "";
	  				$ext =  ""; 
	  			}
	          
	        		if(!$add)
		          	$add = 1;
	        		else
	             	 $add++;
	          	
	          	$fname = $fileName.'_'.$add.$dot.$ext;
	        
	        return $this->checkfileExsits($path,$fname,$add,$orgfname);
        }
        else
        {
           	return $fname;
        };  
	}

	private function ftplogin(){
		 

		$server = $this->tokenfile->server;

	 	 

		if(count($server)>1)
			$this->con = ftp_connect($server[0],$server[1]);
		else
			$this->con = ftp_connect($server[0]);

		if(!ftp_login($this->con, $this->tokenfile->user,$this->tokenfile->password)){
			$return = array("error"=>"auth error", "errorcode"=>503, 'ackid' => $this->ackid);

			return $this->returnData($return);
		}
		
		if($this->tokenfile->startdir != ""){
			$this->startdir = $this->tokenfile->startdir;
		} else{
			$this->startdir = ftp_pwd($this->con).'/';
		}



	}

	private function ftp_syncdown ($remote_dir,$local_dir) { 

	    if (ftp_chdir($this->con, $remote_dir) == false) {
	        echo ("Change Dir Failed: $remote_dir \n");
	        return;
	    }

	    $contents = ftp_nlist($this->con, ".");

	    foreach ($contents as $file) {

	        if ($file == '.' || $file == '..') continue;

	        if (@ftp_chdir($this->con, $file)) {
	            ftp_chdir($this->con, "..");

	            
	   			mkdir($local_dir."/".$file);

	           $this->ftp_syncdown($remote_dir."/".$file, $local_dir."/".$file);
	        }
	        else {
	       
	            ftp_get($this->con, "$local_dir/$file", "$file", FTP_BINARY);   
	        }
	    }

	    ftp_chdir($this->con, "..");
	} 

	private function ftp_upload($dir,$dest) {
	    global $con;

	    if($handle = opendir($dir))
	    {
		    while(false !== ($file = readdir($handle)))
		    {
		        if($file != "." && $file != ".." && $file != "...") {

			        if(!is_dir($dir."/".$file))
			        {
			  
			            $source_file = $file;
			            $destination_file = $file;
			            
			            $upload = ftp_put($this->con, $dest."/".$destination_file, $dir."/".$source_file, FTP_BINARY);

			        }else{

			            ftp_mkdir($this->con, $dest."/".$file);
			            $this->ftp_upload($dir."/".$file,$dest."/".$file);
			        }
			    }
		    }   
	    }  else {
	    } 

	}

	private function recursiveDelete($handle, $directory)
	{  	
	    if( !(@ftp_rmdir($handle, $directory) || @ftp_delete($handle, $directory)) )
	    {            
	        $filelist = @ftp_nlist($handle, $directory);
	       
	        foreach($filelist as $file) {            
	            $this->recursiveDelete($handle, $file);            
	        }
	        $this->recursiveDelete($handle, $directory);
	    }
	}


	private function deleteDirectory($dir) { 
	    if (!file_exists($dir)) return true; 
	   
	    if (!is_dir($dir) || is_link($dir)) return unlink($dir); 
        
        foreach (scandir($dir) as $item) { 
            if ($item == '.' || $item == '..') continue; 
            if (!$this->deleteDirectory($dir . "/" . $item)) { 
                chmod($dir . "/" . $item, 0777); 
                if (!$this->deleteDirectory($dir . "/" . $item)) return false; 
            }; 
    	}

	    return rmdir($dir); 
    } 

	public function login($server,$username,$password,$startdir=""){
		
		$server = str_replace('ftp://', '', $server);
		$server = explode(':', $server);

		if($server[1]){
			$this->con = ftp_connect($server[0],$server[1]);
		}
		else
			$this->con = ftp_connect($server[0]);

		if(ftp_login($this->con,$username,$password)){

			$newtoken = $this->crypto->genToken($password);

				$return = array(
					'success' => true,
					'ackid' => $this->ackid,
					'msg' => array(
						'token' => $newtoken,
						'server' => str_replace('ftp://', '', $server),
					)
				);



			$storedata = array(
				"token"=> $newtoken,
				"user"=>$username,
				"server"=>str_replace('ftp://', '', $server),
				"password"=>$password,
				"startdir"=>$startdir
				);
			


			file_put_contents('./tmp/user_'.$username.'.php', "<?php".json_encode($storedata));
		} else {

			$return = array("error"=>"auth error", "errorcode"=>503, 'ackid' => $this->ackid);
		}

		ftp_close($this->con);
		$this->returnData($return);
		 
	}

	public function listDir($path){

		$this->getTokenFile();
		$this->ftplogin();

 		 

		if(!$this->checkPath('/'.$path,""))
			die('security error');




		$content = array();

		$dir = array();
		$return = array();
		$return['type'] = "filelist";
		$content['path'] =  $path;
		
		if($this->ackid)
			$return['ackid'] =  $this->ackid;
		
		$content['docroot'] = "";
		
		
	 	
		$files = array();

		ftp_chdir($this->con, $this->startdir.$path);
		 

		$contents = ftp_rawlist ($this->con, '-A '.$this->startdir.$path);

		 

		$a = 0;
		if(count($contents)){
	        foreach($contents as $line){

	        	 
	        	$chunks = preg_split("/\s+/", $line); 
	                list($item['rights'], $item['number'], $item['user'], $item['group'], $item['size'], $item['month'], $item['day'], $item['time'], $item['name']) = $chunks; 
	                $item['type'] = $chunks[0]{0} === 'd' ? 'folder' : 'file'; 

	               
	                //$line = preg_replace("/\s+/", "", $line);


	                $str = strrchr($line, $item['name']);

	                preg_match('/^([drwx+-]{10})\s+(\d+)\s+(\w+)\s+(\w+)\s+(\d+)\s+(.{12}) (.*)$/m', $line, $match);
	                
	                $fname = "";
	                $n = 0;
	                foreach ($chunks as $key => $value) {
	                	
	                	if($n==8)
	                	 $fname.= ''.$value;
	                	else if($n>8)
	                	 $fname.= ' '.$value;
	                	$n++;
	                }

	                $item['name'] = $fname;

	                $files[implode(" ", $chunks)] = $item; 


	               // var_dump($line);
	          
	        }
	    }


	 
	      
	   	foreach ($files as $key => $file) {
	 
	         

	        $isdir = false;

	        if($file['type']=="folder"){

	        	$isdir = true;
	        }

	 
					$chmod = $file['rights'];

		        array_push($dir,
		        	array(
			        	'filename' => $file['name'],
			        	'directory' => $isdir,
			        	'chmod' => $chmod,
			        	'fullpath' => $path."/".$file['name'],
			        	'filesize' => $file['size'],

		        	)
		        );
		   

		   // closedir($handle);
		}
		$return['success'] = true;
		$content['dir'] = $dir;
		$return['msg'] = $content;

		ftp_close($this->con);
			
	
		return $this->returnData($return);	 
	
	}

	private function instr($haystack, $needle) {
	     if (is_array($needle)) {
	         foreach ($needle as $need) {
	               if (stripos($haystack, $need) !== false) {
	                       return true;
	               }
	         }
	     }else {
	          if (stripos($haystack, $need) !== false) {
	                       return true;
	          }
	     }

	     return false;
	}

	private function is_ascii($content)
	{
	    $content = str_replace(array("\n", "\r", "\t", "\v", "\\"), '', $content);
	    return ctype_print($content);
	}
	private function is_binary($str) {
		
		 
  

		$probably_binary = 0;
		$max_detections = 2;

		$checkarray = array();
	 	$checkarray[0] = substr($str, 0,32);
	 	$checkarray[1] = substr($str, 31,32);
	 	$checkarray[2] = substr($str, 63,32);
	 	$checkarray[3] = substr($str, 127,64);
	 	$checkarray[4] = substr($str, 255,64);
	 	$checkarray[5] = substr($str, 511,64);

	 	foreach ($checkarray as $strpart) {
	 		$strpart = str_replace(array("\n", "\r", "\t", "\v", "\b"), '', $strpart);
	 		$strpart = utf8_encode($strpart);
	 		
	 		if($strpart)
	 			if(is_string($strpart) === true && ctype_print($strpart) === false) $probably_binary++;
	 	}

		if($probably_binary>=$max_detections)
			return true;
		else
			return false;

	}

	public function loadFile($path){


		$this->getTokenFile();
		$this->ftplogin();
		$uid = substr($this->tokenfile->user,0,10).time();

		$local_file = './tmp/tmpfile-'.$uid.'.php';

	 	
		@ftp_get($this->con,$local_file,$this->startdir.$path, FTP_ASCII, 0);


	 

		$dir = array();
		$return = array();
		$return['type'] = "ack_loadfile";

		$return['ackid'] = $this->ackid;


	 	 
	 	$mime = mime_content_type($local_file);



	 	 

		$content = @file_get_contents($local_file);

		$skip = false;
		if($this->instr($mime,array('xml','text','html','csv','php','ruby'))){
			$skip = true;
		}


		if($this->is_binary($content) && !$skip){
		
		//if(!preg_match(':^(\P{Cc}|[\t\n])*$:', $content)){

			$content = false;
			$return['error'] = array('errorcode'=>35);

		} else {
		 
			//
	 	
			$return['msg'] = ($content);

			 if(!json_encode($return)){
			 	$return['msg'] = base64_encode($return['msg']);
			 	$return['b64msg'] = true;
			 	$return['error'] = array('errorcode'=>53);
			 }
			 
			if($content === false||$content === null)
				$return['error'] = array('errorcode'=>34);
			else{
				$return['success'] = true;
				 
			} 
		}

		unlink($local_file);

		ftp_close($this->con);
		return $this->returnData($return);

		
			

	}


	public function saveFile($path){

		$dir = array();
		$return = array();
		$return['type'] = "ack_filesaved";
	 	$return['msg'] = "";
	 	$return['ackid'] = $this->ackid;

	 	if($_REQUEST['content'] == "")
	 		$_REQUEST['content'] = " ";
	 	 

	 	$this->getTokenFile();
		$this->ftplogin();
		
		$uid = substr($this->tokenfile->user,0,10).time();

		$local_file = './tmp/tmpfile-'.$uid.'.php';

		 
		file_put_contents($local_file,($_REQUEST['content']));

		if(ftp_put($this->con,$this->startdir.$path,$local_file, FTP_ASCII))
		$return['success'] = true;
		else
		$return['error'] = "not permitted";

		unlink($local_file);

		ftp_close($this->con);
		return $this->returnData($return);

	}


	public function mkDir($path){

		$dir = array();
		$return = array();
		$return['type'] = "ack_mkdir";
	 	$return['msg'] = "";

	 	 
	 	$return['ackid'] = $this->ackid;

	 	$this->getTokenFile();
		$this->ftplogin();


		if(ftp_mkdir($this->con,$this->startdir.$path))
			$return['success'] = true;
		else
			$return['error'] = "not permitted";


		ftp_close($this->con);
		return $this->returnData($return);


	}

	public function renameFile($file,$to){

		$dir = array();
		$return = array();
		$return['type'] = "ack_renamefile";
	 	$return['msg'] = "";
	 	
	 	 
	 	$return['ackid'] = $this->ackid;

	 	$this->getTokenFile();
		$this->ftplogin();
		  

		if(@ftp_rename($this->con, $this->startdir.$file['fullpath'],$this->startdir.$file['path'].'/'.$to ) )
			$return['success'] = true;
		else
			$return['error'] = "not permitted".$file['path'].'/'.$to;

		ftp_close($this->con);
		return $this->returnData($return);

	}


	public function deleteFiles($paths){

		$dir = array();
		$return = array();
		$return['type'] = "ack_deletefiles";
	 	$return['msg'] = "";
	 	
	 
	 	$return['ackid'] = $this->ackid;

	 	$files = $paths;

		$this->getTokenFile();
		$this->ftplogin();

	 	foreach ($files as $key => $file) {
	 		$this->recursiveDelete($this->con,$this->startdir.$file);
	 	}
	  
	 	$return['success'] = true;
		
		ftp_close($this->con);
		return $this->returnData($return);


	}


	public function copyFiles($files,$topath){

		$dir = array();
		$return = array();
		$return['type'] = "ack_copyfiles";
	 	$return['msg'] = "";
	 	$return['ackid'] = $this->ackid;

	 	$this->getTokenFile();
		$this->ftplogin();

	 	//deleteDirectory('__gocoed_tmp_');
	 	
	 	$uid = substr($this->tokenfile->user,0,10).time();
	  

	 	mkdir('./tmp/__gocoed_tmp_'.$uid);
	 	chmod('./tmp/__gocoed_tmp_'.$uid, 0777);

	  

	 	foreach ($files as $key => $file) {
	 		 	
	 			$file['filename'] = $this->checkfileExsits($topath,$file['filename']);
	 			
	 			 
	 		 	if($file['directory']=="true"){
	 		 		mkdir('./tmp/__gocoed_tmp_'.$uid.'/'.$file['filename']);

	 				$this->ftp_syncdown($this->startdir.$file['fullpath'],'./tmp/__gocoed_tmp_'.$uid.'/'.$file['filename']);

	 				if (@!ftp_chdir($this->con, $this->startdir.$topath.'/'.$file['filename'])) {
	 					
	 					ftp_mkdir($this->con, $this->startdir.$topath.'/'.$file['filename']);

	 					ftp_chdir($this->con, $this->startdir);
	 				}

	 				$this->ftp_upload('./tmp/__gocoed_tmp_'.$uid.'/'.$file['filename'],$this->startdir.$topath.'/'.$file['filename']);

	 			} else {

	 				 ftp_chdir($this->con, $this->startdir);
	 				 
	 				 ftp_get($this->con, './tmp/__gocoed_tmp_'.$uid.'/'.$file['filename'], $this->startdir.$file['fullpath'], FTP_BINARY);

	 				 ftp_put($this->con,$this->startdir.$topath.'/'.$file['filename'], './tmp/__gocoed_tmp_'.$uid.'/'.$file['filename'], FTP_BINARY);

	 			}
	 	}

	 	$this->deleteDirectory('./tmp/__gocoed_tmp_'.$uid);
	  
	 	$return['success'] = true;
		
		
		ftp_close($this->con);
		return $this->returnData($return);

	}



	public function moveFiles($files,$topath){

		$dir = array();
		$return = array();
		$return['type'] = "ack_movefiles";
	 	$return['msg'] = "";
	 	$return['ackid'] = $this->ackid;

	  

	 	$this->getTokenFile();
		$this->ftplogin();

	 	foreach ($files as $key => $file) {

	 		

	  		@ftp_rename($this->con,$this->startdir.$file['fullpath'],$this->startdir.$topath.'/'.$file['filename']);
	 	 
	 	}
	  
	 	$return['success'] = true;
		
	 

		ftp_close($this->con);
		return $this->returnData($return);
	}





	private function returnData($returnArray){
		$hash = $this->crypto->hashData(json_encode($returnArray),$this->config->salt);
		$returnArray['hash'] = $hash;
		echo  $this->crypto->encodeData(json_encode($returnArray));

		//echo json_encode($returnArray);
	}


}

class ConnectorCrypt {





	public function decodeData($data,$salt,$check){

		$hashcheck  = hash_hmac('sha512', $data, $salt);
		 
		if($hashcheck != $check)
			return false;


		$data_decoded = base64_decode($data);
		$_REQUEST = array_merge($_REQUEST,json_decode($data_decoded,true));
	 	
		return true;
	}

	public function hashData($data,$salt){
		$hashcheck  = hash_hmac('sha512', $data, $salt);
		return $hashcheck;
	}

	public function encodeData($data){
		$data_encoded = base64_encode($data);
		return $data_encoded;
	}

	public function specialToken(){


		$p1 =  ""; //hash("sha256",$_SERVER['HTTP_USER_AGENT']);
		$p2 =  "";//md5($_SERVER['HTTP_REFERER']);
		$p3 =  "";// md5($_SERVER['REMOTE_ADDR']);
		$p4 =  "";
		//$p4 =  hash("sha256",$_SERVER['REMOTE_HOST']);
		$p5 =  md5(date('m'));


		return $p1.$p2.$p3.$p4.$p5;
	}

	public function validateToken($token){

	 	
		 
		if(!strstr($token, $this->specialToken())){
			 

			 return false;
		}

		
		if(!$tokenfile = json_decode( str_replace('<?php', '', @file_get_contents('./tmp/user_'.$_REQUEST['username'].'.php')))){
			 
			 return false;

		}

	 

		if(isset($_REQUEST['server']) && $_REQUEST['server'] && $_REQUEST['server'] != "")
			if($tokenfile->server[0].':'.$tokenfile->server[1].$tokenfile->startdir != str_replace('ftp://', '', $_REQUEST['server'].$_REQUEST['startdir'])){
				 
				return false;
			}
			
		 


		
		if($tokenfile->token != $token){
			 
			return false;
		}



		return $tokenfile;
	}

	public function genToken($password){

		$p1 = $this->specialToken();
		$p5 = hash("sha256",$password);

		return $p1.$p5;
	}


}
 

$connector = new ConnectorFTP();




if(isset($_REQUEST['type'])){
	switch ($_REQUEST['type']) {
		case 'login':
				$connector->checkCrypto();
				if(isset($_REQUEST['ackid'])) $connector->ACKID($_REQUEST['ackid']);
				if($_REQUEST['startdir'] == "null") $_REQUEST['startdir'] = "";
				$connector->login($_REQUEST['server'],$_REQUEST['username'],$_REQUEST['password'],$_REQUEST['startdir']);
			break;
		case 'listdir':
				 
				$connector->checkCrypto();
				if(isset($_REQUEST['ackid'])) $connector->ACKID($_REQUEST['ackid']);
				$connector->listdir($_REQUEST['path']);
			break;
		case 'readconnections': 
				 
				if(isset($_REQUEST['ackid'])) $connector->ACKID($_REQUEST['ackid']);
				$connector->readConnections();
			break;
		case 'loadfile':
 				$connector->checkCrypto();
 				if(isset($_REQUEST['ackid'])) $connector->ACKID($_REQUEST['ackid']);
				$connector->loadFile($_REQUEST['path']);
			break;
		case 'savefile':
 				$connector->checkCrypto();
 				if(isset($_REQUEST['ackid'])) $connector->ACKID($_REQUEST['ackid']);
				$connector->saveFile($_REQUEST['path']);
			break;
		case 'mkdir':
 				$connector->checkCrypto();
 				if(isset($_REQUEST['ackid'])) $connector->ACKID($_REQUEST['ackid']);
				$connector->mkDir($_REQUEST['path']);
			break;
		case 'renamefile':
 				$connector->checkCrypto();
 				if(isset($_REQUEST['ackid'])) $connector->ACKID($_REQUEST['ackid']);
				$connector->renameFile($_REQUEST['file'],$_REQUEST['to']);
			break;
		case 'copyfiles':
 				$connector->checkCrypto();
 				if(isset($_REQUEST['ackid'])) $connector->ACKID($_REQUEST['ackid']);
				$connector->copyFiles($_REQUEST['files'],$_REQUEST['topath']);
			break;
		case 'movefiles':
 				$connector->checkCrypto();
 				if(isset($_REQUEST['ackid'])) $connector->ACKID($_REQUEST['ackid']);
				$connector->moveFiles($_REQUEST['files'],$_REQUEST['topath']);
			break;
		case 'deletefiles':
 				$connector->checkCrypto();
 				if(isset($_REQUEST['ackid'])) $connector->ACKID($_REQUEST['ackid']);
				$connector->deleteFiles($_REQUEST['paths']);
			break;
		case 'fileupload':
				if(isset($_REQUEST['ackid'])) $connector->ACKID($_REQUEST['ackid']);
				$connector->fileUpload($_REQUEST['topath']);
			break;
		case 'dropboxfileupload':
			
				$connector->checkCrypto();
				if(isset($_REQUEST['ackid'])) $connector->ACKID($_REQUEST['ackid']);
				$connector->fileUploadDropbox($_REQUEST['files'],$_REQUEST['topath']);
			break;
		default:
			$connector->checkCrypto();
			echo "gocoed running...";
			break;
	}
} else {
	$connector->checkCrypto();
}


?>

