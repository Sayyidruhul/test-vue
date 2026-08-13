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
</script>

<template>
    <Head title="Tambah Product" />

    <div
        class="min-h-full w-full flex-1 bg-background px-4 py-6 md:px-8 lg:px-10"
    >
        <div class="w-full">

            <!-- Header -->
            <h1 class="text-3xl font-bold tracking-tight">
                Tambah Product
            </h1>

            <p class="mt-1 mb-6 text-muted-foreground">
                Tambahkan product baru
            </p>

            <!-- Card Form -->
            <Card class="w-full border-border/70 bg-card/95 shadow-sm">

                <CardHeader>
                    <CardTitle>
                        Informasi Product
                    </CardTitle>

                    <CardDescription>
                        Lengkapi data product di bawah ini.
                    </CardDescription>
                </CardHeader>

                <CardContent>
                    <Form />
                </CardContent>

            </Card>

        </div>
    </div>
</template>