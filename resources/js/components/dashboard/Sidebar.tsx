import React from 'react';
import { NavLink, useLocation } from 'react-router-dom';
import { cn } from '@/lib/utils';
import {
  LayoutDashboard,
  Layers,
  Receipt,
  CreditCard,
  BarChart,
  ShoppingCart,
  Settings,
  List,
  Users,
} from 'lucide-react';
import { useDashboard } from '@/context/DashboardContext';
import { Button } from '@/components/ui/button';

const navItems = [
  { name: 'Indicadores a realizar', path: '/resumenVer/realizar', icon: LayoutDashboard, exact: true },
  { name: 'Indicadores existentes', path: '/resumenVer/existentes', icon: Layers },
  { name: 'Ventas', path: '/resumenVer/ventas', icon: ShoppingCart },
  { name: 'Gastos', path: '/resumenVer/gastos', icon: Receipt },
  { name: 'Pinta', path: '/resumenVer/pinta', icon: BarChart },
  { name: 'Pagar', path: '/resumenVer/pagar', icon: CreditCard },
  { name: 'Users', path: '/resumenVer/user', icon: Users },
];

const Sidebar = () => {
  const { toggleSidebar, sidebarWidth, miniaturizeSidebar } = useDashboard();
  const location = useLocation();
  
  // Function to check if a nav item is active
  interface NavItem {
    name: string;
    path: string;
    icon: React.ComponentType<{ size?: number }>;
    exact?: boolean;
  }

  const isItemActive = (path: string, exact?: boolean): boolean => {
    if (exact) {
      // For exact matches, check if current path equals the nav path
      // Also handle the case where we're at /resumenVer and should redirect to /resumenVer/realizar
      return location.pathname === path || 
             (path === '/resumenVer/realizar' && location.pathname === '/resumenVer');
    }
    return location.pathname.startsWith(path);
  };

  // Function to get the width class based on sidebar state
  const getSidebarWidthClass = () => {
    switch (sidebarWidth) {
      case 'normal': return "w-64";
      case 'reduced': return "w-48";
      case 'collapsed': return "w-16";
      default: return "w-64";
    }
  };

  // Function to determine if text labels should be shown
  const shouldShowLabels = () => {
    return sidebarWidth !== 'collapsed';
  };

  // Function to determine if logos should be compact
  const isCompactMode = () => {
    return sidebarWidth === 'collapsed';
  };

  // Toggle function for the sidebar collapse/expand
  const handleToggleSidebar = () => {
    if (sidebarWidth === 'collapsed') {
      // If already collapsed, reset to normal
      toggleSidebar();
    } else {
      // Otherwise collapse to icon-only view
      miniaturizeSidebar();
    }
  };

  return (
    <aside
      className={cn(
        "h-screen bg-sidebar border-r border-sidebar-border fixed left-0 top-0 z-30 transition-all duration-300 theme-transition",
        getSidebarWidthClass()
      )}
    >
      {/* Header with toggle button */}
      <div className={cn(
        "flex items-center justify-between h-16 px-4 border-b border-sidebar-border theme-transition",
        isCompactMode() && "justify-center"
      )}>
        {!isCompactMode() && (
          <span className="text-lg font-semibold text-sidebar-foreground truncate theme-transition">Wolf Admin</span>
        )}

        {/* Single toggle button at the top */}
        <Button
          variant="ghost"
          size="icon"
          onClick={handleToggleSidebar}
          className="rounded-sm text-sidebar-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground theme-transition"
        >
          <List size={20} />
        </Button>
      </div>

      {/* Main navigation */}
      <nav className="p-2 pt-4">
        <ul className="space-y-1">
          {navItems.map((item) => {
            const active = isItemActive(item.path, item.exact);
            
            return (
              <li key={item.name}>
                <NavLink
                  to={item.path}
                  className={cn(
                    "flex items-center gap-3 px-3 py-2.5 rounded-md transition-all duration-200 theme-transition",
                    active
                      ? "bg-sidebar-primary text-sidebar-primary-foreground"
                      : "text-sidebar-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground",
                    isCompactMode() && "justify-center"
                  )}
                  end={item.exact}
                  title={isCompactMode() ? item.name : undefined}
                >
                  <item.icon size={20} />
                  {shouldShowLabels() && <span className="text-sm theme-transition">{item.name}</span>}
                </NavLink>
              </li>
            );
          })}
        </ul>
      </nav>
    </aside>
  );
};

export default Sidebar;