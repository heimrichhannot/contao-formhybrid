<?php 

namespace HeimrichHannot\FormHybrid;

class Submission extends \Model
{

    public function __construct($arrData=array())
    {
        $this->arrData = $arrData;
    }

    public function save(){}

    public function delete(){}

    public function refresh(){}
}