import { type PageHeaderProps } from "@/components/page-header";
import { CurrenciesList } from "@/components/currencies/currencies-list";
import PageLayout from "../page-layout";

const headerProps: PageHeaderProps = {
  title: "Currencies",
  backLink: "/extra",
};

export default function CurrenciesPage() {
  return (
    <PageLayout headerProps={headerProps}>
      <CurrenciesList />
    </PageLayout>
  );
}
