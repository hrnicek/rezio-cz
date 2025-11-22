<template>
  <div class="min-h-screen w-full bg-neutral-50 text-gray-900 font-sans">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">
        
        <!-- SIDEBAR: Progress & Summary -->
        <aside class="lg:col-span-3 xl:col-span-3">
          <div class="sticky top-8 space-y-8">
            

            <!-- Live Summary (Desktop Sticky) -->
            <div class="hidden rounded-xl border border-gray-200 bg-white p-5 lg:block">
              <h3 class="mb-4 flex items-center gap-2 font-medium text-gray-900">
                <StickyNote class="h-4 w-4 text-gray-400" />
                Shrnutí rezervace
              </h3>
              
              <div class="space-y-4 text-sm">
                <!-- Dates -->
                <div class="flex justify-between border-b border-gray-100 pb-3">
                  <span class="text-gray-500">Termín</span>
                  <div class="text-right">
                    <div v-if="startDate && endDate" class="font-medium text-gray-900">
                      {{ formatDate(startDate) }} <br> {{ formatDate(endDate) }}
                    </div>
                    <span v-else class="text-gray-400 italic">Vyberte termín</span>
                  </div>
                </div>

                <!-- Nights -->
                <div class="flex justify-between border-b border-gray-100 pb-3">
                  <span class="text-gray-500">Délka pobytu</span>
                  <span class="font-medium text-gray-900">{{ selectedNights }} nocí</span>
                </div>

                <!-- Base Price -->
                <div class="flex justify-between py-1">
                  <span class="text-gray-500">Ubytování</span>
                  <span class="font-medium text-gray-900">{{ currency(selectedTotalPrice) }}</span>
                </div>

                <!-- Extras -->
                <div v-if="addonsTotalPrice > 0" class="flex justify-between py-1">
                  <span class="text-gray-500">Doplňkové služby</span>
                  <span class="font-medium text-gray-900">{{ currency(addonsTotalPrice) }}</span>
                </div>

                <!-- Total -->
                <div class="mt-4 border-t border-gray-200 pt-4">
                  <div class="flex items-end justify-between">
                    <span class="font-medium text-gray-900">Celkem</span>
                    <span class="text-xl font-semibold text-primary">{{ currency(grandTotalPrice) }}</span>
                  </div>
                </div>
              </div>
              
            </div>
            <div class="hidden lg:flex items-center gap-3 text-[11px] text-gray-600">
              <div class="flex items-center gap-1">
                <span class="inline-block h-2 w-2 rounded-full bg-green-500"></span>
                <span>Volné</span>
              </div>
              <div class="flex items-center gap-1">
                <span class="inline-block h-2 w-2 rounded-full bg-red-500"></span>
                <span>Obsazené</span>
              </div>
              <div class="flex items-center gap-1">
                <span class="inline-block h-2 w-2 rounded-full bg-orange-500"></span>
                <span>Nedostupné</span>
              </div>
            </div>
            <div class="hidden lg:block">
              <div class="mt-4">
                <div class="mb-2 text-[11px] font-medium text-gray-500">Dokumenty</div>
                <div class="flex flex-col gap-1 text-[11px]">
                  <Link href="/vseobecne-obchodni-podminky" class="flex items-center justify-between gap-2 text-gray-600 hover:text-gray-900">
                    <span>Všeobecné obchodní podmínky</span>
                    <ChevronRight class="h-4 w-4 text-gray-400" />
                  </Link>
                  <Link href="/zasady-zpracovani-osobnich-udaju" class="flex items-center justify-between gap-2 text-gray-600 hover:text-gray-900">
                    <span>Zásady zpracování osobních údajů</span>
                    <ChevronRight class="h-4 w-4 text-gray-400" />
                  </Link>
                  <Link href="/ubytovaci-rad" class="flex items-center justify-between gap-2 text-gray-600 hover:text-gray-900">
                    <span>Ubytovací řád</span>
                    <ChevronRight class="h-4 w-4 text-gray-400" />
                  </Link>
                </div>
              </div>
            </div>
          </div>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="lg:col-span-9 xl:col-span-9">
          <div class="mb-3 hidden lg:block">
            <ol class="flex items-center gap-2">
              <li v-for="item in stepItems" :key="item.id">
                <button
                  @click="navigateTo(item.id)"
                  :disabled="!canNavigateTo(item.id)"
                  class="group flex items-center gap-2 rounded-md border border-transparent px-3 py-1 text-sm"
                  :class="[
                    step === item.id 
                      ? 'bg-white border-gray-200 shadow-sm' 
                      : canNavigateTo(item.id) 
                        ? 'hover:bg-gray-100' 
                        : 'opacity-50 cursor-not-allowed'
                  ]"
                >
                  <div 
                    class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md border"
                    :class="[
                      step === item.id ? 'border-primary bg-primary/5 text-primary' :
                      step > item.id ? 'border-primary bg-primary/5 text-primary' :
                      'border-gray-200 bg-gray-50 text-gray-400'
                    ]"
                  >
                    <CheckCircle v-if="step > item.id" class="h-4 w-4" />
                    <component v-else :is="item.icon" class="h-3.5 w-3.5" />
                  </div>
                  <span :class="step === item.id ? 'text-gray-900' : 'text-gray-600'">{{ item.label }}</span>
                </button>
              </li>
            </ol>
          </div>
          <div class="min-h-[600px] rounded-xl border border-gray-200 bg-white p-4 sm:p-6 lg:p-8">
            <div class="mb-3">
              <div class="mb-4 block lg:hidden">
                <div class="h-2 w-full overflow-hidden rounded-full bg-gray-200">
                  <div class="h-full bg-primary transition-all duration-500" :style="{ width: progressPercent + '%' }"></div>
                </div>
                <div class="mt-2 flex justify-between text-xs text-gray-500">
                  <span>Start</span>
                  <span>Dokončení</span>
                </div>
              </div>
              <div class="block lg:hidden">
                <div class="mb-2 text-[11px] font-medium text-gray-500">Dokumenty</div>
                <div class="flex flex-col gap-1 text-[11px]">
                  <Link href="/vseobecne-obchodni-podminky" class="flex items-center justify-between gap-2 text-gray-600 hover:text-gray-900">
                    <span>Všeobecné obchodní podmínky</span>
                    <ChevronRight class="h-4 w-4 text-gray-400" />
                  </Link>
                  <Link href="/zasady-zpracovani-osobnich-udaju" class="flex items-center justify-between gap-2 text-gray-600 hover:text-gray-900">
                    <span>Zásady zpracování osobních údajů</span>
                    <ChevronRight class="h-4 w-4 text-gray-400" />
                  </Link>
                  <Link href="/ubytovaci-rad" class="flex items-center justify-between gap-2 text-gray-600 hover:text-gray-900">
                    <span>Ubytovací řád</span>
                    <ChevronRight class="h-4 w-4 text-gray-400" />
                  </Link>
                </div>
              </div>
            </div>
            
            
            <!-- Step 1: Calendar -->
            <div v-if="step === 1" class="space-y-4">
              <header class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                  <h1 class="text-2xl font-medium text-gray-900">Vyberte termín</h1>
                  <p class="text-gray-500">Klikněte na datum příjezdu a poté na datum odjezdu.</p>
                </div>
                <div class="flex items-center gap-2">
                  <button 
                    v-if="canGoPrev"
                    @click="prevMonth"
                    class="flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 text-gray-600 transition-colors hover:border-gray-300 hover:bg-gray-50"
                  >
                    <ChevronLeft class="h-5 w-5" />
                  </button>
                  <div class="relative" ref="monthPickerEl">
                    <button
                      type="button"
                      @click="monthPickerOpen = !monthPickerOpen"
                      :aria-expanded="monthPickerOpen ? 'true' : 'false'"
                      aria-haspopup="listbox"
                      class="inline-flex items-center gap-1 rounded-lg px-3 py-2 font-medium text-gray-900 hover:bg-gray-50"
                    >
                      <span>{{ monthLabel }} {{ year }}</span>
                      <ChevronDown class="h-4 w-4 text-gray-400" />
                    </button>
                    <div
                      v-if="monthPickerOpen"
                      class="absolute left-1/2 z-10 mt-2 w-56 -translate-x-1/2 rounded-md border border-gray-200 bg-white p-1 shadow-lg"
                    >
                      <ul role="listbox" class="max-h-80 space-y-1 overflow-auto">
                        <li v-for="opt in monthOptions" :key="`${opt.year}-${opt.month}`">
                          <button
                            type="button"
                            @click="selectMonthOption(opt.year, opt.month)"
                            class="flex w-full items-center justify-between rounded-md px-3 py-2 text-sm text-gray-700 hover:bg-gray-50"
                          >
                            <span>{{ opt.label }} {{ opt.year }}</span>
                            <ChevronRight class="h-4 w-4 text-gray-300" />
                          </button>
                        </li>
                      </ul>
                    </div>
                  </div>
                  <button 
                    @click="nextMonth"
                    class="flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 text-gray-600 transition-colors hover:border-gray-300 hover:bg-gray-50"
                  >
                    <ChevronRight class="h-5 w-5" />
                  </button>
              </div>
              </header>

              <div class="mt-1 flex flex-wrap items-center gap-3 text-xs text-gray-600 lg:hidden">
                <div class="flex items-center gap-1">
                  <span class="inline-block h-2 w-2 rounded-full bg-green-500"></span>
                  <span>Volné</span>
                </div>
                <div class="flex items-center gap-1">
                  <span class="inline-block h-2 w-2 rounded-full bg-red-500"></span>
                  <span>Obsazené</span>
                </div>
                <div class="flex items-center gap-1">
                  <span class="inline-block h-2 w-2 rounded-full bg-orange-500"></span>
                  <span>Nedostupné</span>
                </div>
              </div>

              <!-- Calendar Grid -->
              <div class="relative">
                 <div v-if="error" class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700" role="alert" aria-live="polite">
                   <div class="flex items-center justify-between gap-3">
                     <span>{{ error }}</span>
                     <button @click="fetchCalendar" class="rounded-md bg-red-600 px-3 py-1.5 text-white hover:bg-red-700">Zkusit znovu</button>
                   </div>
                 </div>
                 <!-- Skeleton Loading -->
                 <div v-if="loading" class="grid grid-cols-7 gap-2">
                    <!-- Weekday Skeletons -->
                    <div v-for="i in 7" :key="`skeleton-day-${i}`" class="h-6 bg-gray-200 rounded animate-pulse"></div>
                    
                    <!-- Day Cell Skeletons -->
                    <div v-for="i in 35" :key="`skeleton-cell-${i}`" class="rounded border border-gray-200 p-2 space-y-2">
                      <div class="h-4 bg-gray-200 rounded animate-pulse w-8"></div>
                      <div class="h-3 bg-gray-200 rounded animate-pulse w-12"></div>
                      <div class="h-3 bg-gray-200 rounded animate-pulse w-16"></div>
                    </div>
                 </div>

                 <div class="grid grid-cols-7 gap-2" role="grid">
                    <!-- Weekdays -->
                    <div v-for="d in weekDays" :key="d" class="text-center font-medium text-gray-700">
                      {{ d }}
                    </div>
                    
                    <!-- Days -->
                    <button
                      v-for="(cell, idx) in cells"
                      :key="cell.date"
                      type="button"
                      :disabled="!isSelectable(cell.date)"
                      @click="selectDate(cell)"
                      @mouseenter="onEnterCell(cell)"
                      @mouseleave="onLeaveCell"
                      @keydown.left.prevent="focusAdjacentCell(idx, -1)"
                      @keydown.right.prevent="focusAdjacentCell(idx, 1)"
                      @keydown.up.prevent="focusAdjacentCell(idx, -7)"
                      @keydown.down.prevent="focusAdjacentCell(idx, 7)"
                      @keydown.enter.prevent="selectDate(cell)"
                      @keydown.space.prevent="selectDate(cell)"
                      :aria-label="ariaLabelForCell(cell)"
                      :title="ariaLabelForCell(cell)"
                      :ref="el => dayRefs[idx] = el"
                      class="cursor-pointer rounded border p-2.5 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-green-500 hover:border-green-400 hover:bg-green-50"
                      :class="[
                        cell.inCurrent ? '' : 'opacity-60',
                        !isSelectable(cell.date)
                          ? isBlackout(cell.date)
                            ? 'border-orange-400 bg-orange-50'
                            : 'border-red-400 bg-red-50'
                          : '',
                        isWeekend(cell.date) && isSelectable(cell.date) ? 'bg-gray-50' : '',
                        isInPreviewRange(cell.date) && isSelectable(cell.date) ? 'ring-1 ring-green-300 border-green-300 bg-green-50' : '',
                        isInRange(cell.date) && isSelectable(cell.date) ? 'ring-1 ring-green-500 border-green-400 bg-green-50' : '',
                        isStart(cell.date) || isEnd(cell.date) ? 'ring-1 ring-green-600' : '',
                        isToday(cell.date) && isSelectable(cell.date) ? 'ring-1 ring-gray-300' : '',
                        !isSelectable(cell.date) ? 'cursor-not-allowed' : ''
                      ]"
                    >
                      <div class="flex justify-between items-center">
                        <span class="font-semibold">{{ cell.day }}</span>
                        <span 
                          class="inline-block h-2 w-2 rounded-full"
                          :class="statusBgClass(cell.date)"
                          :aria-label="statusText(cell.date)"
                        ></span>
                      </div>
                      <div class="mt-1 flex items-center justify-start">
                        <div v-if="infoByDate(cell.date)?.price" class="text-xs font-medium text-gray-800">
                          {{ currency(infoByDate(cell.date)?.price) }}
                        </div>
                      </div>
                    </button>
                 </div>
              </div>

              <div v-if="rangeHasUnavailable" class="rounded-lg bg-amber-50 border border-amber-200 p-3 text-sm text-amber-900 mt-4">
                Vybraný termín zahrnuje obsazené dny:
                <ul class="list-disc pl-4">
                  <li v-for="d in unavailableDates" :key="d">{{ formatDate(d) }}</li>
                </ul>
              </div>

              <!-- Step 1 Footer -->
              <div class="flex flex-col gap-4 border-t border-gray-100 pt-6 sm:flex-row sm:items-center sm:justify-between">
                 <div class="text-sm text-gray-600">
                    <span v-if="startDate && !endDate">Vyberte datum odjezdu</span>
                    <span v-else-if="!startDate">Začněte výběrem data příjezdu</span>
                    <span v-else class="text-primary font-medium">Termín vybrán</span>
                 </div>
                 
                 <div class="flex gap-3">
                    <button 
                      v-if="startDate"
                      @click="clearSelection"
                      class="rounded-lg border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900"
                    >
                      Zrušit výběr
                    </button>
                    <button
                      @click="verifyAndProceed"
                      :disabled="!canProceed || verifying"
                      class="flex items-center gap-2 rounded-lg bg-primary px-6 py-2.5 text-sm font-medium text-white transition-colors hover:bg-primary/90 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                      <Loader2 v-if="verifying" class="h-4 w-4 animate-spin" />
                      <span>{{ verifying ? 'Ověřuji...' : 'Pokračovat' }}</span>
                      <ChevronRight v-if="!verifying" class="h-4 w-4" />
                     </button>
                  </div>
                 <div v-if="verifyError" class="text-sm text-red-700" role="alert" aria-live="polite">{{ verifyError }}</div>
              </div>
            </div>

            <!-- Step 2: Personal Info -->
            <div v-if="step === 2" class="space-y-8">
              <header>
                <h1 class="text-2xl font-medium text-gray-900">Osobní údaje</h1>
                <p class="text-gray-500">Vyplňte kontaktní údaje pro potvrzení rezervace.</p>
              </header>

              <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <!-- First Name -->
                <div class="space-y-1.5">
                  <label class="text-sm font-medium text-gray-700">Jméno</label>
                  <div class="relative">
                    <input
                      v-model="customer.firstName"
                      @blur="validateField('firstName')"
                      @input="validateField('firstName')"
                      @focus="clearFieldError('firstName')"
                      type="text"
                      autocomplete="given-name"
                      class="w-full rounded-lg border-2 px-4 py-2.5 pr-10 text-gray-900 placeholder-gray-400 transition-colors focus:outline-none"
                      :class="fieldErrors.firstName ? 'border-red-400' : validFields.firstName ? 'border-emerald-400' : 'border-gray-200 focus:border-primary'"
                      placeholder="Jan"
                    />
                    <CheckCircle v-if="validFields.firstName" class="absolute right-3 top-1/2 -translate-y-1/2 h-5 w-5 text-emerald-500" />
                  </div>
                  <p v-if="fieldErrors.firstName" class="text-xs text-red-600">{{ fieldErrors.firstName }}</p>
                </div>

                <!-- Last Name -->
                <div class="space-y-1.5">
                  <label class="text-sm font-medium text-gray-700">Příjmení</label>
                  <div class="relative">
                    <input
                      v-model="customer.lastName"
                      @blur="validateField('lastName')"
                      @input="validateField('lastName')"
                      @focus="clearFieldError('lastName')"
                      type="text"
                      autocomplete="family-name"
                      class="w-full rounded-lg border-2 px-4 py-2.5 pr-10 text-gray-900 placeholder-gray-400 transition-colors focus:outline-none"
                      :class="fieldErrors.lastName ? 'border-red-400' : validFields.lastName ? 'border-emerald-400' : 'border-gray-200 focus:border-primary'"
                      placeholder="Novák"
                    />
                    <CheckCircle v-if="validFields.lastName" class="absolute right-3 top-1/2 -translate-y-1/2 h-5 w-5 text-emerald-500" />
                  </div>
                  <p v-if="fieldErrors.lastName" class="text-xs text-red-600">{{ fieldErrors.lastName }}</p>
                </div>

                <!-- Email -->
                <div class="space-y-1.5">
                  <label class="text-sm font-medium text-gray-700">E-mail</label>
                  <div class="relative">
                    <input
                      v-model="customer.email"
                      @blur="validateField('email')"
                      @input="validateField('email')"
                      @focus="clearFieldError('email')"
                      type="email"
                      autocomplete="email"
                      class="w-full rounded-lg border-2 px-4 py-2.5 pr-10 text-gray-900 placeholder-gray-400 transition-colors focus:outline-none"
                      :class="fieldErrors.email ? 'border-red-400' : validFields.email ? 'border-emerald-400' : 'border-gray-200 focus:border-primary'"
                      placeholder="jan.novak@example.com"
                    />
                    <CheckCircle v-if="validFields.email" class="absolute right-3 top-1/2 -translate-y-1/2 h-5 w-5 text-emerald-500" />
                  </div>
                  <p v-if="fieldErrors.email" class="text-xs text-red-600">{{ fieldErrors.email }}</p>
                </div>

                <!-- Phone -->
                <div class="space-y-1.5">
                  <label class="text-sm font-medium text-gray-700">Telefon</label>
                  <div class="relative">
                    <input
                      v-model="customer.phone"
                      @blur="validateField('phone')"
                      @input="validateField('phone')"
                      @focus="clearFieldError('phone')"
                      type="tel"
                      autocomplete="tel"
                      inputmode="tel"
                      class="w-full rounded-lg border-2 px-4 py-2.5 pr-10 text-gray-900 placeholder-gray-400 transition-colors focus:outline-none"
                      :class="fieldErrors.phone ? 'border-red-400' : validFields.phone ? 'border-emerald-400' : 'border-gray-200 focus:border-primary'"
                      placeholder="+420 777 123 456"
                    />
                    <CheckCircle v-if="validFields.phone" class="absolute right-3 top-1/2 -translate-y-1/2 h-5 w-5 text-emerald-500" />
                  </div>
                  <p v-if="fieldErrors.phone" class="text-xs text-red-600">{{ fieldErrors.phone }}</p>
                </div>

                <!-- Note -->
                <div class="md:col-span-2 space-y-1.5">
                  <label class="text-sm font-medium text-gray-700">Poznámka (nepovinné)</label>
                  <textarea
                    v-model="customer.note"
                    rows="4"
                    class="w-full rounded-lg border-2 border-gray-200 px-4 py-2.5 text-gray-900 placeholder-gray-400 transition-colors focus:border-primary focus:outline-none"
                    placeholder="Máte nějaké speciální přání?"
                  ></textarea>
                </div>
              </div>

              <div class="flex justify-between border-t border-gray-100 pt-6">
                <button 
                  @click="step = 1"
                  class="flex items-center gap-2 rounded-lg px-4 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-900"
                >
                  <ChevronLeft class="h-4 w-4" />
                  Zpět
                </button>
                <button
                  @click="verifyCustomerAndProceed"
                  :disabled="!formReady"
                  class="flex items-center gap-2 rounded-lg bg-primary px-6 py-2.5 text-sm font-medium text-white transition-colors hover:bg-primary/90 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  Pokračovat
                  <ChevronRight class="h-4 w-4" />
                </button>
              </div>
              <div v-if="customerVerifyError" class="text-sm text-red-700" role="alert" aria-live="polite">{{ customerVerifyError }}</div>
            </div>

            <!-- Step 3: Extras -->
            <div v-if="step === 3" class="space-y-8">
              <header>
                <h1 class="text-2xl font-medium text-gray-900">Doplňkové služby</h1>
                <p class="text-gray-500">Vylepšete si svůj pobyt.</p>
              </header>

              <div v-if="extrasLoading" class="grid gap-4 sm:grid-cols-2">
                <div v-for="i in 4" :key="`skeleton-extra-${i}`" class="rounded-xl border-2 border-gray-100 p-5 space-y-3">
                  <div class="flex justify-between">
                    <div class="h-5 bg-gray-200 rounded animate-pulse w-32"></div>
                    <div class="h-5 bg-gray-200 rounded animate-pulse w-16"></div>
                  </div>
                  <div class="h-4 bg-gray-200 rounded animate-pulse w-full"></div>
                  <div class="h-4 bg-gray-200 rounded animate-pulse w-3/4"></div>
                  <div class="flex justify-end gap-3 pt-2">
                    <div class="h-8 w-8 bg-gray-200 rounded-full animate-pulse"></div>
                    <div class="h-8 w-8 bg-gray-200 rounded animate-pulse"></div>
                    <div class="h-8 w-8 bg-gray-200 rounded-full animate-pulse"></div>
                  </div>
                </div>
              </div>
              
              <div v-else-if="extrasError" class="rounded-lg bg-red-50 p-4 text-red-700">
                {{ extrasError }}
              </div>

              <div v-else class="grid gap-4 sm:grid-cols-2">
                <div 
                  v-for="ex in validExtras" 
                  :key="ex.id"
                  class="group relative flex flex-col justify-between rounded-xl border-2 border-gray-100 p-5 transition-all hover:border-primary/50"
                  :class="{ 'border-primary bg-primary/5': (extraSelection[ex.id] || 0) > 0 }"
                >
                  <div>
                    <div class="flex items-start justify-between mb-2">
                      <h3 class="font-medium text-gray-900">{{ ex.name }}</h3>
                      <div class="text-sm font-semibold text-primary">
                        {{ currency(ex.price) }}
                        <span class="text-xs font-normal text-gray-500">
                          {{ ex.price_type === 'per_day' ? '/den' : '/pobyt' }}
                        </span>
                      </div>
                    </div>
                    <p class="text-sm text-gray-500 mb-4">{{ ex.description }}</p>
                  </div>

                  <div class="flex items-center justify-end gap-3">
                    <button 
                      @click="booking.setExtraQuantity(ex.id, Math.max(0, (extraSelection[ex.id] || 0) - 1))"
                      class="flex h-8 w-8 items-center justify-center rounded-full border border-gray-200 text-gray-500 hover:border-gray-300 hover:bg-gray-50"
                      :disabled="(extraSelection[ex.id] || 0) === 0"
                    >
                      -
                    </button>
                    <span class="w-8 text-center font-medium text-gray-900">{{ extraSelection[ex.id] || 0 }}</span>
                    <button 
                      @click="booking.setExtraQuantity(ex.id, Math.min(ex.max_quantity, (extraSelection[ex.id] || 0) + 1))"
                      class="flex h-8 w-8 items-center justify-center rounded-full border border-gray-200 text-gray-500 hover:border-gray-300 hover:bg-gray-50"
                      :disabled="(extraSelection[ex.id] || 0) >= ex.max_quantity"
                      :title="(extraSelection[ex.id] || 0) >= ex.max_quantity ? 'Max. ' + ex.max_quantity : ''"
                    >
                      +
                    </button>
                    <span v-if="ex.max_quantity" class="text-xs font-medium text-gray-700">Max. {{ ex.max_quantity }}</span>
                    <span v-if="(extraSelection[ex.id] || 0) >= ex.max_quantity" class="text-xs font-medium text-amber-700">Dosaženo maxima</span>
                  </div>
                </div>
              </div>

              <div v-if="extrasAvailabilityError" class="rounded-lg bg-red-50 p-4 text-sm text-red-700 space-y-2" role="alert" aria-live="polite">
                <div>{{ extrasAvailabilityError }}</div>
                <ul v-if="extrasAvailabilityDetails.length" class="list-disc pl-5 text-red-800">
                  <li v-for="d in extrasAvailabilityDetails" :key="d.name">
                    {{ d.name }}: požadováno {{ d.requested }}, dostupné {{ d.available }}
                  </li>
                </ul>
              </div>

              <div class="flex justify-between border-t border-gray-100 pt-6">
                <button 
                  @click="step = 2"
                  class="flex items-center gap-2 rounded-lg px-4 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-900"
                >
                  <ChevronLeft class="h-4 w-4" />
                  Zpět
                </button>
                <button
                  @click="checkExtrasAvailability"
                  :disabled="!canSubmit"
                  class="flex items-center gap-2 rounded-lg bg-primary px-6 py-2.5 text-sm font-medium text-white transition-colors hover:bg-primary/90 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  Pokračovat
                  <ChevronRight class="h-4 w-4" />
                </button>
              </div>
            </div>

            <!-- Step 4: Review -->
            <div v-if="step === 4" class="space-y-8">
              <header>
                <h1 class="text-2xl font-medium text-gray-900">Kontrola rezervace</h1>
                <p class="text-gray-500">Prosím zkontrolujte všechny údaje před odesláním.</p>
              </header>

              <div class="space-y-6">
                <!-- Date & Price Summary Block -->
                <div class="rounded-xl border border-gray-200 bg-gray-50/50 p-6">
                  <h3 class="mb-4 font-medium text-gray-900">Termín a cena</h3>
                  <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                    <div>
                      <dt class="text-xs text-gray-500 uppercase tracking-wide">Příjezd</dt>
                      <dd class="mt-1 font-medium text-gray-900">{{ formatDate(startDate) }}</dd>
                    </div>
                    <div>
                      <dt class="text-xs text-gray-500 uppercase tracking-wide">Odjezd</dt>
                      <dd class="mt-1 font-medium text-gray-900">{{ formatDate(endDate) }}</dd>
                    </div>
                    <div>
                      <dt class="text-xs text-gray-500 uppercase tracking-wide">Délka pobytu</dt>
                      <dd class="mt-1 text-gray-900">{{ selectedNights }} nocí</dd>
                    </div>
                    <div>
                      <dt class="text-xs text-gray-500 uppercase tracking-wide">Celková cena</dt>
                      <dd class="mt-1 text-xl font-semibold text-primary">{{ currency(grandTotalPrice) }}</dd>
                    </div>
                  </dl>
                </div>
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                  K celkové ceně se na místě připočítává: vratná kauce 5 000 Kč, elektřina dle skutečné spotřeby (VT 9,00 Kč/kWh, NT 8,00 Kč/kWh), rekreační poplatek 20 Kč/osoba/den, pes 350 Kč/den. U pobytu na 1 noc se účtuje jednorázový úklid 3 000 Kč.
                </div>

                <!-- Personal Info Block -->
                <div class="rounded-xl border border-gray-200 p-6">
                  <div class="flex items-center justify-between mb-4">
                    <h3 class="font-medium text-gray-900">Kontaktní údaje</h3>
                    <button @click="step = 2" class="text-sm text-primary hover:underline">Upravit</button>
                  </div>
                  <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                    <div>
                      <dt class="text-xs text-gray-500 uppercase tracking-wide">Jméno</dt>
                      <dd class="mt-1 text-gray-900">{{ customer.firstName }} {{ customer.lastName }}</dd>
                    </div>
                    <div>
                      <dt class="text-xs text-gray-500 uppercase tracking-wide">Email</dt>
                      <dd class="mt-1 text-gray-900">{{ customer.email }}</dd>
                    </div>
                    <div>
                      <dt class="text-xs text-gray-500 uppercase tracking-wide">Telefon</dt>
                      <dd class="mt-1 text-gray-900">{{ customer.phone }}</dd>
                    </div>
                  </dl>
                  <div v-if="customer.note" class="mt-4 border-t border-gray-100 pt-4">
                    <dt class="text-xs text-gray-500 uppercase tracking-wide">Poznámka</dt>
                    <dd class="mt-1 text-gray-700 italic">"{{ customer.note }}"</dd>
                  </div>
                </div>

                <!-- Extras Block -->
                <div v-if="selectedExtras.length > 0" class="rounded-xl border border-gray-200 p-6">
                  <div class="flex items-center justify-between mb-4">
                    <h3 class="font-medium text-gray-900">Vybrané služby</h3>
                    <button @click="step = 3" class="text-sm text-primary hover:underline">Upravit</button>
                  </div>
                  <ul class="space-y-3">
                    <li v-for="ex in selectedExtras" :key="ex.id" class="flex justify-between text-sm">
                      <span class="text-gray-700">
                        {{ ex.name }} <span class="text-gray-400">× {{ extraSelection[ex.id] }}</span>
                      </span>
                      <span class="font-medium text-gray-900">
                        {{ currency(ex.price_type === "per_day" ? extraSelection[ex.id] * selectedNights * ex.price : extraSelection[ex.id] * ex.price) }}
                      </span>
                    </li>
                  </ul>
                </div>
              </div>

              <div v-if="submitError" class="rounded-lg bg-red-50 p-4 text-sm text-red-700">
                <div class="flex items-center justify-between gap-3">
                  <span>{{ submitError }}</span>
                  <Link :href="route('welcome')" class="rounded-md bg-red-600 px-3 py-1.5 text-white hover:bg-red-700">Kontaktovat nás</Link>
                </div>
              </div>

              <div class="rounded-xl border border-gray-200 p-4">
                <div class="space-y-2">
                  <label class="flex items-start gap-3 text-sm text-gray-700">
                    <Checkbox v-model="agreeGdpr" class="mt-0.5" />
                    <span>
                      Souhlasím se <Link href="/zasady-zpracovani-osobnich-udaju" class="text-primary hover:underline">zpracováním osobních údajů (GDPR)</Link>.
                    </span>
                  </label>
                  <label class="flex items-start gap-3 text-sm text-gray-700">
                    <Checkbox v-model="agreeTerms" class="mt-0.5" />
                    <span>
                      Souhlasím s <Link href="/vseobecne-obchodni-podminky" class="text-primary hover:underline">Všeobecnými obchodními podmínkami</Link>.
                    </span>
                  </label>
                  <p v-if="step === 4 && !consentsAccepted" class="text-xs text-gray-500">Pro odeslání rezervace je nutné zaškrtnout oba souhlasy.</p>
                </div>
              </div>

              <div class="flex justify-between border-t border-gray-100 pt-6">
                <button 
                  @click="step = 3"
                  class="flex items-center gap-2 rounded-lg px-4 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-900"
                >
                  <ChevronLeft class="h-4 w-4" />
                  Zpět
                </button>
                <button
                  @click="submit"
                  :disabled="!canSubmit || submitting"
                  class="flex items-center gap-2 rounded-lg bg-primary px-6 py-2.5 text-sm font-medium text-white transition-colors hover:bg-primary/90 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <Loader2 v-if="submitting" class="h-4 w-4 animate-spin" />
                  <span v-else>Odeslat rezervaci</span>
                  <Send v-if="!submitting" class="h-4 w-4" />
                </button>
              </div>
            </div>

            <!-- Step 5: Success -->
            <div v-if="step === 5" class="flex min-h-[400px] flex-col items-center justify-center text-center">
              <div class="mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
                <CheckCircle class="h-10 w-10" />
              </div>
              <h1 class="mb-2 text-3xl font-medium text-gray-900">Rezervace odeslána</h1>
              <p class="mb-8 max-w-md text-gray-500">
                Děkujeme za vaši rezervaci. Na email <strong>{{ customer.email }}</strong> jsme vám poslali potvrzení a další instrukce.
              </p>
              
              <div class="flex gap-4">
                <button 
                  @click="step = 1"
                  class="rounded-lg border border-gray-200 px-6 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900"
                >
                  Nová rezervace
                </button>
                <a 
                  href="/"
                  class="rounded-lg bg-primary px-6 py-2.5 text-sm font-medium text-white hover:bg-primary/90"
                >
                  Zpět na úvod
                </a>
              </div>
            </div>

          </div>
        </main>

      </div>
    </div>

    <!-- Sticky Mobile Summary Bar -->
    <div 
      v-if="step < 5" 
      class="fixed bottom-0 left-0 right-0 z-50 bg-white border-t-2 border-gray-200 px-4 py-3 shadow-2xl lg:hidden"
    >
      <div class="flex items-center justify-between gap-4">
        <div class="flex flex-col">
          <span class="text-xs text-gray-500">Celková cena</span>
          <span class="text-lg font-semibold text-primary">{{ currency(grandTotalPrice) }}</span>
        </div>
        <button
          v-if="step === 1"
          @click="verifyAndProceed"
          :disabled="!canProceed || verifying"
          class="flex items-center gap-2 rounded-lg bg-primary px-6 py-3 text-sm font-medium text-white transition-colors hover:bg-primary/90 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <Loader2 v-if="verifying" class="h-4 w-4 animate-spin" />
          <span>{{ verifying ? 'Ověřuji...' : (!canProceed ? 'Vyberte termín' : 'Pokračovat') }}</span>
          <ChevronRight v-if="!verifying" class="h-4 w-4" />
        </button>
        <button
          v-else-if="step === 2"
          @click="verifyCustomerAndProceed"
          :disabled="!formReady"
          class="flex items-center gap-2 rounded-lg bg-primary px-6 py-3 text-sm font-medium text-white transition-colors hover:bg-primary/90 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Pokračovat
          <ChevronRight class="h-4 w-4" />
        </button>
        <button
          v-else-if="step === 3"
          @click="checkExtrasAvailability"
          :disabled="!canSubmit"
          class="flex items-center gap-2 rounded-lg bg-primary px-6 py-3 text-sm font-medium text-white transition-colors hover:bg-primary/90 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Pokračovat
          <ChevronRight class="h-4 w-4" />
        </button>
        <button
          v-else-if="step === 4"
          @click="submit"
          :disabled="!canSubmit || submitting"
          class="flex items-center gap-2 rounded-lg bg-primary px-6 py-3 text-sm font-medium text-white transition-colors hover:bg-primary/90 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <Loader2 v-if="submitting" class="h-4 w-4 animate-spin" />
          <span v-else>Odeslat</span>
          <Send v-if="!submitting" class="h-4 w-4" />
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import axios from "axios";
import { ref, computed, onMounted, onBeforeUnmount } from "vue";
import { toast } from "vue-sonner";
import { useBookingStore } from "@/stores/booking";
import { Link, usePage } from "@inertiajs/vue3";
import { Checkbox } from "@/components/ui/checkbox";
import {
  ChevronLeft,
  ChevronRight,
  ChevronDown,
  CheckCircle,
  Calendar,
  User,
  StickyNote,
  Send,
  PawPrint,
  Loader2,
} from "lucide-vue-next";

