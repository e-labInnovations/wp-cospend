"use client";

import { Card } from "@/components/ui/card";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { motion } from "framer-motion";
import { Link, useNavigate, useLocation } from "react-router-dom";
import { CustomAvatar } from "@/components/ui/custom-avatar";

// Mock data - would come from API in real app
const groups = [
  {
    id: 1,
    name: "Roommates",
    balance: 125.5,
    icon: "🏠",
    members: 3,
  },
  {
    id: 2,
    name: "Trip to Paris",
    balance: -45.75,
    icon: "✈️",
    members: 5,
  },
  {
    id: 3,
    name: "Family",
    balance: 0,
    icon: "👨‍👩‍👧‍👦",
    members: 4,
  },
];

const people = [
  {
    id: 1,
    name: "John Doe",
    balance: 50.25,
    icon: "👨",
  },
  {
    id: 2,
    name: "Jane Smith",
    balance: -25.5,
    icon: "👩",
  },
  {
    id: 3,
    name: "Mike Johnson",
    balance: 0,
    icon: "👨",
  },
];

export function GroupsList() {
  const navigate = useNavigate();
  const location = useLocation();
  const currentTab = location.pathname.includes("/people")
    ? "people"
    : "groups";

  const handleTabChange = (value: string) => {
    navigate(value === "people" ? "/people" : "/groups", { replace: true });
  };

  return (
    <Tabs value={currentTab} onValueChange={handleTabChange} className="w-full">
      <TabsList className="grid w-full grid-cols-2 mb-4">
        <TabsTrigger value="groups">Groups</TabsTrigger>
        <TabsTrigger value="people">People</TabsTrigger>
      </TabsList>

      <TabsContent value="groups" className="space-y-3">
        {groups.map((group, index) => (
          <motion.div
            key={group.id}
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: index * 0.05 }}
          >
            <Link to={`/groups/${group.id}`}>
              <Card className="p-4 hover:bg-accent transition-colors">
                <div className="flex items-center">
                  <CustomAvatar icon={group.icon} className="h-10 w-10 mr-3" />
                  <div className="flex-1">
                    <div className="font-medium">{group.name}</div>
                    <div className="text-xs text-muted-foreground">
                      {group.members} members
                    </div>
                  </div>
                  <div
                    className={`text-right ${
                      group.balance < 0
                        ? "text-destructive"
                        : group.balance > 0
                        ? "text-green-600 dark:text-green-400"
                        : "text-muted-foreground"
                    }`}
                  >
                    {group.balance === 0 ? (
                      "Settled"
                    ) : (
                      <>
                        {group.balance < 0 ? "-" : "+"}$
                        {Math.abs(group.balance).toFixed(2)}
                      </>
                    )}
                  </div>
                </div>
              </Card>
            </Link>
          </motion.div>
        ))}
      </TabsContent>

      <TabsContent value="people" className="space-y-3">
        {people.map((person, index) => (
          <motion.div
            key={person.id}
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: index * 0.05 }}
          >
            <Link to={`/people/${person.id}`}>
              <Card className="p-4 hover:bg-accent transition-colors">
                <div className="flex items-center">
                  <CustomAvatar icon={person.icon} className="h-10 w-10 mr-3" />
                  <div className="flex-1">
                    <div className="font-medium">{person.name}</div>
                  </div>
                  <div
                    className={`text-right ${
                      person.balance < 0
                        ? "text-destructive"
                        : person.balance > 0
                        ? "text-green-600 dark:text-green-400"
                        : "text-muted-foreground"
                    }`}
                  >
                    {person.balance === 0 ? (
                      "Settled"
                    ) : (
                      <>
                        {person.balance < 0 ? "-" : "+"}$
                        {Math.abs(person.balance).toFixed(2)}
                      </>
                    )}
                  </div>
                </div>
              </Card>
            </Link>
          </motion.div>
        ))}
      </TabsContent>
    </Tabs>
  );
}
