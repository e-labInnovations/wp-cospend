import { type PageHeaderProps } from "@/components/page-header";
import { TransactionsList } from "@/components/transactions/transactions-list";
import { TransactionFilters } from "@/components/transactions/transaction-filters";
import { useState } from "react";
import { SearchBar } from "@/components/search-bar";
import PageLayout from "../page-layout";

export default function TransactionsPage() {
  const [filters, setFilters] = useState<Array<{ id: string; label: string }>>(
    []
  );
  const [showSearchBar, setShowSearchBar] = useState(false);

  const handleFilter = (appliedFilters: any) => {
    console.log("Filters:", appliedFilters);
    // In a real app, we would update the transactions list based on the filters
  };
  const [activeFilters, setActiveFilters] = useState<
    Array<{ id: string; label: string }>
  >([]);

  const handleSearch = (query: string) => {
    console.log("Searching for:", query);
    // In a real app, we would filter the data based on the query
    if (query && !activeFilters.some((f) => f.id === `query-${query}`)) {
      setActiveFilters([
        ...activeFilters,
        { id: `query-${query}`, label: `Search: ${query}` },
      ]);
    }
  };

  const handleFilterRemove = (id: string) => {
    setActiveFilters(activeFilters.filter((filter) => filter.id !== id));
  };

  const headerProps: PageHeaderProps = {
    title: "Transactions",
    actions: (
      <TransactionFilters
        setShowSearchBar={setShowSearchBar}
        showSearchBar={showSearchBar}
        onFilter={handleFilter}
      />
    ),
  };

  return (
    <PageLayout headerProps={headerProps}>
      {showSearchBar && (
        <SearchBar
          onSearch={handleSearch}
          placeholder="Search transactions..."
          filters={activeFilters}
          onFilterRemove={handleFilterRemove}
        />
      )}

      {filters.length > 0 && (
        <div className="flex flex-wrap gap-2 mb-4">
          {filters.map((filter) => (
            <div
              key={filter.id}
              className="bg-muted text-sm rounded-full px-3 py-1 flex items-center"
            >
              {filter.label}
              <button
                className="ml-2 text-muted-foreground hover:text-foreground"
                onClick={() =>
                  setFilters(filters.filter((f) => f.id !== filter.id))
                }
              >
                ×
              </button>
            </div>
          ))}
        </div>
      )}

      <TransactionsList />
    </PageLayout>
  );
}
