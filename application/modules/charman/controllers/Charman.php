<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Charman extends MX_Controller
{
   
    public function charman_view($id)
    {
        $data = [
            'pagetitle' => 'Character Manager',
            'lang'      => $this->lang->lang(),
            'realms'    => $this->wowrealm->getRealms()->result(),
        ];
    
        $this->template->buildz('charman', $data);
    }
}


?>