import { type PageHeaderProps } from "@/components/page-header";
import { AddTransactionForm } from "@/components/transactions/add-transaction-form";
import PageLayout from "../page-layout";

const headerProps: PageHeaderProps = {
  title: "Add Transaction",
};

export default function AddTransactionPage() {
  return (
    <PageLayout headerProps={headerProps}>
      <AddTransactionForm />
    </PageLayout>
  );
}
