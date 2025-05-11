import { type PageHeaderProps } from "@/components/page-header";
import { GroupsList } from "@/components/groups/groups-list";
import { Button } from "@/components/ui/button";
import { Plus } from "lucide-react";
import { Link } from "react-router-dom";
import PageLayout from "../page-layout";
import { useLocation } from "react-router-dom";

export default function GroupsPage() {
  const location = useLocation();
  const type = location.pathname.includes("people") ? "people" : "groups";

  const headerProps: PageHeaderProps = {
    title: "Groups & People",
    actions: (
      <Button size="icon" asChild>
        <Link to={`/${type}/add`}>
          <Plus className="h-5 w-5" />
        </Link>
      </Button>
    ),
  };

  return (
    <PageLayout headerProps={headerProps}>
      <GroupsList />
    </PageLayout>
  );
}
