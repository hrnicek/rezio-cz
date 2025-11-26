import { ref, computed } from "vue";
import axios from "axios";
import { toast } from "vue-sonner";
import type { CalendarService } from "@/pages/Widget/types";

export const useWidgetCalendar = (propertyId: number): CalendarService => {
  const now = new Date();
  const month = ref(now.getMonth() + 1);
  const year = ref(now.getFullYear());
  const daysData = ref<any[]>([]); 
  const loading = ref(false);

  const pad = (n: number) => String(n).padStart(2, "0");
  const parseISO = (s: string) => { const [Y, M, D] = s.split("-").map(Number); return new Date(Y, M - 1, D); };
  const formatDate = (iso: string | null) => iso ? new Date(iso).toLocaleDateString("cs-CZ", { day: 'numeric', month: 'long', year: 'numeric' }) : "";
  const toISO = (d: Date) => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
  
  const monthLabel = computed(() => new Date(year.value, month.value - 1, 1).toLocaleString("cs-CZ", { month: "long" }));
  
  const changeMonth = (delta: number) => {
    let newM = month.value + delta;
    if (newM > 12) { newM = 1; year.value++; }
    else if (newM < 1) { newM = 12; year.value--; }
    month.value = newM;
    fetchCalendarData();
  };

  const fetchCalendarData = async () => {
    if (!propertyId) return;
    loading.value = true;
    try {
      const prevM = month.value === 1 ? 12 : month.value - 1;
      const prevY = month.value === 1 ? year.value - 1 : year.value;
      const [curr, prev] = await Promise.all([
        axios.get(`/api/widgets/${propertyId}`, { params: { month: month.value, year: year.value } }),
        axios.get(`/api/widgets/${propertyId}`, { params: { month: prevM, year: prevY } }),
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

  const getDayInfo = (dateStr: string) => daysData.value.find(x => x.date === dateStr);
  
  return { month, year, loading, monthLabel, cells, daysData, changeMonth, fetchCalendarData, getDayInfo, parseISO, formatDate, toISO };
};
