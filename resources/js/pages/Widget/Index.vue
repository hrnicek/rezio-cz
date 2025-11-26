<script setup lang="ts">
import { ref, computed, onMounted, reactive } from "vue";
import { Link } from "@inertiajs/vue3";
import { toast } from "vue-sonner";
import axios from "axios";
import { storeToRefs } from "pinia"; 
import { useBookingStore } from "@/stores/booking";

// UI Components
import { Button } from "@/components/ui/button";
import { Checkbox } from "@/components/ui/checkbox";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import {
  ChevronLeft, ChevronRight, CheckCircle,
  Calendar, User, StickyNote, PawPrint, Loader2
} from "lucide-vue-next";

// --- PROPS ---
const props = defineProps({
  property: { type: Object, default: () => ({}) },
});

// --- STORE SETUP ---
const booking = useBookingStore();
const { customer, startDate, endDate, extras, extraSelection } = storeToRefs(booking);

// --- UTILS ---
const currencyFormatter = new Intl.NumberFormat("cs-CZ", {
  style: "currency",
  currency: "CZK",
  maximumFractionDigits: 0,
});
const currency = (n) => currencyFormatter.format(Number(n));

// --- CONSTANTS ---
const WEEK_DAYS = ["Po", "Út", "St", "Čt", "Pá", "So", "Ne"];
const STEPS = [
  { id: 1, label: "Termín", icon: Calendar },
  { id: 2, label: "Údaje", icon: User },
  { id: 3, label: "Služby", icon: PawPrint },
  { id: 4, label: "Kontrola", icon: StickyNote },
];

// --- STATE ---
const currentStep = ref(1);
const processing = ref(false);

function navigateTo(id) {
  if (id < currentStep.value) currentStep.value = id;
}

// --- LOGIC: CALENDAR ---
const useCalendar = () => {
  const now = new Date();
  const month = ref(now.getMonth() + 1);
  const year = ref(now.getFullYear());
  const daysData = ref([]); 
  const loading = ref(false);

  const pad = (n) => String(n).padStart(2, "0");
  const parseISO = (s) => { const [Y, M, D] = s.split("-").map(Number); return new Date(Y, M - 1, D); };
  const formatDate = (iso) => iso ? new Date(iso).toLocaleDateString("cs-CZ", { day: 'numeric', month: 'long', year: 'numeric' }) : "";
  const toISO = (d) => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
  
  const monthLabel = computed(() => new Date(year.value, month.value - 1, 1).toLocaleString("cs-CZ", { month: "long" }));
  
  const changeMonth = (delta) => {
    let newM = month.value + delta;
    if (newM > 12) { newM = 1; year.value++; }
    else if (newM < 1) { newM = 12; year.value--; }
    month.value = newM;
    fetchCalendarData();
  };

  const fetchCalendarData = async () => {
    if (!props.property?.id) return;
    loading.value = true;
    try {
      const prevM = month.value === 1 ? 12 : month.value - 1;
      const prevY = month.value === 1 ? year.value - 1 : year.value;
      const [curr, prev] = await Promise.all([
        axios.get(`/api/widgets/${props.property.id}`, { params: { month: month.value, year: year.value } }),
        axios.get(`/api/widgets/${props.property.id}`, { params: { month: prevM, year: prevY } }),
      ]);
      daysData.value = [...prev.data.days, ...curr.data.days];
    } catch {
      toast.error("Chyba načítání kalendáře");
    } finally {
      loading.value = false;
    }
  };

  const cells = computed(() => {
    const firstDayIdx = new Date(year.value, month.value - 1, 1).getDay() || 7; 
    const offset = firstDayIdx - 1; 
    const prevM = month.value === 1 ? 12 : month.value - 1;
    const prevY = month.value === 1 ? year.value - 1 : year.value;
    const prevDaysCount = new Date(prevY, prevM, 0).getDate();
    
    const prevCells = Array.from({ length: offset }, (_, i) => {
      const d = prevDaysCount - offset + 1 + i;
      return { date: `${prevY}-${pad(prevM)}-${pad(d)}`, day: d, inCurrent: false };
    });
    
    const daysInMonth = new Date(year.value, month.value, 0).getDate();
    const currCells = Array.from({ length: daysInMonth }, (_, i) => {
       return { date: `${year.value}-${pad(month.value)}-${pad(i + 1)}`, day: i + 1, inCurrent: true };
    });
    return [...prevCells, ...currCells];
  });

  const getDayInfo = (dateStr) => daysData.value.find(x => x.date === dateStr);
  
  return { month, year, loading, monthLabel, cells, daysData, changeMonth, fetchCalendarData, getDayInfo, parseISO, formatDate, toISO };
};

