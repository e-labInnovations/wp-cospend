import type React from "react";

import { useState, useRef, useEffect } from "react";
import { Badge } from "@/components/ui/badge";
import { Input } from "@/components/ui/input";
import { X } from "lucide-react";
import { Button } from "@/components/ui/button";

// Mock data - would come from API in real app
const suggestedTags = [
  { id: "1", name: "Essentials", icon: "🛒" },
  { id: "2", name: "Work", icon: "💼" },
  { id: "3", name: "Dining", icon: "🍽️" },
  { id: "4", name: "Car", icon: "⛽" },
  { id: "5", name: "Leisure", icon: "🎭" },
  { id: "6", name: "Monthly", icon: "📅" },
  { id: "7", name: "Family", icon: "👨‍👩‍👧‍👦" },
  { id: "8", name: "Travel", icon: "✈️" },
  { id: "9", name: "Health", icon: "🏥" },
  { id: "10", name: "Education", icon: "🎓" },
];

interface Tag {
  id: string;
  name: string;
  icon: string;
}

interface TagInputProps {
  value: Tag[];
  onChange: (tags: Tag[]) => void;
}

export function TagInput({ value, onChange }: TagInputProps) {
  const [inputValue, setInputValue] = useState("");
  const [suggestions, setSuggestions] = useState<Tag[]>([]);
  const [showSuggestions, setShowSuggestions] = useState(false);
  const inputRef = useRef<HTMLInputElement>(null);
  const suggestionsRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    // Filter suggestions based on input value
    if (inputValue.trim()) {
      const filtered = suggestedTags.filter(
        (tag) =>
          tag.name.toLowerCase().includes(inputValue.toLowerCase()) &&
          !value.some((t) => t.id === tag.id)
      );
      setSuggestions(filtered);
      setShowSuggestions(filtered.length > 0);
    } else {
      setSuggestions([]);
      setShowSuggestions(false);
    }
  }, [inputValue, value]);

  useEffect(() => {
    // Close suggestions when clicking outside
    const handleClickOutside = (event: MouseEvent) => {
      if (
        suggestionsRef.current &&
        !suggestionsRef.current.contains(event.target as Node) &&
        inputRef.current &&
        !inputRef.current.contains(event.target as Node)
      ) {
        setShowSuggestions(false);
      }
    };

    document.addEventListener("mousedown", handleClickOutside);
    return () => {
      document.removeEventListener("mousedown", handleClickOutside);
    };
  }, []);

  const addTag = (tag: Tag) => {
    if (!value.some((t) => t.id === tag.id)) {
      onChange([...value, tag]);
    }
    setInputValue("");
    setShowSuggestions(false);
    inputRef.current?.focus();
  };

  const removeTag = (tagId: string) => {
    onChange(value.filter((tag) => tag.id !== tagId));
  };

  const handleKeyDown = (e: React.KeyboardEvent<HTMLInputElement>) => {
    if (e.key === "Enter" && inputValue.trim()) {
      e.preventDefault();

      // Check if the tag already exists in suggestions
      const existingTag = suggestedTags.find(
        (tag) => tag.name.toLowerCase() === inputValue.toLowerCase()
      );

      if (existingTag) {
        addTag(existingTag);
      } else {
        // Create a new tag
        const newTag: Tag = {
          id: `new-${Date.now()}`,
          name: inputValue.trim(),
          icon: "🏷️", // Default icon for new tags
        };
        addTag(newTag);
      }
    } else if (e.key === "Backspace" && !inputValue && value.length > 0) {
      // Remove the last tag when backspace is pressed and input is empty
      removeTag(value[value.length - 1].id);
    }
  };

  return (
    <div className="space-y-2">
      <div className="flex flex-wrap gap-2 mb-2">
        {value.map((tag) => (
          <Badge key={tag.id} variant="secondary" className="gap-1 px-2 py-1">
            <span className="mr-1">{tag.icon}</span>
            {tag.name}
            <Button
              variant="ghost"
              size="icon"
              className="h-4 w-4 p-0 ml-1"
              onClick={() => removeTag(tag.id)}
            >
              <X className="h-3 w-3" />
              <span className="sr-only">Remove</span>
            </Button>
          </Badge>
        ))}
      </div>

      <div className="relative">
        <Input
          ref={inputRef}
          type="text"
          placeholder="Add tags..."
          value={inputValue}
          onChange={(e) => setInputValue(e.target.value)}
          onKeyDown={handleKeyDown}
          onFocus={() =>
            inputValue && setSuggestions.length > 0 && setShowSuggestions(true)
          }
        />

        {showSuggestions && (
          <div
            ref={suggestionsRef}
            className="absolute z-10 w-full mt-1 bg-background border rounded-md shadow-lg max-h-60 overflow-auto"
          >
            {suggestions.map((tag) => (
              <div
                key={tag.id}
                className="px-3 py-2 hover:bg-accent cursor-pointer flex items-center"
                onClick={() => addTag(tag)}
              >
                <span className="mr-2">{tag.icon}</span>
                <span>{tag.name}</span>
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  );
}
