<?php

class Flatpage extends Entity
{
    
    use Entity_ContentTrait;
    use Entity_TreeTrait;

    /** @Field_Bool */
    public $in_navigation;

    /** @Field_Html */
    public $body;

    /** @Field_Text */
    public $short_description;

    protected function preSave($oldRow, $newRow)
    {
        if ($this->parent->id) {
            if ($this->in_navigation && ! $this->parent->in_navigation) {
                throw new Entity_Exception('This element cant be in navigation because its parent is not');
            }
        }
    }

    public static function getNavigation()
    {
        return self::getManyByQuery('WHERE in_navigation=1 ORDER BY `left`');
    }

    public static function getAdminColumns()
    {
        return ['name', 'slug', 'short_description', 'in_navigation'];
    }

    public function getUrl()
    {
        return Action_ShowFlatpage::lt($this->slug);
    }
}

