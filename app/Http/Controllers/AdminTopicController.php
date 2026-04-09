<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminTopicController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
        ]);

        $name = strtolower(trim($validated['name']));
        $slug = Str::slug($name);

        if (Topic::where('slug', $slug)->exists()) {
            return back()->with('topic_error', 'Topic already exists.');
        }

        $maxSort = Topic::max('sort_order') ?? -1;

        Topic::create([
            'slug' => $slug,
            'name' => $name,
            'sort_order' => $maxSort + 1,
        ]);

        return back()->with('status', "Topic \"{$name}\" created.");
    }

    public function destroy(Topic $topic): RedirectResponse
    {
        $name = $topic->name;
        $topic->delete();

        return back()->with('status', "Topic \"{$name}\" deleted.");
    }
}
