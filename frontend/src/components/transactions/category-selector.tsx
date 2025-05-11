import { useState } from "react";
import { Button } from "@/components/ui/button";
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/components/ui/dialog";
import { Input } from "@/components/ui/input";
import { Search } from "lucide-react";
import { CustomAvatar } from "@/components/ui/custom-avatar";

// Mock data - would come from API in real app
const mainCategories = [
  { id: "food", name: "Food & Drinks", icon: "🍔" },
  { id: "transport", name: "Transportation", icon: "🚗" },
  { id: "entertainment", name: "Entertainment", icon: "🎬" },
  { id: "shopping", name: "Shopping", icon: "🛍️" },
  { id: "housing", name: "Housing", icon: "🏠" },
  { id: "health", name: "Health", icon: "🏥" },
  { id: "education", name: "Education", icon: "🎓" },
  { id: "other", name: "Other", icon: "📦" },
];

const subCategories = {
  food: [
    { id: "restaurant", name: "Restaurant", icon: "🍽️" },
    { id: "groceries", name: "Groceries", icon: "🛒" },
    { id: "cafe", name: "Cafe", icon: "☕" },
    { id: "fastfood", name: "Fast Food", icon: "🍟" },
  ],
  transport: [
    { id: "gas", name: "Gas", icon: "⛽" },
    { id: "parking", name: "Parking", icon: "🅿️" },
    { id: "public", name: "Public Transport", icon: "🚌" },
    { id: "taxi", name: "Taxi", icon: "🚕" },
  ],
  entertainment: [
    { id: "movies", name: "Movies", icon: "🎞️" },
    { id: "games", name: "Games", icon: "🎮" },
    { id: "concerts", name: "Concerts", icon: "🎵" },
    { id: "sports", name: "Sports", icon: "⚽" },
  ],
  shopping: [
    { id: "clothes", name: "Clothes", icon: "👕" },
    { id: "electronics", name: "Electronics", icon: "📱" },
    { id: "gifts", name: "Gifts", icon: "🎁" },
    { id: "home", name: "Home Goods", icon: "🏡" },
  ],
  housing: [
    { id: "rent", name: "Rent", icon: "🏢" },
    { id: "mortgage", name: "Mortgage", icon: "🏦" },
    { id: "utilities", name: "Utilities", icon: "💡" },
    { id: "maintenance", name: "Maintenance", icon: "🔧" },
  ],
  health: [
    { id: "doctor", name: "Doctor", icon: "👨‍⚕️" },
    { id: "pharmacy", name: "Pharmacy", icon: "💊" },
    { id: "fitness", name: "Fitness", icon: "🏋️" },
    { id: "insurance", name: "Insurance", icon: "📋" },
  ],
  education: [
    { id: "tuition", name: "Tuition", icon: "🏫" },
    { id: "books", name: "Books", icon: "📚" },
    { id: "courses", name: "Courses", icon: "📝" },
    { id: "supplies", name: "Supplies", icon: "✏️" },
  ],
  other: [
    { id: "income", name: "Income", icon: "💰" },
    { id: "gifts", name: "Gifts", icon: "🎁" },
    { id: "charity", name: "Charity", icon: "🤲" },
    { id: "misc", name: "Miscellaneous", icon: "🔮" },
  ],
};

interface CategorySelectorProps {
  value: { id: string; name: string; icon: string };
  onChange: (category: { id: string; name: string; icon: string }) => void;
}

export function CategorySelector({ value, onChange }: CategorySelectorProps) {
  const [open, setOpen] = useState(false);
  const [searchQuery, setSearchQuery] = useState("");
  const [selectedMainCategory, setSelectedMainCategory] = useState<
    string | null
  >(null);

  const handleCategorySelect = (category: {
    id: string;
    name: string;
    icon: string;
  }) => {
    onChange(category);
    setOpen(false);
  };

  // Filter categories based on search query
  const filteredMainCategories = searchQuery
    ? mainCategories.filter((cat) =>
        cat.name.toLowerCase().includes(searchQuery.toLowerCase())
      )
    : mainCategories;

  const getFilteredSubCategories = (mainCatId: string) => {
    const subs = subCategories[mainCatId as keyof typeof subCategories] || [];
    return searchQuery
      ? subs.filter((cat) =>
          cat.name.toLowerCase().includes(searchQuery.toLowerCase())
        )
      : subs;
  };

  // Get all subcategories for search results
  const getAllFilteredSubCategories = () => {
    if (!searchQuery) return [];

    return Object.values(subCategories)
      .flat()
      .filter((cat) =>
        cat.name.toLowerCase().includes(searchQuery.toLowerCase())
      );
  };

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogTrigger asChild>
        <Button variant="outline" className="w-full justify-start">
          <CustomAvatar icon={value.icon} className="h-6 w-6 mr-2" />
          <span>{value.name}</span>
        </Button>
      </DialogTrigger>
      <DialogContent className="sm:max-w-md">
        <DialogHeader>
          <DialogTitle>Select Category</DialogTitle>
        </DialogHeader>

        <div className="space-y-4">
          <div className="relative">
            <Search className="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
            <Input
              type="text"
              placeholder="Search categories..."
              className="pl-8"
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
            />
          </div>

          <div className="max-h-[400px] overflow-y-auto space-y-4">
            {/* Search results from subcategories */}
            {searchQuery && getAllFilteredSubCategories().length > 0 && (
              <div className="space-y-2">
                <h3 className="text-sm font-medium">Search Results</h3>
                <div className="grid grid-cols-2 gap-2">
                  {getAllFilteredSubCategories().map((category) => (
                    <Button
                      key={category.id}
                      variant="outline"
                      className="justify-start h-auto py-2"
                      onClick={() => handleCategorySelect(category)}
                    >
                      <CustomAvatar
                        icon={category.icon}
                        className="h-6 w-6 mr-2"
                      />
                      <span className="text-sm">{category.name}</span>
                    </Button>
                  ))}
                </div>
              </div>
            )}

            {/* Main categories */}
            {filteredMainCategories.map((category) => (
              <div key={category.id} className="space-y-2">
                <Button
                  variant="ghost"
                  className="w-full justify-start font-medium"
                  onClick={() =>
                    setSelectedMainCategory(
                      selectedMainCategory === category.id ? null : category.id
                    )
                  }
                >
                  <CustomAvatar icon={category.icon} className="h-6 w-6 mr-2" />
                  <span>{category.name}</span>
                </Button>

                {/* Subcategories */}
                {(selectedMainCategory === category.id || searchQuery) && (
                  <div className="grid grid-cols-2 gap-2 ml-6">
                    {getFilteredSubCategories(category.id).map(
                      (subCategory) => (
                        <Button
                          key={subCategory.id}
                          variant="outline"
                          className="justify-start h-auto py-2"
                          onClick={() => handleCategorySelect(subCategory)}
                        >
                          <CustomAvatar
                            icon={subCategory.icon}
                            className="h-6 w-6 mr-2"
                          />
                          <span className="text-sm">{subCategory.name}</span>
                        </Button>
                      )
                    )}
                  </div>
                )}
              </div>
            ))}
          </div>
        </div>
      </DialogContent>
    </Dialog>
  );
}
