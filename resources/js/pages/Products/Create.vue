<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    name: '',
    description: '',
    price: '',
    stock: 0,
    status: 'aktif',
    image: null as File | null,
});

const imagePreview = ref<string | null>(null);

const handleImage = (event: Event) => {
    const target = event.target as HTMLInputElement;

    if (target.files && target.files[0]) {
        const file = target.files[0];

        form.image = file;
        imagePreview.value = URL.createObjectURL(file);
    }
};

const submit = () => {
    form.post('/products', {
        forceFormData: true,
    });
};
</script>

<template>
    <div class="min-h-screen bg-gray-100 p-8">
        <div class="mx-auto max-w-2xl">

            <!-- Header -->
            <div class="mb-6">
                <Link
                    href="/products"
                    class="text-sm text-blue-600 hover:underline"
                >
                    ← Kembali ke Products
                </Link>

                <h1 class="mt-3 text-3xl font-bold text-gray-800">
                    Tambah Product
                </h1>

                <p class="mt-1 text-gray-600">
                    Tambahkan product baru
                </p>
            </div>

            <!-- Form -->
            <div class="rounded-lg bg-white p-6 shadow">
                <form @submit.prevent="submit" class="space-y-5">

                    <!-- Nama -->
                    <div>
                        <label
                            for="name"
                            class="mb-2 block text-sm font-medium text-gray-700"
                        >
                            Nama Product
                        </label>

                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            placeholder="Masukkan nama product"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-800 placeholder-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none"
                        />

                        <p
                            v-if="form.errors.name"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <!-- Deskripsi -->
                    <div>
                        <label
                            for="description"
                            class="mb-2 block text-sm font-medium text-gray-700"
                        >
                            Deskripsi
                        </label>

                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="4"
                            placeholder="Masukkan deskripsi product"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-800 placeholder-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none"
                        ></textarea>

                        <p
                            v-if="form.errors.description"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.description }}
                        </p>
                    </div>

                    <!-- Harga -->
                    <div>
                        <label
                            for="price"
                            class="mb-2 block text-sm font-medium text-gray-700"
                        >
                            Harga
                        </label>

                        <input
                            id="price"
                            v-model="form.price"
                            type="number"
                            min="0"
                            placeholder="Contoh: 5000000"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-800 placeholder-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none"
                        />

                        <p
                            v-if="form.errors.price"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.price }}
                        </p>
                    </div>

                    <!-- Stok -->
                    <div>
                        <label
                            for="stock"
                            class="mb-2 block text-sm font-medium text-gray-700"
                        >
                            Stok
                        </label>

                        <input
                            id="stock"
                            v-model="form.stock"
                            type="number"
                            min="0"
                            placeholder="Contoh: 10"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-800 placeholder-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none"
                        />

                        <p
                            v-if="form.errors.stock"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.stock }}
                        </p>
                    </div>

                    <!-- Status -->
                    <div>
                        <label
                            for="status"
                            class="mb-2 block text-sm font-medium text-gray-700"
                        >
                            Status
                        </label>

                        <select
                            id="status"
                            v-model="form.status"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none"
                        >
                            <option value="aktif">
                                Aktif
                            </option>

                            <option value="tidak_aktif">
                                Tidak Aktif
                            </option>
                        </select>

                        <p class="mt-1 text-xs text-gray-500">
                            Pilih apakah product aktif atau tidak aktif.
                        </p>

                        <p
                            v-if="form.errors.status"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.status }}
                        </p>
                    </div>

                    <!-- Foto -->
                    <div>
                        <label
                            for="image"
                            class="mb-2 block text-sm font-medium text-gray-700"
                        >
                            Foto Product
                        </label>

                        <input
                            id="image"
                            type="file"
                            accept="image/jpeg,image/png,image/webp"
                            @change="handleImage"
                            class="block w-full rounded-lg border border-gray-300 bg-white text-sm text-gray-800 file:mr-4 file:rounded-lg file:border-0 file:bg-blue-600 file:px-4 file:py-2 file:font-semibold file:text-white hover:file:bg-blue-700"
                        />

                        <p class="mt-1 text-xs text-gray-500">
                            Format: JPG, JPEG, PNG, WEBP. Maksimal 2 MB.
                        </p>

                        <p
                            v-if="form.errors.image"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.image }}
                        </p>

                        <!-- Preview -->
                        <div v-if="imagePreview" class="mt-4">
                            <p class="mb-2 text-sm font-medium text-gray-700">
                                Preview:
                            </p>

                            <img
                                :src="imagePreview"
                                alt="Preview Product"
                                class="h-48 w-full rounded-lg border border-gray-200 object-contain"
                            />
                        </div>
                    </div>

                    <!-- Tombol -->
                    <div class="flex justify-end gap-3 pt-3">
                        <Link
                            href="/products"
                            class="rounded-lg border border-gray-300 px-5 py-2 font-medium text-gray-700 hover:bg-gray-50"
                        >
                            Batal
                        </Link>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-lg bg-blue-600 px-5 py-2 font-semibold text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            {{
                                form.processing
                                    ? 'Menyimpan...'
                                    : 'Simpan Product'
                            }}
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </div>
</template>