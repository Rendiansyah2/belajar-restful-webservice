<?php

namespace App\Transformers;

use App\Models\Post;
use League\Fractal\TransformerAbstract;

class PostTransformer extends TransformerAbstract
{
    public function transform(Post $post): array
    {
        return [
            'id' => (int) $post->id,
            'title' => (string) $post->title,
            'status' => (string) $post->status,
            'content' => (string) $post->content,
            'user_id' => (int) $post->user_id,
            'created_at' => $post->created_at?->toISOString(),
            'updated_at' => $post->updated_at?->toISOString(),
        ];
    }
}
