import { type PageHeaderProps } from "@/components/page-header";
import { GroupDetails } from "@/components/groups/group-details";
import { Button } from "@/components/ui/button";
import { Edit } from "lucide-react";
import { Link, useParams } from "react-router-dom";
import PageLayout from "../../page-layout";

export default function GroupPage() {
  const { id } = useParams();

  const headerProps: PageHeaderProps = {
    title: "Group Details",
    actions: (
      <Button variant="ghost" size="icon">
        <Link to={`/groups/${id}/edit`}>
          <Edit className="h-5 w-5" />
        </Link>
      </Button>
    ),
    backLink: "/groups",
  };

  return (
    <PageLayout headerProps={headerProps}>
      {id && <GroupDetails id={id} />}
    </PageLayout>
  );
}
