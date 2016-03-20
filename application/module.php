<?php

class module{
	public static $instance;
	public $xml;
	public $fields;
	private $id;
	private $name;
	function module($mdl=null){
		$id=request::getInt('id',0,'get');
		$this->id=$id;
		$name =$id!=0?get($id,'mdl','modules'):request::getCmd('mdl', '','get');
		$this->name = $mdl==null?$name:$mdl;
		$config = $this->getRawParams($this->name);
		$this->xml = $config;
		$this->fields = $this->getFieldsByNames();
	}
	public static function getInstance($mdl=null){
		if(self::$instance instanceof modules){
			return self::$instance;
		}
		self::$instance = new module($mdl);
		return self::$instance;
	}
	public function getParams(){
		$id = $this->id;		
		$db = Factory::getDbo();
		$db->setQuery("SELECT * FROM modules WHERE id = '$id' AND client_id = ".CLIENT." ");
		if($db->query()){
			$data = $db->loadAssoc();
			if(empty($data)) return $db->loadObject;
			$params = json_decode($data['params'],true);
			unset($data['params']);
			$data['params'] = $params;
			//$params = array_merge($data,$params);
			$params = json_decode(json_encode($data));
			return $params;
		}
		return $db->getErrorMsg();
	}
	public function setParams(array $value,$id){
	}
	public function getRawParams($name){
		if(is_numeric($name)){
			$db = Factory::getDbo();
			$db->setQuery("SELECT mdl FROM modules WHERE id ='$name'");
			$name = $db->loadResult();
		}
		$mdl_path = ROOT.DS."mdl".DS.$name.DS.$name.".xml";
		if(file_exists($mdl_path)){
			$content = file_get_contents($mdl_path);
			$xml = new SimpleXMLElement($content);
			$xml = json_decode(json_encode($xml),true);
			return $xml;
		}
		return array();
	}
	public function getFieldConfig(){		
		return $this->xml['config'];
	}
	private function getFields(){		
		return $this->xml['config']['fields']['fieldset']['field'];
	}
	private function getFieldsByNames(){	
		$fields = $this->getFields();	
		$total = count($fields);
		$params = array();
		if(!empty($fields) && $total>1){
			foreach($fields as $attr){
				$params[$attr['@attributes']['name']]=$attr['@attributes'];
			}
		}
		else if(!empty($fields) && $total==1){
			$attr = $fields;
			$params[$attr['@attributes']['name']]=$attr['@attributes'];
		}
		$params = json_decode(json_encode($params));
		return $params;
	}
	public function getByName($name){		
		return $this->fields->$name;
	}
	public function getFieldParams(){
		$fields = $this->getFields();
		$total = count($fields);
		$params = array();
		if(!empty($fields)  && $total>1){
			foreach($fields as $attr){
				$params[$attr['@attributes']['name']]=$attr['@attributes']['default'];
			}
		}
		else if(!empty($fields) && $total==1){
			$attr = $fields;
			$params[$attr['@attributes']['name']]=$attr['@attributes']['default'];
		}
		$params = json_decode(json_encode($params));
		return $params;
	}
	public function getParamsNames(){
		$fields = $this->getFields();
		$total = count($fields);
		$params = array();
		if(!empty($fields) && $total>1){
			foreach($fields as $attr){
				$params[]=$attr['@attributes']['name'];
			}
		}
		else if(!empty($fields) && $total==1){
			$attr = $fields;
			$params[]=$attr['@attributes']['name'];
		}
		$params = json_decode(json_encode($params));
		return $params;
	}
	public function bind(){
		
		$data = request::get('post');
		$names = $this->getParamsNames();
		//dump($names);
		$params=array();
		if(!empty($names)){
			foreach($names as $k){
				if($this->getByName($k)->type == 'file'){
					$h = $this->getByName($k)->height;
					$w = $this->getByName($k)->width;
					if($_FILES[$k]['name']!=''){
						$dir = $this->getByName($k)->dir==''?'image':rtrim(trim($this->getByName($k)->dir,'/'),ROOT.DS);
						$upload = utilities::uploadFile(array("field"=>$k,"url"=>ROOT.DS.$dir,"h"=>$h,"w"=>$w));
						if($this->id !=0){
							$current_file = $this->getParams()->params->$k;
							if(!is_bool($upload)){
								$params[$k]=$dir."/".$upload;
								if($current_file !=''){
									@unlink(ROOT.DS.$current_file);
								}
							}
						}
						else{
							if(!is_bool($upload)){
								$params[$k]=$dir."/".$upload;
							}
							else{
								$params[$k]='';
							}
						}
						
					}
					else{
						if($this->id !=0){
							$current_file = $this->getParams()->params->$k;
							$params[$k] = $current_file;
						}
						else{
							$params[$k]='';
						}
					}
				}
				else{
					/*if(is_array($data[$k])){
						if($this->getByName($k)->type == 'external'){
							$phpsrc =$this->getByName($k)->src_file!=''?ROOT.DS.$this->getByName($k)->src_file:'';
							if(file_exists($phpsrc)){
								require_once($phpsrc);
							}
							$savefunc=$k."_format";
							if(function_exists($savefunc)){
								$params[$k]=$savefunc($data[$k]);
							}
							else{
								$params[$k]=implode(",",$data[$k]);
							}
						}
						else{
							$params[$k]=implode(",",$data[$k]);
						}
					}
					else{
						$params[$k] = $data[$k];
					}*/
					$params[$k] = $data[$k];
				}
				unset($data[$k]);
			}
		}
		$data['params']=$params;
		$data['mdl']=$this->name;
		return $data;
	}
	public function getEscaped(){
		$data = $this->bind();
		$cdata = array();
		if(empty($data))return $data;
		$db = Factory::getDbo();
		foreach($data as $k=>$v){
			if(!is_array($v)){
				$cdata[$k] = $db->escape($v);
			}
			else{
				$cdata[$k] = $v;
			}
		}
		return $cdata;
	}
	public function getAttr($attr_name){
		$fields = $this->getFields();
		$total = count($fields);
		$params = array();
		if(!empty($fields) && $total > 1){
			foreach($fields as $attr){
				$params[$attr['@attributes']['name']]=$attr['@attributes'][$attr_name];
			}
		}
		else if(!empty($fields) && $total == 1){
			$attr = $fields;
			$params[$attr['@attributes']['name']]=$attr['@attributes'][$attr_name];
		}
		$params = array_filter($params);
		$params = json_decode(json_encode($params),true);
		return $params;
	}
	public function save(){
		$db = Factory::getDbo();
		$data = $this->bind();
		//dump($data);
		$menuid = $data['menu_id'];
		unset($data['task']);
		unset($data['menu_id']);
		$id = request::getInt('id',0,'get');
		if(empty($data))return false;
		$fields=array();
		$values=array();
		$update=array();
		foreach($data as $k=>$v){
			if($v !=''){
				$v = is_array($v)?json_encode($v):$v;
				$v = $db->escape($v);
				$fields[] = "`$k`"; 
				$values[]= "'$v'";
				$update[]="`$k`='$v'";
			}
			else{
			}
		}
		$fields = "(".implode(",",$fields).")";
		$values = "(".implode(",",$values).")";
		$update = implode(",",$update);
		$q = $id!=0?"UPDATE modules SET $update WHERE id = $id":"INSERT INTO modules $fields VALUES $values";
		
		$db->setQuery( $q );
		if(!$db->query()){
			url::enqueueMsg($db->getErrorMsg(),'error');
			return false;
		}
		$insertid=$id!=0?'':$db->insertid();
		$q2 = $id!=0?"UPDATE modules_menu SET menu_id='$menuid' WHERE mdl_id = $id":"INSERT INTO modules_menu (mdl_id,menu_id) VALUES ('$insertid','$menuid')";
		$db->setQuery( $q2 );
		if(!$db->query()){
			url::enqueueMsg($db->getErrorMsg()." $insertid",'error');
		}
		return true;
	}
	public function buildForm($extra=array()){
		$fields = $this->getFields();
		$total = count($fields);
		//dump($fields);
		$form ='';
		if(!empty($fields) && $total > 1){
			foreach($fields as $attr){
				$params=$attr['@attributes'];
				$invalue = array_key_exists("invalue",$extra)?$extra["invalue"]->$params['name']:'';
				if($params['type']=='file'){
					$postvalue=request::getVar($params['name'],'','files');
					$datavalue=request::getVar($params['name'],'','post')!=''?$postvalue:'';
				}
				else{
					$tp = is_array($_POST[$params['name']])?'array':'none';
					//echo $tp;
					$postvalue=request::getVar($params['name'],'','post',$tp);
					$datavalue=$postvalue!=''?$postvalue:$invalue;
				}
				$params['value'] = $datavalue;
				//echo $params['name'].":".$postvalue."<br>";
				$fextra = array_key_exists($params['name'],$extra)? (array)$extra[$params['name']]:array();
				$ferr = array_key_exists('error',$extra)? (array)$extra['error']:array();
				$params['error'] = array_key_exists($params['name'],$ferr)? $ferr[$params['name']]:'';
				
				$params = array_merge($params,$fextra);
				$op = array_key_exists('option',$attr)?$attr['option']:array();
				$form .= forms::create($params, $op);
			}
		}
		else if(!empty($fields) && $total==1){
			$attr = $fields;
			$params=$attr['@attributes'];
			$invalue = array_key_exists("invalue",$extra)?$extra["invalue"]->$params['name']:'';
			if($params['type']=='file'){
				$postvalue=request::getVar($params['name'],'','files');
				$datavalue=request::getVar($params['name'],'','post')!=''?$postvalue:'';
			}
			else{
				$tp = is_array($_POST[$params['name']])?'array':'none';
				//echo $tp;
				$postvalue=request::getVar($params['name'],'','post',$tp);
				$datavalue=$postvalue!=''?$postvalue:$invalue;
			}
			$params['value'] = $datavalue;
			//echo $params['name'].":".$postvalue."<br>";
			$fextra = array_key_exists($params['name'],$extra)? (array)$extra[$params['name']]:array();
			$ferr = array_key_exists('error',$extra)? (array)$extra['error']:array();
			$params['error'] = array_key_exists($params['name'],$ferr)? $ferr[$params['name']]:'';
			
			$params = array_merge($params,$fextra);
			$op = array_key_exists('option',$attr)?$attr['option']:array();
			$form .= forms::create($params, $op);
		}
		return $form;
	}
	public function validate(){
		$valid = array();
		foreach($_POST as $k=>$v){
			$req=$this->getByName($k)->reg;
			if($req==1){
				$valid[$k]=utilities::validate(array("var"=>request::getVar($k,'','post')));
			}
		}
		$files = request::get('files');
		if(!empty($files)){
			foreach($files as $k=>$v){
				$req=$this->getByName($k)->req;
				$ftype=$this->getByName($k)->ftype!=''?$this->getByName($k)->ftype:'image';
				$extype=$this->getByName($k)->extype!=''?explode(",",$this->getByName($k)->extype):array('jpg','png','gif');
				$maxSize=$this->getByName($k)->maxSize!=''?$this->getByName($k)->maxSize:1;
				if($req==1 && $this->id==0){
					$valid[$k]=utilities::validateUpload($k,$ftype,$extype,$maxSize);
				}
			}
		}
		return array_filter($valid);
	}
}