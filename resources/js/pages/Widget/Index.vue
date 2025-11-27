<script setup lang="ts">
import { ref, computed, onMounted, reactive } from "vue";
import { toast } from "vue-sonner";
import axios from "axios";
import { storeToRefs } from "pinia"; 
import { useBookingStore } from "@/stores/booking";
import { useWidgetCalendar } from "@/composables/useWidgetCalendar";
import { useCurrency } from "@/composables/useCurrency";
import { ServicePriceType } from "@/lib/enums";

// Partials
import StepDates from "./partials/StepDates.vue";
import StepGuest from "./partials/StepGuest.vue";
import StepServices from "./partials/StepServices.vue";
import StepReview from "./partials/StepReview.vue";
import StepSuccess from "./partials/StepSuccess.vue";
import BookingSummary from "./partials/BookingSummary.vue";

// UI Components
import { Button } from "@/components/ui/button";
import { Calendar, User, StickyNote, PawPrint, Info, X, ChevronUp, ChevronLeft, Loader2 } from "lucide-vue-next";
import type { Customer } from "./types";

// --- PROPS ---
const props = defineProps({
  property: { type: Object, default: () => ({}) },
});

// --- STORE SETUP ---
const booking = useBookingStore();
const { customer, startDate, endDate, extras, extraSelection } = storeToRefs(booking);

// --- UTILS ---
const { formatCurrency } = useCurrency();

// --- CONSTANTS ---
const STEPS = [
  { id: 1, label: "Termín", icon: Calendar },
  { id: 2, label: "Údaje", icon: User },
  { id: 3, label: "Služby", icon: PawPrint },
  { id: 4, label: "Kontrola", icon: StickyNote },
];

// --- STATE ---
const currentStep = ref(1);
const processing = ref(false);
const showMobilePriceDetails = ref(false);

const calendar = useWidgetCalendar(props.property?.id);

const dateSelectionHint = computed(() => {
  if (!startDate.value) return "Vyberte datum příjezdu";
  if (startDate.value && !endDate.value) return "Nyní vyberte datum odjezdu";
  return "Termín vybrán";
});

function navigateTo(id: number) {
  if (id < currentStep.value) currentStep.value = id;
}

// --- LOGIC: DATE HELPER ---
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

const selectedTotalPrice = computed(() => {
   const nights = rangeDates.value.slice(0, -1);
   return nights.reduce((sum, iso) => sum + Number(calendar.getDayInfo(iso)?.price || 0), 0);
});

// --- LOGIC: EXTRAS ---
const fieldErrors = reactive<Record<string, string>>({});
const agreeGdpr = ref(false);
const agreeTerms = ref(false);

const validExtras = computed(() => extras.value.filter(e => e?.id));
const selectedExtras = computed(() => validExtras.value.filter(e => (extraSelection.value[e.id] || 0) > 0));

