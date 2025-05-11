"use client";

import { Card } from "@/components/ui/card";
import { motion } from "framer-motion";
import { Link } from "react-router-dom";
import { Tag, Wallet, Coins, PieChart } from "lucide-react";
import { CustomAvatar } from "@/components/ui/custom-avatar";

const items = [
  {
    id: "categories",
    name: "Categories",
    icon: PieChart,
    href: "/categories",
    count: 12,
  },
  {
    id: "tags",
    name: "Tags",
    icon: Tag,
    href: "/tags",
    count: 24,
  },
  {
    id: "accounts",
    name: "Accounts",
    icon: Wallet,
    href: "/accounts",
    count: 4,
  },
  {
    id: "currencies",
    name: "Currencies",
    icon: Coins,
    href: "/currencies",
    count: 3,
  },
];

export function ExtraItems() {
  return (
    <div className="grid grid-cols-2 gap-3">
      {items.map((item, index) => (
        <motion.div
          key={item.id}
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: index * 0.05 }}
        >
          <Link to={item.href}>
            <Card className="p-4 hover:bg-accent transition-colors h-full">
              <div className="flex flex-col items-center text-center">
                <CustomAvatar
                  icon={<item.icon className="h-6 w-6" />}
                  className="h-12 w-12 mb-3"
                />
                <div className="font-medium">{item.name}</div>
                <div className="text-xs text-muted-foreground mt-1">
                  {item.count} items
                </div>
              </div>
            </Card>
          </Link>
        </motion.div>
      ))}
    </div>
  );
}
