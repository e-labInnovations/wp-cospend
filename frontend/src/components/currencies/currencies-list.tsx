"use client";
import { useState } from "react";
import { Button } from "@/components/ui/button";
import { Star } from "lucide-react";
import { motion, AnimatePresence } from "framer-motion";

// Mock data - would come from API in real app
const initialCurrencies = [
  {
    id: 1,
    name: "US Dollar",
    code: "USD",
    symbol: "$",
    icon: "🇺🇸",
    isDefault: false,
  },
  {
    id: 2,
    name: "Euro",
    code: "EUR",
    symbol: "€",
    icon: "🇪🇺",
    isDefault: false,
  },
  {
    id: 3,
    name: "British Pound",
    code: "GBP",
    symbol: "£",
    icon: "🇬🇧",
    isDefault: false,
  },
  {
    id: 4,
    name: "Japanese Yen",
    code: "JPY",
    symbol: "¥",
    icon: "🇯🇵",
    isDefault: false,
  },
  {
    id: 5,
    name: "Canadian Dollar",
    code: "CAD",
    symbol: "C$",
    icon: "🇨🇦",
    isDefault: false,
  },
  {
    id: 6,
    name: "Indian Rupee",
    code: "INR",
    symbol: "₹",
    icon: "🇮🇳",
    isDefault: true,
  },
];

export function CurrenciesList() {
  const [hoveredCurrency, setHoveredCurrency] = useState<number | null>(null);
  const [currencies, setCurrencies] = useState(initialCurrencies);

  const handleSetDefault = (id: number) => {
    // In a real app, this would make an API call
    console.log("Setting default currency:", id);

    setCurrencies((prevCurrencies) => {
      const newCurrencies = prevCurrencies.map((currency) => ({
        ...currency,
        isDefault: currency.id === id,
      }));

      // Move the selected currency to the top
      const selectedCurrency = newCurrencies.find((c) => c.id === id);
      const otherCurrencies = newCurrencies.filter((c) => c.id !== id);

      return selectedCurrency
        ? [selectedCurrency, ...otherCurrencies]
        : newCurrencies;
    });
  };

  return (
    <div className="space-y-4">
      <AnimatePresence initial={false}>
        {currencies.map((currency) => (
          <motion.div
            key={currency.id}
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0, y: -20 }}
            transition={{ duration: 0.2 }}
            className="flex items-center justify-between p-4 rounded-lg border bg-card hover:bg-accent/50 transition-colors"
            onMouseEnter={() => setHoveredCurrency(currency.id)}
            onMouseLeave={() => setHoveredCurrency(null)}
          >
            <div className="flex items-center gap-3">
              <span className="text-2xl">{currency.icon}</span>
              <div>
                <div className="font-medium">{currency.name}</div>
                <div className="text-sm text-muted-foreground">
                  {currency.code} • {currency.symbol}
                </div>
              </div>
            </div>
            <div className="flex items-center gap-2">
              {hoveredCurrency === currency.id && (
                <Button
                  variant="ghost"
                  size="icon"
                  onClick={() => handleSetDefault(currency.id)}
                  disabled={currency.isDefault}
                >
                  <Star
                    className={`h-4 w-4 ${
                      currency.isDefault ? "fill-primary text-primary" : ""
                    }`}
                  />
                </Button>
              )}
            </div>
          </motion.div>
        ))}
      </AnimatePresence>
    </div>
  );
}
