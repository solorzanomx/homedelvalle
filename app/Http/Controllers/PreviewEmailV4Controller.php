<?php

namespace App\Http\Controllers;

use App\Mail\V4\Data\LeadInternoData;
use App\Mail\V4\Data\AcuseData;
use App\Mail\V4\Data\CitaData;
use App\Mail\V4\Data\CompradorData;
use App\Mail\V4\Data\BienvenidaData;
use Illuminate\View\View;

class PreviewEmailV4Controller extends Controller
{
    public function leadInterno(): View
    {
        $data = new LeadInternoData(
            nombre: 'Juan Pérez López',
            email: 'juan.perez@example.com',
            telefono: '+52 55 1234 5678',
            origen: 'Contacto web',
            fecha: now()->format('Y-m-d H:i'),
            mensaje: 'Estoy muy interesado en vender mi propiedad en Benito Juárez. He visto que manejan pocos inmuebles y eso me atrae porque indica calidad.'
        );

        return view('emails.v4.lead-interno', ['data' => $data]);
    }

    public function acuse(): View
    {
        $data = new AcuseData(
            folio: 'lead-' . uniqid(),
            email: 'cliente@example.com'
        );

        return view('emails.v4.acuse', ['data' => $data]);
    }

    public function cita(): View
    {
        $data = new CitaData(
            email: 'cliente@example.com',
            dia_semana: 'Lunes',
            dia: '15',
            mes: 'abril',
            anio: '2026',
            hora: '10:00 AM',
            duracion: '30',
            direccion: 'Paseo de los Tamarindos 400, Depto 1501',
            colonia: 'Bosques de las Lomas',
            asesor: 'María García'
        );

        return view('emails.v4.cita', ['data' => $data]);
    }

    public function comprador(): View
    {
        $data = new CompradorData(
            email: 'cliente@example.com',
            colonia: 'Benito Juárez',
            titulo: 'Casa moderna con azotea y jardín',
            metros: '350',
            recamaras: '3',
            banos: '2',
            estacionamientos: '2',
            precio: '4500000',
            foto_url: null
        );

        return view('emails.v4.comprador', ['data' => $data]);
    }

    public function bienvenida(): View
    {
        $data = new BienvenidaData(
            email: 'cliente@example.com',
            usuario: 'juan.perez@homedelvalle.com',
            password_temporal: 'Temp123!@#',
            url_acceso: 'https://app.homedelvalle.mx/login'
        );

        return view('emails.v4.bienvenida', ['data' => $data]);
    }
}
