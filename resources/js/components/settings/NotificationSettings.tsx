
import React from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Switch } from '@/components/ui/switch';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';

const NotificationSettings = () => {
  return (
    <Card className="shadow-card">
      <CardHeader>
        <CardTitle>Notification Preferences</CardTitle>
      </CardHeader>
      <CardContent>
        <div className="space-y-6">
          <div className="space-y-4">
            <h3 className="text-lg font-medium">Email Notifications</h3>
            <div className="space-y-3">
              <div className="flex items-center justify-between">
                <div className="space-y-0.5">
                  <Label htmlFor="daily-summary">Daily Summary</Label>
                  <p className="text-sm text-muted-foreground">
                    Receive a daily summary of your restaurant's performance.
                  </p>
                </div>
                <Switch id="daily-summary" />
              </div>
              
              <div className="flex items-center justify-between">
                <div className="space-y-0.5">
                  <Label htmlFor="inventory-alerts">Inventory Alerts</Label>
                  <p className="text-sm text-muted-foreground">
                    Get notified when inventory items are running low.
                  </p>
                </div>
                <Switch id="inventory-alerts" defaultChecked />
              </div>
              
              <div className="flex items-center justify-between">
                <div className="space-y-0.5">
                  <Label htmlFor="staff-updates">Staff Updates</Label>
                  <p className="text-sm text-muted-foreground">
                    Receive notifications about staff schedule changes.
                  </p>
                </div>
                <Switch id="staff-updates" />
              </div>
            </div>
          </div>
          
          <div className="space-y-4">
            <h3 className="text-lg font-medium">System Notifications</h3>
            <div className="space-y-3">
              <div className="flex items-center justify-between">
                <div className="space-y-0.5">
                  <Label htmlFor="maintenance-alerts">Maintenance Alerts</Label>
                  <p className="text-sm text-muted-foreground">
                    Get notified about scheduled system maintenance.
                  </p>
                </div>
                <Switch id="maintenance-alerts" defaultChecked />
              </div>
              
              <div className="flex items-center justify-between">
                <div className="space-y-0.5">
                  <Label htmlFor="feature-updates">Feature Updates</Label>
                  <p className="text-sm text-muted-foreground">
                    Be the first to know about new features and updates.
                  </p>
                </div>
                <Switch id="feature-updates" defaultChecked />
              </div>
            </div>
          </div>
          
          <div className="flex justify-end">
            <Button>Save Preferences</Button>
          </div>
        </div>
      </CardContent>
    </Card>
  );
};

export default NotificationSettings;
