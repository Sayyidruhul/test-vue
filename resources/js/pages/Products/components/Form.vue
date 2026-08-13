<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

interface Product {
    id: number;
    name: string;
    description: string | null;
    price: string | number;
    stock: number;
    status: 'aktif' | 'tidak_aktif';
    image: string | null;
}

const props = defineProps<{
    product?: Product;
}>();

/*
|--------------------------------------------------------------------------
| Form
|--------------------------------------------------------------------------
|
| Jika product tidak ada:
| → digunakan untuk Create
|
| Jika product ada:
| → digunakan untuk Edit
|
*/

const form = useForm({
    name: props.product?.name ?? '',
    description: props.product?.description ?? '',
    price: props.product?.price ?? '',
    stock: props.product?.stock ?? 0,
    status: props.product?.status ?? 'aktif',
    image: null as File | null,
});

/*
|--------------------------------------------------------------------------
| Preview Foto
|--------------------------------------------------------------------------
*/

const imagePreview = ref<string | null>(
    props.product?.image
        ? `/storage/${props.product.image}`
        : null,
);

/*
|--------------------------------------------------------------------------
| Pilih Foto
|--------------------------------------------------------------------------
*/

const handleImage = (event: Event) => {
    const target = event.target as HTMLInputElement;

    const file = target.files?.[0];

    if (file) {
        form.image = file;

        imagePreview.value = URL.createObjectURL(file);
    }
};

/*
|--------------------------------------------------------------------------
| Submit
|--------------------------------------------------------------------------
*/

const submit = () => {
    /*
    |--------------------------------------------------------------------------
    | Edit Product
    |--------------------------------------------------------------------------
    */

    if (props.product) {
        form.transform((data) => ({
            ...data,
            _method: 'PUT',
        })).post(`/products/${props.product.id}`, {
            forceFormData: true,
        });

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Tambah Product
    |--------------------------------------------------------------------------
    */

    form.post('/products', {
        forceFormData: true,
    });
};
</script>

<template>
    <form
        class="space-y-5"
        @submit.prevent="submit"
    >
        <!-- Nama Product -->
        <div class="space-y-2">
            <Label for="name">
                Nama Product
            </Label>

            <Input
                id="name"
                v-model="form.name"
                placeholder="Masukkan nama product"
                :aria-invalid="!!form.errors.name"
            />

            <p
                v-if="form.errors.name"
                class="text-sm text-destructive"
            >
                {{ form.errors.name }}
            </p>
        </div>

        <!-- Deskripsi -->
        <div class="space-y-2">
            <Label for="description">
                Deskripsi
            </Label>

            <textarea
                id="description"
                v-model="form.description"
                rows="4"
                placeholder="Masukkan deskripsi product"
                class="min-h-20 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
            ></textarea>

            <p
                v-if="form.errors.description"
                class="text-sm text-destructive"
            >
                {{ form.errors.description }}
            </p>
        </div>

        <!-- Harga & Stok -->
        <div class="grid gap-5 sm:grid-cols-2">

            <!-- Harga -->
            <div class="space-y-2">
                <Label for="price">
                    Harga
                </Label>

                <Input
                    id="price"
                    v-model="form.price"
                    type="number"
                    min="0"
                    placeholder="Contoh: 5000000"
                    :aria-invalid="!!form.errors.price"
                />

                <p
                    v-if="form.errors.price"
                    class="text-sm text-destructive"
                >
                    {{ form.errors.price }}
                </p>
            </div>

            <!-- Stok -->
            <div class="space-y-2">
                <Label for="stock">
                    Stok
                </Label>

                <Input
                    id="stock"
                    v-model="form.stock"
                    type="number"
                    min="0"
                    placeholder="Contoh: 10"
                    :aria-invalid="!!form.errors.stock"
                />

                <p
                    v-if="form.errors.stock"
                    class="text-sm text-destructive"
                >
                    {{ form.errors.stock }}
                </p>
            </div>

        </div>

        <!-- Status -->
        <div class="space-y-2">
            <Label for="status">
                Status
            </Label>

            <Select v-model="form.status">
                <SelectTrigger
                    id="status"
                    class="w-full"
                >
                    <SelectValue placeholder="Pilih status" />
                </SelectTrigger>

                <SelectContent>
                    <SelectItem value="aktif">
                        Aktif
                    </SelectItem>

                    <SelectItem value="tidak_aktif">
                        Tidak Aktif
                    </SelectItem>
                </SelectContent>
            </Select>

            <p class="text-xs text-muted-foreground">
                Status menentukan apakah product aktif atau tidak aktif.
            </p>

            <p
                v-if="form.errors.status"
                class="text-sm text-destructive"
            >
                {{ form.errors.status }}
            </p>
        </div>

        <!-- Foto Product -->
        <div class="space-y-2">
            <Label for="image">
                Foto Product
            </Label>

            <Input
                id="image"
                type="file"
                accept="image/jpeg,image/png,image/webp"
                @change="handleImage"
            />

            <p class="text-xs text-muted-foreground">
                {{
                    props.product
                        ? 'Pilih foto baru jika ingin mengganti foto.'
                        : 'Format JPG, JPEG, PNG, WEBP.'
                }}
                Maksimal 2 MB.
            </p>

            <p
                v-if="form.errors.image"
                class="text-sm text-destructive"
            >
                {{ form.errors.image }}
            </p>

            <!-- Preview Foto -->
            <div
                v-if="imagePreview"
                class="pt-2"
            >
                <p class="mb-2 text-sm font-medium text-foreground">
                    Preview Foto
                </p>

                <img
                    :src="imagePreview"
                    alt="Preview Product"
                    class="h-48 w-full rounded-md border object-contain"
                />
            </div>
        </div>

        <!-- Tombol -->
        <div class="flex justify-end gap-3 pt-3">

            <!-- Batal -->
            <Button
                as-child
                type="button"
                variant="outline"
            >
                <Link href="/products">
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
                        : props.product
                            ? 'Update Product'
                            : 'Simpan Product'
                }}
            </Button>

        </div>
    </form>
</template>