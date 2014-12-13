<?php

class Post extends Entity
{

    use Entity_ContentTrait;

    /** @Field_Relation(toEntity='Category') */
    public $category;

    /** @Field_Html */
    public $body;

    /** @Field_DateTime(default='now') */
    public $created;

    public static function getNewest ($page = 1) {
        return static::getManyByQuery('ORDER BY created DESC LIMIT 10');
    }

    public function postAdd () {
        Eve::async([
        'Post',
        'postToFacebookAboutNewPost'
                ], $this->id, 5000);
    }

    public function postToFacebookAboutNewPost ($id) {
        $post = static::getById($id);
        // some fancy logic here
    }
}