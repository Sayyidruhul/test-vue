<script setup lang="ts">
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Badge } from '@/components/ui/badge';
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
import {
    Table,
    TableBody,
    TableHeader,
} from '@/components/ui/table';

interface Product {
    id: number;
    name: string;
    description: string | null;
    price: string | number;
    stock: number;
    status: 'aktif' | 'tidak_aktif';
    image: string | null;
}

interface Filters {
    search: string;
    min_price: string | number;
    max_price: string | number;
    status: string;
}

interface Props {
    products: Product[];
    filters: Filters;
}

const props = defineProps<Props>();

const page = usePage();

const user = page.props.auth?.user as any;

const isSuperAdmin = user?.role === 'super_admin';

/*
|--------------------------------------------------------------------------
| Search & Filter
|--------------------------------------------------------------------------
*/

const search = ref(props.filters?.search ?? '');

const minPrice = ref(
    props.filters?.min_price !== undefined &&
    props.filters?.min_price !== null
        ? String(props.filters.min_price)
        : '',
);

const maxPrice = ref(
    props.filters?.max_price !== undefined &&
    props.filters?.max_price !== null
        ? String(props.filters.max_price)
        : '',
);

const status = ref(
    props.filters?.status && props.filters.status !== ''
        ? props.filters.status
        : 'semua',
);

/*
|--------------------------------------------------------------------------
| Jalankan Filter
|--------------------------------------------------------------------------
*/

