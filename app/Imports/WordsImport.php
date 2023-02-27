<?php

namespace App\Imports;

use App\Models\Word;
use Maatwebsite\Excel\Concerns\ToModel;

class WordsImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $exists = Word::where('word',$row[0])->where('language', $row[1])->first();
        if ($exists) {
            //LOGIC HERE TO UPDATE
            return null;
        }
        
        return new Word([
            'word'     => $row[0],
            'language'     => $row[1],
        ]);
    }
}
