import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { motion } from "framer-motion";

// Mock data - would come from API in real app
const weeklyExpenses = [
  { day: "Mon", amount: 45.5 },
  { day: "Tue", amount: 20.75 },
  { day: "Wed", amount: 65.0 },
  { day: "Thu", amount: 32.25 },
  { day: "Fri", amount: 85.5 },
  { day: "Sat", amount: 120.0 },
  { day: "Sun", amount: 15.75 },
];

export function ExpensesThisWeekSection() {
  const maxAmount = Math.max(...weeklyExpenses.map((day) => day.amount));

  return (
    <Card>
      <CardHeader className="pb-2">
        <CardTitle className="text-lg">Expenses This Week</CardTitle>
      </CardHeader>
      <CardContent>
        <div className="flex justify-between items-end h-40 pt-4">
          {weeklyExpenses.map((day) => {
            const barHeight = (day.amount / maxAmount) * 100;

            return (
              <div key={day.day} className="flex flex-col items-center">
                {/* <div className="relative h-full w-8 flex items-end mb-2">
                    <motion.div
                      className="w-full bg-primary rounded-t-md"
                      initial={{ height: 0 }}
                      animate={{ height: `${barHeight}%` }}
                      transition={{ delay: index * 0.1, duration: 0.5 }}
                    />
                  </div> */}
                <div className="mb-5 flex flex-col items-center">
                  <div className="h-24 w-4 overflow-hidden rounded-md bg-red-700">
                    <div
                      className="h-full bg-white"
                      style={{ height: `${barHeight}%` }}
                    ></div>
                  </div>
                </div>
                <div className="text-xs font-medium">{day.day}</div>
                <div className="text-xs text-muted-foreground">
                  ${day.amount}
                </div>
              </div>
            );
          })}
        </div>
      </CardContent>
    </Card>
  );
}
