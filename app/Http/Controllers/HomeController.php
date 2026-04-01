<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\Client;
use App\Models\Broker;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            $properties = Property::all();
            $clients = Client::all();
            $brokers = Broker::all();
            return view('home.dashboard-improved', compact('properties', 'clients', 'brokers'));
        }
        
        return view('welcome');
    }
}
