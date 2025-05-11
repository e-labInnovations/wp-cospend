import { type PageHeaderProps } from "@/components/page-header";
import PageLayout from "@/pages/page-layout";
import { motion } from "framer-motion";
import { useEffect, useState } from "react";
import { authService, type User } from "@/services/api/auth";
import { useToast } from "@/components/ui/use-toast";
import { CustomAvatar } from "@/components/ui/custom-avatar";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";

const headerProps: PageHeaderProps = {
  title: "Profile",
  backLink: "/settings",
};

export default function ProfilePage() {
  const [user, setUser] = useState<User | null>(null);
  const [loading, setLoading] = useState(true);
  const { toast } = useToast();

  useEffect(() => {
    const fetchUser = async () => {
      try {
        const userData = await authService.getMe();
        setUser(userData);
      } catch {
        toast({
          title: "Error",
          description: "Failed to load user profile",
          variant: "destructive",
        });
      } finally {
        setLoading(false);
      }
    };

    fetchUser();
  }, [toast]);

  if (loading) {
    return (
      <PageLayout headerProps={headerProps}>
        <div className="flex items-center justify-center h-64">
          <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
        </div>
      </PageLayout>
    );
  }

  if (!user) {
    return (
      <PageLayout headerProps={headerProps}>
        <div className="flex items-center justify-center h-64">
          <p className="text-muted-foreground">No user data available</p>
        </div>
      </PageLayout>
    );
  }

  return (
    <PageLayout headerProps={headerProps}>
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.3 }}
      >
        <div className="flex flex-col items-center mb-6">
          {user.avatar_urls["96"] ? (
            <img
              src={user.avatar_urls["96"]}
              alt={user.name}
              className="h-24 w-24 rounded-full object-cover mb-4"
            />
          ) : (
            <CustomAvatar
              icon={user.name.charAt(0).toUpperCase()}
              className="h-24 w-24 mb-4"
            />
          )}
        </div>

        <Card>
          <CardHeader>
            <CardTitle>User Information</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="space-y-2">
              <h3 className="text-sm font-medium text-muted-foreground">
                Name
              </h3>
              <p className="text-lg">{user.name}</p>
            </div>

            <div className="space-y-2">
              <h3 className="text-sm font-medium text-muted-foreground">
                Email
              </h3>
              <p className="text-lg">{user.email}</p>
            </div>

            <div className="space-y-2">
              <h3 className="text-sm font-medium text-muted-foreground">
                First Name
              </h3>
              <p className="text-lg">{user.first_name || "Not set"}</p>
            </div>

            <div className="space-y-2">
              <h3 className="text-sm font-medium text-muted-foreground">
                Last Name
              </h3>
              <p className="text-lg">{user.last_name || "Not set"}</p>
            </div>

            <div className="space-y-2">
              <h3 className="text-sm font-medium text-muted-foreground">
                Description
              </h3>
              <p className="text-lg">
                {user.description || "No description available"}
              </p>
            </div>
          </CardContent>
        </Card>
      </motion.div>
    </PageLayout>
  );
}
