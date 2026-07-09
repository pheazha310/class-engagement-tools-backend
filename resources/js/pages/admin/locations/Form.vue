<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

type ProvinceObject = {
    name: string;
    country: string | null;
};

const props = defineProps<{
    countries: string[];
    provinces: ProvinceObject[];
    location?: {
        id: number;
        country: string;
        province: string;
        school_name: string;
    };
    submitLabel: string;
}>();

const emit = defineEmits<{
    (e: 'submit', form: ReturnType<typeof useForm>): void;
}>();

const form = useForm({
    country: props.location?.country ?? '',
    province: props.location?.province ?? '',
    school_name: props.location?.school_name ?? '',
});

const filteredProvinces = computed(() =>
    props.provinces.filter((p) => p.country === form.country),
);

const submit = () => emit('submit', form);
</script>

<template>
    <form class="max-w-xl space-y-6" @submit.prevent="submit">
        <div class="grid gap-2">
            <Label for="country">Country</Label>
            <Select v-model="form.country" name="country">
                <SelectTrigger>
                    <SelectValue placeholder="Select a country" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem
                        v-for="country in countries"
                        :key="country"
                        :value="country"
                    >
                        {{ country }}
                    </SelectItem>
                </SelectContent>
            </Select>
            <InputError :message="form.errors.country" />
        </div>

        <div class="grid gap-2">
            <Label for="province">Province</Label>
            <Select
                v-model="form.province"
                name="province"
                :disabled="!form.country"
            >
                <SelectTrigger>
                    <SelectValue :placeholder="form.country ? 'Select a province' : 'Select a country first'" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem
                        v-for="province in filteredProvinces"
                        :key="province.name"
                        :value="province.name"
                    >
                        {{ province.name }}
                    </SelectItem>
                </SelectContent>
            </Select>
            <InputError :message="form.errors.province" />
        </div>

        <div class="grid gap-2">
            <Label for="school_name">School Name</Label>
            <Input id="school_name" v-model="form.school_name" required />
            <InputError :message="form.errors.school_name" />
        </div>

        <div class="flex gap-2">
            <Button type="submit" :disabled="form.processing">{{ submitLabel }}</Button>
        </div>
    </form>
</template>
