<?php
/**
* https://livegpstracks.com/
* @package project
* @author Wizard <sergejey@gmail.com>
* @copyright http://majordomo.smartliving.ru/ (c)
* @version 0.1 (wizard, 09:04:00 [Apr 04, 2016])
*/
//
//
class livegpstracks extends module {
/**
*
* Module class constructor
*
* @access private
*/
function livegpstracks() {
  $this->name="livegpstracks";
  $this->title="livegpstracks.com";
  $this->module_category="<#LANG_SECTION_APPLICATIONS#>";
  $this->checkInstalled();
}
/**
* saveParams
*
* Saving module parameters
*
* @access public
*/
function saveParams($data=0) {
 $p=array();
 if (IsSet($this->id)) {
  $p["id"]=$this->id;
 }
 if (IsSet($this->view_mode)) {
  $p["view_mode"]=$this->view_mode;
 }
 if (IsSet($this->edit_mode)) {
  $p["edit_mode"]=$this->edit_mode;
 }
 if (IsSet($this->tab)) {
  $p["tab"]=$this->tab;
 }
 return parent::saveParams($p);
}



/**
* getParams
*
* Getting module parameters from query string
*
* @access public
*/
function getParams() {
  global $id;
  global $mode;
  global $view_mode;
  global $edit_mode;
  global $tab;
  if (isset($id)) {
   $this->id=$id;
  }
  if (isset($mode)) {
   $this->mode=$mode;
  }
  if (isset($view_mode)) {
   $this->view_mode=$view_mode;
  }
  if (isset($edit_mode)) {
   $this->edit_mode=$edit_mode;
  }
  if (isset($tab)) {
   $this->tab=$tab;
  }
}
/**
* Run
*
* Description
*
* @access public
*/
function run() {
 global $session;
  $out=array();
  if ($this->action=='admin') {
   $this->admin($out);
  } else {
   $this->usual($out);
  }
  if (IsSet($this->owner->action)) {
   $out['PARENT_ACTION']=$this->owner->action;
  }
  if (IsSet($this->owner->name)) {
   $out['PARENT_NAME']=$this->owner->name;
  }
  $out['VIEW_MODE']=$this->view_mode;
  $out['EDIT_MODE']=$this->edit_mode;
  $out['MODE']=$this->mode;
  $out['ACTION']=$this->action;
  $out['TAB']=$this->tab;
  $this->data=$out;
  $p=new parser(DIR_TEMPLATES.$this->name."/".$this->name.".html", $this->data, $this);
  $this->result=$p->result;
}
/**
* BackEnd
*
* Module backend
*
* @access public
*/
function admin(&$out) {
 $this->getConfig();

//        if ((time() - gg('cycle_livegpstracksRun')) < $this->config['TLG_TIMEOUT']*2 ) {
        if ((time() - gg('cycle_livegpstracksRun')) < 360*2 ) {
			$out['CYCLERUN'] = 1;
		} else {
			$out['CYCLERUN'] = 0;
		}

 
 $out['UUID'] = $this->config['UUID'];
 $out['SRV_NAME']=$this->config['SRV_NAME'];
 $out['API_MAC']=$this->config['API_MAC'];
 $out['API_SERVER']=$this->config['API_SERVER'];
 $out['API_PORT']=$this->config['API_PORT'];
 $out['EVERY']=$this->config['EVERY'];
 
 if (!$out['UUID']) {
	 $out['UUID'] = md5(microtime() . rand(0, 9999));
	 $this->config['UUID'] = $out['UUID'];
	 $this->saveConfig();
 }
 
 if ($this->view_mode=='update_settings') {
	global $srv_name;
	$this->config['SRV_NAME']=$srv_name;	 

	global $api_server;
	$this->config['API_SERVER']=$api_server;	 

	global $api_port;
	$this->config['API_PORT']=$api_port;	 

	global $api_mac;
	$this->config['API_MAC']=$api_mac;

	global $every;
	$this->config['EVERY']=$every;
   
   $this->saveConfig();
   $this->redirect("?");
 }
 if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
  $out['SET_DATASOURCE']=1;
 }
 
