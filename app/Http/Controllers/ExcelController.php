<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Account;
use App\Models\Customer;
use App\Models\Document;
use App\Models\BufferExcel;
use Illuminate\Http\Request;
use App\Imports\Api\BufferImport;
use Error;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ExcelController extends Controller
{
    public function storeFile($file,$modules_id){
        $module =  Account::find($modules_id);
        $tenant_id = 1;
        $fileAbbreviation  =  $module->name;
        $generateFileNameModuleWise = trim($fileAbbreviation . '.' . $file->getClientOriginalExtension()); 
        $path = 'public/imports/tenant_'.$tenant_id.'/'.$module->name;
       
        if(Storage::exists($path)){
            Storage::deleteDirectory($path);
           
        }
        $path = $file->storeAs($path,$generateFileNameModuleWise);
        return $path; 
        
        
    }
    public function ExcelImport($document,$request) {
        $moduleImportFunc = new BufferImport($request->reupload,$document);
        $file = storage_path('app/'.$document->name);
        $moduleImportFunc->import($file);
        return response()->json([
            'errorData' =>  $moduleImportFunc->getErrorData(),
            'successData' =>  $moduleImportFunc->getSuccessData(),
        ]);
    }

    public function uploadDocument(Request $request){
       $path_name =  $this->storeFile($request->file('file'),$request->module_id);
       $document =  Document::create([
            'name' => $path_name,
            'imported_by' => 1, // auth user
            'module_id' => $request->module_id,
        ]);
      return  $this->ExcelImport($document,$request);  
    }
    public function transferToModule(Request $request){
        
       try{
        DB::beginTransaction();
        $q  =  BufferExcel::where('document_id',$request->document_id)
        ->where('validate_status',1);
        
        $bufferData =  $q->pluck('data')->toArray();
        if(count($bufferData) == 0){
            throw new Exception('No document found');
        }
        foreach($bufferData as $data) {
            $data = (array) json_decode($data);
            Customer::create($data);
        }
        $q->update([
            'import_status' => true
        ]);
        DB::commit();
        return response()->json([
            'status' => true,
        ]);
       }catch(Exception $e){
        DB::rollBack();
        return response()->json([
            'status' => false,
            'message' => $e->getMessage()
        ]);
       }

       



      
    

    }
}
