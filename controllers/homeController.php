<?php
class homeController 
{

  private $_view;

  public function __construct() 
  {
    require_once __DIR__  . '/../' . 'library/View.php';
    $this->_view = new View();    
  }


  public function indexAction() 
  {
    
    require_once __DIR__  . '/../' . 'models/homeModel.php';
    $this->_view->title = TITEL_HOME;    
    $this->_view->display('home/index.tpl.php');
  }  
    
    
  public function roomAction()
  {
    require_once __DIR__  . '/../' .  'models/homeModel.php';
    $model = new homeModel();
    $room_name = $model->getRoomNameById($_GET['id']);      
    $lampsByRoomId = $model->getLampsByRoomId($_GET['id']);         
    $this->_view->title = $room_name.TITEL_ROOM;          
    $this->_view->room_name = $room_name;
    $this->_view->lampen = $lampsByRoomId;
    $this->_view->display('room/index.tpl.php');
  }
    
    
  public function lampsAction()
  {
    require_once __DIR__  . '/../' .  'models/homeModel.php';
    $model = new homeModel();    
    $this->_view->title = TITEL_LAMPS; 
      
    if($_POST['send']=="adddevice"){ 
        $newDevice = $model->saveDevice($_POST);        
    }
      
    if($_POST['send']=="editdevice"){ 
        $editDevice = $model->updateDevice($_POST);         
    }    
      
    $lights = $model->getAllDevices();          
    foreach($lights as $lamp)
    {
        $arr['id'] = $lamp['id'];
        $arr['room_id'] = $lamp['room_id'];
        $arr['device'] = $lamp['device'];
        $arr['status'] = $lamp['status'];
        $arr['sunset'] = $lamp['sunset'];  
        $arr['letter']  = $lamp['letter'];
        $arr['code']    = $lamp['code'];
        $arr['sort']    = $lamp['sort'];
        $arr['aktiv']    = $lamp['aktiv'];
        $arr['room'] = $model->getRoomNameById($lamp['room_id']);         
        $array[] = $arr;         
    }   
    $this->_view->lampen = $array;  
    $rooms = $model->getAllRooms();
    $this->_view->rooms = $rooms;
    $this->_view->display('lamps/index.tpl.php');
  }
    
    
  public function roomsAction()
  {
    require_once __DIR__  . '/../' .  'models/homeModel.php';
    $model = new homeModel();
        
    if($_POST['send']=="addroom"){ 
        $newRoom = $model->insertRoom($_POST);        
    }
      
    if($_POST['send']=="editroom"){ 
        $editRoom = $model->updateRoom($_POST);        
    }
      
    $rooms = $model->getAllRooms();
    $this->_view->title = TITEL_ROOMS;    
    $this->_view->rooms = $rooms;
    $this->_view->display('rooms/index.tpl.php');
  }
    
