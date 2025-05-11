"use client";

import { Card } from "@/components/ui/card";
import { motion } from "framer-motion";
import { Link } from "react-router-dom";
import { Bell, Globe, Lock, Shield, User } from "lucide-react";
import { Switch } from "@/components/ui/switch";
import { Label } from "@/components/ui/label";
import { useTheme } from "@/components/theme-provider";
import { useEffect, useState } from "react";

const items = [
  {
    id: "profile",
    name: "Profile",
    icon: User,
    href: "/settings/profile",
    description: "Manage your profile information",
  },
  {
    id: "notifications",
    name: "Notifications",
    icon: Bell,
    href: "/settings/notifications",
    description: "Configure notification preferences",
  },
  {
    id: "security",
    name: "Security",
    icon: Lock,
    href: "/settings/security",
    description: "Update your password and security settings",
  },
  {
    id: "privacy",
    name: "Privacy",
    icon: Shield,
    href: "/settings/privacy",
    description: "Control your privacy settings",
  },
  {
    id: "language",
    name: "Language",
    icon: Globe,
    href: "/settings/language",
    description: "Change your preferred language",
  },
];

export function SettingsItems() {
  const { theme, setTheme } = useTheme();
  const [mounted, setMounted] = useState(false);

  // Avoid hydration mismatch by only showing the switch after mounting
  useEffect(() => {
    setMounted(true);
  }, []);

  return (
    <div className="space-y-6">
      <Card className="p-4">
        <div className="flex items-center justify-between">
          <div className="space-y-0.5">
            <Label htmlFor="dark-mode">Dark Mode</Label>
            <div className="text-sm text-muted-foreground">
              Toggle between light and dark theme
            </div>
          </div>
          {mounted && (
            <Switch
              id="dark-mode"
              checked={theme === "dark"}
              onCheckedChange={(checked) =>
                setTheme(checked ? "dark" : "light")
              }
            />
          )}
        </div>
      </Card>

      <div className="space-y-3">
        {items.map((item, index) => (
          <motion.div
            key={item.id}
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: index * 0.05 }}
          >
            <Link to={item.href}>
              <Card className="p-4 hover:bg-accent transition-colors">
                <div className="flex items-center">
                  <div className="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center mr-3">
                    <item.icon className="h-5 w-5" />
                  </div>
                  <div className="flex-1">
                    <div className="font-medium">{item.name}</div>
                    <div className="text-xs text-muted-foreground">
                      {item.description}
                    </div>
                  </div>
                </div>
              </Card>
            </Link>
          </motion.div>
        ))}
      </div>
    </div>
  );
}
