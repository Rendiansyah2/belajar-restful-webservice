<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    // GET /api/comments
    public function index()
    {
        $comments = Comment::all();
        return response()->json($comments);
    }

    // GET /api/comments/{id}
    public function show($id)
    {
        $comment = Comment::find($id);
        return response()->json($comment);
    }

    // POST /api/comments
    public function store(Request $request)
    {
        $allowedFields = ['comment', 'post_id', 'user_id'];
        $input = $request->only($allowedFields);

        $comment = Comment::create($input);

        return response()->json($comment, 201);
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