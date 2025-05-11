import { useEffect, useState } from "react";
import { cn } from "@/lib/utils";

interface DayExpenseBarProps {
  percentage: number;
  day: string;
  amount: string;
  className?: string;
  progressColor?: string;
}

const DayExpenseBar = ({
  percentage,
  day,
  amount,
  className,
  progressColor,
}: DayExpenseBarProps) => {
  const [animatedPercentage, setAnimatedPercentage] = useState(0);

  useEffect(() => {
    setAnimatedPercentage(0);

    const timer = setTimeout(() => {
      setAnimatedPercentage(percentage);
    }, 50);

    return () => clearTimeout(timer);
  }, [percentage]);

  return (
    <div
      className={cn("flex flex-col items-center", className)}
      role="group"
      aria-label={`Expenses for ${day}`}
    >
      <div
        className="text-xs font-medium text-muted-foreground mb-1"
        aria-hidden="true"
      >
        {day}
      </div>
      <div
        className="relative h-40 w-4 bg-muted rounded-full overflow-hidden"
        role="progressbar"
        aria-valuemin={0}
        aria-valuemax={100}
        aria-valuenow={animatedPercentage}
        aria-label={`${animatedPercentage}% of expenses for ${day}`}
      >
        <div
          className={cn(
            "absolute bottom-0 w-full rounded-b-full transition-all duration-1000 ease-out",
            progressColor || "bg-primary"
          )}
          style={{ height: `${animatedPercentage}%` }}
        />
      </div>
      <div
        className="text-xs font-medium text-muted-foreground mt-1"
        aria-hidden="true"
      >
        {amount}
      </div>
    </div>
  );
};

export default DayExpenseBar;
