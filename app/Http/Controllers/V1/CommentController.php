<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Transformers\CommentTransformer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\Collection;

class CommentController extends Controller
{
    // GET /api/v1/posts/{id}/comments - dengan pagination
    public function index($id)
    {
        $comments = Comment::where('post_id', $id)->paginate(20);
        $fractal = new Manager();

        $resource = new Collection($comments->items(), new CommentTransformer());
        $resource->setMeta([
            'total_count' => $comments->total(),
            'limit' => $comments->perPage(),
            'pagination' => [
                'next_page' => $comments->nextPageUrl(),
                'current_page' => $comments->currentPage(),
            ],
        ]);

        return response()->json($fractal->createData($resource)->toArray());
    }

    // POST /api/v1/posts/{id}/comments - dengan validation
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

        $fractal = new Manager();
        $resource = new Item($comment, new CommentTransformer());

        return response()->json($fractal->createData($resource)->toArray(), 201);
    }

    // GET /api/v1/comments/{id}
    public function show($id)
    {
        $comment = Comment::find($id);
        if (!$comment) {
            abort(404);
        }

        $fractal = new Manager();
        $resource = new Item($comment, new CommentTransformer());

        return response()->json($fractal->createData($resource)->toArray());
    }

    // PUT /api/v1/comments/{id}
    public function update(Request $request, $id)
    {
        $comment = Comment::find($id);
        if (!$comment) {
            abort(404);
        }

        $comment->fill($request->only(['comment']));
        $comment->save();

        $fractal = new Manager();
        $resource = new Item($comment, new CommentTransformer());

        return response()->json($fractal->createData($resource)->toArray());
    }

    // DELETE /api/v1/comments/{id}
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