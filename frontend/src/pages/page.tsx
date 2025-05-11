import { AccountsSection } from "@/components/home/accounts-section";
import { ExpensesThisWeekSection } from "@/components/home/expenses-this-week-section";
import { MonthlySummarySection } from "@/components/home/monthly-summary-section";
import { ExpensesByCategorySection } from "@/components/home/expenses-by-category-section";
import { ExpensesByTagSection } from "@/components/home/expenses-by-tag-section";
import PageLayout from "./page-layout";
import type { PageHeaderProps } from "@/components/page-header";

const headerProps: PageHeaderProps = {
  title: "Home",
};

export default function HomePage() {
  return (
    <PageLayout headerProps={headerProps} className="space-y-8">
      <AccountsSection />
      <ExpensesThisWeekSection />
      <MonthlySummarySection />
      <ExpensesByCategorySection />
      <ExpensesByTagSection />
    </PageLayout>
  );
}
