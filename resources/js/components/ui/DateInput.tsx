import React, { useState, useRef, useEffect } from 'react';
import { Calendar, ChevronLeft, ChevronRight, ChevronDown } from 'lucide-react';

interface CustomDateInputProps {
  value?: string;
  onChange?: ((date: string) => void) | ((e: React.ChangeEvent<HTMLInputElement>) => void);
  placeholder?: string;
  disabled?: boolean;
  className?: string;
  id?: string;
  name?: string; // This is the name attribute for form submission
}

const DateInput: React.FC<CustomDateInputProps> = ({
  value = '',
  onChange,
  placeholder = 'Select date',
  disabled = false,
  className = '',
  id,
  name // Now properly used for form compatibility
}) => {
  const [isOpen, setIsOpen] = useState(false);
  const [selectedDate, setSelectedDate] = useState<Date | null>(
    value ? new Date(value) : null
  );
  const [currentMonth, setCurrentMonth] = useState(
    selectedDate ? new Date(selectedDate.getFullYear(), selectedDate.getMonth()) : new Date()
  );
  const [showYearSelector, setShowYearSelector] = useState(false);
  
  const containerRef = useRef<HTMLDivElement>(null);
  const inputRef = useRef<HTMLInputElement>(null);

  // Close calendar when clicking outside
  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (containerRef.current && !containerRef.current.contains(event.target as Node)) {
        setIsOpen(false);
        setShowYearSelector(false);
      }
    };

    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  // Update selected date when value prop changes
  useEffect(() => {
    if (value) {
      const newDate = new Date(value);
      setSelectedDate(newDate);
      setCurrentMonth(new Date(newDate.getFullYear(), newDate.getMonth()));
    } else {
      setSelectedDate(null);
    }
  }, [value]);

  const months = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
  ];

  const daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

  // Generate year range (current year ± 50 years)
  const currentYear = new Date().getFullYear();
  const yearRange = Array.from({ length: 101 }, (_, i) => currentYear - 50 + i);

  const getDaysInMonth = (date: Date) => {
    const year = date.getFullYear();
    const month = date.getMonth();
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const daysInMonth = lastDay.getDate();
    const startingDayOfWeek = firstDay.getDay();

    const days = [];
    
    // Add empty cells for days before the first day of the month
    for (let i = 0; i < startingDayOfWeek; i++) {
      days.push(null);
    }
    
    // Add days of the month
    for (let day = 1; day <= daysInMonth; day++) {
      days.push(new Date(year, month, day));
    }
    
    return days;
  };

  const formatDate = (date: Date) => {
    return date.toISOString().split('T')[0];
  };

  const formatDisplayDate = (date: Date) => {
    return date.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    });
  };

  const handleDateSelect = (date: Date) => {
    setSelectedDate(date);
    const formattedDate = formatDate(date);
    
    // Create synthetic event for compatibility with e.target.value
    if (onChange) {
      const syntheticEvent = {
        target: { 
          value: formattedDate,
          name: name || '' // Include name in the event
        },
        currentTarget: { 
          value: formattedDate,
          name: name || ''
        }
      } as React.ChangeEvent<HTMLInputElement>;
      
      // Try to call with synthetic event first, fallback to direct value
      try {
        (onChange as any)(syntheticEvent);
      } catch {
        (onChange as any)(formattedDate);
      }
    }
    
    setIsOpen(false);
    setShowYearSelector(false);
  };

  const handlePreviousMonth = () => {
    setCurrentMonth(new Date(currentMonth.getFullYear(), currentMonth.getMonth() - 1));
  };

  const handleNextMonth = () => {
    setCurrentMonth(new Date(currentMonth.getFullYear(), currentMonth.getMonth() + 1));
  };

  const handleYearSelect = (year: number) => {
    setCurrentMonth(new Date(year, currentMonth.getMonth()));
    setShowYearSelector(false);
  };

  const handleInputClick = () => {
    if (!disabled) {
      setIsOpen(!isOpen);
      setShowYearSelector(false);
    }
  };

  const handleClear = () => {
    setSelectedDate(null);
    
    // Create synthetic event for clear action
    if (onChange) {
      const syntheticEvent = {
        target: { 
          value: '',
          name: name || ''
        },
        currentTarget: { 
          value: '',
          name: name || ''
        }
      } as React.ChangeEvent<HTMLInputElement>;
      
      try {
        (onChange as any)(syntheticEvent);
      } catch {
        (onChange as any)('');
      }
    }
    
    setIsOpen(false);
    setShowYearSelector(false);
  };

  const isToday = (date: Date) => {
    const today = new Date();
    return date.getDate() === today.getDate() &&
           date.getMonth() === today.getMonth() &&
           date.getFullYear() === today.getFullYear();
  };

  const isSelected = (date: Date) => {
    return selectedDate &&
           date.getDate() === selectedDate.getDate() &&
           date.getMonth() === selectedDate.getMonth() &&
           date.getFullYear() === selectedDate.getFullYear();
  };

  return (
    <div className={`relative ${className}`} ref={containerRef}>
      {/* Hidden native input for form compatibility - NOW WITH PROPER NAME ATTRIBUTE */}
      <input
        ref={inputRef}
        type="hidden"
        value={value}
        name={name} // This ensures form submission includes the date value with the specified name
        id={id}
        readOnly
      />
      
      {/* Custom input display */}
      <div
        className={`
          flex items-center justify-between w-full px-3 py-2 
          text-sm border rounded-md cursor-pointer
          transition-all duration-200
          ${disabled 
            ? 'bg-gray-50 dark:bg-gray-800 text-gray-400 dark:text-gray-500 cursor-not-allowed border-gray-200 dark:border-gray-700' 
            : 'bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500'
          }
          ${isOpen ? 'ring-2 ring-blue-500 dark:ring-blue-400 border-blue-500 dark:border-blue-400' : ''}
          focus-within:ring-2 focus-within:ring-blue-500 dark:focus-within:ring-blue-400
          focus-within:border-blue-500 dark:focus-within:border-blue-400
        `}
        onClick={handleInputClick}
      >
        <span className={selectedDate ? 'text-gray-900 dark:text-gray-100' : 'text-gray-500 dark:text-gray-400'}>
          {selectedDate ? formatDisplayDate(selectedDate) : placeholder}
        </span>
        <Calendar className="w-4 h-4 text-gray-400 dark:text-gray-500" />
      </div>

      {/* Calendar dropdown */}
      {isOpen && (
        <div className="absolute z-50 mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow-lg min-w-[280px]">
          <div className="p-3">
            {/* Month/Year navigation */}
            <div className="flex items-center justify-between mb-4">
              <button
                type="button"
                onClick={handlePreviousMonth}
                className="p-1 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700 rounded"
              >
                <ChevronLeft className="w-4 h-4" />
              </button>
              
              <div className="flex items-center space-x-2">
                {/* Month selector */}
                <button
                  type="button"
                  onClick={() => setShowYearSelector(false)}
                  className={`px-2 py-1 text-sm font-medium rounded hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors ${
                    !showYearSelector ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900' : 'text-gray-900 dark:text-gray-100'
                  }`}
                >
                  {months[currentMonth.getMonth()]}
                </button>
                
                {/* Year selector button */}
                <button
                  type="button"
                  onClick={() => setShowYearSelector(!showYearSelector)}
                  className={`flex items-center px-2 py-1 text-sm font-medium rounded hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors ${
                    showYearSelector ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900' : 'text-gray-900 dark:text-gray-100'
                  }`}
                >
                  {currentMonth.getFullYear()}
                  <ChevronDown className="w-3 h-3 ml-1" />
                </button>
              </div>
              
              <button
                type="button"
                onClick={handleNextMonth}
                className="p-1 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700 rounded"
              >
                <ChevronRight className="w-4 h-4" />
              </button>
            </div>

            {/* Year selector grid */}
            {showYearSelector && (
              <div className="mb-4 max-h-48 overflow-y-auto border border-gray-200 dark:border-gray-600 rounded-md">
                <div className="grid grid-cols-4 gap-1 p-2">
                  {yearRange.map(year => (
                    <button
                      key={year}
                      type="button"
                      onClick={() => handleYearSelect(year)}
                      className={`
                        px-2 py-1 text-xs rounded transition-all duration-150
                        ${year === currentMonth.getFullYear()
                          ? 'bg-blue-500 dark:bg-blue-600 text-white'
                          : year === currentYear
                          ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400'
                          : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
                        }
                      `}
                    >
                      {year}
                    </button>
                  ))}
                </div>
              </div>
            )}

            {/* Calendar view (only show when not selecting year) */}
            {!showYearSelector && (
              <>
                {/* Days of week header */}
                <div className="grid grid-cols-7 mb-2">
                  {daysOfWeek.map(day => (
                    <div key={day} className="p-2 text-xs font-medium text-center text-gray-500 dark:text-gray-400">
                      {day}
                    </div>
                  ))}
                </div>

                {/* Calendar grid */}
                <div className="grid grid-cols-7 gap-1 mb-3">
                  {getDaysInMonth(currentMonth).map((date, index) => (
                    <div key={index} className="p-1">
                      {date ? (
                        <button
                          type="button"
                          onClick={() => handleDateSelect(date)}
                          className={`
                            w-8 h-8 text-xs rounded-full flex items-center justify-center
                            transition-all duration-150
                            ${isSelected(date)
                              ? 'bg-blue-500 dark:bg-blue-600 text-white'
                              : isToday(date)
                              ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400'
                              : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
                            }
                          `}
                        >
                          {date.getDate()}
                        </button>
                      ) : (
                        <div className="w-8 h-8" />
                      )}
                    </div>
                  ))}
                </div>
              </>
            )}

            {/* Action buttons */}
            <div className="flex justify-between pt-3 border-t border-gray-200 dark:border-gray-600">
              <button
                type="button"
                onClick={handleClear}
                className="px-3 py-1 text-xs text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700 rounded"
              >
                Clear
              </button>
              <button
                type="button"
                onClick={() => {
                  const today = new Date();
                  const formattedDate = formatDate(today);
                  setSelectedDate(today);
                  
                  // Create synthetic event for today button
                  if (onChange) {
                    const syntheticEvent = {
                      target: { 
                        value: formattedDate,
                        name: name || ''
                      },
                      currentTarget: { 
                        value: formattedDate,
                        name: name || ''
                      }
                    } as React.ChangeEvent<HTMLInputElement>;
                    
                    try {
                      (onChange as any)(syntheticEvent);
                    } catch {
                      (onChange as any)(formattedDate);
                    }
                  }
                  
                  setIsOpen(false);
                  setShowYearSelector(false);
                }}
                className="px-3 py-1 text-xs text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900 rounded"
              >
                Today
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default DateInput;