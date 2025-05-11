import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { motion } from "framer-motion";
import { CustomAvatar } from "@/components/ui/custom-avatar";

// Mock data - would come from API in real app
const accounts = [
  { id: 1, name: "Cash", balance: 1250.75, icon: "💵" },
  { id: 2, name: "Bank", balance: 3420.5, icon: "🏦" },
  { id: 3, name: "Credit Card", balance: -450.25, icon: "💳" },
  { id: 4, name: "Savings", balance: 5000, icon: "🏆" },
];

export function AccountsSection() {
  const totalBalance = accounts.reduce(
    (sum, account) => sum + account.balance,
    0
  );

  return (
    <Card>
      <CardHeader className="pb-2">
        <CardTitle className="text-lg flex justify-between">
          <span>Accounts</span>
          <span className="font-medium">${totalBalance.toFixed(2)}</span>
        </CardTitle>
      </CardHeader>
      <CardContent>
        <div className="grid grid-cols-2 gap-3">
          {accounts.map((account, index) => (
            <motion.div
              key={account.id}
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: index * 0.1 }}
              className="flex items-center p-3 rounded-lg border bg-card"
            >
              <CustomAvatar icon={account.icon} className="h-10 w-10 mr-3" />
              <div>
                <div className="font-medium text-sm">{account.name}</div>
                <div
                  className={`text-sm ${
                    account.balance < 0
                      ? "text-destructive"
                      : "text-muted-foreground"
                  }`}
                >
                  ${account.balance.toFixed(2)}
                </div>
              </div>
            </motion.div>
          ))}
        </div>
      </CardContent>
    </Card>
  );
}
