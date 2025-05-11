import { TabNavigation } from "@/components/tab-navigation";
import { Outlet } from "react-router-dom";

export default function RootLayout() {
  return (
    <div className="flex flex-col min-h-screen">
      <main className="flex-1 pb-16 md:pb-0 md:pl-64">
        <Outlet />
      </main>
      <TabNavigation className="fixed bottom-0 left-0 right-0 z-50 md:left-0 md:top-0 md:bottom-0 md:w-64 md:h-screen" />
    </div>
  );
}