  public function userAction()
  {
    require_once __DIR__  . '/../' .  'models/homeModel.php';
    $model = new homeModel(); 
    if($_POST['send']=="adduser"){            
        # make password
        $_POST['pass'] = $model->makeApiKey("7");        
        # make apikey
        $_POST['apikey'] = $model->makeApiKey("32");  
        # add user in db        
        $newUser = $model->insertUser($_POST);
    }
      
    if($_POST['send']=="edituser"){
        $editUser = $model->updateUser($_POST);
    }
      
      
    $users = $model->getAllUsers();
    $this->_view->title = TITEL_USER;
    $this->_view->users = $users;
    $this->_view->display('user/index.tpl.php');
  }
    
    
  public function settingsAction()
  {
    require_once __DIR__  . '/../' .  'library/Country.php';
    require_once __DIR__  . '/../' .  'models/homeModel.php';             
    $model = new homeModel();
    if($_POST['send']=="save"){ $model->updateSettings($_POST); }   
    $room_name = $model->getRoomNameById($_GET['id']);      
    $lampsByRoomId = $model->getLampsByRoomId($_GET['id']);      
    $timezones = $model->timezone();      
    $this->_view->title = TITEL_SETTINGS;                 
    $this->_view->room_name = $room_name;
    $this->_view->lampen = $lampsByRoomId;
    $this->_view->timezones = $timezones;
    $this->_view->countries = $countries;
    $this->_view->display('settings/index.tpl.php');
  }
    
    
  public function pwchangeAction()
  {
    require_once __DIR__  . '/../' .  'models/homeModel.php';
    $model = new homeModel(); 
    $user = $model->getUser($_SESSION[user_id]);
    $err = "";
    $msg = "";
    if($_POST['send']=="save")
    {        
        if ($_POST['pw'] === $user['pass'])
        {
            if ($_POST['newpw1'] === $_POST['newpw2'])
            {
                $model->updatePassword($_SESSION[user_id], $_POST['newpw1']);   
                $msg = "Passwort wurde geändert!";    
            }else{
                $err = "Passwörter nicht identisch!";    
            }
        }else{
            $err = "Passwort nicht korrekt!";
        }
    }
    $this->_view->err = $err;   
    $this->_view->msg = $msg;  
    $this->_view->title = TITEL_CHPW;      
    $this->_view->display('pwchange/index.tpl.php');
  }
  
        
  public function  getlightsAction() 
  {    
    require_once __DIR__  . '/../' .  'models/homeModel.php';
    $model = new homeModel();
    $lights = $model->getActivDevices();    
    foreach($lights as $lamp)
    {
        $arr['id'] = $lamp['id'];
        $arr['room_id'] = $lamp['room_id'];
        $arr['device']  = $lamp['device'];
        $arr['status']  = $lamp['status'];
        $arr['sunset']  = $lamp['sunset'];
        $arr['letter']  = $lamp['letter'];
	$arr['tempData'] = $this->getTempData($lamp['remoteAddress']);        
        $room = $model->getRoomNameById($lamp['room_id']); 
        $arr['room'] = $room;        
        $array[] = $arr;         
    }    
    echo json_encode($array);    
  } 

  public function getTempData($remoteAddress){
     	if (!isset($remoteAddress) || trim($remoteAddress)===''){
		return "no Address";
	}
	$curl = curl_init();
	// Set some options - we are passing in a useragent too here
	curl_setopt_array($curl, array(
    		CURLOPT_RETURNTRANSFER => 1,
    		CURLOPT_URL => $remoteAddress . '?op=getTemp',
		CURLOPT_TIMEOUT => 1
	));
	// Send the request & save response to $resp
	$resp = curl_exec($curl);
	// Close request to clear up some resources
	curl_close($curl);	
	if (!isset($resp) || trim($resp)===''){
                return "timeout or other error";
        }
	return json_decode($resp);
  } 
    
  public function testAction(){
       if($_GET['id']!=""){
        $str = explode("_", $_GET['id']);
        $lampid = $str[0];
        require_once __DIR__  . '/../' .  'models/homeModel.php';
        $model = new homeModel();
   	$setlamp = $model->setDeviceTimer($lampid,123411);    
	echo $setlamp; 
       }

  }

  public function timerAction(){
	$res="noAction";
	require_once __DIR__  . '/../' .  'models/homeModel.php';
    $model = new homeModel();
    // get timers
	$devices = $model->getDeviceTimer();
    foreach($devices as $device){
        if (time()>$device['timer_time']){
	        for ($x = 0; $x <= 3; $x++) {
                $this->execCommand($device['letter'],$device['code'],"0", $device["remoteAddress"]);
		        usleep(250000);
		    }
	        $res = $model->setDeviceStatus($device['id'],0);
	    }
	}
	echo $res;	
  }
  
