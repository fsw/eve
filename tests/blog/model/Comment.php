<?php

class Comment extends Entity
{

    /** @Field_Relation(to='Post') */
    public $post;

    /** @Field_Relation(to='User') */
    public $user;

    /** @Field_Text */
    public $body;
}