const page = usePage();
const now = new Date();
const month = ref(now.getMonth() + 1);
const year = ref(now.getFullYear());
const todayMonth = now.getMonth() + 1;
const todayYear = now.getFullYear();
const todayDay = now.getDate();
const daysData = ref([]);
const loading = ref(false);
const error = ref("");
const monthPickerOpen = ref(false);
const monthPickerEl = ref(null);
const startDate = computed({
  get: () => booking.startDate,
  set: (val) => booking.setStartDate(val),
});
const endDate = computed({
  get: () => booking.endDate,
  set: (val) => booking.setEndDate(val),
});
const step = ref(1);
const booking = useBookingStore();
const customer = computed({
  get: () => booking.customer,
  set: (val) => booking.updateCustomer(val),
});
const extras = computed(() => booking.extras);
const extraSelection = computed(() => booking.extraSelection);
const validExtras = computed(() =>
  (extras.value || []).filter((ex) => ex && typeof ex === "object" && "id" in ex)
);
const selectedExtras = computed(() =>
  validExtras.value.filter((ex) => Number(extraSelection.value[ex.id] || 0) > 0)
);
const submitted = ref(false);
const submitting = ref(false);
const submitError = ref("");
const verifying = ref(false);
const verifyError = ref("");
const verifyingCustomer = ref(false);
const customerVerifyError = ref("");
const extrasAvailabilityDetails = ref([]);
const extrasLoading = ref(false);
const extrasError = ref("");
const extrasAvailabilityError = ref("");
const dayRefs = ref([]);
const hoverDate = ref(null);
const agreeGdpr = ref(false);
const agreeTerms = ref(false);

