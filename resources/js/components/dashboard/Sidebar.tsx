
import React from 'react';
import { NavLink } from 'react-router-dom';
import { cn } from '@/lib/utils';
import {
  LayoutDashboard,
  BarChart,
  PieChart,
  Users,
  Settings,
  List,
  Layers
} from 'lucide-react';
import { useDashboard } from '@/context/DashboardContext';
import { Button } from '@/components/ui/button';

const navItems = [
  { name: 'Indicadores a realizar', path: '/resumenVer/', icon: LayoutDashboard },
  { name: 'Indicadores existentes', path: '/resumenVer/existentes', icon: Layers },
  { name: 'Ventas', path: '/resumenVer/ventas', icon: PieChart },
  { name: 'Gastos', path: '/resumenVer/gastos', icon: BarChart },
  { name: 'Pinta ', path: '/resumenVer/pinta', icon: Settings },
  { name: 'Pagar', path: '/resumenVer/pagar', icon: Settings },
  { name: 'User', path: '/resumenVer/user', icon: Users },
];

const Sidebar = () => {
  const { toggleSidebar, sidebarWidth, miniaturizeSidebar } = useDashboard();

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
        "h-screen bg-white border-r border-gray-200 fixed left-0 top-0 z-30 transition-all duration-300",
        getSidebarWidthClass()
      )}
    >
      {/* Header with toggle button */}
      <div className={cn(
        "flex items-center justify-between h-16 px-4 border-b border-gray-200",
        isCompactMode() && "justify-center"
      )}>
        {!isCompactMode() && (
          <span className="text-lg font-semibold text-gray-800 truncate">Wolf Admin</span>
        )}

        {/* Single toggle button at the top */}
        <Button
          variant="ghost"
          size="icon"
          onClick={handleToggleSidebar}
          className="rounded-sm text-gray-600 hover:bg-gray-100"
        >
          <List size={20} />
        </Button>
      </div>

      {/* Main navigation */}
      <nav className="p-2 pt-4">
        <ul className="space-y-1">
          {navItems.map((item) => (
            <li key={item.name}>
              <NavLink
                to={item.path}
                className={({ isActive }) => cn(
                  "flex items-center gap-3 px-3 py-2.5 rounded-md transition-all duration-200 no-underline",
                  isActive
                    ? "bg-dashboard-cyan text-white"
                    : "text-gray-700 hover:bg-gray-100",
                  isCompactMode() && "justify-center"
                )}
                end={item.path === '/resumenVer/user'}
                title={isCompactMode() ? item.name : undefined}
              >
                <item.icon size={20} />
                {shouldShowLabels() && <span className="text-sm">{item.name}</span>}
              </NavLink>
            </li>
          ))}
        </ul>
      </nav>
    </aside>
  );
};

export default Sidebar;
