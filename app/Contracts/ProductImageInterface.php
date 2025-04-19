<?php

namespace App\Contracts;

interface ProductImageInterface{
    public function createNew(array $data);
    public function update(string $id, array $data);
    public function getAll();
    public function delete(string $id);
}
