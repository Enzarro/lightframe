<?php
class FormItem {
	//Basic
	public $label;
	public $name;
	public $value;
	public $prop;
	public $classes;
	public $styles;
	public $data;
	
	public $idonly;
	public $addhidden;
	public $hiddenvalue;
	public $hiddenfeedbackval;
	
	private $type;
	public $params;
	
	public $stack;
	public $wrap;
	public $horizontal;
	public $size;
	
	private $addons;
	private $selectOptionsTemp;
	
	private $validTypes = ["text", "select", "search", "file", "textarea", "static", "checkbox", "password", "table", "image", "upfile"];
	private $validParams = ['type', 'label', 'name', 'value', 'prop', 'horizontal', 'size', 'stack', 'addons', 'wrap', 'class', 'icon'];
	private $excludeData = ['title', 'style', 'class', 'disabled'];
	
	public function __construct($data = null, $common = null) {
		$this->label = "";
		$this->name = "";
		$this->value = "";
		$this->prop = array();
		$this->classes = "";
		$this->styles = [];
		$this->data = [];
		
		$this->idonly = false;
		$this->addhidden = false;
		$this->hiddenvalue = "";
		
		$this->type = "";
		$this->params = array();
		$this->params["includeNone"] = true;
		
		$this->stack = false;
		$this->wrap = true;
		$this->horizontal = false;
		$this->size = "";
		
		$this->addons = [];
		$this->selectOptionsTemp = [];
		
		if ($data && is_array($data)) {
			if ($common && is_array($common)) {
				$data = array_replace_recursive($data, $common);
			}
			foreach (array_keys($data) as $param) {
				if (in_array($param, $this->validParams)) {
					switch ($param) {
						case 'type':
							$this->setType($data[$param], isset($data['type-params'])?$data['type-params']:null);
							break;
						case 'label':
							$this->label = $data[$param];
							break;
						case 'name':
							$this->name = $data[$param];
							break;
						case 'value':
							$this->value = $data[$param];
							break;
						case 'prop':
							$this->prop = array_replace($this->prop, $data[$param]);
							break;
						case 'horizontal':
							$this->horizontal = $data[$param];
							break;
						case 'size':
							$this->size = $data[$param];
							break;
						case 'stack':
							$this->stack = $data[$param];
							break;
						case 'addons':
							foreach ($data[$param] as $addon) {
								$this->setAddon(
									isset($addon['pos'])?$addon['pos']:'l',
									$addon['content'],
									isset($addon['type'])?$addon['type']:'addon'
								);
							}
							break;
						case 'icon':
							
							$this->setAddon(
								'l',
								'<span class="'.$data[$param].'"></span>',
								'addon'
							);
							break;
						case 'wrap':
							$this->wrap = $data[$param];
							break;
						case 'class':
							$this->classes = $data[$param];
							break;
					}
				}
			}
		}
	}
	
	public function build() {
		if (in_array($this->type, $this->validTypes)) {
			return $this->buildItem();
		} else {
			return "false";
		}
	}
	
	public function setBasic($label, $name, $value = "", $required = false, $readonly = false) {
		$this->label = $label;
		$this->name = $name;
		$this->value = $value;
		$this->prop["required"] = $required;
		$this->prop["readonly"] = $readonly;
	}
	
	public function setType($type, $params = null) {
		//Tipo
		if (in_array($type, $this->validTypes)) {
			$this->type = $type;
		}
		//Parametros
		if (!is_null($params)) {
			if ($type == "table") {
                $this->params = $params;
                if (!isset($params['btn-new'])) {
                    $this->params['btn-new'] = true;
				}
				if (!isset($params['btn-confirmall'])) {
                    $this->params['btn-confirmall'] = true;
                }
				// $this->params['config'] = $params['config'];
				// $this->params['empty'] = $params['empty'];
			} else {
				foreach($params as $key => &$param) {
					//Parametros Select
					if ($type == "select" && ($key == "table" || $key == "id" || $key == "text" || $key == "id-alias" || $key == "text-alias" || $key == "where" || $key == "includeNone" || $key == "data")) {
						$this->params[$key] = $param;
					} else {
						unset($param);
					}
				}
			}
			
		}
	}
	
