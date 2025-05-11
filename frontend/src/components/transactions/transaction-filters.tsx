import { useState } from "react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Calendar } from "@/components/ui/calendar";
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
  DialogFooter,
  DialogClose,
} from "@/components/ui/dialog";
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from "@/components/ui/popover";
import { Filter, Search, SearchX } from "lucide-react";
import { format } from "date-fns";
import { cn } from "@/lib/utils";
import { Checkbox } from "@/components/ui/checkbox";

// Mock data - would come from API in real app
const accounts = [
  { id: "1", name: "Cash", icon: "💵" },
  { id: "2", name: "Bank", icon: "🏦" },
  { id: "3", name: "Credit Card", icon: "💳" },
];

const categories = [
  { id: "1", name: "Food", icon: "🍔" },
  { id: "2", name: "Transport", icon: "🚗" },
  { id: "3", name: "Entertainment", icon: "🎬" },
  { id: "4", name: "Income", icon: "💰" },
];

const tags = [
  { id: "1", name: "Essentials", icon: "🛒" },
  { id: "2", name: "Work", icon: "💼" },
  { id: "3", name: "Dining", icon: "🍽️" },
  { id: "4", name: "Car", icon: "⛽" },
  { id: "5", name: "Leisure", icon: "🎭" },
];

interface TransactionFiltersProps {
  setShowSearchBar: (show: boolean) => void;
  showSearchBar: boolean;
  onFilter: (filters: any) => void;
}

