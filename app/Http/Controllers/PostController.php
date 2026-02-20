<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Platform character limits.
     */
    public const PLATFORM_LIMITS = [
        'twitter' => 280,
        'facebook' => 63206,
        'linkedin' => 3000,
        'instagram' => 2200,
    ];

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
        $platformLimits = self::PLATFORM_LIMITS;
        return view('posts.create', compact('accounts', 'platformLimits'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'social_account_ids' => ['required', 'array', 'min:1'],
            'social_account_ids.*' => ['exists:social_accounts,id'],
            'content' => ['required', 'string', 'max:63206'],
            'scheduled_at' => ['nullable', 'date', 'after:now'],
            'image' => ['nullable', 'image', 'max:5120'],
        ]);

        $user = $request->user();
        $accounts = $user->socialAccounts()
            ->whereIn('id', $validated['social_account_ids'])
            ->where('is_active', true)
            ->get();

        if ($accounts->isEmpty()) {
            return back()->with('warning', 'No valid social accounts selected.');
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('post-images', 'public');
        }

        $status = $validated['scheduled_at'] ? 'scheduled' : 'draft';

        // Check schedule limits (count all posts that will be created)
        if ($status === 'scheduled') {
            $currentScheduled = $user->posts()->where('status', 'scheduled')->count();
            $plan = $user->subscriptionPlan();
            if ($plan && ($currentScheduled + $accounts->count()) > $plan->max_scheduled_posts) {
                return back()->with('warning', 'Adding posts to ' . $accounts->count() . ' platforms would exceed your scheduled post limit.');
            }
        }

        $created = 0;
        $warnings = [];

        foreach ($accounts as $account) {
            $content = $validated['content'];
            $limit = self::PLATFORM_LIMITS[$account->platform] ?? 5000;

            // Truncate content to platform limit
            if (mb_strlen($content) > $limit) {
                $content = mb_substr($content, 0, $limit);
                $warnings[] = "{$account->platformLabel()}: content truncated to {$limit} characters";
            }

            Post::create([
                'user_id' => $user->id,
                'social_account_id' => $account->id,
                'content' => $content,
                'image_path' => $imagePath,
                'status' => $status,
                'scheduled_at' => $validated['scheduled_at'] ?? null,
            ]);

            $created++;
        }

        ActivityLog::log($user, 'post.created_multi', null, [
            'platforms' => $accounts->pluck('platform')->toArray(),
            'count' => $created,
        ]);

        $message = "Post {$status} to {$created} platform(s).";
        if (!empty($warnings)) {
            $message .= ' ' . implode('. ', $warnings) . '.';
        }

        return redirect()->route('posts.index')->with('success', $message);
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
        $platformLimits = self::PLATFORM_LIMITS;
        return view('posts.edit', compact('post', 'accounts', 'platformLimits'));
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
            'content' => ['required', 'string', 'max:63206'],
            'scheduled_at' => ['nullable', 'date', 'after:now'],
        ]);

        $account = $request->user()->socialAccounts()->findOrFail($validated['social_account_id']);
        $limit = self::PLATFORM_LIMITS[$account->platform] ?? 5000;
        $content = $validated['content'];

        if (mb_strlen($content) > $limit) {
            $content = mb_substr($content, 0, $limit);
        }

        $status = $validated['scheduled_at'] ? 'scheduled' : 'draft';

        $post->update([
            'social_account_id' => $validated['social_account_id'],
            'content' => $content,
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
