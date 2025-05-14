
import React, { createContext, useContext, useState, ReactNode } from 'react';

type DateRangeType = 'daily' | 'weekly' | 'monthly';
type SidebarWidthType = 'normal' | 'reduced' | 'collapsed';

interface DashboardContextType {
  dateRange: DateRangeType;
  setDateRange: (range: DateRangeType) => void;
  sidebarWidth: SidebarWidthType;
  toggleSidebar: () => void;
  miniaturizeSidebar: () => void;
}

const DashboardContext = createContext<DashboardContextType | undefined>(undefined);

export const useDashboard = () => {
  const context = useContext(DashboardContext);
  if (!context) {
    throw new Error('useDashboard must be used within a DashboardProvider');
  }
  return context;
};

interface DashboardProviderProps {
  children: ReactNode;
}

export const DashboardProvider = ({ children }: DashboardProviderProps) => {
  const [dateRange, setDateRange] = useState<DateRangeType>('daily');
  const [sidebarWidth, setSidebarWidth] = useState<SidebarWidthType>('normal');

  // Simplified toggle function
  const toggleSidebar = () => {
    setSidebarWidth(current => {
      if (current === 'collapsed') return 'normal';
      if (current === 'normal') return 'reduced';
      return 'normal';
    });
  };
  
  // Function to specifically set sidebar to collapsed mode (icons only)
  const miniaturizeSidebar = () => {
    setSidebarWidth('collapsed');
  };

  return (
    <DashboardContext.Provider value={{
      dateRange,
      setDateRange,
      sidebarWidth,
      toggleSidebar,
      miniaturizeSidebar,
    }}>
      {children}
    </DashboardContext.Provider>
  );
};