const minLeadDays = computed(() => {
  const v = page?.props?.bookingConfig?.minLeadDays ?? 0;
  return Number(v) || 0;
});

// Form validation
const fieldErrors = ref({
  firstName: "",
  lastName: "",
  email: "",
  phone: "",
});
const validFields = ref({
  firstName: false,
  lastName: false,
  email: false,
  phone: false,
});

function validateField(field) {
  const value = customer.value[field];
  
  if (field === 'firstName' || field === 'lastName') {
    if (!value || value.trim().length < 2) {
      fieldErrors.value[field] = 'Prosím vyplňte alespoň 2 znaky';
      validFields.value[field] = false;
    } else {
      fieldErrors.value[field] = '';
      validFields.value[field] = true;
    }
  }
  
  if (field === 'email') {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!value) {
      fieldErrors.value.email = 'E-mail je povinný';
      validFields.value.email = false;
    } else if (!emailRegex.test(value)) {
      fieldErrors.value.email = 'Neplatný formát e-mailu';
      validFields.value.email = false;
    } else {
      fieldErrors.value.email = '';
      validFields.value.email = true;
    }
  }
  
  if (field === 'phone') {
    const phoneRegex = /^(\+420)?\s?[0-9]{3}\s?[0-9]{3}\s?[0-9]{3}$/;
    if (!value) {
      fieldErrors.value.phone = 'Telefon je povinný';
      validFields.value.phone = false;
    } else if (!phoneRegex.test(value.replace(/\s/g, ''))) {
      fieldErrors.value.phone = 'Neplatný formát (např. +420 777 123 456)';
      validFields.value.phone = false;
    } else {
      fieldErrors.value.phone = '';
      validFields.value.phone = true;
    }
  }
}

