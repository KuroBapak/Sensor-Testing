<?php

namespace App\Services;

use App\Enums\ResetPeriod;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

/**
 * Period boundaries (section 3, rule 5). Period starts are calculated from the
 * settings and never stored, so changing a setting takes effect at once.
 */
class PeriodService
{
    public function __construct(private readonly SettingsService $settings) {}

    /** Start of the period that contains $at, in the site time zone. */
    public function startOf(ResetPeriod $period, ?CarbonInterface $at = null): CarbonImmutable
    {
        $at = $this->inSiteZone($at);
        [$hour, $minute] = $this->settings->resetTime();
        $anchor = $at->setTime($hour, $minute);

        return match ($period) {
            ResetPeriod::Day => $anchor->greaterThan($at) ? $anchor->subDay() : $anchor,
            ResetPeriod::Week => $this->weekStart($anchor, $at),
            ResetPeriod::Month => $this->monthStart($anchor, $at),
        };
    }

    /** Start of the next period, i.e. the moment cards are refilled. */
    public function nextStart(ResetPeriod $period, ?CarbonInterface $at = null): CarbonImmutable
    {
        return $this->shift($period, $this->startOf($period, $at), 1);
    }

    /** Start of the period that is $count periods after (or before, if negative) $start. */
    public function shift(ResetPeriod $period, CarbonImmutable $start, int $count): CarbonImmutable
    {
        [$hour, $minute] = $this->settings->resetTime();

        $shifted = match ($period) {
            ResetPeriod::Day => $start->addDays($count),
            ResetPeriod::Week => $start->addWeeks($count),
            ResetPeriod::Month => $start->addMonthsNoOverflow($count),
        };

        return $shifted->setTime($hour, $minute);
    }

    /** Start of the current period for the reset period chosen in settings. */
    public function currentStart(?CarbonInterface $at = null): CarbonImmutable
    {
        return $this->startOf($this->settings->resetPeriod(), $at);
    }

    /** Format a moment the way MySQL stores it (application time zone). */
    public function toDb(CarbonInterface $moment): string
    {
        return CarbonImmutable::instance($moment)
            ->setTimezone(config('app.timezone'))
            ->format('Y-m-d H:i:s');
    }

    private function inSiteZone(?CarbonInterface $at): CarbonImmutable
    {
        return CarbonImmutable::instance($at ?? now())->setTimezone($this->settings->timezone());
    }

    private function weekStart(CarbonImmutable $anchor, CarbonImmutable $at): CarbonImmutable
    {
        $weekStartDay = (int) $this->settings->get('week_start_day');
        $daysBack = ($anchor->dayOfWeekIso - $weekStartDay + 7) % 7;
        $start = $anchor->subDays($daysBack);

        return $start->greaterThan($at) ? $start->subWeek() : $start;
    }

    private function monthStart(CarbonImmutable $anchor, CarbonImmutable $at): CarbonImmutable
    {
        $day = min(max((int) $this->settings->get('month_reset_day'), 1), 28);
        $start = $anchor->setDate($anchor->year, $anchor->month, $day);

        return $start->greaterThan($at) ? $start->subMonthNoOverflow() : $start;
    }
}
