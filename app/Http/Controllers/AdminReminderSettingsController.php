<?php

namespace App\Http\Controllers;

use App\Models\AssignmentAuditEvent;
use App\Services\AssignmentSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AdminReminderSettingsController extends Controller
{
    public function edit(AssignmentSettingsService $settings): View
    {
        Gate::authorize('admin-access');

        return view('app.admin-reminder-settings', [
            'settings' => $settings->all(),
            'defaults' => $settings->defaults(),
        ]);
    }

    public function update(Request $request, AssignmentSettingsService $settings): RedirectResponse
    {
        Gate::authorize('admin-access');

        $defaults = $settings->defaults();
        $rules = ['settings' => ['required', 'array']];
        foreach (array_keys($defaults) as $key) {
            $rules["settings.{$key}"] = ['required', 'integer', 'min:0', 'max:365'];
        }

        $validated = $request->validate($rules);
        $before = $settings->all();
        $settings->update($validated['settings']);
        $this->recordAuditEvent('reminder_settings_updated', [
            'changed_keys' => collect($validated['settings'])
                ->filter(fn ($value, $key) => (int) $value !== (int) ($before[$key] ?? 0))
                ->keys()
                ->values()
                ->all(),
            'values' => collect($validated['settings'])->map(fn ($value) => (int) $value)->all(),
        ]);

        return redirect()
            ->route('app.admin.reminder-settings.edit')
            ->with('status', 'Reminder settings saved.');
    }

    public function reset(AssignmentSettingsService $settings): RedirectResponse
    {
        Gate::authorize('admin-access');

        $before = $settings->all();
        $settings->resetToDefaults();
        $this->recordAuditEvent('reminder_settings_reset', [
            'previous_values' => $before,
        ]);

        return redirect()
            ->route('app.admin.reminder-settings.edit')
            ->with('status', 'Reminder settings reset to defaults.');
    }

    private function recordAuditEvent(string $action, array $meta = []): void
    {
        if (! Schema::hasTable('assignment_audit_events')) {
            return;
        }

        AssignmentAuditEvent::query()->create([
            'actor_user_id' => auth()->id(),
            'target_user_id' => null,
            'learning_module_id' => null,
            'entity_type' => 'assignment_settings',
            'entity_id' => null,
            'action' => $action,
            'meta' => $meta,
        ]);
    }
}
