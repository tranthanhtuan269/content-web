<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Word;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        if(Auth::check()){
            $words = Word::get();
            return view('dashboard', ['words' => $words]);
        }else{
            return redirect('/login');
        }
    }
}
