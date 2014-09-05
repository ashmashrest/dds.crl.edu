<?php

namespace Dds\Model;

class Collection {

    public $cid; //PK
    public $collectionname;
    public $startdate;
    public $inhouse;
    public $resolution;
    public $colordepth;
    public $tiff;
    public $pdf;
    public $jpeg;
    public $thumbnail;
    public $vendor;
    public $archivelocation;
    public $metadatanotes;
    public $timestamp;
    public $format;
    public $plans;

    public function exchangeArray($data) {
        $this->cid = (isset($data['CID'])) ? $data['CID'] : null;
        $this->collectionname = (isset($data['CollectionName'])) ? $data['CollectionName'] : null;
        $this->startdate = (isset($data['StartDate'])) ? $data['StartDate'] : null;
        $this->inhouse = (isset($data['InHouse'])) ? $data['InHouse'] : null;
        $this->resolution = (isset($data['Resolution'])) ? $data['Resolution'] : null;
        $this->colordepth = (isset($data['ColorDepth'])) ? $data['ColorDepth'] : null;
        $this->tiff = (isset($data['Tiff'])) ? "TIFF; group IV compression for bitonal images, uncompressed for grayscale" : null;
        $this->pdf = (isset($data['PDF'])) ? "PDF page images with OCR when possible (uncorrected)" : null;
        $this->jpeg = (isset($data['Jpeg'])) ? $data['Jpeg'] : null;
        $this->thumbnail = (isset($data['Thumbnail'])) ? $data['Thumbnail'] : null;
        $this->vendor = (isset($data['Vendor'])) ? $data['Vendor'] : null;
        $this->archivelocation = (isset($data['ArchiveLocation'])) ? $data['ArchiveLocation'] : null;
        $this->metadatanotes = (isset($data['MetadataNotes'])) ? $data['MetadataNotes'] : null;
        $this->timestamp = (isset($data['STimeStamp'])) ? $data['STimeStamp'] : null;
        $this->format = (isset($data['Format'])) ? strtolower($data['Format']) : null;
        $this->plans = (isset($data['Plans'])) ? $data['Plans'] : null;
    }

    public function getArrayCopy() {
        return get_object_vars($this);
    }
  
}