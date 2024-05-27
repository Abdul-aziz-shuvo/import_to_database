<?php

namespace App\Imports\Api;

use App\Models\BufferExcel;


use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class BufferImport implements ToModel,SkipsOnFailure,WithValidation,WithHeadingRow
{
    use Importable,RemembersRowNumber,SkipsFailures;
    protected $errorData = [];
    protected $successData = [];
    protected $initial = [
        'validate_status' => false,
        'import_status' => false,
        'reupload' => false,
        'data' => [],
        'message' => '',
        'row_no' => null,
        'document_id' => null
    ];
    public function __construct($reupload,$document)
    {
        $this->initial['reupload'] = $reupload;
        $this->initial['document_id'] = $document->id;
    }
    public function model(array $row)
    {
        if($this->initial['reupload']){
        return $this->reValidate($row);
        }
        if(!$this->initial['reupload']){
            return  $this->validate($row);
        }
    }
    public function rules():array {
        return [
            'email' => 'required'
        ];
    }
     public function reValidate($row) {
        $this->setInitialForSuccess($row);

        $model = new BufferExcel($this->initial);
        $this->successData [] = $this->initial;
        return $model;
    }
    public function validate($row) {
        $this->setInitialForSuccess($row);
        $model = new BufferExcel($this->initial);
        $this->successData[] = $this->initial;
        return $model;
    }
    public function getSuccessData() {
        return $this->successData;
    }
    public function getErrorData() {
        return $this->errorData;
    }
    public function setInitialForSuccess($row) {
        $this->initial = [...$this->initial,...[ 
        'validate_status' => true,
        'import_status' => false,
        'reupload' => false,
        'data' => json_encode($row),
        'message' => '',
        'row_no' => $this->getRowNumber()]];
    }
    public function setInitialForError($row,$error) {
        $this->initial = [...$this->initial,
            ...[ 
            'validate_status' => false,
            'import_status' => false,
            'reupload' => false,
            'data' => json_encode($error->values()),
            'message' => $error->errors()[0],
            'row_no' => $error->row()
            ]
        ];
    }
    public function onFailure(Failure ...$failures)
    {
        foreach($failures as $failure){
            $this->errorData[] = [
                'row_no' => $failure->row(),
                'error' => $failure->errors(),
                'value' => $failure->values(),
            ];
           
            $this->setInitialForError($this->initial,$failure);
            $dd =  BufferExcel::create($this->initial);
            
           
        }
    }
}
