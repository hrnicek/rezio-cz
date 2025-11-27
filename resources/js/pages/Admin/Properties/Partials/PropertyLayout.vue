<script setup lang="ts">
import { ref } from 'vue';
import { useStorage, useBreakpoints, breakpointsTailwind } from '@vueuse/core';
import { Menu, PanelLeftClose, PanelLeftOpen } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import PropertySidebar from './PropertySidebar.vue';
import { Button } from '@/components/ui/button';
import { Sheet, SheetContent, SheetTrigger } from '@/components/ui/sheet';

defineProps<{
    property: {
        id: number;
        name: string;
        address?: string;
    };
    breadcrumbs?: any[];
}>();

// Responsive
const breakpoints = useBreakpoints(breakpointsTailwind);
const isDesktop = breakpoints.greater('lg');

// Sidebar State
const sidebarWidth = useStorage('property-sidebar-width', 256);
const isSidebarOpen = useStorage('property-sidebar-open', true);
const isResizing = ref(false);

const startResizing = () => {
    isResizing.value = true;
    document.addEventListener('mousemove', handleMouseMove);
    document.addEventListener('mouseup', stopResizing);
    document.body.style.cursor = 'col-resize';
    document.body.style.userSelect = 'none';
};

const handleMouseMove = (e: MouseEvent) => {
    if (!isResizing.value) return;
    sidebarWidth.value = Math.max(200, Math.min(400, sidebarWidth.value + e.movementX));
};

const stopResizing = () => {
    isResizing.value = false;
    document.removeEventListener('mousemove', handleMouseMove);
    document.removeEventListener('mouseup', stopResizing);
    document.body.style.cursor = '';
    document.body.style.userSelect = '';
};

// Mobile Sheet State
const isSheetOpen = ref(false);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 items-start h-[calc(100vh-3rem)] overflow-hidden relative group/layout">
            <!-- Desktop Sidebar -->
            <div 
                v-if="isDesktop" 
                class="relative flex-shrink-0 h-full hidden lg:flex transition-[width] duration-300 ease-in-out will-change-[width]"
                :style="{ width: isSidebarOpen ? `${sidebarWidth}px` : '0px' }"
            >
                <div class="h-full w-full overflow-hidden border-r bg-background/50">
                    <div :style="{ width: `${sidebarWidth}px` }" class="h-full">
                        <PropertySidebar :property="property" class="w-full">
                            <template #actions>
                                <Button 
                                    variant="ghost" 
                                    size="icon" 
                                    class="h-7 w-7 text-muted-foreground hover:text-foreground" 
                                    @click="isSidebarOpen = false"
                                    title="Collapse Sidebar"
                                >
                                    <PanelLeftClose class="h-4 w-4" />
                                </Button>
                            </template>
                        </PropertySidebar>
                    </div>
                </div>
                
                <!-- Resize Handle -->
                <div
                    v-if="isSidebarOpen"
                    class="absolute top-0 right-0 h-full w-1 cursor-col-resize hover:bg-primary/50 transition-colors z-10 opacity-0 group-hover/layout:opacity-100"
                    :class="{ 'bg-primary/50 opacity-100': isResizing }"
                    @mousedown.prevent="startResizing"
                />
            </div>

            <!-- Mobile Header / Trigger -->
            <div v-else-if="!isDesktop" class="lg:hidden absolute top-4 right-4 z-20">
                <Sheet v-model:open="isSheetOpen">
                    <SheetTrigger as-child>
                        <Button variant="outline" size="icon" class="h-8 w-8 shadow-sm">
                            <Menu class="h-4 w-4" />
                        </Button>
                    </SheetTrigger>
                    <SheetContent side="left" class="p-0 w-80">
                         <PropertySidebar :property="property" />
                    </SheetContent>
                </Sheet>
            </div>

            <!-- Main Content Area -->
            <div class="flex-1 h-full overflow-y-auto bg-background p-6 relative transition-all duration-300">
                 <!-- Expand Button (Desktop only) -->
                 <div v-if="isDesktop && !isSidebarOpen" class="absolute top-4 left-4 z-10">
                      <Button 
                        variant="outline" 
                        size="icon" 
                        class="h-8 w-8 shadow-sm bg-background" 
                        @click="isSidebarOpen = true"
                        title="Expand Sidebar"
                      >
                          <PanelLeftOpen class="h-4 w-4" />
                      </Button>
                 </div>
                 
                 <!-- Mobile Property Header -->
                 <div v-if="!isDesktop" class="mb-6 flex items-center justify-between pr-10">
                     <div>
                        <h1 class="text-xl font-bold truncate">{{ property.name }}</h1>
                     </div>
                 </div>

                <div :class="{ 'pl-10': isDesktop && !isSidebarOpen }">
                    <slot />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
