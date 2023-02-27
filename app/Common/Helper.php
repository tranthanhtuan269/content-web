<?php

namespace App\Common;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\TranslateGroup;
use App\TranslateText;
use App\Category;
use App\Language;
use App\Process;
use DB;

Class Helper{

    public static function ImportTextsFile($file, $category){
        $time_created = date('Y-m-d H:i:s');
        $temp_db_name = 'temporary_' . \Auth::user()->id;
        if(!Schema::hasTable($temp_db_name)){
            Schema::create($temp_db_name, function (Blueprint $table) {
                $table->increments('id');
                $table->string('keyword');
                $table->string('source_text');
                $table->integer('category_id');
                $table->timestamps();
            });
        }

        $string = file_get_contents($file);
        $string = preg_replace('!/\*.*?\*/!s', '', $string);
        $string = preg_replace('/\n\s*\n/', "\n", $string);
        $string = trim(preg_replace('/\s+/', ' ', $string));
        $string = str_replace('"', "", $string);
        $string = str_replace("'", "", $string);
        $string = str_replace('= ', '=', $string);
        $string = str_replace(' =', '=', $string);
        $objs   = explode(";",$string);

        if(count($objs) > 1){
            foreach ($objs as $obj) {
                $item = explode("=",$obj);
                if(isset($item[0]) && isset($item[1])){
                    DB::table($temp_db_name)->insert([
                        'keyword'       => $item[0],
                        'source_text'   => $item[1],
                        'category_id'   => $category,
                        'created_at'    => $time_created,
                        'updated_at'    => $time_created
                    ]);
                }
            }
            return $time_created;
        }else{
            return 'empty_file';
        }
    }

    public static function ImportStringsFile($file, $category){
        $time_created = date('Y-m-d H:i:s');
        $temp_db_name = 'temporary_' . \Auth::user()->id;
        if(!Schema::hasTable($temp_db_name)){
            Schema::create($temp_db_name, function (Blueprint $table) {
                $table->increments('id');
                $table->string('keyword');
                $table->string('source_text');
                $table->integer('category_id');
                $table->timestamps();
            });
        }

        $string = file_get_contents($file);
        $string = preg_replace('!/\*.*?\*/!s', '', $string);
        $string = preg_replace('/\n\s*\n/', "\n", $string);
        $string = trim(preg_replace('/\s+/', ' ', $string));
        $string = str_replace('"', "", $string);
        $string = str_replace("'", "", $string);
        $string = str_replace('= ', '=', $string);
        $string = str_replace(' =', '=', $string);
        $objs   = explode(";",$string);

        if(count($objs) > 1){
            foreach ($objs as $obj) {
                $item = explode("=",$obj);
                if(isset($item[0]) && isset($item[1])){
                    DB::table($temp_db_name)->insert([
                        'keyword'       => $item[0],
                        'source_text'   => $item[1],
                        'category_id'   => $category,
                        'created_at'    => $time_created,
                        'updated_at'    => $time_created
                    ]);
                }
            }
            return $time_created;
        }else{
            return 'empty_file';
        }
    }

    public static function ImportMultiStringsFile($file, $category){
        // get $language from $group
        $fileName       = $file->getClientOriginalName();
        $shotName       = explode('.', $fileName);
        $language_arr   = explode('_', $shotName[0]);
        $name_lng       = count($language_arr);
        $language_code  = $language_arr[$name_lng - 1];

        if($name_lng <= 1){
            return 'no_language';
        }

        // check language_code 
        $check = Language::checkCode($language_code);
        // if not exist then return error
        if(!$check){
            return 'invalid_language';
        }

        // if exist then insert 
        $time_created = date('Y-m-d H:i:s');

        // create table contain
        $temp_db_name = 'temporary_' . \Auth::user()->id;
        $temp_db_completed_name = 'temporary_' . \Auth::user()->id . '_completed';
        if(!Schema::hasTable($temp_db_completed_name)){
            Schema::create($temp_db_completed_name, function (Blueprint $table) {
                $table->increments('id');
                $table->string('keyword');
                $table->string('source_text');
                $table->string('translated_text');
                $table->integer('language_id');
                $table->string('language_name');
                $table->string('language_code');
                $table->integer('category_id');
                $table->timestamps();
            });
        }

        // cp data to temp 2
        $listDataTemp1 = DB::table($temp_db_name)->get();
        
        foreach($listDataTemp1 as $dataTemp1){
            DB::table($temp_db_completed_name)->insert([
                'keyword' => $dataTemp1['keyword'], 
                'source_text' => $dataTemp1['source_text'],
                'translated_text' => '',
                'language_id' => $check['id'],
                'language_name' => $check['name'],
                'language_code' => $check['code'],
                'category_id' => $category
            ]);
        }

        // read file upload
        $string = file_get_contents($file);
        $string = preg_replace('!/\*.*?\*/!s', '', $string);
        $string = preg_replace('/\n\s*\n/', "\n", $string);
        $string = trim(preg_replace('/\s+/', ' ', $string));
        $string = str_replace('"', "", $string);
        $string = str_replace("'", "", $string);
        $string = str_replace('= ', '=', $string);
        $string = str_replace(' =', '=', $string);
        $objs   = explode(";",$string);
        $lang_id= $check['id'];

        if(count($objs) > 1){
            foreach ($objs as $obj) {
                if(strlen($obj) > 0){
                    $item = explode("=",$obj);
                    if(isset($item[0]) && isset($item[1])){
                        $keyword = $item[0];
                        $translated_text = $item[1];

                        // update database temp 2
                        $temp = DB::table($temp_db_completed_name)
                        ->where('keyword', '=', $keyword)
                        ->where('category_id', '=', $category)
                        ->where('language_id', '=', $check['id']);

                        $temp->update(['translated_text' => $translated_text]);
                        $ret = $temp->select('source_text')->first();
                        $slug = Helper::slug($ret["source_text"], "_");
                        // find in 

                        // import to database
                        $checkExist = TranslateText::checkExist(
                                            $slug, 
                                            $category, 
                                            $lang_id);

                        if($checkExist){
                            if(strlen($translated_text) > 0){
                                $updateTranslate = TranslateText::updateTranslate(
                                                    $slug, 
                                                    $category, 
                                                    $lang_id,
                                                    $translated_text
                                                );
                            }else{
                                $updateTranslate = TranslateText::updateTranslate(
                                                    $slug, 
                                                    $category, 
                                                    $lang_id,
                                                    $translated_text,
                                                    0
                                                );
                            }
                        }else{
                            // chua co trong db
                            // tim trong bang tam xem co chua
                            $result = DB::table($temp_db_name)
                                        ->select('source_text')
                                        ->where('keyword', '=', $keyword)
                                        ->first();

                            // neu co roi thi lay source_text, translated_text de tao
                            if($result){
                                $slug = Helper::slug($result['source_text'], "_");
                                $translate = new TranslateText;
                                $translate->slug            = $slug;
                                $translate->source_text     = $result['source_text'];
                                $translate->trans_text      = $translated_text;
                                $translate->category_id     = $category;
                                $translate->language_id     = $lang_id;
                                $translate->translate_type  = 2;
                                $translate->created_by      = \Auth::user()->id;
                                $translate->updated_by      = \Auth::user()->id;
                                $translate->created_at      = $time_created;
                                $translate->updated_at      = $time_created;
                                $translate->save();
                            }
                        }
                    }
                }
            }
            return 'Ok';
        }else{
            return 'empty_file';
        }
    }

    public static function ImportJsonFile($file, $category){
        $time_created = date('Y-m-d H:i:s');
        $temp_db_name = 'temporary_' . \Auth::user()->id;
        if(!Schema::hasTable($temp_db_name)){
            Schema::create($temp_db_name, function (Blueprint $table) {
                $table->increments('id');
                $table->string('keyword');
                $table->string('source_text');
                $table->integer('category_id');
                $table->timestamps();
            });
        }

        $string = file_get_contents($file);
        $string = str_replace(array("\r", "\n", "\t"), "", $string);
        $objs = json_decode($string, true);

        if(count($objs) > 0){
            foreach ($objs as $obj) {
                DB::table($temp_db_name)->insert([
                    'keyword'       => $obj['name'],
                    'source_text'   => $obj['text'],
                    'category_id'   => $category,
                    'created_at'    => $time_created,
                    'updated_at'    => $time_created
                ]);
            }
            return $time_created;
        }else{
            return 'empty_file';
        }
    }

    public static function ImportMultiJsonFile($file, $category){
        // get $language from $group
        $fileName       = $file->getClientOriginalName();
        $shotName       = explode('.', $fileName);
        $language_arr   = explode('_', $shotName[0]);
        $name_lng       = count($language_arr);
        $language_code  = $language_arr[$name_lng - 1];

        if($name_lng <= 1){
            return 'no_language';
        }

        // check language_code 
        $check = Language::checkCode($language_code);
        // if not exist then return error
        if(!$check){
            return 'invalid_language';
        }

        // if exist then insert 
        $time_created = date('Y-m-d H:i:s');

        // create table contain
        $temp_db_name = 'temporary_' . \Auth::user()->id;
        $temp_db_completed_name = 'temporary_' . \Auth::user()->id . '_completed';
        if(!Schema::hasTable($temp_db_completed_name)){
            Schema::create($temp_db_completed_name, function (Blueprint $table) {
                $table->increments('id');
                $table->string('keyword');
                $table->string('source_text');
                $table->string('translated_text');
                $table->integer('language_id');
                $table->string('language_name');
                $table->string('language_code');
                $table->integer('category_id');
                $table->timestamps();
            });
        }

        // cp data to temp 2
        $listDataTemp1 = DB::table($temp_db_name)->get();
        
        foreach($listDataTemp1 as $dataTemp1){
            DB::table($temp_db_completed_name)->insert([
                'keyword' => $dataTemp1['keyword'], 
                'source_text' => $dataTemp1['source_text'],
                'translated_text' => '',
                'language_id' => $check['id'],
                'language_name' => $check['name'],
                'language_code' => $check['code'],
                'category_id' => $category
            ]);
        }

        // read file upload
        $string = file_get_contents($file);
        $string = str_replace(array("\r", "\n", "\t"), "", $string);
        $objs = json_decode($string, true);

        if(count($objs) > 0){
            foreach ($objs as $obj) {
                if(isset($obj['name']) && isset($obj['text'])){
                    $keyword = $obj['name'];
                    $translated_text = $obj['text'];

                    // update database temp 2
                    $temp = DB::table($temp_db_completed_name)
                    ->where('keyword', '=', $keyword)
                    ->where('category_id', '=', $category)
                    ->where('language_id', '=', $check['id']);

                    $temp->update(['translated_text' => $translated_text]);
                    $ret = $temp->select('source_text')->first();
                    $slug = Helper::slug($ret["source_text"], "_");
                    // find in 

                    // import to database
                    $checkExist = TranslateText::checkExist(
                                        $slug, 
                                        $category, 
                                        $lang_id);

                    if($checkExist){
                        if(strlen($translated_text) > 0){
                            $updateTranslate = TranslateText::updateTranslate(
                                                $slug, 
                                                $category, 
                                                $lang_id,
                                                $translated_text
                                            );
                        }else{
                            $updateTranslate = TranslateText::updateTranslate(
                                                $slug, 
                                                $category, 
                                                $lang_id,
                                                $translated_text,
                                                0
                                            );
                        }
                    }else{
                        // chua co trong db
                        // tim trong bang tam xem co chua
                        $result = DB::table($temp_db_name)
                                    ->select('source_text')
                                    ->where('keyword', '=', $keyword)
                                    ->first();

                        // neu co roi thi lay source_text, translated_text de tao
                        if($result){
                            $slug = Helper::slug($result['source_text'], "_");
                            $translate = new TranslateText;
                            $translate->slug            = $slug;
                            $translate->source_text     = $result['source_text'];
                            $translate->trans_text      = $translated_text;
                            $translate->category_id     = $category;
                            $translate->language_id     = $lang_id;
                            $translate->translate_type  = 2;
                            $translate->created_by      = \Auth::user()->id;
                            $translate->updated_by      = \Auth::user()->id;
                            $translate->created_at      = $time_created;
                            $translate->updated_at      = $time_created;
                            $translate->save();
                        }
                    }
                }else{
                    return 'invalid_file';
                }
            }
            return 'Ok';
        }else{
            return 'empty_file';
        }
    }

    public static function ImportMultiXmlFile($file, $category){
        // get $language from $group
        $fileName       = $file->getClientOriginalName();
        $shotName       = explode('.', $fileName);
        $language_arr   = explode('_', $shotName[0]);
        $name_lng       = count($language_arr);
        $language_code  = $language_arr[$name_lng - 1];

        if($name_lng <= 1){
            return 'no_language';
        }

        // check language_code 
        $check = Language::checkCode($language_code);
        // if not exist then return error
        if(!$check){
            return 'invalid_language';
        }

        // if exist then insert 
        $time_created = date('Y-m-d H:i:s');

        // create table contain
        $temp_db_name = 'temporary_' . \Auth::user()->id;
        $temp_db_completed_name = 'temporary_' . \Auth::user()->id . '_completed';
        if(!Schema::hasTable($temp_db_completed_name)){
            Schema::create($temp_db_completed_name, function (Blueprint $table) {
                $table->increments('id');
                $table->string('keyword');
                $table->string('source_text');
                $table->string('translated_text');
                $table->integer('language_id');
                $table->string('language_name');
                $table->string('language_code');
                $table->integer('category_id');
                $table->timestamps();
            });
        }

        // cp data to temp 2
        $listDataTemp1 = DB::table($temp_db_name)->get();
        
        foreach($listDataTemp1 as $dataTemp1){
            DB::table($temp_db_completed_name)->insert([
                'keyword' => $dataTemp1['keyword'], 
                'source_text' => $dataTemp1['source_text'],
                'translated_text' => '',
                'language_id' => $check['id'],
                'language_name' => $check['name'],
                'language_code' => $check['code'],
                'category_id' => $category
            ]);
        }

        $string = file_get_contents($file);
        if(strpos($string,"resources") > 0){
            $xml = \XmlParser::load($file);

            if(count($xml->getContent()) > 0){
                foreach($xml->getContent()->string as $string){
                    foreach($string->attributes() as $a => $b){
                        if($a == "name"){
                            $keyword = $b->__toString();
                            $translated_text = $string->__toString();
                            $lang_id = $check['id'];

                            // update database temp 2
                            $temp = DB::table($temp_db_completed_name)
                            ->where('keyword', '=', $keyword)
                            ->where('category_id', '=', $category)
                            ->where('language_id', '=', $check['id']);

                            $temp->update(['translated_text' => $translated_text]);
                            $ret = $temp->select('source_text')->first();
                            $slug = Helper::slug($ret["source_text"], "_");
                            // find in 

                            // import to database
                            $checkExist = TranslateText::checkExist(
                                                $slug, 
                                                $category, 
                                                $lang_id);

                            if($checkExist){
                                if(strlen($translated_text) > 0){
                                    $updateTranslate = TranslateText::updateTranslate(
                                                        $slug, 
                                                        $category, 
                                                        $lang_id,
                                                        $translated_text
                                                    );
                                }else{
                                    $updateTranslate = TranslateText::updateTranslate(
                                                        $slug, 
                                                        $category, 
                                                        $lang_id,
                                                        $translated_text,
                                                        0
                                                    );
                                }
                            }else{
                                // chua co trong db
                                // tim trong bang tam xem co chua
                                $result = DB::table($temp_db_name)
                                            ->select('source_text')
                                            ->where('keyword', '=', $keyword)
                                            ->first();

                                // neu co roi thi lay source_text, translated_text de tao
                                if($result){
                                    $slug = Helper::slug($result['source_text'], "_");
                                    $translate = new TranslateText;
                                    $translate->slug            = $slug;
                                    $translate->source_text     = $result['source_text'];
                                    $translate->trans_text      = $translated_text;
                                    $translate->category_id     = $category;
                                    $translate->language_id     = $lang_id;
                                    $translate->translate_type  = 2;
                                    $translate->created_by      = \Auth::user()->id;
                                    $translate->updated_by      = \Auth::user()->id;
                                    $translate->created_at      = $time_created;
                                    $translate->updated_at      = $time_created;
                                    $translate->save();
                                }
                            }
                        }
                    }
                }
                return $time_created;
            }else{
                return 'empty_file';
            }
        }else{
            return 'empty_file';
        }
    }

    public static function ImportXmlFile($file, $category){
        $time_created = date('Y-m-d H:i:s');
        $temp_db_name = 'temporary_' . \Auth::user()->id;
        if(!Schema::hasTable($temp_db_name)){
            Schema::create($temp_db_name, function (Blueprint $table) {
                $table->increments('id');
                $table->string('keyword');
                $table->string('source_text');
                $table->integer('category_id');
                $table->timestamps();
            });
        }

        $string = file_get_contents($file);
        if(strpos($string,"resources") > 0){
            $xml = \XmlParser::load($file);

            if(count($xml->getContent()) > 0){
                foreach($xml->getContent()->string as $string){
                    foreach($string->attributes() as $a => $b){
                        if($a == "name"){                      
                            DB::table($temp_db_name)->insert([
                                'keyword'       => $b->__toString(),
                                'source_text'   => $string->__toString(),
                                'category_id'   => $category,
                                'created_at'    => $time_created,
                                'updated_at'    => $time_created
                            ]);
                            break;
                        }
                    }
                }
                return $time_created;
            }else{
                return 'empty_file';
            }
        }else{
            return 'empty_file';
        }
    }

    public static function readFileJSON($file, $category, $group){
        // get $language from $group
        $string = file_get_contents($file);
        $string = str_replace(array("\r", "\n", "\t"), "", $string);
        $objs = json_decode($string, true);
        $outputList     = [];
        $outputListFile = [];

        if(count($objs) > 0){
            $fileName       = $file->getClientOriginalName();

            $languageList = TranslateGroup::getLanguages($group);
            $outputList['none'] = [];
            foreach($languageList as $lang){
                $outputList[$lang['code']] = [];
            }

        
            foreach ($objs as $obj) {
                // content file upload
                $outObj         = new OutPut();
                $outObj->text   = $obj['text'];

                if(isset($obj['name'])){
                    $outObj->name = $obj['name'];
                }
                $outputList['none'][] = $outObj;

                // $obj['slug'] = Helper::slug($obj['text'], '_');
                $return_data[] = $obj;
                foreach($languageList as $lang){
                    $outObj         = new OutPut();

                    $slug = Helper::slug($obj['text'], '_');
                    $keyword = $obj['name'];

                    $translateText = TranslateText::findBySlug($slug, $category, $lang['id'], $obj['text']);
                    if($translateText){
                        $outObj->text  = $translateText->trans_text;
                    }else{
                        $outObj->text  = 'Find not found!';
                    }
                    if(isset($keyword)){
                        $outObj->name = $keyword;
                    }
                    $outputList[$lang['code']][] = $outObj;
                }
            }

            $outputListFile['none'] = Helper::storeFileByExt($outputList['none'], 'none', $fileName, 'json');
            foreach($languageList as $lang){
                $outputListFile[$lang['code']] = Helper::storeFileByExt($outputList[$lang['code']], $lang['code'], $fileName, 'json');
            }
        }
        return $outputListFile;
    }

    public static function readFileTEXT($file, $category, $group, $lang){
        // get $language from $group
        $string = file_get_contents($file);
        $objs   = explode("\r\n",$string);
        // $string = str_replace(array("\r", "\n", "\t"), "", $string);
        // $objs = json_decode($string, true);
        $outputList     = [];
        $outputListFile = [];

        if(count($objs) > 0){
            $fileName       = $file->getClientOriginalName();

            $language = Language::find($lang);
            $outputList[$language->code] = [];

            foreach ($objs as $obj) {
                // content file upload
                $outObj         = new OutPut();
                $outObj->text   = $obj;

                if(isset($obj)){
                    $outObj->name = $obj;
                }
                // $obj['slug'] = Helper::slug($obj['text'], '_');
                $return_data[] = $obj;
                $outObj         = new OutPut();

                $slug = Helper::slug($obj, '_');
                $keyword = $obj;

                if($language->code == 'en'){
                    $outObj->text  = $obj;
                }else{
                    // get language code
                    $autoTranslate = new AutoTranslate($obj, 'en', $language->code);
                    $objReturn = $autoTranslate->callApi();
                    $translated_text = '';
                    if($objReturn != null)
                    {
                        if(isset($objReturn['error']))
                        {
                            echo "Error is : ".$objReturn['error']['message'];
                        }
                        else
                        {
                            $translated_text = $objReturn['data']['translations'][0]['translatedText'];
                        }
                    }

                    $outObj->text  = $translated_text;
                }

                if(isset($keyword)){
                    $outObj->name = $keyword;
                }
                $outputList[$language->code][] = $outObj;
            }

            $outputListFile[$language->code] = Helper::storeFileByExt($outputList[$language->code], $language->code, $fileName, 'text');
        }
        return $outputListFile;
    }

    public static function readFileXML($file, $category, $lang){

        // get $language from $group
        $outputList     = [];
        $outputListFile = [];
        $fileName       = $file->getClientOriginalName();
        $langObj        = Language::find($lang);
        $outputList['none'] = [];
        $time_created = date('Y-m-d H:i:s');
        
        $string = file_get_contents($file);
        if(strpos($string,"resources") > 0){
            $xml = \XmlParser::load($file);

            $outputList[$langObj['code']] = [];

            foreach($xml->getContent()->string as $string){
                $outObj         = new OutPut();
                $outObj->text   = $string->__toString();
                foreach($string->attributes() as $a => $b){
                    if($a == "name"){
                        $outObj->name = $b->__toString();
                        // $keyword = $outObj->name;
                        break;
                    }
                }
                $outputList['none'][] = $outObj;
            }

            if(isset($xml->getContent()->{'string-array'})){
                foreach ($xml->getContent()->{'string-array'} as $list){
                    $tempGroup = '';
                    foreach($list->attributes() as $a => $b){
                        if($a == "name"){
                            $tempGroup = $b->__toString();
                            break;
                        }
                    }
                    foreach($list->item as $k=>$v){
                        $outObj         = new OutPut();
                        $outObj->name   = $list->attributes()->name->__toString();
                        $outObj->text   = $v->__toString();

                        $outputList['none']['string-array'][$tempGroup][] = $outObj;
                    }
                }
            }

            $listTranslateRequest = [];
            $textTranslateRequest = [];
            $text = '';
            $temp = '';
            if($langObj['code'] == 'en'){
                $outputList['en'] = $outputList['none'];
            }else{
                $count = 0;
                foreach ($outputList['none'] as $obj) {
                    if(isset($obj->name) && isset($obj->text)){
                        $outObj         = new OutPut();
                        $outObj->text   = $obj->text;
                        $outObj->name   = $obj->name;
                        $slug = Helper::slug($obj->text, "_");
                        $translateText = TranslateText::findBySlug($slug, $category, $langObj['id'], $obj->text);
                        if($translateText){
                            $outObj->text  = $translateText->trans_text;
                        }else{
                            $listTranslateRequest[] = $obj;
                            $temp .= $obj->text . '<br />';
                            if(strlen($temp) > 4500){
                                $textTranslateRequest[] = $text;
                                $text = $obj->text . '<br />';
                                $temp = $obj->text . '<br />';
                            }else{
                                $text = $temp;
                            }
                            $outObj->text  = $obj->text;
                        }
                        $outputList[$langObj['code']][] = $outObj;
                    }
                }

                $textTranslateRequest[] = $text;
                $objsList = [];

                foreach ($textTranslateRequest as $text) {
                    $start = new \DateTime();
                    $process = Process::find(1);
                    $diff = $start->diff(new \DateTime($process->timeStart));
                    if($process->time > 100){
                        // reset 
                        $process->chars = strlen($text);
                        $process->time = 0;
                        $process->timeStart = $start;
                        $process->save();
                    }else{
                        if($process->chars + strlen($text) > 2000000){
                            sleep(110 - $process->time);
                            $process->chars = strlen($text);
                            $process->time = 0;
                            $process->timeStart = $start;
                            $process->save();
                        }else{
                            $process->chars = $process->chars + strlen($text);
                            $process->time = $diff->days * 24 * 60 + $diff->h * 60 + $diff->i;
                            $process->save();
                        }
                    }


                    // get language code
                    $autoTranslate = new AutoTranslate($text, 'en', $langObj['code']);
                    $objReturn = $autoTranslate->callApi();
                    $translated_text = '';
                    if($objReturn != null)
                    {
                        if(isset($objReturn['error']))
                        {
                            echo "Error is : ".$objReturn['error']['message'];
                        }
                        else
                        {
                            $translated_text = $objReturn['data']['translations'][0]['translatedText'];
                        }
                    }

                    $translated_text = $translated_text . " ";
                    $translated_text = str_replace("<br /> ", "<br />", $translated_text);
                    $translated_text = str_replace(" <br />", "<br />", $translated_text);
                    $translated_text = str_replace("'", "\'", $translated_text);
                    $list = explode("<br />",$translated_text);
                    array_pop($list);
                    $objsList = array_merge($objsList,$list);
                }

                for($i = 0; $i < count($objsList); $i++){
                    for($j = 0; $j < count($outputList[$langObj['code']]); $j++){
                        if ($listTranslateRequest[$i]->name == $outputList[$langObj['code']][$j]->name) {
                            if(substr($outputList[$langObj['code']][$j]->text, 0, 1) == ctype_upper(substr($outputList[$langObj['code']][$j]->text, 0, 1))){
                                $objsList[$i] = Helper::mb_ucfirst(trim($objsList[$i]), "utf8");
                            }else{
                                $objsList[$i] = trim($objsList[$i]);
                            }
                            $outputList[$langObj['code']][$j]->text = trim($objsList[$i]);
                            $translate_text                 = new TranslateText;
                            $translate_text->slug           = Helper::slug($listTranslateRequest[$i]->text);
                            $translate_text->category_id    = $category;
                            $translate_text->language_id    = $langObj['id'];
                            $translate_text->source_text    = $listTranslateRequest[$i]->text;
                            $translate_text->trans_text     = mb_convert_encoding($objsList[$i], 'UTF-8', 'UTF-8');
                            $translate_text->translate_type = 0;
                            $translate_text->created_by     = \Auth::user()->id;
                            $translate_text->updated_by     = \Auth::user()->id;
                            $translate_text->created_at     = $time_created;
                            $translate_text->updated_at     = $time_created;
                            $translate_text->save();

                            break;
                        }
                    }
                }

                if(isset($xml->getContent()->{'string-array'})){
                    // foreach ($xml->getContent()->{'string-array'} as $list){
                    //     $tempGroup = '';
                    //     foreach($list->attributes() as $a => $b){
                    //         if($a == "name"){
                    //             $tempGroup = $b->__toString();
                    //             break;
                    //         }
                    //     }
                    //     foreach($list->item as $k=>$v){
                    //         $outObj         = new OutPut();
                    //         $outObj->name   = $list->attributes()->name->__toString();
                    //         $outObj->text   = $v->__toString();

                    //         $outputList['none']['string-array'][$tempGroup][] = $outObj;
                    //     }
                    // }

                    foreach ($outputList['none']['string-array'] as $key=>$obj) {
                        $text = '';
                        if(isset($key)){
                            foreach($obj as $o){
                                $outObj         = new OutPut();
                                $outObj->text   = $o->text;
                                $outObj->name   = $o->name;
                                $slug = Helper::slug($o->text, "_");
                                $translateText = TranslateText::findBySlug($slug, $category, $langObj['id'], $o->text);
                                if($translateText){
                                    $outObj->text  = $translateText->trans_text;
                                }else{
                                    $listTranslateRequest['string-array'][$key][] = $o;
                                    $text .= $o->text . '<br />';
                                    $outObj->text  = $o->text;
                                }
                                $outputList[$langObj['code']]['string-array'][$key][] = $outObj;
                            }
                        }

                        $start = new \DateTime();
                        $process = Process::find(1);
                        $diff = $start->diff(new \DateTime($process->timeStart));
                        if($process->time > 100){
                            // reset 
                            $process->chars = strlen($text);
                            $process->time = 0;
                            $process->timeStart = $start;
                            $process->save();
                        }else{
                            if($process->chars + strlen($text) > 2000000){
                                sleep(110 - $process->time);
                                $process->chars = strlen($text);
                                $process->time = 0;
                                $process->timeStart = $start;
                                $process->save();
                            }else{
                                $process->chars = $process->chars + strlen($text);
                                $process->time = $diff->days * 24 * 60 + $diff->h * 60 + $diff->i;
                                $process->save();
                            }
                        }

                        // get language code
                        $autoTranslate = new AutoTranslate($text, 'en', $langObj['code']);
                        $objReturn = $autoTranslate->callApi();
                        $translated_text = '';
                        if($objReturn != null)
                        {
                            if(isset($objReturn['error']))
                            {
                                echo "Error is : ".$objReturn['error']['message'];
                            }
                            else
                            {
                                $translated_text = $objReturn['data']['translations'][0]['translatedText'];
                            }
                        }

                        $translated_text = $translated_text . " ";
                        $translated_text = str_replace("<br /> ", "<br />", $translated_text);
                        $translated_text = str_replace(" <br />", "<br />", $translated_text);
                        $translated_text = str_replace("'", "\'", $translated_text);
                        $list = explode("<br />",$translated_text);
                        // dd($list);

                        for($i = 0; $i < count($list) - 1; $i++){
                            for($j = 0; $j < count($outputList[$langObj['code']]['string-array'][$key]); $j++){
                                if ($listTranslateRequest['string-array'][$key][$i]->name == $outputList[$langObj['code']]['string-array'][$key][$j]->name) {
                                    if(substr($listTranslateRequest['string-array'][$key][$i]->text, 0, 1) == ctype_upper(substr($listTranslateRequest['string-array'][$key][$i]->text, 0, 1))){
                                        $list[$i] = ucfirst(trim($list[$i]));
                                    }else{
                                        $list[$i] = trim($list[$i]);
                                    }
                                    $outputList[$langObj['code']]['string-array'][$key][$i]->text = trim($list[$i]);

                                    $translate_text                 = new TranslateText;
                                    $translate_text->slug           = Helper::slug($listTranslateRequest['string-array'][$key][$i]->text);
                                    $translate_text->category_id    = $category;
                                    $translate_text->language_id    = $langObj['id'];
                                    $translate_text->source_text    = $listTranslateRequest['string-array'][$key][$i]->text;
                                    $translate_text->trans_text     = trim($list[$i]);
                                    $translate_text->translate_type = 0;
                                    $translate_text->created_by     = \Auth::user()->id;
                                    $translate_text->updated_by     = \Auth::user()->id;
                                    $translate_text->created_at     = $time_created;
                                    $translate_text->updated_at     = $time_created;
                                    $translate_text->save();

                                    break;
                                }
                            }
                        }
                    }
                }

            }
            $outputListFile['none'] = Helper::storeFileByExt($outputList['none'], 'none', $fileName, 'xml');

            $outputListFile[$langObj['code']] = Helper::storeFileByExt($outputList[$langObj['code']], $langObj['code'], $fileName, 'xml');
        }
        return $outputListFile;
    }

    public static function mb_ucfirst($string, $encoding)
    {
        $firstChar = mb_substr($string, 0, 1, $encoding);
        $then = mb_substr($string, 1, null, $encoding);
        return mb_strtoupper($firstChar, $encoding) . $then;
    }

    public static function readFileSTRINGS($file, $category, $lang){

        $outputList     = [];
        $outputListFile = [];
        $time_created = date('Y-m-d H:i:s');

        // get $language from $group
        $string = file_get_contents($file);
        $string = preg_replace('!/\*.*?\*/!s', '', $string);
        $string = preg_replace('/\n\s*\n/', "\n", $string);
        $string = trim(preg_replace('/\s+/', ' ', $string));
        $string = str_replace('"', "", $string);
        $string = str_replace("'", "", $string);
        $string = str_replace('= ', '=', $string);
        $string = str_replace(' =', '=', $string);
        $objs   = explode(";",$string);

        if(count($objs) > 1){
            $fileName           = $file->getClientOriginalName();
            // $languageList       = TranslateGroup::getLanguages($group);
            $langObj            = Language::find($lang);
            $outputList['none'] = [];
            // foreach($languageList as $lang){
                // $outputList[$lang['code']] = [];
            // }
            $outputList[$langObj['code']] = [];

            // to create file .STRINGS
            $item = null;
            foreach ($objs as $obj) {
                $item = explode("=",$obj);
                if(isset($item[0]) && isset($item[1])){
                    $outObj         = new OutPut();
                    $outObj->text   = $item[1];
                    $outObj->name   = trim($item[0]);
                    $slug = Helper::slug($outObj->text, "_");
                    $outputList['none'][] = $outObj;
                }
            }

            // foreach($languageList as $lang){
                
            $listTranslateRequest = [];
            $textTranslateRequest = [];
            $text = '';
            $temp = '';
            
            if($langObj['code'] == 'en'){
                $outputList['en'] = $outputList['none'];
            }else{
                foreach ($outputList['none'] as $obj) {
                    if(isset($obj->name) && isset($obj->text)){
                        $outObj         = new OutPut();
                        $outObj->text   = $obj->text;
                        $outObj->name   = $obj->name;
                        $slug = Helper::slug($obj->text, "_");
                        $translateText = TranslateText::findBySlug($slug, $category, $langObj['id'], $obj->text);
                        if($translateText){
                            $outObj->text  = $translateText->trans_text;
                        }else{
                            $listTranslateRequest[] = $obj;
                            $temp .= $obj->text . '<br />';
                            if(strlen($temp) > 4500){
                                $textTranslateRequest[] = $text;
                                $text = $obj->text . '<br />';
                                $temp = $obj->text . '<br />';
                            }else{
                                $text = $temp;
                            }
                            $outObj->text  = $obj->text;
                        }
                        $outputList[$langObj['code']][] = $outObj;
                    }
                }

                $textTranslateRequest[] = $text;

                $objsList = [];

                foreach ($textTranslateRequest as $text) {
                    $start = new \DateTime();
                    // change time in db
                    $process = Process::find(1);
                    $diff = $start->diff(new \DateTime($process->timeStart));
                    if($process->time > 100){
                        // reset 
                        $process->chars = strlen($text);
                        $process->time = 0;
                        $process->timeStart = $start;
                        $process->save();
                    }else{
                        if($process->chars + strlen($text) > 2000000){
                            sleep(110 - $process->time);
                            $process->chars = strlen($text);
                            $process->time = 0;
                            $process->timeStart = $start;
                            $process->save();
                        }else{
                            $process->chars = $process->chars + strlen($text);
                            $process->time = $diff->days * 24 * 60 + $diff->h * 60 + $diff->i;
                            $process->save();
                        }
                    }

                    // get language code
                    $autoTranslate = new AutoTranslate($text, 'en', $langObj['code']);
                    $objReturn = $autoTranslate->callApi();
                    $translated_text = '';
                    if($objReturn != null)
                    {
                        if(isset($objReturn['error']))
                        {
                            echo "Error is : ".$objReturn['error']['message'];
                        }
                        else
                        {
                            $translated_text = $objReturn['data']['translations'][0]['translatedText'];
                        }
                    }
                    $translated_text = $translated_text . " ";
                    $translated_text = str_replace("<br /> ", "<br />", $translated_text);
                    $translated_text = str_replace(" <br />", "<br />", $translated_text);
                    $translated_text = str_replace("'", "\'", $translated_text);
                    // $objsList = explode("<br />",$translated_text);
                    $list = explode("<br />",$translated_text);
                    array_pop($list);
                    $objsList = array_merge($objsList,$list);
                }

                for($i = 0; $i < count($objsList); $i++){
                    for($j = 0; $j < count($outputList[$langObj['code']]); $j++){
                        if(strlen($objsList[$i]) <= 500){
                            if ($listTranslateRequest[$i]->name == $outputList[$langObj['code']][$j]->name) {
                                if(substr($outputList[$langObj['code']][$j]->name, 0, 1) == ctype_upper(substr($outputList[$langObj['code']][$j]->name, 0, 1))){
                                    $objsList[$i] = ucfirst(trim($objsList[$i]));
                                }else{
                                    $objsList[$i] = trim($objsList[$i]);
                                }
                                $outputList[$langObj['code']][$j]->text = $objsList[$i];
                                $translate_text                 = new TranslateText;
                                $translate_text->slug           = Helper::slug($listTranslateRequest[$i]->text);
                                $translate_text->category_id    = $category;
                                $translate_text->language_id    = $langObj['id'];
                                $translate_text->source_text    = $listTranslateRequest[$i]->text;
                                $translate_text->trans_text     = $objsList[$i];
                                $translate_text->translate_type = 0;
                                $translate_text->created_by     = \Auth::user()->id;
                                $translate_text->updated_by     = \Auth::user()->id;
                                $translate_text->created_at     = $time_created;
                                $translate_text->updated_at     = $time_created;
                                $translate_text->save();

                                break;
                            }
                        }
                    }
                }
            }
            // }

            $outputListFile['none'] = Helper::storeFileByExt($outputList['none'], 'none', $fileName, 'strings');
            // foreach($languageList as $lang){
                $outputListFile[$langObj['code']] = Helper::storeFileByExt($outputList[$langObj['code']], $langObj['code'], $fileName, 'strings');
            // }
        }
        return $outputListFile;
    }

    public static function storeFileByExt($array, $lang, $fileName, $ext){
        switch ($ext) {
            case 'xml':
                return Helper::buildFileXML($array, $lang, $fileName);
                break;
            
            case 'json':
                return Helper::buildFileJSON($array, $lang, $fileName);
                break;
            
            case 'strings':
                return Helper::buildFileSTRINGS($array, $lang, $fileName);
                break;
        
            case 'text':
                return Helper::buildFileTEXT($array, $lang, $fileName);
                break;
            
            default:
                return null;
                break;
        }
    }

    public static function buildFileXML($array, $lang, $fileName){
        $xml = new \SimpleXMLElement("<?xml version=\"1.0\" encoding=\"UTF-8\"?><resources></resources>");
        return Helper::array2XML($array, $lang, $fileName, $xml);
    }

    public static function buildFileJSON($array, $lang, $fileName){
        $jsondata = json_encode($array, JSON_PRETTY_PRINT);

        $urlUserFolder  = public_path() . '/uploads/' . \Auth::id();
        $urlUserFolder2  = public_path() . '/uploads/' . \Auth::id() . '/translated';
        if($lang != 'none'){
            $urlFolder      = public_path() . '/uploads/' . \Auth::id() .'/translated/values-'. strtolower($lang);
        }else{
            $urlFolder      = public_path() . '/uploads/' . \Auth::id() .'/translated/input';
        }
        $urlFile        = $urlFolder . '/' . $fileName;

        Helper::checkFolder($urlUserFolder);
        Helper::checkFolder($urlUserFolder2);
        Helper::checkFolder($urlFolder);

        $fh = fopen($urlFile, 'w');
        fwrite($fh, $jsondata);
        fclose($fh);

        if($lang == 'none'){
            return 'translated/input/' . $fileName;
        }
        return 'translated/values-'. strtolower($lang) . '/' . $fileName;
    }

    public static function buildFileSTRINGS($array, $lang, $fileName){
        $content = '';
        foreach($array as $obj) {
            $content .= '"' . ltrim(rtrim($obj->name," ")," ") . '" = "';
            $content .= $obj->text . '";';
            $content .= "\n";
        }

        $urlUserFolder  = public_path() . '/uploads/' . \Auth::id();
        $urlUserFolder2  = public_path() . '/uploads/' . \Auth::id() . '/translated';
        if($lang != 'none'){
            if($lang == 'zh-CN'){
                $urlFolder      = public_path() . '/uploads/' . \Auth::id() .'/translated/zh-Hans.lproj';
            }else if($lang == 'zh-TW'){
                $urlFolder      = public_path() . '/uploads/' . \Auth::id() .'/translated/zh-Hant.lproj';
            }else{
                $urlFolder      = public_path() . '/uploads/' . \Auth::id() .'/translated/'. strtolower($lang) . '.lproj';
            }
        }else{
            $urlFolder      = public_path() . '/uploads/' . \Auth::id() .'/translated/input';
        }
        $urlFile        = $urlFolder . '/' . $fileName;

        Helper::checkFolder($urlUserFolder);
        Helper::checkFolder($urlUserFolder2);
        Helper::checkFolder($urlFolder);

        $fh = fopen($urlFile, 'w');

        $content = str_replace('&quot;', '"', $content);
        $content = str_replace("'", "\'", $content);

        fwrite($fh, $content);
        fclose($fh);

        if($lang == 'none'){
            return 'translated/input/' . $fileName;
        }else if($lang == 'zh-CN'){
            return 'translated/zh-Hans.lproj/' . $fileName;
        }else if($lang == 'zh-TW'){
            return 'translated/zh-Hant.lproj/' . $fileName;
        }else{
            return 'translated/' . strtolower($lang) . '.lproj/' . $fileName;
        }
        return 'translated/' . strtolower($lang) . '.lproj/' . $fileName;
    }

    public static function buildFileTEXT($array, $lang, $fileName){
        $content = "";
        foreach($array as $obj) {
            $content .= $obj->text;
            $content .= "\r\n";
        }

        $urlUserFolder  = public_path() . '/uploads/' . \Auth::id();
        $urlUserFolder2  = public_path() . '/uploads/' . \Auth::id() . '/translated';
        $urlFolder      = public_path() . '/uploads/' . \Auth::id() .'/translated';
        $urlFile        = $urlFolder . '/' . $fileName;

        Helper::checkFolder($urlUserFolder);
        Helper::checkFolder($urlUserFolder2);
        Helper::checkFolder($urlFolder);

        $fh = fopen($urlFile, 'a');

        $content = str_replace('&quot;', '"', $content);
        $content = str_replace("&#39;", "'", $content);
        // $content = str_replace("'", "\'", $content);
        $content = "<".$lang.">\r\n".$content . "</".$lang.">\r\n";
        
        fwrite($fh, $content);
        fclose($fh);

        if($lang == 'none'){
            return 'translated/' . $fileName;
        }else if($lang == 'zh-CN'){
            return 'translated/' . $fileName;
        }else if($lang == 'zh-TW'){
            return 'translated/' . $fileName;
        }else{
            return 'translated/' . $fileName;
        }
        return 'translated/' . $fileName;
    }

    public static function ExportFileXML($arrayList){
        $xml = new \SimpleXMLElement("<?xml version=\"1.0\" encoding=\"UTF-8\"?><resources></resources>");
        return Helper::exportArray2XML($arrayList, 'en', 'translation.xml', $xml);
    }

    public static function exportArray2XML($array, $lang, $fileName, &$xml){
        foreach($array as $key=>$obj) {
            $string = $xml->addChild('string', $obj['source_text']);
            $string->addAttribute('name', 'name_' . $key);
        }

        $data_out = str_replace( '<resources>', "<resources>\n", $xml->asXML());
        $data_out = str_replace( '<string', "\t<string", $data_out);
        $data_out = str_replace( '</string>', "</string>\n", $data_out);
        $data_out = str_replace( "'", "\'", $data_out);

        return $data_out;
    }

    public static function array2XML($array, $lang, $fileName, &$xml){
        $firstTime = 0;
        foreach($array as $key=>$obj) {
            if(isset($obj->text)){
                $string = $xml->addChild('string', $obj->text);
                $string->addAttribute('name', $obj->name);    
            }
            else{
                foreach ($obj as $key2=>$value2) {
                    $firstTime = 0;
                    $string_array = $xml->addChild($key);
                    $temp = '';
                    foreach ($value2 as $key3 => $value3) {
                        if(isset($value3->name)){
                            if($firstTime == 0){
                                $string_array->addAttribute('name', $value3->name);
                                $firstTime++;
                            }
                            $string_array->addChild('item', $value3->text);
                        }
                    }
                }
            }
        }

        $urlUserFolder     = public_path() . '/uploads/' . \Auth::id();
        $urlUserFolder2     = public_path() . '/uploads/' . \Auth::id() . '/translated';
        if($lang != 'none'){
            if($lang == 'zh-CN'){
                $urlFolder      = public_path() . '/uploads/' . \Auth::id() .'/translated/values-zh-rCN';
            }else if($lang == 'zh-TW'){
                $urlFolder      = public_path() . '/uploads/' . \Auth::id() .'/translated/values-zh-rTW';
            }else{
                $urlFolder      = public_path() . '/uploads/' . \Auth::id() .'/translated/values-'. strtolower($lang);
            }
        }else{
            $urlFolder      = public_path() . '/uploads/' . \Auth::id() .'/translated/input';
        }
        $urlFile        = $urlFolder . '/' . $fileName;

        Helper::checkFolder($urlUserFolder);
        Helper::checkFolder($urlUserFolder2);
        Helper::checkFolder($urlFolder);

        $fh = fopen($urlFile, 'w');

        $data_out = str_replace( '<resources>', "<resources>\n", $xml->asXML());
        $data_out = str_replace( '<string', "\t<string", $data_out);
        $data_out = str_replace( '</string>', "</string>\n", $data_out);
        $data_out = str_replace( '><item', ">\n\t\t<item", $data_out);
        $data_out = str_replace( '</string-array>', "\n\t</string-array>\n", $data_out);
        $data_out = str_replace( "'", "\'", $data_out);

        fwrite($fh, $data_out);
        fclose($fh);

        if($lang == 'none'){
            return 'translated/input/' . $fileName;
        }else if($lang == 'zh-CN'){
            return 'translated/values-zh-rCN/' . $fileName;
        }else if($lang == 'zh-TW'){
            return 'translated/values-zh-rTW/' . $fileName;
        }else{
            return 'translated/values-'. strtolower($lang) . '/' . $fileName;
        }
        return 'translated/values-'. strtolower($lang) . '/' . $fileName;
    }

    public static function zip($files = array()) {
        $fileSource = public_path() . '/zipfile/translated.zip';
        $urlUserFolder  = public_path() . '/uploads/' . \Auth::id();
        $urlZipFolder   = public_path() . '/uploads/' . \Auth::id() .'/zip';
        $urlZipFile   = public_path() . '/uploads/' . \Auth::id() .'/zip/translated.zip';

        if (!file_exists($urlUserFolder)) {
            // ShowAlert: user must be upload file
            return '';
        }else{
            Helper::checkFolder($urlZipFolder);
        }

        if (!file_exists($urlZipFile)) {
            copy($fileSource, $urlZipFile);
            chmod($urlZipFile, 0777);
        }

        $zip = new \ZipArchive();
        if ($zip->open($urlZipFile, \ZipArchive::CREATE) === TRUE)
        {
            foreach ($files as $file) {
                $zip->addFile(public_path() . '/uploads/' . \Auth::id() .'/'. $file, $file);
            }
            $zip->close();
            return $urlZipFile;
        }else{
            return '';
        }
    }

    public static function delete_directory_child($dirname) {
        $dir_handle = null;
        if (is_dir($dirname))
            $dir_handle = opendir($dirname);
        if (!$dir_handle)
            return false;
        while($file = readdir($dir_handle)) {
            if ($file != "." && $file != "..") {
                if (!is_dir($dirname."/".$file))
                    unlink($dirname."/".$file);
                else
                    Helper::delete_directory($dirname.'/'.$file);
            }
        }
        closedir($dir_handle);
        rmdir($dirname);
        return true;
    }

    public static function delete_directory($dirname) {
        $dir_handle = null;
        if (is_dir($dirname))
            $dir_handle = opendir($dirname);
        if (!$dir_handle)
            return false;
        while($file = readdir($dir_handle)) {
            if ($file != "." && $file != "..") {
                if (!is_dir($dirname."/".$file))
                    unlink($dirname."/".$file);
                else
                    Helper::delete_directory_child($dirname.'/'.$file);
            }
        }
        closedir($dir_handle);
        // rmdir($dirname);
        return true;
    }


    public static function checkFolder($dir){
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        return true;
    }

    public static function ExportFileExcel2($arrayList){
        $arrayStyle = array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    ),
                    'font' => array(
                        'bold' => true
                    )
                );

        $arrayWidth = array(
                    'A'     =>  20,
                    'B'     =>  25,
                    'C'     =>  12,
                    'D'     =>  12,
                    'E'     =>  10
                );

        set_time_limit(0);
        ini_set('memory_limit', '1G');
        \Excel::create('translation', function($excel) use ($arrayList, $arrayStyle, $arrayWidth) {

            $excel->sheet('Worksheet', function($sheet) use ($arrayList, $arrayStyle, $arrayWidth) {
                $sheet->getStyle('A1')->applyFromArray($arrayStyle);
                $sheet->getStyle('B1')->applyFromArray($arrayStyle);
                $sheet->getStyle('C1')->applyFromArray($arrayStyle);
                $sheet->getStyle('D1')->applyFromArray($arrayStyle);
                $sheet->getStyle('E1')->applyFromArray($arrayStyle);

                $sheet->setWidth($arrayWidth);

                $sheet->fromArray($arrayList->toArray());
            });

        })->export('xls');
    }

    public static function ReadFileExcel2($file, $category){
        ini_set('memory_limit', '2048M');
        $returnCheck = \Excel::load($file)->skipRows(1)->takeRows(1)->get();

        if(!method_exists($returnCheck, 'getHeading')){
            return 'invalid_file';
        }else{
            $dataCheck = $returnCheck->getHeading();
            if(!(in_array('keyword',$dataCheck, TRUE) && 
                in_array('source_text',$dataCheck, TRUE) && 
                // in_array('translated_text',$dataCheck, TRUE) && 
                // in_array('in_language',$dataCheck, TRUE) && 
                // in_array('category',$dataCheck, TRUE) && 
                // in_array('status',$dataCheck, TRUE) &&
                1)){
                return 'invalid_file';
            }

            // if file is ok, then load all data and process
            $results = \Excel::load($file);
            $data = $results->toArray();
            $time_created = date('Y-m-d H:i:s');
            $user_create  = \Auth::user()->id;

            if (count($data) > 0) {
                // Kiểm tra xem file có đúng định dạng như file Example ko
                if (isset($data[0]['keyword']) && 
                    isset($data[0]['source_text']) && 
                    isset($category) && 
                    // isset($data[0]['status']) &&
                    1) {
                    $list_check = false;

                    // lay du lieu trong ban category va language ra day
                    // $categories = Category::pluck('id', 'name');
                    // $languages  = Language::select('id')->get();

                    // create table contain
                    $temp_db_name = 'temporary_' . \Auth::user()->id;
                    if(!Schema::hasTable($temp_db_name)){
                        Schema::create($temp_db_name, function (Blueprint $table) {
                            $table->increments('id');
                            $table->string('keyword');
                            $table->string('source_text');
                            $table->integer('category_id');
                            $table->timestamps();
                        });
                    }

                    foreach ($data as $row) {
                        if(strlen($row['keyword']) > 0 && strlen($row['source_text']) > 0){
                            DB::table($temp_db_name)->insert([
                                'keyword'       => $row['keyword'],
                                'source_text'   => $row['source_text'],
                                'category_id'   => $category,
                                'created_at'    => $time_created,
                                'updated_at'    => $time_created
                            ]);
                            $list_check = true;
                        }
                        // foreach($languages as $lang){
                            /*$checkExist = TranslateText::checkExist(
                                                $row['keyword'], 
                                                $category, 
                                                $lang['id']);*/
                            // $list_check = true;
                            // if(!$checkExist){
                                // if not exist then add to database
                                // $translate = new TranslateText;

                                // $translate->keyword = $row['keyword'];
                                // $translate->source_text = $row['source_text'];
                                // $translate->trans_text = '';
                                // $translate->language_id = $lang['id'];
                                // $translate->category_id = $category;
                                // $translate->translate_type = Helper::getValueStatus($row['status']);
                                // $translate->created_by = $user_create;
                                // $translate->created_at = $time_created;
                                // $translate->updated_by = $user_create;
                                // $translate->updated_at = $time_created;

                                // $translate->save();
                                // $list_check = true;
                            // }
                        // }
                    }
                    if($list_check){
                        return $time_created;
                    }
                    return 'empty_file';
                }else{
                    return 'invalid_file';
                }
            }else{
                return 'empty_file';
            }
        }
    }

    public static function ImportFileExcel2($file, $category){
        ini_set('memory_limit', '2048M');
        $returnCheck = \Excel::load($file)->skipRows(1)->takeRows(1)->get();

        if(!method_exists($returnCheck, 'getHeading')){
            return 'invalid_file';
        }else{
            $dataCheck = $returnCheck->getHeading();
            if(!(in_array('keyword',$dataCheck, TRUE) && 
                // in_array('source_text',$dataCheck, TRUE) && 
                in_array('translated_text',$dataCheck, TRUE) && 
                in_array('in_language',$dataCheck, TRUE) && 
                // in_array('category',$dataCheck, TRUE) && 
                // in_array('status',$dataCheck, TRUE) && 
                1)){
                return 'invalid_file';
            }

            // if file is ok, then load all data and process
            $results = \Excel::load($file);
            $data = $results->toArray();
            $time_created = date('Y-m-d H:i:s');
            $user_create  = \Auth::user()->id;
            $temp_db_name = 'temporary_' . \Auth::user()->id;

            // create table contain
            $temp_db_completed_name = 'temporary_' . \Auth::user()->id . '_completed';
            if(!Schema::hasTable($temp_db_completed_name)){
                Schema::create($temp_db_completed_name, function (Blueprint $table) {
                    $table->increments('id');
                    $table->string('keyword');
                    $table->string('source_text');
                    $table->string('translated_text');
                    $table->integer('language_id');
                    $table->string('language_name');
                    $table->string('language_code');
                    $table->integer('category_id');
                    $table->timestamps();
                });
            }


            if (count($data) > 0) {
                // Kiểm tra xem file có đúng định dạng như file Example ko
                if (isset($data[0]['keyword']) && 
                    isset($data[0]['translated_text']) && 
                    isset($data[0]['in_language']) && 
                    isset($category) && 
                    // isset($data[0]['status']) && 
                    1) {
                    $list_check = true;

                    // lay du lieu trong ban category va language ra day
                    // $categories = Category::pluck('id', 'name');
                    $languages  = Language::pluck('id', 'name');

                    $current_language = 0;

                    foreach ($data as $row) {
                        $language = Language::checkExist($row['in_language']);
                        if($language){
                            $lang_id = $languages[$row['in_language']];
                                
                            if($lang_id != $current_language){
                                $current_language = $lang_id;
                                // check current language existed in temp 2 table
                                TranslateText::checkCurrentLanguage($language, $time_created);
                            }

                            $checkExist = TranslateText::checkExist(
                                                $row['keyword'], 
                                                $category, 
                                                $lang_id);

                            DB::table($temp_db_completed_name)
                                ->where('keyword', '=', $row['keyword'])
                                ->where('category_id', '=', $category)
                                ->where('language_id', '=', $lang_id)
                                ->update(['translated_text' => $row['translated_text']]);

                            if($checkExist){
                                if(strlen($row['translated_text']) > 0){
                                    $updateTranslate = TranslateText::updateTranslate(
                                                        $row['keyword'], 
                                                        $category, 
                                                        $lang_id,
                                                        $row['translated_text']
                                                    );
                                }else{
                                    $updateTranslate = TranslateText::updateTranslate(
                                                        $row['keyword'], 
                                                        $category, 
                                                        $lang_id,
                                                        $row['translated_text'],
                                                        0
                                                    );
                                }
                                $list_check &= true;
                            }else{
                                // chua co trong db
                                // tim trong bang tam xem co chua
                                $result = DB::table($temp_db_name)
                                            ->select('keyword', 'source_text')
                                            ->where('keyword', '=', $row['keyword'])
                                            ->first();

                                // neu co roi thi lay source_text, translated_text de tao
                                if($result){
                                    $translate = new TranslateText;
                                    $translate->keyword         = $row['keyword'];
                                    $translate->source_text     = $result['source_text'];
                                    $translate->trans_text      = $row['translated_text'];
                                    $translate->category_id     = $category;
                                    $translate->language_id     = $lang_id;
                                    $translate->translate_type  = 2;
                                    $translate->created_by      = \Auth::user()->id;
                                    $translate->updated_by      = \Auth::user()->id;
                                    $translate->created_at      = $time_created;
                                    $translate->updated_at      = $time_created;

                                    if($translate->save()){
                                        $list_check &= true;
                                    }else{
                                        $list_check &= false;
                                    }
                                }
                            }
                        }else{
                            return 'invalid_language';
                        }
                    }
                    return $list_check;
                }else{
                    return 'invalid_file';
                }
            }else{
                return 'empty_file';
            }
        }
    }

    public static function getValueStatus($status){
        switch ($status) {
            case 'Auto':
                return 0;
                break;

            case 'Contributor':
                return 1;
                break;

            case 'Confirmed':
                return 2;
                break;
            
            default:
                return 0;
                break;
        }
    }

    public static function slug($string, $replace = '_'){
        $string = strtolower($string);
        return preg_replace('/\s+/', $replace, $string);
    }
}