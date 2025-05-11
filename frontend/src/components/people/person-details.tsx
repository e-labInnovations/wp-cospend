"use client";

import { Card, CardContent } from "@/components/ui/card";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { motion } from "framer-motion";
import { Link } from "react-router-dom";
import { CustomAvatar } from "@/components/ui/custom-avatar";
import { Button } from "@/components/ui/button";
import { Plus } from "lucide-react";

// Mock data - would come from API in real app
const personData = {
  id: "1",
  name: "John Doe",
  email: "john@example.com",
  phone: "+1 (555) 123-4567",
  description: "Roommate and friend.",
  balance: 50.25,
  icon: "👨",
  transactions: [
    {
      id: 1,
      title: "Dinner",
      amount: -35.5,
      date: "2025-01-10",
      account: { name: "Cash", icon: "💵" },
      category: { name: "Food", icon: "🍔" },
    },
    {
      id: 2,
      title: "Movie Tickets",
      amount: -24.0,
      date: "2025-01-11",
      account: { name: "Cash", icon: "💵" },
      category: { name: "Entertainment", icon: "🎬" },
    },
  ],
};

interface PersonDetailsProps {
  id: string;
}

export function PersonDetails({ id }: PersonDetailsProps) {
  // In a real app, we would fetch the person data based on the ID
  const person = personData;

  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.3 }}
    >
      <Card className="mb-6">
        <CardContent className="p-4">
          <div className="flex items-center">
            <CustomAvatar icon={person.icon} className="h-12 w-12 mr-4" />
            <div className="flex-1">
              <h2 className="text-xl font-bold">{person.name}</h2>
              <div className="text-sm text-muted-foreground">
                {person.email && <div>{person.email}</div>}
                {person.phone && <div>{person.phone}</div>}
              </div>
              {person.description && (
                <p className="text-sm text-muted-foreground mt-1">
                  {person.description}
                </p>
              )}
            </div>
            <div
              className={`text-right ${
                person.balance < 0 ? "text-expense" : "text-income"
              }`}
            >
              <div className="text-sm font-medium">Balance</div>
              <div className="font-bold">
                {person.balance < 0 ? "-" : "+"}$
                {Math.abs(person.balance).toFixed(2)}
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <Tabs defaultValue="transactions" className="w-full">
        <TabsList className="grid w-full grid-cols-1 mb-4">
          <TabsTrigger value="transactions">Transactions</TabsTrigger>
        </TabsList>

        <TabsContent value="transactions" className="space-y-3">
          <Button className="w-full" asChild>
            <Link to={`/add-transaction?person=${id}`}>
              <Plus className="mr-2 h-4 w-4" />
              Add Transaction
            </Link>
          </Button>
          {person.transactions.map((transaction, index) => (
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
