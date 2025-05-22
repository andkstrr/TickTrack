<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|max:255|unique:users', // unique:users digunakan untuk memeriksa apakah email sudah terdaftar
            'password' => 'required|string|min:8'
        ]; // field ini digunakan untuk melakukan validasi saat melakukan register
    }
}
