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
import { IconSelector } from "@/components/icon-selector";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { Switch } from "@/components/ui/switch";

const formSchema = z.object({
  name: z.string().min(2, {
    message: "Name must be at least 2 characters.",
  }),
  parentCategory: z.string().optional(),
  isMainCategory: z.boolean().default(false),
});

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

export function AddCategoryForm() {
  const [icon, setIcon] = useState("📋");

  const form = useForm<z.infer<typeof formSchema>>({
    resolver: zodResolver(formSchema),
    defaultValues: {
      name: "",
      parentCategory: "",
      isMainCategory: false,
    },
  });

  const isMainCategory = form.watch("isMainCategory");

  function onSubmit(values: z.infer<typeof formSchema>) {
    // In a real app, we would send this data to the API
    console.log({
      ...values,
      icon,
    });
    alert("Category added successfully!");
    form.reset();
  }

  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.3 }}
    >
      <Form {...form}>
        <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-6">
          <div className="flex justify-center mb-4">
            <IconSelector value={icon} onChange={setIcon} className="w-auto" />
          </div>

          <FormField
            control={form.control}
            name="name"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Name</FormLabel>
                <FormControl>
                  <Input placeholder="e.g., Groceries" {...field} />
                </FormControl>
                <FormMessage />
              </FormItem>
            )}
          />

          <FormField
            control={form.control}
            name="isMainCategory"
            render={({ field }) => (
              <FormItem className="flex flex-row items-center justify-between rounded-lg border p-4">
                <div className="space-y-0.5">
                  <FormLabel className="text-base">Main Category</FormLabel>
                  <div className="text-sm text-muted-foreground">
                    Mark this as a main category
                  </div>
                </div>
                <FormControl>
                  <Switch
                    checked={field.value}
                    onCheckedChange={field.onChange}
                  />
                </FormControl>
              </FormItem>
            )}
          />

          {!isMainCategory && (
            <FormField
              control={form.control}
              name="parentCategory"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Parent Category</FormLabel>
                  <Select
                    onValueChange={field.onChange}
                    defaultValue={field.value}
                  >
                    <FormControl>
                      <SelectTrigger>
                        <SelectValue placeholder="Select a parent category" />
                      </SelectTrigger>
                    </FormControl>
                    <SelectContent>
                      {mainCategories.map((category) => (
                        <SelectItem key={category.id} value={category.id}>
                          <div className="flex items-center">
                            <span className="mr-2">{category.icon}</span>
                            <span>{category.name}</span>
                          </div>
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                  <FormMessage />
                </FormItem>
              )}
            />
          )}

          <Button type="submit" className="w-full">
            Add Category
          </Button>
        </form>
      </Form>
    </motion.div>
  );
}