const calendar = useCalendar();

// --- LOGIC: DATE SELECTION ---
const hoverDate = ref(null);

const rangeDates = computed(() => {
  if (!startDate.value || !endDate.value) return [];
  const start = calendar.parseISO(startDate.value);
  const end = calendar.parseISO(endDate.value);
  const dates = [];
  for (let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
    dates.push(calendar.toISO(d));
  }
  return dates;
});

const selectedNights = computed(() => Math.max(0, rangeDates.value.length - 1));

const getCellStyles = (dateStr) => {
  const info = calendar.getDayInfo(dateStr);
  const isAvailable = info?.available !== false; 
  const isBlackout = !!info?.blackout;

  if (isBlackout) return 'bg-slate-100 text-slate-300 cursor-not-allowed';
  if (!isAvailable) return 'bg-red-50 text-red-300 cursor-not-allowed';

  const d = calendar.parseISO(dateStr);
  const s = startDate.value ? calendar.parseISO(startDate.value) : null;
  const e = endDate.value ? calendar.parseISO(endDate.value) : null;
  const h = hoverDate.value ? calendar.parseISO(hoverDate.value) : null;

  if (s && d.getTime() === s.getTime()) return 'bg-emerald-600 text-white font-medium';
  if (e && d.getTime() === e.getTime()) return 'bg-emerald-600 text-white font-medium';
  if (s && e && d > s && d < e) return 'bg-emerald-50 text-emerald-900 border-emerald-100';
  if (s && !e && h) {
    const min = Math.min(s, h);
    const max = Math.max(s, h);
    if (d >= min && d <= max) return 'bg-emerald-50/50 text-emerald-700 dashed-border';
  }

  return 'bg-white hover:bg-slate-50 text-slate-700 border-slate-100';
};

const handleDateClick = (date) => {
  const info = calendar.getDayInfo(date);
  if (info?.blackout || info?.available === false) return;

  if (!startDate.value || (startDate.value && endDate.value)) {
    booking.setStartDate(date);
    booking.setEndDate(null);
  } else {
    if (new Date(date) < new Date(startDate.value)) {
       booking.setEndDate(startDate.value);
       booking.setStartDate(date);
    } else {
       booking.setEndDate(date);
    }
  }
};

const selectedTotalPrice = computed(() => {
   const nights = rangeDates.value.slice(0, -1);
   return nights.reduce((sum, iso) => sum + Number(calendar.getDayInfo(iso)?.price || 0), 0);
});

// --- LOGIC: EXTRAS ---
const fieldErrors = reactive({});
const agreeGdpr = ref(false);
const agreeTerms = ref(false);

const validExtras = computed(() => extras.value.filter(e => e?.id));
const selectedExtras = computed(() => validExtras.value.filter(e => (extraSelection.value[e.id] || 0) > 0));

const addonsTotalPrice = computed(() => {
  return selectedExtras.value.reduce((sum, ex) => {
    const qty = extraSelection.value[ex.id] || 0;
    const cost = ex.price_type === 'per_day' ? (ex.price * qty * selectedNights.value) : (ex.price * qty);
    return sum + cost;
  }, 0);
});

const grandTotalPrice = computed(() => selectedTotalPrice.value + addonsTotalPrice.value);

const seasonsInRange = computed(() => {
  if (!startDate.value || !endDate.value) return [];
  return Array.from(new Set(rangeDates.value
    .map((d) => calendar.getDayInfo(d)?.season?.name)
    .filter((n) => !!n)));
});

const seasonLabel = computed(() => {
  if (!startDate.value || !endDate.value) return '';
  if (seasonsInRange.value.length === 0) return 'Výchozí';
  if (seasonsInRange.value.length === 1) return seasonsInRange.value[0];
  return 'Více sezón';
});

