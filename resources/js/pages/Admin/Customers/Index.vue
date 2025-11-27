<script setup lang="ts">
import { ref, watch } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import AppDataTable from '@/components/AppDataTable.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import {
  Sheet,
  SheetContent,
  SheetDescription,
  SheetHeader,
  SheetTitle,
} from '@/components/ui/sheet'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import { MoreHorizontal, Plus, Search, Pencil, Trash, Download, Upload } from 'lucide-vue-next'
import CustomerForm from './CustomerForm.vue'
import { useDebounceFn } from '@vueuse/core'

declare const route: any;

const breadcrumbs = [
  { title: 'Zákazníci', href: route('admin.customers.index') },
]

const props = defineProps<{
  customers: any
  filters: any
}>()

const search = ref(props.filters.search || '')
const isSheetOpen = ref(false)
const selectedCustomer = ref<any>(null)
const fileInput = ref<HTMLInputElement | null>(null)

const columns = [
  { key: 'name', label: 'Jméno' },
  { key: 'contact', label: 'Kontakt' },
  { key: 'status', label: 'Stav' },
  { key: 'actions', label: '', align: 'right' as const },
]

const handleSearch = useDebounceFn((value: string) => {
  router.get(
    route('admin.customers.index'),
    { search: value },
    { preserveState: true, replace: true }
  )
}, 300)

watch(search, (value) => {
  handleSearch(value)
})

const openCreate = () => {
  selectedCustomer.value = null
  isSheetOpen.value = true
}

const openEdit = (customer: any) => {
  selectedCustomer.value = customer
  isSheetOpen.value = true
}

const handleSuccess = () => {
  isSheetOpen.value = false
  selectedCustomer.value = null
  // Toast is handled by backend flash message usually, or we can add local toast here
}

const deleteCustomer = (customer: any) => {
  if (confirm('Opravdu chcete smazat tohoto zákazníka?')) {
    router.delete(route('admin.customers.destroy', customer.id))
  }
}

const exportCustomers = () => {
  window.location.href = route('admin.customers.export')
}

const triggerImport = () => {
  fileInput.value?.click()
}

const handleImport = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files && target.files.length > 0) {
    const file = target.files[0]
    const formData = new FormData()
    formData.append('file', file)
    
    router.post(route('admin.customers.import'), formData, {
      onSuccess: () => {
        if (fileInput.value) fileInput.value.value = ''
      },
      forceFormData: true,
    })
  }
}

const getStatusVariant = (status: string) => {
  switch (status) {
    case 'active': return 'default'
    case 'vip': return 'secondary' // or purple if custom
    case 'inactive': return 'secondary'
    case 'blacklisted': return 'destructive'
    default: return 'outline'
  }
}
</script>

<template>
  <Head title="Zákazníci" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 p-4">
      <div class="flex items-center justify-between">
        <div class="relative w-full max-w-sm items-center">
          <Input 
            v-model="search" 
            placeholder="Hledat jméno, email, telefon..." 
            class="pl-10 h-9" 
          />
          <span class="absolute start-0 inset-y-0 flex items-center justify-center px-2">
            <Search class="size-4 text-muted-foreground" />
          </span>
        </div>
        <div class="flex gap-2">
            <input 
                ref="fileInput"
                type="file"
                accept=".csv,.xlsx,.xls"
                class="hidden"
                @change="handleImport"
            />
            <Button variant="outline" size="sm" class="h-9" @click="exportCustomers">
                <Download class="mr-2 h-4 w-4" />
                Export
            </Button>
            <Button variant="outline" size="sm" class="h-9" @click="triggerImport">
                <Upload class="mr-2 h-4 w-4" />
                Import
            </Button>
            <Button size="sm" class="h-9" @click="openCreate">
              <Plus class="mr-2 h-4 w-4" />
              Přidat zákazníka
            </Button>
        </div>
      </div>

      <AppDataTable :data="customers" :columns="columns">
        <template #contact="{ item }">
          <div class="flex flex-col text-sm">
              <span v-if="item.email" class="font-medium">{{ item.email }}</span>
              <span v-if="item.phone" class="text-muted-foreground">{{ item.phone }}</span>
            </div>
          </template>

          <template #status="{ value }">
            <Badge :variant="getStatusVariant(value)">
              {{ value === 'active' ? 'Aktivní' : (value === 'vip' ? 'VIP' : (value === 'inactive' ? 'Neaktivní' : 'Blacklist')) }}
            </Badge>
          </template>

          <template #actions="{ item }">
            <DropdownMenu>
              <DropdownMenuTrigger as-child>
                <Button variant="ghost" class="h-8 w-8 p-0">
                  <span class="sr-only">Open menu</span>
                  <MoreHorizontal class="h-4 w-4" />
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent align="end">
                <DropdownMenuLabel>Akce</DropdownMenuLabel>
                <DropdownMenuItem @click="openEdit(item)">
                  <Pencil class="mr-2 h-4 w-4" />
                  Upravit
                </DropdownMenuItem>
                <DropdownMenuItem @click="deleteCustomer(item)" class="text-red-600">
                  <Trash class="mr-2 h-4 w-4" />
                  Smazat
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>
        </template>
      </AppDataTable>
    </div>

    <Sheet v-model:open="isSheetOpen">
      <SheetContent class="sm:max-w-md overflow-y-auto">
        <SheetHeader>
          <SheetTitle>{{ selectedCustomer ? 'Upravit zákazníka' : 'Nový zákazník' }}</SheetTitle>
          <SheetDescription>
            {{ selectedCustomer ? 'Upravte údaje o zákazníkovi.' : 'Vyplňte údaje pro vytvoření nového zákazníka.' }}
          </SheetDescription>
        </SheetHeader>
        <CustomerForm 
          :customer="selectedCustomer" 
          @success="handleSuccess" 
        />
      </SheetContent>
    </Sheet>
  </AppLayout>
</template>