function clearFieldError(field) {
  fieldErrors.value[field] = '';
}

const monthLabel = computed(() =>
  new Date(year.value, month.value - 1, 1).toLocaleString("cs-CZ", { month: "long" })
);
const weekDays = ["Po", "Út", "St", "Čt", "Pá", "So", "Ne"];

function monthName(y, m) {
  return new Date(y, m - 1, 1).toLocaleString("cs-CZ", { month: "long" });
}

const monthOptions = computed(() => {
  const opts = [];
  let y = todayYear;
  let m = todayMonth;
  for (let i = 0; i < 12; i++) {
    opts.push({ year: y, month: m, label: monthName(y, m) });
    m++;
    if (m > 12) {
      m = 1;
      y += 1;
    }
  }
  return opts;
});

function onDocClick(e) {
  if (!monthPickerOpen.value) return;
  const el = monthPickerEl.value;
  if (el && !el.contains(e.target)) {
    monthPickerOpen.value = false;
  }
}

onMounted(() => {
  document.addEventListener('click', onDocClick);
});

onBeforeUnmount(() => {
  document.removeEventListener('click', onDocClick);
});

const stepItems = [
  { id: 1, label: "Termín", desc: "Krok 1", icon: Calendar },
  { id: 2, label: "Údaje", desc: "Krok 2", icon: User },
  { id: 3, label: "Služby", desc: "Krok 3", icon: PawPrint },
  { id: 4, label: "Kontrola", desc: "Krok 4", icon: StickyNote },
  { id: 5, label: "Hotovo", desc: "Krok 5", icon: CheckCircle },
];

