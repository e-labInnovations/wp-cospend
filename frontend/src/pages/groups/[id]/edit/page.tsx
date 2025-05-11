import { type PageHeaderProps } from "@/components/page-header";
import { EditGroupForm } from "@/components/groups/edit-group-form";
import PageLayout from "@/pages/page-layout";
import { useParams } from "react-router-dom";

export default function EditGroupPage() {
  const { id } = useParams();
  const headerProps: PageHeaderProps = {
    title: "Edit Group",
    backLink: `/groups/${id}`,
  };

  return (
    <PageLayout headerProps={headerProps}>
      {id && <EditGroupForm id={id} />}
    </PageLayout>
  );
}
