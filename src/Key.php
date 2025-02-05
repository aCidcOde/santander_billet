<?php

namespace FlyCorp\SantanderBillet;

class Key {
    private $type;
    private $dictKey;

    public function setType($type) { $this->type = $type; return $this; }
    public function setDictKey($dictKey) { $this->dictKey = $dictKey; return $this; }

    public function toArray() { return get_object_vars($this); }
}