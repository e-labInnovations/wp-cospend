import { CategoriesList } from "@/components/categories/categories-list";
import { Button } from "@/components/ui/button";
import { Plus } from "lucide-react";
import { Link } from "react-router-dom";
import PageLayout from "../page-layout";
import type { PageHeaderProps } from "@/components/page-header";

const headerProps: PageHeaderProps = {
  title: "Categories",
  backLink: "/extra",
  actions: (
    <Button size="icon" asChild>
      <Link to="/categories/add">
        <Plus className="h-5 w-5" />
      </Link>
    </Button>
  ),
};

export default function CategoriesPage() {
  return (
    <PageLayout headerProps={headerProps}>
      <CategoriesList />
    </PageLayout>
  );
}
