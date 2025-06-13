import * as React from "react"
import { Slot } from "@radix-ui/react-slot"
import { cva, type VariantProps } from "class-variance-authority"
import { Loader2 } from "lucide-react"

import { cn } from "@/lib/design-system"


const buttonVariants = cva(
  "inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md font-medium transition-smooth disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg:not([class*='size-'])]:size-4 shrink-0 [&_svg]:shrink-0 focus-brand",
  {
    variants: {
      variant: {
        default:
          "bg-primary text-primary-foreground shadow-sm hover:bg-primary/90 hover:shadow-brand",
        destructive:
          "bg-destructive text-destructive-foreground shadow-sm hover:bg-destructive/90 hover:shadow-destructive",
        outline:
          "border border-input bg-background shadow-sm hover:bg-accent hover:text-accent-foreground hover:border-primary/30",
        secondary:
          "bg-secondary text-secondary-foreground shadow-sm hover:bg-secondary/80",
        ghost:
          "hover:bg-accent hover:text-accent-foreground",
        link:
          "text-primary underline-offset-4 hover:underline p-0 h-auto font-normal",
        brand:
          "bg-gradient-primary text-primary-foreground shadow-brand hover:shadow-brand-strong hover:scale-102 transform",
        success:
          "bg-success text-success-foreground shadow-sm hover:bg-success/90 hover:shadow-success",
        warning:
          "bg-warning text-warning-foreground shadow-sm hover:bg-warning/90 hover:shadow-warning",
        info:
          "bg-info text-info-foreground shadow-sm hover:bg-info/90",
        glass:
          "glass text-foreground hover:glass-strong backdrop-blur-md",
        "outline-brand":
          "border-2 border-brand bg-transparent text-brand hover:bg-brand hover:text-primary-foreground",
        "outline-success":
          "border-2 border-success bg-transparent text-success hover:bg-success hover:text-success-foreground",
        "outline-destructive":
          "border-2 border-destructive bg-transparent text-destructive hover:bg-destructive hover:text-destructive-foreground",
      },
      size: {
        xs: "h-7 px-2 text-xs gap-1",
        sm: "h-8 px-3 text-sm gap-1.5",
        default: "h-9 px-4 py-2 text-sm gap-2",
        md: "h-10 px-6 py-2 text-sm gap-2",
        lg: "h-11 px-8 py-2 text-base gap-2",
        xl: "h-12 px-10 py-2 text-lg gap-2",
        icon: "h-9 w-9 p-0",
        "icon-sm": "h-8 w-8 p-0",
        "icon-lg": "h-10 w-10 p-0",
      },
    },
    defaultVariants: {
      variant: "default",
      size: "default",
    },
  }
)

export interface ButtonProps
  extends React.ButtonHTMLAttributes<HTMLButtonElement>,
    VariantProps<typeof buttonVariants> {
  asChild?: boolean
  loading?: boolean
  loadingText?: string
  leftIcon?: React.ReactNode
  rightIcon?: React.ReactNode
  fullWidth?: boolean
}

const Button = React.forwardRef<HTMLButtonElement, ButtonProps>(
  ({ 
    className, 
    variant, 
    size, 
    asChild = false, 
    loading = false,
    loadingText,
    leftIcon,
    rightIcon,
    fullWidth = false,
    disabled,
    children,
    ...props 
  }, ref) => {
    const Comp = asChild ? Slot : "button"
    
    const isDisabled = disabled || loading

    return (
      <Comp
        className={cn(
          buttonVariants({ variant, size }),
          fullWidth && "w-full",
          loading && "relative",
          className
        )}
        ref={ref}
        disabled={isDisabled}
        data-loading={loading}
        {...props}
      >
        {loading && (
          <div className="absolute inset-0 flex items-center justify-center">
            <Loader2 className="h-4 w-4 animate-spin" />
            {loadingText && (
              <span className="ml-2 text-sm">{loadingText}</span>
            )}
          </div>
        )}
        
        <div className={cn(
          "flex items-center justify-center gap-2",
          loading && "opacity-0"
        )}>
          {leftIcon && (
            <span className="flex-shrink-0">
              {leftIcon}
            </span>
          )}
          
          {children}
          
          {rightIcon && (
            <span className="flex-shrink-0">
              {rightIcon}
            </span>
          )}
        </div>
      </Comp>
    )
  }
)
Button.displayName = "Button"

export interface ButtonGroupProps extends React.HTMLAttributes<HTMLDivElement> {
  orientation?: 'horizontal' | 'vertical'
  size?: ButtonProps['size']
  variant?: ButtonProps['variant']
  attached?: boolean
}

const ButtonGroup = React.forwardRef<HTMLDivElement, ButtonGroupProps>(
  ({ 
    className, 
    orientation = 'horizontal', 
    size, 
    variant, 
    attached = false,
    children,
    ...props 
  }, ref) => {
    const processedChildren = React.Children.map(children, (child, index) => {
      if (React.isValidElement<ButtonProps>(child) && child.type === Button) {
        const isFirst = index === 0
        const isLast = index === React.Children.count(children) - 1
        
        let attachedClasses = ''
        if (attached) {
          if (orientation === 'horizontal') {
            attachedClasses = cn(
              'border-r-0 rounded-none',
              isFirst && 'rounded-l-md border-r-0',
              isLast && 'rounded-r-md border-r'
            )
          } else {
            attachedClasses = cn(
              'border-b-0 rounded-none',
              isFirst && 'rounded-t-md border-b-0',
              isLast && 'rounded-b-md border-b'
            )
          }
        }
        
        return React.cloneElement(child, {
          ...child.props,
          size: child.props.size || size,
          variant: child.props.variant || variant,
          className: cn(child.props.className, attachedClasses)
        })
      }
      return child
    })

    return (
      <div
        ref={ref}
        className={cn(
          "inline-flex",
          orientation === 'vertical' ? "flex-col" : "flex-row",
          attached ? "" : "gap-2",
          className
        )}
        role="group"
        {...props}
      >
        {processedChildren}
      </div>
    )
  }
)
ButtonGroup.displayName = "ButtonGroup"

export interface IconButtonProps extends Omit<ButtonProps, 'leftIcon' | 'rightIcon' | 'children'> {
  icon: React.ReactNode
  'aria-label': string
}

const IconButton = React.forwardRef<HTMLButtonElement, IconButtonProps>(
  ({ icon, size = 'icon', ...props }, ref) => {
    return (
      <Button
        ref={ref}
        size={size}
        {...props}
      >
        {icon}
      </Button>
    )
  }
)
IconButton.displayName = "IconButton"

export interface LinkButtonProps extends ButtonProps {
  href?: string
  external?: boolean
}

const LinkButton = React.forwardRef<HTMLButtonElement, LinkButtonProps>(
  ({ href, external = false, ...props }, ref) => {
    if (href) {
      return (
        <Button
          ref={ref}
          asChild
          {...props}
        >
          <a 
            href={href}
            {...(external && {
              target: "_blank",
              rel: "noopener noreferrer"
            })}
          >
            {props.children}
          </a>
        </Button>
      )
    }
    
    return <Button ref={ref} {...props} />
  }
)
LinkButton.displayName = "LinkButton"

export { Button, buttonVariants, ButtonGroup, IconButton, LinkButton }
