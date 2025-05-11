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
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { CustomAvatar } from "@/components/ui/custom-avatar";
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/components/ui/dialog";

const formSchema = z.object({
  name: z.string().min(2, {
    message: "Name must be at least 2 characters.",
  }),
  email: z.string().email().optional().or(z.literal("")),
  phone: z.string().optional(),
  description: z.string().optional(),
});

// Mock data for global members
const globalMembers = [
  {
    id: 1,
    name: "John Doe",
    email: "john@example.com",
    phone: "+1 (555) 123-4567",
    icon: "👨",
  },
  {
    id: 2,
    name: "Jane Smith",
    email: "jane@example.com",
    phone: "+1 (555) 987-6543",
    icon: "👩",
  },
  {
    id: 3,
    name: "Mike Johnson",
    email: "mike@example.com",
    phone: "+1 (555) 456-7890",
    icon: "👨",
  },
  {
    id: 4,
    name: "Sarah Williams",
    email: "sarah@example.com",
    phone: "+1 (555) 234-5678",
    icon: "👩",
  },
  {
    id: 5,
    name: "David Brown",
    email: "david@example.com",
    phone: "+1 (555) 876-5432",
    icon: "👨",
  },
  {
    id: 6,
    name: "Emily Davis",
    email: "emily@example.com",
    phone: "+1 (555) 345-6789",
    icon: "👩",
  },
];

// Mock data for icons
const icons = [
  "👨",
  "👩",
  "👱‍♂️",
  "👱‍♀️",
  "👨‍🦰",
  "👩‍🦰",
  "👨‍🦱",
  "👩‍🦱",
  "👨‍🦳",
  "👩‍🦳",
  "👨‍🦲",
  "👩‍🦲",
  "🧔",
  "🧔‍♀️",
  "🧔‍♂️",
  "👵",
  "👴",
  "👲",
  "👳‍♀️",
  "👳‍♂️",
  "🧕",
  "👮‍♀️",
  "👮‍♂️",
  "👷‍♀️",
  "👷‍♂️",
  "💂‍♀️",
  "💂‍♂️",
  "🕵️‍♀️",
  "🕵️‍♂️",
  "👩‍⚕️",
  "👨‍⚕️",
  "👩‍🌾",
  "👨‍🌾",
  "👩‍🍳",
  "👨‍🍳",
  "👩‍🎓",
  "👨‍🎓",
  "👩‍🎤",
  "👨‍🎤",
  "👩‍🏫",
  "👨‍🏫",
  "👩‍🏭",
  "👨‍🏭",
  "👩‍💻",
  "👨‍💻",
  "👩‍💼",
  "👨‍💼",
  "👩‍🔧",
  "👨‍🔧",
  "👩‍🔬",
  "👨‍🔬",
  "👩‍🎨",
  "👨‍🎨",
  "👩‍🚒",
  "👨‍🚒",
  "👩‍✈️",
  "👨‍✈️",
  "👩‍🚀",
  "👨‍🚀",
  "👩‍⚖️",
  "👨‍⚖️",
  "👰‍♀️",
  "👰‍♂️",
  "🤵‍♀️",
  "🤵‍♂️",
  "🤴",
  "👸",
  "🦸‍♀️",
  "🦸‍♂️",
  "🦹‍♀️",
  "🦹‍♂️",
  "🧙‍♀️",
  "🧙‍♂️",
  "🧚‍♀️",
  "🧚‍♂️",
  "🧛‍♀️",
  "🧛‍♂️",
  "🧜‍♀️",
  "🧜‍♂️",
  "🧝‍♀️",
  "🧝‍♂️",
  "🧞‍♀️",
  "🧞‍♂️",
  "🧟‍♀️",
  "🧟‍♂️",
  "💆‍♀️",
  "💆‍♂️",
  "💇‍♀️",
  "💇‍♂️",
  "🚶‍♀️",
  "🚶‍♂️",
  "🧍‍♀️",
  "🧍‍♂️",
  "🧎‍♀️",
  "🧎‍♂️",
  "🧑‍🦯",
  "👩‍🦯",
  "👨‍🦯",
  "🧑‍🦼",
  "👩‍🦼",
  "👨‍🦼",
  "🧑‍🦽",
  "👩‍🦽",
  "👨‍🦽",
  "🏃‍♀️",
  "🏃‍♂️",
  "💃",
  "🕺",
  "🕴️",
  "👯‍♀️",
  "👯‍♂️",
  "🧖‍♀️",
  "🧖‍♂️",
  "🧗‍♀️",
  "🧗‍♂️",
  "🤺",
  "🏇",
  "⛷️",
  "🏂",
  "🏌️‍♀️",
  "🏌️‍♂️",
  "🏄‍♀️",
  "🏄‍♂️",
  "🚣‍♀️",
  "🚣‍♂️",
  "🏊‍♀️",
  "🏊‍♂️",
  "⛹️‍♀️",
  "⛹️‍♂️",
  "🏋️‍♀️",
  "🏋️‍♂️",
  "🚴‍♀️",
  "🚴‍♂️",
  "🚵‍♀️",
  "🚵‍♂️",
  "🤸‍♀️",
  "🤸‍♂️",
  "🤼‍♀️",
  "🤼‍♂️",
  "🤽‍♀️",
  "🤽‍♂️",
  "🤾‍♀️",
  "🤾‍♂️",
  "🤹‍♀️",
  "🤹‍♂️",
  "🧘‍♀️",
  "🧘‍♂️",
  "🛀",
  "🛌",
  "🧑‍🤝‍🧑",
  "👭",
  "👫",
  "👬",
];

