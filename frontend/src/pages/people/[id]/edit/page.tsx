import { type PageHeaderProps } from "@/components/page-header";
import { EditPersonForm } from "@/components/people/edit-person-form";
import PageLayout from "@/pages/page-layout";
import { useParams } from "react-router-dom";

export default function EditPersonPage() {
  const { id } = useParams();
  const headerProps: PageHeaderProps = {
    title: "Edit Person",
    backLink: `/people/${id}`,
  };

  return (
    <PageLayout headerProps={headerProps}>
      {id && <EditPersonForm id={id} />}
    </PageLayout>
  );
}
