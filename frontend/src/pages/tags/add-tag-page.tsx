import { type PageHeaderProps } from "@/components/page-header";
import { AddTagForm } from "@/components/tags/add-tag-form";
import PageLayout from "@/pages/page-layout";

const headerProps: PageHeaderProps = {
  title: "Add Tag",
  backLink: "/tags",
};

export default function AddTagPage() {
  return (
    <PageLayout headerProps={headerProps}>
      <AddTagForm />
    </PageLayout>
  );
}
