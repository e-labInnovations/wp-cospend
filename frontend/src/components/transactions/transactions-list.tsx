"use client";

import { Card } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { motion } from "framer-motion";
import { Link } from "react-router-dom";
import { CustomAvatar } from "@/components/ui/custom-avatar";

// Mock data - would come from API in real app
const transactions = [
  {
    id: 1,
    title: "Grocery Shopping",
    amount: -85.75,
    date: "2025-01-10",
    account: { name: "Credit Card", icon: "💳" },
    category: { name: "Food", icon: "🍔" },
    tags: [{ name: "Essentials", icon: "🛒" }],
  },
  {
    id: 2,
    title: "Salary",
    amount: 2500,
    date: "2025-01-05",
    account: { name: "Bank", icon: "🏦" },
    category: { name: "Income", icon: "💰" },
    tags: [{ name: "Work", icon: "💼" }],
  },
  {
    id: 3,
    title: "Restaurant",
    amount: -45.5,
    date: "2025-01-08",
    account: { name: "Cash", icon: "💵" },
    category: { name: "Food", icon: "🍔" },
    tags: [{ name: "Dining", icon: "🍽️" }],
  },
  {
    id: 4,
    title: "Gas Station",
    amount: -35.25,
    date: "2025-01-09",
    account: { name: "Credit Card", icon: "💳" },
    category: { name: "Transport", icon: "🚗" },
    tags: [{ name: "Car", icon: "⛽" }],
  },
  {
    id: 5,
    title: "Movie Tickets",
    amount: -24.0,
    date: "2025-01-11",
    account: { name: "Cash", icon: "💵" },
    category: { name: "Entertainment", icon: "🎬" },
    tags: [{ name: "Leisure", icon: "🎭" }],
  },
];

export function TransactionsList() {
  return (
    <div className="space-y-3">
      {transactions.map((transaction, index) => (
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
                  <div className="flex items-center text-xs text-muted-foreground">
                    <span>{transaction.date}</span>
                    <span className="mx-1">•</span>
                    <span>{transaction.account.name}</span>
                    <span className="mx-1">•</span>
                    <span>{transaction.category.name}</span>
                  </div>
                </div>
                <div
                  className={`text-right ${
                    transaction.amount < 0
                      ? "text-destructive"
                      : "text-green-600 dark:text-green-400"
                  }`}
                >
                  {transaction.amount < 0 ? "-" : "+"}$
                  {Math.abs(transaction.amount).toFixed(2)}
                </div>
              </div>
              <div className="mt-2 flex gap-1">
                {transaction.tags.map((tag) => (
                  <Badge key={tag.name} variant="outline" className="text-xs">
                    {tag.icon} {tag.name}
                  </Badge>
                ))}
              </div>
            </Card>
          </Link>
        </motion.div>
      ))}
    </div>
  );
}
