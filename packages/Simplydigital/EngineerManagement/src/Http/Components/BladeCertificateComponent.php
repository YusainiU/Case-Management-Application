<?php
namespace Simplydigital\EngineerManagement\Http\Components;
use Illuminate\View\Component;
class BladeCertificateComponent extends Component
{
    public function render()
    {
        return view('engineermanagement::components.certificate-component');
    }
}
