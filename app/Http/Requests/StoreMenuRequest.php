<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreMenuRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Dilindungi middleware auth di routes
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'kategori_id' => ['required', 'exists:kategori,id'],
            'nama_menu'   => ['required', 'string', 'max:255'],
            'deskripsi'   => ['nullable', 'string'],
            'harga'       => ['required', 'numeric', 'min:0'],
            'foto'        => ['nullable', 'image', 'max:5120'],
            'status'      => ['required', 'in:tersedia,habis'],
        ];
    }
}
