import { type PageHeaderProps } from "@/components/page-header";
import { ProfileForm } from "@/components/settings/profile-form";
import PageLayout from "@/pages/page-layout";

const headerProps: PageHeaderProps = {
  title: "Profile",
  backLink: "/settings",
};

export default function ProfilePage() {
  return (
    <PageLayout headerProps={headerProps}>
      <ProfileForm />
    </PageLayout>
  );
}