	public function setHidden($value) {
		$this->idonly = true;
		$this->addhidden = true;
		$this->hiddenvalue = $value;
	}
	
	public function setProp($props) {
		foreach($props as $prop) {
			$this->prop[$prop] = true;
		}
	}
	
	public function setAddon($pos, $content, $type = "addon") {
		if ($pos == "l" || $pos == "r") {
			$this->addons[] = array("pos" => $pos, "content" => $content, "type" => $type);
		}
	}

	public function buildItem() {
		$required = "";
		$properties = "";
		$id = "";
		$name = "";
		$stack = "";
		$class = "";
		$style = "";
		$data = "";
		//Properties
		foreach($this->prop as $prop => $stat) {
			if ($stat) {
				$properties .= " ".$prop;
				if ($prop == "required") {
					$required = " ".$prop;
				}
			}
		}
		//ID Only
		if ($this->idonly) {
			$id = $this->name;
			$name = "";
		} else {
			$id = $this->name;
			$name = $this->name;
		}
		//Stack (Bootstrap columns)
		if (is_bool($this->stack)) {
			if ($this->stack) {
				$stack = " col-xs-12 col-md-6";
			} else {
				$stack = " col-xs-12";
			}
		} else {
			$stack = " ".$this->stack;
		}
 		//Classes
		if ($this->classes) {
			$class = " ".$this->classes;
		}
		//Styles
		if ($this->styles && is_array($this->styles)) {
			foreach($this->styles as $sKey => $sVal) {
				$style .= "{$sKey}: {$sVal}; ";
			}
		}
		//Data
		if ($this->data && is_array($this->data)) {
			foreach($this->data as $key => $value) {
				$data .= "data-{$key}='{$value}' ";
			}
		}
		
		//Start build
		ob_start(); ?>

		<?php if ($this->type == "table"): ?>
		<div class="form-group col-sm-12">
			<h3><?=$this->label?></h3>
			<?=$this->buildTable([
				'properties' => $properties,
				'name' => $name,
				'value' => $this->value,
				'config' => [],
				'empty' => []
			])?>
		</div>
		<?php return ob_get_clean(); endif; ?>
		
		<?php if ($this->wrap): //Start Wrap ?>
		
			<?php if (!$this->stack): ?>
			<div class="row elementcontainer" style="<?=$style?>">
			<?php endif; ?>
			<div class="form-group<?=$required.$stack.($this->size?" form-group-".$this->size:"").($this->stack?" elementcontainer":"")?>" style="<?=($this->stack?$style:'').($this->size=="sm"?'margin-bottom: 0px;':'')?>">
				
				<label for="<?=$name?>" class="control-label<?=($this->horizontal?" col-sm-33":"")?>"><?=$this->label?></label>
				
				<?php if($this->horizontal): ?>
				<div class="col-sm-99">
				<?php endif; ?>
				
					<?php if(!empty($this->addons)): ?>
						<!-- <div class="input-group<?php echo ($this->size?" input-group-".$this->size:""); ?>"> -->
						<div class="input-group <?php echo ($this->size?" input-group-".$this->size:""); ?>">
						<?php foreach($this->addons as $addon): ?>
							<?php if($addon['pos'] == 'l'): ?>
								<!-- <span class="input-group-<?=$addon['type']?>"><?=$addon['content']?></span> -->
								<div class="input-group-prepend">
									<span class="input-group-text"><?=$addon['content']?></span>
								</div>
							<?php endif; ?>
						<?php endforeach; ?>
					<?php endif; ?>
					
		<?php endif; //End Wrap ?>
					
					<?php if ($this->type == "text" || $this->type == "search" || $this->type == "password"): /* TEXT */ ?>
					<input type="<?=$this->type?>" class="<?=$class?$class:'form-control'?>" id="<?=$id?>" name="<?=$name?>" placeholder="<?=$this->label?>" value="<?=$this->value?>"<?=$properties?> <?=$data?>>
					
					<?php elseif($this->type == "image"): /* IMAGE */ ?>
					<input type="file" id="<?=$id?>" name="<?=$name?>" accept="image/png, image/jpeg, image/jpg"  data-default-file="<?=$this->value?>" <?=$properties?> >
					
					<?php elseif($this->type == "upfile"): /* FILE */ ?>
					<input type="file" id="<?=$id?>" name="<?=$name?>" accept=".xls, .xlsx, .pdf, .doc, .docx, .csv"  data-default-file="<?=$this->value?>" <?=$properties?> >
					
					<?php elseif($this->type == "textarea"): /* TEXTAREA */ ?>
					<textarea class="<?=$class?$class:'form-control'?>" id="<?=$id?>" name="<?=$name?>" placeholder="<?=$this->label?>" <?=$properties?> <?=$data?>><?=$this->value?></textarea>

					<?php elseif($this->type == "static"): /* STATIC */ ?>
					<p class="<?=$class?$class:'form-control-static'?>" id="<?=$id?>" <?=$properties?> <?=$data?>><?=$this->value?></p>

					<?php elseif($this->type == "select"): /* SELECT */ ?>
					<select
						class="<?=$class?$class:'form-control'?>" 
						<?php if ($id): ?>id="<?=$id?>"<?php endif; ?>
						<?php if ($name): ?>name="<?=$name?>"<?php endif; ?> 
						<?=$properties?> 
						<?=$data?>>

						<?php echo $this->selectOptionsBuilder(); ?>
					</select>

					<?php elseif($this->type == "file"): /* FILE */ ?>
					<div class="input-group">
						<label class="input-group-btn">
							<span class="btn btn-outline-dark">
							<span class="glyphicon glyphicon-paperclip" aria-hidden="true"></span> Buscar... <input type="file" name="file" id="file" style="display: none;" <?=$properties?>>
							</span>
						</label>
						<input type="text" id="<?=$id?>" name="<?=$name?>" class="<?=$class?$class:'form-control'?>" <?=$properties?>>
					</div>

					<?php elseif($this->type == "checkbox"): /* CHECKBOX */ ?>
					<input type="<?=$this->type?>" class="<?=$class?>" id="<?=$id?>" name="<?=$name?>" placeholder="<?=$this->label?>" <?php if($this->value): ?>checked<?php endif; ?><?=$properties?> <?=$data?>>
					
					<?php endif; ?>
					
		<?php if ($this->wrap): //Start Wrap?>
		
					<?php if ($this->addhidden): /* ADD HIDDEN FIELD */ ?>
					<input type="hidden" id="h-<?=$id?>" name="<?=$id?>" value="<?=$this->hiddenvalue?>">
					<?php endif; ?>
					
					<?php if(!empty($this->addons)): ?>
						<?php foreach($this->addons as $addon): ?>
							<?php if($addon['pos'] == 'r'): ?>
								<!-- <span class="input-group-<?=$addon['type']?>"><?=$addon['content']?></span> -->

								<?php if($addon['type'] == "addon"):?>
									<div class="input-group-append">
										<span class="input-group-text"><?=$addon['content']?></span>
									</div>
								<?php else:?>
									<?=$addon['content']?>
								<?php endif; ?>

							<?php endif; ?>
						<?php endforeach; ?>
						</div>
					<?php endif; ?>
					
					<span class="glyphicon form-control-feedback" aria-hidden="true" style="padding-right: 20px;"></span>
					<div class="help-block with-errors"></div>
					
				<?php if($this->horizontal): ?>
				</div>
				<?php endif; ?>
				
			</div>
			<?php if (!$this->stack): ?>
			</div>
			<?php endif; ?>
			
		<?php endif; //End Wrap ?>
		
		<?php return ob_get_clean();
		//End build
	}
	
