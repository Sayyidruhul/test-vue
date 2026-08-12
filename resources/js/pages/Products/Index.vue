<script setup lang="ts">
import { Link, router, useForm, usePage } from '@inertiajs/vue3'
import { ref, watch } from 'vue'

interface Product {
    id: number
    name: string
    description: string | null
    price: string | number
    stock: number
    status: 'aktif' | 'tidak_aktif'
    image: string | null
}

interface Filters {
    search: string
    min_price: string | number
    max_price: string | number
    status: string
}

interface Props {
    products: Product[]
    filters: Filters
}

const props = defineProps<Props>()

const page = usePage()

const user = page.props.auth?.user as any

const isSuperAdmin = user?.role === 'super_admin'

/*
|--------------------------------------------------------------------------
| Search & Filter
|--------------------------------------------------------------------------
*/

const search = ref(props.filters?.search ?? '')

const minPrice = ref(
    props.filters?.min_price !== undefined &&
    props.filters?.min_price !== null
        ? String(props.filters.min_price)
        : '',
)

const maxPrice = ref(
    props.filters?.max_price !== undefined &&
    props.filters?.max_price !== null
        ? String(props.filters.max_price)
        : '',
)

const status = ref(props.filters?.status ?? '')

/*
|--------------------------------------------------------------------------
| Jalankan Filter
|--------------------------------------------------------------------------
*/

const applyFilter = () => {
    const params: Record<string, string> = {}

    if (search.value.trim() !== '') {
        params.search = search.value.trim()
    }

    if (minPrice.value !== '') {
        params.min_price = minPrice.value
    }

    if (maxPrice.value !== '') {
        params.max_price = maxPrice.value
    }

    if (status.value !== '') {
        params.status = status.value
    }

    router.get(
        '/products',
        params,
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    )
}

/*
|--------------------------------------------------------------------------
| Realtime Search
|--------------------------------------------------------------------------
|
| Search, harga, dan status otomatis dijalankan.
|
| Delay 350ms digunakan supaya request tidak dikirim
| setiap kali user menekan satu tombol keyboard.
|
*/

let filterTimer: ReturnType<typeof setTimeout> | null = null

watch(
    [search, minPrice, maxPrice, status],
    () => {
        if (filterTimer) {
            clearTimeout(filterTimer)
        }

        filterTimer = setTimeout(() => {
            applyFilter()
        }, 350)
    },
)

/*
|--------------------------------------------------------------------------
| Reset Filter
|--------------------------------------------------------------------------
*/

const resetFilter = () => {
    search.value = ''
    minPrice.value = ''
    maxPrice.value = ''
    status.value = ''
}

/*
|--------------------------------------------------------------------------
| Hapus Product
|--------------------------------------------------------------------------
*/

const deleteProduct = (id: number) => {
    if (!confirm('Apakah Anda yakin ingin menghapus product ini?')) {
        return
    }

    const form = useForm({})

    form.delete(`/products/${id}`)
}

/*
|--------------------------------------------------------------------------
| Export PDF
|--------------------------------------------------------------------------
*/

const exportPdf = () => {
    window.open('/products/export-pdf', '_blank')
}

/*
|--------------------------------------------------------------------------
| Export Excel
|--------------------------------------------------------------------------
*/

const exportExcel = () => {
    window.open('/products/export-excel', '_blank')
}

/*
|--------------------------------------------------------------------------
| Import Excel
|--------------------------------------------------------------------------
*/

const fileInput = ref<HTMLInputElement | null>(null)

const importForm = useForm<{
    file: File | null
}>({
    file: null,
})

const openImportFile = () => {
    fileInput.value?.click()
}

const handleImportFile = (event: Event) => {
    const target = event.target as HTMLInputElement

    if (!target.files || target.files.length === 0) {
        return
    }

    const file = target.files[0]

    importForm.file = file

    const confirmed = confirm(
        `Apakah Anda yakin ingin meng-import file "${file.name}"?`,
    )

    if (!confirmed) {
        importForm.reset()

        if (fileInput.value) {
            fileInput.value.value = ''
        }

        return
    }

    importForm.post('/products/import-excel', {
        forceFormData: true,
        preserveScroll: true,

        onSuccess: () => {
            importForm.reset()

            if (fileInput.value) {
                fileInput.value.value = ''
            }
        },

        onError: () => {
            if (fileInput.value) {
                fileInput.value.value = ''
            }
        },
    })
}
</script>

