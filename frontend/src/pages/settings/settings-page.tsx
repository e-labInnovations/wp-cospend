import { type PageHeaderProps } from "@/components/page-header";
import { SettingsItems } from "@/components/settings/settings-items";
import PageLayout from "@/pages/page-layout";

const headerProps: PageHeaderProps = {
  title: "Settings",
};

export default function SettingsPage() {
  return (
    <PageLayout headerProps={headerProps}>
      <SettingsItems />
    </PageLayout>
  );
}