	public function selectOptionsBuilder($returnArray = false) {
		if (array_key_exists("table", $this->params) || array_key_exists("id", $this->params) || array_key_exists("text", $this->params) || array_key_exists("where", $this->params)) {
			//If table == array
			$options = [];
			
			if (is_array($this->params["table"])) {
				foreach ($this->params["table"] as $items) {
					$option = [];
					//echo json_encode($items);
					//echo count($items);
					$option["id"] = $items[0];
					if (count($items) == 1) {
						//Only 1 Item
						$option["text"] = $items[0];
					} else {
						//More tha 1 item
						if (array_key_exists("data", $this->params)) {
							if (count($items) == 2) {
								$option["text"] = $items[0];
							} elseif (count($items) == 3) {
								$option["text"] = $items[1];
							}
						} else {
							if (count($items) == 2) {
								$option["text"] = $items[1];
							}
						}
						//data-*:
						if (array_key_exists("data", $this->params)) {
							$lastItem = end($items); //Extract last item
							if (is_array($lastItem)) { //Verificar si es array
								if (count($this->params["data"]) == count($lastItem)) { //Si el número de parámetros 'data' conf es igual a los presentes en data seteada al ítem
									$option["data"] = [];
									for($i = 0; $i <= count($this->params["data"])-1; $i++) {
										$option["data"][$this->params["data"][$i][0]] = $lastItem[$i];
									}
								} else {
									echo '<!--'.json_encode($lastItem, JSON_PRETTY_PRINT).PHP_EOL.json_encode($this->params["data"], JSON_PRETTY_PRINT).'-->';
								}
							}
						}
					}
					$options[] = $option;
				}
			} else {
				global $_DB;
				
				if (array_key_exists("id", $this->params)) {
					$id = (array_key_exists("id-alias", $this->params)?$this->params["id-alias"]:$this->params["id"]);
					$text = (array_key_exists("text-alias", $this->params)?$this->params["text-alias"]:$this->params["text"]);
				} else {
					$id = (array_key_exists("text-alias", $this->params)?$this->params["text-alias"]:$this->params["text"]);
					$text = (array_key_exists("text-alias", $this->params)?$this->params["text-alias"]:$this->params["text"]);
				}
				//data-*: SET SELECT STATEMENTS
				$queryData = "";
				if (array_key_exists("data", $this->params)) {
					foreach($this->params["data"] as $data) {
						if (is_array($data) && !empty($data)) {
							if (count($data) == 1 || count($data) == 2) {
								$queryData .= "{$data[0]}".(count($data) == 2?" AS ".$data[1]:"").", ";
							}
						}
					}
                }
                if (!isset($this->params["where"])) $this->params["where"] = '';
				$query =
					"SELECT {$queryData}".
						(array_key_exists("id", $this->params)?"{$this->params["id"]}".(array_key_exists("id-alias", $this->params)?" AS ".$this->params["id-alias"]:"").", ":"").
						"{$this->params["text"]}".(array_key_exists("text-alias", $this->params)?" AS ".$this->params["text-alias"]:"").
					" FROM 
						{$this->params["table"]} {$this->params["where"]}";
				$res = $_DB->queryToArray($query);

                foreach ($res as $reg) {
				// while ($reg = $_DB->to_object($res)) {
                    $reg = (object)$reg;
					$option = [];
					$option["id"] = $reg->$id;
					$option["text"] = $reg->$text;
					//data-*
					if (array_key_exists("data", $this->params)) {
						foreach($this->params["data"] as $data) {
							if (is_array($data) && !empty($data)) {
								if (count($data) == 1 || count($data) == 2) {
                                    $option["data"] = [];
                                    $option["data"][$data[count($data)-1]] = $reg->{$data[count($data)-1]};
								}
							}
						}
					}
					$options[] = $option;
				}
			}
			
			//Save temp
			if (!$this->selectOptionsTemp) {
				$this->selectOptionsTemp = $options;
			}

			//Return Array
			if ($returnArray) {
				return $options;
			}
			
			//Build within groups
			$option = [];
			$output = [];
			$arrValue = $this->value;
			if (!is_array($arrValue)) {
				$arrValue = [$arrValue];
			}
			
			foreach ($options as $option) {
				ob_start();
				?>
<option <?php if(array_key_exists("data", $option)): foreach($option["data"] as $key => $value): if ($key!="group"): ?> <?php if(!in_array($key, $this->excludeData)): ?> data-<?php endif; ?><?=$key?>='<?=$value?>' <?php endif; endforeach; endif; ?> value="<?=$option["id"]?>"<?=( in_array($option["id"], $arrValue)?' selected="selected"':'') ?>><?=$option["text"]?></option>
				<?php
				if (array_key_exists("data", $option) && array_key_exists("group", $option["data"]) && $option["data"]["group"]) {
					$output[$option["data"]["group"]][] = ob_get_clean();
				} else {
					$output['default'][] = ob_get_clean();
				}
			}
			//Print
			ob_start();
			if ($this->params["includeNone"]) {
				echo '<option value="">No hay selección</option>';
			}
			foreach ($output as $gName => $gData) {
				if ($gName == "default") {
					foreach ($gData as $gOpt) {
						echo $gOpt;
					}
				} else {
					?><optgroup label="<?=$gName?>"><?php
					foreach ($gData as $gOpt) {
						echo $gOpt;
					}
					?></optgroup><?php
				}
			}
			return ob_get_clean();
		}
	}
	
