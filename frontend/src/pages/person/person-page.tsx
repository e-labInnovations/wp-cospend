import { type PageHeaderProps } from "@/components/page-header";
import { PersonDetails } from "@/components/people/person-details";
import { Button } from "@/components/ui/button";
import PageLayout from "@/pages/page-layout";
import { Edit } from "lucide-react";
import { Link, useParams } from "react-router-dom";

export default function PersonPage() {
  const { id } = useParams();

  const headerProps: PageHeaderProps = {
    title: "Person Details",
    backLink: "/groups",
    actions: (
      <Button variant="ghost" size="icon" asChild>
        <Link to={`/people/${id}/edit`}>
          <Edit className="h-5 w-5" />
        </Link>
      </Button>
    ),
  };

  return (
    <PageLayout headerProps={headerProps}>
      {id && <PersonDetails id={id} />}
    </PageLayout>
  );
}
