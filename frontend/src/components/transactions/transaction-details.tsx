import { Card, CardContent } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { motion } from "framer-motion";
import { CustomAvatar } from "@/components/ui/custom-avatar";
import { Button } from "@/components/ui/button";
import { FileText, ImageIcon, ExternalLink } from "lucide-react";
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/components/ui/dialog";

// Mock data - would come from API in real app
const transactionData = {
  id: "1",
  title: "Grocery Shopping",
  amount: -85.75,
  date: "2025-01-10",
  account: { name: "Credit Card", icon: "💳" },
  category: { name: "Food", icon: "🍔" },
  tags: [{ name: "Essentials", icon: "🛒" }],
  note: "Weekly grocery shopping at Whole Foods. Bought fruits, vegetables, and some snacks for the week.",
  group: { id: "1", name: "Roommates", icon: "🏠" },
  person: null, // If it's a group transaction, person would be null
  attachments: [
    {
      id: "1",
      name: "receipt.jpg",
      type: "image/jpeg",
      url: "/placeholder.svg?height=300&width=200",
    },
    { id: "2", name: "invoice.pdf", type: "application/pdf", url: "#" },
  ],
};

interface TransactionDetailsProps {
  id: string;
}

export function TransactionDetails({ id }: TransactionDetailsProps) {
  // In a real app, we would fetch the transaction data based on the ID
  const transaction = transactionData;

  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.3 }}
    >
      <Card>
        <CardContent className="p-6">
          <div className="flex flex-col items-center mb-6">
            <CustomAvatar
              icon={transaction.category.icon}
              className="h-16 w-16 mb-3"
            />
            <h2 className="text-xl font-bold">{transaction.title}</h2>
            <div
              className={`text-2xl font-bold mt-2 ${
                transaction.amount < 0
                  ? "text-destructive"
                  : "text-green-600 dark:text-green-400"
              }`}
            >
              {transaction.amount < 0 ? "-" : "+"}$
              {Math.abs(transaction.amount).toFixed(2)}
            </div>
          </div>

          <div className="space-y-4">
            <div className="flex justify-between">
              <span className="text-muted-foreground">Date</span>
              <span>{transaction.date}</span>
            </div>

            <div className="flex justify-between items-center">
              <span className="text-muted-foreground">Account</span>
              <div className="flex items-center">
                <span className="mr-2">{transaction.account.icon}</span>
                <span>{transaction.account.name}</span>
              </div>
            </div>

            <div className="flex justify-between items-center">
              <span className="text-muted-foreground">Category</span>
              <div className="flex items-center">
                <span className="mr-2">{transaction.category.icon}</span>
                <span>{transaction.category.name}</span>
              </div>
            </div>

            <div className="flex justify-between items-start">
              <span className="text-muted-foreground">Tags</span>
              <div className="flex flex-wrap justify-end gap-1">
                {transaction.tags.map((tag) => (
                  <Badge key={tag.name} variant="outline">
                    {tag.icon} {tag.name}
                  </Badge>
                ))}
              </div>
            </div>

            {transaction.group && (
              <div className="flex justify-between items-center">
                <span className="text-muted-foreground">Group</span>
                <div className="flex items-center">
                  <span className="mr-2">{transaction.group.icon}</span>
                  <span>{transaction.group.name}</span>
                </div>
              </div>
            )}

            {transaction.person && (
              <div className="flex justify-between items-center">
                <span className="text-muted-foreground">Person</span>
                <div className="flex items-center">
                  <span className="mr-2">{transaction.person.icon}</span>
                  <span>{transaction.person.name}</span>
                </div>
              </div>
            )}

            {transaction.note && (
              <div className="pt-4 border-t">
                <div className="text-muted-foreground mb-1">Note</div>
                <p>{transaction.note}</p>
              </div>
            )}

            {transaction.attachments && transaction.attachments.length > 0 && (
              <div className="pt-4 border-t">
                <div className="text-muted-foreground mb-2">Attachments</div>
                <div className="grid grid-cols-2 gap-2">
                  {transaction.attachments.map((attachment) => (
                    <Dialog key={attachment.id}>
                      <DialogTrigger asChild>
                        <Button
                          variant="outline"
                          className="flex items-center justify-start h-auto p-2 w-full"
                        >
                          {attachment.type.startsWith("image/") ? (
                            <div className="flex items-center">
                              <ImageIcon className="h-5 w-5 mr-2" />
                              <span className="text-sm truncate">
                                {attachment.name}
                              </span>
                            </div>
                          ) : (
                            <div className="flex items-center">
                              <FileText className="h-5 w-5 mr-2" />
                              <span className="text-sm truncate">
                                {attachment.name}
                              </span>
                            </div>
                          )}
                        </Button>
                      </DialogTrigger>
                      <DialogContent className="sm:max-w-md">
                        <DialogHeader>
                          <DialogTitle>{attachment.name}</DialogTitle>
                        </DialogHeader>
                        <div className="flex flex-col items-center justify-center p-4">
                          {attachment.type.startsWith("image/") ? (
                            <img
                              src={attachment.url || "/placeholder.svg"}
                              alt={attachment.name}
                              className="max-w-full max-h-[70vh] object-contain rounded-md"
                            />
                          ) : (
                            <div className="text-center p-8">
                              <FileText className="h-16 w-16 mx-auto mb-4 text-muted-foreground" />
                              <p className="mb-4">
                                Preview not available for this file type
                              </p>
                              <Button>
                                <ExternalLink className="mr-2 h-4 w-4" />
                                Open File
                              </Button>
                            </div>
                          )}
                        </div>
                      </DialogContent>
                    </Dialog>
                  ))}
                </div>
              </div>
            )}
          </div>
        </CardContent>
      </Card>
    </motion.div>
  );
}
