import { type PageHeaderProps } from "@/components/page-header";
import { AddGroupForm } from "@/components/groups/add-group-form";
import PageLayout from "@/pages/page-layout";

const headerProps: PageHeaderProps = {
  title: "Add Group",
  backLink: "/groups",
};

export default function AddGroupPage() {
  return (
    <PageLayout headerProps={headerProps}>
      <AddGroupForm />
    </PageLayout>
  );
}
