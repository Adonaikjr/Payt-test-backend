<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateRedirectRequestValidade;
use App\Http\Requests\UrlDestinoRequestValidade;
use App\Models\table_redirects;
use Illuminate\Http\Request;
use Hashids\Hashids;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;

class ControllerRedirect extends Controller
{
    public function redirectTest($redirect)
    {
        $hashids = new Hashids();

        $id = $hashids->encode(1, 2, 3);


        return response()->json([
            'redirect' => $redirect,
            'code' => $id,
        ]);
    }

    public function createRedirect(UrlDestinoRequestValidade  $req)
    {

        try {
            $urlDestinoSave = $req->url_destino;

            $Destino = table_redirects::create([
                'url_destino' => $urlDestinoSave,
            ]);

            $hashids = new Hashids();
            $code = $hashids->encode($Destino->id, $Destino->id, $Destino->id);

            $Destino->update([
                'codigo' => $code,
                'status' => true,
            ]);

            return response()->json($Destino, 201);
        } catch (\Exception $e) {
            //   Se ocorrer uma exceção, registre-a nos logs
            Log::error('Erro ao enviar a url_destino ao banco: ' . $e->getMessage());

            //   Retorne uma resposta de erro para o cliente
            return response()->json(['error' => 'Erro ao conectar com banco'], 500);
        }
    }

    public function updateRedirect(UrlDestinoRequestValidade $req)
    {
        try {
            $codigo = $req->codigo;
            $urlDestino = $req->url_destino;
            $status = $req->status;

            $CodigoUpdate = table_redirects::where('codigo', $codigo)->first();

            $CodigoUpdate->update([
                'url_destino' => $urlDestino,
                'status' => $status,
            ]);

            return response()->json(['sucesso' => 'Atualizado com sucesso'], 200);
        } catch (\Exception $e) {

            Log::error('Erro ao enviar a dados ao banco: ' . $e->getMessage());

            return response()->json(['error' => 'Erro ao conectar com banco'], 500);
        }
    }
}
