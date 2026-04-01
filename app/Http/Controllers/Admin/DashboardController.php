<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Property;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $propertiesCount = Property::count();
        $usersCount = User::count();
        $brokersCount = User::where('role', 'broker')->count();
        $clientsCount = User::where('role', 'user')->count();

        $approvedBrokers = User::where('role', 'broker')->get();
        $pendingUsers = User::where('role', 'user')->get();

        return view('admin.dashboard', compact(
            'propertiesCount',
            'clientsCount',
            'brokersCount',
            'usersCount',
            'approvedBrokers',
            'pendingUsers'
        ));
    }
}
