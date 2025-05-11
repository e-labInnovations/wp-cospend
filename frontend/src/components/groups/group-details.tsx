"use client";

import { Card, CardContent } from "@/components/ui/card";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { motion } from "framer-motion";
import { Link } from "react-router-dom";
import { CustomAvatar } from "@/components/ui/custom-avatar";
import { Button } from "@/components/ui/button";
import { Plus, UserPlus } from "lucide-react";
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
  DialogFooter,
} from "@/components/ui/dialog";
import { Input } from "@/components/ui/input";
import { useState } from "react";
import { Label } from "@/components/ui/label";
import { Checkbox } from "@/components/ui/checkbox";

// Mock data - would come from API in real app
const groupData = {
  id: "1",
  name: "Roommates",
  description:
    "Expenses shared between roommates for rent, utilities, and groceries.",
  balance: 125.5,
  icon: "🏠",
  members: [
    {
      id: 1,
      name: "John Doe",
      email: "john@example.com",
      balance: 50.25,
      icon: "👨",
    },
    {
      id: 2,
      name: "Jane Smith",
      email: "jane@example.com",
      balance: -25.5,
      icon: "👩",
    },
    {
      id: 3,
      name: "Mike Johnson",
      email: "mike@example.com",
      balance: 100.75,
      icon: "👨",
    },
  ],
  transactions: [
    {
      id: 1,
      title: "Grocery Shopping",
      amount: -85.75,
      date: "2025-01-10",
      account: { name: "Credit Card", icon: "💳" },
      category: { name: "Food", icon: "🍔" },
    },
    {
      id: 2,
      title: "Electricity Bill",
      amount: -120.0,
      date: "2025-01-05",
      account: { name: "Bank", icon: "🏦" },
      category: { name: "Utilities", icon: "💡" },
    },
  ],
};

// Mock data for all available members
const allMembers = [
  { id: 1, name: "John Doe", email: "john@example.com", icon: "👨" },
  { id: 2, name: "Jane Smith", email: "jane@example.com", icon: "👩" },
  { id: 3, name: "Mike Johnson", email: "mike@example.com", icon: "👨" },
  { id: 4, name: "Sarah Williams", email: "sarah@example.com", icon: "👩" },
  { id: 5, name: "David Brown", email: "david@example.com", icon: "👨" },
  { id: 6, name: "Emily Davis", email: "emily@example.com", icon: "👩" },
];

interface GroupDetailsProps {
  id: string;
}

