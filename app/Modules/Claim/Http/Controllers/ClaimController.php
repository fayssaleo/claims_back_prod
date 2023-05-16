<?php

namespace App\Modules\Claim\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Claim\Models\Claim;
use App\Modules\ClaimOrIncidentFile\Models\ClaimOrIncidentFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Libs\UploadTrait;
use App\Modules\Automobile\Models\Automobile;
use App\Modules\Container\Models\Container;
use App\Modules\Equipment\Models\Equipment;
use App\Modules\Vessel\Models\Vessel;
use PhpParser\Node\Expr\Cast\Object_;
use SebastianBergmann\Type\ObjectType;
use stdClass;

class ClaimController extends Controller
{
    use UploadTrait;

    public function getDownloadsFiles(Request $request)
    {
        $claim = Claim::find($request->id);
        if (!$claim) {

            return [
                "payload" => "The searched row does not exist !",
                "status" => "404_1"
            ];
        } else {
            $downloads = [];

            array_push($downloads, ["inidentReports" => $claim->claimOrIncidentFile]);
            $equipments = $claim->equipments()->with("liabilityInsuranceFiles")->with("typeOfEquipment")->with("matricule")->get();
            $equipmentsFile = [];
            for ($i = 0; $i < count($equipments); $i++) {
                $equipments_inssurance = [];
                $equipments_liability = [];
                $equipments_estimations_items = [];
                $equipments_files = $equipments[$i]->liabilityInsuranceFiles;
                $equipments_estimations = $equipments[$i]->estimate;

                $equipment_name = "Equipment";
                if (!is_null($equipments[$i]->typeOfEquipment)) {
                    if (!is_null($equipments[$i]->matricule)) {
                        $equipment_name = $equipments[$i]->typeOfEquipment->name . " : " . $equipments[$i]->matricule->id_equipment;
                    } else
                        $equipment_name = $equipments[$i]->typeOfEquipment->name;
                }
                for ($j = 0; $j < count($equipments_files); $j++) {

                    if ($equipments_files[$j]->type == "insurance")
                        array_push($equipments_inssurance, $equipments_files[$j]);

                    if ($equipments_files[$j]->type == "liability")
                        array_push($equipments_liability, $equipments_files[$j]);
                }
                for ($j = 0; $j < count($equipments_estimations); $j++) {
                    $estimationValuation = $equipments_estimations[$j]->otherValuation;
                    $total = 0;
                    for ($k = 0; $k < count($estimationValuation); $k++) {
                        $total += $estimationValuation[$k]->value_valuation;
                    }
                    array_push($equipments_estimations_items, [
                        $total . " " . $equipments_estimations[$j]->currency_estimate =>
                        $equipments_estimations[$j]->estimateFile
                    ]);
                }
                array_push($equipmentsFile, [$equipment_name => [
                    "insurance" =>
                    $equipments_inssurance,
                    "liability" =>
                    $equipments_liability,
                    "estimation" =>
                    $equipments_estimations_items

                ]]);
            }
            //---------------------------------------------------------------------------

            $automobiles = $claim->automobiles()->with("liabilityInsuranceFiles")->with("typeOfEquipment")->with("matricule")->get();
            $automobilesFile = [];
            for ($i = 0; $i < count($automobiles); $i++) {
                $automobiles_inssurance = [];
                $automobiles_liability = [];
                $automobiles_estimations_items = [];
                $automobiles_files = $automobiles[$i]->liabilityInsuranceFiles;
                $automobiles_estimations = $automobiles[$i]->estimate;

                $equipment_name = "Equipment";
                if (!is_null($automobiles[$i]->typeOfEquipment)) {
                    if (!is_null($automobiles[$i]->matricule)) {
                        $equipment_name = $automobiles[$i]->typeOfEquipment->name . " : " . $automobiles[$i]->matricule->id_equipment;
                    } else
                        $equipment_name = $automobiles[$i]->typeOfEquipment->name;
                }
                for ($j = 0; $j < count($automobiles_files); $j++) {

                    if ($automobiles_files[$j]->type == "insurance")
                        array_push($automobiles_inssurance, $automobiles_files[$j]);

                    if ($automobiles_files[$j]->type == "liability")
                        array_push($automobiles_liability, $automobiles_files[$j]);
                }
                for ($j = 0; $j < count($automobiles_estimations); $j++) {
                    $estimationValuation = $automobiles_estimations[$j]->otherValuation;
                    $total = 0;
                    for ($k = 0; $k < count($estimationValuation); $k++) {
                        $total += $estimationValuation[$k]->value_valuation;
                    }
                    array_push($automobiles_estimations_items, [
                        $total . " (" . $automobiles_estimations[$j]->currency_estimate . ")" =>
                        $automobiles_estimations[$j]->estimateFile
                    ]);
                }
                array_push($automobilesFile, [$equipment_name => [
                    "insurance" =>
                    $automobiles_inssurance,
                    "liability" =>
                    $automobiles_liability,
                    "estimation" =>
                    $automobiles_estimations_items

                ]]);
            }
            //----------------------------------------------------------------------------


            $vessels = $claim->vessels()->with("liabilityInsuranceFiles")->with("typeOfEquipment")->with("shippingLine")->get();
            $vesselsFile = [];
            for ($i = 0; $i < count($vessels); $i++) {
                $vessels_inssurance = [];
                $vessels_liability = [];
                $vessels_estimations_items = [];
                $vessels_files = $vessels[$i]->liabilityInsuranceFiles;
                $vessels_estimations = $vessels[$i]->estimate;

                $vessel_name = "Vessel";
                if (!is_null($vessels[$i]->shippingLine)) {
                    //                if ($vessels[$i]->id === 17)
                    //                  dd($vessels[$i]->vessel_number);
                    if (!empty($vessels[$i]->vessel_number)) {
                        $vessel_name = $vessels[$i]->shippingLine->name . " : " . $vessels[$i]->vessel_number;
                    } else
                        $vessel_name =  $vessels[$i]->shippingLine->name;
                } else if (!empty($vessels[$i]->vessel_number)) {

                    $vessel_name =  $vessels[$i]->vessel_number;
                }
                for ($j = 0; $j < count($vessels_files); $j++) {

                    if ($vessels_files[$j]->type == "insurance")
                        array_push($vessels_inssurance, $vessels_files[$j]);

                    if ($vessels_files[$j]->type == "liability")
                        array_push($vessels_liability, $vessels_files[$j]);
                }
                for ($j = 0; $j < count($vessels_estimations); $j++) {
                    $estimationValuation = $vessels_estimations[$j]->otherValuation;
                    $total = 0;
                    for ($k = 0; $k < count($estimationValuation); $k++) {
                        $total += $estimationValuation[$k]->value_valuation;
                    }
                    array_push($vessels_estimations_items, [
                        $total . " (" . $vessels_estimations[$j]->currency_estimate . ")" =>
                        $vessels_estimations[$j]->estimateFile
                    ]);
                }
                array_push($vesselsFile, [$vessel_name => [
                    "insurance" =>
                    $vessels_inssurance,
                    "liability" =>
                    $vessels_liability,
                    "estimation" =>
                    $vessels_estimations_items

                ]]);
            }

            //----------------------------------------------------------------------------

            $containers = $claim->containers()->with("liabilityInsuranceFiles")->with("typeOfEquipment")->with("shippingLine")->get();
            $containersFile = [];
            for ($i = 0; $i < count($containers); $i++) {
                $containers_inssurance = [];
                $containers_liability = [];
                $containers_estimations_items = [];
                $containers_files = $containers[$i]->liabilityInsuranceFiles;
                $containers_estimations = $containers[$i]->estimate;

                $container_name = "Container";
                if (!is_null($containers[$i]->shippingLine)) {
                    if (!empty($containers[$i]->containerID)) {
                        $container_name =  $containers[$i]->shippingLine->name . " : " . $containers[$i]->containerID;
                    } else {
                        $container_name =  $containers[$i]->shippingLine->name;
                    }
                } else {
                    if (!empty($containers[$i]->containerID)) {
                        $container_name = $containers[$i]->containerID;
                    }
                }
                for ($j = 0; $j < count($containers_files); $j++) {

                    if ($containers_files[$j]->type == "insurance")
                        array_push($containers_inssurance, $containers_files[$j]);

                    if ($containers_files[$j]->type == "liability")
                        array_push($containers_liability, $containers_files[$j]);
                }
                for ($j = 0; $j < count($containers_estimations); $j++) {
                    $estimationValuation = $containers_estimations[$j]->otherValuation;
                    $total = 0;
                    for ($k = 0; $k < count($estimationValuation); $k++) {
                        $total += $estimationValuation[$k]->value_valuation;
                    }
                    array_push($containers_estimations_items, [
                        $total . " (" . $containers_estimations[$j]->currency_estimate . ")" =>
                        $containers_estimations[$j]->estimateFile
                    ]);
                }
                array_push($containersFile, [$container_name => [
                    "insurance" =>
                    $containers_inssurance,
                    "liability" =>
                    $containers_liability,
                    "estimation" =>
                    $containers_estimations_items

                ]]);
            }


            array_push($downloads, ["equipments" => $equipmentsFile]);
            array_push($downloads, ["automobiles" => $automobilesFile]);
            array_push($downloads, ["vessels" => $vesselsFile]);
            array_push($downloads, ["containers" => $containersFile]);

            return [
                "payload" =>  $downloads,
                "status" => "200_1"
            ];
        }
    }

