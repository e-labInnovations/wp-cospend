import { type PageHeaderProps } from "@/components/page-header";
import { TagsList } from "@/components/tags/tags-list";
import { Button } from "@/components/ui/button";
import { Plus } from "lucide-react";
import { Link } from "react-router-dom";
import PageLayout from "../page-layout";

const headerProps: PageHeaderProps = {
  title: "Tags",
  backLink: "/extra",
  actions: (
    <Button size="icon" asChild>
      <Link to="/tags/add">
        <Plus className="h-5 w-5" />
      </Link>
    </Button>
  ),
};

export default function TagsPage() {
  return (
    <PageLayout headerProps={headerProps}>
      <TagsList />
    </PageLayout>
  );
}
