<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePesananRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Route publik — semua orang boleh membuat pesanan
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nama_pelanggan'   => ['required', 'string', 'max:255'],
            'nomor_meja'       => ['required', 'string', 'max:100'],
            'items'            => ['required', 'array', 'min:1'],
            'items.*.menu_id'  => ['required', 'integer', 'exists:menu,id'],
            'items.*.jumlah'   => ['required', 'integer', 'min:1'],
            'items.*.harga'    => ['required', 'numeric', 'min:0'],
        ];
    }
}