export function TransactionFilters({
  setShowSearchBar,
  showSearchBar,
  onFilter,
}: TransactionFiltersProps) {
  const [dateRange, setDateRange] = useState<{
    from: Date | undefined;
    to: Date | undefined;
  }>({
    from: undefined,
    to: undefined,
  });
  const [selectedAccounts, setSelectedAccounts] = useState<string[]>([]);
  const [selectedCategories, setSelectedCategories] = useState<string[]>([]);
  const [selectedTags, setSelectedTags] = useState<string[]>([]);
  const [amountRange, setAmountRange] = useState<{ min: string; max: string }>({
    min: "",
    max: "",
  });

  const handleFilter = () => {
    onFilter({
      dateRange,
      accounts: selectedAccounts,
      categories: selectedCategories,
      tags: selectedTags,
      amountRange,
    });
  };

  const handleResetFilters = () => {
    setDateRange({ from: undefined, to: undefined });
    setSelectedAccounts([]);
    setSelectedCategories([]);
    setSelectedTags([]);
    setAmountRange({ min: "", max: "" });
  };

  const toggleAccount = (id: string) => {
    setSelectedAccounts((prev) =>
      prev.includes(id) ? prev.filter((item) => item !== id) : [...prev, id]
    );
  };

  const toggleCategory = (id: string) => {
    setSelectedCategories((prev) =>
      prev.includes(id) ? prev.filter((item) => item !== id) : [...prev, id]
    );
  };

  const toggleTag = (id: string) => {
    setSelectedTags((prev) =>
      prev.includes(id) ? prev.filter((item) => item !== id) : [...prev, id]
    );
  };

  return (
    <div className="flex gap-2">
      <>
        {!showSearchBar ? (
          <Button
            variant="ghost"
            size="icon"
            onClick={() => setShowSearchBar(true)}
          >
            <Search className="h-5 w-5" />
          </Button>
        ) : (
          <Button
            variant="ghost"
            size="icon"
            onClick={() => setShowSearchBar(false)}
          >
            <SearchX className="h-5 w-5" />
          </Button>
        )}

        <Dialog>
          <DialogTrigger asChild>
            <Button variant="ghost" size="icon">
              <Filter className="h-5 w-5" />
            </Button>
          </DialogTrigger>
          <DialogContent className="sm:max-w-[425px]">
            <DialogHeader>
              <DialogTitle>Filter Transactions</DialogTitle>
            </DialogHeader>
            <div className="grid gap-4 py-4">
              <div className="space-y-2">
                <Label>Date Range</Label>
                <Popover>
                  <PopoverTrigger asChild>
                    <Button
                      variant="outline"
                      className={cn(
                        "w-full justify-start text-left font-normal",
                        !dateRange.from && "text-muted-foreground"
                      )}
                    >
                      {dateRange.from ? (
                        dateRange.to ? (
                          <>
                            {format(dateRange.from, "LLL dd, y")} -{" "}
                            {format(dateRange.to, "LLL dd, y")}
                          </>
                        ) : (
                          format(dateRange.from, "LLL dd, y")
                        )
                      ) : (
                        "Select date range"
                      )}
                    </Button>
                  </PopoverTrigger>
                  <PopoverContent className="w-auto p-0" align="start">
                    <Calendar
                      mode="range"
                      selected={dateRange}
                      onSelect={setDateRange as any}
                      initialFocus
                    />
                  </PopoverContent>
                </Popover>
              </div>

              <div className="space-y-2">
                <Label>Amount Range</Label>
                <div className="flex gap-2">
                  <Input
                    placeholder="Min"
                    type="number"
                    value={amountRange.min}
                    onChange={(e) =>
                      setAmountRange({ ...amountRange, min: e.target.value })
                    }
                  />
                  <Input
                    placeholder="Max"
                    type="number"
                    value={amountRange.max}
                    onChange={(e) =>
                      setAmountRange({ ...amountRange, max: e.target.value })
                    }
                  />
                </div>
              </div>

              <div className="space-y-2">
                <Label>Accounts</Label>
                <div className="grid grid-cols-2 gap-2">
                  {accounts.map((account) => (
                    <div
                      key={account.id}
                      className="flex items-center space-x-2"
                    >
                      <Checkbox
                        id={`account-${account.id}`}
                        checked={selectedAccounts.includes(account.id)}
                        onCheckedChange={() => toggleAccount(account.id)}
                      />
                      <Label
                        htmlFor={`account-${account.id}`}
                        className="flex items-center"
                      >
                        <span className="mr-1">{account.icon}</span>{" "}
                        {account.name}
                      </Label>
                    </div>
                  ))}
                </div>
              </div>

              <div className="space-y-2">
                <Label>Categories</Label>
                <div className="grid grid-cols-2 gap-2">
                  {categories.map((category) => (
                    <div
                      key={category.id}
                      className="flex items-center space-x-2"
                    >
                      <Checkbox
                        id={`category-${category.id}`}
                        checked={selectedCategories.includes(category.id)}
                        onCheckedChange={() => toggleCategory(category.id)}
                      />
                      <Label
                        htmlFor={`category-${category.id}`}
                        className="flex items-center"
                      >
                        <span className="mr-1">{category.icon}</span>{" "}
                        {category.name}
                      </Label>
                    </div>
                  ))}
                </div>
              </div>

              <div className="space-y-2">
                <Label>Tags</Label>
                <div className="grid grid-cols-2 gap-2">
                  {tags.map((tag) => (
                    <div key={tag.id} className="flex items-center space-x-2">
                      <Checkbox
                        id={`tag-${tag.id}`}
                        checked={selectedTags.includes(tag.id)}
                        onCheckedChange={() => toggleTag(tag.id)}
                      />
                      <Label
                        htmlFor={`tag-${tag.id}`}
                        className="flex items-center"
                      >
                        <span className="mr-1">{tag.icon}</span> {tag.name}
                      </Label>
                    </div>
                  ))}
                </div>
              </div>
            </div>
            <DialogFooter className="flex flex-col sm:flex-row gap-2">
              <Button
                variant="outline"
                onClick={handleResetFilters}
                className="w-full sm:w-auto"
              >
                Reset
              </Button>
              <DialogClose asChild>
                <Button onClick={handleFilter} className="w-full sm:w-auto">
                  Apply Filters
                </Button>
              </DialogClose>
            </DialogFooter>
          </DialogContent>
        </Dialog>
      </>
    </div>
  );
}
