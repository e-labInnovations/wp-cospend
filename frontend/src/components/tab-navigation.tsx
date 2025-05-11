import { cn } from "@/lib/utils";
import { Link, useLocation } from "react-router-dom";
import {
  Home,
  Receipt,
  PlusCircle,
  Users,
  MoreHorizontal,
  Settings,
} from "lucide-react";
import { motion } from "framer-motion";

interface TabNavigationProps {
  className?: string;
}

export function TabNavigation({ className }: TabNavigationProps) {
  const location = useLocation();

  const tabs = [
    {
      name: "Home",
      href: "/",
      icon: Home,
    },
    {
      name: "Transactions",
      href: "/transactions",
      icon: Receipt,
    },
    {
      name: "Add",
      href: "/add-transaction",
      icon: PlusCircle,
    },
    {
      name: "Groups",
      href: "/groups",
      icon: Users,
    },
    {
      name: "Extra",
      href: "/extra",
      icon: MoreHorizontal,
    },
    {
      name: "Settings",
      href: "/settings",
      icon: Settings,
    },
  ];

  return (
    <div
      className={cn(
        "bg-background border-t md:border-r md:border-t-0",
        className
      )}
    >
      <nav className="flex md:flex-col justify-around md:justify-start md:pt-16 md:gap-2">
        {tabs.map((tab) => {
          const isActive =
            tab.href === "/"
              ? location.pathname === "/"
              : location.pathname.startsWith(tab.href);

          return (
            <Link
              key={tab.name}
              to={tab.href}
              className={cn(
                "flex flex-col md:flex-row items-center justify-center md:justify-start py-2 px-3 md:px-4 md:py-3 relative",
                "transition-colors duration-200",
                isActive
                  ? "text-primary"
                  : "text-muted-foreground hover:text-foreground"
              )}
            >
              {isActive && (
                <motion.div
                  layoutId="active-tab"
                  className="absolute bottom-0 md:left-0 w-12 md:w-1 h-1 md:h-8 bg-primary rounded-t-full md:rounded-r-full md:rounded-l-none"
                  transition={{ type: "spring", duration: 0.5 }}
                />
              )}
              <tab.icon className="w-6 h-6 md:mr-3" />
              <span className="text-xs md:text-sm font-medium">{tab.name}</span>
            </Link>
          );
        })}
      </nav>
    </div>
  );
}
