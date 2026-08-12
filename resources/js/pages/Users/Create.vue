<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3'

const form = useForm({
    name: '',
    email: '',
    password: '',
    role: 'admin',
})

const submit = () => {
    form.post('/users')
}
</script>

<template>
    <div class="min-h-screen bg-gray-100 p-8">
        <div class="mx-auto max-w-2xl">

            <!-- Header -->
            <div class="mb-6">
                <Link
                    href="/users"
                    class="text-sm font-medium text-blue-600 hover:text-blue-800"
                >
                    ← Kembali ke Users
                </Link>

                <h1 class="mt-4 text-3xl font-bold text-gray-800">
                    Tambah User
                </h1>

                <p class="mt-1 text-gray-600">
                    Tambahkan user baru ke sistem
                </p>
            </div>

            <!-- Form -->
            <div class="rounded-lg bg-white p-6 shadow">

                <!-- Nama -->
                <div class="mb-5">
                    <label
                        for="name"
                        class="mb-2 block text-sm font-semibold text-gray-700"
                    >
                        Nama
                    </label>

                    <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        placeholder="Masukkan nama"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2 text-gray-800 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                    />

                    <p
                        v-if="form.errors.name"
                        class="mt-1 text-sm text-red-600"
                    >
                        {{ form.errors.name }}
                    </p>
                </div>

                <!-- Email -->
                <div class="mb-5">
                    <label
                        for="email"
                        class="mb-2 block text-sm font-semibold text-gray-700"
                    >
                        Email
                    </label>

                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        placeholder="Masukkan email"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2 text-gray-800 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                    />

                    <p
                        v-if="form.errors.email"
                        class="mt-1 text-sm text-red-600"
                    >
                        {{ form.errors.email }}
                    </p>
                </div>

                <!-- Password -->
                <div class="mb-5">
                    <label
                        for="password"
                        class="mb-2 block text-sm font-semibold text-gray-700"
                    >
                        Password
                    </label>

                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        placeholder="Minimal 8 karakter"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2 text-gray-800 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                    />

                    <p
                        v-if="form.errors.password"
                        class="mt-1 text-sm text-red-600"
                    >
                        {{ form.errors.password }}
                    </p>
                </div>

                <!-- Role -->
                <div class="mb-6">
                    <label
                        for="role"
                        class="mb-2 block text-sm font-semibold text-gray-700"
                    >
                        Role
                    </label>

                    <select
                        id="role"
                        v-model="form.role"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-800 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                    >
                        <option value="admin">
                            Admin
                        </option>

                        <option value="super_admin">
                            Super Admin
                        </option>
                    </select>

                    <p
                        v-if="form.errors.role"
                        class="mt-1 text-sm text-red-600"
                    >
                        {{ form.errors.role }}
                    </p>
                </div>

                <!-- Buttons -->
                <div class="flex justify-end gap-3">
                    <Link
                        href="/users"
                        class="rounded-lg border border-gray-300 px-5 py-2 font-semibold text-gray-700 hover:bg-gray-50"
                    >
                        Batal
                    </Link>

                    <button
                        type="button"
                        @click="submit"
                        :disabled="form.processing"
                        class="rounded-lg bg-blue-600 px-5 py-2 font-semibold text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        {{
                            form.processing
                                ? 'Menyimpan...'
                                : 'Simpan User'
                        }}
                    </button>
                </div>

            </div>
        </div>
    </div>
</template>