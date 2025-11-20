<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;


class SubscriptionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Vous pouvez personnaliser cette logique en fonction des autorisations requises
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "phone" => "required|string|min:8|max:21"
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            "phone.min" => "Le numéro de téléphone doit contenir au moins 8 chiffres",
            "phone.required" => "Le numéro de téléphone est requis",
        ];
    }
}