const addonsTotalPrice = computed(() => {
  return selectedExtras.value.reduce((sum, ex) => {
    const qty = extraSelection.value[ex.id] || 0;
    const cost = ex.price_type === ServicePriceType.PerNight ? (ex.price * qty * selectedNights.value) : (ex.price * qty);
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

  const getApiCustomerData = () => ({
    first_name: customer.value.firstName,
    last_name: customer.value.lastName,
    email: customer.value.email,
    phone: customer.value.phone,
    note: customer.value.note
  });

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
    } catch (e: any) {
      toast.error(e.response?.data?.message || e.message || "Chyba ověření dostupnosti.");
    } finally {
      processing.value = false;
    }
    return;
  }

  if (currentStep.value === 2) {
    let isValid = true;
    const requiredFields: (keyof Customer)[] = ['firstName', 'lastName', 'email', 'phone'];
    requiredFields.forEach(field => {
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
       const res = await axios.post(`/api/widgets/${props.property.id}/verify-customer`, { 
         customer: getApiCustomerData() 
       });
       if (res.data.valid) currentStep.value = 3;
       else throw new Error("Neplatné údaje");
    } catch(e: any) {
      toast.error(e.response?.data?.message || "Ověření údajů se nezdařilo.");
    } finally {
      processing.value = false;
    }
    return;
  }

  if (currentStep.value === 3) {
    if (selectedExtras.value.length > 0) {
      processing.value = true;
      try {
        const selections = selectedExtras.value.map(ex => ({ service_id: ex.id, quantity: extraSelection.value[ex.id] }));
        const res = await axios.post(`/api/widgets/${props.property.id}/services/availability`, {
          start_date: startDate.value, end_date: endDate.value, selections
        });
        if (!res.data.available) throw new Error("Některé služby nejsou dostupné.");
      } catch(e: any) {
        toast.error(e.message || "Chyba dostupnosti služeb.");
        processing.value = false;
        return;
      }
    }
    currentStep.value = 4;
    processing.value = false;
    return;
  }

  if (currentStep.value === 4) {
    console.log('Finalizing reservation:', { gdpr: agreeGdpr.value, terms: agreeTerms.value });
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
    } catch (e: any) {
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
  <div class="min-h-screen w-full bg-background text-foreground font-sans pb-24 lg:pb-8">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
      
      <!-- SUCCESS PAGE (Clean Layout) -->
      <div v-if="currentStep === 5">
        <StepSuccess 
           :customer="customer"
           :start-date="startDate"
           :end-date="endDate"
           :calendar="calendar"
        />
      </div>

      <!-- WIZARD LAYOUT -->
      <div v-else>
        <!-- Progress Steps -->
        <div class="mb-10 hidden lg:block">
          <nav aria-label="Progress">
            <ol role="list" class="flex items-center justify-between w-full border-b border-border pb-4">
              <li v-for="(step, index) in STEPS" :key="step.id" class="flex items-center">
                 <button 
                    @click="navigateTo(step.id)"
                    :disabled="step.id > currentStep"
                    class="flex items-center gap-3 group disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    <span class="flex h-8 w-8 items-center justify-center rounded-full text-sm font-semibold transition-colors"
                      :class="[
                        step.id < currentStep ? 'bg-primary text-white' : 
                        step.id === currentStep ? 'bg-primary/10 text-primary ring-2 ring-primary' : 'bg-muted text-muted-foreground'
                      ]">
                      <span v-if="step.id < currentStep">✓</span>
                      <span v-else>{{ step.id }}</span>
                    </span>
                    <span class="text-sm font-medium" :class="step.id === currentStep ? 'text-primary' : 'text-muted-foreground'">
                      {{ step.label }}
                    </span>
                 </button>
                 <div v-if="index !== STEPS.length - 1" class="h-px w-24 bg-border mx-4"></div>
              </li>
            </ol>
          </nav>
        </div>

        <div class="grid grid-cols-1 gap-12 lg:grid-cols-12">
          
          <!-- SIDEBAR SUMMARY -->
          <aside class="lg:col-span-4 xl:col-span-3 order-2 lg:order-1 hidden lg:block">
            <BookingSummary 
               :calendar="calendar"
               :start-date="startDate"
               :end-date="endDate"
               :selected-nights="selectedNights"
               :selected-total-price="selectedTotalPrice"
               :season-label="seasonLabel as string"
               :addons-total-price="addonsTotalPrice"
               :grand-total-price="grandTotalPrice"
            />
          </aside>

          <!-- MAIN CONTENT -->
          <main class="lg:col-span-8 xl:col-span-9 order-1 lg:order-2 space-y-8">
            
            <!-- Mobile Progress Bar -->
            <div class="mb-6 lg:hidden">
              <div class="h-1 w-full bg-muted">
                <div class="h-full bg-primary transition-all" :style="{ width: (currentStep/4)*100 + '%' }"></div>
              </div>
            </div>

            <StepDates 
               v-if="currentStep === 1"
               :calendar="calendar"
               :start-date="startDate"
               :end-date="endDate"
               :date-selection-hint="dateSelectionHint"
               @update:start-date="(v) => booking.setStartDate(v)"
               @update:end-date="(v) => booking.setEndDate(v)"
            />

            <StepGuest 
               v-if="currentStep === 2"
               :customer="customer"
               :field-errors="fieldErrors"
            />

            <StepServices 
               v-if="currentStep === 3"
               :valid-extras="validExtras"
               :extra-selection="extraSelection"
               @update:extraSelection="(id, qty) => booking.setExtraQuantity(id, qty)"
            />

            <StepReview 
               v-if="currentStep === 4"
               :customer="customer"
               :start-date="startDate"
               :end-date="endDate"
               :selected-nights="selectedNights"
               :selected-extras="selectedExtras"
               :extra-selection="extraSelection"
               :grand-total-price="grandTotalPrice"
               :calendar="calendar"
               :agree-gdpr="agreeGdpr"
               :agree-terms="agreeTerms"
               @update:agree-gdpr="(v) => agreeGdpr = v"
               @update:agree-terms="(v) => agreeTerms = v"
               @navigate="navigateTo"
            />

            <!-- Navigation Buttons -->
            <div class="flex justify-between pt-6 border-t border-border">
              <Button 
                v-if="currentStep > 1"
                variant="outline" 
                @click="currentStep--"
                class="hidden lg:flex"
              >
                Zpět
              </Button>
              <div class="flex-1 lg:hidden"></div> <!-- Spacer on mobile -->
              
              <Button 
                @click="nextStep" 
                :disabled="processing"
                class="w-full lg:w-auto ml-auto hidden lg:flex"
              >
                <Loader2 v-if="processing" class="mr-2 h-4 w-4 animate-spin" />
                {{ currentStep === 4 ? 'Dokončit rezervaci' : 'Pokračovat' }}
              </Button>
            </div>
          </main>
        </div>
      </div>

      <!-- Mobile Price Detail Sheet (Fixed Bottom) -->
      <div v-if="currentStep < 5" class="fixed bottom-0 left-0 right-0 bg-background border-t border-border p-4 lg:hidden z-50 safe-area-bottom">
        <!-- Backdrop -->
        <div v-if="showMobilePriceDetails" class="fixed inset-0 bg-black/20 z-[-1]" @click="showMobilePriceDetails = false"></div>
        
        <!-- Expanded Details -->
        <div v-if="showMobilePriceDetails" class="absolute bottom-full left-0 right-0 bg-background border-t border-border p-4 rounded-t-lg animate-in slide-in-from-bottom-10">
            <div class="flex justify-between items-center mb-4 pb-2 border-b border-border">
               <span class="font-semibold text-foreground">Rozpis ceny</span>
               <Button variant="ghost" size="icon" @click="showMobilePriceDetails = false" class="h-9 w-9 text-muted-foreground">
                 <X class="h-4 w-4" />
               </Button>
            </div>
            <div class="space-y-3 text-sm">
               <div class="flex justify-between text-muted-foreground">
                  <span>Ubytování ({{ selectedNights }} nocí)</span>
                  <span class="font-medium text-foreground">{{ formatCurrency(selectedTotalPrice) }}</span>
               </div>
               <div v-if="addonsTotalPrice > 0" class="flex justify-between text-muted-foreground">
                  <span>Služby a poplatky</span>
                  <span class="font-medium text-foreground">{{ formatCurrency(addonsTotalPrice) }}</span>
               </div>
               <div class="pt-3 border-t border-border flex justify-between items-end">
                  <span class="font-bold text-foreground">Celkem</span>
                  <span class="text-xl font-bold text-primary">{{ formatCurrency(grandTotalPrice) }}</span>
               </div>
            </div>
        </div>

        <!-- Collapsed Bar -->
        <div class="flex items-center gap-4">
           <div class="flex-1 cursor-pointer" @click="showMobilePriceDetails = !showMobilePriceDetails">
              <div class="text-xs text-muted-foreground flex items-center gap-1">
                 Celkem za pobyt <Info class="h-3 w-3" />
              </div>
              <div class="flex items-center gap-2">
                 <span class="text-lg font-bold text-primary">{{ formatCurrency(grandTotalPrice) }}</span>
                 <ChevronUp class="h-4 w-4 text-muted-foreground transition-transform duration-200" :class="{'rotate-180': showMobilePriceDetails}" />
              </div>
           </div>
           
           <div class="flex gap-2">
             <Button 
                v-if="currentStep > 1"
                variant="outline" 
                size="icon"
                @click="currentStep--"
                class="h-9 w-9 shrink-0"
              >
                <ChevronLeft class="h-5 w-5" />
              </Button>
             <Button 
                @click="nextStep" 
                :disabled="processing"
                class="h-9 px-4 font-semibold border border-primary text-white"
             >
                <Loader2 v-if="processing" class="mr-2 h-4 w-4 animate-spin" />
                {{ currentStep === 4 ? 'Dokončit' : 'Pokračovat' }}
             </Button>
           </div>
        </div>
      </div>

    </div>
  </div>
</template>

<style scoped>
.safe-area-bottom {
  padding-bottom: env(safe-area-inset-bottom, 1rem);
}
</style>
