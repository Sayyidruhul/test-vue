<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableHeader,
} from '@/components/ui/table';

interface User {
    id: number;
    name: string;
    email: string;
    role: string | null;
}

interface Props {
    users: User[];
}

const props = defineProps<Props>();

/*
|--------------------------------------------------------------------------
| Hapus User
|--------------------------------------------------------------------------
*/

const deleteUser = (id: number) => {
    if (!confirm('Apakah Anda yakin ingin menghapus user ini?')) {
        return;
    }

    const form = useForm({});

    form.delete(`/users/${id}`);
};
</script>

<template>
    <Head title="Manage Users" />

    <div
        class="min-h-full w-full flex-1 bg-background px-4 py-6 md:px-8 lg:px-10"
    >
        <div class="w-full space-y-6">

            <!-- Header -->
            <div
                class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
            >
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">
                        Manage Users
                    </h1>

                    <p class="mt-1 text-muted-foreground">
                        Kelola data user
                    </p>
                </div>

                <!-- Tambah User -->
                <Button as-child>
                    <Link href="/users/create">
                        + Tambah User
                    </Link>
                </Button>
            </div>

            <!-- Jumlah User -->
            <div class="text-sm text-muted-foreground">
                Menampilkan
                <strong>
                    {{ props.users.length }}
                </strong>
                user.
            </div>

            <!-- Table -->
            <Card
                class="overflow-hidden border-border/70 bg-card/95 shadow-sm"
            >
                <Table>

                    <!-- Header Table -->
                    <TableHeader>
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-sm font-semibold text-muted-foreground"
                            >
                                No
                            </th>

                            <th
                                class="px-6 py-3 text-left text-sm font-semibold text-muted-foreground"
                            >
                                Nama
                            </th>

                            <th
                                class="px-6 py-3 text-left text-sm font-semibold text-muted-foreground"
                            >
                                Email
                            </th>

                            <th
                                class="px-6 py-3 text-left text-sm font-semibold text-muted-foreground"
                            >
                                Role
                            </th>

                            <th
                                class="px-6 py-3 text-center text-sm font-semibold text-muted-foreground"
                            >
                                Aksi
                            </th>
                        </tr>
                    </TableHeader>

                    <!-- Body -->
                    <TableBody>

                        <!-- User -->
                        <tr
                            v-for="(user, index) in props.users"
                            :key="user.id"
                            class="transition-colors hover:bg-muted/40"
                        >

                            <!-- No -->
                            <td class="px-6 py-4 text-foreground">
                                {{ index + 1 }}
                            </td>

                            <!-- Nama -->
                            <td
                                class="px-6 py-4 font-medium text-foreground"
                            >
                                {{ user.name }}
                            </td>

                            <!-- Email -->
                            <td class="px-6 py-4 text-muted-foreground">
                                {{ user.email }}
                            </td>

                            <!-- Role -->
                            <td class="px-6 py-4">

                                <!-- Super Admin -->
                                <Badge
                                    v-if="user.role === 'super_admin'"
                                    variant="secondary"
                                    class="bg-purple-100 text-purple-700 hover:bg-purple-100"
                                >
                                    Super Admin
                                </Badge>

                                <!-- Admin -->
                                <Badge
                                    v-else-if="user.role === 'admin'"
                                    variant="secondary"
                                    class="bg-blue-100 text-blue-700 hover:bg-blue-100"
                                >
                                    Admin
                                </Badge>

                                <!-- Belum Ada Role -->
                                <Badge
                                    v-else
                                    variant="secondary"
                                >
                                    Belum ada role
                                </Badge>

                            </td>

                            <!-- Aksi -->
                            <td class="px-6 py-4">
                                <div class="flex justify-center gap-2">

                                    <!-- Edit -->
                                    <Button
                                        as-child
                                        size="sm"
                                        variant="outline"
                                    >
                                        <Link
                                            :href="`/users/${user.id}/edit`"
                                        >
                                            Edit
                                        </Link>
                                    </Button>

                                    <!-- Hapus -->
                                    <Button
                                        type="button"
                                        size="sm"
                                        variant="destructive"
                                        @click="deleteUser(user.id)"
                                    >
                                        Hapus
                                    </Button>

                                </div>
                            </td>

                        </tr>

                        <!-- Tidak Ada User -->
                        <tr v-if="props.users.length === 0">
                            <td
                                colspan="5"
                                class="px-6 py-10 text-center text-muted-foreground"
                            >
                                Belum ada user.
                            </td>
                        </tr>

                    </TableBody>
                </Table>
            </Card>

        </div>
    </div>
</template>