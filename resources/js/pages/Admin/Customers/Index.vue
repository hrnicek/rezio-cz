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
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/alert-dialog'
import { MoreHorizontal, Plus, Search, Pencil, Trash, Download, Upload, Building2, User } from 'lucide-vue-next'
import CustomerForm from './CustomerForm.vue'
import { useDebounceFn } from '@vueuse/core'
import { toast } from 'vue-sonner'

declare const route: any;

// Matches App\Data\Admin\CustomerData
interface CustomerData {
    id: string;
    first_name: string;
    last_name: string;
    name: string;
    email: string;
    phone: string | null;
    
    is_company: boolean;
    company_name: string | null;
    ico: string | null;
    dic: string | null;
    has_vat: boolean;

    billing_street: string | null;
    billing_city: string | null;
    billing_zip: string | null;
    billing_country: string | null;

    internal_notes: string | null;
    is_registered: boolean;
    created_at: string | null;
}

const props = defineProps<{
  customers: {
      data: CustomerData[];
      links: any;
      meta: any;
  };
  filters: {
      search?: string;
  };
}>()

const search = ref(props.filters.search || '')
const isSheetOpen = ref(false)
const selectedCustomer = ref<CustomerData | null>(null)
const fileInput = ref<HTMLInputElement | null>(null)

const customerToDelete = ref<CustomerData | null>(null)
const isDeleteDialogOpen = ref(false)

const breadcrumbs = [
  { title: 'Zákazníci', href: route('admin.customers.index') },
]

const columns = [
  { key: 'name', label: 'Zákazník' },
  { key: 'contact', label: 'Kontakt' },
  { key: 'type', label: 'Typ' },
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

const openEdit = (customer: CustomerData) => {
  selectedCustomer.value = customer
  isSheetOpen.value = true
}

const handleSuccess = () => {
  isSheetOpen.value = false
  selectedCustomer.value = null
}

const confirmDelete = (customer: CustomerData) => {
  customerToDelete.value = customer
  isDeleteDialogOpen.value = true
}

const deleteCustomer = () => {
  if (!customerToDelete.value) return

  router.delete(route('admin.customers.destroy', customerToDelete.value.id), {
    onSuccess: () => {
      isDeleteDialogOpen.value = false
      customerToDelete.value = null
      toast.success('Zákazník byl úspěšně smazán.')
    },
    onError: () => {
      toast.error('Nepodařilo se smazat zákazníka.')
    }
  })
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
        toast.success('Import byl úspěšně dokončen.')
      },
      onError: () => {
        toast.error('Chyba při importu zákazníků.')
      },
      forceFormData: true,
    })
  }
}
</script>

<template>
  <Head title="Zákazníci" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-8 max-w-7xl mx-auto w-full">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-foreground">Zákazníci</h2>
            <p class="text-muted-foreground">Správa databáze hostů a firem.</p>
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
              Nový zákazník
            </Button>
        </div>
      </div>

      <div class="flex items-center">
          <div class="relative w-full max-w-md">
            <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
            <Input 
              v-model="search" 
              placeholder="Hledat jméno, email, telefon, IČO..." 
              class="pl-9 h-9 w-full" 
            />
          </div>
      </div>

      <AppDataTable 
        :data="customers" 
        :columns="columns"
        no-results-message="Žádní zákazníci nenalezeni."
      >
        <template #name="{ item }">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-md border bg-muted/50">
                    <Building2 v-if="item.is_company" class="h-4 w-4 text-muted-foreground" />
                    <User v-else class="h-4 w-4 text-muted-foreground" />
                </div>
                <div>
                    <div class="font-medium text-foreground">{{ item.company_name || item.name }}</div>
                    <div v-if="item.is_company && item.name !== item.company_name" class="text-xs text-muted-foreground">
                        Kontaktní osoba: {{ item.name }}
                    </div>
                </div>
            </div>
        </template>

        <template #contact="{ item }">
          <div class="flex flex-col text-sm">
              <a v-if="item.email" :href="'mailto:' + item.email" class="font-medium hover:underline hover:text-primary">{{ item.email }}</a>
              <span v-else class="text-muted-foreground text-xs">-</span>
              <span v-if="item.phone" class="text-muted-foreground text-xs font-mono">{{ item.phone }}</span>
            </div>
          </template>

          <template #type="{ item }">
            <Badge variant="secondary" class="rounded-md font-mono text-xs" v-if="item.is_company">
                Firma
            </Badge>
            <Badge variant="outline" class="rounded-md font-mono text-xs" v-else>
                Osoba
            </Badge>
          </template>

          <template #actions="{ item }">
            <DropdownMenu>
              <DropdownMenuTrigger as-child>
                <Button variant="ghost" class="h-8 w-8 p-0">
                  <span class="sr-only">Otevřít menu</span>
                  <MoreHorizontal class="h-4 w-4" />
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent align="end">
                <DropdownMenuLabel>Akce</DropdownMenuLabel>
                <DropdownMenuItem @click="openEdit(item)">
                  <Pencil class="mr-2 h-4 w-4" />
                  Upravit
                </DropdownMenuItem>
                <DropdownMenuItem @click="confirmDelete(item)" class="text-red-600 focus:text-red-600">
                  <Trash class="mr-2 h-4 w-4" />
                  Smazat
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>
          </template>
      </AppDataTable>
    </div>

    <Sheet :open="isSheetOpen" @update:open="isSheetOpen = $event">
      <SheetContent class="w-full sm:max-w-2xl overflow-y-auto">
        <SheetHeader>
          <SheetTitle>{{ selectedCustomer ? 'Upravit zákazníka' : 'Nový zákazník' }}</SheetTitle>
          <SheetDescription>
            {{ selectedCustomer ? 'Upravte údaje o zákazníkovi.' : 'Vytvořte nového zákazníka v databázi.' }}
          </SheetDescription>
        </SheetHeader>
        <CustomerForm 
          :customer="selectedCustomer" 
          @success="handleSuccess" 
        />
      </SheetContent>
    </Sheet>

    <AlertDialog :open="isDeleteDialogOpen" @update:open="isDeleteDialogOpen = $event">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Opravdu chcete smazat tohoto zákazníka?</AlertDialogTitle>
          <AlertDialogDescription>
            Tato akce je nevratná. Zákazník bude přesunut do koše a jeho data nebudou dostupná pro nové rezervace.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel @click="isDeleteDialogOpen = false">Zrušit</AlertDialogCancel>
          <AlertDialogAction @click="deleteCustomer" class="bg-destructive text-destructive-foreground hover:bg-destructive/90">
            Smazat
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  </AppLayout>
</template>
