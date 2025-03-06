<?php

namespace Acidcode\SantanderBillet;

class Beneficiary {
    private $name;
    private $documentType;
    private $documentNumber;

    public function setName($name) { $this->name = $name; return $this; }
    public function setDocumentType($documentType) { $this->documentType = $documentType; return $this; }
    public function setDocumentNumber($documentNumber) { $this->documentNumber = $documentNumber; return $this; }
    
    public function toArray() { return get_object_vars($this); }
}