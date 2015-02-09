<?php

class Action_AdminUploadImage extends Action_AdminJson
{

    protected function post($post)
    {
        $img = new CommonImage();
        $img->name = $_FILES['image']['name'];
        $img->image = CommonImage::getField('image')->updateWithPost($img->image, $post);
        $img->save();
        return $img->image->getSrc('original');
    }
}
