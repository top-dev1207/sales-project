import { Moon, Sun } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { useTheme } from '@/hooks/ThemeContext';
import { Switch } from '@/components/ui/switch';

export function ThemeToggle() {
  const { theme, setTheme } = useTheme();

  const toggleTheme = () => {
    setTheme(theme === 'light' ? 'dark' : 'light');
  };

  // Toggle with Switch component
  return (
    <div className="flex items-center gap-2">
      <Sun className="h-[1.2rem] w-[1.2rem] text-foreground theme-transition" />
      <Switch
        checked={theme === 'dark'}
        onCheckedChange={toggleTheme}
        className="theme-transition"
      />
      <Moon className="h-[1.2rem] w-[1.2rem] text-foreground theme-transition" />
    </div>
  );
}