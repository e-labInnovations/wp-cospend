import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { motion } from "framer-motion";
import { CustomAvatar } from "@/components/ui/custom-avatar";

// Mock data - would come from API in real app
const tags = [
  { id: 1, name: "Essentials", icon: "🛒", amount: 280.5, percentage: 28 },
  { id: 2, name: "Dining", icon: "🍽️", amount: 220.75, percentage: 22 },
  { id: 3, name: "Car", icon: "⛽", amount: 180.25, percentage: 18 },
  { id: 4, name: "Leisure", icon: "🎭", amount: 150.0, percentage: 15 },
  { id: 5, name: "Monthly", icon: "📅", amount: 170.5, percentage: 17 },
];

export function ExpensesByTagSection() {
  return (
    <Card>
      <CardHeader className="pb-2">
        <CardTitle className="text-lg">Expenses by Tag</CardTitle>
      </CardHeader>
      <CardContent>
        <div className="space-y-3">
          {tags.map((tag, index) => (
            <motion.div
              key={tag.id}
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: index * 0.05 }}
              className="flex items-center"
            >
              <CustomAvatar icon={tag.icon} className="h-8 w-8 mr-3" />
              <div className="flex-1 min-w-0">
                <div className="flex justify-between mb-1">
                  <span className="font-medium text-sm">{tag.name}</span>
                  <span className="text-sm">${tag.amount.toFixed(2)}</span>
                </div>
                <div className="w-full bg-muted rounded-full h-2 overflow-hidden">
                  <motion.div
                    className="bg-accent h-full"
                    initial={{ width: 0 }}
                    animate={{ width: `${tag.percentage}%` }}
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
