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
use Illuminate\Support\Facades\DB;

class ControllerRedirectLog extends Controller
{
    public function statisticsLog($redirect)
    {
        try {
            $pegarCodigo = fn($codigo) => table_redirects::where('codigo', $codigo)->first();
            $Codigo = $pegarCodigo($redirect);

            $pegarChaveCorrespondente = fn() => table_redirectLog::where('redirect_id', $Codigo->id);
            $CheckAccess = $pegarChaveCorrespondente();
            $totalAccess = $CheckAccess->count();

            $buscarIpUnico = fn() => table_redirectLog::where('redirect_id', $Codigo->id)
                ->distinct('ip')
                ->count();
            $IpUnico = $buscarIpUnico();

            $buscarTopReferer = fn() => table_redirectLog::select('referer', \DB::raw('COUNT(*) as total'))
                ->where('redirect_id', $Codigo->id)
                ->groupBy('referer')
                ->orderByDesc('total')
                ->first();
            $TopReferer = $buscarTopReferer();

            $ultimosDezDias = \Carbon\Carbon::today()->subDays(9);

            $buscarAcessos = fn() => table_redirectLog::select(
                \DB::raw('DATE(date_time_acess) as date'),
                \DB::raw('COUNT(*) as total'),
                \DB::raw('COUNT(DISTINCT ip) as unique_ips')
            )
                ->where('redirect_id', $Codigo->id)
                ->where('date_time_acess', '>=', $ultimosDezDias)
                ->groupBy('date')
                ->orderBy('date', 'asc')
                ->get();

            $dadosAcessos = $buscarAcessos();

            $total_dez_dias = [];

            foreach ($dadosAcessos as $acesso) {
                $total_dez_dias[] = [
                    "date" => $acesso->date,
                    "total" => $acesso->total,
                    "unique_ips" => $acesso->unique_ips
                ];
            }

            return response()->json([
                'total_acessos' => $totalAccess,
                'total_ip_unico' => $IpUnico,
                'top_referencias' => $TopReferer->referer,
                'total_acessos_dez_dias' => $total_dez_dias,
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao interno: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao conectar com banco'], 500);
        }

    }

    public function logsAcess($redirect){
        try {
            $buscarCodigo = fn($codigo) => table_redirects::where('codigo', $codigo)->first();
            $checkCodigo = $buscarCodigo($redirect);

            if(!$checkCodigo){
                return response()->json(['error' => 'Não foi encontrado o código solicitado'], 400);
            }

            $buscarKey = fn($chave) => table_redirectLog::where('redirect_id', $chave)->get();

            $LogsTotal = $buscarKey($checkCodigo->id);

            return response()->json($LogsTotal);
            //code...
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
