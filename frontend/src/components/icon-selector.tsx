import type React from "react";

import { useState } from "react";
import { Button } from "@/components/ui/button";
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/components/ui/dialog";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Upload, ImageIcon, Code, Search } from "lucide-react";
import { CustomAvatar } from "@/components/ui/custom-avatar";

// Mock icon library - in a real app, this would be a more comprehensive set
const iconLibrary = [
  { id: "home", icon: "🏠" },
  { id: "money", icon: "💰" },
  { id: "card", icon: "💳" },
  { id: "food", icon: "🍔" },
  { id: "transport", icon: "🚗" },
  { id: "entertainment", icon: "🎬" },
  { id: "shopping", icon: "🛍️" },
  { id: "health", icon: "🏥" },
  { id: "education", icon: "🎓" },
  { id: "travel", icon: "✈️" },
  { id: "utilities", icon: "💡" },
  { id: "gifts", icon: "🎁" },
  { id: "sports", icon: "⚽" },
  { id: "beauty", icon: "💄" },
  { id: "tech", icon: "💻" },
  { id: "pets", icon: "🐶" },
  { id: "family", icon: "👨‍👩‍👧‍👦" },
  { id: "friends", icon: "👥" },
  { id: "work", icon: "💼" },
  { id: "savings", icon: "🏦" },
  { id: "investments", icon: "📈" },
  { id: "bills", icon: "📝" },
  { id: "subscriptions", icon: "📱" },
  { id: "charity", icon: "🤲" },
];

interface IconSelectorProps {
  value: string;
  onChange: (value: string) => void;
  className?: string;
}

export function IconSelector({
  value,
  onChange,
  className,
}: IconSelectorProps) {
  const [searchQuery, setSearchQuery] = useState("");
  const [svgCode, setSvgCode] = useState("");
  const [open, setOpen] = useState(false);

  const filteredIcons = iconLibrary.filter((icon) =>
    icon.id.toLowerCase().includes(searchQuery.toLowerCase())
  );

  const handleIconSelect = (icon: string) => {
    onChange(icon);
    setOpen(false);
  };

  const handleFileUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      // In a real app, we would upload the file to a server and get a URL back
      // For now, we'll just use a placeholder
      onChange("📁");
      setOpen(false);
    }
  };

  const handleSvgCodeSubmit = () => {
    if (svgCode) {
      // In a real app, we would validate the SVG code
      // For now, we'll just use a placeholder
      onChange("🖼️");
      setOpen(false);
    }
  };

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogTrigger asChild>
        <Button variant="outline" className={className}>
          <CustomAvatar icon={value} className="h-8 w-8 mr-2" />
          <span>Select Icon</span>
        </Button>
      </DialogTrigger>
      <DialogContent className="sm:max-w-md">
        <DialogHeader>
          <DialogTitle>Select Icon</DialogTitle>
        </DialogHeader>
        <Tabs defaultValue="library">
          <TabsList className="grid w-full grid-cols-3">
            <TabsTrigger value="library">Icon Library</TabsTrigger>
            <TabsTrigger value="upload">Upload</TabsTrigger>
            <TabsTrigger value="svg">SVG Code</TabsTrigger>
          </TabsList>

          <TabsContent value="library" className="space-y-4">
            <div className="relative">
              <Search className="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
              <Input
                type="text"
                placeholder="Search icons..."
                className="pl-8"
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
              />
            </div>

            <div className="grid grid-cols-6 gap-2 max-h-[300px] overflow-y-auto p-1">
              {filteredIcons.map((icon) => (
                <Button
                  key={icon.id}
                  variant="outline"
                  className="h-10 w-10 p-0"
                  onClick={() => handleIconSelect(icon.icon)}
                >
                  <span className="text-xl">{icon.icon}</span>
                </Button>
              ))}
            </div>
          </TabsContent>

          <TabsContent value="upload" className="space-y-4">
            <div className="flex flex-col items-center justify-center border-2 border-dashed rounded-lg p-6 text-center">
              <Upload className="h-10 w-10 text-muted-foreground mb-2" />
              <p className="mb-2 text-sm text-muted-foreground">
                PNG, JPG or SVG (MAX. 800x800px)
              </p>
              <Input
                id="icon-upload"
                type="file"
                accept="image/png,image/jpeg,image/svg+xml"
                className="hidden"
                onChange={handleFileUpload}
              />
              <Label
                htmlFor="icon-upload"
                className="cursor-pointer inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2"
              >
                <ImageIcon className="mr-2 h-4 w-4" />
                Upload Image
              </Label>
            </div>
          </TabsContent>

          <TabsContent value="svg" className="space-y-4">
            <div className="space-y-2">
              <Label htmlFor="svg-code">SVG Code</Label>
              <textarea
                id="svg-code"
                className="flex min-h-[120px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 resize-none"
                placeholder="Paste SVG code here..."
                value={svgCode}
                onChange={(e) => setSvgCode(e.target.value)}
              />
            </div>
            <Button onClick={handleSvgCodeSubmit} className="w-full">
              <Code className="mr-2 h-4 w-4" />
              Apply SVG
            </Button>
          </TabsContent>
        </Tabs>
      </DialogContent>
    </Dialog>
  );
}
