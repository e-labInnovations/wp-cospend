import type React from "react";

import { zodResolver } from "@hookform/resolvers/zod";
import { useForm } from "react-hook-form";
import { z } from "zod";
import { motion } from "framer-motion";
import { useState } from "react";

import { Button } from "@/components/ui/button";
import {
  Form,
  FormControl,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from "@/components/ui/form";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { Calendar } from "@/components/ui/calendar";
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from "@/components/ui/popover";
import { cn } from "@/lib/utils";
import { format } from "date-fns";
import { CalendarIcon } from "lucide-react";
import { FileUpload } from "./file-upload";
import { Label } from "@/components/ui/label";
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/components/ui/dialog";

const formSchema = z.object({
  title: z.string().min(2, {
    message: "Title must be at least 2 characters.",
  }),
  amount: z.string().refine((val) => !isNaN(Number(val)) && Number(val) !== 0, {
    message: "Amount must be a non-zero number.",
  }),
  date: z.date(),
  account: z.string({
    required_error: "Please select an account.",
  }),
  category: z.string({
    required_error: "Please select a category.",
  }),
  tags: z.string().optional(),
  note: z.string().optional(),
});

// Mock data - would come from API in real app
const accounts = [
  { id: "1", name: "Cash", icon: "💵" },
  { id: "2", name: "Bank", icon: "🏦" },
  { id: "3", name: "Credit Card", icon: "💳" },
];

const mainCategories = [
  { id: "food", name: "Food & Drinks", icon: "🍔" },
  { id: "transport", name: "Transportation", icon: "🚗" },
  { id: "entertainment", name: "Entertainment", icon: "🎬" },
  { id: "shopping", name: "Shopping", icon: "🛍️" },
  { id: "housing", name: "Housing", icon: "🏠" },
  { id: "other", name: "Other", icon: "📦" },
];

const subCategories = {
  food: [
    { id: "restaurant", name: "Restaurant", icon: "🍽️" },
    { id: "groceries", name: "Groceries", icon: "🛒" },
    { id: "cafe", name: "Cafe", icon: "☕" },
  ],
  transport: [
    { id: "gas", name: "Gas", icon: "⛽" },
    { id: "parking", name: "Parking", icon: "🅿️" },
    { id: "public", name: "Public Transport", icon: "🚌" },
  ],
  entertainment: [
    { id: "movies", name: "Movies", icon: "🎞️" },
    { id: "games", name: "Games", icon: "🎮" },
    { id: "concerts", name: "Concerts", icon: "🎵" },
  ],
  shopping: [
    { id: "clothes", name: "Clothes", icon: "👕" },
    { id: "electronics", name: "Electronics", icon: "📱" },
    { id: "gifts", name: "Gifts", icon: "🎁" },
  ],
  housing: [
    { id: "rent", name: "Rent", icon: "🏢" },
    { id: "utilities", name: "Utilities", icon: "💡" },
    { id: "maintenance", name: "Maintenance", icon: "🔧" },
  ],
  other: [
    { id: "income", name: "Income", icon: "💰" },
    { id: "misc", name: "Miscellaneous", icon: "🔮" },
  ],
};

// Mock data for tags
const suggestedTags = [
  { id: "1", name: "Essentials", icon: "🛒" },
  { id: "2", name: "Work", icon: "💼" },
  { id: "3", name: "Dining", icon: "🍽️" },
  { id: "4", name: "Car", icon: "⛽" },
  { id: "5", name: "Leisure", icon: "🎭" },
];

interface AddTransactionFormProps {
  groupId?: string;
  personId?: string;
}

export function AddTransactionForm({
  groupId,
  personId,
}: AddTransactionFormProps) {
  const [attachments, setAttachments] = useState<File[]>([]);
  const [showCategoryModal, setShowCategoryModal] = useState(false);
  const [selectedMainCategory, setSelectedMainCategory] = useState<
    string | null
  >(null);
  const [searchQuery, setSearchQuery] = useState("");
  const [selectedTags, setSelectedTags] = useState<string[]>([]);
  const [tagInput, setTagInput] = useState("");

  const form = useForm<z.infer<typeof formSchema>>({
    resolver: zodResolver(formSchema),
    defaultValues: {
      title: "",
      amount: "",
      date: new Date(),
      account: "",
      category: "",
      tags: "",
      note: "",
    },
  });

  const handleTagKeyDown = (e: React.KeyboardEvent<HTMLInputElement>) => {
    if (e.key === "Enter" && tagInput.trim()) {
      e.preventDefault();
      if (!selectedTags.includes(tagInput.trim())) {
        setSelectedTags([...selectedTags, tagInput.trim()]);
      }
      setTagInput("");
    }
  };

  const removeTag = (tag: string) => {
    setSelectedTags(selectedTags.filter((t) => t !== tag));
  };

  const addTag = (tag: string) => {
    if (!selectedTags.includes(tag)) {
      setSelectedTags([...selectedTags, tag]);
    }
    setTagInput("");
  };

  function onSubmit(values: z.infer<typeof formSchema>) {
    // In a real app, we would send this data to the API
    console.log({
      ...values,
      tags: selectedTags,
      attachments,
      groupId,
      personId,
    });
    alert("Transaction added successfully!");
    form.reset();
    setAttachments([]);
    setSelectedTags([]);
  }

  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.3 }}
    >
      {(groupId || personId) && (
        <div className="mb-6 p-4 bg-muted rounded-lg">
          {groupId && (
            <div className="text-sm">
              <span className="font-medium">Group:</span> Roommates
            </div>
          )}
          {personId && (
            <div className="text-sm">
              <span className="font-medium">Person:</span> John Doe
            </div>
          )}
        </div>
      )}

      <Form {...form}>
        <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-6">
          <FormField
            control={form.control}
            name="title"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Title</FormLabel>
                <FormControl>
                  <Input placeholder="e.g., Grocery Shopping" {...field} />
                </FormControl>
                <FormMessage />
              </FormItem>
            )}
          />

          <FormField
            control={form.control}
            name="amount"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Amount</FormLabel>
                <FormControl>
                  <Input
                    placeholder="e.g., 42.50 (negative for expenses)"
                    {...field}
                    type="number"
                    step="0.01"
                  />
                </FormControl>
                <FormMessage />
              </FormItem>
            )}
          />

          <FormField
            control={form.control}
            name="date"
            render={({ field }) => (
              <FormItem className="flex flex-col">
                <FormLabel>Date</FormLabel>
                <Popover>
                  <PopoverTrigger asChild>
                    <FormControl>
                      <Button
                        variant={"outline"}
                        className={cn(
                          "w-full pl-3 text-left font-normal",
                          !field.value && "text-muted-foreground"
                        )}
                      >
                        {field.value ? (
                          format(field.value, "PPP")
                        ) : (
                          <span>Pick a date</span>
                        )}
                        <CalendarIcon className="ml-auto h-4 w-4 opacity-50" />
                      </Button>
                    </FormControl>
                  </PopoverTrigger>
                  <PopoverContent className="w-auto p-0" align="start">
                    <Calendar
                      mode="single"
                      selected={field.value}
                      onSelect={field.onChange}
                      initialFocus
                    />
                  </PopoverContent>
                </Popover>
                <FormMessage />
              </FormItem>
            )}
          />

          <FormField
            control={form.control}
            name="account"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Account</FormLabel>
                <Select
                  onValueChange={field.onChange}
                  defaultValue={field.value}
                >
                  <FormControl>
                    <SelectTrigger>
                      <SelectValue placeholder="Select an account" />
                    </SelectTrigger>
                  </FormControl>
                  <SelectContent>
                    {accounts.map((account) => (
                      <SelectItem key={account.id} value={account.id}>
                        <div className="flex items-center">
                          <span className="mr-2">{account.icon}</span>
                          <span>{account.name}</span>
                        </div>
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                <FormMessage />
              </FormItem>
            )}
          />

          <FormField
            control={form.control}
            name="category"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Category</FormLabel>
                <Dialog
                  open={showCategoryModal}
                  onOpenChange={setShowCategoryModal}
                >
                  <DialogTrigger asChild>
                    <FormControl>
                      <Button
                        variant="outline"
                        className="w-full justify-start"
                      >
                        {field.value ? (
                          <>
                            <span className="mr-2">
                              {Object.values(subCategories)
                                .flat()
                                .find((cat) => cat.id === field.value)?.icon ||
                                mainCategories.find(
                                  (cat) => cat.id === field.value
                                )?.icon}
                            </span>
                            <span>
                              {Object.values(subCategories)
                                .flat()
                                .find((cat) => cat.id === field.value)?.name ||
                                mainCategories.find(
                                  (cat) => cat.id === field.value
                                )?.name}
                            </span>
                          </>
                        ) : (
                          <span className="text-muted-foreground">
                            Select a category
                          </span>
                        )}
                      </Button>
                    </FormControl>
                  </DialogTrigger>
                  <DialogContent className="sm:max-w-md">
                    <DialogHeader>
                      <DialogTitle>Select Category</DialogTitle>
                    </DialogHeader>
                    <div className="space-y-4">
                      <Input
                        placeholder="Search categories..."
                        value={searchQuery}
                        onChange={(e) => setSearchQuery(e.target.value)}
                        className="mb-4"
                      />
                      <div className="max-h-[400px] overflow-y-auto space-y-4">
                        {mainCategories.map((category) => {
                          const isSelected =
                            selectedMainCategory === category.id;
                          const subs =
                            subCategories[
                              category.id as keyof typeof subCategories
                            ] || [];

                          // Filter based on search
                          if (
                            searchQuery &&
                            !category.name
                              .toLowerCase()
                              .includes(searchQuery.toLowerCase()) &&
                            !subs.some((sub) =>
                              sub.name
                                .toLowerCase()
                                .includes(searchQuery.toLowerCase())
                            )
                          ) {
                            return null;
                          }

                          return (
                            <div key={category.id} className="space-y-2">
                              <Button
                                variant="ghost"
                                className="w-full justify-start font-medium"
                                onClick={() =>
                                  setSelectedMainCategory(
                                    isSelected ? null : category.id
                                  )
                                }
                              >
                                <span className="mr-2">{category.icon}</span>
                                <span>{category.name}</span>
                              </Button>

                              {(isSelected || searchQuery) && (
                                <div className="grid grid-cols-2 gap-2 ml-6">
                                  {subs
                                    .filter(
                                      (sub) =>
                                        !searchQuery ||
                                        sub.name
                                          .toLowerCase()
                                          .includes(searchQuery.toLowerCase())
                                    )
                                    .map((subCategory) => (
                                      <Button
                                        key={subCategory.id}
                                        variant="outline"
                                        className="justify-start h-auto py-2"
                                        onClick={() => {
                                          field.onChange(subCategory.id);
                                          setShowCategoryModal(false);
                                        }}
                                      >
                                        <span className="mr-2">
                                          {subCategory.icon}
                                        </span>
                                        <span className="text-sm">
                                          {subCategory.name}
                                        </span>
                                      </Button>
                                    ))}
                                </div>
                              )}
                            </div>
                          );
                        })}
                      </div>
                    </div>
                  </DialogContent>
                </Dialog>
                <FormMessage />
              </FormItem>
            )}
          />

          <div className="space-y-2">
            <Label htmlFor="tags">Tags</Label>
            <div className="flex flex-wrap gap-2 mb-2">
              {selectedTags.map((tag) => (
                <div
                  key={tag}
                  className="bg-secondary text-secondary-foreground rounded-full px-3 py-1 text-sm flex items-center"
                >
                  {suggestedTags.find((t) => t.name === tag)?.icon && (
                    <span className="mr-1">
                      {suggestedTags.find((t) => t.name === tag)?.icon}
                    </span>
                  )}
                  {tag}
                  <button
                    type="button"
                    className="ml-2 text-muted-foreground hover:text-foreground"
                    onClick={() => removeTag(tag)}
                  >
                    ×
                  </button>
                </div>
              ))}
            </div>
            <div className="relative">
              <Input
                id="tags"
                placeholder="Add tags (press Enter to add)"
                value={tagInput}
                onChange={(e) => setTagInput(e.target.value)}
                onKeyDown={handleTagKeyDown}
              />
              {tagInput && (
                <div className="absolute z-10 w-full mt-1 bg-background border rounded-md shadow-lg max-h-60 overflow-auto">
                  {suggestedTags
                    .filter(
                      (tag) =>
                        tag.name
                          .toLowerCase()
                          .includes(tagInput.toLowerCase()) &&
                        !selectedTags.includes(tag.name)
                    )
                    .map((tag) => (
                      <div
                        key={tag.id}
                        className="px-3 py-2 hover:bg-accent cursor-pointer flex items-center"
                        onClick={() => addTag(tag.name)}
                      >
                        <span className="mr-2">{tag.icon}</span>
                        <span>{tag.name}</span>
                      </div>
                    ))}
                </div>
              )}
            </div>
          </div>

          <FormField
            control={form.control}
            name="note"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Note</FormLabel>
                <FormControl>
                  <Textarea
                    placeholder="Add any additional details here..."
                    className="resize-none"
                    {...field}
                  />
                </FormControl>
                <FormMessage />
              </FormItem>
            )}
          />

          <div className="space-y-2">
            <Label>Attachments</Label>
            <FileUpload onFilesChange={setAttachments} />
          </div>

          {groupId && (
            <div className="space-y-2 border-t pt-4">
              <Label>Split Options</Label>
              <Select defaultValue="equal">
                <SelectTrigger>
                  <SelectValue placeholder="Select split method" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="equal">Equal Split</SelectItem>
                  <SelectItem value="percentage">Percentage Split</SelectItem>
                  <SelectItem value="fixed">Fixed Amount Split</SelectItem>
                </SelectContent>
              </Select>

              <div className="space-y-2 mt-4">
                <div className="flex items-center justify-between p-2 border rounded-md">
                  <div className="flex items-center">
                    <span className="mr-2">👨</span>
                    <span>John Doe</span>
                  </div>
                  <Input type="number" className="w-24" defaultValue="33.33" />
                </div>
                <div className="flex items-center justify-between p-2 border rounded-md">
                  <div className="flex items-center">
                    <span className="mr-2">👩</span>
                    <span>Jane Smith</span>
                  </div>
                  <Input type="number" className="w-24" defaultValue="33.33" />
                </div>
                <div className="flex items-center justify-between p-2 border rounded-md">
                  <div className="flex items-center">
                    <span className="mr-2">👨</span>
                    <span>Mike Johnson</span>
                  </div>
                  <Input type="number" className="w-24" defaultValue="33.34" />
                </div>
              </div>
            </div>
          )}

          <Button type="submit" className="w-full">
            Add Transaction
          </Button>
        </form>
      </Form>
    </motion.div>
  );
}
