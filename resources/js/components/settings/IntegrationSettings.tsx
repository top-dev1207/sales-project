
import React from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';

const IntegrationSettings = () => {
  const integrations = [
    {
      name: 'Point of Sale',
      connected: true,
      description: 'Connect with popular POS systems.',
      icon: '💳',
    },
    {
      name: 'Inventory Management',
      connected: true,
      description: 'Track inventory levels automatically.',
      icon: '📦',
    },
    {
      name: 'Accounting Software',
      connected: false,
      description: 'Streamline your financial operations.',
      icon: '💰',
    },
    {
      name: 'Online Ordering',
      connected: false,
      description: 'Accept orders from your website.',
      icon: '🛒',
    },
  ];

  return (
    <Card className="shadow-card">
      <CardHeader>
        <CardTitle>Connected Services</CardTitle>
      </CardHeader>
      <CardContent>
        <div className="space-y-6">
          {integrations.map((integration, index) => (
            <React.Fragment key={integration.name}>
              <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div className="flex items-start gap-3">
                  <div className="text-2xl">{integration.icon}</div>
                  <div className="space-y-1">
                    <div className="flex items-center gap-2">
                      <h3 className="font-medium">{integration.name}</h3>
                      {integration.connected ? (
                        <Badge className="bg-green-500">Connected</Badge>
                      ) : (
                        <Badge variant="outline">Not Connected</Badge>
                      )}
                    </div>
                    <p className="text-sm text-muted-foreground">
                      {integration.description}
                    </p>
                  </div>
                </div>
                <Button variant={integration.connected ? "outline" : "default"}>
                  {integration.connected ? "Configure" : "Connect"}
                </Button>
              </div>
              {index < integrations.length - 1 && <Separator />}
            </React.Fragment>
          ))}
          
          <div className="flex justify-end pt-4">
            <Button variant="outline">Find More Integrations</Button>
          </div>
        </div>
      </CardContent>
    </Card>
  );
};

export default IntegrationSettings;