const progressPercent = computed(() => Math.round((Math.min(step.value, 5) / 5) * 100));

function canNavigateTo(id) {
  return id <= step.value;
}

function navigateTo(id) {
  if (canNavigateTo(id)) {
    step.value = id;
  }
}

const daysInMonth = computed(() => new Date(year.value, month.value, 0).getDate());
const firstDayIndex = computed(() => {
  const idx = new Date(year.value, month.value - 1, 1).getDay();
  return idx === 0 ? 6 : idx - 1;
});
const prevYear = computed(() => (month.value === 1 ? year.value - 1 : year.value));
const prevMonthNum = computed(() => (month.value === 1 ? 12 : month.value - 1));
const prevMonthDaysCount = computed(() =>
  new Date(prevYear.value, prevMonthNum.value, 0).getDate()
);

function pad(n) {
  return String(n).padStart(2, "0");
}



function infoByDate(date) {
  return daysData.value.find((x) => x.date === date);
}

function currency(n) {
  return new Intl.NumberFormat("cs-CZ", {
    style: "currency",
    currency: "CZK",
    maximumFractionDigits: 0,
  }).format(Number(n));
}

function formatDate(iso) {
  if (!iso) return "";
  const d = parseISO(iso);
  return `${d.getDate()}. ${d.getMonth() + 1}. ${d.getFullYear()}`;
}

