<?php
/**
 * entity_Content.
 * 
 * @author fsw
 */

trait Entity_ContentTrait {	

    /** @Field_String(maxLength=256, verboseName='SEO Title', helpText='This will appear as page title') */
    public $seo_title;
    
    /** @Field_Text(verboseName='SEO Description') */
    public $seo_description;
    
    /** @Field_String(maxLength=256, verboseName='SEO Keywords') */
    public $seo_keywords;
    
    /** @Field_String(maxLength=256) */
    public $name;
    
    /** @Field_String(maxLength=256) */
    public $slug;
    
    public function getUrl(){
	return "#";
    }
    
    public function getSeoTitle(){
	  return empty($this->seo_title) ? $this->name : $this->seo_title;
    }
    
    public function getSeoDescription(){
	  return empty($this->seo_description) ? $this->name : $this->seo_description;
    }
    
    public function getSeoKeywords(){
	  return empty($this->seo_keywords) ? $this->name : $this->seo_keywords;
    }

}
