
import React from 'react';
import DashboardLayout from '@/components/dashboard/DashboardLayout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Separator } from '@/components/ui/separator';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { useToast } from "@/hooks/use-toast";
import RestaurantGeneralForm from '@/components/settings/RestaurantGeneralForm';
import AccountSettings from '@/components/settings/AccountSettings';
import NotificationSettings from '@/components/settings/NotificationSettings';
import IntegrationSettings from '@/components/settings/IntegrationSettings';

const Settings = () => {
  const { toast } = useToast();

  return (
    <DashboardLayout>
      <div className="space-y-6 animate-fade-in">
        <h1 className="text-2xl font-bold">Settings</h1>
        
        <Tabs defaultValue="general" className="w-full">
          <TabsList className="mb-2">
            <TabsTrigger value="general">General</TabsTrigger>
            <TabsTrigger value="account">Account</TabsTrigger>
            <TabsTrigger value="notifications">Notifications</TabsTrigger>
            <TabsTrigger value="integrations">Integrations</TabsTrigger>
          </TabsList>
          
          <TabsContent value="general" className="pt-4">
            <RestaurantGeneralForm />
          </TabsContent>
          
          <TabsContent value="account" className="pt-4">
            <AccountSettings />
          </TabsContent>
          
          <TabsContent value="notifications" className="pt-4">
            <NotificationSettings />
          </TabsContent>
          
          <TabsContent value="integrations" className="pt-4">
            <IntegrationSettings />
          </TabsContent>
        </Tabs>
      </div>
    </DashboardLayout>
  );
};

export default Settings;