async function fetchCalendar() {
  loading.value = true;
  error.value = "";
  try {
    const token = page.props.property?.widget_token;
    const [curr, prev] = await Promise.all([
      axios.get(`/api/properties/${token}/calendar`, { params: { month: month.value, year: year.value } }),
      axios.get(`/api/properties/${token}/calendar`, {
        params: { month: prevMonthNum.value, year: prevYear.value },
      }),
    ]);
    daysData.value = [...prev.data.days, ...curr.data.days];
  } catch (e) {
    error.value = "Kalendář se nepodařilo načíst. Obnovte stránku nebo zkuste později.";
  } finally {
    loading.value = false;
  }
}

function nextMonth() {
  if (month.value === 12) {
    month.value = 1;
    year.value += 1;
  } else {
    month.value += 1;
  }
  fetchCalendar();
}

function prevMonth() {
  if (month.value === 1) {
    month.value = 12;
    year.value -= 1;
  } else {
    month.value -= 1;
  }
  fetchCalendar();
}

function selectMonthOption(y, m) {
  year.value = y;
  month.value = m;
  monthPickerOpen.value = false;
  fetchCalendar();
}

onMounted(fetchCalendar);

const cells = computed(() => {
  const off = firstDayIndex.value;
  const prevStart = prevMonthDaysCount.value - off + 1;
  const prevCells = Array.from({ length: off }, (_, i) => {
    const day = prevStart + i;
    const date = `${prevYear.value}-${pad(prevMonthNum.value)}-${pad(day)}`;
    return { date, day, inCurrent: false };
  });
  const currCells = Array.from({ length: daysInMonth.value }, (_, i) => {
    const day = i + 1;
    const date = `${year.value}-${pad(month.value)}-${pad(day)}`;
    return { date, day, inCurrent: true };
  });
  return [...prevCells, ...currCells];
});

