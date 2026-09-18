import { Moon, Sun } from 'lucide-react';
import { useAppearance } from '@/hooks/use-appearance';

export default function ThemeToggle() {
    const { resolvedAppearance, updateAppearance } = useAppearance();

    const toggleTheme = () => {
        updateAppearance(resolvedAppearance === 'dark' ? 'light' : 'dark');
    };

    return (
        <button
            onClick={toggleTheme}
            className="flex items-center gap-2 px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors"
            aria-label="Toggle theme"
        >
            {resolvedAppearance === 'dark' ? (
                <>
                    <Moon className="h-4 w-4 text-slate-400" />
                    <span className="text-sm font-medium">Dark</span>
                </>
            ) : (
                <>
                    <Sun className="h-4 w-4 text-amber-500" />
                    <span className="text-sm font-medium">Light</span>
                </>
            )}
        </button>
    );
}
