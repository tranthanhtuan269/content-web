<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Word;
use App\Models\Language;

class WordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $languages = Language::pluck('name', 'id');
        $words = Word::orderBy('id','desc')->paginate(20);
        return view('words.index', ['words' => $words, 'languages' => $languages]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $languages = Language::pluck('name', 'id');
        return view('words.create', ['languages' => $languages]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'word' => 'required',
            'language' => 'required',
        ]);
        
        Word::create($request->post());

        return redirect()->route('words.index')->with('success','Word has been created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Word $word)
    {
        return view('words.show',compact('word'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Word $word)
    {
        $languages = Language::pluck('name', 'id');
        return view('words.edit',compact('word', 'languages'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Word $word)
    {
        $request->validate([
            'word' => 'required',
            'language' => 'required',
        ]);
        
        $word->fill($request->post())->save();

        return redirect()->route('words.index')->with('success','Word has been updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Word $word)
    {
        $word->delete();
        return redirect()->route('words.index')->with('success','Word has been deleted successfully');
    }
}
