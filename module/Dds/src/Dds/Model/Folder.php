<?php

namespace Dds\Model;

class Folder {

    public $mtype; //PK
    public $prefix;
    public $disname;

    public function exchangeArray($data) {
        $this->mtype = (isset($data['Mtype'])) ? $data['Mtype'] : null;
        $this->prefix = (isset($data['prefix'])) ? $data['prefix'] : null;
        $this->disname = (isset($data['disName'])) ? $data['disName'] : null;
    }

    public function getArrayCopy() {
        return get_object_vars($this);
    }


}
