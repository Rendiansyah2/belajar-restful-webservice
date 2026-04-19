<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    // GET /api/posts/{id}/comments
    public function index($id)
    {
        $comments = Comment::where('post_id', $id)->get();
        return response()->json($comments);
    }

    // POST /api/posts/{id}/comments
    public function store(Request $request, $id)
    {
        $comment = Comment::create([
            'comment' => $request->comment,
            'post_id' => $id,      // diambil dari URL, bukan dari body
            'user_id' => $request->user_id,
        ]);

        return response()->json($comment, 201);
    }

    // GET /api/comments/{id}
    public function show($id)
    {
        $comment = Comment::find($id);
        return response()->json($comment);
    }

    // PUT /api/comments/{id}
    public function update(Request $request, $id)
    {
        $comment = Comment::find($id);
        $comment->update($request->only(['comment']));
        return response()->json($comment);
    }

    // DELETE /api/comments/{id}
    public function destroy($id)
    {
        Comment::destroy($id);
        return response()->json(['id' => $id, 'deleted' => 'true']);
    }
}