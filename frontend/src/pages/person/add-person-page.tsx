import { type PageHeaderProps } from "@/components/page-header";
import { AddPersonForm } from "@/components/people/add-person-form";
import PageLayout from "@/pages/page-layout";

const headerProps: PageHeaderProps = {
  title: "Add Person",
  backLink: "/people",
};

export default function AddPersonPage() {
  return (
    <PageLayout headerProps={headerProps}>
      <AddPersonForm />
    </PageLayout>
  );
}
