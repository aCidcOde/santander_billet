<?php

namespace Acidcode\SantanderBillet;

class Payer {
    private $name;
    private $documentType;
    private $documentNumber;
    private $address;
    private $neighborhood;
    private $city;
    private $state;
    private $zipCode;

    public function setName($name) { $this->name = $name; return $this; }
    public function setDocumentType($documentType) { $this->documentType = $documentType; return $this; }
    public function setDocumentNumber($documentNumber) { $this->documentNumber = $documentNumber; return $this; }
    public function setAddress($address) { $this->address = $address; return $this; }
    public function setNeighborhood($neighborhood) { $this->neighborhood = $neighborhood; return $this; }
    public function setCity($city) { $this->city = $city; return $this; }
    public function setState($state) { $this->state = $state; return $this; }
    public function setZipCode($zipCode) { $this->zipCode = $zipCode; return $this; }
    
    public function toArray() { return get_object_vars($this); }
}