    public function indexClaimsByIds(Request $request)
    {

        $claim = Claim::select()->whereIn("id", $request->claims_id)->get();
        $claims = [];
        for ($i = 0; $i < count($claim); $i++) {


            $oneClaim = [
                "id" => $claim[$i]->id,
                "claim_date" => $claim[$i]->claim_date,
                "incident_date" => $claim[$i]->incident_date,
                "ClaimOrIncident" => $claim[$i]->ClaimOrIncident,
                "status" => $claim[$i]->status,
                "incident_report" => $claim[$i]->incident_report,
                "type" => $claim[$i]->type,
                "created_at" => $claim[$i]->created_at,
                "updated_at" => $claim[$i]->updated_at,
                "totalEstimation" => Claim::getEstimation($claim[$i]->id),
                "category" => Claim::getType($claim[$i]->id),
                "getEquipmentIds" => Claim::getEquipmentIds($claim[$i]->id),
                "getDeclare" => Claim::getDeclare($claim[$i]->id),
                "equipments" => Equipment::select()->where("claim_id", $claim[$i]->id)
                    ->with("typeOfEquipment")
                    ->with("brand")
                    ->with("estimate.otherValuation")
                    ->with("natureOfDamage")
                    ->with("companie")
                    ->with("department")
                    ->with("matricule")
                    ->get(),
                "containers" => Container::select()->where("claim_id", $claim[$i]->id)
                    ->with("typeOfEquipment")
                    ->with("natureOfDamage")
                    ->with("companie")
                    ->with("department")
                    ->with("estimate.otherValuation")
                    ->with("shippingLine")
                    ->get(),
                "vessels" => Vessel::select()->where("claim_id", $claim[$i]->id)
                    ->with("typeOfEquipment")
                    ->with("companie")
                    ->with("natureOfDamage")
                    ->with("department")
                    ->with("estimate.otherValuation")
                    ->with("shippingLine")
                    ->get(),
                "automobiles" => Automobile::select()->where("claim_id", $claim[$i]->id)
                    ->with("matricule")
                    ->with("typeOfEquipment")
                    ->with("Brand")
                    ->with("natureOfDamage")
                    ->with("companie")
                    ->with("department")
                    ->with("estimate.otherValuation")
                    ->get(),
            ];
            array_push($claims, $oneClaim);
        }
        return [
            "payload" => $claims,
            "status" => "200_00"
        ];
    }

