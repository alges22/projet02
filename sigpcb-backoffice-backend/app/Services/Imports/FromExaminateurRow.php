<?php

namespace App\Services\Imports;

class FromExaminateurRow
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
     * NPI
     */
    public function getNpi()
    {
        return $this->trimIfNotEmpty($this->row[1]);
    }

    public function getEmail()
    {
        return $this->trimIfNotEmpty($this->row[2]);
    }

    public function getNumeroPermis()
    {
        return $this->trimIfNotEmpty($this->row[3]);
    }

    public function getCategoriesGerees()
    {
        return $this->trimIfNotEmpty($this->row[4]);
    }

    public function getAnnexe()
    {
        return $this->trimIfNotEmpty($this->row[5]);
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
