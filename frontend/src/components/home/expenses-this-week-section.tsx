import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import DayExpenseBar from "./day-expense-bar";

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
        <div className="flex justify-center space-x-4 mb-4">
          {weeklyExpenses.map((day) => {
            const barHeight = (day.amount / maxAmount) * 100;

            return (
              <DayExpenseBar
                percentage={barHeight}
                day={day.day}
                amount={`$${day.amount}`}
                progressColor="bg-red-500"
              />
            );
          })}
        </div>
      </CardContent>
    </Card>
  );
}
