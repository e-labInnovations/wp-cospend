import { type PageHeaderProps } from "@/components/page-header";
import PageLayout from "@/pages/page-layout";
import { motion } from "framer-motion";
import { useToast } from "@/components/ui/use-toast";
import { CustomAvatar } from "@/components/ui/custom-avatar";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { useAuth } from "@/hooks/use-auth";
import { LogOut } from "lucide-react";

const headerProps: PageHeaderProps = {
  title: "Profile",
  backLink: "/settings",
};

export default function ProfilePage() {
  const { toast } = useToast();
  const { user, signOut } = useAuth();

  const handleLogout = () => {
    signOut();
    toast({
      title: "Success",
      description: "You have been logged out successfully.",
    });
  };

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
        className="max-w-2xl mx-auto"
      >
        <div className="flex flex-col items-center mb-8">
          {user.avatar_urls["96"] ? (
            <img
              src={user.avatar_urls["96"].replace("s=96", "s=512")}
              alt={user.name}
              className="h-32 w-32 rounded-full object-cover mb-4 ring-4 ring-primary/10"
            />
          ) : (
            <CustomAvatar
              icon={user.name.charAt(0).toUpperCase()}
              className="h-32 w-32 mb-4 ring-4 ring-primary/10"
            />
          )}
          <h2 className="text-2xl font-semibold mb-2">{user.name}</h2>
          <p className="text-muted-foreground">{user.email}</p>
        </div>

        <div className="space-y-6">
          <Card>
            <CardHeader>
              <CardTitle>Personal Information</CardTitle>
            </CardHeader>
            <CardContent className="space-y-6">
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
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
              </div>

              <div className="space-y-2">
                <h3 className="text-sm font-medium text-muted-foreground">
                  Description
                </h3>
                <p className="text-lg whitespace-pre-wrap">
                  {user.description || "No description available"}
                </p>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Account Actions</CardTitle>
            </CardHeader>
            <CardContent>
              <Button
                variant="destructive"
                onClick={handleLogout}
                className="w-full md:w-auto"
              >
                <LogOut className="mr-2 h-4 w-4" />
                Logout
              </Button>
            </CardContent>
          </Card>
        </div>
      </motion.div>
    </PageLayout>
  );
}
