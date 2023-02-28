<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Models\Word;
use Yajra\Datatables\Datatables;
use Illuminate\Http\Request;

class DatatablesController extends Controller
{
    /**
     * Displays datatables front end view
     *
     * @return \Illuminate\View\View
     */
    public function getIndex()
    {
        return view('datatables.index');
    }

    /**
     * Process datatables ajax request.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function anyData(Request $request)
    {
        $language = 1;
        if(isset($request->language)){
            $language = $request->language;
        }
        return Datatables::of(Word::where('language_id', $language)->get())
            ->addColumn('language', function ($word) {
                return $word->language->name;
            })
            ->addColumn('action', function ($word) {
                return $word->id;
            })
            ->make(true);
    }
}
