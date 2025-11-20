<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inspecteur extends Model
{
    use HasFactory;
    protected $connection  = "admin";

    protected $table = "inspecteurs";

    /**
     * Récupère avec le USER
     *
     * @param array $attributes
     * @return $this
     */
    public function withUser(array $attributes = ["*"])
    {
        $admin = Admin::find($this->user_id, $attributes);
        $this->setAttribute("user", $admin);

        return $this;
    }
}
