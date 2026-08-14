<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';

import { Button } from '@/components/ui/button';

import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

/*
|--------------------------------------------------------------------------
| Form
|--------------------------------------------------------------------------
*/

const form = useForm({
    name: '',
    email: '',
    password: '',
    role: 'admin',
});

/*
|--------------------------------------------------------------------------
| Submit
|--------------------------------------------------------------------------
*/

const submit = () => {
    form.post('/users');
};
</script>

<template>
    <Head title="Tambah User" />

    <div
        class="min-h-full w-full flex-1 bg-background px-4 py-6 md:px-8 lg:px-10"
    >
        <div class="w-full">

            <!-- Header -->
            <h1 class="text-3xl font-bold tracking-tight">
                Tambah User
            </h1>

            <p class="mt-1 mb-6 text-muted-foreground">
                Tambahkan user baru ke sistem
            </p>

            <!-- Card -->
            <Card
                class="w-full border-border/70 bg-card/95 shadow-sm"
            >
                <CardHeader>
                    <CardTitle>
                        Informasi User
                    </CardTitle>

                    <CardDescription>
                        Lengkapi data user di bawah ini.
                    </CardDescription>
                </CardHeader>

                <CardContent>
                    <form
                        class="space-y-5"
                        @submit.prevent="submit"
                    >

                        <!-- Nama -->
                        <div class="space-y-2">
                            <Label for="name">
                                Nama
                            </Label>

                            <Input
                                id="name"
                                v-model="form.name"
                                type="text"
                                placeholder="Masukkan nama"
                                :aria-invalid="!!form.errors.name"
                            />

                            <p
                                v-if="form.errors.name"
                                class="text-sm text-destructive"
                            >
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <!-- Email -->
                        <div class="space-y-2">
                            <Label for="email">
                                Email
                            </Label>

                            <Input
                                id="email"
                                v-model="form.email"
                                type="email"
                                placeholder="Masukkan email"
                                :aria-invalid="!!form.errors.email"
                            />

                            <p
                                v-if="form.errors.email"
                                class="text-sm text-destructive"
                            >
                                {{ form.errors.email }}
                            </p>
                        </div>

                        <!-- Password -->
                        <div class="space-y-2">
                            <Label for="password">
                                Password
                            </Label>

                            <Input
                                id="password"
                                v-model="form.password"
                                type="password"
                                placeholder="Minimal 8 karakter"
                                :aria-invalid="!!form.errors.password"
                            />

                            <p class="text-xs text-muted-foreground">
                                Gunakan password minimal 8 karakter.
                            </p>

                            <p
                                v-if="form.errors.password"
                                class="text-sm text-destructive"
                            >
                                {{ form.errors.password }}
                            </p>
                        </div>

                        <!-- Role -->
                        <div class="space-y-2">
                            <Label for="role">
                                Role
                            </Label>

                            <Select v-model="form.role">
                                <SelectTrigger
                                    id="role"
                                    class="w-full"
                                >
                                    <SelectValue
                                        placeholder="Pilih role"
                                    />
                                </SelectTrigger>

                                <SelectContent>
                                    <SelectItem value="admin">
                                        Admin
                                    </SelectItem>

                                    <SelectItem value="super_admin">
                                        Super Admin
                                    </SelectItem>
                                </SelectContent>
                            </Select>

                            <p
                                v-if="form.errors.role"
                                class="text-sm text-destructive"
                            >
                                {{ form.errors.role }}
                            </p>
                        </div>

                        <!-- Buttons -->
                        <div class="flex justify-end gap-3 pt-3">

                            <!-- Batal -->
                            <Button
                                as-child
                                type="button"
                                variant="outline"
                            >
                                <Link href="/users">
                                    Batal
                                </Link>
                            </Button>

                            <!-- Simpan -->
                            <Button
                                type="submit"
                                :disabled="form.processing"
                            >
                                {{
                                    form.processing
                                        ? 'Menyimpan...'
                                        : 'Simpan User'
                                }}
                            </Button>

                        </div>

                    </form>
                </CardContent>
            </Card>

        </div>
    </div>
</template>