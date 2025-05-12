import React from 'react';

const Widget: React.FC = () => {
  return (
    <div className="p-6 bg-white rounded shadow-md text-center">
      <h1 className="text-2xl font-bold text-blue-600">Hello from Widget!</h1>
      <p className="mt-2 text-gray-600">This is a React component inside Laravel + Blade.</p>
    </div>
  );
};

export default Widget;