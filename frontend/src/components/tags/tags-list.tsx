"use client";

import { Card } from "@/components/ui/card";
import { motion } from "framer-motion";
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
const tags = [
  { id: 1, name: "Essentials", icon: "🛒", count: 18 },
  { id: 2, name: "Work", icon: "💼", count: 7 },
  { id: 3, name: "Dining", icon: "🍽️", count: 12 },
  { id: 4, name: "Car", icon: "⛽", count: 5 },
  { id: 5, name: "Leisure", icon: "🎭", count: 9 },
  { id: 6, name: "Monthly", icon: "📅", count: 6 },
  { id: 7, name: "Family", icon: "👨‍👩‍👧‍👦", count: 4 },
  { id: 8, name: "Travel", icon: "✈️", count: 3 },
  { id: 9, name: "Not Specified", icon: "🏷️", count: 2 },
];

export function TagsList() {
  const [hoveredTag, setHoveredTag] = useState<number | null>(null);
  const [tagToDelete, setTagToDelete] = useState<{
    id: number;
    name: string;
  } | null>(null);

  const handleDeleteClick = (id: number, name: string) => {
    setTagToDelete({ id, name });
  };

  const handleDelete = () => {
    // In a real app, we would send this data to the API
    console.log("Deleting tag:", tagToDelete);
    setTagToDelete(null);
  };

  return (
    <div className="grid grid-cols-2 gap-3">
      {tags.map((tag) => (
        <motion.div
          key={tag.id}
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: (tag.id % 10) * 0.05 }}
          className="relative group"
          onMouseEnter={() => setHoveredTag(tag.id)}
          onMouseLeave={() => setHoveredTag(null)}
        >
          <Link to={`/tags/${tag.id}`}>
            <Card className="p-4 hover:bg-accent transition-colors">
              <div className="flex flex-col items-center text-center">
                <CustomAvatar icon={tag.icon} className="h-12 w-12 mb-2" />
                <div className="font-medium">{tag.name}</div>
                <div className="text-xs text-muted-foreground">
                  {tag.count} transactions
                </div>
              </div>
            </Card>
          </Link>

          {hoveredTag === tag.id && tag.name !== "Not Specified" && (
            <div className="absolute top-2 right-2 flex items-center gap-1 bg-background/80 rounded p-1">
              <Button variant="ghost" size="icon" className="h-7 w-7" asChild>
                <Link to={`/tags/${tag.id}/edit`}>
                  <Edit className="h-3.5 w-3.5" />
                </Link>
              </Button>
              <Button
                variant="ghost"
                size="icon"
                className="h-7 w-7"
                onClick={(e) => {
                  e.preventDefault();
                  handleDeleteClick(tag.id, tag.name);
                }}
              >
                <Trash className="h-3.5 w-3.5" />
              </Button>
            </div>
          )}
        </motion.div>
      ))}

      <AlertDialog
        open={!!tagToDelete}
        onOpenChange={(open) => !open && setTagToDelete(null)}
      >
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle>Are you sure?</AlertDialogTitle>
            <AlertDialogDescription>
              Delete this tag will change the tag of all the transactions
              associated with this tag to 'Not Specified'
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
