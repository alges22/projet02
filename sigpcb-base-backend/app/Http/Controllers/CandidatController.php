<?php

namespace App\Http\Controllers;

use Exception;
use SoapWrapper;
use App\Models\AnipUser as User;
use App\Services\Anip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\ApiController;
use App\Services\GlobalException;
use Illuminate\Support\Facades\Validator;


class CandidatController extends ApiController
{

    public function index()
    {
        try {
            $npis = request('npis', []);
            if (!$npis) {
                $npis = [];
            } else {
                $npis = explode(',', $npis);
                $npis = array_map('trim', $npis); // Supprimer les espaces autour des NPIs
            }
            return $this->successResponse(Anip::get($npis));
        } catch (GlobalException $th) {
            logger()->error($th);
            return $this->errorResponse($th->getMessage());
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }

    public function anipImage()
    {
        try {
            $npis = request('npis', []);
            if (!$npis) {
                $npis = [];
            } else {
                $npis = explode(',', $npis);
                $npis = array_map('trim', $npis); // Supprimer les espaces autour des NPIs
            }
            return $this->successResponse(Anip::getPicture($npis));
        } catch (GlobalException $th) {
            logger()->error($th);
            return $this->errorResponse($th->getMessage());
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }
    public function getInformations(Request $request, $npi)
    {
        try {
            return $this->successResponse(Anip::get($npi));
        } catch (GlobalException $th) {
            logger()->error($th);
            return $this->errorResponse($th->getMessage());
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }

}
