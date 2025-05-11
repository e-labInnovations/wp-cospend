import type React from "react";

import { zodResolver } from "@hookform/resolvers/zod";
import { useForm } from "react-hook-form";
import { z } from "zod";
import { motion } from "framer-motion";
import { useState, useEffect } from "react";

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

// Mock transaction data
const mockTransaction = {
  id: "1",
  title: "Grocery Shopping",
  amount: "-85.75",
  date: new Date("2025-01-10"),
  account: "3", // Credit Card
  category: "groceries",
  tags: ["Essentials"],
  note: "Weekly grocery shopping at Whole Foods. Bought fruits, vegetables, and some snacks for the week.",
  attachments: [
    {
      id: "1",
      name: "receipt.jpg",
      type: "image/jpeg",
      url: "/placeholder.svg?height=300&width=200",
    },
  ],
};

interface EditTransactionFormProps {
  id: string;
}

export function EditTransactionForm({ id }: EditTransactionFormProps) {
  const [attachments, setAttachments] = useState<File[]>([]);
  const [existingAttachments, setExistingAttachments] = useState<any[]>([]);
  const [showCategoryModal, setShowCategoryModal] = useState(false);
  const [selectedMainCategory, setSelectedMainCategory] = useState<
    string | null
  >(null);
  const [searchQuery, setSearchQuery] = useState("");
  const [selectedTags, setSelectedTags] = useState<string[]>([]);
  const [tagInput, setTagInput] = useState("");
  const [isLoading, setIsLoading] = useState(true);

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

  // Simulate fetching transaction data
  useEffect(() => {
    // In a real app, we would fetch the transaction data from the API
    setTimeout(() => {
      // Simulate API call
      const transaction = mockTransaction;

      form.reset({
        title: transaction.title,
        amount: transaction.amount,
        date: transaction.date,
        account: transaction.account,
        category: transaction.category,
        tags: transaction.tags.join(", "),
        note: transaction.note || "",
      });

      setSelectedTags(transaction.tags);
      setExistingAttachments(transaction.attachments);
      setIsLoading(false);

      // Find the main category for this subcategory
      const mainCat = Object.entries(subCategories).find(([_, subs]) =>
        subs.some((sub) => sub.id === transaction.category)
      );

      if (mainCat) {
        setSelectedMainCategory(mainCat[0]);
      }
    }, 500);
  }, [form, id]);

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

  const removeExistingAttachment = (attachmentId: string) => {
    setExistingAttachments(
      existingAttachments.filter((a) => a.id !== attachmentId)
    );
  };

  function onSubmit(values: z.infer<typeof formSchema>) {
    // In a real app, we would send this data to the API
    console.log({
      ...values,
      id,
      tags: selectedTags,
      existingAttachments,
      newAttachments: attachments,
    });
    alert("Transaction updated successfully!");
  }

  if (isLoading) {
    return (
      <div className="flex justify-center items-center py-12">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
      </div>
    );
  }

  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.3 }}
    >
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

            {existingAttachments.length > 0 && (
              <div className="grid grid-cols-2 gap-2 mb-4">
                {existingAttachments.map((attachment) => (
                  <div
                    key={attachment.id}
                    className="relative group border rounded-md p-2 flex items-center"
                  >
                    <div className="w-12 h-12 mr-2 rounded-md overflow-hidden bg-muted flex items-center justify-center">
                      {attachment.type.startsWith("image/") ? (
                        <img
                          src={attachment.url || "/placeholder.svg"}
                          alt={attachment.name}
                          className="w-full h-full object-cover"
                        />
                      ) : (
                        <div className="text-muted-foreground">File</div>
                      )}
                    </div>
                    <div className="flex-1 min-w-0">
                      <p className="text-sm font-medium truncate">
                        {attachment.name}
                      </p>
                    </div>
                    <Button
                      type="button"
                      variant="ghost"
                      size="icon"
                      className="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition-opacity"
                      onClick={() => removeExistingAttachment(attachment.id)}
                    >
                      <span className="sr-only">Remove</span>×
                    </Button>
                  </div>
                ))}
              </div>
            )}

            <FileUpload onFilesChange={setAttachments} />
          </div>

          <Button type="submit" className="w-full">
            Update Transaction
          </Button>
        </form>
      </Form>
    </motion.div>
  );
}
