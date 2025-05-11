import { type PageHeaderProps } from "@/components/page-header";
import { ExtraItems } from "@/components/extra/extra-items";
import PageLayout from "../page-layout";

const headerProps: PageHeaderProps = {
  title: "Extra",
};

export default function ExtraPage() {
  return (
    <PageLayout headerProps={headerProps}>
      <ExtraItems />
    </PageLayout>
  );
}
