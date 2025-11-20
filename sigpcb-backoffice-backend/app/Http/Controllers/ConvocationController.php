<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ConvocationController extends ApiController
{
    public function sendConvocations()
    {
        $this->hasAnyPermission(["all", "edit-programming"]);

        $request = request();

        $request->merge([
            'agent_id' => auth()->id()
        ]);
        return  $this->postToBase("convocations", $request->all());
    }

    public function sendConduiteConvocations()
    {
        $this->hasAnyPermission(["all", "edit-programming"]);

        $request = request();

        $request->merge([
            'agent_id' => auth()->id()
        ]);
        return  $this->postToBase("conduite/convocations", $request->all());
    }
}
