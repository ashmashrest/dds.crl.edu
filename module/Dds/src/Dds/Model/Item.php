<?php

namespace Dds\Model;

class Item {

    public $iid; //PK
    public $titlelink; //FK
    public $year;
    public $part1;
    public $part2;
    public $pubdate;
    public $filename;
    public $digitalpno;
    public $pages;
    public $rights;
    public $sortorder;
    public $fileuneven;
    public $toc;
    public $icounter;
    public $inotes;
    public $icreated;
    public $noocr;
    public $qualityoriginal;
    public $qualityreproduction;
    public $qualitymissingpage;
    public $timestamp;
    public $memberonly;
    public $copyright;
    public $item_text;
    public $f;
    public $perpage;
    public $filelocation;
    public $folderlocation;

    public function exchangeArray($data) {
        $this->iid = (isset($data['IID'])) ? $data['IID'] : null;
        $this->titlelink = (isset($data['TitleLink'])) ? $data['TitleLink'] : null;
        $this->year = (isset($data['Year'])) ? $data['Year'] : null;
        $this->part1 = (isset($data['Part1'])) ? $data['Part1'] : null;
        $this->part2 = (isset($data['Part2'])) ? $data['Part2'] : null;
        $this->pubdate = (isset($data['Pubdate'])) ? $data['Pubdate'] : null;
        $this->filename = (isset($data['FileName'])) ? $data['FileName'] : null;
        $this->digitalpno = (isset($data['DigitalPNo'])) ? $data['DigitalPNo'] : null;
        $this->pages = (isset($data['Pages'])) ? $data['Pages'] : null;
        $this->rights = (isset($data['Rights'])) ? trim($data['Rights']) : null;
        $this->sortorder = (isset($data['SortOrder'])) ? $data['SortOrder'] : null;
        $this->fileuneven = (isset($data['FileUneven'])) ? $data['FileUneven'] : null;
        $this->toc = (isset($data['TOC'])) ? $data['TOC'] : null;
        $this->icounter = (isset($data['iCounter'])) ? $data['iCounter'] : null;
        $this->inotes = (isset($data['iNotes'])) ? $data['iNotes'] : null;
        $this->icreated = (isset($data['iCreated'])) ? $data['iCreated'] : null;
        $this->noocr = (isset($data['NoOCR'])) ? $data['NoOCR'] : null;
        $this->timestamp = (isset($data['STimeStamp'])) ? $data['STimeStamp'] : null;
        $this->memberonly = (isset($data['Rights']) && $data['Rights'] == "MemOnly") ? "Restricted Access" : null;
        $this->copyright = (isset($data['Rights']) && $data['Rights'] == "CopyRt") ? " &copy;" : null;
        $this->f = null;
        $this->perpage = (isset($data['PerPage'])) ? $data['PerPage'] : False;
        $this->item_text = $this->getItemText();
        $this->filelocation = "";
        $this->folderlocation = "";
    }

    private function getItemText() {

        if ($this->part1) {
            $item_text = $this->part1;
        }
        if ($this->part2) {
            $item_text .= " " . $this->part2;
        }
        
        if (empty($item_text)) {
            $item_text = "Full text PDF";
        }

        return $item_text;
    }

    public function getArrayCopy() {
        return get_object_vars($this);
    }

}