<script setup lang="ts">
import { type VariantProps, cva } from 'class-variance-authority'
import { cn } from '@/lib/utils' // Shadcn helper pro spojování tříd

// 1. Definice variant (Design System)
// Zde mapujeme backendové klíče (success, warning) na Tailwind třídy
const badgeVariants = cva(
  'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2',
  {
    variants: {
      variant: {
        default: 'bg-primary/10 text-primary-foreground hover:bg-primary/20 border-transparent',
        
        // Backend: 'gray'
        gray: 'bg-slate-100 text-slate-700 border-slate-200 hover:bg-slate-200', 
        
        // Backend: 'warning'
        warning: 'bg-amber-100 text-amber-800 border-amber-200 hover:bg-amber-200',
        
        // Backend: 'success'
        success: 'bg-emerald-100 text-emerald-800 border-emerald-200 hover:bg-emerald-200',
        
        // Backend: 'info'
        info: 'bg-blue-100 text-blue-800 border-blue-200 hover:bg-blue-200',
        
        // Backend: 'destructive' nebo 'danger'
        destructive: 'bg-red-100 text-red-800 border-red-200 hover:bg-red-200',
      },
    },
    defaultVariants: {
      variant: 'default',
    },
  }
)

interface Props {
  variant?: NonNullable<Parameters<typeof badgeVariants>[0]>['variant'] | string
  class?: string
}

const props = defineProps<Props>()
</script>

<template>
  <div :class="cn(badgeVariants({ variant: props.variant as any }), props.class)">
    <slot />
  </div>
</template>