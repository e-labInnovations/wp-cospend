import { type PageHeaderProps } from "@/components/page-header";
import { AddCategoryForm } from "@/components/categories/add-category-form";
import PageLayout from "@/pages/page-layout";

const headerProps: PageHeaderProps = {
  title: "Add Category",
  backLink: "/categories",
};

export default function AddCategoryPage() {
  return (
    <PageLayout headerProps={headerProps}>
      <AddCategoryForm />
    </PageLayout>
  );
}
