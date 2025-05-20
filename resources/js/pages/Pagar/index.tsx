
import React from 'react';
import DashboardLayout from '@/components/dashboard/DashboardLayout';
import FormaPagoPanel from './FormaPagoPanel';

const Pagar = () => {

  return (
    <DashboardLayout>
      <div className="space-y-6 animate-fade-in">
    <FormaPagoPanel />
      </div>
    </DashboardLayout>
  );
};

export default Pagar;
