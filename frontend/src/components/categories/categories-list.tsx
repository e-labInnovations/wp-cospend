"use client";

import { Card } from "@/components/ui/card";
import { Link } from "react-router-dom";
import { CustomAvatar } from "@/components/ui/custom-avatar";
import { Edit, Trash } from "lucide-react";
import { Button } from "@/components/ui/button";
import { useState } from "react";
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from "@/components/ui/alert-dialog";

// Mock data - would come from API in real app
const mainCategories = [
  {
    id: "food",
    name: "Food & Drinks",
    icon: "🍔",
    subcategories: [
      { id: "restaurant", name: "Restaurant", icon: "🍽️", count: 8 },
      { id: "groceries", name: "Groceries", icon: "🛒", count: 12 },
      { id: "cafe", name: "Cafe", icon: "☕", count: 4 },
    ],
  },
  {
    id: "transport",
    name: "Transportation",
    icon: "🚗",
    subcategories: [
      { id: "gas", name: "Gas", icon: "⛽", count: 5 },
      { id: "parking", name: "Parking", icon: "🅿️", count: 3 },
      { id: "public", name: "Public Transport", icon: "🚌", count: 7 },
    ],
  },
  {
    id: "entertainment",
    name: "Entertainment",
    icon: "🎬",
    subcategories: [
      { id: "movies", name: "Movies", icon: "🎞️", count: 2 },
      { id: "games", name: "Games", icon: "🎮", count: 3 },
      { id: "concerts", name: "Concerts", icon: "🎵", count: 1 },
    ],
  },
  {
    id: "uncategorized",
    name: "Uncategorized",
    icon: "📦",
    subcategories: [
      { id: "misc", name: "Miscellaneous", icon: "🔮", count: 2 },
    ],
  },
];

export function CategoriesList() {
  const [hoveredCategory, setHoveredCategory] = useState<string | null>(null);
  const [categoryToDelete, setCategoryToDelete] = useState<{
    id: string;
    name: string;
    isMain: boolean;
  } | null>(null);

  const handleDeleteClick = (id: string, name: string, isMain: boolean) => {
    setCategoryToDelete({ id, name, isMain });
  };

  const handleDelete = () => {
    // In a real app, we would send this data to the API
    console.log("Deleting category:", categoryToDelete);
    setCategoryToDelete(null);
  };

  return (
    <div className="space-y-8">
      {mainCategories.map((category) => (
        <div key={category.id} className="space-y-3">
          <div
            className="flex items-center justify-between"
            onMouseEnter={() => setHoveredCategory(`main-${category.id}`)}
            onMouseLeave={() => setHoveredCategory(null)}
          >
            <div className="flex items-center">
              <CustomAvatar icon={category.icon} className="h-8 w-8 mr-2" />
              <h3 className="text-lg font-medium">{category.name}</h3>
            </div>

            {hoveredCategory === `main-${category.id}` &&
              category.id !== "uncategorized" && (
                <div className="flex items-center gap-1">
                  <Button variant="ghost" size="icon" asChild>
                    <Link to={`/categories/${category.id}/edit`}>
                      <Edit className="h-4 w-4" />
                    </Link>
                  </Button>

                  {category.subcategories.length === 0 && (
                    <Button
                      variant="ghost"
                      size="icon"
                      onClick={() =>
                        handleDeleteClick(category.id, category.name, true)
                      }
                    >
                      <Trash className="h-4 w-4" />
                    </Button>
                  )}
                </div>
              )}
          </div>

          <div className="grid grid-cols-2 gap-3">
            {category.subcategories.map((subCategory) => (
              <div
                key={subCategory.id}
                className="relative group"
                onMouseEnter={() => setHoveredCategory(subCategory.id)}
                onMouseLeave={() => setHoveredCategory(null)}
              >
                <Link to={`/categories/${subCategory.id}`}>
                  <Card className="p-4 hover:bg-accent transition-colors">
                    <div className="flex items-center">
                      <CustomAvatar
                        icon={subCategory.icon}
                        className="h-10 w-10 mr-3"
                      />
                      <div className="flex-1">
                        <div className="font-medium">{subCategory.name}</div>
                        <div className="text-xs text-muted-foreground">
                          {subCategory.count} transactions
                        </div>
                      </div>
                    </div>
                  </Card>
                </Link>

                {hoveredCategory === subCategory.id &&
                  subCategory.id !== "misc" && (
                    <div className="absolute top-2 right-2 flex items-center gap-1 bg-background/80 rounded p-1">
                      <Button
                        variant="ghost"
                        size="icon"
                        className="h-7 w-7"
                        asChild
                      >
                        <Link to={`/categories/${subCategory.id}/edit`}>
                          <Edit className="h-3.5 w-3.5" />
                        </Link>
                      </Button>
                      <Button
                        variant="ghost"
                        size="icon"
                        className="h-7 w-7"
                        onClick={() =>
                          handleDeleteClick(
                            subCategory.id,
                            subCategory.name,
                            false
                          )
                        }
                      >
                        <Trash className="h-3.5 w-3.5" />
                      </Button>
                    </div>
                  )}
              </div>
            ))}
          </div>
        </div>
      ))}

      <AlertDialog
        open={!!categoryToDelete}
        onOpenChange={(open) => !open && setCategoryToDelete(null)}
      >
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle>Are you sure?</AlertDialogTitle>
            <AlertDialogDescription>
              {categoryToDelete?.isMain
                ? "This will delete the main category and all its subcategories."
                : "Delete this category will change the category of all the transactions associated with this category to 'Uncategorized'"}
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter>
            <AlertDialogCancel>Cancel</AlertDialogCancel>
            <AlertDialogAction
              onClick={handleDelete}
              className="bg-destructive text-destructive-foreground"
            >
              Delete
            </AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>
    </div>
  );
}
