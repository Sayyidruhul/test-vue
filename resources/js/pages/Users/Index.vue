<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3'

interface User {
    id: number
    name: string
    email: string
    role: string | null
}

interface Props {
    users: User[]
}

const props = defineProps<Props>()

const deleteUser = (id: number) => {
    if (!confirm('Apakah Anda yakin ingin menghapus user ini?')) {
        return
    }

    const form = useForm({})

    form.delete(`/users/${id}`)
}
</script>

<template>
    <div class="min-h-screen bg-gray-100 p-8">
        <div class="mx-auto max-w-7xl">

            <!-- Header -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">
                        Manage Users
                    </h1>

                    <p class="mt-1 text-gray-600">
                        Kelola data user
                    </p>
                </div>

                <!-- Tambah User -->
                <Link
                    href="/users/create"
                    class="rounded-lg bg-blue-600 px-4 py-2 font-semibold text-white hover:bg-blue-700"
                >
                    + Tambah User
                </Link>
            </div>

            <!-- Table -->
            <div class="overflow-hidden rounded-lg bg-white shadow">
                <table class="w-full">

                    <!-- Header Table -->
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-sm font-semibold text-gray-600"
                            >
                                No
                            </th>

                            <th
                                class="px-6 py-3 text-left text-sm font-semibold text-gray-600"
                            >
                                Nama
                            </th>

                            <th
                                class="px-6 py-3 text-left text-sm font-semibold text-gray-600"
                            >
                                Email
                            </th>

                            <th
                                class="px-6 py-3 text-left text-sm font-semibold text-gray-600"
                            >
                                Role
                            </th>

                            <th
                                class="px-6 py-3 text-center text-sm font-semibold text-gray-600"
                            >
                                Aksi
                            </th>
                        </tr>
                    </thead>

                    <!-- Body -->
                    <tbody class="divide-y divide-gray-200">

                        <tr
                            v-for="(user, index) in props.users"
                            :key="user.id"
                            class="hover:bg-gray-50"
                        >

                            <!-- No -->
                            <td class="px-6 py-4 text-gray-800">
                                {{ index + 1 }}
                            </td>

                            <!-- Nama -->
                            <td class="px-6 py-4 font-medium text-gray-800">
                                {{ user.name }}
                            </td>

                            <!-- Email -->
                            <td class="px-6 py-4 text-gray-600">
                                {{ user.email }}
                            </td>

                            <!-- Role -->
                            <td class="px-6 py-4">

                                <span
                                    v-if="user.role === 'super_admin'"
                                    class="rounded-full bg-purple-100 px-3 py-1 text-sm font-medium text-purple-700"
                                >
                                    Super Admin
                                </span>

                                <span
                                    v-else-if="user.role === 'admin'"
                                    class="rounded-full bg-blue-100 px-3 py-1 text-sm font-medium text-blue-700"
                                >
                                    Admin
                                </span>

                                <span
                                    v-else
                                    class="rounded-full bg-gray-100 px-3 py-1 text-sm font-medium text-gray-600"
                                >
                                    Belum ada role
                                </span>

                            </td>

                            <!-- Aksi -->
                            <td class="px-6 py-4">
                                <div class="flex justify-center gap-2">

                                    <!-- Edit -->
                                    <Link
                                        :href="`/users/${user.id}/edit`"
                                        class="rounded bg-yellow-500 px-3 py-1 text-sm font-medium text-white hover:bg-yellow-600"
                                    >
                                        Edit
                                    </Link>

                                    <!-- Delete -->
                                    <button
                                        type="button"
                                        @click="deleteUser(user.id)"
                                        class="rounded bg-red-600 px-3 py-1 text-sm font-medium text-white hover:bg-red-700"
                                    >
                                        Hapus
                                    </button>

                                </div>
                            </td>
                        </tr>

                        <!-- Tidak ada user -->
                        <tr v-if="props.users.length === 0">
                            <td
                                colspan="5"
                                class="px-6 py-10 text-center text-gray-500"
                            >
                                Belum ada user.
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>

        </div>
    </div>
</template>