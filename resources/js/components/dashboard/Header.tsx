
import React from 'react';
import { useDashboard } from '@/context/DashboardContext';
import { cn } from '@/lib/utils';
import {
  Select,
  SelectTrigger,
  SelectValue,
  SelectContent,
  SelectItem
} from '@/components/ui/select';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';

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
        "fixed top-0 right-0 h-16 border-b border-gray-200 bg-white z-20 shadow-header transition-all duration-300",
        getHeaderLeftClass()
      )}
    >
      <div className="flex items-center justify-between h-full px-6">
        <div>
          <h1 className="text-xl font-semibold text-gray-800">Dashboard</h1>
        </div>

        <div className="flex items-center gap-4">
          <Select
            value={dateRange}
            onValueChange={(value) => setDateRange(value as 'daily' | 'weekly' | 'monthly')}
          >
            <SelectTrigger className="w-40 border-gray-200">
              <SelectValue placeholder="Select date range" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="daily">Daily</SelectItem>
              <SelectItem value="weekly">Weekly</SelectItem>
              <SelectItem value="monthly">Monthly</SelectItem>
            </SelectContent>
          </Select>

          <Avatar>
            <AvatarImage src="" />
            <AvatarFallback className="bg-primary text-primary-foreground">JD</AvatarFallback>
          </Avatar>
        </div>
      </div>
    </header>
  );
};

export default Header;