const canGoPrev = computed(() => {
  const targetY = prevYear.value;
  const targetM = prevMonthNum.value;
  return targetY > todayYear || (targetY === todayYear && targetM >= todayMonth);
});

function parseISO(s) {
  const [Y, M, D] = s.split("-").map(Number);
  return new Date(Y, M - 1, D);
}

const rangeStart = computed(() => (startDate.value ? parseISO(startDate.value) : null));
const rangeEnd = computed(() => (endDate.value ? parseISO(endDate.value) : null));

function isSameDate(a, b) {
  if (!a || !b) return false;
  return (
    a.getFullYear() === b.getFullYear() &&
    a.getMonth() === b.getMonth() &&
    a.getDate() === b.getDate()
  );
}

function isToday(dateStr) {
  const d = parseISO(dateStr);
  const t = new Date(todayYear, todayMonth - 1, todayDay);
  return isSameDate(d, t);
}

function isWeekend(dateStr) {
  const d = parseISO(dateStr);
  const idx = d.getDay();
  return idx === 0 || idx === 6;
}

function isInRange(dateStr) {
  if (!rangeStart.value) return false;
  const d = parseISO(dateStr);
  if (rangeStart.value && !rangeEnd.value) {
    return isSameDate(d, rangeStart.value);
  }
  if (rangeStart.value && rangeEnd.value) {
    const a = rangeStart.value <= rangeEnd.value ? rangeStart.value : rangeEnd.value;
    const b = rangeStart.value <= rangeEnd.value ? rangeEnd.value : rangeStart.value;
    return d >= a && d <= b;
  }
  return false;
}

function isStart(dateStr) {
  return !!(rangeStart.value && isSameDate(parseISO(dateStr), rangeStart.value));
}

function isEnd(dateStr) {
  return !!(rangeEnd.value && isSameDate(parseISO(dateStr), rangeEnd.value));
}

function meetsLead(dateStr) {
  const d = parseISO(dateStr);
  const base = new Date(todayYear, todayMonth - 1, todayDay);
  const earliest = addDays(base, minLeadDays.value);
  earliest.setHours(0, 0, 0, 0);
  d.setHours(0, 0, 0, 0);
  return d >= earliest;
}

function isSelectable(dateStr) {
  return isAvailable(dateStr) && meetsLead(dateStr);
}

function isInPreviewRange(dateStr) {
  if (!rangeStart.value || rangeEnd.value || !hoverDate.value) return false;
  const d = parseISO(dateStr);
  const h = parseISO(hoverDate.value);
  const a = rangeStart.value <= h ? rangeStart.value : h;
  const b = rangeStart.value <= h ? h : rangeStart.value;
  return d >= a && d <= b;
}

function isAvailable(dateStr) {
  const info = infoByDate(dateStr);
  if (!info) return true;
  return !!info.available;
}

function isBlackout(dateStr) {
  const info = infoByDate(dateStr);
  if (!info) return false;
  return !!info.blackout;
}

function statusText(dateStr) {
  const info = infoByDate(dateStr);
  if (!info) return "";
  if (info.available) return "Volné";
  if (info.blackout) return "Nedostupné";
  return "Obsazené";
}

function statusBgClass(dateStr) {
  const info = infoByDate(dateStr);
  if (!info) return "bg-gray-200";
  if (info.available) return "bg-green-500";
  if (info.blackout) return "bg-orange-500";
  return "bg-red-500";
}


function selectDate(cell) {
  if (!isSelectable(cell.date)) return;
  const date = cell.date;
  if (!startDate.value || (startDate.value && endDate.value)) {
    startDate.value = date;
    endDate.value = null;
    return;
  }
  if (!endDate.value) {
    const a = parseISO(startDate.value);
    const b = parseISO(date);
    if (b < a) {
      endDate.value = startDate.value;
      startDate.value = date;
    } else {
      endDate.value = date;
    }
  }
}

function onEnterCell(cell) {
  if (startDate.value && !endDate.value && isSelectable(cell.date)) {
    hoverDate.value = cell.date;
  } else {
    hoverDate.value = null;
  }
}

function onLeaveCell() {
  hoverDate.value = null;
}

function clearSelection() {
  booking.resetDates();
}

const selectedNights = computed(() => {
  return Math.max(0, (rangeDates.value.length > 0 ? rangeDates.value.length - 1 : 0));
});

const nightDates = computed(() => {
  if (rangeDates.value.length <= 1) return [];
  return rangeDates.value.slice(0, -1);
});

function addDays(date, days) {
  const d = new Date(date);
  d.setDate(d.getDate() + days);
  return d;
}

function toISO(d) {
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
}

const rangeDates = computed(() => {
  if (!(rangeStart.value && rangeEnd.value)) return [];
  const a = rangeStart.value <= rangeEnd.value ? rangeStart.value : rangeEnd.value;
  const b = rangeStart.value <= rangeEnd.value ? rangeEnd.value : rangeStart.value;
  const out = [];
  let cur = new Date(a);
  while (cur <= b) {
    out.push(toISO(cur));
    cur = addDays(cur, 1);
  }
  return out;
});

const monthsForRange = computed(() => {
  const m = new Map();
  for (const iso of rangeDates.value) {
    const d = parseISO(iso);
    const key = `${d.getFullYear()}-${d.getMonth() + 1}`;
    if (!m.has(key)) m.set(key, { month: d.getMonth() + 1, year: d.getFullYear() });
  }
  return Array.from(m.values());
});
function focusAdjacentCell(idx, delta) {
  const target = idx + delta;
  if (target < 0 || target >= cells.value.length) return;
  const el = dayRefs.value[target];
  if (el) {
    el.focus();
  }
}

