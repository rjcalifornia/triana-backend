<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\ActaElectoral;
use App\Models\Dispositivos;
use App\Models\JuntasReceptoras;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TransmisionController extends Controller
{
    public function obtenerTransmisiones(Request $request){

        $actas = ActaElectoral::with(['idJuntaReceptora', 'idCentroVotacion', 'idTipoActa', 'usuarioCrea'])->where('id_centro_votacion', $request->dispositivo->id_centro_votacion)->get();

        return response()->json(['transmisiones'=> $actas], 200);
    }

    public function almacenarTransmisionAlcaldes(Request $request){
        $validator = Validator::make($request->all(), [
            'id_junta_receptora' => 'integer|required',
            'id_centro_votacion' =>  'integer|required',
            'sobrantes' =>  'integer|required',
            'inutilizados' =>  'integer|required',
            'impugnados' =>  'integer|required',
            'nulos' =>  'integer|required',
            'abstenciones' =>  'integer|required',
            'escrutados' =>  'integer|required',
            'faltantes' =>  'integer|required',
            'entregados' =>  'integer|required',
            'id_tipo_acta' =>  'integer|required',
            'partidos' => 'array'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();
        $juntaReceptora = JuntasReceptoras::where('id', $request->id_junta_receptora)->first();
        if(!$juntaReceptora){
            return response()->json(['message' => 'No se pudo procesar la petición solicitada'], 422);
        }
        $dispositivo = Dispositivos::where('id_usuario', $user->id)->where('id_centro_votacion', $juntaReceptora->id_centro_votacion)->first();

        if(!$dispositivo){
            return response()->json(['message' => 'Hubo un problema al procesar la petición del dispositivo'], 422);
        }

        try {
            $acta = new ActaElectoral;
            $acta->id_junta_receptora = $request->id_junta_receptora;
            $acta->id_centro_votacion = $request->id_centro_votacion;
            $acta->id_tipo_acta = $request->id_tipo_acta;
            $acta->usuario_crea = $user->id;
            $acta->save();

            return response()->json($acta, 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th], 500);
        }


    }
}