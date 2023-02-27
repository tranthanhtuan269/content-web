<?php

namespace App\Imports;

use App\Models\Word;
use App\Models\Language;
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
        $language = Language::where('name', $row[1])->first();
        if($language){
            $exists = Word::where('word',$row[0])->where('language_id', $language->id)->first();
            if ($exists) {
                //LOGIC HERE TO UPDATE
                return null;
            }

            return new Word([
                'word'     => $row[0],
                'language_id'     => $language->id,
            ]);
        }
    }
}
