import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import {
  ChevronLeft,
  ChevronRight,
  ArrowUpRight,
  ArrowDownRight,
  Wallet,
} from "lucide-react";
import { Button } from "@/components/ui/button";
import { useState } from "react";
import { motion, AnimatePresence } from "framer-motion";

// Mock data - would come from API in real app
const months = [
  {
    id: 1,
    name: "January 2025",
    startDate: "2025-01-01",
    endDate: "2025-01-31",
    income: 4500,
    expenses: 3200,
  },
  {
    id: 2,
    name: "February 2025",
    startDate: "2025-02-01",
    endDate: "2025-02-28",
    income: 4800,
    expenses: 3500,
  },
  {
    id: 3,
    name: "March 2025",
    startDate: "2025-03-01",
    endDate: "2025-03-31",
    income: 5200,
    expenses: 3800,
  },
];

export function MonthlySummarySection() {
  const [currentMonthIndex, setCurrentMonthIndex] = useState(0);
  const currentMonth = months[currentMonthIndex];

  const goToPreviousMonth = () => {
    if (currentMonthIndex > 0) {
      setCurrentMonthIndex(currentMonthIndex - 1);
    }
  };

  const goToNextMonth = () => {
    if (currentMonthIndex < months.length - 1) {
      setCurrentMonthIndex(currentMonthIndex + 1);
    }
  };

  return (
    <Card>
      <CardHeader className="pb-2">
        <CardTitle className="text-lg flex justify-between items-center">
          <span>Monthly Summary</span>
          <div className="flex items-center">
            <Button
              variant="ghost"
              size="icon"
              onClick={goToPreviousMonth}
              disabled={currentMonthIndex === 0}
            >
              <ChevronLeft className="h-4 w-4" />
            </Button>
            <span className="text-sm mx-2">{currentMonth.name}</span>
            <Button
              variant="ghost"
              size="icon"
              onClick={goToNextMonth}
              disabled={currentMonthIndex === months.length - 1}
            >
              <ChevronRight className="h-4 w-4" />
            </Button>
          </div>
        </CardTitle>
        <div className="text-xs text-muted-foreground">
          {currentMonth.startDate} to {currentMonth.endDate}
        </div>
      </CardHeader>
      <CardContent>
        <AnimatePresence mode="wait">
          <motion.div
            key={currentMonth.id}
            initial={{ opacity: 0, x: 20 }}
            animate={{ opacity: 1, x: 0 }}
            exit={{ opacity: 0, x: -20 }}
            transition={{ duration: 0.3 }}
            className="space-y-4"
          >
            <div className="flex items-center p-3 rounded-lg border bg-card">
              <div className="h-10 w-10 rounded-full bg-green-100 dark:bg-green-900 flex items-center justify-center mr-3">
                <ArrowUpRight className="h-5 w-5 text-green-600 dark:text-green-400" />
              </div>
              <div className="flex-1">
                <div className="font-medium">Income</div>
                <div className="text-muted-foreground">
                  ${currentMonth.income.toFixed(2)}
                </div>
              </div>
            </div>

            <div className="flex items-center p-3 rounded-lg border bg-card">
              <div className="h-10 w-10 rounded-full bg-red-100 dark:bg-red-900 flex items-center justify-center mr-3">
                <ArrowDownRight className="h-5 w-5 text-red-600 dark:text-red-400" />
              </div>
              <div className="flex-1">
                <div className="font-medium">Expenses</div>
                <div className="text-muted-foreground">
                  ${currentMonth.expenses.toFixed(2)}
                </div>
              </div>
            </div>

            <div className="flex items-center p-3 rounded-lg border bg-card">
              <div className="h-10 w-10 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center mr-3">
                <Wallet className="h-5 w-5 text-blue-600 dark:text-blue-400" />
              </div>
              <div className="flex-1">
                <div className="font-medium">Balance</div>
                <div className="text-muted-foreground">
                  ${(currentMonth.income - currentMonth.expenses).toFixed(2)}
                </div>
              </div>
            </div>
          </motion.div>
        </AnimatePresence>
      </CardContent>
    </Card>
  );
}
