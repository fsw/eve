<?php

class CommonImage extends Entity
{

    /** @Field_String(maxLength=256) */
    public $name;

    /** @Field_Image(format='original', sizes={'original'}) */
    public $image;

    public static function getAdminColumns()
    {
        return ['name', 'image'];
    }

    public static function getPlural()
    {
        return 'Common Images';
    }

    public static function getTableName()
    {
        return 'commonImages';
    }
}

