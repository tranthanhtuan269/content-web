<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Word;
use App\Models\Language;
use App\Imports\WordsImport;
use Maatwebsite\Excel\Facades\Excel;

class WordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if(isset($_GET['language'])){
            $words = Word::where('language_id', $_GET['language'])->orderBy('id','desc')->paginate(20);
        }else{
            $words = Word::where('language_id', 1)->orderBy('id','desc')->paginate(20);
        }

        $languages = Language::pluck('name', 'id');
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
            'language_id' => 'required',
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
            'language_id' => 'required',
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

    public function upload(Request $request){
        if(isset($request->file)){
            $file       = $request->file;
            $string     = file_get_contents($file);
            $return     = null;
            switch($file->getClientOriginalExtension())
            {
                case "xlsx":
                    Excel::import(new WordsImport, $file);
                    $res=array('status'=>"200","time_created"=> $return);
                    echo json_encode($res);
                    break;
                case "xls":
                    Excel::import(new WordsImport, $file);
                    $res=array('status'=>"200","time_created"=> $return);
                    echo json_encode($res);
                    break;
                case "csv": 
                    Excel::import(new WordsImport, $file);
                    $res=array('status'=>"200","time_created"=> $return);
                    echo json_encode($res);
                    break;
                break;
                default:
                    $res=array('status'=>"302","Message"=> "Extenstion File is invalid!");
                    echo json_encode($res);  
                break;
            }
        }
    }

    public function import() 
    {
        Excel::import(new WordsImport, 'words.xlsx');
        
        return redirect('/')->with('success', 'All good!');
    }
}