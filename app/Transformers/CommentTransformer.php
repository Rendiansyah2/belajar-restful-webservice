<?php

namespace App\Transformers;

use App\Models\Comment;
use League\Fractal\TransformerAbstract;

class CommentTransformer extends TransformerAbstract
{
    public function transform(Comment $comment): array
    {
        return [
            'id' => (int) $comment->id,
            'comment' => (string) $comment->comment,
            'post_id' => (int) $comment->post_id,
            'user_id' => (int) $comment->user_id,
            'created_at' => $comment->created_at?->toISOString(),
            'updated_at' => $comment->updated_at?->toISOString(),
        ];
    }
}