  private function execCommand($letter, $code, $command,$remoteAddress){
	$co = $code;
	$codes = explode(";", $co);

        #if we have on and off codes
        if (count($codes)==2){
           if($command=="1" or $command=="2"){
             $co=$codes[0];
           } elseif($command=="0"){
             $co=$codes[1];
           }
        }
        if ($letter == "ir"){
            //execute irsend
            shell_exec('irsend SEND_ONCE '.$co.' ');
        } else {
            if ($letter == "irRemote"){
              // Get cURL resource
              $curl = curl_init();
              // Set some options - we are passing in a useragent too here
              curl_setopt_array($curl, array(
                  CURLOPT_RETURNTRANSFER => 1,
                  CURLOPT_URL => $remoteAddress."/?op=$co"
              ));
              // Send the request & save response to $resp
              $resp = curl_exec($curl);
              // Close request to clear up some resources
              curl_close($curl);

            } else {
                // execute rcswitch-pi
                shell_exec('sudo /home/assafs/workspace/433Utils/RPi_utils/codesend '.$co.' ');
            }
        }
  }

public function setActionInternal($lampid, $state, $timerEnd = 0)
   {
         require_once __DIR__  . '/../' .  'models/homeModel.php';
         $model = new homeModel();
         // get device data
         $device = $model->getDeviceById($lampid);
         if($state=="on"){
 		$lampset="1";
 	} elseif($state=="off"){
 		$lampset="0";
 	} elseif($state=="timer"){
 		$lampset="2";
 	}


         $letter = $device['letter'];
         $co = $device['code'];

 	$this->execCommand($letter, $co, $lampset,$device["remoteAddress"]);
 	// Set device status
         $setlamps = $model->setDeviceStatus($lampid,$lampset);
 	$setlampt = 1;
 	if ($lampset=="2"){
 	    //default timer is 45 minutes;
 	    if ($timerEnd<=0){
 	        $timerEnd = time()+45*60;
 	    }
 		$setlampt = $model->setDeviceTimer($lampid, $timerEnd);
 	}
         echo ($setlamps or $setlampt);
   }

  public function setAction(){
    if($_GET['id']!=""){
        $str = explode("_", $_GET['id']);
        $lampid = $str[0];
        $state = $str[1];
        $this->setActionInternal($lampid, $state);
    }
  }

  public function alloffAction()
  {    
        require_once __DIR__  . '/../' .  'models/homeModel.php';
        $model = new homeModel();
        $lights = $model->getDeviceOn();        
        foreach($lights as $light){
            $stat = "0";    
            $letter = $this->letter($light['letter']);            
            // execute rcswitch-pi
            shell_exec('sudo /home/div/rcswitch-pi/send '.$light['code'].' '.$letter.' '.$stat.' ');      
            // Set device status
            $model->setDeviceStatus($light['id'],$stat);
        }
        echo 1;      
  }

    
  public function deldeviceAction()
  { 
        if($_GET['id']!=""){
            require_once __DIR__  . '/../' .  'models/homeModel.php';
            $model = new homeModel();
            echo $model->delDevice($_GET['id']);
        }
  }
    
    
  public function updatesunsetAction()
  {         
        if($_GET['id']!=""){
            require_once __DIR__  . '/../' .  'models/homeModel.php';
            $model = new homeModel();
            echo $model->updateDeviceSunset($_GET['id'], $_GET['set']);
        }
  }  
    
    
  public function updateaktivAction()
  { 
        if($_GET['id']!=""){
            require_once __DIR__  . '/../' .  'models/homeModel.php';
            $model = new homeModel();
            echo $model->updateDeviceAktiv($_GET['id'], $_GET['set']);
        }
  }

    
  public function delroomAction()
  { 
        if($_GET['id']!=""){
            require_once __DIR__  . '/../' .  'models/homeModel.php';
            $model = new homeModel();
            echo $model->delRoom($_GET['id']);
        }
  }

    
  public function deluserAction()
  { 
        if($_GET['id']!=""){
            require_once __DIR__  . '/../' .  'models/homeModel.php';
            $model = new homeModel();
            echo $model->delUser($_GET['id']);
        }
  }      
    
    
  private function letter($code)
  {
        if($code=="A"){
                $letter = "1";
        }elseif($code=="B"){
                $letter = "2";
        }elseif($code=="C"){
                $letter = "3";
        }elseif($code=="D"){
                $letter = "4";
        }elseif($code=="E"){
                $letter = "5";
        }
        return $letter;
  }
    
  
}
