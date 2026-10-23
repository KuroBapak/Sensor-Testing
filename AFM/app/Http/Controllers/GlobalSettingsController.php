<?php

namespace App\Http\Controllers;

use App\Enums\ResetPeriod;
use App\Http\Requests\SettingsUpdateRequest;
use App\Models\QrCard;
use App\Models\Tank;
use App\Services\ActivityLogger;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class GlobalSettingsController extends Controller
{
    public function __construct(
        private readonly SettingsService $settings,
        private readonly ActivityLogger $logger,
    ) {}

    public function edit(): Response
    {
        return Inertia::render('global-settings/index', [
            'settings' => $this->settings->all(),
            'tanks' => Tank::query()->orderBy('id')->get(['id', 'name', 'capacity_liters'])->all(),
            'timezones' => SettingsUpdateRequest::TIMEZONES,
            // Shown in the confirmation dialog before a reset rule changes.
            'resetCardCount' => $this->resetCardCount(),
        ]);
    }

    public function update(SettingsUpdateRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $tanks = $data['tanks'];
        $logo = $request->file('logo');
        unset($data['tanks'], $data['confirm_reset_change'], $data['logo'], $data['remove_logo']);
        $data['site_name'] = $data['site_name'] ?? '';

        $before = $this->settings->all();

        if ($this->resetRuleChanges($before, $data) && ! $request->boolean('confirm_reset_change')) {
            throw ValidationException::withMessages([
                'confirm_reset_change' => __('fuel.settings.confirm_required', ['count' => $this->resetCardCount()]),
            ]);
        }

        $oldLogo = (string) $before['logo_path'];
        if ($logo !== null) {
            $data['logo_path'] = (string) $logo->store('logos', 'local');
        } elseif ($request->boolean('remove_logo')) {
            $data['logo_path'] = '';
        }

        DB::transaction(function () use ($data, $tanks) {
            $this->settings->update($data);

            foreach ($tanks as $tank) {
                Tank::query()->whereKey($tank['id'])->update(['capacity_liters' => $tank['capacity_liters']]);
            }
        });

        // The old file goes only after the new settings are saved.
        if (isset($data['logo_path']) && $oldLogo !== '' && $data['logo_path'] !== $oldLogo) {
            Storage::disk('local')->delete($oldLogo);
        }

        $this->logger->log('settings.updated', null, null, [
            'changed' => collect($data)
                ->filter(fn ($value, $key) => $before[$key] != $value)
                ->map(fn ($value, $key) => ['from' => $before[$key], 'to' => $value])
                ->all(),
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('fuel.settings.saved')]);

        return back();
    }

    /** Cards that follow the reset rules, i.e. reset on and not expired. */
    private function resetCardCount(): int
    {
        return QrCard::query()->where('reset_enabled', true)->notExpired()->count();
    }

    /**
     * @param  array<string, mixed>  $before
     * @param  array<string, mixed>  $after
     */
    private function resetRuleChanges(array $before, array $after): bool
    {
        if ($before['reset_period'] !== $after['reset_period'] || $before['reset_time'] !== $after['reset_time']) {
            return true;
        }

        return match (ResetPeriod::from($after['reset_period'])) {
            ResetPeriod::Week => (int) $before['week_start_day'] !== (int) $after['week_start_day'],
            ResetPeriod::Month => (int) $before['month_reset_day'] !== (int) $after['month_reset_day'],
            default => false,
        };
    }
}
