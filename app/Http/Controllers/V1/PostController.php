<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Transformers\PostTransformer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\Collection;

class PostController extends Controller
{
    // GET /api/v1/posts - dengan pagination
    public function index()
    {
        $posts = Post::paginate(20);
        $fractal = new Manager();

        $resource = new Collection($posts->items(), new PostTransformer());
        $resource->setMeta([
            'total_count' => $posts->total(),
            'limit' => $posts->perPage(),
            'pagination' => [
                'next_page' => $posts->nextPageUrl(),
                'current_page' => $posts->currentPage(),
            ],
        ]);

        return response()->json($fractal->createData($resource)->toArray());
    }

    // GET /api/v1/posts/{id}
    public function show($id)
    {
        $post = Post::find($id);
        if (!$post) {
            abort(404);
        }

        $fractal = new Manager();
        $resource = new Item($post, new PostTransformer());

        return response()->json($fractal->createData($resource)->toArray());
    }

    // POST /api/v1/posts - dengan validation
    public function store(Request $request)
    {
        $input = $request->all();

        $validationRules = [
            'content' => 'required|min:1',
            'title'   => 'required|min:1',
            'status'  => 'required|in:draft,published',
            'user_id' => 'required|exists:users,id'
        ];

        $validator = Validator::make($input, $validationRules);
        if ($validator->fails()) {
            return new JsonResponse(
                ['errors' => $validator->errors()],
                Response::HTTP_BAD_REQUEST
            );
        }

        $post = Post::create($input);

        $fractal = new Manager();
        $resource = new Item($post, new PostTransformer());

        return response()->json($fractal->createData($resource)->toArray(), 201);
    }

    // PUT /api/v1/posts/{id}
    public function update(Request $request, $id)
    {
        $post = Post::find($id);
        if (!$post) {
            abort(404);
        }

        $post->fill($request->all());
        $post->save();

        $fractal = new Manager();
        $resource = new Item($post, new PostTransformer());

        return response()->json($fractal->createData($resource)->toArray());
    }

    // DELETE /api/v1/posts/{id}
    public function destroy($id)
    {
        $post = Post::find($id);
        if (!$post) {
            abort(404);
        }

        $post->delete();
        return response()->json(['message' => 'deleted successfully', 'post_id' => $id]);
    }
}