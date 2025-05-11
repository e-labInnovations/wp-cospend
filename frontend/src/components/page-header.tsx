import type React from "react";
import { cn } from "@/lib/utils";
import { Button } from "./ui/button";
import { Link } from "react-router-dom";
import { ChevronLeft } from "lucide-react";

export interface PageHeaderProps {
  title: string;
  description?: string;
  className?: string;
  actions?: React.ReactNode;
  backLink?: string;
}

export function PageHeader({
  title,
  description,
  className,
  actions,
  backLink,
}: PageHeaderProps) {
  return (
    <div className={cn("sticky top-0 z-50 bg-background border-b", className)}>
      <div className="flex items-center justify-between py-4 px-4">
        <div className="flex items-center gap-2">
          {backLink && (
            <Button variant="ghost" size="icon" asChild>
              <Link to={backLink}>
                <ChevronLeft className="h-5 w-5" />
              </Link>
            </Button>
          )}
          <div>
            <h1 className="text-2xl font-bold tracking-tight">{title}</h1>
            {description && (
              <p className="text-muted-foreground">{description}</p>
            )}
          </div>
        </div>
        {actions && <div className="flex items-center gap-2">{actions}</div>}
      </div>
    </div>
  );
}
