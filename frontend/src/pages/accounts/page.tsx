import { AccountsList } from "@/components/accounts/accounts-list";
import { Button } from "@/components/ui/button";
import { Plus } from "lucide-react";
import { Link } from "react-router-dom";
import PageLayout from "../page-layout";
import type { PageHeaderProps } from "@/components/page-header";

const headerProps: PageHeaderProps = {
  title: "Accounts",
  backLink: "/extra",
  actions: (
    <Button size="icon" asChild>
      <Link to="/accounts/add">
        <Plus className="h-5 w-5" />
      </Link>
    </Button>
  ),
};

export default function AccountsPage() {
  return (
    <PageLayout headerProps={headerProps}>
      <AccountsList />
    </PageLayout>
  );
}
