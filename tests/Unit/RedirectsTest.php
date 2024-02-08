<?php

namespace Tests\Unit;

use App\Models\table_redirectLog;
use App\Models\table_redirects;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\TableRedirects;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;

class RedirectsTest extends TestCase
{
    // use DatabaseTransactions;
    use WithFaker; // Para acessar a instância do Faker

    /**
     * Testa a criação de um redirecionamento com sucesso.
     *
     * @return void
     */

    public function generateValidUrl(): string
    {
        $url = $this->faker->url;

        // Adiciona o esquema "https://" à URL
        $url = "https://" . parse_url($url, PHP_URL_HOST) . parse_url($url, PHP_URL_PATH);

        // Tente fazer uma solicitação HTTP sem verificar SSL
        try {
            $response = Http::withoutVerifying()->get($url);
        } catch (\Exception $e) {
            // Em caso de erro, tente novamente
            return $this->generateValidUrl();
        }

        // Se a solicitação for bem-sucedida (status 200), retorne a URL
        if ($response->ok()) {
            return $url;
        } else {
            // Se a URL não for válida, tente novamente
            return $this->generateValidUrl();
        }
    }
    public function teste_espero_status_200_url_destino()
    {
        $urlDestino = $this->generateValidUrl();

        $urlDestino = "https://" . parse_url($urlDestino, PHP_URL_HOST) . parse_url($urlDestino, PHP_URL_PATH);

        $response = $this->postJson('api/redirects', ['url_destino' => $urlDestino]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('table_redirects', ['url_destino' => $urlDestino]);
    }

    public function test_espero_status_422_rejeite_create()
    {
        // Caso 1: URL com DNS inválido
        $response = $this->postJson('api/redirects', ['url_destino' => 'https://invalid-dns-url.com']);
        $response->assertStatus(422); 

        // Caso 2: URL inválida
        $response = $this->postJson('api/redirects', ['url_destino' => 'invalid-url']);
        $response->assertStatus(422); 

        // Caso 3: URL apontando para a própria aplicação
        $response = $this->postJson('api/redirects', ['url_destino' => url('/api/redirects')]);
        $response->assertStatus(422); 

        // Caso 4: URL sem HTTPS
        $response = $this->postJson('api/redirects', ['url_destino' => 'http://example.com']);
        $response->assertStatus(422); 

        // Caso 5: URL retornando status diferente de 200 ou 201
        $response = $this->postJson('api/redirects', ['url_destino' => 'https://example.com/404']);
        $response->assertStatus(422); 

        // Caso 6: URL inválida pois possui query params com chave vazia
        $response = $this->postJson('api/redirects', ['url_destino' => 'https://example.com/?key=']);
        $response->assertStatus(422); 
    }
}
