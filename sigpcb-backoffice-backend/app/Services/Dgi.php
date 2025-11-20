<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class Dgi
{
    private array $data = [];
    public function __construct(private $ifu)
    {
        $this->fetch();
    }
    private function fetch()
    {
        // Effectuer la requête HTTP
        $response = Http::withOptions(['verify' => false])->withHeaders([
            'Uxp-Client' => 'BJ/GOV/ANATT/SIGPCB',
            'Uxp-Service' => 'BJ/GOV/DGI/CFISC/DETAIL-IFU/V1',
            'Authorization' => 'Bearer eyJhbGciOiJIUzUxMiJ9.eyJzdWIiOiI1MDQiLCJVc2VyIjp7ImlkIjo1MDQsIm5vbSI6IkFOQVRUIEJFTklOIiwicHJlbm9tIjoiQU5BVFQgQkVOSU4iLCJ1c2VybmFtZSI6ImFuYXR0LWFwaSIsInRlbGVwaG9uZSI6Ijk1MDUxMTU1IiwiZW1haWwiOiJhZm9hZGV5QGdvdXYuYmoiLCJ1c2VyU3RhdHVzIjoiQUNUSVZBVEVEIiwiaW5pdFBhc3N3b3JkIjpmYWxzZSwiY2VudHJlSW1wb3QiOnsiaWQiOjk0LCJjb2RlIjoiSU1NQVQiLCJsaWJlbGxlIjoiU2VydmljZSBkJ2ltbWF0cmljdWxhdGlvbiBkZXMgcGVyc29ubmVzIHBoeXNpcXVlcyIsInR5cGVDZW50cmUiOiJBVVRSRSIsImRhdGVEZWJ1dCI6IjIwMjMtMDEtMTIgMTc6MzU6MDEiLCJkYXRlRmluIjpudWxsLCJvcGVyYXRvckRhdGUiOm51bGx9LCJyb2xlcyI6W3siaWQiOjExLCJuYW1lIjoiUk9MRV9VU0VSX0FQSSJ9XX0sImlhdCI6MTczODU3OTQ4MCwiZXhwIjoxNzcwMTM2NDMyfQ.H1bprrHzbPldNEbRTQxamXQ81h8MzX4-8tgeMFdbtIIloDH_urfmaWqQIPNB5eFXyA8kVpT4TFr9fvG03fzHmw'
        ])->get("https://common-ss.xroad.bj:8443/restapi", ['ifu' => $this->ifu]);

        $this->data = $response->json() ?? [];
    }

    public function exists()
    {
        return $this->status() == 'OK';
    }

    public function get($key)
    {
        return data_get($this->data, $key);
    }

    public function raisonSociale()
    {
        return $this->get("object.raisonSociale") ?? 'Inconnue';
    }

    public function status()
    {
        return $this->get("status");
    }

    public function ifu()
    {
        return $this->get("object.ifu");
    }
    public function type()
    {
        return $this->get("object.type");
    }

    public function rccm()
    {
        return $this->get("object.rccm");
    }
    public function categorie()
    {
        return $this->get("object.categorie");
    }

    public function telephone()
    {
        return $this->get("object.telephone");
    }

    public function email()
    {
        return $this->get("object.email");
    }

    public function ville()
    {
        return $this->get("object.ville");
    }

    public function data()
    {
        return $this->data;
    }
}