	public function selectOptionsTxtByVal($value) {
		if (!$this->selectOptionsTemp) {
			$this->selectOptionsTemp = self::selectOptionsBuilder(true);
		}
		$aRes = array_filter($this->selectOptionsTemp, function($row) use ($value) {
			return $row['id'] == $value;
		});
		if (!empty($aRes)) {
			$row = array_shift($aRes);
			return $row['text'];
		} else {
			return false;
		}
		
	}

	/**
	 * @param string:?formid
	 * @param array:!fields
	 * @param array:?common
	 * @param bool:?roweven
	 */
	public function buildArray($data, $values = null) {
		if (!$data) {
			return "undefined";
		}
		if (isset($data['fields'])) {
			
			extract($data);
			$formid = isset($formid)?$formid:null;
			$roweven = isset($roweven)?$roweven:true;
			$common = isset($common)?$common:[];
			
		} else {
			$formid = null;
			$roweven = true;
			$common = [];
			if (isset($data['formid'])) {
				$formid = $data['formid'];
				unset($data['formid']);
			}
			if (isset($data['roweven'])) {
				$roweven = $data['roweven'];
				unset($data['roweven']);
			}
			if (isset($data['common'])) {
				$common = $data['common'];
				unset($data['common']);
			}
			$fields = $data;
			
		}
		
		ob_start();
		if ($formid) {
			?><form id="<?=$formid?>" enctype="multipart/form-data"><?php
		}
		$i = 0;
		// error_log(print_r($fields));
		foreach(array_keys($fields) as $key) {
			$resource = $fields[$key];
			if (!isset($resource['name']) && !is_numeric($key)) $resource['name'] = $key;
			if ($values) {
				//Setear valor a elemento
				if (is_object($values)) {
					$values = (array)$values;
				}
				if (isset($values[$resource['name']])) {
					$resource['value'] = $values[$resource['name']];
				}
			}
			if ($roweven && $i%2==0) {
				?><div class="row"><?php
			}
			echo (new FormItem($resource, $common))->build();
			if ($roweven && $i%2!=0) {
				?></div><?php
			}
			$i++;
		}
		if ($roweven && $i%2!=0) {
			?></div><?php
		}
		if ($formid) {
			?></form><?php
		}
		return ob_get_clean();
	}

