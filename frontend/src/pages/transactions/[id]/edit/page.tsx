import { type PageHeaderProps } from "@/components/page-header";
import { EditTransactionForm } from "@/components/transactions/edit-transaction-form";
import PageLayout from "@/pages/page-layout";
import { useParams } from "react-router-dom";
export default function EditTransactionPage() {
  const { id } = useParams();
  const headerProps: PageHeaderProps = {
    title: "Edit Transaction",
    backLink: `/transactions/${id}`,
  };

  return (
    <PageLayout headerProps={headerProps}>
      {id && <EditTransactionForm id={id} />}
    </PageLayout>
  );
}
