<?php 

namespace HeimrichHannot\FormHybrid;

class Submission extends \Model
{

    public function __construct($arrData= [])
    {
        $this->arrData = $arrData;
    }

    public function save(){}

    public function delete(){}

    public function refresh(){}
}