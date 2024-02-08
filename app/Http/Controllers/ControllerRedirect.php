<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateRedirectRequestValidade;
use App\Http\Requests\UrlDestinoRequestValidade;
use App\Models\table_redirectLog;
use App\Models\table_redirects;
use Illuminate\Http\Request;
use Hashids\Hashids;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class ControllerRedirect extends Controller
{
    private function Logs($request, $redirect_id)
    {
        try {
            $checkParamsEmpty = $request->query();
            $queryNoEmpty = [];

            foreach ($checkParamsEmpty as $key => $value) {
                if (!empty($value)) {
                    $queryNoEmpty[$key] = $value;
                }
            }

            table_redirectLog::create([
                'redirect_id' => $redirect_id,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'referer' => $request->header('referer'),
                'query_params' =>json_encode($queryNoEmpty),
                'date_time_acess' => now(),
            ]);
            $LatestAcess = table_redirects::findOrFail($redirect_id);
            $LatestAcess->update(['ultimo_acesso' => now()]);

            return $queryNoEmpty;
        } catch (\Exception $e) {
            Log::error('Erro ao interno: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao conectar com banco'], 500);
        }
    }
    public function redirectGo(Request $req, $redirect)
    {
        // dd($req->query);
        try {
            $codigo = $redirect;

            $GetLink = table_redirects::where('codigo', $codigo)->first();

            $RegisterLog = $this->Logs($req, $GetLink->id);

            $GoRedirect = fn ($GetLink) => $GetLink->status === 0
                ? response()->json(['error' => 'Status está desativado'], 400)
                : redirect()->away($GetLink->url_destino);

            return $GoRedirect($GetLink);
        } catch (\Exception $e) {
            Log::error('Erro ao interno: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao conectar com banco ou link deletado'], 500);
        }
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
            Log::error('Erro interno: ' . $e->getMessage());
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

    public function getListAllRedirect()
    {
        try {
            $GetAll = table_redirects::all();

            return response()->json($GetAll, 200);
        } catch (\Exception $e) {
            Log::error('Erro ao interno: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao conectar com banco'], 500);
        }
    }

    public function deleteRedirect(Request $req)
    {
        try {
            $Delete = table_redirects::where('codigo', $req->codigo)->first();
            $Delete->update(['status' => false]);
            $Delete->delete();

            return response()->json(['sucesso' => 'Deletado com sucesso'], 200);
        } catch (\Exception $e) {
            Log::error('Erro interno: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao conectar com banco'], 500);
        }
    }

    public function restoreRedirect($codigo)
    {
        try {
            $Restore = table_redirects::withTrashed()->findOrFail($codigo);
            $Restore->update(['status' => true]);
            $Restore->restore();

            return response()->json(['sucesso' => 'Restaurado com sucesso'], 200);
        } catch (\Exception $e) {
            Log::error('Erro interno: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao conectar com banco'], 500);
        }
    }
}