	/**
	 * name, value, config, empty
	 */
	function buildTable(array $data) {
		extract($data);
		$value = json_encode($value, JSON_PRETTY_PRINT);
		$config = json_encode($this->params['config']);
		$empty = isset($this->params['empty'])?json_encode($this->params['empty']):false;
		$addCallback = isset($this->params['addCallback'])?$this->params['addCallback']:false;
		$classes = $this->classes?$this->classes:"table table-bordered table-hypercondensed";
		ob_start(); ?>
		<div id="<?=$name?>" <?=$properties?>>
            <textarea name="<?=$name?>" id="data" style="display: none;"><?=$value?$value:'null'?></textarea>
            <textarea id="config" style="display: none;"><?=$config?></textarea>
			<?php if($empty): ?><pre id="emptyrow" style="display: none;"><?=$empty?></pre><?php endif; ?>
			<table class="<?=$classes?>" style="width: 100%"></table>
			<div class="text-right">
				<?php if ($this->params['btn-new']): ?>
					<button type="button" class="btn btn-link"  id="agregar" <?php if($addCallback): ?>data-callback="<?=$addCallback?>"<?php endif; ?>> <i class="fas fa-plus"></i> Agregar nuevo </button>
				<?php endif; ?>
				<?php if ($this->params['btn-confirmall']): ?>
					<button type="button" class="btn btn-link"  id="apply-all"> <i class="fa fa-check-double"></i> Confirmar todo </button>
				<?php endif; ?>
			</div>

        </div>
		<?php return ob_get_clean();
	}
	
}