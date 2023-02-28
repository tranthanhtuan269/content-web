<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Word;
use App\Models\Language;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        if(Auth::check()){
            if(isset($request->language)){
                $language = Language::find($request->language);
                if($language){
                    $words = Word::where('language_id', $request->language)->get();
                }else{
                    $words = Word::get();
                }
            }else{
                $words = Word::where('language_id', 1)->get();
            }
            $languages = Language::pluck('name', 'id');
            return view('dashboard', ['words' => $words, 'languages' => $languages]);
        }else{
            return redirect('/login');
        }
    }

    public function ajaxLoadWordLanguage(Request $request)
    {
        $words = Word::where('language_id', $request->language)->get();
        return response()->json(['status' => 200,"words"=>$words]);
    }
}
