<?php

namespace App\GraphQL\Queries;
use App\Models\Post;


class StepImage
{
    public function getImage($root, array $args, $context, $resolveInfo)
    {
        if($root->label === "icon_image_id"){
            return Post::where('id', $root->value)->first()->guid;
        }
        return null;
    }
}
