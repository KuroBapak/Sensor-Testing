import type { GlobalSettings } from '@/types/fuel';

type ResetRules = Pick<
    GlobalSettings,
    'reset_period' | 'reset_time' | 'week_start_day' | 'month_reset_day'
>;

/**
 * True when saving would change how quotas reset: the period, the reset time,
 * or the day setting that matters for the chosen period. The server asks for the
 * same confirmation, so the dialog and the rule always agree.
 */
export function resetRulesChanged(
    before: ResetRules,
    after: ResetRules,
): boolean {
    if (
        before.reset_period !== after.reset_period ||
        before.reset_time !== after.reset_time
    ) {
        return true;
    }

    if (after.reset_period === 'week') {
        return Number(before.week_start_day) !== Number(after.week_start_day);
    }

    if (after.reset_period === 'month') {
        return Number(before.month_reset_day) !== Number(after.month_reset_day);
    }

    return false;
}
