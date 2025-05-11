"use client";

import { Card } from "@/components/ui/card";
import { motion } from "framer-motion";
import { Link } from "react-router-dom";
import { CustomAvatar } from "@/components/ui/custom-avatar";
import { Edit, Trash } from "lucide-react";
import { Button } from "@/components/ui/button";
import { useState } from "react";
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from "@/components/ui/alert-dialog";

// Mock data - would come from API in real app
const accounts = [
  { id: 1, name: "Cash", icon: "💵", balance: 1250.75, count: 15 },
  { id: 2, name: "Bank", icon: "🏦", balance: 3420.5, count: 22 },
  { id: 3, name: "Credit Card", icon: "💳", balance: -450.25, count: 18 },
  { id: 4, name: "Savings", icon: "🏆", balance: 5000, count: 5 },
  { id: 5, name: "Shared Bank Account", icon: "🏦", balance: 450.25, count: 8 },
];

export function AccountsList() {
  const [hoveredAccount, setHoveredAccount] = useState<number | null>(null);
  const [accountToDelete, setAccountToDelete] = useState<{
    id: number;
    name: string;
  } | null>(null);

  const handleDeleteClick = (id: number, name: string) => {
    setAccountToDelete({ id, name });
  };

  const handleDelete = () => {
    // In a real app, we would send this data to the API
    console.log("Deleting account:", accountToDelete);
    setAccountToDelete(null);
  };

  return (
    <div className="grid grid-cols-2 gap-3">
      {accounts.map((account) => (
        <motion.div
          key={account.id}
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: (account.id % 10) * 0.05 }}
          className="relative group"
          onMouseEnter={() => setHoveredAccount(account.id)}
          onMouseLeave={() => setHoveredAccount(null)}
        >
          <Link to={`/accounts/${account.id}`}>
            <Card className="p-4 hover:bg-accent transition-colors">
              <div className="flex flex-col items-center text-center">
                <CustomAvatar icon={account.icon} className="h-12 w-12 mb-2" />
                <div className="font-medium">{account.name}</div>
                <div
                  className={`text-sm font-medium ${
                    account.balance < 0 ? "text-expense" : "text-income"
                  }`}
                >
                  ${account.balance.toFixed(2)}
                </div>
                <div className="text-xs text-muted-foreground">
                  {account.count} transactions
                </div>
              </div>
            </Card>
          </Link>

          {hoveredAccount === account.id && (
            <div className="absolute top-2 right-2 flex items-center gap-1 bg-background/80 rounded p-1">
              <Button variant="ghost" size="icon" className="h-7 w-7" asChild>
                <Link to={`/accounts/${account.id}/edit`}>
                  <Edit className="h-3.5 w-3.5" />
                </Link>
              </Button>
              <Button
                variant="ghost"
                size="icon"
                className="h-7 w-7"
                onClick={(e) => {
                  e.preventDefault();
                  handleDeleteClick(account.id, account.name);
                }}
              >
                <Trash className="h-3.5 w-3.5" />
              </Button>
            </div>
          )}
        </motion.div>
      ))}

      <AlertDialog
        open={!!accountToDelete}
        onOpenChange={(open) => !open && setAccountToDelete(null)}
      >
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle>Are you sure?</AlertDialogTitle>
            <AlertDialogDescription>
              Delete this account will delete all the transactions associated
              with this account
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter>
            <AlertDialogCancel>Cancel</AlertDialogCancel>
            <AlertDialogAction
              onClick={handleDelete}
              className="bg-destructive text-destructive-foreground"
            >
              Delete
            </AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>
    </div>
  );
}