 //if ($this->tab=='' || $this->tab=='outdata') {
if ($this->tab=='outdata') {
   $this->outdata_search($out);
 }  
 //if ($this->tab=='indata') {
if ($this->tab=='' || $this->tab=='indata') {	
   $this->indata_search($out); 
 }
 if ($this->view_mode=='test') {
setGlobal('cycle_livegpstracksControl','start'); 	 
		$this->readData();

 }
 if ($this->view_mode=='outdata_edit') {
   $this->outdata_edit($out, $this->id);
 }
 if ($this->view_mode=='outdata_del') {
   $this->outdata_del($this->id);
   $this->redirect("?data_source=$this->data_source&view_mode=node_edit&id=$pid&tab=outdata");
 }	
 if ($this->view_mode=='indata_edit') {
   $this->indata_edit($out, $this->id);
 }
 if ($this->view_mode=='indata_del') {
   $this->indata_del($this->id);
   $this->redirect("?data_source=$this->data_source&view_mode=node_edit&id=$pid&tab=indata");
 }	
}
/**
* FrontEnd
*
* Module frontend
*
* @access public
*/
function usual(&$out) {
 $this->admin($out);
}
/**
* OutData search
*
* @access public
*/
 function outdata_search(&$out) {	 
  require(DIR_MODULES.$this->name.'/outdata.inc.php');
 }
/**
* InData search
*
* @access public
*/ 
 function indata_search(&$out) {	 
  require(DIR_MODULES.$this->name.'/indata.inc.php');
 }
/**
* OutData edit/add
*
* @access public
*/
 function outdata_edit(&$out, $id) {	
  require(DIR_MODULES.$this->name.'/outdata_edit.inc.php');
 } 
/**
* OutData delete record
*
* @access public
*/
 function outdata_del($id) {
  $rec=SQLSelectOne("SELECT * FROM lgps_out WHERE ID='$id'");
  // some action for related tables
  SQLExec("DELETE FROM lgps_out WHERE ID='".$rec['ID']."'");
 }
/**
* InData edit/add
*
* @access public
*/
 function indata_edit(&$out, $id) {	
  require(DIR_MODULES.$this->name.'/indata_edit.inc.php');
 } 
