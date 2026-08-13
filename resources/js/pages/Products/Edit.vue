<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

import Form from './components/Form.vue';

interface Product {
    id: number;
    name: string;
    description: string | null;
    price: string | number;
    stock: number;
    status: 'aktif' | 'tidak_aktif';
    image: string | null;
}

defineProps<{
    product: Product;
}>();
</script>

<template>
    <Head title="Edit Product" />

    <div
        class="min-h-full w-full flex-1 bg-background px-4 py-6 md:px-8 lg:px-10"
    >
        <div class="w-full">

            <!-- Header -->
            <h1 class="text-3xl font-bold tracking-tight">
                Edit Product
            </h1>

            <p class="mt-1 mb-6 text-muted-foreground">
                Ubah informasi product
            </p>

            <!-- Card Form -->
            <Card class="w-full border-border/70 bg-card/95 shadow-sm">

                <CardHeader>
                    <CardTitle>
                        Informasi Product
                    </CardTitle>

                    <CardDescription>
                        Perbarui data product di bawah ini.
                    </CardDescription>
                </CardHeader>

                <CardContent>
                    <Form :product="product" />
                </CardContent>

            </Card>

        </div>
    </div>
</template>