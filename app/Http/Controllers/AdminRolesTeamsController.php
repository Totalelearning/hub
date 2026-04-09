<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Team;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminRolesTeamsController extends Controller
{
    public function index(): View
    {
        Gate::authorize('admin-access');

        return view('app.admin-roles-teams', [
            'roles' => Role::ordered()->get(),
            'teams' => Team::ordered()->get(),
        ]);
    }

    public function storeRole(Request $request): RedirectResponse
    {
        Gate::authorize('admin-access');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $slug = Str::slug($validated['name'], '_');

        if (Role::where('slug', $slug)->exists()) {
            return redirect()->route('app.admin.roles-teams.index')
                ->withErrors(['name' => 'A role with this name already exists.'])
                ->withInput();
        }

        $maxOrder = Role::max('sort_order') ?? 0;

        Role::create([
            'slug' => $slug,
            'name' => trim($validated['name']),
            'sort_order' => $maxOrder + 1,
        ]);

        return redirect()->route('app.admin.roles-teams.index')
            ->with('status', 'Role created.');
    }

    public function updateRole(Request $request, Role $role): RedirectResponse
    {
        Gate::authorize('admin-access');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $oldName = $role->name;
        $newName = trim($validated['name']);
        $newSlug = Str::slug($newName, '_');

        if ($newSlug !== $role->slug && Role::where('slug', $newSlug)->exists()) {
            return redirect()->route('app.admin.roles-teams.index')
                ->withErrors(['name' => 'A role with this name already exists.'])
                ->withInput();
        }

        // Update user_preferences that reference the old name
        if ($oldName !== $newName) {
            \App\Models\UserPreference::where('role', $oldName)->update(['role' => $newName]);
        }

        $role->update([
            'slug' => $newSlug,
            'name' => $newName,
        ]);

        return redirect()->route('app.admin.roles-teams.index')
            ->with('status', 'Role updated.');
    }

    public function destroyRole(Request $request, Role $role): RedirectResponse
    {
        Gate::authorize('admin-access');

        // Clear this role from any user preferences
        \App\Models\UserPreference::where('role', $role->name)->update(['role' => null]);

        $role->delete();

        return redirect()->route('app.admin.roles-teams.index')
            ->with('status', 'Role deleted.');
    }

    public function storeTeam(Request $request): RedirectResponse
    {
        Gate::authorize('admin-access');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $slug = Str::slug($validated['name'], '_');

        if (Team::where('slug', $slug)->exists()) {
            return redirect()->route('app.admin.roles-teams.index')
                ->withErrors(['name' => 'A team with this name already exists.'])
                ->withInput();
        }

        $maxOrder = Team::max('sort_order') ?? 0;

        Team::create([
            'slug' => $slug,
            'name' => trim($validated['name']),
            'sort_order' => $maxOrder + 1,
        ]);

        return redirect()->route('app.admin.roles-teams.index')
            ->with('status', 'Team created.');
    }

    public function updateTeam(Request $request, Team $team): RedirectResponse
    {
        Gate::authorize('admin-access');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $oldName = $team->name;
        $newName = trim($validated['name']);
        $newSlug = Str::slug($newName, '_');

        if ($newSlug !== $team->slug && Team::where('slug', $newSlug)->exists()) {
            return redirect()->route('app.admin.roles-teams.index')
                ->withErrors(['name' => 'A team with this name already exists.'])
                ->withInput();
        }

        // Update user_preferences that reference the old name
        if ($oldName !== $newName) {
            \App\Models\UserPreference::where('team', $oldName)->update(['team' => $newName]);
        }

        $team->update([
            'slug' => $newSlug,
            'name' => $newName,
        ]);

        return redirect()->route('app.admin.roles-teams.index')
            ->with('status', 'Team updated.');
    }

    public function destroyTeam(Request $request, Team $team): RedirectResponse
    {
        Gate::authorize('admin-access');

        // Clear this team from any user preferences
        \App\Models\UserPreference::where('team', $team->name)->update(['team' => null]);

        $team->delete();

        return redirect()->route('app.admin.roles-teams.index')
            ->with('status', 'Team deleted.');
    }
}
