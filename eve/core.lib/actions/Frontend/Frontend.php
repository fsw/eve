<?php

abstract class Frontend extends Action_HTML5 {
   
   protected function getStylesheetsUrls(){
	  $ret = parent::getStylesheetsUrls();
	  $ret[] = '/assets/frontend.less';
	  $ret[] = '//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css';
          return $ret;
   }
   
   protected function sectionBody(){ ?>
      <div class="container">
	  <?php $this->sectionContent(); ?>
      </div>
   <?php }
   
   protected function sectionContent(){
   
   }
        	
}
