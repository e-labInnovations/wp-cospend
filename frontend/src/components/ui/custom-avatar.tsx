import type React from "react"
import { cn } from "@/lib/utils"
import { Avatar, AvatarFallback } from "@/components/ui/avatar"

interface CustomAvatarProps {
  icon: React.ReactNode | string
  className?: string
  fallbackClassName?: string
}

export function CustomAvatar({ icon, className, fallbackClassName }: CustomAvatarProps) {
  return (
    <Avatar className={cn("bg-primary/10", className)}>
      <AvatarFallback className={cn("flex items-center justify-center", fallbackClassName)}>
        {typeof icon === "string" ? <span className="text-center">{icon}</span> : icon}
      </AvatarFallback>
    </Avatar>
  )
}
