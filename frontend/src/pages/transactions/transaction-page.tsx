import { type PageHeaderProps } from "@/components/page-header";
import { TransactionDetails } from "@/components/transactions/transaction-details";
import { Button } from "@/components/ui/button";
import { ChevronLeft, Edit, Trash } from "lucide-react";
import { Link, useParams } from "react-router-dom";
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogTrigger,
} from "@/components/ui/alert-dialog";
import PageLayout from "@/pages/page-layout";

export default function TransactionPage() {
  const { id } = useParams();
  const headerProps: PageHeaderProps = {
    title: "Transaction Details",
    backLink: "/transactions",
    actions: (
      <>
        <Button variant="ghost" size="icon" asChild>
          <Link to={`/transactions/${id}/edit`}>
            <Edit className="h-5 w-5" />
          </Link>
        </Button>
        <AlertDialog>
          <AlertDialogTrigger asChild>
            <Button variant="ghost" size="icon">
              <Trash className="h-5 w-5" />
            </Button>
          </AlertDialogTrigger>
          <AlertDialogContent>
            <AlertDialogHeader>
              <AlertDialogTitle>Are you sure?</AlertDialogTitle>
              <AlertDialogDescription>
                This action cannot be undone. This will permanently delete the
                transaction.
              </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
              <AlertDialogCancel>Cancel</AlertDialogCancel>
              <AlertDialogAction className="bg-destructive text-destructive-foreground">
                Delete
              </AlertDialogAction>
            </AlertDialogFooter>
          </AlertDialogContent>
        </AlertDialog>
      </>
    ),
  };

  return (
    <PageLayout headerProps={headerProps}>
      {id && <TransactionDetails id={id} />}
    </PageLayout>
  );
}
