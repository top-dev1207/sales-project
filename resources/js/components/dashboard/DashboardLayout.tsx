import React from 'react';
import Sidebar from '@/components/dashboard/Sidebar';
import Header from '@/components/dashboard/Header';
import { useDashboard } from '@/context/DashboardContext';
import { cn } from '@/lib/utils';

interface DashboardLayoutProps {
  children: React.ReactNode;
}

const DashboardLayout = ({ children }: DashboardLayoutProps) => {
  const { sidebarWidth } = useDashboard();

  // Function to determine main content padding based on sidebar state
  const getMainPaddingClass = () => {
    switch (sidebarWidth) {
      case 'normal': return "pl-64";
      case 'reduced': return "pl-48";
      case 'collapsed': return "pl-16";
      default: return "pl-64";
    }
  };

  return (
    <div className="min-h-screen bg-background theme-transition">
      <Sidebar />
      <Header />
      <main className={cn(
        "pt-16 min-h-screen transition-all duration-300 theme-transition",
        getMainPaddingClass()
      )}>
        <div className="p-6 max-w-7xl mx-auto">
          {children}
        </div>
      </main>
    </div>
  );
};

export default DashboardLayout;