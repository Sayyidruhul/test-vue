<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

interface Product {
    id: number;
    name: string;
    description: string | null;
    price: string;
    stock: number;
    image: string | null;
    created_at: string;
    updated_at: string;
}

defineProps<{
    product: Product;
}>();
</script>

<template>
    <div class="min-h-screen bg-gray-100 p-8">
        <div class="mx-auto max-w-4xl">
            <!-- Header -->
            <div class="mb-6">
                <Link
                    href="/products"
                    class="text-sm text-blue-600 hover:underline"
                >
                    ← Kembali ke Products
                </Link>

                <h1 class="mt-3 text-3xl font-bold text-gray-800">
                    Detail Product
                </h1>
            </div>

            <!-- Product Card -->
            <div class="overflow-hidden rounded-lg bg-white shadow">
                <div class="grid md:grid-cols-2">
                    <!-- Foto -->
                    <div
                        class="flex min-h-[400px] items-center justify-center bg-gray-50 p-6"
                    >
                        <div
                            v-if="product.image"
                            class="flex h-full max-h-[400px] w-full items-center justify-center"
                        >
                            <img
                                :src="`/storage/${product.image}`"
                                :alt="product.name"
                                class="max-h-[400px] max-w-full rounded-lg object-contain"
                            />
                        </div>

                        <div
                            v-else
                            class="flex h-80 w-full items-center justify-center rounded-lg bg-gray-200"
                        >
                            <span class="text-gray-400"> Tidak ada foto </span>
                        </div>
                    </div>

                    <!-- Informasi -->
                    <div class="p-8">
                        <p class="mb-2 text-sm text-gray-500">
                            Product #{{ product.id }}
                        </p>

                        <h2 class="text-3xl font-bold text-gray-800">
                            {{ product.name }}
                        </h2>

                        <div class="mt-6">
                            <p class="text-sm font-medium text-gray-500">
                                Harga
                            </p>

                            <p class="mt-1 text-2xl font-bold text-blue-600">
                                Rp
                                {{
                                    Number(product.price).toLocaleString(
                                        'id-ID',
                                    )
                                }}
                            </p>
                        </div>

                        <div class="mt-5">
                            <p class="text-sm font-medium text-gray-500">
                                Stok
                            </p>

                            <p class="mt-1 text-lg font-semibold text-gray-800">
                                {{ product.stock }}
                            </p>
                        </div>

                        <div class="mt-5">
                            <p class="text-sm font-medium text-gray-500">
                                Deskripsi
                            </p>

                            <p class="mt-1 whitespace-pre-line text-gray-700">
                                {{
                                    product.description ||
                                    'Tidak ada deskripsi.'
                                }}
                            </p>
                        </div>

                        <!-- Tombol -->
                        <div class="mt-8 flex gap-3">
                            <Link
                                :href="`/products/${product.id}/edit`"
                                class="rounded-lg bg-yellow-500 px-5 py-2 font-semibold text-white hover:bg-yellow-600"
                            >
                                Edit
                            </Link>

                            <Link
                                href="/products"
                                class="rounded-lg border border-gray-300 px-5 py-2 font-medium text-gray-700 hover:bg-gray-50"
                            >
                                Kembali
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