/**
* InData delete record
*
* @access public
*/
 function indata_del($id) {
  $rec=SQLSelectOne("SELECT * FROM lgps_in WHERE ID='$id'");
  // some action for related tables
  SQLExec("DELETE FROM lgps_in WHERE ID='".$rec['ID']."'");
 }
 
 function propertySetHandle($object, $property, $value) {
   $this->getConfig();
   $table='lgps_out';
   $properties=SQLSelect("SELECT ID FROM $table WHERE LINKED_OBJECT LIKE '".DBSafe($object)."' AND LINKED_PROPERTY LIKE '".DBSafe($property)."'");
   $total=count($properties);
   if ($total) {
    for($i=0;$i<$total;$i++) {
     //to-do
    }
   }
 }
 function processCycle() {
   $this->getConfig();

   $every=$this->config['EVERY'];
   $tdev = time()-$this->config['LATEST_UPDATE'];
   $has = $tdev>$every*60;
//   if ($tdev < 0) {
		$has = true;
//   }
   
   if ($has) {     

	$this->readData();
		 
	$this->config['LATEST_UPDATE']=time();
	$this->saveConfig();
   } 
 }

 function sendData() {
	 
	 //rtt protocol
$host="5.9.136.109";
$port=3359;
$socket = socket_create(AF_INET, SOCK_STREAM, getprotobyname("tcp"));  // Create Socket
        if (socket_connect($socket, $host, $port)) 
        {  //Connect
//circle 1
       // $sendStr = 'ff 08 00 ff ff ff ff ff ff 4f 2d';  // 16 hexadecimal data
       //  $sendStr = '41 03 51 51 30 54 46 76 56 41 14 98 26 00 97 53 18 80 65';
//410351513054467656 4114 9826009753188065         
//              0351513054467656         
//$sendStr = '410351513054467656 4114 9826009753188065';
//$sendStr = 'AQQ0TFvVA&';
//$sendStr = 'ARR0TFvVA&';
         
         
$imei='351513054467656';
$x='-59.4364251';
$y='-129.9839853';
$speed=0023;
$visota=0014;      
$azimut=123;
$bat="090";
$dt=20130618;
$tm=195430;
$utc='-11';
$sputnik=24;
$gsm=60;         
$gpslbs='A';
$sos=0;         
         
         
$sendStr= "rtt003,$imei,$x,$y,$speed,$visota,$azimut,$bat,$dt,$tm,$utc,$sputnik,$gsm,$gpslbs,".$sos;         
echo    $sendStr;      
         
socket_write ($socket,$sendStr );   // by group data transmission
//$sendStrArray = str_split(str_replace(' ', '', $sendStr), 2);  // The 16 binary data into a set of two arrays
//for ($j = 0; $j <count ($sendStrArray); $j++) {
//socket_write ($socket, Chr (hexdec ($sendStrArray[$j])));   // by group data transmission
//            }
         
//$receiveStr = "";
//$receiveStr = socket_read($socket, 1024, PHP_BINARY_READ);  // The 2 band data received 
//$receiveStrHex = bin2hex ($receiveStr);   // the 2 hexadecimal data convert 16 hex
//echo  "send:".$sendStr ;
//         echo "<br>";
//         echo " answer:" . $receiveStr;   
//         echo " answerSTR:" .hex2str($receiveStrHex);
//         echo " answerHEX:" . $receiveStrHex.'<br>';
//   
}
        socket_close($socket);  // Close Socket


/////////////////////////////////
/////////////////////////////////
//http://livegpstracks.com/forum/viewtopic.php?f=30&t=949
//Облегченный открытый RTT-протокол версии 003

//Сервер: srv1.livegpstracks.com или 5.9.136.109
//Порт: 3359
//Устройство для выбора из списка при подключении: RTT Lite

//Данный протокол реализует однонаправленную передачу данных между терминалом и сервером.
//Информация передаётся по сети интернет с использованием протокола TCP/IP.

//От устройства приходит строка с данными. Пример:
//Код:
//rtt003,356217625371625,-59.4364251,-129.9839853,0023,0014,123,090,20130618,195430,-11,24,60,A,1
//В ответ сервер выдаст "\r\n". Под "\r\n" понимается последовательность 0x0D, 0x0A.

//Расшифровка пакета:
//Протокол: "rtt003", (string) 6 символов
//imei: "356217625371625", (string) 15 символов
//долгота: "-59.4364251", (float) 11 символов
//широта: "-129.9839853", (float) 12 символов
//скорость: "23", (int) 2 символа
//высота: "14", (int) 2 символа
//азимут: "123", (int) 3 символа
//заряд батареи: "90", в процентах, (int) 3 символа
//дата: "20130618", (string) 8 символов
//время: "195430", (string) 6 символов
//UTC: "-11", (int) 3 символа
//количество спутников: "24", (int) 2 символа
//уровень GSM сигнала: "60", (int) 2 символа
//признак GPS или LBS: "A" (координаты определены по GPS или "V" - по LBS), (string) 1 символ
//SOS: "1" (сработала кнопка SOS) или "0" (не нажата), (int) 1 символ
	 
 }
 
 
 function readData() {
	$this->getConfig(); 

	$table='lgps_in';	
	$properties=SQLSelect("SELECT * FROM $table;");

foreach ($properties as $did)
{
$num=$did['DID'];
$title=$did['TITLE'];
//$urls[] = ['url' => 'http://livegpstracks.com/viewer_coos_s.php?code='.$num];
$urls[] = ['url' => 'http://livegpstracks.com/viewer_coos_s.php?code='.$num,'name'=>$title,'numer'=>$num];
}	



		
foreach ($urls as $url1) {
     
//echo $url1['url'];
$title=$url1['name'];
$numer=$url1['numer'];
$content=getURL($url1['url'], 0);  
$data=json_decode($content,true);
//$objn=$data[0]['id'];
$objn=$data[0]['code'];
     
//echo $objn.'----------------';
if ($objn<>'') {
addClassObject('livegpstracks',$objn);
$src=$data[0];
     
$lud=gg($objn.'.d'); $lut=gg($objn.'.d');         
     
     
sg( $objn.'.json',$content);
sg( $objn.'.link','https://livegpstracks.com/dv_'.$objn.'.html'); 
sg( $objn.'.title',$title);     
foreach ($src as $key=> $value ) {
   sg( $objn.'.'.$key,$value);
 //echo $key;
$upd = false;
}     

$rec=SQLSelectOne("SELECT * FROM lgps_in WHERE DID='".$numer."'");
//$rec['VALUE'] = 'ok';
$smadr=$this->getaddrfromcoord(gg($objn.'.lat'),gg($objn.'.lng'));
//$smadr='РЎС“Р В»Р С‘РЎвЂ Р В°';
//$smadr=$this->ga('56.836498','60.691435' );
$rec['VALUE'] = gg($objn.'.lat').','.gg($objn.'.lng') ;
$rec['COORD'] = gg($objn.'.lat').','.gg($objn.'.lng') ;	
//$rec['VALUE'] = $smadr ;
$rec['GPSLBS'] =gg($objn.'.gpslbs'); 
if (gg($objn.'.battery')<>"" ) {$rec['BATTERY'] =gg($objn.'.battery'); }
if (gg($objn.'.perbattery')<>"" ) {$rec['BATTERY'] =gg($objn.'.perbattery'); }	
	
$rec['TEMP'] =gg($objn.'.temper'); 
$rec['DEVICE'] =gg($objn.'.device'); 
$rec['UPDATED'] = date('Y-m-d H:i:s');
SQLUpdate('lgps_in', $rec);
//if ($lud<> gg($objn.'.d')   and  ($lut<> gg($objn.'.t'))) {
    
$url = BASE_URL . '/gps.php?latitude=' . gg($objn.'.lat')
        . '&longitude=' . gg($objn.'.lng')
        . '&altitude=' . gg($objn.'.altitude')
        . '&accuracy=' . gg($objn.'.gpsaccuracy') 
        . '&provider=' . gg($objn.'.cellid') 
        . '&speed='       .gg($objn.'.speed') 
        . '&battlevel=' . gg($objn.'.battery') 
        . '&charging=' . gg($objn.'.charging') 
        . '&deviceid=' . $objn ;
getURL($url, 0);
//$adr=getaddrfromcoord(gg($objn.'.lat'),gg($objn.'.lng'));  
sg($objn.'.address', $adr); 
//$spl=split(',',$adr) ;
$spl=explode(',',$adr) ;
sg($objn.'.short_address', $spl[0]); 
sg($objn.'.gpsupdate', 'updated'); 
//}    
//else {sg($objn.'.gpsupdate', 'no need'); }     
}				

}
						

		}
	
 
   
 
