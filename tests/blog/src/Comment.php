<?php
class Comment extends Entity
{

    /** @Field_Relation(toEntity='Post') */
    public $post;

    /** @Field_Relation(toEntity='User') */
    public $user;

    /** @Field_Text */
    public $body;
}