<template>
    <div class="min-h-screen bg-gray-100 p-8">
        <div class="mx-auto max-w-7xl">

            <!-- Header -->
            <div class="mb-6 flex items-start justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">
                        Products
                    </h1>

                    <p class="mt-1 text-gray-600">
                        Kelola data product
                    </p>
                </div>

                <div class="flex flex-wrap justify-end gap-2">

                    <!-- Input Import Excel -->
                    <input
                        ref="fileInput"
                        type="file"
                        accept=".xlsx,.xls"
                        class="hidden"
                        @change="handleImportFile"
                    />

                    <!-- Import Excel -->
                    <button
                        v-if="isSuperAdmin"
                        type="button"
                        :disabled="importForm.processing"
                        @click="openImportFile"
                        class="rounded-lg bg-emerald-600 px-4 py-2 font-semibold text-white hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        {{
                            importForm.processing
                                ? 'Importing...'
                                : 'Import Excel'
                        }}
                    </button>

                    <!-- Export Excel -->
                    <button
                        v-if="isSuperAdmin"
                        type="button"
                        @click="exportExcel"
                        class="rounded-lg bg-green-600 px-4 py-2 font-semibold text-white hover:bg-green-700"
                    >
                        Export Excel
                    </button>

                    <!-- Export PDF -->
                    <button
                        v-if="isSuperAdmin"
                        type="button"
                        @click="exportPdf"
                        class="rounded-lg bg-red-600 px-4 py-2 font-semibold text-white hover:bg-red-700"
                    >
                        Export PDF
                    </button>

                    <!-- Tambah Product -->
                    <Link
                        href="/products/create"
                        class="rounded-lg bg-blue-600 px-4 py-2 font-semibold text-white hover:bg-blue-700"
                    >
                        + Tambah Product
                    </Link>

                </div>
            </div>

            <!-- Search dan Filter -->
            <div class="mb-6 rounded-lg bg-white p-5 shadow">

                <div class="mb-4">
                    <h2 class="text-lg font-semibold text-gray-800">
                        Search & Filter Product
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        Hasil akan otomatis berubah saat pencarian atau filter diubah.
                    </p>
                </div>

                <div
                    class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4"
                >

                    <!-- Search -->
                    <div>
                        <label
                            for="search"
                            class="mb-2 block text-sm font-medium text-gray-700"
                        >
                            Cari Product
                        </label>

                        <input
                            id="search"
                            v-model="search"
                            type="text"
                            placeholder="Nama atau deskripsi..."
                            autocomplete="off"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-800 placeholder-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none"
                        />

                        <p class="mt-1 text-xs text-gray-500">
                            Cari berdasarkan nama atau deskripsi.
                        </p>
                    </div>

                    <!-- Harga Minimum -->
                    <div>
                        <label
                            for="min_price"
                            class="mb-2 block text-sm font-medium text-gray-700"
                        >
                            Harga Minimum
                        </label>

                        <input
                            id="min_price"
                            v-model="minPrice"
                            type="number"
                            min="0"
                            placeholder="Contoh: 50000"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-800 placeholder-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none"
                        />
                    </div>

                    <!-- Harga Maksimum -->
                    <div>
                        <label
                            for="max_price"
                            class="mb-2 block text-sm font-medium text-gray-700"
                        >
                            Harga Maksimum
                        </label>

                        <input
                            id="max_price"
                            v-model="maxPrice"
                            type="number"
                            min="0"
                            placeholder="Contoh: 500000"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-800 placeholder-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none"
                        />
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
                            v-model="status"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none"
                        >
                            <option value="">
                                Semua Status
                            </option>

                            <option value="aktif">
                                Aktif
                            </option>

                            <option value="tidak_aktif">
                                Tidak Aktif
                            </option>
                        </select>
                    </div>

                </div>

                <!-- Reset -->
                <div class="mt-4">
                    <button
                        type="button"
                        @click="resetFilter"
                        class="rounded-lg border border-gray-300 bg-white px-5 py-2 font-semibold text-gray-700 hover:bg-gray-50"
                    >
                        Reset Filter
                    </button>
                </div>

            </div>

            <!-- Error Import Excel -->
            <div
                v-if="importForm.errors.file"
                class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
            >
                {{ importForm.errors.file }}
            </div>

            <!-- Informasi Format Import -->
            <div
                v-if="isSuperAdmin"
                class="mb-6 rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-700"
            >
                Format Import Excel:
                <strong>
                    Nama | Deskripsi | Harga | Stok | Status
                </strong>

                <span class="ml-1">
                    (Status opsional: Aktif / Tidak Aktif)
                </span>
            </div>

            <!-- Jumlah Hasil -->
            <div class="mb-3 text-sm text-gray-600">
                Menampilkan
                <strong>
                    {{ props.products.length }}
                </strong>
                product.
            </div>

            <!-- Table -->
            <div class="overflow-x-auto rounded-lg bg-white shadow">
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
                                Foto
                            </th>

                            <th
                                class="px-6 py-3 text-left text-sm font-semibold text-gray-600"
                            >
                                Nama
                            </th>

                            <th
                                class="px-6 py-3 text-left text-sm font-semibold text-gray-600"
                            >
                                Deskripsi
                            </th>

                            <th
                                class="px-6 py-3 text-left text-sm font-semibold text-gray-600"
                            >
                                Harga
                            </th>

                            <th
                                class="px-6 py-3 text-left text-sm font-semibold text-gray-600"
                            >
                                Stok
                            </th>

                            <th
                                class="px-6 py-3 text-center text-sm font-semibold text-gray-600"
                            >
                                Status
                            </th>

                            <th
                                class="px-6 py-3 text-center text-sm font-semibold text-gray-600"
                            >
                                Aksi
                            </th>

                        </tr>
                    </thead>

                    <!-- Isi Table -->
                    <tbody class="divide-y divide-gray-200">

                        <tr
                            v-for="(product, index) in props.products"
                            :key="product.id"
                            class="hover:bg-gray-50"
                        >

                            <!-- No -->
                            <td class="px-6 py-4 text-gray-800">
                                {{ index + 1 }}
                            </td>

                            <!-- Foto -->
                            <td class="px-6 py-4">
                                <div
                                    class="flex h-20 w-20 items-center justify-center overflow-hidden rounded-lg bg-gray-100"
                                >
                                    <img
                                        v-if="product.image"
                                        :src="`/storage/${product.image}`"
                                        :alt="product.name"
                                        class="h-full w-full object-cover"
                                    />

                                    <span
                                        v-else
                                        class="text-xs text-gray-400"
                                    >
                                        No Image
                                    </span>
                                </div>
                            </td>

                            <!-- Nama -->
                            <td class="px-6 py-4 font-medium text-gray-800">
                                {{ product.name }}
                            </td>

                            <!-- Deskripsi -->
                            <td class="px-6 py-4 text-gray-600">
                                {{ product.description || '-' }}
                            </td>

                            <!-- Harga -->
                            <td
                                class="whitespace-nowrap px-6 py-4 text-gray-800"
                            >
                                Rp
                                {{
                                    Number(product.price).toLocaleString(
                                        'id-ID',
                                    )
                                }}
                            </td>

                            <!-- Stok -->
                            <td class="px-6 py-4 text-gray-800">
                                {{ product.stock }}
                            </td>

                            <!-- Status -->
                            <td class="px-6 py-4 text-center">

                                <!-- Tidak Aktif -->
                                <span
                                    v-if="
                                        Number(product.stock) === 0 ||
                                        product.status === 'tidak_aktif'
                                    "
                                    class="inline-flex rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700"
                                >
                                    Tidak Aktif
                                </span>

                                <!-- Aktif -->
                                <span
                                    v-else
                                    class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700"
                                >
                                    Aktif
                                </span>

                            </td>

                            <!-- Aksi -->
                            <td class="px-6 py-4">
                                <div class="flex justify-center gap-2">

                                    <!-- Lihat -->
                                    <Link
                                        :href="`/products/${product.id}`"
                                        class="rounded bg-blue-600 px-3 py-1 text-sm font-medium text-white hover:bg-blue-700"
                                    >
                                        Lihat
                                    </Link>

                                    <!-- Edit -->
                                    <Link
                                        :href="`/products/${product.id}/edit`"
                                        class="rounded bg-yellow-500 px-3 py-1 text-sm font-medium text-white hover:bg-yellow-600"
                                    >
                                        Edit
                                    </Link>

                                    <!-- Hapus -->
                                    <button
                                        v-if="isSuperAdmin"
                                        type="button"
                                        @click="deleteProduct(product.id)"
                                        class="rounded bg-red-600 px-3 py-1 text-sm font-medium text-white hover:bg-red-700"
                                    >
                                        Hapus
                                    </button>

                                </div>
                            </td>

                        </tr>

                        <!-- Tidak Ada Hasil -->
                        <tr v-if="props.products.length === 0">
                            <td
                                colspan="8"
                                class="px-6 py-10 text-center text-gray-500"
                            >
                                Product tidak ditemukan.
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>

        </div>
    </div>
</template>