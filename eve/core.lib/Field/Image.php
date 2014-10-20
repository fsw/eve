<?php

class Image implements JsonSerializable {
	
	private $entity;
	private $name;
	private $id;
	private $ext;
	private $sizes;
	
	public function jsonSerialize() {
		return ['thumb' => $this->getSrc($this->sizes[count($this->sizes) - 1]), 'image' => $this->getSrc($this->sizes[0])];
	}

	function __construct($entity, $name, $id, $ext, $sizes){
		$this->entity = $entity;
		$this->name = $name;
		$this->id = $id;
		$this->ext = $ext;
		$this->sizes = $sizes;
		$this->sizes[] = '20x20';
	}
	
	function getId(){
		return $this->id;
	}
	
	function isEmpty(){
		return empty($this->id);
	}
	
	function getExt(){
		return $this->ext;
	}

	function getImg($size) {
		if ($size == 'original'){
			return '<img src="' . $this->getSrc($size) . '"/>';
		} else {
			list($width, $height) = split('x', $size);
			return '<img src="' . $this->getSrc($size) . '" width="' . $width . '" height="' . $height . '"/>';
		}

	}
	
	function getSrc($size) {
		return EVE_UPLOADS_URL . $this->entity . '/' . $this->name . '_' . sprintf('%04d', $this->id) . '_' . $size . '.' . $this->ext;
	}
	
	function __toString(){
		return $this->getImg($this->sizes[count($this->sizes) - 1]);
	}


}

class Field_Image extends Field {

        public $sizes;
        public $format;
        
	public function updateWithPost($value, $post) {
		//var_dump($_FILES[$this->name]);
		if (empty($_FILES[$this->name])) {
			return $value;
		}
		if (!empty($_FILES[$this->name]['error'])) {
			if(!empty($post[$this->name . '_delete']) && ($post[$this->name . '_delete']=='delete')){
			      //var_dump($this->name, $_FILES, $post); die();
			      return new Image($this->entity, $this->name, 0, 'jpg', array('original'));
			}
			return $value;
			throw new Field_Exception($this->name, "Sorry no bonus here");
		}
		$tmp_path = $_FILES[$this->name]['tmp_name'];
		if (($tmp_info = getimagesize($tmp_path)) === false) {
			throw new Field_Exception($this->name, "File type seems invalid (allowed types are jpg, png, gif)");
		}
		
		list($originalWidth, $originalHeight, $type, $attr) = $tmp_info;
		switch ($type) {
			case IMAGETYPE_GIF  : $src_img = imagecreatefromgif($tmp_path);  break;
			case IMAGETYPE_JPEG : $src_img = imagecreatefromjpeg($tmp_path); break;
			case IMAGETYPE_PNG  : $src_img = imagecreatefrompng($tmp_path);  break;
			default : throw new Field_Exception($this->name, "File type seems invalid (allowed types are jpg, png, gif)");
		}
			
		$output_sizes = $this->sizes;
		$output_sizes[] = '20x20';
		$output_format = $this->format;
		if ($output_format == 'original'){
			$output_format = ($type == IMAGETYPE_GIF ? 'gif' : ($type == IMAGETYPE_JPEG ? 'jpg' : 'png'));
		}
		
		$files = glob(EVE_UPLOADS_ROOT . $this->entity . '/' . $this->name . '_*');
		natsort($files);
		$highest = end($files); //.{png,jpg,gif}
		if (!sscanf($highest, EVE_UPLOADS_ROOT . $this->entity . '/' . $this->name . '_%d', $new_id)) {
			$new_id = 111;
		} else {
			$new_id ++;
		}
			
		foreach($output_sizes as $out_size){
			if ($out_size == 'original'){
				$width = $originalWidth;
				$height = $originalHeight;
			} else {
				list($width, $height) = split('x', $out_size);
			}
			//var_dump($output_sizes, $output_format, $width, $height); die();
			
			$new_img = imagecreatetruecolor($width, $height);
			
			//method FILL
			if (($width / $originalWidth) < ($height / $originalHeight)){
			      $cropTop = 0;
			      $cropLeft = ($originalWidth - ($width * ($originalHeight / $height))) / 2;
			} else {
			      $cropTop = ($originalHeight - ($height * ($originalWidth / $width))) / 2;
			      $cropLeft = 0;
			}
			//TODO method FIT
			//var_dump($cropTop, $cropLeft); die();
			
			imagecopyresampled($new_img, $src_img, 0, 0, $cropLeft, $cropTop, $width, $height, $originalWidth - (2*$cropLeft), $originalHeight - (2*$cropTop));
			
			$new_path = EVE_UPLOADS_ROOT . $this->entity . '/' . $this->name . '_' . sprintf('%04d', $new_id) . '_' . $out_size;
			mkdir(dirname($new_path), 0755, true);
			
			//var_dump($new_path); die();
			
			switch ($output_format) {
				case 'gif'  : imagegif($new_img,  $new_path . '.gif');      break;
				case 'jpg' : imagejpeg($new_img, $new_path . '.jpg', 90); break;
				case 'png'  : imagepng($new_img,  $new_path . '.png', 9);   break;
			}
		}
		//die($new_path);
		return new Image($this->entity, $this->name, $new_id, $output_format, $this->sizes);
	}
	
        public function getDefault() {
		return new Image($this->entity, $this->name, 0, 'jpg', array('original'));
        }
        
	public function fromDbRow($row){
		return new Image($this->entity, $this->name, $row[$this->name], ($this->format == 'original') ? $row[$this->name . '_ext'] : $this->format, $this->sizes);
        }

	public function toDbRow($value){
		if ($this->format == 'original') {
		    return [$this->name => $value->getId(), $this->name. '_ext' => $value->getExt()];
		}
		return [$this->name => $value->getId()];
        }

	public function getFormInput($value) {
		$ret = '<div class="clearfix">';
		$ret .= '<img class="pull-left" style="margin-right:40px;" src="' . $value->getSrc('20x20') . '" width="20" height="20"/>';
		$ret .= '<input class="pull-left" type="file" name="' . $this->name . '"  />';
		$ret .= '<label class="pull-left"><input type="checkbox" name="' . $this->name . '_delete" value="delete" /> delete</label>';
		$ret .= '</div>';
		return $ret;
	}
	
        public function getDbDefinition()
	{
		if ($this->format == 'original') {
		    return [$this->name => 'int(11) NOT NULL', $this->name. '_ext' => 'char(3) NOT NULL'];
		}
		return [$this->name => 'int(11) NOT NULL'];
	}
}
