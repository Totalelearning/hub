<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Topic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class AdminTopicController extends Controller
{
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        Gate::authorize('admin-write');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
        ]);

        $name = strtolower(trim($validated['name']));
        $slug = Str::slug($name);

        if (Topic::where('slug', $slug)->exists()) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Topic already exists.'], 422);
            }

            return back()->with('topic_error', 'Topic already exists.');
        }

        $maxSort = Topic::max('sort_order') ?? -1;

        $topic = Topic::create([
            'slug' => $slug,
            'name' => $name,
            'sort_order' => $maxSort + 1,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['name' => $topic->name], 201);
        }

        return back()->with('status', "Topic \"{$name}\" created.");
    }

    public function check(Topic $topic): JsonResponse
    {
        Gate::authorize('admin-access');

        $courseCount = Course::where('topic', $topic->name)->count();

        return response()->json([
            'name' => $topic->name,
            'course_count' => $courseCount,
        ]);
    }

    public function destroy(Topic $topic): RedirectResponse|JsonResponse
    {
        Gate::authorize('admin-write');

        $name = $topic->name;
        $topic->delete();

        if (request()->wantsJson()) {
            return response()->json(['deleted' => $name]);
        }

        return back()->with('status', "Topic \"{$name}\" deleted.");
    }
}