    public function indexClaims()
    {

        $claim = Claim::select()->where('ClaimOrIncident', "Claim")->get();
        $claims = [];
        for ($i = 0; $i < count($claim); $i++) {


            $oneClaim = [
                "id" => $claim[$i]->id,
                "claim_date" => $claim[$i]->claim_date,
                "incident_date" => $claim[$i]->incident_date,
                "ClaimOrIncident" => $claim[$i]->ClaimOrIncident,
                "status" => $claim[$i]->status,
                "incident_report" => $claim[$i]->incident_report,
                "type" => $claim[$i]->type,
                "created_at" => $claim[$i]->created_at,
                "updated_at" => $claim[$i]->updated_at,
                "totalEstimation" => Claim::getEstimation($claim[$i]->id),
                "category" => Claim::getType($claim[$i]->id),
                "getEquipmentIds" => Claim::getEquipmentIds($claim[$i]->id),
                "getDeclare" => Claim::getDeclare($claim[$i]->id),
                "getReinvoiced" => Claim::getReinvoiced($claim[$i]->id),
            ];
            array_push($claims, $oneClaim);
        }
        return [
            "payload" => $claims,
            "status" => "200_00"
        ];
    }

    public function indexClaimsByMonth($monthAndYear)
    {
        $year = explode("-", $monthAndYear)[0];
        $month = explode("-", $monthAndYear)[1];
        $claim = Claim::select()->where('ClaimOrIncident', "Claim")->whereMonth('incident_date', (int)$month)->whereYear('incident_date', (int)$year)->get();
        $claims = [];
        for ($i = 0; $i < count($claim); $i++) {


            $oneClaim = [
                "id" => $claim[$i]->id,
                "claim_date" => $claim[$i]->claim_date,
                "incident_date" => $claim[$i]->incident_date,
                "ClaimOrIncident" => $claim[$i]->ClaimOrIncident,
                "status" => $claim[$i]->status,
                "incident_report" => $claim[$i]->incident_report,
                "type" => $claim[$i]->type,
                "created_at" => $claim[$i]->created_at,
                "updated_at" => $claim[$i]->updated_at,
                "totalEstimation" => Claim::getEstimation($claim[$i]->id),
                "category" => Claim::getType($claim[$i]->id),
                "getEquipmentIds" => Claim::getEquipmentIds($claim[$i]->id),
                "getDeclare" => Claim::getDeclare($claim[$i]->id),
                "getReinvoiced" => Claim::getReinvoiced($claim[$i]->id),
            ];
            array_push($claims, $oneClaim);
        }
        return [
            "payload" => $claims,
            "status" => "200_00"
        ];
    }


