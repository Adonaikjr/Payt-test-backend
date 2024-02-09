<?php

namespace Tests\Feature;

use App\Models\table_redirectLog;
use App\Models\table_redirects;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Hashids\Hashids;


class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_the_application_returns_a_successful_response()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
    public function test_espero_que_retorne_status_201_e_codigo_seja_gerado()
    {
        $data = [
            'url_destino' => 'https://google.com',
        ];

        $response = $this->post('/api/redirects', $data);

        $ultimoRegistro = table_redirects::latest()->first();

        $hashids = new Hashids();
        $code = $hashids->encode($ultimoRegistro->id, $ultimoRegistro->id, $ultimoRegistro->id);


        $response->assertStatus(201)
            ->assertJson([
                'url_destino' => 'https://google.com',
                'codigo' => $code,
                'status' => true,
            ]);
    }

    public function test_espero_que_retorne_numero_de_ips_unicos_check_dados_estatisticas()
    {
        $data = [
            'url_destino' => 'https://google.com',
        ];

        $response = $this->post('/api/redirects', $data);

        $redirectId = $response->json('id');
        $codigoGerado = $response->json('codigo');

        $primeiroIpTest = '192.168.1.1';
        $segundoIpTest = '172.16.0.1';

        $primeiroRefererTest = 'https://instagram.com';
        $segundoRefererTest = 'https://facebook.com';

        $gerandoStatisticas = fn($codigo, $fakeIp, $referer) => $this->withHeaders(['Referer' => $referer])->withServerVariables(['REMOTE_ADDR' => $fakeIp])->get('/r' . '/' . $codigo)->assertStatus(302);

        //Gerando dados para estatisticas
        $gerandoStatisticas($codigoGerado, $primeiroIpTest, $primeiroRefererTest);
        $gerandoStatisticas($codigoGerado, $primeiroIpTest, $segundoRefererTest);
        $gerandoStatisticas($codigoGerado, $segundoIpTest, $primeiroRefererTest);

        $buscarIpUnico = fn($redirect_id) => table_redirectLog::where('redirect_id', $redirect_id)
            ->distinct('ip')
            ->count();
        $IpsUnico = $buscarIpUnico($redirectId);

        //Essa verificação é feita com base nos nossos testes aonde eu faço 3 requisições, 2 com o mesmo IP e 1 com um ip diferente.
        //com base nas chamadas o resultado tem que ser 2.
        $this->assertEquals(2, $IpsUnico);

        $data_hoje = now()->format('Y-m-d');

        $buscandoEstatisticas = fn($codigo) => $this->get('api/redirects/' . $codigo . '/stats')->assertStatus(200)
            ->assertJson([
                'total_acessos' => 3, //chamamos a função anonima gerandoStatisticas()  3x então temos 3 acessos.
                'total_ip_unico' => 2, //ips passados pra function 2 ips diferentes.
                'top_referencias' => $primeiroRefererTest, //enviamos 2 referencias diferentes, porém o $primeiroRefererTest é usado 2x.
                'total_acessos_dez_dias' => [
                    [
                        'date' => $data_hoje,
                        'total' => 3, //logica no controller pra pegar os 10dias, como estamos usando a data atual, sempre sera retornado a quantidade 3.
                        'unique_ips' => 2  //logica no controller ips passados pra function 2 ips diferentes.
                    ]
                ]
            ]);

        $buscandoEstatisticas($codigoGerado);
    }

    public function teste_validade_query()
    {

        $data = [
            'url_destino' => 'https://google.com?src=facebook',
        ];

        $response = $this->post('/api/redirects', $data);
        $codigoGerado = $response->json('codigo');

        $gerandoStatisticas = function ($codigo) {
            $response = $this->get('/r/' . $codigo);

            // Verifica se a resposta é um redirecionamento
            $response->assertRedirect();

            // Extrai os parâmetros de consulta da URL de redirecionamento
            $queryParams = collect(parse_url($response->headers->get('Location'))['query'] ?? '')
                ->map(function ($param) {
                    return explode('=', $param);
                })
                ->pluck(1, 0)
                ->toArray();

            // Verifica se os parâmetros de consulta esperados estão presentes na URL de redirecionamento
            $this->assertArrayHasKey('src', $queryParams);
            $this->assertEquals('facebook', $queryParams['src']);

            // Retorna a resposta
            return $response;
        };

        // Cenário 2: URL de destino sem parâmetros de consulta
        $data2 = [
            'url_destino' => 'https://google.com',
        ];

        $response2 = $this->post('/api/redirects', $data2);
        $codigoGerado2 = $response2->json('codigo');

        $gerandoStatisticas2 = function ($codigo) {
            $response = $this->get('/r/' . $codigo);

            // Verifica se a resposta é um redirecionamento
            $response->assertRedirect();

            // Extrai os parâmetros de consulta da URL de redirecionamento
            $queryParams = collect(parse_url($response->headers->get('Location'))['query'] ?? '')
                ->map(function ($param) {
                    return explode('=', $param);
                })
                ->pluck(1, 0)
                ->toArray();

            // Verifica se não há parâmetros de consulta na URL de redirecionamento
            $this->assertEmpty($queryParams);

            // Retorna a resposta
            return $response;
        };
        $gerandoStatisticas($codigoGerado);

    }
}
