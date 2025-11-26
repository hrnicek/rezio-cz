<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { ChevronLeft, ChevronRight, ChevronsLeft, ChevronsRight } from 'lucide-vue-next';

interface LinkItem {
    url: string | null;
    label: string;
    active: boolean;
}

interface Meta {
    current_page: number;
    from: number;
    last_page: number;
    links: LinkItem[];
    path: string;
    per_page: number;
    to: number;
    total: number;
}

defineProps<{
    links: LinkItem[];
    meta?: Meta; // Optional, sometimes we just get links array
}>();
</script>

<template>
    <div class="flex items-center justify-between px-2" v-if="links.length > 3">
        <div class="flex-1 text-sm text-muted-foreground" v-if="meta">
            Zobrazeno {{ meta.from }} až {{ meta.to }} z {{ meta.total }} výsledků
        </div>
        <div class="flex items-center space-x-2">
            <template v-for="(link, key) in links" :key="key">
                <div v-if="link.url === null" class="px-2 text-muted-foreground" v-html="link.label" />
                
                <Button
                    v-else
                    :variant="link.active ? 'default' : 'outline'"
                    size="sm"
                    class="h-8 w-8 p-0"
                    :disabled="link.active"
                    as-child
                >
                    <Link :href="link.url" preserve-scroll preserve-state>
                        <span v-html="link.label"></span>
                    </Link>
                </Button>
            </template>
        </div>
    </div>
</template>
