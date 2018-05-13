<?php
/**
* https://yandex.ru/pogoda/
* author Sannikov Dmitriy sannikovdi@yandex.ru
* support page 
* @package project
* @author Wizard <sergejey@gmail.com>
* @copyright http://majordomo.smartliving.ru/ (c)
* @version 0.1 (wizard, 09:04:00 [Apr 04, 2016])
*/
//
//
class online extends module {
/**
*
* Module class constructor
*
* @access private
*/
function online() {
  $this->name="online";
  $this->title="Модуль онлайн";
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
 function edit_classes(&$out, $id) {
  require(DIR_MODULES.$this->name.'/classes_edit.inc.php');
 }

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
        if ((time() - gg('cycle_onlineRun')) < 360*2 ) {
			$out['CYCLERUN'] = 1;
		} else {
			$out['CYCLERUN'] = 0;
		}

 
 $out['DUUID'] = $this->config['DUUID'];
 $out['DEVICEID']=$this->config['DEVICEID'];

	
 $out['EVERY']=$this->config['EVERY'];
 
 if (!$out['UUID']) {
	 $out['UUID'] = md5(microtime() . rand(0, 9999));
	 $this->config['UUID'] = $out['UUID'];
	 $this->saveConfig();
 }
 
 if ($this->view_mode=='update_settings') {
	global $duuid;
	$this->config['DUUID']=$duuid;	 

	global $deviceid;
	$this->config['DEVICEID']=$deviceid;	 

   
   $this->saveConfig();
   $this->redirect("?");
 }
 if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
  $out['SET_DATASOURCE']=1;
 }
 
// if ($this->tab=='' || $this->tab=='outdata') {
//   $this->outdata_search($out);
// }  

 if ($this->tab=='' || $this->tab=='indata') {
    $this->indata_search($out); 
 }

 if ($this->view_mode=='config_edit') {
   $this->config_edit($out, $this->id);
 }

 if ($this->view_mode=='config_check') {
echo "echeck";
   $this->config_check($this->id);
 }

 if ($this->view_mode=='config_uncheck') {
   $this->config_uncheck($this->id);
 }

if ($this->view_mode=='config_mycity') {
   $this->config_mycity($this->id);
 }
	



 if ($this->view_mode=='get') {
setGlobal('cycle_onlineControl','start'); 
		$this->getdatefnc();
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
 
 function indata_search(&$out) {	 
  require(DIR_MODULES.$this->name.'/indata.inc.php');
  require(DIR_MODULES.$this->name.'/cfgdata.inc.php');
 }



 function processCycle() {
   $this->getConfig();

   $every=$this->config['EVERY'];
   $tdev = time()-$this->config['LATEST_UPDATE'];
   $has = $tdev>$every*60;
   if ($tdev < 0) {
		$has = true;
   }
   
   if ($has) {  
$this->getdatefnc();   
		 
	$this->config['LATEST_UPDATE']=time();
	$this->saveConfig();
   } 
  }
/**
* InData edit/add
*
* @access public
*/
 
 function config_edit(&$out, $id) {	
  require(DIR_MODULES.$this->name.'/config_edit.inc.php');
 } 
/**
* InData delete record
*
* @access public
*/
 function config_del($id) {
  $rec=SQLSelectOne("SELECT * FROM yaweather_cities WHERE ID='$id'");
  // some action for related tables
  SQLExec("DELETE FROM yaweather_cities WHERE ID='".$rec['ID']."'");
 }
/**
* InData delete record
*
* @access public
*/
 function config_check($id) {
  $rec=SQLSelectOne("SELECT * FROM yaweather_cities WHERE ID=".$id);
//echo "<br>". implode( $id);
   $rec['check']=1;
SQLUpdate('yaweather_cities',$rec); 
} 


/**
* InData delete record
*
* @access public
*/
 
 function config_uncheck($id) {
  $rec=SQLSelectOne("SELECT * FROM yaweather_cities WHERE ID=".$id);
   $rec['check']=0;
SQLUpdate('yaweather_cities',$rec); 
} 
	
 function config_mycity($id) {
$rec=SQLSelectOne("update yaweather_cities set mycity=0");
SQLExec($rec);
	 
$rec=SQLSelectOne("update yaweather_cities set mycity=1 WHERE ID=".$id );
SQLExec($rec);

	 
} 	
	
 
 
///////////////////////////////////

function  getdatefnc(){
	
	
//////////	
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
  SQLExec('DROP TABLE IF EXISTS yaweather_cities');
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
addClass('online'); 
  
  $data = <<<EOD
online: IPADDR varchar(100)
online: HOSTNAME varchar(100) 
online: RESULT int(30)
online: SEARCH_WORD varchar(255) NOT NULL DEFAULT ''
online: CHECK_LATEST datetime
 online: CHECK_NEXT datetime
 online: SCRIPT_ID_ONLINE int(10) NOT NULL DEFAULT '0'
 online: CODE_ONLINE text
 online: SCRIPT_ID_OFFLINE int(10) NOT NULL DEFAULT '0'
 online: CODE_OFFLINE text
 online: OFFLINE_INTERVAL int(10) NOT NULL DEFAULT '0'
 online: ONLINE_INTERVAL int(10) NOT NULL DEFAULT '0'
 online: LINKED_OBJECT varchar(255) NOT NULL DEFAULT ''
 online: LINKED_PROPERTY varchar(255) NOT NULL DEFAULT ''
 online: COUNTER_CURRENT int(10) NOT NULL DEFAULT '0'
 online: COUNTER_REQUIRED int(10) NOT NULL DEFAULT '0'
 online: STATUS_EXPECTED int(3) NOT NULL DEFAULT '0'
 online: LOG text
EOD;
  parent::dbInstall($data);
setGlobal('cycle_onlineAutoRestart','1');	 
	 		
 }
// --------------------------------------------------------------------

}//////

/*
*
* TW9kdWxlIGNyZWF0ZWQgQXByIDA0LCAyMDE2IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