/**
* Install
*
* Module installation routine
*
* @access private
*/
 function install($data='') {
  parent::install();
 }
/**
* Uninstall
*
* Module uninstall routine
*
* @access public
*/
 function uninstall() {
  SQLExec('DROP TABLE IF EXISTS lgps_in');
  SQLExec('DROP TABLE IF EXISTS lgps_out');
  parent::uninstall();
 }
/**
* dbInstall
*
* Database installation routine
*
* @access private
*/
 function dbInstall($data) {
/*
nm_outdata - 
*/
addClass('livegpstracks'); // Р В Р’В Р В Р вЂ№Р В Р’В Р РЋРІР‚СћР В Р’В Р вЂ™Р’В·Р В Р’В Р СћРІР‚ВР В Р’В Р вЂ™Р’В°Р В Р’В Р вЂ™Р’ВµР В Р’В Р РЋР’В Р В Р’В Р РЋРІР‚СњР В Р’В Р вЂ™Р’В»Р В Р’В Р вЂ™Р’В°Р В Р Р‹Р В РЎвЂњР В Р Р‹Р В РЎвЂњ
addClassMethod('livegpstracks','update','SQLUpdate(\'objects\', array("ID"=>$this->id, "DESCRIPTION"=>$this->getProperty("title").\' \'.gg(\'sysdate\').\' \'.gg(\'timenow\'))); ');
//addClassProperty('livegpstracks','t');
addClassProperty('livegpstracks','d',10);

$prop_id=addClassProperty('livegpstracks', 't', 10);
				  if ($prop_id) {
					  $property=SQLSelectOne("SELECT * FROM properties WHERE ID=".$prop_id);
					  $property['ONCHANGE']='update'; //   <-----------
					  SQLUpdate('properties',$property);
				  } 

  
  $data = <<<EOD
 lgps_out: ID int(30) unsigned NOT NULL auto_increment
 lgps_out: TITLE varchar(100) NOT NULL DEFAULT ''
 lgps_out: MAC varchar(100) NOT NULL DEFAULT ''
 lgps_out: LINKED_OBJECT varchar(100) NOT NULL DEFAULT ''
 lgps_out: LINKED_PROPERTY varchar(100) NOT NULL DEFAULT ''
 lgps_out: UPDATED datetime
 lgps_out: ACTIVE int(3) DEFAULT 1  
 
 lgps_in: ID int(30) unsigned NOT NULL auto_increment
 lgps_in: DID varchar(30) NOT NULL
 lgps_in: TITLE varchar(100) NOT NULL DEFAULT ''
 lgps_in: VALUE varchar(100)
 lgps_in: STREET varchar(100)
 lgps_in: UPDATED datetime
 lgps_in: LINKED_OBJECT varchar(100) NOT NULL DEFAULT ''
 lgps_in: LINKED_PROPERTY varchar(100) NOT NULL DEFAULT ''
 lgps_in: COORD varchar(50) 
 lgps_in: BATTERY varchar(30)  
 lgps_in: TEMP varchar(30)   
 lgps_in: DEVICE varchar(100)    
 lgps_in: GPSLBS varchar(100)     
EOD;
  parent::dbInstall($data);
setGlobal('cycle_livegpstracksAutoRestart','1');	 
	 
 }
// --------------------------------------------------------------------

//////
function getaddrfromcoord($x,$y)
{
$url='http://maps.googleapis.com/maps/api/geocode/xml?latlng='.$x.',' .$y.'&sensor=false&language=ru'; 
  $fields = array(
   	'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
	'Accept-Charset: windows-1251,utf-8;q=0.7,*;q=0.3',
	'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.6,en;q=0.4',	'Connection: keep-alive',	'User-Agent: Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/29.0.1547.76 Safari/537.36'     );
foreach($fields as $key=>$value)
{ $fields_string .= $key.'='.urlencode($value).'&'; }
rtrim($fields_string, '&');
   $ch = curl_init();   
   curl_setopt($ch, CURLOPT_URL, $url);
   curl_setopt($ch, CURLOPT_POST, count($fields));   
   curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
   curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
   curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
   curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); 
   $result = curl_exec($ch);
 curl_close($ch);
$xml = simplexml_load_string($result);
$otvet=$xml->result->formatted_address; 
$spl=explode(',',$otvet) ;
return $spl[0] ;
//return $url;
} 
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgQXByIDA0LCAyMDE2IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
