<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    // GET /api/posts → getAllPosts()
    public function index()
    {
        $posts = Post::all();
        return response()->json($posts);
    }

    // GET /api/posts/{id} → getPost()
    public function show($id)
    {
        $post = Post::find($id);
        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }
        return response()->json($post);
    }

    // POST /api/posts → addPost()
    public function store(Request $request)
    {
        $post = Post::create([
            'title'   => $request->title,
            'status'  => $request->status ?? 'draft',
            'content' => $request->content,
            'user_id' => $request->user_id,
        ]);

        return response()->json($post, 201);
    }

    // PUT /api/posts/{id} → updatePost()
    public function update(Request $request, $id)
    {
        $post = Post::find($id);
        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $post->update($request->only(['title', 'status', 'content', 'user_id']));
        return response()->json($post);
    }

    // DELETE /api/posts/{id} → deletePost()
    public function destroy($id)
    {
        $post = Post::find($id);
        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $post->delete();
        return response()->json(['id' => $id, 'deleted' => true]);
    }
}