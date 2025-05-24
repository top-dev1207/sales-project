
import React, { useState } from 'react';
import { useDashboard } from '@/context/DashboardContext';
import { useTheme } from '@/hooks/ThemeContext';
import { cn } from '@/lib/utils';
import {
  Select,
  SelectTrigger,
  SelectValue,
  SelectContent,
  SelectItem
} from '@/components/ui/select';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Moon, Sun } from 'lucide-react';
import { ThemeToggle } from '../ui/theme-toggle';

const Header = () => {
  const { dateRange, setDateRange, sidebarWidth } = useDashboard();

  // Function to determine header positioning based on sidebar state
  const getHeaderLeftClass = () => {
    switch (sidebarWidth) {
      case 'normal': return "left-64";
      case 'reduced': return "left-48";
      case 'collapsed': return "left-16";
      default: return "left-64";
    }
  };

  return (
    <header
      className={cn(
        "fixed top-0 right-0 h-16 border-b border-b-[hsl(var(--sidebar-border))] bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60 z-20 shadow-sm theme-transition",
        getHeaderLeftClass()
      )}
    >
      <div className="flex items-center justify-between h-full px-6">
        <div>
          <h1 className="text-xl font-semibold text-foreground theme-transition">Dashboard</h1>
        </div>

        <div className="flex items-center gap-4">
          <ThemeToggle />
          <Avatar>
            <AvatarImage src="" />
            <AvatarFallback className="bg-primary text-primary-foreground theme-transition">JD</AvatarFallback>
          </Avatar>
        </div>
      </div>
    </header>
  );
};

export default Header;