const applyFilter = () => {
    const params: Record<string, string> = {};

    if (search.value.trim() !== '') {
        params.search = search.value.trim();
    }

    if (minPrice.value !== '') {
        params.min_price = minPrice.value;
    }

    if (maxPrice.value !== '') {
        params.max_price = maxPrice.value;
    }

    if (status.value !== 'semua') {
        params.status = status.value;
    }

    router.get('/products', params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

/*
|--------------------------------------------------------------------------
| Realtime Search
|--------------------------------------------------------------------------
*/

let filterTimer: ReturnType<typeof setTimeout> | null = null;

watch([search, minPrice, maxPrice, status], () => {
    if (filterTimer) {
        clearTimeout(filterTimer);
    }

    filterTimer = setTimeout(() => {
        applyFilter();
    }, 350);
});

/*
|--------------------------------------------------------------------------
| Reset Filter
|--------------------------------------------------------------------------
*/

const resetFilter = () => {
    search.value = '';
    minPrice.value = '';
    maxPrice.value = '';
    status.value = 'semua';
};

/*
|--------------------------------------------------------------------------
| Hapus Product
|--------------------------------------------------------------------------
*/

const deleteProduct = (id: number) => {
    if (!confirm('Apakah Anda yakin ingin menghapus product ini?')) {
        return;
    }

    const form = useForm({});

    form.delete(`/products/${id}`);
};

/*
|--------------------------------------------------------------------------
| Export PDF
|--------------------------------------------------------------------------
*/

const exportPdf = () => {
    window.open('/products/export-pdf', '_blank');
};

/*
|--------------------------------------------------------------------------
| Export Excel
|--------------------------------------------------------------------------
*/

const exportExcel = () => {
    window.open('/products/export-excel', '_blank');
};

/*
|--------------------------------------------------------------------------
| Import Excel
|--------------------------------------------------------------------------
*/

const fileInput = ref<HTMLInputElement | null>(null);

const importForm = useForm<{
    file: File | null;
}>({
    file: null,
});

const openImportFile = () => {
    fileInput.value?.click();
};

const handleImportFile = (event: Event) => {
    const target = event.target as HTMLInputElement;

    if (!target.files || target.files.length === 0) {
        return;
    }

    const file = target.files[0];

    importForm.file = file;

    const confirmed = confirm(
        `Apakah Anda yakin ingin meng-import file "${file.name}"?`,
    );

    if (!confirmed) {
        importForm.reset();

        if (fileInput.value) {
            fileInput.value.value = '';
        }

        return;
    }

    importForm.post('/products/import-excel', {
        forceFormData: true,
        preserveScroll: true,

        onSuccess: () => {
            importForm.reset();

            if (fileInput.value) {
                fileInput.value.value = '';
            }
        },

        onError: () => {
            if (fileInput.value) {
                fileInput.value.value = '';
            }
        },
    });
};
</script>

<template>
    <Head title="Products" />

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
                        Products
                    </h1>

                    <p class="mt-1 text-muted-foreground">
                        Kelola data product
                    </p>
                </div>

                <div class="flex flex-wrap gap-2 sm:justify-end">

                    <!-- Input Import Excel -->
                    <input
                        ref="fileInput"
                        type="file"
                        accept=".xlsx,.xls"
                        class="hidden"
                        @change="handleImportFile"
                    />

                    <!-- Import Excel -->
                    <Button
                        v-if="isSuperAdmin"
                        type="button"
                        variant="outline"
                        :disabled="importForm.processing"
                        @click="openImportFile"
                    >
                        {{
                            importForm.processing
                                ? 'Importing...'
                                : 'Import Excel'
                        }}
                    </Button>

                    <!-- Export Excel -->
                    <Button
                        v-if="isSuperAdmin"
                        type="button"
                        variant="outline"
                        @click="exportExcel"
                    >
                        Export Excel
                    </Button>

                    <!-- Export PDF -->
                    <Button
                        v-if="isSuperAdmin"
                        type="button"
                        variant="outline"
                        @click="exportPdf"
                    >
                        Export PDF
                    </Button>

                    <!-- Tambah Product -->
                    <Button as-child>
                        <Link href="/products/create">
                            + Tambah Product
                        </Link>
                    </Button>

                </div>
            </div>

            <!-- Search & Filter -->
            <Card class="border-border/70 bg-card/95 shadow-sm">
                <CardHeader>
                    <CardTitle>
                        Search & Filter Product
                    </CardTitle>

                    <CardDescription>
                        Hasil akan otomatis berubah saat pencarian atau filter
                        diubah.
                    </CardDescription>
                </CardHeader>

                <CardContent class="space-y-4">
                    <div
                        class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4"
                    >

                        <!-- Search -->
                        <div>
                            <Label for="search">
                                Cari Product
                            </Label>

                            <Input
                                id="search"
                                v-model="search"
                                class="mt-2"
                                placeholder="Nama atau deskripsi..."
                                autocomplete="off"
                            />

                            <p class="mt-1 text-xs text-muted-foreground">
                                Cari berdasarkan nama atau deskripsi.
                            </p>
                        </div>

                        <!-- Harga Minimum -->
                        <div>
                            <Label for="min_price">
                                Harga Minimum
                            </Label>

                            <Input
                                id="min_price"
                                v-model="minPrice"
                                class="mt-2"
                                type="number"
                                min="0"
                                placeholder="Contoh: 50000"
                            />
                        </div>

                        <!-- Harga Maksimum -->
                        <div>
                            <Label for="max_price">
                                Harga Maksimum
                            </Label>

                            <Input
                                id="max_price"
                                v-model="maxPrice"
                                class="mt-2"
                                type="number"
                                min="0"
                                placeholder="Contoh: 500000"
                            />
                        </div>

                        <!-- Status -->
                        <div>
                            <Label for="status">
                                Status
                            </Label>

                            <Select v-model="status">
                                <SelectTrigger
                                    id="status"
                                    class="mt-2 w-full"
                                >
                                    <SelectValue
                                        placeholder="Semua Status"
                                    />
                                </SelectTrigger>

                                <SelectContent>
                                    <SelectItem value="semua">
                                        Semua Status
                                    </SelectItem>

                                    <SelectItem value="aktif">
                                        Aktif
                                    </SelectItem>

                                    <SelectItem value="tidak_aktif">
                                        Tidak Aktif
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                    </div>

                    <Button
                        type="button"
                        variant="outline"
                        @click="resetFilter"
                    >
                        Reset Filter
                    </Button>
                </CardContent>
            </Card>

            <!-- Error Import Excel -->
            <div
                v-if="importForm.errors.file"
                class="rounded-lg border border-destructive/30 bg-destructive/10 px-4 py-3 text-sm text-destructive"
            >
                {{ importForm.errors.file }}
            </div>

            <!-- Informasi Format Import -->
            <div
                v-if="isSuperAdmin"
                class="rounded-lg border border-primary/30 bg-primary/10 px-4 py-3 text-sm text-primary"
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
            <div class="text-sm text-muted-foreground">
                Menampilkan
                <strong>
                    {{ props.products.length }}
                </strong>
                product.
            </div>

            <!-- Table -->
            <Card
                class="overflow-hidden border-border/70 bg-card/95 shadow-sm"
            >
                <Table>

                    <!-- Header -->
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
                                Foto
                            </th>

                            <th
                                class="px-6 py-3 text-left text-sm font-semibold text-muted-foreground"
                            >
                                Nama
                            </th>

                            <th
                                class="px-6 py-3 text-left text-sm font-semibold text-muted-foreground"
                            >
                                Deskripsi
                            </th>

                            <th
                                class="px-6 py-3 text-left text-sm font-semibold text-muted-foreground"
                            >
                                Harga
                            </th>

                            <th
                                class="px-6 py-3 text-left text-sm font-semibold text-muted-foreground"
                            >
                                Stok
                            </th>

                            <th
                                class="px-6 py-3 text-center text-sm font-semibold text-muted-foreground"
                            >
                                Status
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
                        <tr
                            v-for="(product, index) in props.products"
                            :key="product.id"
                            class="transition-colors hover:bg-muted/40"
                        >

                            <!-- No -->
                            <td class="px-6 py-4 text-foreground">
                                {{ index + 1 }}
                            </td>

                            <!-- Foto -->
                            <td class="px-6 py-4">
                                <div
                                    class="flex h-20 w-20 items-center justify-center overflow-hidden rounded-lg bg-muted"
                                >
                                    <img
                                        v-if="product.image"
                                        :src="`/storage/${product.image}`"
                                        :alt="product.name"
                                        class="h-full w-full object-cover"
                                    />

                                    <span
                                        v-else
                                        class="text-xs text-muted-foreground"
                                    >
                                        No Image
                                    </span>
                                </div>
                            </td>

                            <!-- Nama -->
                            <td class="px-6 py-4 font-medium text-foreground">
                                {{ product.name }}
                            </td>

                            <!-- Deskripsi -->
                            <td class="px-6 py-4 text-muted-foreground">
                                {{ product.description || '-' }}
                            </td>

                            <!-- Harga -->
                            <td
                                class="whitespace-nowrap px-6 py-4 text-foreground"
                            >
                                Rp
                                {{
                                    Number(product.price).toLocaleString(
                                        'id-ID',
                                    )
                                }}
                            </td>

                            <!-- Stok -->
                            <td class="px-6 py-4 text-foreground">
                                {{ product.stock }}
                            </td>

                            <!-- Status -->
                            <td class="px-6 py-4 text-center">

                                <Badge
                                    v-if="
                                        Number(product.stock) === 0 ||
                                        product.status === 'tidak_aktif'
                                    "
                                    variant="destructive"
                                >
                                    Tidak Aktif
                                </Badge>

                                <Badge
                                    v-else
                                    variant="secondary"
                                    class="bg-green-100 text-green-700 hover:bg-green-100"
                                >
                                    Aktif
                                </Badge>

                            </td>

                            <!-- Aksi -->
                            <td class="px-6 py-4">
                                <div class="flex justify-center gap-2">

                                    <Button
                                        as-child
                                        size="sm"
                                        variant="outline"
                                    >
                                        <Link
                                            :href="`/products/${product.id}`"
                                        >
                                            Lihat
                                        </Link>
                                    </Button>

                                    <Button
                                        as-child
                                        size="sm"
                                        variant="outline"
                                    >
                                        <Link
                                            :href="`/products/${product.id}/edit`"
                                        >
                                            Edit
                                        </Link>
                                    </Button>

                                    <Button
                                        v-if="isSuperAdmin"
                                        type="button"
                                        size="sm"
                                        variant="destructive"
                                        @click="deleteProduct(product.id)"
                                    >
                                        Hapus
                                    </Button>

                                </div>
                            </td>

                        </tr>

                        <!-- Tidak Ada Data -->
                        <tr v-if="props.products.length === 0">
                            <td
                                colspan="8"
                                class="px-6 py-10 text-center text-muted-foreground"
                            >
                                Product tidak ditemukan.
                            </td>
                        </tr>

                    </TableBody>
                </Table>
            </Card>

        </div>
    </div>
</template>