import { cn } from "@/lib/design-system"

function Skeleton({ className, ...props }: React.ComponentProps<"div">) {
  return (
    <div
      data-slot="skeleton"
      className={cn("bg-accent animate-pulse rounded-[var(--radius-md)]", className)}
      {...props}
    />
  )
}

export { Skeleton }