export function GroupDetails({ id }: GroupDetailsProps) {
  // In a real app, we would fetch the group data based on the ID
  const group = groupData;
  const [searchQuery, setSearchQuery] = useState("");
  const [selectedMembers, setSelectedMembers] = useState<number[]>([]);

  // Filter members that are not already in the group
  const availableMembers = allMembers.filter(
    (member) => !group.members.some((m) => m.id === member.id)
  );

  const filteredMembers = availableMembers.filter(
    (member) =>
      member.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
      member.email.toLowerCase().includes(searchQuery.toLowerCase())
  );

  const toggleMember = (memberId: number) => {
    setSelectedMembers((prev) =>
      prev.includes(memberId)
        ? prev.filter((id) => id !== memberId)
        : [...prev, memberId]
    );
  };

  const handleAddMembers = () => {
    // In a real app, we would send this data to the API
    console.log("Adding members:", selectedMembers);
    setSelectedMembers([]);
  };

  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.3 }}
    >
      <Card className="mb-6">
        <CardContent className="p-4">
          <div className="flex items-center">
            <CustomAvatar icon={group.icon} className="h-12 w-12 mr-4" />
            <div className="flex-1">
              <h2 className="text-xl font-bold">{group.name}</h2>
              {group.description && (
                <p className="text-sm text-muted-foreground mt-1">
                  {group.description}
                </p>
              )}
            </div>
            <div
              className={`text-right ${
                group.balance < 0 ? "text-expense" : "text-income"
              }`}
            >
              <div className="text-sm font-medium">Balance</div>
              <div className="font-bold">
                {group.balance < 0 ? "-" : "+"}$
                {Math.abs(group.balance).toFixed(2)}
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <Tabs defaultValue="members" className="w-full">
        <TabsList className="grid w-full grid-cols-2 mb-4">
          <TabsTrigger value="members">Members</TabsTrigger>
          <TabsTrigger value="transactions">Transactions</TabsTrigger>
        </TabsList>

        <TabsContent value="members" className="space-y-3">
          <Dialog>
            <DialogTrigger asChild>
              <Button className="w-full">
                <UserPlus className="mr-2 h-4 w-4" />
                Add Members
              </Button>
            </DialogTrigger>
            <DialogContent className="sm:max-w-md">
              <DialogHeader>
                <DialogTitle>Add Members</DialogTitle>
              </DialogHeader>
              <div className="space-y-4 py-4">
                <div className="relative">
                  <Input
                    placeholder="Search by name or email..."
                    value={searchQuery}
                    onChange={(e) => setSearchQuery(e.target.value)}
                    className="w-full"
                  />
                </div>

                <div className="max-h-[300px] overflow-y-auto space-y-2">
                  {filteredMembers.length === 0 ? (
                    <div className="text-center py-4 text-muted-foreground">
                      No members found
                    </div>
                  ) : (
                    filteredMembers.map((member) => (
                      <div
                        key={member.id}
                        className="flex items-center space-x-2 p-2 border rounded-md"
                      >
                        <Checkbox
                          id={`member-${member.id}`}
                          checked={selectedMembers.includes(member.id)}
                          onCheckedChange={() => toggleMember(member.id)}
                        />
                        <Label
                          htmlFor={`member-${member.id}`}
                          className="flex items-center flex-1 cursor-pointer"
                        >
                          <CustomAvatar
                            icon={member.icon}
                            className="h-8 w-8 mr-2"
                          />
                          <div>
                            <div className="font-medium">{member.name}</div>
                            <div className="text-xs text-muted-foreground">
                              {member.email}
                            </div>
                          </div>
                        </Label>
                      </div>
                    ))
                  )}
                </div>
              </div>
              <DialogFooter>
                <Button
                  variant="outline"
                  onClick={() => setSelectedMembers([])}
                >
                  Cancel
                </Button>
                <Button
                  onClick={handleAddMembers}
                  disabled={selectedMembers.length === 0}
                >
                  Add Selected
                </Button>
              </DialogFooter>
            </DialogContent>
          </Dialog>

          {group.members.map((member, index) => (
            <motion.div
              key={member.id}
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: index * 0.05 }}
            >
              <Link to={`/people/${member.id}`}>
                <Card className="p-4 hover:bg-accent transition-colors">
                  <div className="flex items-center">
                    <CustomAvatar
                      icon={member.icon}
                      className="h-10 w-10 mr-3"
                    />
                    <div className="flex-1">
                      <div className="font-medium">{member.name}</div>
                      <div className="text-xs text-muted-foreground">
                        {member.email}
                      </div>
                    </div>
                    <div
                      className={`text-right ${
                        member.balance < 0 ? "text-expense" : "text-income"
                      }`}
                    >
                      {member.balance === 0 ? (
                        <span className="text-muted-foreground">Settled</span>
                      ) : (
                        <>
                          {member.balance < 0 ? "-" : "+"}$
                          {Math.abs(member.balance).toFixed(2)}
                        </>
                      )}
                    </div>
                  </div>
                </Card>
              </Link>
            </motion.div>
          ))}
        </TabsContent>

        <TabsContent value="transactions" className="space-y-3">
          <Button className="w-full" asChild>
            <Link to={`/add-transaction?group=${id}`}>
              <Plus className="mr-2 h-4 w-4" />
              Add Transaction
            </Link>
          </Button>
          {group.transactions.map((transaction, index) => (
            <motion.div
              key={transaction.id}
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: index * 0.05 }}
            >
              <Link to={`/transactions/${transaction.id}`}>
                <Card className="p-4 hover:bg-accent transition-colors">
                  <div className="flex items-center">
                    <CustomAvatar
                      icon={transaction.category.icon}
                      className="h-10 w-10 mr-3"
                    />
                    <div className="flex-1">
                      <div className="font-medium">{transaction.title}</div>
                      <div className="text-xs text-muted-foreground">
                        {transaction.date}
                      </div>
                    </div>
                    <div
                      className={`text-right ${
                        transaction.amount < 0 ? "text-expense" : "text-income"
                      }`}
                    >
                      {transaction.amount < 0 ? "-" : "+"}$
                      {Math.abs(transaction.amount).toFixed(2)}
                    </div>
                  </div>
                </Card>
              </Link>
            </motion.div>
          ))}
        </TabsContent>
      </Tabs>
    </motion.div>
  );
}