    public function indexIncidents()
    {

        $claim = Claim::select()->where('ClaimOrIncident', "Incident")->get();
        for ($i = 0; $i < count($claim); $i++) {

            $claims = [];
            $oneClaim[] = [
                "id" => $claim[$i]->id,
                "claim_date" => $claim[$i]->claim_date,
                "incident_date" => $claim[$i]->incident_date,
                "ClaimOrIncident" => $claim[$i]->ClaimOrIncident,
                "status" => $claim[$i]->status,
                "incident_report" => $claim[$i]->incident_report,
                "type" => $claim[$i]->type,
                "created_at" => $claim[$i]->created_at,
                "updated_at" => $claim[$i]->updated_at,
                "totalEstimation" => Claim::getEstimation($claim[$i]->id),
                "category" => Claim::getType($claim[$i]->id),
                "getEquipmentIds" => Claim::getEquipmentIds($claim[$i]->id),
                "getDeclare" => Claim::getDeclare($claim[$i]->id),
                "getReinvoiced" => Claim::getReinvoiced($claim[$i]->id),

            ];
            array_push($claims, $oneClaim);
        }
        return [
            "payload" => $claims[0],
            "status" => "200_00"
        ];
    }
    public function indexIncidentsByMonth($monthAndYear)
    {

        $year = explode("-", $monthAndYear)[0];
        $month = explode("-", $monthAndYear)[1];
        $claim = Claim::select()->where('ClaimOrIncident', "Incident")->whereMonth('incident_date', (int)$month)->whereYear('incident_date', (int)$year)->get();
        $claims = [];
        for ($i = 0; $i < count($claim); $i++) {


            $oneClaim = [
                "id" => $claim[$i]->id,
                "claim_date" => $claim[$i]->claim_date,
                "incident_date" => $claim[$i]->incident_date,
                "ClaimOrIncident" => $claim[$i]->ClaimOrIncident,
                "status" => $claim[$i]->status,
                "incident_report" => $claim[$i]->incident_report,
                "type" => $claim[$i]->type,
                "created_at" => $claim[$i]->created_at,
                "updated_at" => $claim[$i]->updated_at,
                "totalEstimation" => Claim::getEstimation($claim[$i]->id),
                "category" => Claim::getType($claim[$i]->id),
                "getEquipmentIds" => Claim::getEquipmentIds($claim[$i]->id),
                "getDeclare" => Claim::getDeclare($claim[$i]->id),
                "getReinvoiced" => Claim::getReinvoiced($claim[$i]->id),

            ];
            array_push($claims, $oneClaim);
        }
        return [
            "payload" => $claims,
            "status" => "200_00"
        ];
    }

