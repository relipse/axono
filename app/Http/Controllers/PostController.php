<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->user()->posts()->with('socialAccount')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('platform')) {
            $query->whereHas('socialAccount', function ($q) use ($request) {
                $q->where('platform', $request->platform);
            });
        }

        $posts = $query->paginate(20);

        return view('posts.index', compact('posts'));
    }

    public function create(Request $request)
    {
        $accounts = $request->user()->socialAccounts()->where('is_active', true)->get();
        return view('posts.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'social_account_id' => ['required', 'exists:social_accounts,id'],
            'content' => ['required', 'string', 'max:5000'],
            'scheduled_at' => ['nullable', 'date', 'after:now'],
            'image' => ['nullable', 'image', 'max:5120'],
        ]);

        // Verify ownership of the social account
        $account = $request->user()->socialAccounts()->findOrFail($validated['social_account_id']);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('post-images', 'public');
        }

        $status = $validated['scheduled_at'] ? 'scheduled' : 'draft';

        if ($status === 'scheduled' && !$request->user()->canScheduleMore()) {
            return back()->with('warning', 'You have reached your scheduled post limit. Upgrade your plan for more.');
        }

        $post = Post::create([
            'user_id' => $request->user()->id,
            'social_account_id' => $account->id,
            'content' => $validated['content'],
            'image_path' => $imagePath,
            'status' => $status,
            'scheduled_at' => $validated['scheduled_at'] ?? null,
        ]);

        ActivityLog::log($request->user(), 'post.created', $post);

        return redirect()->route('posts.index')
            ->with('success', 'Post ' . ($status === 'scheduled' ? 'scheduled' : 'created as draft') . ' successfully.');
    }

    public function show(Post $post)
    {
        $this->authorize('view', $post);
        $post->load('socialAccount');
        return view('posts.show', compact('post'));
    }

    public function edit(Post $post)
    {
        $this->authorize('update', $post);

        if (!in_array($post->status, ['draft', 'scheduled', 'failed'])) {
            return redirect()->route('posts.index')
                ->with('warning', 'Published posts cannot be edited.');
        }

        $accounts = $post->user->socialAccounts()->where('is_active', true)->get();
        return view('posts.edit', compact('post', 'accounts'));
    }

    public function update(Request $request, Post $post)
    {
        $this->authorize('update', $post);

        if (!in_array($post->status, ['draft', 'scheduled', 'failed'])) {
            return redirect()->route('posts.index')
                ->with('warning', 'Published posts cannot be edited.');
        }

        $validated = $request->validate([
            'social_account_id' => ['required', 'exists:social_accounts,id'],
            'content' => ['required', 'string', 'max:5000'],
            'scheduled_at' => ['nullable', 'date', 'after:now'],
        ]);

        $request->user()->socialAccounts()->findOrFail($validated['social_account_id']);

        $status = $validated['scheduled_at'] ? 'scheduled' : 'draft';

        $post->update([
            'social_account_id' => $validated['social_account_id'],
            'content' => $validated['content'],
            'status' => $status,
            'scheduled_at' => $validated['scheduled_at'] ?? null,
            'error_message' => null,
        ]);

        ActivityLog::log($request->user(), 'post.updated', $post);

        return redirect()->route('posts.index')
            ->with('success', 'Post updated successfully.');
    }

    public function destroy(Request $request, Post $post)
    {
        $this->authorize('delete', $post);

        ActivityLog::log($request->user(), 'post.deleted', $post);

        $post->delete();

        return redirect()->route('posts.index')
            ->with('success', 'Post deleted.');
    }
}
