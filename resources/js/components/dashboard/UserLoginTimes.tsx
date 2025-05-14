
import React from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Users } from 'lucide-react';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';

// Mock user data
const users = [
  { id: 1, name: 'John Doe', position: 'Manager', loginTime: '08:15 AM', totalHours: '9h 45m', avatar: '' },
  { id: 2, name: 'Jane Smith', position: 'Chef', loginTime: '07:30 AM', totalHours: '10h 15m', avatar: '' },
  { id: 3, name: 'Robert Johnson', position: 'Waiter', loginTime: '10:00 AM', totalHours: '8h 20m', avatar: '' },
  { id: 4, name: 'Sarah Wilson', position: 'Bartender', loginTime: '09:45 AM', totalHours: '8h 30m', avatar: '' },
  { id: 5, name: 'Michael Brown', position: 'Host', loginTime: '09:00 AM', totalHours: '8h 45m', avatar: '' },
];

const UserLoginTimes = () => {
  return (
    <Card className="col-span-2">
      <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
        <CardTitle className="text-base font-medium">Staff Login Times</CardTitle>
        <Users className="h-4 w-4 text-muted-foreground" />
      </CardHeader>
      <CardContent className="pt-4 px-0">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead className="w-[50px]"></TableHead>
              <TableHead>Staff Member</TableHead>
              <TableHead>Position</TableHead>
              <TableHead>Login Time</TableHead>
              <TableHead className="text-right">Total Hours</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {users.map((user) => (
              <TableRow key={user.id}>
                <TableCell className="p-2">
                  <Avatar className="h-8 w-8">
                    <AvatarImage src={user.avatar} alt={user.name} />
                    <AvatarFallback>
                      {user.name.split(' ').map(n => n[0]).join('')}
                    </AvatarFallback>
                  </Avatar>
                </TableCell>
                <TableCell className="font-medium">{user.name}</TableCell>
                <TableCell>{user.position}</TableCell>
                <TableCell>{user.loginTime}</TableCell>
                <TableCell className="text-right">{user.totalHours}</TableCell>
              </TableRow>
            ))}
          </TableBody>
        </Table>
      </CardContent>
    </Card>
  );
};

export default UserLoginTimes;
