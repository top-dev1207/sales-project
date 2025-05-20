import React, { useState } from 'react';
import DashboardLayout from '@/components/dashboard/DashboardLayout';
import PanelAnalisisVentas from './PanelAnalisisComparativo';
import PanelVentas from './PanelVentas';

const Vantas = () => {

    return (
        <DashboardLayout>
            <PanelVentas />
            <PanelAnalisisVentas />
        </DashboardLayout>
    );
};

export default Vantas;
