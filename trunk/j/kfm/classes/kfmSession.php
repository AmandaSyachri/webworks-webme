<?php
class kfmSession extends kfmObject{
	static $instances=array();
	var $vars;
	var $id;
	function kfmSession($key=''){
		global $kfm;
		parent::__construct();
		$create=1;
		if($key=='' && isset($_COOKIE['kfm_cookie']) && strlen($_COOKIE['kfm_cookie'])==32){
			$key=$_COOKIE['kfm_cookie'];
		}
		if($key!='' && strlen($key)==32){
			$res=db_fetch_row("SELECT id FROM ".KFM_DB_PREFIX."session WHERE cookie='".$key."'");
			if(is_array($res) && count($res)){
				$create=0;
				$this->id=$res['id'];
				$this->isNew=false;
				$kfm->db->query("UPDATE ".KFM_DB_PREFIX."session SET last_accessed='".date('Y-m-d G:i:s')."' WHERE id='".$this->id."'");
				// { clean up expired session data
				$old_sessions=db_fetch_all("SELECT id FROM ".KFM_DB_PREFIX."session WHERE last_accessed<'".date('Y-m-d G:i:s',mktime(0, 0, 0, date('m'),date('d')-1,date('Y')))."'");
				if($old_sessions && count($old_sessions)){
					$old_done=0;
					foreach($old_sessions as $r){
						if($old_done++ == 10)break;
						echo 'DELETE FROM '.KFM_DB_PREFIX.'session_vars WHERE session_id='.$r['id'].'<br />';
						$kfm->db->query('DELETE FROM '.KFM_DB_PREFIX.'session_vars WHERE session_id='.$r['id']);
						$kfm->db->query('DELETE FROM '.KFM_DB_PREFIX.'session WHERE id='.$r['id']);
					}
				}
				// }
			}
		}
		if($create){
			$kfm->db->query("INSERT INTO ".KFM_DB_PREFIX."session (last_accessed) VALUES ('".date('Y-m-d G:i:s')."')");
			$this->id=$kfm->db->lastInsertId(KFM_DB_PREFIX.'session','id');
			$key=md5($this->id);
			$kfm->db->query("UPDATE ".KFM_DB_PREFIX."session SET cookie='".$key."' WHERE id=".$this->id);
			$this->isNew=true;
	    $this->setMultiple(array(
		    'cwd_id'   => 1,
		    'language' => '',
		    'user_id'  => 1,
		    'username' => '',
		    'password' => '',
		    'loggedin' => 0,
		    'theme'    => false
	    ));
		}
		$this->key=$key;
		$this->vars=array();
		setcookie('kfm_cookie',$key,0,'/');
	}
	function __construct($key=''){
		$this->kfmSession($key);
	}
	function getInstance($id=0){
		$id=(int)$id;
		if ($id<1) return false;
		if (!@array_key_exists($id,self::$instances)) self::$instances[$id]=new kfmSession($id);
		return self::$instances[$id];
	}
	function set($name='',$value=''){
		global $kfm;
		if(isset($this->vars[$name])&&$this->vars[$name]==$value)return;
		$this->vars[$name]=$value;
		$kfm->db->query("DELETE FROM ".KFM_DB_PREFIX."session_vars WHERE session_id=".$this->id." and varname='".sql_escape($name)."'");
		$kfm->db->query("INSERT INTO ".KFM_DB_PREFIX."session_vars (session_id,varname,varvalue) VALUES (".$this->id.",'".sql_escape($name)."','".sql_escape(json_encode($value))."')");
	}
	function setMultiple($vars){
		foreach($vars as $key=>$val)$this->set($key,$val);
	}
	function get($name){
		if(isset($this->vars[$name]))return $this->vars[$name];
		$res=db_fetch_row("SELECT varvalue FROM ".KFM_DB_PREFIX."session_vars WHERE session_id=".$this->id." and varname='".sql_escape($name)."'");
		if($res && count($res)){
			$ret=json_decode('['.stripslashes($res['varvalue']).']',true);
			if(count($ret))$ret=$ret[0];
			else $ret='';
			$this->vars[$name]=$ret;
			return $ret;
		}
		return null;
	}
	function logout(){
		global $kfm;
		$kfm->db->query("DELETE FROM ".KFM_DB_PREFIX."session_vars WHERE session_id=".$this->id);
		$kfm->db->query("DELETE FROM ".KFM_DB_PREFIX."session WHERE id=".$this->id);
		$this->loggedin=0;
	}
}
