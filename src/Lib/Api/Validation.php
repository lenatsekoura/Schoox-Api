<?php

namespace App\Lib\Api;

class Validation
{
    protected $valid_keys;
    protected $item;

    public function __construct(array $valid_keys, array $item)
    {
        $this->valid_keys = $valid_keys;
        $this->item = $item;
    }

    public function validate() :array
    {
        $result = array('status'=>'', 'code'=>'', 'message'=>'');

        //validate required keys
        foreach ($this->valid_keys as $key => $value){
            if($value === 'required') {
                if (
                    !isset($this->item[$key]) ||
                    $this->item[$key] === ''  &&
                    $this->item[$key] !== false
                ) {
                    $result['status'] = 'error';
                    $result['code'] = '400';
                    $result['message'] = $key . ' is required';
                }
            }
        }
        //validate key set
        if(
            count(array_diff_key($this->item, $this->valid_keys))>0
        ){
            $result['status'] = 'error';
            $result['code'] = '400';
            $result['message'] = 'Check Json structure';
            $result['description'] = $this->valid_keys;
        }

        return $result;
    }
}
