<?php

namespace App\Services\Imports;

class FromExcelRow
{
    public function __construct(protected array $row)
    {
    }

    /**
     * N°
     * @return string|null
     */
    public function getN()
    {
        return $this->trimIfNotEmpty($this->row[0]);
    }

    /**
     * Auto-école
     * @return string|null
     */
    public function getAutoEcole()
    {
        return $this->trimIfNotEmpty($this->row[1]);
    }

    /**
     * Département
     * @return string|null
     */
    public function getDepartement()
    {
        return $this->trimIfNotEmpty($this->row[2]);
    }

    /**
     * Commune
     * @return string|null
     */
    public function getCommune()
    {
        return $this->trimIfNotEmpty($this->row[3]);
    }

    /**
     * NPI du Promoteur
     * @return string|null
     */
    public function getNpiDuPromoteur()
    {
        return $this->trimIfNotEmpty($this->row[4]);
    }

    /**
     * E-mail du promoteur
     * @return string|null
     */
    public function getEMailDuPromoteur()
    {
        $email = $this->trimIfNotEmpty($this->row[5]);
        return $email ? strtolower($email) : null;
    }

    /**
     * E-mail professionnel
     * @return string|null
     */
    public function getEMailProfessionnel()
    {
        $email = $this->trimIfNotEmpty($this->row[6]);
        return $email ? strtolower($email) : null;
    }

    /**
     * Téléphone professionnel
     * @return string|null
     */
    public function getTelephoneProfessionnel()
    {
        return $this->trimIfNotEmpty($this->row[7]);
    }

    /**
     * Adresse  (quartier,ilot,parcelle)
     * @return string|null
     */
    public function getAdresseQuartierilotparcelle()
    {
        return $this->trimIfNotEmpty($this->row[8]);
    }

    /**
     * Date expiration  de la licence
     * @return string|null
     */
    public function getDateExpirationDeLaLicence()
    {
        return $this->trimIfNotEmpty($this->row[9]);
    }

    /**
     * Référence autorisation
     * @return string|null
     */
    public function getReferenceAutorisation()
    {
        return $this->trimIfNotEmpty($this->row[10]);
    }

    /**
     * NPIs des moniteurs  (séparés par ,)
     * @return string|null
     */
    public function getNpisDesMoniteursSeparesPar()
    {
        return $this->trimIfNotEmpty($this->row[11]);
    }

    /**
     * IFU
     * @return string|null
     */
    public function getIfu()
    {
        return $this->trimIfNotEmpty($this->row[12]);
    }

    /**
     * Code licence
     * @return string|null
     */
    public function getCodeLicence()
    {
        return $this->trimIfNotEmpty($this->row[13]);
    }

    /**
     * Véhicules de l'auto-école
     * @return string|null
     */
    public function getVehiculesDeLautoEcole()
    {
        return $this->trimIfNotEmpty($this->row[14]);
    }

    /**
     * Trim the value if not empty
     * @param string|null $value
     * @return string|null
     */
    protected function trimIfNotEmpty(?string $value): ?string
    {
        return $value !== null ? trim($value) : null;
    }
}
