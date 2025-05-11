import { PageHeader, type PageHeaderProps } from "@/components/page-header";
import { cn } from "@/lib/utils";
import type { ReactNode } from "react";

interface PageLayoutProps {
  children: ReactNode;
  headerProps: PageHeaderProps;
  className?: string;
}

export default function PageLayout({
  headerProps,
  children,
  className,
}: PageLayoutProps) {
  return (
    <>
      <PageHeader {...headerProps} />
      <div className={cn("container max-w-md mx-auto px-4 py-4", className)}>
        {children}
      </div>
    </>
  );
}
