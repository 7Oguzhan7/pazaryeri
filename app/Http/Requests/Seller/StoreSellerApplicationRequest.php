<?php

namespace App\Http\Requests\Seller;

use App\Models\Seller;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreSellerApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $seller = $this->user()?->seller;

        return $seller === null || $seller->status === Seller::STATUS_REJECTED;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'store_name' => ['required', 'string', 'max:255'],
            'tax_number' => ['required', 'string', 'regex:/^\d{10,11}$/', Rule::unique('sellers', 'tax_number')
                ->ignore($this->user()?->seller?->id)],
            'iban' => ['required', 'string', 'regex:/^TR\d{24}$/i'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'store_name.required' => 'Mağaza adı zorunludur.',
            'store_name.max' => 'Mağaza adı en fazla 255 karakter olabilir.',
            'tax_number.required' => 'Vergi numarası zorunludur.',
            'tax_number.regex' => 'Vergi numarası 10 veya 11 rakamdan oluşmalıdır.',
            'tax_number.unique' => 'Bu vergi numarasıyla kayıtlı bir satıcı bulunmaktadır.',
            'iban.required' => 'IBAN zorunludur.',
            'iban.regex' => 'IBAN, TR ile başlayan 26 karakterli bir Türkiye IBAN’ı olmalıdır.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $iban = $this->input('iban');

        if (is_string($iban)) {
            $this->merge(['iban' => strtoupper(preg_replace('/\s+/', '', $iban) ?? $iban)]);
        }
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $iban = $this->input('iban');

            if (! is_string($iban) || ! preg_match('/^TR\d{24}$/', $iban)) {
                return;
            }

            $rearrangedIban = substr($iban, 4).substr($iban, 0, 4);
            $numericIban = preg_replace_callback('/[A-Z]/', fn (array $match): string => (string) (ord($match[0]) - 55), $rearrangedIban);
            $remainder = 0;

            foreach (str_split($numericIban ?? '') as $digit) {
                $remainder = ($remainder * 10 + (int) $digit) % 97;
            }

            if ($remainder !== 1) {
                $validator->errors()->add('iban', 'IBAN doğrulama kontrolünden geçemedi.');
            }
        });
    }
}
