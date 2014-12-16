<?php

class Category extends Entity
{
    use Entity_TreeTrait;
    
    use Entity_VersionableTrait;

    /** @Field_String(minLength=5, maxLength=256) */
    public $name;

    public static function getPopularCategories () {}
}