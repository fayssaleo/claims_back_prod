<?php

namespace App\Modules\TypeOfEquipment\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Modules\TypeOfEquipment\Models\TypeOfEquipment;


class TypeOfEquipmentController extends Controller{

    public function index(){

        $typeOfEquipment=TypeOfEquipment::all();

        return [
            "payload" => $typeOfEquipment,
            "status" => "200_00"
        ];
    }
    public function indexEquipment(){

        $typeOfEquipment=TypeOfEquipment::select()->where('categorie', "equipment")->get();

        return [
            "payload" => $typeOfEquipment,
            "status" => "200_00"
        ];
    }
    public function indexAutomobile(){

        $typeOfEquipment=TypeOfEquipment::select()->where('categorie', "automobile")->get();

        return [
            "payload" => $typeOfEquipment,
            "status" => "200_00"
        ];
    }
   

    public function get($id){
        $typeOfEquipment=TypeOfEquipment::find($id);
        if(!$typeOfEquipment){
            return [
                "payload" => "The searched row does not exist !",
                "status" => "404_1"
            ];
        }
        else {
            return [
                "payload" => $typeOfEquipment,
                "status" => "200_1"
            ];
        }
    }

    public function create(Request $request){
        $validator = Validator::make($request->all(), [
            "name" => "required:nature_of_damages,name",
        ]);
        if ($validator->fails()) {
            return [
                "payload" => $validator->errors(),
                "status" => "406_2"
            ];
        }
        $typeOfEquipment=TypeOfEquipment::make($request->all());
        $typeOfEquipment->save();
        return [
            "payload" => $typeOfEquipment,
            "status" => "200"
        ];
    }

    public function update(Request $request){
        $validator = Validator::make($request->all(), [
            "id" => "required",
        ]);
        if ($validator->fails()) {
            return [
                "payload" => $validator->errors(),
                "status" => "406_2"
            ];
        }
        $typeOfEquipment=TypeOfEquipment::find($request->id);
        if (!$typeOfEquipment) {
            return [
                "payload" => "The searched row does not exist !",
                "status" => "404_3"
            ];
        }
        if($request->name!=$typeOfEquipment->name){
            if(TypeOfEquipment::where("name",$request->name)->count()>0)
                return [
                    "payload" => "The typeOfEquipment has been already taken ! ",
                    "status" => "406_2"
                ];
        }
        $typeOfEquipment->name=$request->name;

        $typeOfEquipment->save();
        return [
            "payload" => $typeOfEquipment,
            "status" => "200"
        ];
    }

    public function delete(Request $request){
        $typeOfEquipment=TypeOfEquipment::find($request->id);

        if(!$typeOfEquipment){
            return [
                "payload" => "The searched row does not exist !",
                "status" => "404_4"
            ];
        }
        else {
            $typeOfEquipment->delete();
            return [
                "payload" => "Deleted successfully",
                "status" => "200_4"
            ];
        }
    }
}
