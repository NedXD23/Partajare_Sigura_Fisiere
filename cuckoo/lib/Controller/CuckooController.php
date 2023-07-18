<?php
namespace OCA\Cuckoo\Controller;

use OCP\AppFramework\Controller;
use OCP\IRequest;
use OC\Files\Filesystem;
use OCP\AppFramework\Http\JSONResponse;
use OCP\Files\IRootFolder;
class CuckooController extends Controller {

		protected $language;
		private $storage;
		public function __construct($appName, IRequest $request) {

				parent::__construct($appName, $request);

				// get i10n
				$this->language = \OC::$server->getL10N('cuckoo');
				

		}

		
	  public function send($source) {

		// ## Set yout sandbox's api url for file submit
		// eg: http://YOU_HOST:PORT/tasks/create/file
		$cuckoo_api_url = 'http://127.0.0.1:8090/tasks/create/file';
	
		
		$myfile = Filesystem::getLocalFile($source);

		// initialise the curl request
		$request = curl_init($cuckoo_api_url);
		curl_setopt($request, CURLOPT_HTTPHEADER,array("Authorization: Bearer 0J2iLFbPtDQKGePyu0InwA"));
		$cfile = curl_file_create($myfile);
		$post_file=array('file' => $cfile);
//		$post_file = '';

		// send a file
		curl_setopt($request, CURLOPT_POST, true);
		curl_setopt($request, CURLOPT_POSTFIELDS,$post_file);

		// output the response
		curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
		$result =  curl_exec($request);

		// close the session
		curl_close($request);
		// url
		$url1='http://localhost:8090/tasks/view/';
		$json = json_decode($result, true);
		$task_id=$json["task_id"];
		$cuckoo_url=$url1.$task_id;
		// check if the report its DONE!
		while(1){
		// init curl
		$request1 = curl_init($cuckoo_url);
		curl_setopt($request1, CURLOPT_HTTPHEADER,array("Authorization: Bearer 0J2iLFbPtDQKGePyu0InwA"));
		///output
		curl_setopt($request1, CURLOPT_RETURNTRANSFER, true);
		$result1 =  curl_exec($request1);
		//close the session
		curl_close($request1);
		$json1=json_decode($result1,true);
		$result1=$json1["task"]["status"];
		if(strcmp($result1,"reported")==0)
		{
		break;
		}
		
		sleep(30);
		}
		// Get report
		// url
		$url="http://localhost:8090/tasks/report/".$task_id;
		$cuckoo_url=$url."/html";
		$request1 = curl_init($cuckoo_url);
		
		curl_setopt($request1, CURLOPT_HTTPHEADER,array("Authorization: Bearer 0J2iLFbPtDQKGePyu0InwA"));
		// output
		curl_setopt($request1, CURLOPT_RETURNTRANSFER, true);
		$result1 =  curl_exec($request1);
		// close the session
		curl_close($request1);
		// Get Score
		$request2 = curl_init($url);
		curl_setopt($request2, CURLOPT_HTTPHEADER,array("Authorization: Bearer 0J2iLFbPtDQKGePyu0InwA"));
		// output
		curl_setopt($request2, CURLOPT_RETURNTRANSFER, true);
		$result2 =  curl_exec($request2);
		curl_close($request2);
		$json=json_decode($result2,true);
		$score=$json["info"]["score"];
		// set color and text //
		$scorex=floatval($score);
		$Name='';
		$Name=$json["target"]["file"]["yara"][0]["name"];
		$html='';
		// Print Message in function by Score
		if($scorex<1)
		{
		$display1="<html><div style=' border: 2px solid Green;background-color:#defcd8; color:#1ac007;margin-left:10px;margin-right:10px;'><p>This file is  appears fairly benign with a score of </p><p style='font-weight: bold;'>".$scorex;
		$display2=$display1." out of 10.";
		$html=$display2."</p></div></html>";
		}
		if($scorex>=1 && scorex<4)
		{
		$display1="<html><div style=' border: 2px solid Blue;background-color:#ccccff; color:#0000b3;margin-left:10px;margin-right:10px;'><p>This file shows some signs of potential malicious behavior.The score of this file is  a score of </p><p style='font-weight: bold;'>".$scorex;
		$display2=$display1." out of 10.";
		$html=$display2."</p></div></html>";
		}
		if($scorex<=7 && $scorex>=4)
		{
		$display1="<html><div style=' border: 2px solid yellow;background-color:#fefee1; color:#b5b806;margin-left:10px;margin-right:10px;'><p>This file shows numerous signs of malicious behavior. </p><p>The score of this file is </p><p style='font-weight: bold;text-align: center;'>".$scorex;
		$display2=$display1." out of 10.";
		if(!empty($Name)){
		$html1=$display2."<br>Malware Name by Yara: ";
		$html3=$html1.$Name;
		$html=$html3."</p></div></html>";
		}else{
		$html=$display2."</p></div></html>";
		}
		}
		if($scorex>7)
		{
		$display1="<html><div style=' border: 2px solid ;background-color:#ffeaea; color:#c41e05;margin-left:10px;margin-right:10px;'><p>This file is</p> <p style='font-weight: bold;'>very suspicious,</p><p>with a score of </p> <p style='font-weight: bold;'>".$scorex;
		$display2=$display1." out of 10.";
		if(!empty($Name)){
		$html1=$display2."<br>Malware Name by Yara: ";
		$html3=$html1.$Name;
		$html=$html3."</p></div></html>";
		}else{
		$html=$display2."</p></div></html>";
		}
		}
		
		return $html.$result1;

 	  }
		

		/**
		 * callback function to get md5 hash of a file
		 * @NoAdminRequired
		 * @param (string) $source - filename
		 * @param (string) $type - hash algorithm type
		 */
	  public function check($source, $type) {
	  		if(!$this->checkAlgorithmType($type)) {
	  			return new JSONResponse(
							array(
									'response' => 'error',
									'msg' => $this->language->t('The algorithm type "%s" is not a valid or supported algorithm type.', array($type))
							)
					);
	  		}

				if($hash = $this->getHash($source, $type)){
						return new JSONResponse(
								array(
										'response' => 'success',
										'msg' => $hash
								)
						);
				} else {
						return new JSONResponse(
								array(
										'response' => 'error',
										'msg' => $this->language->t('File not found.')
								)
						);
				};

	  }

	  protected function getHash($source, $type) {

	  	if($info = Filesystem::getLocalFile($source)) {
	  			return hash_file($type, $info);
	  	}

	  	return false;
	  }

	  protected function checkAlgorithmType($type) {
	  	$list_algos = hash_algos();
	  	return in_array($type, $this->getAllowedAlgorithmTypes()) && in_array($type, $list_algos);
	  }

	  protected function getAllowedAlgorithmTypes() {
	  	return array(
				'md5',
				'sha1',
				'sha256',
				'sha384',
				'sha512',
				'crc32'
			);
		}
}