function ariaLabelForCell(cell) {
  const d = formatDate(cell.date);
  const s = statusText(cell.date) || "";
  const priceInfo = infoByDate(cell.date)?.price ? currency(infoByDate(cell.date)?.price) : "";
  return priceInfo ? `${d}, ${s}, ${priceInfo}` : `${d}, ${s}`;
}

const selectedTotalPrice = computed(() => {
  if (rangeDates.value.length <= 1) return 0;
  const nights = rangeDates.value.slice(0, -1);
  return nights.reduce((sum, iso) => {
    const info = infoByDate(iso);
    const price = info?.price ? Number(info.price) : 0;
    return sum + price;
  }, 0);
});

const rangeHasUnavailable = computed(() => {
  if (nightDates.value.length === 0) return false;
  return nightDates.value.some((iso) => {
    const info = infoByDate(iso);
    return info && info.available === false;
  });
});
const unavailableDates = computed(() => {
  if (nightDates.value.length === 0) return [];
  return nightDates.value.filter((iso) => {
    const info = infoByDate(iso);
    return info && info.available === false;
  });
});

const canProceed = computed(() => {
  return !!(startDate.value && endDate.value) && selectedNights.value >= 1 && !rangeHasUnavailable.value;
});

const allFieldsValid = computed(() => {
  return Object.values(validFields.value).every(Boolean);
});

const formReady = computed(() => {
  return canProceed.value && allFieldsValid.value;
});

const addonsTotalPrice = computed(() => {
  if (selectedNights.value <= 0 || selectedExtras.value.length === 0) return 0;
  return selectedExtras.value.reduce((sum, ex) => {
    const qty = Number(extraSelection.value[ex.id] || 0);
    const unit = Number(ex.price || 0);
    const line = ex.price_type === "per_day" ? qty * selectedNights.value * unit : qty * unit;
    return sum + line;
  }, 0);
});

const grandTotalPrice = computed(() => selectedTotalPrice.value + addonsTotalPrice.value);

const consentsAccepted = computed(() => agreeGdpr.value && agreeTerms.value);
const canSubmit = computed(() => formReady.value && (step.value !== 4 ? true : consentsAccepted.value));

async function submit() {
  if (!canSubmit.value || submitting.value) return;
  submitting.value = true;
  submitError.value = "";
  try {
    const payload = {
      property_id: page.props.property?.id,
      start_date: startDate.value,
      end_date: endDate.value,
      customer: {
        first_name: customer.value.firstName,
        last_name: customer.value.lastName,
        email: customer.value.email,
        phone: customer.value.phone,
        note: customer.value.note || "",
      },
      addons: selectedExtras.value.map((ex) => ({
        service_id: ex.id,
        quantity: Number(extraSelection.value[ex.id] || 0),
      })),
      accommodation_total: selectedTotalPrice.value,
      addons_total: addonsTotalPrice.value,
      grand_total: grandTotalPrice.value,
    };
    await axios.post("/api/bookings", payload);
    submitted.value = true;
    step.value = 5;
  } catch (e) {
    submitError.value = "Rezervaci se nepodařilo odeslat. Zkuste to prosím znovu.";
  } finally {
    submitting.value = false;
  }
}

async function verifyAndProceed() {
  if (!canProceed.value) return;
  verifying.value = true;
  verifyError.value = "";
  try {
    const res = await axios.post("/api/bookings/verify", {
      start_date: startDate.value,
      end_date: endDate.value,
    });
    if (!res.data.available) {
      toast.error("Vybraný termín je mezitím obsazen. Vyberte prosím jiné datum.");
      verifyError.value = "Vybraný termín je mezitím obsazen. Vyberte prosím jiné datum.";
      return;
    }
    const token = page.props.property?.widget_token;
    const requests = monthsForRange.value.map(({ month, year }) =>
      axios.get(`/api/properties/${token}/calendar`, { params: { month, year } })
    );
    const responses = await Promise.all(requests);
    const freshDays = responses.flatMap((r) => r.data.days);
    const merged = new Map(daysData.value.map((d) => [d.date, d]));
    freshDays.forEach((d) => merged.set(d.date, d));
    daysData.value = Array.from(merged.values());
    step.value = 2;
  } catch (e) {
    toast.error("Ověření dostupnosti se nezdařilo. Zkuste to prosím znovu.");
    verifyError.value = "Ověření dostupnosti se nezdařilo. Zkuste to prosím znovu.";
  } finally {
    verifying.value = false;
  }
}

async function verifyCustomerAndProceed() {
  if (!formReady.value || verifyingCustomer.value) return;
  verifyingCustomer.value = true;
  customerVerifyError.value = "";
  try {
    const res = await axios.post('/api/bookings/verify-customer', {
      customer: {
        first_name: customer.value.firstName,
        last_name: customer.value.lastName,
        email: customer.value.email,
        phone: customer.value.phone,
        note: customer.value.note || '',
      },
    });
    if (res.data && res.data.valid) {
      step.value = 3;
      return;
    }
    customerVerifyError.value = 'Ověření kontaktních údajů se nezdařilo. Zkontrolujte prosím zadání.';
    toast.error('Ověření kontaktních údajů se nezdařilo.');
  } catch (e) {
    customerVerifyError.value = 'Ověření kontaktních údajů se nezdařilo. Zkuste to prosím znovu.';
    toast.error('Ověření kontaktních údajů se nezdařilo.');
  } finally {
    verifyingCustomer.value = false;
  }
}

async function loadExtras() {
  extrasLoading.value = true;
  extrasError.value = "";

  try {
    const res = await axios.get("/api/services");
    booking.setExtras(res.data.services || []);
  } catch (e) {
    extrasError.value = "Příplatkové služby se nepodařilo načíst.";
  } finally {
    extrasLoading.value = false;
  }
}

async function checkExtrasAvailability() {
  if (selectedExtras.value.length === 0) {
    step.value = 4;
    return;
  }

  try {
    const selections = selectedExtras.value.map((ex) => ({
      service_id: ex.id,
      quantity: Number(extraSelection.value[ex.id] || 0),
    }));

    const res = await axios.post("/api/services/availability", {
      start_date: startDate.value,
      end_date: endDate.value,
      selections: selections,
    });
    if (!res.data.available) {
      const unavailable = (res.data.items || []).filter((i) => i.is_available === false);
      extrasAvailabilityDetails.value = unavailable.map((i) => {
        const id = i.service_id ?? i.extra_id;
        const svc = validExtras.value.find((s) => s.id === id);
        return {
          name: svc?.name || `Služba #${id}`,
          available: i.available_quantity ?? 0,
          requested: i.requested_quantity ?? 0,
        };
      });
      toast.error("Některé služby nejsou v požadovaném množství dostupné.");
      extrasAvailabilityError.value = "Některé služby nejsou v požadovaném množství dostupné.";
      return;
    }
    extrasAvailabilityError.value = "";
    extrasAvailabilityDetails.value = [];
    step.value = 4;
  } catch (e) {
    toast.error("Ověření dostupnosti služeb se nezdařilo.");
    extrasAvailabilityError.value = "Ověření dostupnosti služeb se nezdařilo. Zkuste to prosím znovu.";
    extrasAvailabilityDetails.value = [];
  }
}

onMounted(() => {
  loadExtras();
});
</script>
