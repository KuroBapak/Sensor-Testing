import { Moon, Sun } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { useAppearance } from '@/hooks/use-appearance';
import { id } from '@/lang/id';

/** Switches between the light and dark theme; the label names the theme in use. */
export function ThemeToggle() {
    const { resolvedAppearance, updateAppearance } = useAppearance();
    const dark = resolvedAppearance === 'dark';
    const Icon = dark ? Moon : Sun;

    return (
        <Button
            variant="outline"
            size="sm"
            aria-label={dark ? id.header.switchToLight : id.header.switchToDark}
            title={dark ? id.header.switchToLight : id.header.switchToDark}
            onClick={() => updateAppearance(dark ? 'light' : 'dark')}
            data-test="theme-toggle"
        >
            <Icon className={dark ? 'text-indigo-400' : 'text-amber-500'} />
            <span className="hidden sm:inline">
                {dark ? id.account.dark : id.account.light}
            </span>
        </Button>
    );
}