// --- NAVIGATION ACTIONS ---
const nextStep = async () => {
  if (processing.value) return;

  // --- Helper to format customer data for API (camelCase -> snake_case) ---
  const getApiCustomerData = () => ({
    first_name: customer.value.firstName,
    last_name: customer.value.lastName,
    email: customer.value.email,
    phone: customer.value.phone,
    note: customer.value.note
  });

  // STEP 1: DATE VERIFICATION
  if (currentStep.value === 1) {
    if (!startDate.value || !endDate.value) {
      toast.error("Prosím vyberte datum příjezdu a odjezdu.");
      return;
    }
    processing.value = true;
    try {
      const res = await axios.post(`/api/widgets/${props.property.id}/verify`, { 
        start_date: startDate.value, 
        end_date: endDate.value 
      });
      if (!res.data.available) throw new Error("Vybraný termín je obsazen.");
      currentStep.value = 2;
    } catch (e) {
      toast.error(e.response?.data?.message || e.message || "Chyba ověření dostupnosti.");
    } finally {
      processing.value = false;
    }
    return;
  }

  // STEP 2: CUSTOMER VALIDATION
  if (currentStep.value === 2) {
    let isValid = true;
    ['firstName', 'lastName', 'email', 'phone'].forEach(field => {
       if (!customer.value[field] || customer.value[field].length < 2) {
         fieldErrors[field] = "Povinné pole"; isValid = false;
       } else {
         fieldErrors[field] = "";
       }
    });
    
    if (!isValid) {
      toast.error("Vyplňte prosím všechny údaje.");
      return;
    }
    
    processing.value = true;
    try {
       // FIX: Use mapped object here
       const res = await axios.post(`/api/widgets/${props.property.id}/verify-customer`, { 
         customer: getApiCustomerData() 
       });
       if (res.data.valid) currentStep.value = 3;
       else throw new Error("Neplatné údaje");
    } catch(e) {
      console.error(e);
      toast.error(e.response?.data?.message || "Ověření údajů se nezdařilo.");
    } finally {
      processing.value = false;
    }
    return;
  }

  // STEP 3: EXTRAS CHECK
  if (currentStep.value === 3) {
    if (selectedExtras.value.length > 0) {
      processing.value = true;
      try {
        const selections = selectedExtras.value.map(ex => ({ service_id: ex.id, quantity: extraSelection.value[ex.id] }));
        const res = await axios.post(`/api/widgets/${props.property.id}/services/availability`, {
          start_date: startDate.value, end_date: endDate.value, selections
        });
        if (!res.data.available) throw new Error("Některé služby nejsou dostupné.");
      } catch(e) {
        toast.error(e.message || "Chyba dostupnosti služeb.");
        processing.value = false;
        return;
      }
    }
    currentStep.value = 4;
    processing.value = false;
    return;
  }

  // STEP 4: SUBMIT
  if (currentStep.value === 4) {
    if (!agreeGdpr.value || !agreeTerms.value) {
      toast.error("Musíte souhlasit s podmínkami.");
      return;
    }
    processing.value = true;
    try {
      await axios.post(`/api/widgets/${props.property.id}/reservations`, {
        start_date: startDate.value,
        end_date: endDate.value,
        customer: getApiCustomerData(),
        addons: selectedExtras.value.map(ex => ({ service_id: ex.id, quantity: extraSelection.value[ex.id] })),
        grand_total: grandTotalPrice.value
      });
      currentStep.value = 5;
    } catch (e) {
      toast.error(e.response?.data?.message || "Rezervaci se nepodařilo odeslat.");
    } finally {
      processing.value = false;
    }
  }
};

onMounted(async () => {
  await calendar.fetchCalendarData();
  try {
    const res = await axios.get(`/api/widgets/${props.property.id}/services`);
    booking.setExtras(res.data.services || []);
  } catch {}
});
</script>

