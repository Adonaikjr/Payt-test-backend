<?php

namespace App\Http\Requests;

use GuzzleHttp\Client;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UrlDestinoRequestValidade extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'url_destino' => [
                'required',
                'url',
                'different:' . url('/'),
                function ($attribute, $value, $fail) {
                    if (parse_url($value, PHP_URL_SCHEME) !== 'https') {
                        $fail('A URL de destino deve ser HTTPS.');
                    }
                    $client = new Client();
                    try {
                        $response = $client->request('GET', $value);
                        if ($response->getStatusCode() !== 200) {
                            $fail('A URL de destino não retornou um status 200.');
                        }
                    } catch (\Exception $e) {
                        $fail('A URL de destino não está acessível ou retornou um erro.');
                    }
                },
            ],
        ];
    }

    public function messages()
    {
        return [
            'url_destino.required' => 'A URL de destino é obrigatória.',
            'url_destino.url' => 'A URL de destino deve ser uma URL válida.',
            'url_destino.different' => 'A URL de destino não pode ser igual à URL da própria aplicação.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(['errors' => $validator->errors()], 422));
    }
}
