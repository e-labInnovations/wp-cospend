import { type PageHeaderProps } from "@/components/page-header";
import { AddAccountForm } from "@/components/accounts/add-account-form";
import PageLayout from "@/pages/page-layout";

const headerProps: PageHeaderProps = {
  title: "Add Account",
  backLink: "/accounts",
};
export default function AddAccountPage() {
  return (
    <PageLayout headerProps={headerProps}>
      <AddAccountForm />
    </PageLayout>
  );
}
