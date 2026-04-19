<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    // GET /api/posts/{id}/comments - dengan pagination
    public function index($id)
    {
        $comments = Comment::where('post_id', $id)->paginate(20);
        $data = $comments->items();

        return response()->json([
            'data' => $data,
            'total_count' => $comments->total(),
            'limit' => $comments->perPage(),
            'pagination' => [
                'next_page' => $comments->nextPageUrl(),
                'current_page' => $comments->currentPage()
            ]
        ]);
    }

    // POST /api/posts/{id}/comments - dengan validation
    public function store(Request $request, $id)
    {
        $input = $request->all();

        $validationRules = [
            'comment' => 'required|min:1',
            'user_id' => 'required|exists:users,id'
        ];

        $validator = Validator::make($input, $validationRules);
        if ($validator->fails()) {
            return new JsonResponse(
                ['errors' => $validator->errors()],
                Response::HTTP_BAD_REQUEST
            );
        }

        $comment = Comment::create([
            'comment' => $request->comment,
            'post_id' => $id,
            'user_id' => $request->user_id,
        ]);

        return response()->json(['data' => $comment], 201);
    }

    // GET /api/comments/{id}
    public function show($id)
    {
        $comment = Comment::find($id);
        if (!$comment) {
            abort(404);
        }
        return response()->json($comment);
    }

    // PUT /api/comments/{id}
    public function update(Request $request, $id)
    {
        $comment = Comment::find($id);
        if (!$comment) {
            abort(404);
        }

        $comment->fill($request->only(['comment']));
        $comment->save();
        return response()->json($comment);
    }

    // DELETE /api/comments/{id}
    public function destroy($id)
    {
        $comment = Comment::find($id);
        if (!$comment) {
            abort(404);
        }

        $comment->delete();
        return response()->json(['message' => 'deleted successfully', 'comment_id' => $id]);
    }
}