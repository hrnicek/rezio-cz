<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';
import { ArrowLeft, Mail, Edit, CheckCircle, XCircle } from 'lucide-vue-next';
import { ref } from 'vue';

declare const route: any;

const props = defineProps<{
  property: { id: number; name: string };
  templates: Array<{
    id: number;
    type: string;
    subject: string;
    body: string;
    is_active: boolean;
  }>;
  availableTypes: Array<{
    type: string;
    name: string;
    description: string;
  }>;
}>();

const editingTemplate = ref<any>(null);
const isDialogOpen = ref(false);

const form = useForm({
  subject: '',
  body: '',
  is_active: true,
});

const openEditDialog = (type: string) => {
  const existingTemplate = props.templates.find(t => t.type === type);
  const typeInfo = props.availableTypes.find(t => t.type === type);

  if (existingTemplate) {
    editingTemplate.value = { ...existingTemplate, ...typeInfo };
    form.subject = existingTemplate.subject;
    form.body = existingTemplate.body;
    form.is_active = existingTemplate.is_active;
  } else if (typeInfo) {
    // Default values for new template
    editingTemplate.value = { ...typeInfo, id: null };
    
    let defaultSubject = '';
    let defaultBody = '';

    switch (type) {
      case 'booking_confirmation':
        defaultSubject = `Potvrzení rezervace - ${props.property.name}`;
        defaultBody = `Dobrý den,\n\nděkujeme za Vaši rezervaci v ${props.property.name}.\n\nTěšíme se na Vás!`;
        break;
      case 'payment_reminder':
        defaultSubject = `Připomínka platby - ${props.property.name}`;
        defaultBody = `Dobrý den,\n\npřipomínáme Vám blížící se splatnost platby za Vaši rezervaci {{ booking_code }} v ${props.property.name}.`;
        break;
      case 'payment_confirmation':
        defaultSubject = `Potvrzení platby - ${props.property.name}`;
        defaultBody = `Dobrý den,\n\npotvrzujeme přijetí Vaší platby za rezervaci {{ booking_code }} v ${props.property.name}.\n\nDěkujeme.`;
        break;
      case 'payment_overdue':
        defaultSubject = `Upomínka platby - ${props.property.name}`;
        defaultBody = `Dobrý den,\n\nevidujeme neuhrazenou platbu po splatnosti za Vaši rezervaci {{ booking_code }} v ${props.property.name}.\n\nProsíme o úhradu co nejdříve.`;
        break;
      case 'booking_cancelled':
        defaultSubject = `Vaše rezervace byla stornována - ${props.property.name}`;
        defaultBody = `Dobrý den,\n\nVaše rezervace {{ booking_code }} v ${props.property.name} byla stornována.`;
        break;
      case 'booking_rejected':
        defaultSubject = `Zamítnutí rezervace - ${props.property.name}`;
        defaultBody = `Dobrý den,\n\nje nám líto, ale Vaši rezervaci {{ booking_code }} v ${props.property.name} jsme museli zamítnout.`;
        break;
      case 'pre_arrival_info':
        defaultSubject = `Informace před příjezdem - ${props.property.name}`;
        defaultBody = `Dobrý den,\n\nblíží se Váš pobyt v ${props.property.name}.\n\nZde jsou důležité informace před příjezdem.`;
        break;
    }

    form.subject = defaultSubject;
    form.body = defaultBody;
    form.is_active = true;
  }
  
  isDialogOpen.value = true;
};

const saveTemplate = () => {
  if (editingTemplate.value.id) {
    form.put(route('admin.properties.email-templates.update', [props.property.id, editingTemplate.value.id]), {
      onSuccess: () => isDialogOpen.value = false,
    });
  } else {
    form.transform((data) => ({
      ...data,
      type: editingTemplate.value.type,
    })).post(route('admin.properties.email-templates.store', props.property.id), {
      onSuccess: () => isDialogOpen.value = false,
    });
  }
};

const getTemplateStatus = (type: string) => {
  const template = props.templates.find(t => t.type === type);
  return template ? (template.is_active ? 'active' : 'inactive') : 'default';
};
</script>

<template>
  <Head title="Emailové šablony" />

  <AppLayout :breadcrumbs="[
    { title: 'Nemovitosti', href: route('admin.properties.index') },
    { title: property.name, href: route('admin.properties.edit', property.id) },
    { title: 'Emailové šablony', href: route('admin.properties.email-templates.index', property.id) },
  ]">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
          <Button variant="outline" size="icon" as-child>
            <Link :href="route('admin.properties.edit', property.id)">
              <ArrowLeft class="h-4 w-4" />
            </Link>
          </Button>
          <div>
            <h2 class="text-2xl font-bold tracking-tight">Emailové šablony</h2>
            <p class="text-sm text-muted-foreground">
              Správa automatických emailů pro {{ property.name }}
            </p>
          </div>
        </div>
      </div>

      <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        <Card v-for="type in availableTypes" :key="type.type" class="flex flex-col">
          <CardHeader>
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <div class="p-2 bg-primary/10 rounded-full">
                  <Mail class="h-5 w-5 text-primary" />
                </div>
                <Badge :variant="getTemplateStatus(type.type) === 'active' ? 'default' : 'secondary'">
                  {{ getTemplateStatus(type.type) === 'active' ? 'Aktivní' : (getTemplateStatus(type.type) === 'inactive' ? 'Neaktivní' : 'Výchozí') }}
                </Badge>
              </div>
            </div>
            <CardTitle class="mt-4">{{ type.name }}</CardTitle>
            <CardDescription>{{ type.description }}</CardDescription>
          </CardHeader>
          <CardContent class="flex-1">
            <!-- Preview or additional info could go here -->
          </CardContent>
          <CardFooter>
            <Button class="w-full" variant="outline" @click="openEditDialog(type.type)">
              <Edit class="mr-2 h-4 w-4" />
              Upravit šablonu
            </Button>
          </CardFooter>
        </Card>
      </div>

      <Dialog v-model:open="isDialogOpen">
        <DialogContent class="sm:max-w-[600px]">
          <DialogHeader>
            <DialogTitle>Upravit šablonu: {{ editingTemplate?.name }}</DialogTitle>
            <DialogDescription>
              Upravte předmět a obsah emailu. Můžete použít zástupné znaky.
            </DialogDescription>
          </DialogHeader>
          
          <div class="grid gap-4 py-4">
            <div class="flex items-center space-x-2">
              <Switch id="active" :checked="form.is_active" @update:checked="form.is_active = $event" />
              <Label htmlFor="active">Aktivní (odesílat tento email)</Label>
            </div>
            
            <div class="grid gap-2">
              <Label htmlFor="subject">Předmět</Label>
              <Input id="subject" v-model="form.subject" />
            </div>
            
            <div class="grid gap-2">
              <Label htmlFor="body">Obsah</Label>
              <Textarea id="body" v-model="form.body" class="min-h-[200px]" />
              <p class="text-xs text-muted-foreground">
                Dostupné proměnné: {{ '{' + '{ customer_name }' + '}' }}, {{ '{' + '{ booking_code }' + '}' }}, {{ '{' + '{ property_name }' + '}' }}
              </p>
            </div>
          </div>

          <DialogFooter>
            <Button variant="outline" @click="isDialogOpen = false">Zrušit</Button>
            <Button @click="saveTemplate" :disabled="form.processing">Uložit změny</Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  </AppLayout>
</template>