<template>
  <div class="min-h-screen w-full bg-white text-gray-900 font-sans pb-24 lg:pb-8">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
      
      <div class="mb-10 hidden lg:block">
        <nav aria-label="Progress">
          <ol role="list" class="flex items-center justify-between w-full border-b border-gray-200 pb-4">
            <li v-for="(step, index) in STEPS" :key="step.id" class="flex items-center">
               <button 
                  @click="navigateTo(step.id)"
                  :disabled="step.id > currentStep"
                  class="flex items-center gap-3 group disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <span class="flex h-8 w-8 items-center justify-center rounded-full text-sm font-semibold transition-colors"
                    :class="[
                      step.id < currentStep ? 'bg-green-600 text-white' : 
                      step.id === currentStep ? 'bg-green-100 text-green-700 ring-2 ring-green-600' : 'bg-gray-100 text-gray-500'
                    ]">
                    <span v-if="step.id < currentStep">✓</span>
                    <span v-else>{{ step.id }}</span>
                  </span>
                  <span class="text-sm font-medium" :class="step.id === currentStep ? 'text-green-700' : 'text-gray-600'">
                    {{ step.label }}
                  </span>
               </button>
               <div v-if="index !== STEPS.length - 1" class="h-px w-24 bg-gray-200 mx-4"></div>
            </li>
          </ol>
        </nav>
      </div>

      <div class="grid grid-cols-1 gap-12 lg:grid-cols-12">
        
        <aside class="lg:col-span-4 xl:col-span-3 order-2 lg:order-1">
          <div class="sticky top-8">
            <div class="rounded-lg border border-gray-200 bg-white p-6">
              <h3 class="mb-6 flex items-center gap-2 text-lg font-medium text-gray-900">
                <StickyNote class="h-5 w-5 text-gray-400" />
                Rezervace
              </h3>
              
              <div class="space-y-4 text-sm">
                <div class="pb-4 border-b border-gray-100">
                  <div class="flex justify-between text-gray-500 mb-1">
                    <span>Termín</span>
                  </div>
                  <div class="font-medium text-lg text-gray-900">
                     <div v-if="startDate">{{ calendar.formatDate(startDate) }}</div>
                     <div v-if="endDate" class="text-gray-400 text-xs">až</div>
                     <div v-if="endDate">{{ calendar.formatDate(endDate) }}</div>
                     <div v-if="!startDate" class="text-gray-400 italic text-sm">Vyberte termín</div>
                  </div>
                </div>

              <div class="space-y-2">
                <div class="flex justify-between text-gray-600">
                  <span>Nocí</span>
                  <span class="font-medium text-gray-900">{{ selectedNights }}</span>
                </div>
                <div class="flex justify-between text-gray-600">
                  <span>Cena ubytování</span>
                  <span class="font-medium text-gray-900">{{ currency(selectedTotalPrice) }}</span>
                </div>
                <div class="flex justify-between text-gray-600">
                  <span>Sezóna</span>
                  <span class="font-medium text-gray-900">{{ seasonLabel }}</span>
                </div>
                <div v-if="addonsTotalPrice > 0" class="flex justify-between text-gray-600">
                  <span>Služby</span>
                  <span class="font-medium text-gray-900">{{ currency(addonsTotalPrice) }}</span>
                </div>
              </div>

                <div class="border-t border-gray-200 pt-4 mt-4">
                  <div class="flex items-end justify-between">
                    <span class="font-medium text-gray-900">Celkem</span>
                    <span class="text-xl font-bold text-green-700">{{ currency(grandTotalPrice) }}</span>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="mt-6 text-xs text-gray-400 px-2">
               <div class="flex items-center gap-2 mb-2"><span class="h-2 w-2 rounded-full bg-green-600"></span> Vybráno</div>
               <div class="flex items-center gap-2 mb-2"><span class="h-2 w-2 rounded-full bg-red-300"></span> Obsazeno</div>
               <div class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-gray-200"></span> Nedostupné</div>
            </div>
          </div>
        </aside>

        <main class="lg:col-span-8 xl:col-span-9 order-1 lg:order-2">
          
          <div class="mb-6 lg:hidden">
            <div class="h-1 w-full bg-gray-100">
              <div class="h-full bg-green-600 transition-all" :style="{ width: (currentStep/4)*100 + '%' }"></div>
            </div>
          </div>

          <div v-if="currentStep === 1" class="space-y-6">
            <div class="flex items-center justify-between">
              <h1 class="text-2xl font-medium text-gray-900">Vyberte termín</h1>
              <div class="flex items-center gap-1 bg-white border border-gray-200 rounded-md">
                 <Button variant="ghost" size="icon" @click="calendar.changeMonth(-1)" class="h-9 w-9 text-gray-500 hover:text-green-700">
                    <ChevronLeft class="h-4 w-4" />
                 </Button>
                 <span class="w-32 text-center text-sm font-semibold text-gray-700">
                    {{ calendar.monthLabel.value }} {{ calendar.year.value }}
                 </span>
                 <Button variant="ghost" size="icon" @click="calendar.changeMonth(1)" class="h-9 w-9 text-gray-500 hover:text-green-700">
                    <ChevronRight class="h-4 w-4" />
                 </Button>
              </div>
            </div>

            <div class="border border-gray-200 rounded-lg overflow-hidden select-none">
               <div class="grid grid-cols-7 bg-gray-50 border-b border-gray-200">
                  <div v-for="d in WEEK_DAYS" :key="d" class="py-2 text-center text-xs font-semibold text-gray-500">
                    {{ d }}
                  </div>
               </div>
               
               <div class="grid grid-cols-7 bg-white relative">
                  <div v-if="calendar.loading.value" class="absolute inset-0 bg-white/80 z-10 flex items-center justify-center">
                     <Loader2 class="h-6 w-6 animate-spin text-green-600" />
                  </div>

                  <button
                      v-for="(cell, idx) in calendar.cells.value"
                      :key="idx"
                      @click="handleDateClick(cell.date)"
                      @mouseenter="hoverDate = cell.date"
                      @mouseleave="hoverDate = null"
                      class="h-20 sm:h-24 border-r border-b border-gray-100 p-2 flex flex-col justify-between transition-colors focus:outline-none focus:ring-2 focus:ring-inset focus:ring-green-500"
                      :class="[
                        getCellStyles(cell.date), 
                        (idx + 1) % 7 === 0 ? 'border-r-0' : '' 
                      ]"
                  >
                      <span class="text-sm font-medium self-end" :class="{'opacity-25': !cell.inCurrent}">{{ cell.day }}</span>
                      <span v-if="calendar.getDayInfo(cell.date)?.price" class="text-xs font-medium self-start">
                         {{ currency(calendar.getDayInfo(cell.date).price) }}
                      </span>
                  </button>
               </div>
            </div>
          </div>

          <div v-if="currentStep === 2" class="space-y-8">
             <h1 class="text-2xl font-medium text-gray-900">Kontaktní údaje</h1>
             <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div v-for="field in ['firstName', 'lastName', 'email', 'phone']" :key="field" class="space-y-1">
                   <Label class="text-sm text-gray-600 capitalize">
                      {{ {firstName:'Jméno', lastName:'Příjmení', email:'E-mail', phone:'Telefon'}[field] }}
                   </Label>
                   <Input 
                      v-model="customer[field]" 
                      class="rounded-md border-gray-300 focus:border-green-500 focus:ring-green-500"
                      :class="{'border-red-500': fieldErrors[field]}"
                   />
                   <span class="text-xs text-red-500 min-h-[16px] block">{{ fieldErrors[field] }}</span>
                </div>
                <div class="md:col-span-2 space-y-1">
                   <Label class="text-sm text-gray-600">Poznámka</Label>
                   <Textarea v-model="customer.note" class="rounded-md border-gray-300 focus:border-green-500 focus:ring-green-500" rows="3" />
                </div>
             </div>
          </div>

          <div v-if="currentStep === 3" class="space-y-8">
             <h1 class="text-2xl font-medium text-gray-900">Doplňkové služby</h1>
             <div class="grid gap-4 sm:grid-cols-2">
                <div 
                  v-for="ex in validExtras" 
                  :key="ex.id"
                  class="flex flex-col justify-between rounded-lg border p-5 transition-colors"
                  :class="extraSelection[ex.id] > 0 ? 'border-green-500 bg-green-50/50' : 'border-gray-200 bg-white hover:border-green-300'"
                >
                   <div>
                      <div class="flex justify-between items-start mb-2">
                         <h4 class="font-medium text-gray-900">{{ ex.name }}</h4>
                         <span class="font-bold text-green-700">{{ currency(ex.price) }}</span>
                      </div>
                      <p class="text-sm text-gray-500 mb-4">{{ ex.description }}</p>
                   </div>
                   <div class="flex items-center justify-end gap-3 pt-4 mt-auto border-t border-gray-100">
                      <Button variant="outline" size="sm" class="h-8 w-8 p-0 rounded-full" 
                        @click="booking.setExtraQuantity(ex.id, (extraSelection[ex.id]||0) - 1)" :disabled="!extraSelection[ex.id]">-</Button>
                      <span class="font-medium w-6 text-center">{{ extraSelection[ex.id] || 0 }}</span>
                      <Button variant="outline" size="sm" class="h-8 w-8 p-0 rounded-full" 
                        @click="booking.setExtraQuantity(ex.id, (extraSelection[ex.id]||0) + 1)" :disabled="(extraSelection[ex.id]||0) >= ex.max_quantity">+</Button>
                   </div>
                </div>
             </div>
          </div>

          <div v-if="currentStep === 4" class="space-y-8">
             <h1 class="text-2xl font-medium text-gray-900">Kontrola údajů</h1>
             <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 grid sm:grid-cols-2 gap-6">
                <div>
                   <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Termín</h4>
                   <p class="text-gray-900 font-medium">{{ calendar.formatDate(startDate) }} — {{ calendar.formatDate(endDate) }}</p>
                   <p class="text-sm text-gray-500">{{ selectedNights }} nocí</p>
                </div>
                <div>
                   <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Host</h4>
                   <p class="text-gray-900 font-medium">{{ customer.firstName }} {{ customer.lastName }}</p>
                   <p class="text-sm text-gray-500">{{ customer.email }}</p>
                </div>
                <div class="sm:col-span-2">
                   <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Sezóna</h4>
                   <p class="text-gray-900 font-medium">{{ seasonsInRange.length > 1 ? 'Více sezón' : (seasonLabel || '-') }}</p>
                   <p v-if="seasonsInRange.length > 1" class="text-sm text-gray-500">{{ seasonsInRange.join(', ') }}</p>
                </div>
             </div>

             <div class="space-y-4">
                <div class="flex items-center gap-3">
                   <Checkbox id="gdpr" v-model="agreeGdpr" class="text-green-600 focus:ring-green-500" />
                   <Label for="gdpr" class="text-gray-700 cursor-pointer">Souhlasím se zpracováním osobních údajů</Label>
                </div>
                <div class="flex items-center gap-3">
                   <Checkbox id="terms" v-model="agreeTerms" class="text-green-600 focus:ring-green-500" />
                   <Label for="terms" class="text-gray-700 cursor-pointer">Souhlasím s obchodními podmínkami</Label>
                </div>
             </div>
          </div>

          <div v-if="currentStep === 5" class="py-16 text-center">
             <div class="inline-flex h-20 w-20 items-center justify-center rounded-full bg-green-100 text-green-600 mb-6">
                <CheckCircle class="h-10 w-10" />
             </div>
             <h2 class="text-3xl font-medium text-gray-900 mb-4">Rezervace odeslána</h2>
             <p class="text-gray-500 mb-8 max-w-md mx-auto">Potvrzení jsme odeslali na <strong>{{ customer.email }}</strong>.</p>
             <Button as-child class="bg-green-600 hover:bg-green-700 text-white">
                <Link :href="route('welcome')">Zpět na úvod</Link>
             </Button>
          </div>

          <div v-if="currentStep < 5" class="mt-8 pt-8 border-t border-gray-100 flex justify-between">
             <Button 
               v-if="currentStep > 1" 
               variant="outline" 
               @click="navigateTo(currentStep - 1)"
               class="border-gray-300 text-gray-700 hover:bg-gray-50"
             >
               Zpět
             </Button>
             <div v-else></div>

             <Button 
               @click="nextStep" 
               :disabled="processing"
               class="bg-green-600 hover:bg-green-700 text-white min-w-[150px]"
             >
               <Loader2 v-if="processing" class="mr-2 h-4 w-4 animate-spin" />
               <span v-else>{{ currentStep === 4 ? 'Dokončit rezervaci' : 'Pokračovat' }}</span>
               <ChevronRight v-if="!processing" class="ml-2 h-4 w-4" />
             </Button>
          </div>

        </main>
      </div>
    </div>

    <div v-if="currentStep < 5" class="fixed bottom-0 inset-x-0 bg-white border-t border-gray-200 p-4 lg:hidden z-50">
       <div class="flex items-center justify-between gap-4 max-w-md mx-auto">
          <div>
             <div class="text-xs text-gray-500 uppercase">Celkem</div>
             <div class="text-lg font-bold text-green-700">{{ currency(grandTotalPrice) }}</div>
          </div>
          <Button 
             @click="nextStep" 
             :disabled="processing"
             class="bg-green-600 hover:bg-green-700 text-white px-8"
          >
             <Loader2 v-if="processing" class="h-4 w-4 animate-spin" />
             <span v-else>{{ currentStep === 4 ? 'Odeslat' : 'Dále' }}</span>
          </Button>
       </div>
    </div>

  </div>
</template>