export function AddPersonForm() {
  const [selectedIcon, setSelectedIcon] = useState("👤");
  const [searchQuery, setSearchQuery] = useState("");
  const [selectedTab, setSelectedTab] = useState("manual");
  const [showIconSelector, setShowIconSelector] = useState(false);
  const [iconSearchQuery, setIconSearchQuery] = useState("");

  const form = useForm<z.infer<typeof formSchema>>({
    resolver: zodResolver(formSchema),
    defaultValues: {
      name: "",
      email: "",
      phone: "",
      description: "",
    },
  });

  const filteredMembers = globalMembers.filter(
    (member) =>
      member.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
      member.email.toLowerCase().includes(searchQuery.toLowerCase()) ||
      member.phone.includes(searchQuery)
  );

  const filteredIcons = iconSearchQuery
    ? icons.filter((icon) => icon.includes(iconSearchQuery))
    : icons;

  function onSubmit(values: z.infer<typeof formSchema>) {
    // In a real app, we would send this data to the API
    console.log({
      ...values,
      icon: selectedIcon,
    });
    alert("Person added successfully!");
    form.reset();
  }

  const selectGlobalMember = (member: (typeof globalMembers)[0]) => {
    form.setValue("name", member.name);
    form.setValue("email", member.email);
    form.setValue("phone", member.phone || "");
    setSelectedIcon(member.icon);
    setSelectedTab("manual");
  };

  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.3 }}
    >
      <Tabs
        value={selectedTab}
        onValueChange={setSelectedTab}
        className="w-full mb-6"
      >
        <TabsList className="grid w-full grid-cols-2">
          <TabsTrigger value="manual">Manual Entry</TabsTrigger>
          <TabsTrigger value="search">Search Existing</TabsTrigger>
        </TabsList>

        <TabsContent value="search" className="space-y-4 mt-4">
          <Input
            placeholder="Search by name, email or phone..."
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
          />

          <div className="space-y-2 max-h-[300px] overflow-y-auto">
            {filteredMembers.length === 0 ? (
              <div className="text-center py-4 text-muted-foreground">
                No members found
              </div>
            ) : (
              filteredMembers.map((member) => (
                <Button
                  key={member.id}
                  variant="outline"
                  className="w-full justify-start h-auto py-2"
                  onClick={() => selectGlobalMember(member)}
                >
                  <CustomAvatar icon={member.icon} className="h-8 w-8 mr-2" />
                  <div className="text-left">
                    <div className="font-medium">{member.name}</div>
                    <div className="text-xs text-muted-foreground">
                      {member.email} • {member.phone}
                    </div>
                  </div>
                </Button>
              ))
            )}
          </div>
        </TabsContent>

        <TabsContent value="manual">
          <Form {...form}>
            <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-6">
              <div className="flex justify-center mb-4">
                <Dialog
                  open={showIconSelector}
                  onOpenChange={setShowIconSelector}
                >
                  <DialogTrigger asChild>
                    <Button variant="outline" className="w-auto">
                      <CustomAvatar
                        icon={selectedIcon}
                        className="h-8 w-8 mr-2"
                      />
                      <span>Select Icon</span>
                    </Button>
                  </DialogTrigger>
                  <DialogContent className="sm:max-w-md">
                    <DialogHeader>
                      <DialogTitle>Select Icon</DialogTitle>
                    </DialogHeader>
                    <div className="space-y-4 py-4">
                      <Input
                        placeholder="Search icons..."
                        value={iconSearchQuery}
                        onChange={(e) => setIconSearchQuery(e.target.value)}
                      />
                      <div className="grid grid-cols-8 gap-2 max-h-[300px] overflow-y-auto">
                        \
                        {filteredIcons.map((icon, index) => (
                          <Button
                            key={index}
                            variant="outline"
                            className="h-10 w-10 p-0"
                            onClick={() => {
                              setSelectedIcon(icon);
                              setShowIconSelector(false);
                            }}
                          >
                            <span className="text-xl">{icon}</span>
                          </Button>
                        ))}
                      </div>
                    </div>
                  </DialogContent>
                </Dialog>
              </div>

              <FormField
                control={form.control}
                name="name"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Name</FormLabel>
                    <FormControl>
                      <Input placeholder="e.g., John Doe" {...field} />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />

              <FormField
                control={form.control}
                name="email"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Email</FormLabel>
                    <FormControl>
                      <Input
                        placeholder="e.g., john@example.com"
                        {...field}
                        type="email"
                      />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />

              <FormField
                control={form.control}
                name="phone"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Phone</FormLabel>
                    <FormControl>
                      <Input
                        placeholder="e.g., +1 (555) 123-4567"
                        {...field}
                        type="tel"
                      />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />

              <FormField
                control={form.control}
                name="description"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Description</FormLabel>
                    <FormControl>
                      <Textarea
                        placeholder="Add a description..."
                        className="resize-none"
                        {...field}
                      />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />

              <Button type="submit" className="w-full">
                Add Person
              </Button>
            </form>
          </Form>
        </TabsContent>
      </Tabs>
    </motion.div>
  );
}