    public function get($id)
    {

        $claim = Claim::find($id);
        if (!$claim) {
            return [
                "payload" => "The searched row does not exist !",
                "status" => "404_1"
            ];
        } else {
            return [
                "payload" => $claim,
                "status" => "200_1"
            ];
        }
    }

    public function treated(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "id" => "required",
        ]);
        if ($validator->fails()) {
            return [
                "payload" => $validator->errors(),
                "status" => "406_2"
            ];
        }
        $claim = Claim::find($request->id);
        if (!$claim) {
            return [
                "payload" => "The searched row does not exist !",
                "status" => "404_1"
            ];
        } else {
            $claim->status = "On progress";

            //$claim->getEquipmentIds = Claim::getEquipmentIds($claim->id);
            $claim->save();
            $claim->totalEstimation = Claim::getEstimation($claim->id);
            $claim->category = Claim::getType($claim->id);
            $claim->getEquipmentIds = Claim::getEquipmentIds($claim->id);
            $claim->getDeclare = Claim::getDeclare($claim->id);
            $claim->getReinvoiced = Claim::getReinvoiced($claim[$i]->id);
            return [
                "payload" => $claim,
                "status" => "200_1"
            ];
        }
    }

    public function closed(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "id" => "required",
        ]);
        if ($validator->fails()) {
            return [
                "payload" => $validator->errors(),
                "status" => "406_2"
            ];
        }
        $claim = Claim::find($request->id);
        if (!$claim) {
            return [
                "payload" => "The searched row does not exist !",
                "status" => "404_1"
            ];
        } else {
            $claim->status = "Closed";

            //$claim->getEquipmentIds = Claim::getEquipmentIds($claim->id);
            $claim->save();
            $claim->totalEstimation = Claim::getEstimation($claim->id);
            $claim->category = Claim::getType($claim->id);
            $claim->getEquipmentIds = Claim::getEquipmentIds($claim->id);
            $claim->getDeclare = Claim::getDeclare($claim->id);
            $claim->getReinvoiced = Claim::getReinvoiced($claim[$i]->id);

            return [
                "payload" => $claim,
                "status" => "200_1"
            ];
        }
    }

    public function create(Request $request)
    {

        if ($request->id == 0) {

            $validator = Validator::make($request->all(), [
                //"name" => "required:claims,name",
            ]);
            if ($validator->fails()) {
                return [
                    "payload" => $validator->errors(),
                    "status" => "406_2"
                ];
            }
            $claim = Claim::make($request->all());
            //$claim->status = "On progress";
            if ($claim->status == "" || $claim->status == null) {
                $claim->status = "On progress";
            }
            $claim->save();
            if ($request->file()) {
                for ($i = 0; $i < count($request["files"]); $i++) {
                    $file = $request["files"][$i];
                    $filename = time() . "_" . $file->getClientOriginalName();
                    $this->uploadOne($file, config('cdn.claim.path'), $filename, "public_uploads_claim_incident_report");
                    $claimOrIncidentFile = new ClaimOrIncidentFile();
                    $claimOrIncidentFile->filename = $filename;
                    $claimOrIncidentFile->claim_id = $claim->id;

                    $claimOrIncidentFile->save();
                }
            }
            return [
                "payload" => $claim,
                "status" => "200"
            ];
        } else {

            $validator = Validator::make($request->all(), [
                "id" => "required",
            ]);
            if ($validator->fails()) {
                return [
                    "payload" => $validator->errors(),
                    "status" => "406_2"
                ];
            }
            $claim = Claim::find($request->id);
            if (!$claim) {
                return [
                    "payload" => "The searched row does not exist !",
                    "status" => "404_3"
                ];
            }
            if ($request->name != $claim->name) {
                if (Claim::where("name", $request->name)->count() > 0)
                    return [
                        "payload" => "The claim has been already taken ! ",
                        "status" => "406_2"
                    ];
            }
            $claim->claim_date = $request->claim_date;
            $claim->incident_date = $request->incident_date;
            $claim->ClaimOrIncident = $request->ClaimOrIncident;
            $claim->type = $request->type;

            $claim->save();

            if ($request->file()) {
                for ($i = 0; $i < count($request["files"]); $i++) {
                    $file = $request["files"][$i];
                    $filename = time() . "_" . $file->getClientOriginalName();
                    $this->uploadOne($file, config('cdn.claim.path'), $filename, "public_uploads_claim_incident_report");
                    $claimOrIncidentFile = new ClaimOrIncidentFile();
                    $claimOrIncidentFile->filename = $filename;
                    $claimOrIncidentFile->claim_id = $claim->id;

                    $claimOrIncidentFile->save();
                }
            }
            // delete files
            if (!empty($request["filesDelete"]) && $request["filesDelete"] != null) {
                for ($i = 0; $i < count($request["filesDelete"]); $i++) {
                    $claimOrIncidentFile = ClaimOrIncidentFile::find($request["filesDelete"][$i]["id"]);
                    $this->deleteOne(config('cdn.claim.path'), $claimOrIncidentFile->filename);
                    $claimOrIncidentFile->delete();
                }
            }
            return [
                "payload" => $claim,
                "status" => "200"
            ];
        }
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "id" => "required",
        ]);
        if ($validator->fails()) {
            return [
                "payload" => $validator->errors(),
                "status" => "406_2"
            ];
        }
        $claim = Claim::find($request->id);
        if (!$claim) {
            return [
                "payload" => "The searched row does not exist !",
                "status" => "404_3"
            ];
        }
        if ($request->name != $claim->name) {
            if (Claim::where("name", $request->name)->count() > 0)
                return [
                    "payload" => "The claim has been already taken ! ",
                    "status" => "406_2"
                ];
        }
        //add other files

        if ($request->file()) {
            for ($i = 0; $i < count($request["files"]); $i++) {
                $file = $request["files"][$i];
                $filename = time() . "_" . $file->getClientOriginalName();
                $this->uploadOne($file, config('cdn.claim.path'), $filename, "public_uploads_claim_incident_report");
                $claimOrIncidentFile = new ClaimOrIncidentFile();
                $claimOrIncidentFile->filename = $filename;
                $claimOrIncidentFile->claim_id = $claim->id;
                $claimOrIncidentFile->save();
            }
        }
        // delete files
        if (!empty($request["filesDelete"]) && $request["filesDelete"] != null) {
            for ($i = 0; $i < count($request["filesDelete"]); $i++) {
                $claimOrIncidentFile = ClaimOrIncidentFile::find($request["filesDelete"][$i]["id"]);
                $this->deleteOne(config('cdn.claim.path'), $claimOrIncidentFile->filename);
                $claimOrIncidentFile->delete();
            }
        }


        $claim->claim_date = $request->claim_date;
        $claim->incident_date = $request->incident_date;
        $claim->ClaimOrIncident = $request->ClaimOrIncident;
        $claim->type = $request->type;

        $claim->save();
        return [
            "payload" => $claim,
            "status" => "200"
        ];
    }

    public function delete(Request $request)
    {
        $claim = Claim::find($request->id);
        if (!$claim) {
            return [
                "payload" => "The searched row does not exist !",
                "status" => "404_4"
            ];
        } else {
            $claim->delete();
            return [
                "payload" => "Deleted successfully",
                "status" => "200_4"
            ];
        }
    }
}
