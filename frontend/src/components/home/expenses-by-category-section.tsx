import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { motion } from "framer-motion";
import { CustomAvatar } from "@/components/ui/custom-avatar";

// Mock data - would come from API in real app
const categories = [
  { id: 1, name: "Food", icon: "🍔", amount: 350.75, percentage: 35 },
  { id: 2, name: "Transport", icon: "🚗", amount: 250.5, percentage: 25 },
  { id: 3, name: "Entertainment", icon: "🎬", amount: 150.25, percentage: 15 },
  { id: 4, name: "Utilities", icon: "💡", amount: 120.0, percentage: 12 },
  { id: 5, name: "Rent", icon: "🏠", amount: 130.5, percentage: 13 },
];

export function ExpensesByCategorySection() {
  return (
    <Card>
      <CardHeader className="pb-2">
        <CardTitle className="text-lg">Expenses by Category</CardTitle>
      </CardHeader>
      <CardContent>
        <div className="space-y-3">
          {categories.map((category, index) => (
            <motion.div
              key={category.id}
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: index * 0.05 }}
              className="flex items-center"
            >
              <CustomAvatar icon={category.icon} className="h-8 w-8 mr-3" />
              <div className="flex-1 min-w-0">
                <div className="flex justify-between mb-1">
                  <span className="font-medium text-sm">{category.name}</span>
                  <span className="text-sm">${category.amount.toFixed(2)}</span>
                </div>
                <div className="w-full bg-muted rounded-full h-2 overflow-hidden">
                  <motion.div
                    className="bg-primary h-full"
                    initial={{ width: 0 }}
                    animate={{ width: `${category.percentage}%` }}
                    transition={{ delay: index * 0.05 + 0.2, duration: 0.5 }}
                  />
                </div>
              </div>
            </motion.div>
          ))}
        </div>
      </CardContent>
    </Card>
  );
}
